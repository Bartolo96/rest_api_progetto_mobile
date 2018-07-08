<?php
    use \Firebase\JWT\JWT;
    
    function generate_jwt_token($payload,$tokenType){
        if($tokenType === REFRESH_TOKEN_TYPE)
            $payload = array_merge($payload,['iss'=>'nitwx','iat'=>time(),'scope' =>'refresh']);
        else
            $payload = array_merge($payload,['iss'=>'nitwx','iat'=>time(),'scope' =>'resources','exp'=>time()+ACCESS_TOKEN_DURATION]);
        $jwt = JWT::encode($payload, PRIVATE_KEY,'RS256');
        return $jwt;
    }
    function decode_jwt_token($token){
        JWT::$leeway = 30;
        $decoded = JWT::decode($token,PUBLIC_KEY, array('RS256'));
        return $decoded;
    }