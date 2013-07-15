<?php
include 'controller.php';
session_start();

$controller = new Controller();

$loginData = $controller->getLoginData();

if ($loginData['loggedin'] == false)
  die();

$gameinfo = $controller->getGameInfo($_GET);

echo json_encode($gameinfo);

?>