<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (session('user')) {
            return redirect()->to('stock');
        }

        return view('auth');
    }

    public function auth()
    {
        try {
            $result = service('ApiHelper')->setParams($this->request->getPost())
                ->setMethod('api/v1/auth')
                ->getResult();
            dd($result);
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }


        return redirect()->to('stock/64');
    }
}
