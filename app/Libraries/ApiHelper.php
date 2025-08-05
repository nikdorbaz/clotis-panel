<?php

namespace App\Libraries;

use CodeIgniter\Config\Services;
use Exception;

class ApiHelper
{
  protected $url = "https://clotpanel.kebeta.agency/";
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

    if ($response->getStatusCode() == 500) {
      throw new Exception("Doesn't work");
    }

    return json_decode($response->getBody() ?? "", true);
  }

  public function getResultOriginal()
  {
    $response = $this->client->post($this->method);

    return $response->getBody();
  }
}
