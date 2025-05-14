<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\ApiRouter;
use App\Controllers\Base;

class Certificates extends ApiRouter
{
  public function index()
  {
    $certificates = model('Certificates')->join('files', 'files.model_id = certificates.id')
      ->select('certificates.id, certificates.name, certificates.description, certificates.bonus_price, files.path as image_path')
      ->where('files.model', 'certificates')
      ->findAll();


    return $this->respond($certificates);
  }

  public function purchase()
  {

    $certificate = model('Certificates')->find($this->request->getPost('certificate_id'));

    if (is_null($certificate)) {
      return $this->respond([
        'failed' => true,
        'message' => "Сертифікат вже не активний"
      ]);
    }

    $chat = model('Chats')->find($this->request->getPost('chat_id'));
    if (is_null($chat)) {
      return $this->respond([
        'failed' => true,
        'message' => 'Чат не знайдено'
      ]);
    }

    $bonuses = model('BonusHistory')->select('SUM(bonus) as total_bonus')
      ->where('chat_id', $chat['id'])
      ->first();

    if ($bonuses['total_bonus'] < $certificate['bonus_price']) {
      return $this->respond([
        'failed' => true,
        'message' => 'На вашому балансі недостатньо коштів'
      ]);
    }

    model('BonusHistory')->insert([
      'chat_id' => $chat['id'],
      'bonus_type' => 'certificate',
      'bonus' => -abs($certificate['bonus_price']),
      'pending' => 1,
    ]);

    $leadData = [
      'chat_id' => $chat['id'],
      'name' => $chat['name'],
      'phone' => $chat['phone'],
      'action_type' => 'certificate',
      'source' => 'Замовлення сертифікату',
      'status' => 'none',
      'fields' => json_encode([
        [
          'quest' => 'Сума бонусів',
          'answer' => $certificate['bonus_price'],
        ],
        [
          'quest' => 'Сертифікат',
          'answer' => $certificate['name'],
          'link' => base_url('/certificates/edit/' . $certificate['id'])
        ],
      ])
    ];

    model(\App\Models\Leads::class)->insert($leadData);

    return $this->respond([
      'result' => true,
      'message' => 'Дякуємо, ваш запит прийнято',
    ]);
  }
}
