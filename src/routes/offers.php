<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Get data reagrding product corresopnding to code sent 
*/

$app->get('/api/offers', function (Request $request, Response $response) {
    $responseBody=$response->getBody();
    $sqlOffers = 'SELECT * FROM offers';
    $sqlProducts = 'SELECT products.id,products.name,products.description,products.price,products.code,quantity FROM 
                            (offers_products JOIN products ON offers_products.product_id = products.id) 
                            WHERE offers_products.offer_id = :id';

    
    try {
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();
        $stmt = $db->prepare($sqlOffers);
        
        if($stmt->execute()){
            $offers = $stmt->fetchAll(PDO::FETCH_OBJ);
            foreach($offers as $offer){
                $stmt = $db->prepare($sqlProducts);
                $stmt->bindParam('id',$offer->id);
                if($stmt->execute()){
                    $offer->product_list =  $stmt->fetchAll(PDO::FETCH_OBJ);
                }
            }
            
        }   
        
        $responseBody->write(json_encode($offers));
    }catch(PDOException $e){
        $responseBody->write(json_encode(['error'=>$e->getMessage()]));
    }
})->add($resource_middleware_get);;

