<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Get data reagrding product corresopnding to code sent 
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
            if(count($product)==1)
                echo json_encode($product[0]);
            else 
                echo '{"error" : {"code" : 111}}';
        }
    }catch(PDOException $e){
        echo '{"error" : {"text" : '. $e->getMessage().'}';
    }
});

/**
 * Get all products on the database
*/
$app->get('/api/products', function (Request $request, Response $response) {
    $sql = "SELECT * FROM products";
    try {
        //Get DB object 
        $db = new db();
        //Connect 
        $db = $db->connect();

        $stmt = $db->query($sql);
        $users  = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        echo '{"products_list": "'.json_encode($users).'"}';

    }catch(PDOException $e){
        echo '{"error" : {"text" : '. $e->getMessage().'}';
    }

});