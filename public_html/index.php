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

$resource_middleware_get = function ($request, $response, $next) {
    if($request->hasHeader('Authorization')){
        $token = preg_split('/ /',implode($request->getHeader('Authorization')))[1];
        if($token!=null){
            try{
                $decode_token = decode_jwt_token($token);
                $request = $request->withParsedBody($decode_token);
                if($request->getParam('scope') === 'resources'){
                    $response = $next($request, $response);
                    $response = $response->withHeader('Content-type', 'application/json');
                }
                else {
                    $response = $response->withHeader('WWW-Authenticate','http://nitwx.000webhostapp.com');
                    $response = $response->withStatus(401,'Unauthorized');
                }
            }catch(ExpiredException|SignatureInvalidException|UnexpectedValueException $e){
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

$resource_midlleware_post = function($request, $response, $next){
    if($request->hasHeader('Authorization')){
        $token = preg_split('/ /',implode($request->getHeader('Authorization')))[1];
        if($token!=null){
            try{
                $decode_token = decode_jwt_token($token);
                $requestBody = array_merge($request->getParsedBody(),(array)$decode_token);
                $request = $request->withParsedBody($requestBody);

                if($request->getParam('scope') === 'resources'){
                    $response = $next($request, $response);
                    $response = $response->withHeader('Content-type', 'application/json');
                }
                else {
                    $response = $response->withHeader('WWW-Authenticate','http://nitwx.000webhostapp.com');
                    $response = $response->withStatus(401,'Unauthorized');
                }
            }catch(ExpiredException|SignatureInvalidException|UnexpectedValueException $e){
                $response = $response->withHeader('WWW-Authenticate','http://nitwx.000webhostapp.com');
                $response = $response->withStatus(401,'Unauthorized');
                
            }
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
$refresh_middleware = function ($request,$response,$next){
    if($request->hasHeader('Authorization')){
        $token = preg_split('/ /',implode($request->getHeader('Authorization')))[1];
        if($token!=null){
            try{
                $decode_token = decode_jwt_token($token);
                $request = $request->withParsedBody($decode_token);
                if($request->getParam('scope') === 'refresh'){
                    $response = $next($request, $response);
                    $response = $response->withHeader('Content-type', 'application/json');
                }
                else {
                    $response = $response->withHeader('WWW-Authenticate','http://nitwx.000webhostapp.com');
                    $response = $response->withStatus(401,'Unauthorized');
                }
            }catch(ExpiredException|SignatureInvalidException|UnexpectedValueException $e){
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


//Routes
require '../src/routes/transactions.php';
require '../src/routes/stores.php';
require '../src/routes/auth.php';
require '../src/routes/offers.php';
require '../src/routes/users.php';
require '../src/routes/products.php';

$app->run();