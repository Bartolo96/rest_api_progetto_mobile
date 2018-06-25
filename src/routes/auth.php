<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Authenticate user
 */
$app->post('/auth/authenticate_user', function (Request $request, Response $response) {
    $email = $request->getParam('email');
    $password = $request->getParam('password');
    $remember_me = $request->getParam('remember_me');
    $sql = 'SELECT id FROM  users WHERE email = :email AND password = :password';
    try{
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email',$email);
        $stmt->bindParam(':password',$password);
        if($stmt->execute()){
            $user = $stmt->fetchAll(PDO::FETCH_OBJ);
            if(count($user)==1){
                $auth_token = generate_auth_token($user[0]->id);
                if($auth_token != false){
                    if($remember_me){
                        $refresh_token = generate_refresh_token($user[0]->id);
                        echo '{'.$auth_token.',"refresh_token":"'.$refresh_token.'","notice" : {"text" : "user authenticated"}}';
                    }
                    else{
                        $refresh_token = generate_refresh_token($user[0]->id);
                        echo '{'.$auth_token.',"notice" : {"text" : "user authenticated"}}';
                    }
                }
            }
            else 
                echo '{"error" : {"code" : 111}}';
        }
    }catch(PDOException $e){
        echo '{"error" : {"text" : '. $e->getMessage().'}';
    }
});

//TO DO : Refresh token?
$app->get('/auth/refresh_auth_token', function (Request $request, Response $response) {
    $token = $request->getHeader('refresh_token');
    $token = $request->getHeader('auth_token'); 
    $sql = 'SELECT id FROM  users WHERE email = :email AND password = :password';
    try{
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();
        $stmt = $db->prepare($sql);
    }catch(PDOException $e){
        echo '{"error" : {"text" : '. $e->getMessage().'}';
    }
});