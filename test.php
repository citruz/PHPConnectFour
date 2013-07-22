<?php

include 'model.php';
$c = new Model();

$err = $c->checkForWin(21,1,3,4);

var_dump($err);
?>