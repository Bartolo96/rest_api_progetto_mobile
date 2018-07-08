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
    
    $sql = 'SELECT users.id,users.email,users.user_type,users.gender,users.points,users.birth_date FROM  
                    (users JOIN registered_users ON users.id = registered_users.id AND user_type = :user_type)
                            WHERE email = :email AND password = :password';
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
                $signedAccessToken = generate_jwt_token(['id'=>$user[0]->id,
                                                        'user_type'=>$user[0]->user_type,
                                                        'points'=>$user[0]->points,
                                                        'gender'=>$user[0]->gender,
                                                        'birth_date'=>$user[0]->birth_date,
                                                        'email'=>$user[0]->email],
                                                        ACCESS_TOKEN_TYPE);
                if($remember_me){
                    $refreshToken = generate_refresh_token('id',$user[0]->id,REGISTERED_USER);
                    $signedRefreshToken = generate_jwt_token(['id'=>$user[0]->id,'user_type'=>$user[0]->user_type,REFRESH_TOKEN=>$refreshToken],REFRESH_TOKEN_TYPE);

                    $responseBody->write(json_encode([ACCESS_TOKEN =>[ACCESS_TOKEN=>$signedAccessToken,'token_type' => 'Bearer','expires_in' => 3600],
                                                        REFRESH_TOKEN=>[REFRESH_TOKEN =>$signedRefreshToken, 'token_type' => 'Bearer']]));
                }
                else{
                    $responseBody->write(json_encode([ACCESS_TOKEN =>[ACCESS_TOKEN =>$signedAccessToken, 'token_type' => 'Bearer','expires_in' => 3600]]));
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
                $fbResponse = $facebook->get('/me?fields=id,user_gender,email', $token);
               
            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
              
              $user = $fbResponse->getGraphUser();
              $responseBody->write(authenticate_third_party_users($user->getEmail(),$user_type));
              break; 
    }
    $response = $response->withHeader('Content-Type','application/json');
    return $response;
    
});

/**
*Recieve a Refresh token that can be used to 
**/
$app->get('/auth/refresh_access_token', function (Request $request, Response $response) {;
    $responseBody = $response->getBody();
    $token = $request->getParam(REFRESH_TOKEN);
    $id = $request->getParam('id');
    $sql = 'SELECT users.id,users.email,users.user_type,users.gender,users.points,users.birth_date,users.email FROM users 
                    WHERE refresh_token = :token AND id = :id';
    try{
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id',$id);
        $stmt->bindParam(':token',$token);
        if($stmt->execute()){
            $user = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            if(count($user)==1){
                $signedAccessToken = generate_jwt_token(['id'=>$user[0]->id,
                                                        'user_type'=>$user[0]->user_type,
                                                        'points'=>$user[0]->points,
                                                        'gender'=>$user[0]->gender,
                                                        'birth_date'=>$user[0]->birth_date,
                                                        'email'=>$user[0]->email],
                                                         ACCESS_TOKEN_TYPE);
                $refreshToken = generate_refresh_token('id',$user[0]->id,$user[0]->user_type);
                $signedRefreshToken = generate_jwt_token(['id'=>$user[0]->id,REFRESH_TOKEN=>$refreshToken],REFRESH_TOKEN_TYPE);
                $responseBody->write(json_encode([ACCESS_TOKEN =>[ACCESS_TOKEN=>$signedAccessToken,'token_type' => 'Bearer','expires_in' => 3600],
                                                    REFRESH_TOKEN=>[REFRESH_TOKEN =>$signedRefreshToken,'user_type'=>$user[0]->user_type, 'token_type' => 'Bearer']]));
            }
            else{
                $response = $response->withHeader('WWW-Authenticate','http://nitwx.000webhostapp.com');
                $response = $response->withStatus(408,'Unauthorized');
            }
        }
    }catch(PDOException $e){
        return $responseBody->write(json_encode(['Notice'=>$e->grtMessage()]));
    }
    return $response;
})->add($refresh_middleware);

