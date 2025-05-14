<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $result = null;
        $error = null;

        try {
            $url = 'https://clotiss.site/api/v1/sales/get';

            $response = file_get_contents($url);

            if ($response === false) {
                throw new \Exception('Ошибка при выполнении GET-запроса.');
            }

            $result = json_decode($response, true);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return view('welcome_message', ['result' => $result, 'error' => $error]);
    }
}
