<?php
include 'controller.php';
session_start();

$controller = new Controller();
$loginData = $controller->getLoginData();

if ($loginData['loggedin'] == false)
  die();


$ret = $controller->createGame($_POST);

if ($ret === true) {
  die('success');
} else {
  http_response_code(400);
  die(json_encode($ret));
}

?>