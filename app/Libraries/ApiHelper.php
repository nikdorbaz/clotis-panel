<?php

namespace App\Libraries;

use CodeIgniter\Config\Services;

class ApiHelper
{
  protected $url = "https://clotiss.site/";
  protected $client;
  protected $method;

  public function __construct()
  {
    $this->client = Services::curlrequest([
      'baseURI' => $this->url,
      'http_errors' => false,
      'timeout' => 5,
    ]);
  }

  public function setMethod(string $method)
  {
    $this->method = $method;
    return $this;
  }

  public function setParams(array $params)
  {
    $this->client->setForm($params);
    return $this;
  }

  public function getResult()
  {
    $response = $this->client->post($this->method);

    return json_decode($response->getBody() ?? "", true);
  }
}
