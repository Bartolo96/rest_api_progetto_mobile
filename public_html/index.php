<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\ExpiredException;
use \Firebase\JWT\SignatureInvalidException;
use \Firebase\JWT\JWT;

require_once '../vendor/autoload.php';
require_once '../vendor/facebook/graph-sdk/src/Facebook/autoload.php';
require '../src/config/constants.php';
require '../src/helpers/jwt_operations.php';
require '../src/config/db.php';
require '../src/helpers/sql_operations.php';

$app = new Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'debug'               => true,
        'whoops.editor'       => 'sublime',
    ]
]);

//Middleware for functions after user is authenticated

$middleware = function ($request, $response, $next) {
    if($request->hasHeader('Authorization')){
        $token = preg_split('/ /',implode($request->getHeader('Authorization')))[1];
        if($token!=null){
            try{
                $decode_token = decode_jwt_token($token);
                $request = $request->withParsedBody($decode_token);
                $response = $next($request, $response);
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withStatus(202,'OK');

            }catch(ExpiredException|SignatureInvalidException $e){
                $response = $response->withHeader('WWW-Authenticate','http://nitwx.000webhostapp.com');
                $response = $response->withStatus(401,'Unauthorized');
            }
            /* catch(SignatureInvalidException $e){
                $new_response = $response->withHeader('','http://nitwx.000webhostapp.com');
                $new_response->withStatus(401);
            } */
        }
        else{
            $response = $response->withHeader('WWW-Authenticate','http://nitwx.000webhostapp.com');
            $response = $response->withStatus(400,'Bad Request');
        }
    }
    else{
        $response = $response->withHeader('WWW-Authenticate','http://nitwx.000webhostapp.com');
        $response = $response->withStatus(400,'Bad Request');
    }
    return $response;
};

//Middleware
$old_middleware = function ($request,$response,$next){
    
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
        $response->getBody()->write('{"Error":"You are not authorized"}');
    $new_response = $response->withHeader('Content-type', 'application/json');
    return $new_response;
};

//Routes
require '../src/routes/auth.php';
require '../src/routes/users.php';
require '../src/routes/products.php';

$app->run();