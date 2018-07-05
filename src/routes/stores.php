<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Get data reagrding product corresopnding to code sent 
*/

$app->get('/api/offers', function (Request $request, Response $response) {
    $responseBody=$response->getBody();
    $sqlOffers = 'SELECT * FROM stores';

    
    try {
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();
        $stmt = $db->prepare($sqlOffers);
        
        if($stmt->execute()){
            $stores = $stmt->fetchAll(PDO::FETCH_OBJ);
            $responseBody->write(json_encode($stores));
            
        }   
    }catch(PDOException $e){
        $responseBody->write(json_encode(['error'=>$e->getMessage()]));
    }
})->add($resource_middleware_get);;
