<?php
include 'controller.php';
session_start();

$controller = new Controller();
$controller->checkLogin(true, false);

$controller->logout();
?>