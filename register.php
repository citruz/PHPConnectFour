<?php
include 'controller.php';
session_start();

$controller = new Controller();
$controller->checkLogin(false, true);

if (isset($_POST['username']))
  $msg = $controller->login($_POST);


?>