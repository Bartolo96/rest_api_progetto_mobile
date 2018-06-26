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
    
    if ($request->hasHeader('Auth-Token')){
        $token = implode($request->getHeader('Auth-Token'),'');
        if(is_valid_token($token)){
            $response = $next($request,$response);
        }
        else
            $response->getBody()->write('{"Error":"Invalid token provided"}');   
    }
    else
        $response->getBody()->write('{"Error":"No token provided"}');  
    
    return $response;
};

//Routes
require '../src/routes/auth.php';
require '../src/routes/users.php';
require '../src/routes/products.php';

$app->run();