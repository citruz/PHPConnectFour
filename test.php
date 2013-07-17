<?php
include('controller.php');
session_start();
$con = new Controller();

$ret = $con->createGame(array('name'=> ''.time()));

echo "rtet : ".$ret;
var_dump($ret);

?>