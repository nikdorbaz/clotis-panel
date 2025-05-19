<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        // Если уже есть активная сессия пользователя, перенаправляем
        if (session('stock')) {
            return redirect()->to('stock');
        }

        return view('auth'); // форма авторизации
    }

    public function auth()
    {
        try {
            $params = $this->request->getPost();

            $response = service('ApiHelper')
                ->setParams($params)
                ->setMethod('api/v1/auth')
                ->getResult();

            if (!empty($response['result'])) {
                // Пример: сохраняем в сессии stock ID (или другой нужный объект)
                session()->set('stock', $response['result']);

                // Можно редиректить на конкретный склад, если нужно
                return redirect()->to('stock/' . $response['result']['id']);
            }

            // Если result пустой — авторизация не удалась
            return redirect()->back()->with('error', 'Неверный логин или пароль');
        } catch (\Throwable $e) {
            log_message('error', 'Auth error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Произошла ошибка при входе');
        }
    }
}
