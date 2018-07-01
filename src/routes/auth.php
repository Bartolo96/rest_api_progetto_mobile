<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
/**
 * Authenticate user 
 * @request CLIENT SIDE LOGIN REQUEST
 */
$app->post('/auth/authenticate_user', function ($request, $response) {
    $responseBody = $response->getBody();
    $email = $request->getParam('email');
    $password = $request->getParam('password');
    $remember_me = $request->getParam('remember_me');
    
    $sql = 'SELECT users.id,users.email,users.user_type FROM  (users JOIN registered_users ON users.id = registered_users.id AND user_type = :user_type) WHERE email = :email AND password = :password';
    try{
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $user_type = REGISTERED_USER;
        $stmt->bindParam(':email',$email);
        $stmt->bindParam(':password',$password);
        $stmt->bindParam(':user_type',$user_type);

        if($stmt->execute()){
            $user = $stmt->fetchAll(PDO::FETCH_OBJ);
            if(count($user)==1){
                $signedAccessToken = generate_jwt_token(['id'=>$user[0]->id,'user_type'=>$user[0]->user_type],ACCESS_TOKEN_TYPE);
                if($remember_me){
                    $refreshToken = generate_refresh_token('id',$user[0]->id,REGISTERED_USER);
                    $signedRefreshToken = generate_jwt_token(['id'=>$user[0]->id,REFRESH_TOKEN=>$refreshToken],REFRESH_TOKEN_TYPE);
                    $responseBody->write(json_encode([ACCESS_TOKEN=>$signedAccessToken,REFRESH_TOKEN =>$signedRefreshToken,"remember_me" =>$remember_me]));
                }
                else{
                    $responseBody->write(json_encode([ACCESS_TOKEN =>$signedAccessToken]));
                }
            }
            else 
                $responseBody->write('{"Error":"Invalid Email: '.$email.' or Password:'.$password.'"}');
        }
    }catch(PDOException $e){
        $responseBody->write('{"error" : {"text" : '. $e->getMessage().'}');
    }
    $new_response = $response->withHeader('Content-type', 'application/json');
    return $new_response;
});

/**
 * Authenticate user
 */
$app->post('/auth/authenticate_third_party_user', function (Request $request, Response $response) {

    $token = $request->getParam('token');
    $user_type = $request->getParam('user_type');
    $responseBody = $response->getBody();
    switch($user_type){
        case GOOGLE_USER:
            //Google client
            $googleClient = new Google_Client(['client_id' => CLIENT_ID]);
            $payload = $googleClient->verifyIdToken($token);
            if ($payload) {
                $email = (string)$payload['email'];               
                
                try{
                    //Check if given email already exists
                    $responseBody->write(authenticate_third_party_users($email,$user_type)); 
            
                }catch(PDOException $e){
                    $response = $response->withStatus('418',e.getMessage());
                }
            } else {
                // Invalid ID token
                $response = $response->withStatus('400','Bad Request');
            };
            break;
        case FACEBOOK_USER:
            $facebook = new \Facebook\Facebook([
                'app_id' => FACEBOOK_APP_ID,
                'app_secret' => FACEBOOK_CLIENT_SECRET,
                'default_graph_version' => 'v2.10' // optional
            ]);
            try {
                $fbResponse = $facebook->get('/me?fields=id,name,email', $token);
               
              } catch(Facebook\Exceptions\FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
              } catch(Facebook\Exceptions\FacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
              }
              
              $user = $fbResponse->getGraphUser();
              $response = authenticate_third_party_users($user->getEmail(),$user_type);
              break; 
    }
    return $response;
    
});

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
        $auth_token = generate_auth_token($user[0]->id);
    }catch(PDOException $e){
        echo '{"error" : {"text" : '. $e->getMessage().'}';
    }
});


$app->post('/auth/old_authenticate_user', function (Request $request, Response $response) {
    $email = $request->getParam('email');
    $password = $request->getParam('password');
    $remember_me = $request->getParam('remember_me');
    $sql = 'SELECT users.id FROM  (users JOIN registered_users ON users.id = registered_users.id AND user_type = :user_type) WHERE email = :email AND password = :password';
    try{
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $user_type = REGISTERED_USER;
        $stmt->bindParam(':email',$email);
        $stmt->bindParam(':password',$password);
        $stmt->bindParam(':user_type',$user_type);
        if($stmt->execute()){
            $user = $stmt->fetchAll(PDO::FETCH_OBJ);
            if(count($user)==1){
                $auth_token = generate_auth_token('id',$user[0]->id,REGISTERED_USER);
                if($auth_token != false){
                    if($remember_me){
                        $refresh_token = generate_refresh_token('id',$user[0]->id,REGISTERED_USER);
                        echo '{"authtoken":"'.$auth_token.'","refreshtoken":"'.$refresh_token.'"}';
                    }
                    else{
                        echo '{"authtoken":"'.$auth_token.'"}';
                    }
                }
            }
            else 
                echo '{"Error":"Invalid Email or Password"}';
        }
    }catch(PDOException $e){
        echo '{"error" : {"text" : '. $e->getMessage().'}';
    }
});
