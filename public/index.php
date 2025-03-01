<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];

$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    return $response->write('Welcome to Slim!');
});

$app->post('/test', function ($request, $response) {
    return $response->withStatus(302);
});

$app->get('/users/{id}', function ($request, $response, $args) {
    $params = ['id' => $args['id'], 'nickname' => 'user-' . $args['id']];
    // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
    // $this доступен внутри анонимной функции благодаря https://php.net/manual/ru/closure.bindto.php
    // $this в Slim это контейнер зависимостей
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

$app->get('/users', function ($request, $response) use ($users) {
    $term = $request->getQueryParam('term');
    if ($term == '') {
        $filteredUsers = $users;
        $value = "";
    } else {
        $filteredUsers = [];
        foreach ($users as $user) {
            if (str_contains($user, strtolower($term))) {
                $filteredUsers[] = $user;
            }
        }
        $value = $term;
    }

    if (count($filteredUsers) == 0) {
        $filteredUsers = ["Ничего не найдено"];
    } else {
        sort($filteredUsers);
    }
    $params = ['users' => $filteredUsers, 'value' => $value];
    return $this->get('renderer')->render($response, 'search/index.phtml', $params);
})->setName('users');

//$repo = new App\UserRepository();

$app->get('/users/new/', function ($request, $response) {
    $params = [
        'user' => ['name' => '', 'email' => ''],
        'errors' => []
    ];
    return $this->get('renderer')->render($response, "users/new.phtml", $params);
});
$router = $app->getRouteCollector()->getRouteParser();
$app->post('/users', function ($request, $response) use ($router) {
    $validator = new App\Validator();
    $user = $request->getParsedBodyParam('user');
    $errors = $validator->validate($user);
    $params = [
        'user' => $user,
        'errors' => $errors
    ];
    if (count($errors) === 0) {
        //$repo->save($user);
        return $response->withRedirect($router->urlFor('users'), 302);
    }
    return $this->get('renderer')->render($response->withStatus(422), "users/new.phtml", $params);
});

$app->run();
