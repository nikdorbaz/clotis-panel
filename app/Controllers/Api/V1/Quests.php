<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\ApiRouter;
use App\Controllers\Base;
use App\Libraries\ChatGpt;
use CodeIgniter\Controller;

class Quests extends ApiRouter
{
  public function index()
  {
    $quests = model('Quests')->find();
    return $this->respond($quests);
  }

  public function single()
  {
    $quest = model('Quests')->getQuiz($this->request->getGet('slug'));

    return $this->respond($quest);
  }

  public function submit()
  {
    return $this->respond($this->request->getPost());
  }

  public function upload() {}
}
