<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Add new user to the Database
*/
$app->post('/api/users/add', function (Request $request, Response $response) {
    $email = $request->getParam('email');
    $password = $request->getParam('password');
    $temporaneo = 1;
    $sql = 'INSERT INTO  prova(email,password,id_token) VALUES(:email,:password,:id_token)';
    try{
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email',$email);
        $stmt->bindParam(':password',$password);
        $stmt->bindParam(':id_token',$temporaneo);
        $stmt->execute();
        echo '{"notice" : {"text" : "user '.$email.' added"}}';
    }catch(PDOException $e){
        echo '{"error" : {"text" : '. $e->getMessage().'}';
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
