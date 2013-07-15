<?php
include 'controller.php';
session_start();

$controller = new Controller();
$controller->checkLogin(false, true);

if (isset($_POST['username']))
  $_GET['msg'] = $controller->login($_POST);


if (isset($_GET['msg']))
echo $_GET['msg'];
?>


<form action="" method="POST"> 
  <input type="text" name="username" />
  <input type="text" name="email" />
  <input type="password" name="password" />
  <input type="submit" value="Register" />
</form>