<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Add new user to the Database
*/
$app->post('/api/users/add', function (Request $request, Response $response) {

    $email = $request->getParam('email');
    $password = $request->getParam('password');
    $gender = $request->getParam('gender');
    $birth_date = $request->getParam('birth_date');

    $user_type = REGISTERED_USER;
    $sql = 'INSERT INTO  users(email,user_type,birth_date,gender) VALUES(:email,:user_type,:birth_date,:gender)';
    $sql2 = 'SELECT id FROM users WHERE email = :email AND user_type = :user_type';
    $sql3 = 'INSERT INTO  registered_users VALUES(:id,:password)';
    try{
        //Insert user 
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email',$email);
        $stmt->bindParam(':user_type',$user_type);
        $stmt->bindParam(':birth_date',$birth_date);
        $stmt->bindParam(':gender',$gender);
        $stmt->execute();
        
        //Retrieve user id 
        $stmt = $db->prepare($sql2);
        $stmt->bindParam(':email',$email);
        $stmt->bindParam(':user_type',$user_type);
        if($stmt->execute()){
            $user=$stmt->fetchall(PDO::FETCH_OBJ);
            if(count($user)==1){
                $stmt = $db->prepare($sql3);
                $stmt->bindParam(':id',$user[0]->id);
                $stmt->bindParam(':password',$password);
                if($stmt->execute()){
                    echo '{"register": true}';
                }

            }
        }
    }catch(PDOException $e){
        echo '{"error" : {"text" : '. $e->getMessage().'}}';
    }

});

$app->post('/api/users/change_password', function (Request $request, Response $response) {
    $id = $request->getParam('id');
    $password = $request->getParam('old_password');
    $new_password = $request->getParam('new_password');
    $user_type = $request->getparam('user_type');

    if($user_type == REGISTERED_USER){
        $sql = 'UPDATE registered_users SET password = :new_password WHERE id = :id AND password = :old_password';
        try{
            //Insert user 
            $db = new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id',$id);
            $stmt->bindParam(':new_password',$new_password);
            $stmt->bindParam(':old_password',$password);
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    $response->getBody()->write(json_encode(['password_updated'=>true]));
                }
                else{
                    $response->getBody()->write(json_encode(['password_updated'=>false]));
                }
            }
           
        }catch(PDOException $e){
            echo '{"code" : 10}';
        }
    }
    else
        echo "test";

})->add($resource_midlleware_post);


$app->get('/api/users/update_user_points', function (Request $request, Response $response) {
   
    $user_id = $request->getParam('id');
    $user_points = $request->getParam('points');
    $user_gender = $request->getParam('gender');
    $user_birth_date = $request->getParam('birth_date');
    $user_email = $request->getParam('email');
    $user_type = $request->getParam('user_type');
    $user_last_time_played = $request->getParam('last_time_played');
    $new_points = $user_points + 1;

    $sql = 'UPDATE users SET points = :new_points,last_time_played = :cur_time WHERE id = :id';
    $timestamp = time();
    try{
        //Insert user 
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id',$user_id);
        $stmt->bindParam(':new_points',$new_points);
        $stmt->bindParam(':cur_time',$timestamp);

        if($stmt->execute()){
            if($stmt->rowCount() == 1){
                $signedAccessToken = generate_jwt_token(['id'=>$user_id,
                                                            'user_type'=>$user_type,
                                                            'points'=>$new_points,
                                                            'gender'=>$user_gender,
                                                            'birth_date'=>$user_birth_date,
                                                            'email'=>$user_email,
                                                            'last_time_played' => $timestamp],
                                                            ACCESS_TOKEN_TYPE);
                $response->getBody()->write(json_encode([ACCESS_TOKEN=>$signedAccessToken,'token_type' => 'Bearer']));
            }
            else{
                $response->getBody()->write(json_encode(['password_updated'=>false]));
            }
        }
           
    }catch(PDOException $e){
        echo '{"code" : 10}';
    }
    
})->add($resource_middleware_get);
      

$app->get('/api/users', function (Request $request, Response $response) {
    $sql = "SELECT id,email FROM users";
    try {
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();

        $stmt = $db->query($sql);
        $users  = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        echo json_encode($users);

    }catch(PDOException $e){
        echo '{"error" : {"text" : '. $e->getMessage().'}';
    }

});
/*
$app->post('/api/users/check_game_availability', function (Request $request, Response $response) {
    $id = $request->getParam('id');
    $sql = 'SELECT last_time_played FROM users WHERE id = :id';
    
    try{
        //Insert user 
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id',$id);
        if($stmt->execute()){
            $users = $stmt->fetchAll(PDO::FETCH_OBJ);
            if(count($users)==1){
                if($users[0]->last_time_played > (time() - (24 * 60 * 60) )){
                    
                    $response->getBody()->write(json_encode(['is_game_available' => true]));
                }
                else
                    $response->getBody()->write(json_encode(['is_game_available' => false]));

            }
        }
           
    }catch(PDOException $e){
        echo '{"code" : 10}';
    }
    
})->add($resource_midlleware_get);



 * Get a specific user datas
 
    $app->get('/api/users/{id}', function (Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $sql = 'SELECT email FROM  prova WHERE id = :id';
        try{
            //Get DB object 
            $db = new db();
            //Connect 
            $db = $db->connect();
            $stmt = $db->prepare($sql);

            $stmt->bindParam(':id',$id);
            if($stmt->execute()){
                $user = $stmt->fetchAll(PDO::FETCH_OBJ);
                if(count($user)==1)
                    echo json_encode($user[0]);
                else 
                    echo '{"error" : {"code" : 111}}';
            }
        }catch(PDOException $e){
            echo '{"error" : {"text" : '. $e->getMessage().'}';
        }
    })->add($resource_middleware);;


    $app->get('/api/users', function (Request $request, Response $response) {
        $sql = "SELECT id,email FROM prova";
        try {
            //Get DB object 
            $db = new db();
            //Connect 
            $db = $db->connect();

            $stmt = $db->query($sql);
            $users  = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            echo json_encode($users);

        }catch(PDOException $e){
            echo '{"error" : {"text" : '. $e->getMessage().'}';
        }

    })->add($resource_middleware);;
**/