<?php
include 'controller.php';
session_start();

$controller = new Controller();

$loginData = $controller->getLoginData();

if ($loginData['loggedin'] == false)
  die();

echo json_encode($controller->submitMove($_GET));
?>