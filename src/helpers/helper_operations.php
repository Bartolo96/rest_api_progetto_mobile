<?php
function is_valid_token ($token){
    if(is_existent_token($token)){
        $sql = 'SELECT auth_token_timestamp, validity FROM users WHERE auth_token = :token';
        try{
            
            //Get DB object 
            $db = new db();
            //Connect 
            $db = $db->connect();
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':token',$token);
            if($stmt->execute()){
                
                $token = $stmt->fetchAll(PDO::FETCH_OBJ);
                $timestamp = time();
                if(($timestamp - $token[0]->auth_token_timestamp) >= $token[0]->validity){
                    return false;
                }
                else
                    return true;
            }
           
        }catch(PDOException $e){
            return '{"error" : {"text" : '. $e->getMessage().'}';
        }
    }
    else
        return false;
};

function is_existent_token ($token){
    $sql = 'SELECT * FROM users WHERE auth_token = :token';
    try{
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':token',$token);
        
        if($stmt->execute()){
            $user = $stmt->fetchAll(PDO::FETCH_OBJ);
            if(count($user)==1)
                return true;
            else 
                return false;
        }
    }catch(PDOException $e){
        return '{"error" : {"text" : '. $e->getMessage().'}';
    }
};

function generate_auth_token($key_name,$key_value,$user_type){
    $sql = 'UPDATE users SET auth_token = :token, auth_token_timestamp = :timestamp, validity = :validity  
                WHERE '.$key_name.' = :key_value AND user_type = :user_type';
    try{
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $token = bin2hex(random_bytes(16));
        $validity = 3600;
        while(is_existent_token($token)){
            $token = bin2hex(random_bytes(16));    
        }
        $time = time();
        $stmt->bindParam(':token',$token);
        $stmt->bindParam(':key_value',$key_value);
        $stmt->bindParam(':user_type',$user_type);
        $stmt->bindParam(':validity',$validity);
        $stmt->bindParam(':timestamp',$time);
        if ($stmt->execute()) {
            return $token;
        }
        else
            return false;
               
    }catch(PDOException $e){
        return '{"error" : {"text" : '. $e->getMessage().'}';
    }    
};

function generate_refresh_token($key_name,$key_value,$user_type = 1 ){
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
    $sql = 'SELECT id FROM users WHERE email = :email AND user_type = :user_type';
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
        //Generate Tokens 
        $auth_token = generate_auth_token("email",$email,$user_type);
        if($auth_token != false){
            $refresh_token = generate_refresh_token("email",$email,$user_type);
            return '{"authtoken":"'.$auth_token.'","refreshtoken":"'.$refresh_token.'"}';
        }  
    }
};