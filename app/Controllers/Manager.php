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

      // $result = service('ApiHelper')->setParams([
      //   'id' => $id
      // ])->setMethod('api/v1/stock/getSpedizione')->getResult();
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('manager/index', ['result' => $result, 'error' => $error, 'name' => $name]);
  }
}
