<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../src/config/db.php';
require '../src/helpers/token_operations.php';

$app = new Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'debug'               => true,
        'whoops.editor'       => 'sublime',
    ]
]);

//Middleware
$middleware = function ($request,$response,$next){
    if ($request->hasHeader('auth_token') && is_token_valid($request->getHeader('auth_token')[0])) {
        $response = $next($request,$response);
    }
    else
        $response->getBody()->write('{"Error":"Invalid token provided"}');   
    return $response;
};

//Routes
require '../src/routes/auth.php';
require '../src/routes/users.php';
require '../src/routes/products.php';

$app->run();