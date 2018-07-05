<?php

function generate_refresh_token($key_name,$key_value,$user_type){
    $sql = 'UPDATE users SET refresh_token =:token WHERE '.$key_name.' = :key_value AND user_type = :user_type';
    try{
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $token = bin2hex(random_bytes(32));
        $stmt->bindParam(':token',$token);
        $stmt->bindParam(':key_value',$key_value);
        $stmt->bindParam(':user_type',$user_type);
        
        if ($stmt->execute()) {
            return $token;
        }
        else
            return false;
    }catch(PDOException $e){
        return '{"error" : {"text" : '. $e->getMessage().'}';
    }    
};
function authenticate_third_party_users($email,$user_type){
    //Check if given user already exists
    $sql = 'SELECT * FROM users WHERE email = :email AND user_type = :user_type';
    $sql2 = 'INSERT INTO users(email,user_type) VALUES(:email,:user_type)';
    $db = new db();
    $db = $db->connect();
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email',$email);
    $stmt->bindParam(':user_type',$user_type);
    if($stmt->execute()){
        $user = $stmt->fetchAll(PDO::FETCH_OBJ);
        //If it does not exist create the user
        if(count($user)==0){
            //Get DB object 
            $db = new db();
            //Connect 
            $db = $db->connect();
            $stmt = $db->prepare($sql2);
            $stmt->bindParam(':email',$email);
            $stmt->bindParam(':user_type',$user_type);
            if($stmt->execute()){
            
            }
        }
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email',$email);
        $stmt->bindParam(':user_type',$user_type);
        //Generate Tokens 
        if($stmt->execute()){
            $user = $stmt->fetchAll(PDO::FETCH_OBJ);
            $signedAccessToken = generate_jwt_token(['id'=>$user[0]->id,
                                                    'user_type'=>$user[0]->user_type,
                                                    'points'=>$user[0]->points,
                                                    'gender'=>$user[0]->gender,
                                                    'birth_date'=>$user[0]->birth_date,
                                                    'email'=>$user[0]->email],
                                                    ACCESS_TOKEN_TYPE);
            $refreshToken = generate_refresh_token('email',$email,$user[0]->user_type);

            $signedRefreshToken = generate_jwt_token(['id'=>$user[0]->id,
                                                    'token'=>$refreshToken,
                                                    'user_typee'=>$user[0]->user_type]
                                                    ,  REFRESH_TOKEN_TYPE);
            
            return json_encode([ACCESS_TOKEN => [ACCESS_TOKEN=>$signedAccessToken,'token_type' => 'Bearer','expires_in' => 3600],
                                REFRESH_TOKEN => [REFRESH_TOKEN =>$signedRefreshToken, 'token_type' => 'Bearer']]);
         
        }
        else
            return false;
        
    }
};