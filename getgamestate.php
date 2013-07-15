<?php
include 'controller.php';
session_start();

$controller = new Controller();

$loginData = $controller->getLoginData();

if ($loginData['loggedin'] == false)
  die();

$gamestate = $controller->getGameState($_GET);

echo json_encode($gamestate);

?>