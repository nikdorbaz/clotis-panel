<?php

namespace App\Controllers;

class Manager extends BaseController
{
  public function index()
  {
    $manager = session('manager');

    if (is_null($manager)) {
      return redirect()->to('/');
    }

    $result = null;
    $error = null;

    $id = $manager['id'];
    $name = $manager['name'];
    try {
      $result = service('ApiHelper')->setParams([
        'id' => $id
      ])->setMethod('api/v1/sales/get')->getResult();
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('manager/index', ['result' => $result, 'error' => $error, 'name' => $name]);
  }

  public function monthly()
  {
    $manager = session('manager');

    if (is_null($manager)) {
      return redirect()->to('/');
    }

    $name = $manager['name'];

    return view('manager/monthly', ['name' => $name]);
  }

  public function difference()
  {
    $manager = session('manager');

    if (is_null($manager)) {
      return redirect()->to('/');
    }

    $result = null;
    $error = null;

    $id = $manager['id'];
    $name = $manager['name'];
    $stockID = $this->request->getGet('stock_id') ?? 0;

    $api = [];
    try {
      $result = service('ApiHelper')->setParams([
        'id' => $id,
        'stock_id' => $stockID,
      ])->setMethod('api/v1/sales/getDifference')->getResult();


      $api = [
        'difference' => 'https://clotiss.site/api/v1/sales/getDifference'
      ];
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('manager/difference', ['result' => $result, 'error' => $error, 'name' => $name, 'stock_id' => $stockID, 'api' => $api]);
  }
}
