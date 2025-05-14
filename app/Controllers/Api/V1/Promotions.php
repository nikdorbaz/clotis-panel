<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\ApiRouter;
use App\Controllers\Base;
use App\Libraries\ChatGpt;
use CodeIgniter\Controller;

class Promotions extends ApiRouter
{
  public function index()
  {
    $promotions = model('Promotions')->find();

    foreach ($promotions as &$promotion) {
      $promotionProducts = model('PromotionProducts')->join('products', 'products.id = promotion_products.product_id', 'left')
        ->select('products.name')
        ->where('promotion_id', $promotion['id'])
        ->find();

      $promotion['products'] = $promotionProducts;
    }


    return $this->respond($promotions);
  }

  public function upload() {}
}
