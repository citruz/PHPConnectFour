<?php
include 'controller.php';
session_start();

$controller = new Controller();
$loginData = $controller->getLoginData();

if ($loginData['loggedin'] == false)
  die();


$ret = $controller->createGame($_POST);

if (!is_array($ret)) {
  die("".$ret);
} else {
  http_response_code(400);
  die(json_encode($ret));
}

?>