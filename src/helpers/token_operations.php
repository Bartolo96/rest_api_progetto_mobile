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
                if(($timestamp - $token->auth_token_timestamp) >= $validity){
                    return false;
                }
                else
                    return true;
            }
           
        }catch(PDOException $e){
            return '{"error" : {"text" : '. $e->getMessage().'}';
        }
    }
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

function generate_auth_token($id){
    $sql = 'UPDATE users SET auth_token = :token, auth_token_timestamp = :timestamp, validity = :validity  WHERE id = :id';
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
        $stmt->bindParam(':token',$token);
        $stmt->bindParam(':id',$id);
        $stmt->bindParam(':validity',$validity);
        return '"auth_token":"'.$token.'", "validity":"'.$validity.'"';        
    }catch(PDOException $e){
        return '{"error" : {"text" : '. $e->getMessage().'}';
    }    
};

function generate_refresh_token($id){
    $sql = 'UPDATE users SET refresh_token = :token WHERE id = :id';
    try{
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $token = bin2hex(random_bytes(32));
        $stmt->bindParam(':token',$token);
        $stmt->bindParam(':id',$id);
        
        if ($stmt->execute()) {
            return $token;
        }
        else
            return false;
    }catch(PDOException $e){
        return '{"error" : {"text" : '. $e->getMessage().'}';
    }    
};