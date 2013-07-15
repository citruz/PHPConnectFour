<?php
include 'controller.php';
session_start();

$controller = new Controller();

$loginData = $controller->getLoginData();

if ($loginData['loggedin'] == false)
  die();
if (isset($_GET['scope']) && $_GET['scope'] == 'user') {
  $games = $loginData['games'];
} else {
  $games = $controller->getOpenGames();
}

echo json_encode($games);
?>