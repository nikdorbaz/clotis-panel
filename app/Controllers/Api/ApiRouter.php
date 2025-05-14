<?php

namespace App\Controllers\Api;

use CodeIgniter\Config\Services;
use CodeIgniter\Controller;

class ApiRouter extends Controller
{
    /**
     * @var \CodeIgniter\HTTP\IncomingRequest
     */
    protected $request;

    public function route($version = 'v1', $controller = null, $method = 'index')
    {
        $response = service('response');

        $namespace = "App\\Controllers\\Api\\" . ucfirst($version);
        $className = ucfirst($controller);
        $fullClass = $namespace . '\\' . $className;

        if (!class_exists($fullClass)) {
            return $response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => "Контроллер '{$className}' не найден в версии '{$version}'"
            ]);
        }

        $controllerInstance = new $fullClass();
        $controllerInstance->request = service('request');

        // Определяем HTTP-метод (GET, POST, PUT, DELETE)
        $httpMethod = $this->request->getMethod(true); // true = ucfirst()

        // Метод с префиксом: getLogin, postAuth, etc.
        $methodWithHttp = strtolower($httpMethod) . ucfirst($method);

        // Безопасность: не позволяем вызывать __магические методы
        if (strpos($methodWithHttp, '__') === 0 || strpos($method, '__') === 0) {
            return $response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => "Метод '{$method}' запрещён"
            ]);
        }

        // Вариант 1: HTTP-специфичный метод
        if (is_callable([$controllerInstance, $methodWithHttp])) {
            return $controllerInstance->$methodWithHttp();
        }

        // Вариант 2: обычный метод
        if (is_callable([$controllerInstance, $method])) {
            return $controllerInstance->$method();
        }

        return $response->setStatusCode(404)->setJSON([
            'status' => 'error',
            'message' => "Метод '{$method}' не найден в контроллере '{$className}'"
        ]);
    }

    protected function respond(array $data)
    {
        $response = service('response');

        return $response->setStatusCode(200)->setJSON($data);
    }
}
