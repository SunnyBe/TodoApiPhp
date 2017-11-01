<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
include 'config.php';
//require '../src/routes/customer.php'

$app = new \Slim\App(["settings" => $config]);
//Handle Dependencies
$container = $app ->getContainer();

$container['db'] = function($c){
	try{
		$db = $c['settings']['db'];
		$options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE	=> PDO::FETCH_ASSOC,
			);
		$pdo = new PDO("mysql:host=" . $db['servername'] . ";dbname=" . $db['dbname'],
       	$db['username'], $db['password'],$options);
       	return $pdo;
	}catch(\Exception $ex){
		return $ex -> getMessage();
	}
};


$app->post('/user', function ($request, $response) {
   
   try{
       $con = $this->db;
       $sql = "INSERT INTO `users`(`username`, `email`,`password`) VALUES (:username,:email,:password)";
       $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
       $values = array(
       ':username' => $request->getParam('username'),
       ':email' => $request->getParam('email'),
//Using hash for password encryption
       'password' => password_hash($request->getParam('password'),PASSWORD_DEFAULT)
       );
       $result = $pre->execute($values);
       return $response->withJson(array('status' => 'User Created'),200);
       
   }
   catch(\Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
   }
   
});

$app->put('/user/{id}', function ($request,$response) {
   try{
       $id     = $request->getAttribute('id');
       $con = $this->db;
       $sql = "UPDATE users SET name=:name,email=:email,password=:password WHERE id = :id";
       $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
       $values = array(
       ':name' => $request->getParam('name'),
       ':email' => $request->getParam('email'),
       ':password' => password_hash($request->getParam('password'),PASSWORD_DEFAULT),
       ':id' => $id
       );
       $result =  $pre->execute($values);
       if($result){
           return $response->withJson(array('status' => 'User Updated'),200);
       }else{
           return $response->withJson(array('status' => 'User Not Found'),422);
       }
              
   }
   catch(Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
   }
   
});

$app->delete('/user/{id}', function ($request,$response) {
   try{
       $id     = $request->getAttribute('id');
       $con = $this->db;
       $sql = "DELETE FROM users WHERE id = :id";
       $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
       $values = array(
       ':id' => $id);
       $result = $pre->execute($values);
       if($result){
           return $response->withJson(array('status' => 'User Deleted'),200);
       }else{
           return $response->withJson(array('status' => 'User Not Found'),422);
       }
      
   }
   catch(Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
   }
   
});

$app->get('/users', function ($request,$response) {
   try{
       $con = $this->db;
       $sql = "SELECT * FROM users";
       $result = null;
       foreach ($con->query($sql) as $row) {
           $result[] = $row;
       }
       if($result){
           return $response->withJson(array('status' => 'true','result'=>$result),200);
       }else{
           return $response->withJson(array('status' => 'Users Not Found'),422);
       }
              
   }
   catch(Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
   }
   
});


$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");

    return $response;
});

$app->run();