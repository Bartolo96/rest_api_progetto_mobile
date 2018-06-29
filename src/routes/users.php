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

    $user_type = REGISTERED_USER;
    $sql = 'INSERT INTO  users(email,user_type) VALUES(:email,:user_type)';
    $sql2 = 'SELECT id FROM users WHERE email = :email';
    $sql3 = 'INSERT INTO  registered_users VALUES(:id,:password)';
    try{
        //Insert user 
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email',$email);
        $stmt->bindParam(':user_type',$user_type);
        $stmt->execute();
        //Retrieve user id 
        $stmt = $db->prepare($sql2);
        $stmt->bindParam(':email',$email);
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
        echo '{"code" : 10}';
    }

    
});

/**
 * Get a specific user datas
 */
$app->get('/api/users/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $sql = 'SELECT * FROM  prova WHERE id = :id';
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
})->add($middleware);;

//TO REMOVE
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

})->add($middleware);;
