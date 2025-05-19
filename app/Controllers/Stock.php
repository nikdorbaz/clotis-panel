<?php

namespace App\Controllers;

class Stock extends BaseController
{
  public function index(int $id): string
  {
    $result = null;
    $error = null;
    $warehouses = [
      64 => 'Склад CA',
      54 => 'Склад DS/TL',
      57 => 'Склад CL',
      29 => 'Склад AX',
      28 => 'Склад TR',
      65 => 'Склад IN',
      47 => 'Склад BB',
      60 => 'Склад IT',
      61 => 'Склад PS',
    ];

    timer('apiRequest');
    try {
      $type = $this->request->getGet('type');

      if ($type == 'spedizione') {
        $url = 'https://clotiss.site/api/v1/sales/getSpedizione?id=' . $id;
      } else {
        $url = 'https://clotiss.site/api/v1/sales/get?id=' . $id;
      }

      $response = file_get_contents($url);

      if ($response === false) {
        throw new \Exception('Ошибка при выполнении GET-запроса.');
      }

      $result = json_decode($response, true);

      timer()->stop('apiRequest');

      // dd(timer()->getElapsedTime('apiRequest'));
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('table/index', ['result' => $result, 'error' => $error, 'name' => $warehouses[$id], 'type' => $type]);
  }
}
