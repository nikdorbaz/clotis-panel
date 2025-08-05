<?php

namespace App\Controllers;

class Stock extends BaseController
{
  public function ordini()
  {
    $stock = session('stock');

    if (is_null($stock)) {
      return redirect()->to('/');
    }

    $result = null;
    $error = null;

    timer('apiRequest');

    $id = $stock['id'];
    $type = 'ordini';

    try {
      $result = service('ApiHelper')->setParams([
        'id' => $id
      ])->setMethod('api/v1/stock/getOrdini')->getResult();

      $campaigns = service('ApiHelper')->setMethod('api/v1/campaign/get')->getResult();

      timer()->stop('apiRequest');
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('table/index', ['result' => $result, 'error' => $error, 'stock' => $stock, 'type' => $type, 'campaigns' => $campaigns, 'campaign' => '']);
  }

  public function spedizione()
  {
    $stock = session('stock');

    if (is_null($stock)) {
      return redirect()->to('/');
    }

    $result = null;
    $error = null;

    timer('apiRequest');

    $id = $stock['id'];
    $type = 'spedizione';

    try {
      $result = service('ApiHelper')->setParams([
        'id' => $id
      ])->setMethod('api/v1/stock/getSpedizione')->getResult();

      $campaigns = service('ApiHelper')->setMethod('api/v1/campaign/get')->getResult();

      timer()->stop('apiRequest');
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('table/index', ['result' => $result, 'error' => $error, 'stock' => $stock, 'type' => $type, 'campaigns' => $campaigns, 'campaign' => '']);
  }

  public function ordiniByCampaign($campaign)
  {
    try {
      $stock = session('stock');
      if (is_null($stock)) {
        return redirect()->to('/');
      }

      $stockId = $stock['id'];
      $name    = $stock['name'];
      $error  = null;

      // Тип можно передавать через GET ?type=ordini, по умолчанию 'ordini'
      $type = 'ordini';

      $result = service('ApiHelper')->setParams([
        'id'    => $stockId,
        'campaign_id' => $campaign,
      ])->setMethod('api/v1/stock/getOrdiniByCampaign')->getResult();

      $campaigns = service('ApiHelper')->setMethod('api/v1/campaign/get')->getResult();
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('table/index', ['result' => $result, 'error' => $error, 'stock' => $stock, 'type' => $type, 'campaigns' => $campaigns, 'campaign' => $campaign]);
  }

  public function spedizioneByCampaign($campaign)
  {
    try {
      $stock = session('stock');
      if (is_null($stock)) {
        return redirect()->to('/');
      }

      $stockId = $stock['id'];
      $name    = $stock['name'];
      $error  = null;

      // Тип можно передавать через GET ?type=ordini, по умолчанию 'ordini'
      $type = 'spedizione';

      $result = service('ApiHelper')->setParams([
        'id'    => $stockId,
        'campaign_id' => $campaign,
      ])->setMethod('api/v1/stock/getSpedizioneByCampaign')->getResult();

      $campaigns = service('ApiHelper')->setMethod('api/v1/campaign/get')->getResult();
    } catch (\Exception $e) {
      $error = $e->getMessage();
    }

    return view('table/index', ['result' => $result, 'error' => $error, 'stock' => $stock, 'type' => $type, 'campaigns' => $campaigns, 'campaign' => $campaign]);
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
