<?php

namespace App\Commands;

use App\Controllers\Api\Widget as ApiWidget;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Cron extends BaseCommand
{
  protected $group       = 'Cron';
  protected $name        = 'cron';
  protected $description = 'Run this by server';

  /**
   * Options
   *
   * @var array
   */
  protected $options = [
    '-method' => 'The cron name [default: "8080"]',
  ];


  public function run(array $params)
  {
    helper('filesystem');
    try {
      /** @var \App\Libraries\ApiHelper $apiHelper */
      $apiHelper = service('apiHelper');

      $spedizioneData = $apiHelper->setParams([
        'id' => 64
      ])->setMethod('api/v1/stock/getSpedizione')->getResultOriginal();


      $ordiniData = $apiHelper->setParams([
        'id' => 64
      ])->setMethod('api/v1/stock/getOrdini')->getResultOriginal();

      $month = date('m');
      $filePathSpedizione = "backups/$month-spedizione.json";
      $filePathOrdini = "backups/$month-ordini.json";

      write_file(WRITEPATH . $filePathSpedizione, $spedizioneData);
      write_file(WRITEPATH . $filePathOrdini, $ordiniData);
    } catch (\Throwable $e) {
      var_dump($e->getMessage());
    }
  }
}
