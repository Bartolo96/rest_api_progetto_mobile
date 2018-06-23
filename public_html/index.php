<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../src/config/db.php';

$app = new Slim\App([

    'settings' => [
        'displayErrorDetails' => true,
        'debug'               => true,
        'whoops.editor'       => 'sublime',
    ]

]);
//$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {//
//    $name = $args['name'];
//    $response->getBody()->write("Hello, $name");
//
//    return $response;
//});


//Routes
require '../src/routes/users.php';
require '../src/routes/products.php';

$app->run();