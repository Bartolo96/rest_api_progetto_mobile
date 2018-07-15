<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/api/transactions/add_transaction', function (Request $request, Response $response) {
    $timestamp = time();
    $user_id = $request->getParam('id');
    $user_points = $request->getParam('points');
    $user_gender = $request->getParam('gender');
    $user_birth_date = $request->getParam('birth_date');
    $user_email = $request->getParam('email');
    $user_type = $request->getParam('user_type');
    $user_last_time_played = $request->getParam('last_time_played');

    $offer_id = $request->getParam('offer_id');
    $sql = 'SELECT validity_end,points_cost FROM offers WHERE id = :offer_id';
    $sql2 = 'INSERT INTO transactions(`offer_id`, `user_id`, `timestamp`) VALUES(:offer_id, :user_id, :timestamp)';
    $sql3 = 'SELECT `id` FROM transactions WHERE `offer_id` = :offer_id AND `user_id` = :user_id AND `timestamp` = :timestamp';
    $sql4 = 'UPDATE users SET points = :points WHERE id = :id';

    try{
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':offer_id',$offer_id);

        if($stmt->execute()){
            $offers = $stmt->fetchAll(PDO::FETCH_OBJ);
            if(count($offers) == 1){
                if($offers[0]->points_cost <= $user_points){
                    $stmt = $db->prepare($sql2);
                    $stmt->bindParam(':offer_id',$offer_id);
                    $stmt->bindParam(':user_id',$user_id);
                    $stmt->bindParam(':timestamp',$timestamp);
        
                    if($stmt->execute()){
                        $stmt = $db->prepare($sql3);
                        $stmt->bindParam(':offer_id',$offer_id);
                        $stmt->bindParam(':user_id',$user_id);
                        $stmt->bindParam(':timestamp',$timestamp);
                        
                        if($stmt->execute()){
                            $transactions = $stmt->fetchAll(PDO::FETCH_OBJ);
                            
                            if(count($transactions) == 1){
                                $newPoints = $user_points - $offers[0]->points_cost;
                                $stmt = $db->prepare($sql4);
                                $stmt->bindParam('points',$newPoints);
                                $stmt->bindParam('id',$user_id);
                                if($stmt->execute()){
                           
                                    $signedAccessToken = generate_jwt_token(['id'=>$user_id,
                                                                'user_type'=>$user_type,
                                                                'points'=>$newPoints,
                                                                'last_time_played' => $user_last_time_played,
                                                                'gender'=>$user_gender,
                                                                'birth_date'=>$user_birth_date,
                                                                'email'=>$user_email],
                                                                ACCESS_TOKEN_TYPE);
                                    $response->getBody()->write(json_encode(['transaction_token' => generate_jwt_token([ 'id' => $transactions[0]->id, 
                                                                                                                        'offer_id' => $offer_id,
                                                                                                                        'user_id' => $user_id,
                                                                                                                        'exp' => $offers[0]->validity_end],
                                                                                                                        TRANSACTION_TOKEN_TYPE),
                                                                            ACCESS_TOKEN =>[ACCESS_TOKEN => $signedAccessToken,'token_type'=>'Bearer']]));
                            
                                }
                            }
                            else 
                                echo '{"error" : {"code" : 111}}';
                        }
             
                    }
                } else{
                    $response = $response->withHeader('WWW-Authenticate','http://nitwx.000webhostapp.com');
                    $response = $response->withStatus(421,"Not Enough Points");
                }
                    
            }

        }
    
    }catch(PDOException $e){
         echo '{"error" : {"text" : '. $e->getMessage().'}}';
    }
    return $response;
})->add($resource_midlleware_post);

$app->post('/api/transactions/check_transaction_availability', function (Request $request, Response $response) {
    $timestamp = time();
    $user_id = $request->getParam('id');
    $offer_id = $request->getParam('offer_id');
    $sql = 'SELECT id FROM transactions WHERE `offer_id` = :offer_id AND `user_id` = :user_id';
    $sql2 = 'SELECT validity_end FROM offers WHERE id = :offer_id';
    
    //return $user_id." ".$offer_id;
    try{
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':offer_id',$offer_id);
        $stmt->bindParam(':user_id',$user_id);
        
        if($stmt->execute()){
            
            $transactions = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            if(count($transactions) == 1){
                $stmt = $db->prepare($sql2);
                $stmt->bindParam(':offer_id',$offer_id);
                if($stmt->execute()){
                    $offers = $stmt->fetchAll(PDO::FETCH_OBJ);
                    if(count($offers) == 1){
                        
                        $response->getBody()->write(json_encode(['transaction_token' => generate_jwt_token(['id' => $transactions[0]->id, 
                                                                                                            'offer_id' => $offer_id,
                                                                                                            'user_id' => $user_id,
                                                                                                            'exp' => $offers[0]->validity_end],
                                                                                                            TRANSACTION_TOKEN_TYPE)]));
                    }
                }
            }else{
                $response = $response->withHeader('WWW-Authenticate','http://nitwx.000webhostapp.com');
                $response = $response->withStatus(420,"No Transaction Found");

            }
        }else
            return $user_id." ".$offer_id;
    }catch(PDOException $e){
         echo '{"error" : {"text" : '. $e->getMessage().'}}';
    }
    return $response;
})->add($resource_midlleware_post);