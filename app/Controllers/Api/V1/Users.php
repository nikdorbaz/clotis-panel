<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\ApiRouter;
use App\Controllers\Base;
use CodeIgniter\I18n\Time;

class Users extends ApiRouter
{
  public function index()
  {
    return $this->respond(['GET index']);
  }

  public function postLogin()
  {
    return $this->respond(['POST login']);
  }

  public function putUpdate()
  {
    return $this->respond(['PUT update']);
  }

  public function deleteDelete()
  {
    return $this->respond(['DELETE delete']);
  }

  public function auth()
  {
    $raw = file_get_contents('php://input');
    // Шаг 2. Преобразуем в массив параметров
    parse_str($raw, $params);

    // Шаг 3. Декодируем JSON из поля 'user'
    $userJson = urldecode($params['user'] ?? '{}');
    $user = json_decode($userJson, true);

    // Шаг 4. Получаем chat_id
    $chatId = $user['id'] ?? null;

    // Ищем чат пользователя
    $chat = model('Chats')->where('chatId', $chatId)
      ->select('id, name, phone, avatar')
      ->first();

    if (!$chat) {
      return $this->respond(['failed' => true, 'message' => 'Чат не найден']);
    }

    // Ищем пользователя среди клиентов
    $client = model('ClientChats')->where('chat_id', $chat['id'])->first();
    if (!$client) {
      return $this->respond(['failed' => true, 'message' => 'Клиент не был создан', 'chat' => $chat]);
    }

    // Ищем добавленные торговые точки
    $clientEntities = model('ClientEntities')->where('client_id', $client['client_id'])->find();
    if (is_null($clientEntities)) {
      return $this->respond(['failed' => true, 'message' => 'Торговая точка не добавлена']);
    }

    $bonus = model('BonusHistory')->select('SUM(bonus) as total_bonus')
      ->where('chat_id', $chat['id'])
      ->first();

    $chat['total_bonus'] = $bonus['total_bonus'] ?? 0;
    return $this->respond($chat);
  }

  public function getEntities()
  {
    $profileEntities = model('ClientChats')->join('client_entities', 'client_entities.client_id = client_chats.client_id', 'left')
      ->select('client_entities.id, client_entities.address, client_entities.company_name, client_entities.full_name, client_entities.receipt_type, client_entities.shop_name')
      ->where('client_chats.chat_id', $this->request->getGet('chat_id'))
      ->find();

    return $this->respond($profileEntities);
  }

  public function updateProfile()
  {

    $id = $this->request->getPost('id');

    $chat = model('ClientChats')->where('chat_id', $this->request->getPost('chat_id'))->first();

    if (is_null($chat)) {
      $this->respond(['client not found']);
    }

    $data = [
      'client_id' => $chat['client_id'],
      'address' => $this->request->getPost('address'),
      'company_name' => $this->request->getPost('company_name'),
      'receipt_type' => $this->request->getPost('receipt_type'),
      'shop_name' => $this->request->getPost('shop_name'),
    ];

    if ($id !== 'undefined') {
      model('ClientEntities')->update($id, $data);
    } else {
      $id = model('ClientEntities')->insert($data);
    }


    return $this->respond([
      'id' => $id
    ]);
  }

  public function register()
  {
    $phone = $this->request->getPost('phone');

    $chat = model('Chats')->like('phone', $phone)->first();

    if (is_null($chat)) {
      return $this->respond(['failed' => true, 'message' => 'Не найден номер телефона, не доверенный пользователь']);
    }

    // Ищем клиента по чату
    $client = model('Clients')->where('chat_id', $chat['id'])->first();
    $clientId = $client['id'] ?? 0;
    if (is_null($client)) {
      // Если клиента нет, регистрируем как нового клиента
      $clientId = model('Clients')->insert([
        'name' => $chat['name'],
        'phone' => $phone,
      ]);

      // Привязываем чат клиента
      model('ClientChats')->insert([
        'chat_id' => $chat['id'],
        'client_id' => $clientId,
      ]);
    }

    $data = [
      'client_id' => $clientId,
      'address' => $this->request->getPost('address'),
      'company_name' => $this->request->getPost('company_name'),
      'receipt_type' => $this->request->getPost('receipt_type'),
      'shop_name' => $this->request->getPost('shop_name'),
    ];

    model('ClientEntities')->insert($data);

    return $this->respond($chat);
  }

  public function getStats()
  {

    $chat = model('Chats')->find($this->request->getGet('chat_id'));

    if (is_null($chat)) {
      return $this->respond(['failed' => true, 'message' => 'Error message']);
    }


    $bonuses = model('BonusHistory')->select('SUM(bonus) as total')
      ->where('chat_id', $chat['id'])
      ->first();

    $withdrawCount = model('BonusHistory')->where([
      'chat_id' => $chat['id'],
      'pending' => 1,
    ])->countAllResults();

    $receipts = model('Receipts')->where('chat_id', $chat['id'])->countAllResults();

    $data = [
      'balance' => $bonuses['total'],
      'withdraw_requests' => $withdrawCount,
      'withdraw' => 0,
      'bonuses_by_referral' => 0,
      'receipts_count' => $receipts,
      'quests_count' => 0,
      'days' => $this->getUserDaysWithLabel($chat['created_at']),
    ];


    return $this->respond($data);
  }

  public function withdrawRequests()
  {
    // sleep(1);
    $leads = model('Leads')->find();

    return $this->respond($leads);
  }

  private function getUserDaysWithLabel(string $createdAt): string
  {
    $created = Time::parse($createdAt);
    $now = Time::now();
    $days = $created->difference($now)->getDays();

    // Если меньше 1 дня — считаем как 1
    if ($days < 1) {
      $days = 1;
    }

    // Определим окончание
    $lastDigit = $days % 10;
    $lastTwoDigits = $days % 100;

    if ($lastTwoDigits >= 11 && $lastTwoDigits <= 14) {
      $label = 'днів';
    } elseif ($lastDigit == 1) {
      $label = 'день';
    } elseif ($lastDigit >= 2 && $lastDigit <= 4) {
      $label = 'дні';
    } else {
      $label = 'днів';
    }

    return "$days $label";
  }

  public function phoneTopup()
  {
    $chatID = $this->request->getPost('chat_id');
    $bonus = $this->request->getPost('bonus');
    $phone = $this->request->getPost('phone');

    $chat = model('Chats')->find($chatID);
    if (is_null($chat)) {
      return $this->respond([
        'failed' => true,
        'message' => 'Чат не знайдено'
      ]);
    }

    if (!$bonus) {
      return $this->respond([
        'failed' => true,
        'message' => 'Мінімальна сума для поповнення 20'
      ]);
    }

    $bonuses = model('BonusHistory')->select('SUM(bonus) as total_bonus')
      ->where('chat_id', $chatID)
      ->first();

    if ($bonuses['total_bonus'] < $bonus) {
      return $this->respond([
        'failed' => true,
        'message' => 'На вашому балансі недостатньо коштів'
      ]);
    }

    model('BonusHistory')->insert([
      'chat_id' => $chat['id'],
      'bonus_type' => 'mobile_topup',
      'bonus' => -abs($bonus),
      'pending' => 1,
    ]);

    $leadData = [
      'chat_id' => $chat['id'],
      'name' => $chat['name'],
      'phone' => $chat['phone'],
      'action_type' => 'mobile_topup',
      'source' => 'Замовлення поповнення',
      'status' => 'none',
      'fields' => json_encode([
        [
          'quest' => 'Сума бонусів для поповнення',
          'answer' => $bonus,
        ],
        [
          'quest' => 'Номер телефону для поповнення',
          'answer' => $phone,
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
