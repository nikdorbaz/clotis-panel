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

      $months = service('ApiHelper')->setParams([
        'id' => $id,
        'type' => 'payments',
      ])->setMethod('api/v1/sales/historyMonths')->getResult();
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('manager/index', ['result' => $result, 'error' => $error, 'name' => $name, 'months' => $months, 'currentMonth' => '', 'type' => 'payments']);
  }

  public function history($month)
  {
    try {
      $manager = session('manager');

      if (is_null($manager)) {
        return redirect()->to('/');
      }

      $id = $manager['id'];
      $name = $manager['name'];
      $error = null;

      // Тип можно передавать через GET ?type=ordini, по умолчанию 'ordini'
      $type = $this->request->getGet('type') ?? 'difference';

      $result = service('ApiHelper')->setParams([
        'type'  => $type,
        'month' => $month
      ])->setMethod('api/v1/sales/getHistory')->getResult();

      $months = service('ApiHelper')->setParams([
        'type' => $type,
      ])->setMethod('api/v1/sales/historyMonths')->getResult();


      if ($type == 'payments') {
        return view('manager/index', ['result' => $result, 'error' => $error, 'name' => $name, 'months' => $months, 'currentMonth' => $month, 'type' => 'payments']);
      } else {
        return view('manager/difference', [
          'result' => $result,
          'error' => $error,
          'name' => $name,
          'stock_id' => $stockID ?? 0,
          'months' => $months,
          'currentMonth' => '',
          'type' => 'difference'
        ]);
      }
    } catch (\Exception $e) {
      dd($e->getMessage());
      return redirect()->to('manager');
    }

    return view('manager/index', ['result' => $result, 'name' => $name ?? "", 'type' => 'ordini']);
  }

  public function monthly()
  {
    $manager = session('manager');

    if (is_null($manager)) {
      return redirect()->to('/');
    }

    $name = $manager['name'];
    $error = null;
    $result = null;
    $id = $manager['id'];
    $name = $manager['name'];
    $stockID = $this->request->getGet('stock_id') ?? 0;

    try {
      $result = service('ApiHelper')->setParams([
        'id' => $id,
        'stock_id' => $stockID,
      ])->setMethod('api/v1/sales/getByMonth')->getResult();

      $months = service('ApiHelper')->setParams([
        'id' => $id,
        'type' => 'payments',
      ])->setMethod('api/v1/sales/historyMonths')->getResult();
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('manager/monthly', ['name' => $name, 'error' => $error, 'result' => $result, 'months' => $months, 'currentMonth' => '', 'type' => '']);
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

      $months = service('ApiHelper')->setParams([
        'id' => $id,
        'type' => 'payments',
      ])->setMethod('api/v1/sales/historyMonths')->getResult();

      $api = [
        'difference' => 'https://clotiss.site/api/v1/sales/getDifference'
      ];
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('manager/difference', [
      'result' => $result,
      'error' => $error,
      'name' => $name,
      'stock_id' => $stockID,
      'months' => $months,
      'currentMonth' => '',
      'api' => $api,
      'type' => 'difference'
    ]);
  }
}
