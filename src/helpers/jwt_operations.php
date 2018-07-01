<?php
    use \Firebase\JWT\JWT;
    
    function generate_jwt_token($payload,$tokenType){
        if($tokenType == REFRESH_TOKEN_TYPE)
            $payload = array_merge($payload,['iss'=>'nitwx','iat'=>time(), 'token_type' => 'Bearer']);
        else if($tokenType == ACCESS_TOKEN_TYPE)
            $payload = array_merge($payload,['iss'=>'nitwx','iat'=>time(),'exp'=>time()+20, 'token_type' => 'Bearer']);
        $jwt = JWT::encode($payload, PRIVATE_KEY,'RS256');
        return $jwt;
    }
    function decode_jwt_token($token){
        JWT::$leeway = 30;
        $decoded = JWT::decode($token,PUBLIC_KEY, array('RS256'));
        return $decoded;
    }