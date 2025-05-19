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

    $result = null;
    $error = null;

    timer('apiRequest');

    $id = $stock['id'];
    $name = $stock['name'];
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
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('table/index', ['result' => $result, 'error' => $error, 'name' => $name, 'type' => $type]);
  }
}
