<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/api/add_offer_transaction/', function (Request $request, Response $response) {
    $timestamp = time();
    $id = $request->getParam('id');
    $offer_id = $reuqest->getParam('offer_id');
    $sql = 'INSERT INTO transactions VALUES(:offer_id, :user_id, :timestamp)';
    $sq2 = 'SELECT id FROM transactions VALUES(:offer_id, :user_id, :timestamp)';
    try{
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':offer_id',$offer_id);
        $stmt->bindParam(':user_id',$id);
        $stmt->bindParam(':timestamp',$timestamp);

        if($stmt->execute()){
            $product = $stmt->fetchAll(PDO::FETCH_OBJ);
            if(count($product)==1)
                echo json_encode($product[0]);
            else 
                echo '{"error" : {"code" : 111}}';
        }
    }catch(PDOException $e){
        echo '{"error" : {"text" : '. $e->getMessage().'}';
    }
})->add($resource_middleware_post);