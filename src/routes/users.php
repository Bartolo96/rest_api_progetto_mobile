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


//TO REMOVE
$app->get('/api/users/show_users', function (Request $request, Response $response) {
    $sql = "SELECT * FROM prova";
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
