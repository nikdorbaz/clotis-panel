<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\ApiRouter;
use App\Controllers\Base;
use App\Libraries\ChatGpt;
use CodeIgniter\Controller;

class Bonuses extends ApiRouter
{
  public function index()
  {
    $chatID = $this->request->getGet('chat_id') ?? 0;
    $type = $this->request->getGet('type') ?? "success";

    if (!$chatID) {
      return $this->respond(['error' => 'chat_id is required']);
    }

    if ($type == 'success') {
      $bonuses = model('BonusHistory')->join('receipt', 'receipt.id = client_bonus_history.receipt_id')
        ->where([
          'client_bonus_history.chat_id' => $chatID,
          'client_bonus_history.receipt_id !=' => null,
          'client_bonus_history.bonus >' => 0,
        ])->orderBy('client_bonus_history.created_at', 'ASC')->findAll();
    } else {
      $bonuses = model('BonusHistory')->join('receipt', 'receipt.id = client_bonus_history.receipt_id')
        ->where([
          'client_bonus_history.chat_id' => $chatID,
          'client_bonus_history.receipt_id !=' => null,
          'client_bonus_history.bonus' => 0,
        ])->orderBy('client_bonus_history.created_at', 'ASC')->findAll();
    }

    foreach ($bonuses as $key => $bonus) {
      $bonuses[$key]['created_at'] = date('d.m.Y H:i', strtotime($bonus['created_at']));
      $bonuses[$key]['product_name'] = getProductNameByReceipt($bonus['ocr_result']);
    }

    // $bonuses = [];
    return $this->respond($bonuses);
  }

  public function getByUser()
  {
    $chatId = $this->request->getPost('chat_id') ?? 0;
    if (!$chatId) {
      return $this->respond(['error' => 'chat_id is required']);
    }

    $bonuses = model('BonusHistory')->select('SUM(bonus) as total_bonus')
      ->where('chat_id', $chatId)
      ->groupBy('chat_id')
      ->first();

    return $this->respond($bonuses);
  }
}
