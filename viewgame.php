<?php
include 'controller.php';
session_start();

$controller = new Controller();
$controller->checkLogin(true, false);

$loginData = $controller->getLoginData();


$gameinfo = $controller->getGameInfo($_GET);

?>