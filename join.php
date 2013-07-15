<?php
include 'controller.php';
session_start();

$controller = new Controller();
$controller->checkLogin(true, false);

$ret = $controller->joinGame($_GET);

if ($ret === true) {
  die('success');
} else {
  http_response_code(400);
  die(json_encode($ret));
}
?>