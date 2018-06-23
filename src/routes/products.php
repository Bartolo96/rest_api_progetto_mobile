<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Add new user to the Database
*/
$app->get('/api/products/{code}', function (Request $request, Response $response) {
    $code = $request->getAttribute('code');
    $sql = 'SELECT * FROM products WHERE code = :code';
    try{
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':code',$code);
        if($stmt->execute()){
            $product = $stmt->fetchAll(PDO::FETCH_OBJ);
            if(count($user)==1)
                echo json_encode($product[0]);
            else 
                echo '{"error" : {"code" : 111}}';
        }
    }catch(PDOException $e){
        echo '{"error" : {"text" : '. $e->getMessage().'}';
    }
});