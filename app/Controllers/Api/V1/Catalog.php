<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\ApiRouter;

class Catalog extends ApiRouter
{
  public function index()
  {
    $catalogs = model('Catalog')->join('files', 'files.model_id = catalog.id')
      ->where('files.model', 'catalog')
      ->findAll();


    return $this->respond($catalogs);
  }

  public function putUpdate()
  {
    return $this->respond(['PUT update']);
  }

  public function deleteDelete()
  {
    return $this->respond(['DELETE delete']);
  }

  public function auth()
  {
    return $this->respond(['auth', file_get_contents('php://input')]);
  }
}
