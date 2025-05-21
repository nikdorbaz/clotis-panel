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
        ])->setMethod('api/v1/stock/getSpedizione')->getResult();
      } elseif ($type == 'ordini') {
        $result = service('ApiHelper')->setParams([
          'id' => $id
        ])->setMethod('api/v1/stock/getOrdini')->getResult();
      }

      timer()->stop('apiRequest');
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('table/index', ['result' => $result, 'error' => $error, 'name' => $name, 'type' => $type]);
  }

  public function history($month)
  {
    try {
      $backupContent = file_get_contents(WRITEPATH . "backups/$month-ordini.json");
      $result = json_decode($backupContent, true);
    } catch (\Exception $e) {
      return redirect()->to('stock');
    }

    return view('table/index', ['result' => $result, 'name' => $name ?? "", 'type' => 'ordini']);
  }
}
