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

    try {
      $type = $this->request->getGet('type') ?? "ordini";

      if ($type == 'spedizione') {
        $result = service('ApiHelper')->setParams([
          'id' => $id
        ])->setMethod('api/v1/stock/getSpedizione')->getResult();
      } elseif ($type == 'ordini') {
        $result = service('ApiHelper')->setParams([
          'id' => $id
        ])->setMethod('api/v1/stock/getOrdini')->getResult();
      }

      $months = service('ApiHelper')->setParams([
        'id' => $id,
        'type' => $type,
      ])->setMethod('api/v1/stock/historyMonths')->getResult();

      timer()->stop('apiRequest');
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('table/index', ['result' => $result, 'error' => $error, 'stock' => $stock, 'type' => $type, 'months' => $months]);
  }

  public function history($month)
  {
    try {
      $stock = session('stock');
      if (is_null($stock)) {
        return redirect()->to('/');
      }

      $stockId = $stock['id'];
      $name    = $stock['name'];

      // Тип можно передавать через GET ?type=ordini, по умолчанию 'ordini'
      $type = $this->request->getGet('type') ?? 'ordini';

      $result = service('ApiHelper')->setParams([
        'id'    => $stockId,
        'type'  => $type,
        'month' => $month
      ])->setMethod('api/v1/stock/getHistory')->getResult();

      $months = service('ApiHelper')->setParams([
        'id' => $stockId,
        'type' => $type,
      ])->setMethod('api/v1/stock/historyMonths')->getResult();

      return view('table/index', ['result' => $result, 'name' => $name, 'type' => $type, 'months' => $months, 'stock' => $stock]);
    } catch (\Exception $e) {
      return redirect()->to('stock');
    }

    return view('table/index', ['result' => $result, 'name' => $name ?? "", 'type' => 'ordini']);
  }

  public function tableData()
  {
    $type = $this->request->getGet('type') ?? 'ordini';
    $stock = session('stock');
    if (is_null($stock)) {
      return $this->response->setStatusCode(403)->setBody('Нет доступа');
    }

    $id = $stock['id'];

    $result = service('ApiHelper')->setParams([
      'id' => $id
    ])->setMethod("api/v1/stock/get" . ucfirst($type))->getResult();

    $data = [
      'result' => $result,
      'type'   => $type,
    ];

    return view("table/partial_$type", $data);
  }
}
