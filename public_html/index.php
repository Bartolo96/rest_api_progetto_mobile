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
    
    if ($request->hasHeader('authtoken')){
        $token = implode($request->getHeader('authtoken'),'');
        if(is_valid_token($token)){
            $response = $next($request,$response);
        }
        else
            $response->getBody()->write('{"Error":"Invalid token provided '.$token.'"}');   
    }
    else
        $response->getBody()->write('{"Error":"No token provided"}');  
    
    $response = $response->withHeader('Content-type', 'application/json');
    return $response;
};

//Routes
require '../src/routes/auth.php';
require '../src/routes/users.php';
require '../src/routes/products.php';

$app->run();