<?php

namespace App\Controllers;

class Manager extends BaseController
{
  public function payments()
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
      ])->setMethod('api/v1/sales/getPayments')->getResult();

      $campaigns = service('ApiHelper')->setMethod('api/v1/campaign/get')->getResult();
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('manager/index', ['result' => $result, 'error' => $error, 'name' => $name, 'campaigns' => $campaigns, 'campaign' => '', 'type' => 'payments']);
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

      $campaigns = service('ApiHelper')->setMethod('api/v1/campaign/get')->getResult();
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('manager/monthly', ['name' => $name, 'error' => $error, 'result' => $result, 'campaigns' => $campaigns, 'campaign' => '', 'type' => 'monthly']);
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

      $campaigns = service('ApiHelper')->setMethod('api/v1/campaign/get')->getResult();

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
      'campaigns' => $campaigns,
      'campaign' => '',
      'api' => $api,
      'type' => 'difference'
    ]);
  }

  public function paymentsByCampaign(int $campaign)
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
        'id' => $id,
        'campaign' => $campaign
      ])->setMethod('api/v1/sales/getPaymentsByCampaign')->getResult();

      $campaigns = service('ApiHelper')->setMethod('api/v1/campaign/get')->getResult();
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('manager/index', ['result' => $result, 'error' => $error, 'name' => $name, 'campaigns' => $campaigns, 'campaign' => $campaign, 'type' => 'payments']);
  }

  public function differenceByCampaign(int $campaign)
  {
    $manager = session('manager');

    if (is_null($manager)) {
      return redirect()->to('/');
    }

    $result = null;
    $error = null;
    $stockID = $this->request->getGet('stock_id') ?? 0;

    $id = $manager['id'];
    $name = $manager['name'];
    try {
      $result = service('ApiHelper')->setParams([
        'id' => $id,
        'stock_id' => $stockID,
        'campaign' => $campaign,
      ])->setMethod('api/v1/sales/getDifferenceByCampaign')->getResult();

      $campaigns = service('ApiHelper')->setMethod('api/v1/campaign/get')->getResult();
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('manager/difference', [
      'result' => $result,
      'error' => $error,
      'name' => $name,
      'stock_id' => $stockID,
      'campaigns' => $campaigns,
      'campaign' => $campaign,
      'type' => 'difference'
    ]);
  }
}
