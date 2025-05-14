<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\ApiRouter;
use App\Controllers\Base;
use App\Libraries\ChatGpt;
use App\Libraries\ReceiptParse;
use App\Models\Receipts;
use CodeIgniter\Controller;

class Receipt extends ApiRouter
{
  public function index()
  {
    return $this->respond(['GET index']);
  }

  public function upload()
  {
    make_dir('receipts');

    $data = [];
    $chatID = $this->request->getPost('chat_id') ?? 0;
    $entityID = $this->request->getPost('entity_id') ?? 0;

    if (!$chatID) {
      return $this->respond(['error' => 'Чат не знайдено']);
    }

    if (!$entityID) {
      return $this->respond(['error' => 'Додайте компанію']);
    }

    $clientEntity = model('ClientEntities')->find($entityID);

    if ($file = $this->request->getFile('image')) {

      if ($file->hasMoved() && !$file->isValid() && !$file->isReadable()) {
        return $this->respond(['error' => 'Не вдалося обробити файл. Переконайтесь, що ви надіслали правильне зображення, та спробуйте ще раз.']);
      }
      if ($file->getSize() > 30 * 1024 * 1024) { // 30MB
        return $this->respond(['error' => 'Файл занадто великий, будь ласка, надішліть сжатий файл']);
      }

      // Сохраняем оригинальный файл
      $tempFileName = $file->getRandomName();
      $originalPath = FILESPATH . DIRECTORY_SEPARATOR . SUBDOMAIN_NAME . '/receipts/' . $tempFileName;
      $file->move(FILESPATH . DIRECTORY_SEPARATOR . SUBDOMAIN_NAME . '/receipts', basename($originalPath));

      // Сжимаем картинку 
      $compressedPath = FILESPATH . DIRECTORY_SEPARATOR . SUBDOMAIN_NAME . '/receipts/compressed_' . $tempFileName;
      $this->compressImage($originalPath, $compressedPath, 75); // 75% качество

      try {
        $result = (new ReceiptParse())
          ->setCompressedPath($compressedPath)
          ->setOriginalPath($originalPath)
          ->setReceiptType($this->getReceiptType($clientEntity['receipt_type']))
          ->setChatGptInstance(new ChatGpt())
          ->parse();
      } catch (\Throwable $e) {
        return $this->respond(['error' => $e->getMessage()]);
      }


      if ($clientEntity['receipt_type'] == 'Рукописні') {
        $data = [
          'status' => 'processing',
          'message' => 'Дякуємо за чек! Чек було передано на перевірку, ми повідомимо вас про результати.',
        ];
      } else {
        // Анализируем сжатую версию для экономии токенов
        $chatGpt = new ChatGpt();
        $response = $chatGpt->analyzeImage($compressedPath, "Будь ласка, уважно розпізнай дані з цього зображення чека");

        $receiptNumber = $response['receipt_number'] ?? null;

        if (!is_null($receiptNumber)) {
          $receipt = model('Receipts')->where('receipt_number', $receiptNumber)->first();

          if (!is_null($receipt)) {
            return $this->respond(['error' => 'Цей чек вже був відправлений раніше. Його дані не будуть враховані повторно.']);
          }
        }

        $bonus = $response['bonus'] ?? 0;

        $receiptID = model(Receipts::class)->insert([
          'chat_id' => $chatID,
          'client_entity_id' => $entityID,
          'file_name' => basename($originalPath),
          'file_path' => $originalPath,
          'file_hash' => $fileHash,
          'compressed_file_path' => $compressedPath,
          'ocr_result' => json_encode($response['data'] ?? []),
          'gpt_result' => json_encode($response['original'] ?? []),
          'bonus' => $bonus,
          'receipt_number' => $response['receipt_number'] ?? null,
          'receipt_date' => $response['receipt_date'] ?? null,
          'receipt_type' => $this->getReceiptType($clientEntity['receipt_type']),
          'status' => ($bonus > 0) ? 'completed' : 'pending'
        ]);

        model(\App\Models\BonusHistory::class)->insert([
          'chat_id' => $chatID,
          'bonus' => $response['bonus'] ?? 0,
          'bonus_type' => 'plus',
          'receipt_id' => $receiptID,
        ]);

        if ($response['bonus'] > 0) {
          $data = [
            'status' => 'success',
            'message' => 'Дякуємо за чек! Вам нараховано бонус: ' . $response['bonus'] . '',
          ];
        } else {
          $data = [
            'status' => 'processing',
            'message' => 'Дякуємо за чек! Чек було передано на перевірку, ми повідомимо вас про результати.',
          ];
        }
      }
    } else {
      return $this->respond(['error' => 'Файл не було завантажено, спробуйте ще раз.']);
    }

    return $this->respond($data);
  }

  private function compressImage($source, $destination, $quality = 75)
  {
    $maxWidth = 1000; // Максимальная ширина изображения для чеков
    $imagick = new \Imagick($source);

    // Приводим к чёрно-белому формату
    $imagick->setImageColorspace(\Imagick::COLORSPACE_GRAY);
    $imagick->modulateImage(100, 0, 100); // убираем цвет
    $imagick->contrastImage(true); // повышаем контрастность
    $imagick->enhanceImage(); // дополнительно улучшаем текст
    $imagick->reduceNoiseImage(1); // убираем шум

    // Масштабируем если надо
    $width = $imagick->getImageWidth();
    if ($width > $maxWidth) {
      $imagick->resizeImage($maxWidth, 0, \Imagick::FILTER_LANCZOS, 1);
    }

    // Сохраняем
    $imagick->setImageFormat('jpeg');
    $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
    $imagick->setImageCompressionQuality($quality);
    $imagick->setInterlaceScheme(\Imagick::INTERLACE_JPEG);
    $imagick->writeImage($destination);

    $imagick->clear();
    $imagick->destroy();
  }

  private function receiptStatus() {}

  /**
   * Получаем тип чека по данным клиента
   *
   * @return string
   */
  private function getReceiptType(string $name = ''): string
  {
    $types = Receipts::RECEIPT_TYPES;

    return array_search($name, $types, true) ?: '';
  }
}
