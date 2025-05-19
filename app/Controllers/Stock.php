<?php

namespace App\Controllers;

class Stock extends BaseController
{
  public function index()
  {
    $stock = session('stock');

    if (is_null($stock)) {
      return redirect()->to('/');
    }

    dd(session('stock'));

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
      $type = $this->request->getGet('type') ?? "ordini";

      if ($type == 'spedizione') {
        $result = service('ApiHelper')->setParams([
          'id' => $id
        ])->setMethod('api/v1/sales/getSpedizione')->getResult();
      } else {
        $result = service('ApiHelper')->setParams([
          'id' => $id
        ])->setMethod('api/v1/sales/get')->getResult();
      }

      timer()->stop('apiRequest');

      // dd(timer()->getElapsedTime('apiRequest'));
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('table/index', ['result' => $result, 'error' => $error, 'name' => $warehouses[$id], 'type' => $type]);
  }
}
