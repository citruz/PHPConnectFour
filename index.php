<?php
include 'controller.php';
session_start();

$controller = new Controller();
$controller->checkLogin(true, false);

$loginData = $controller->getLoginData();
$userData = $loginData['user_obj'];


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Vier gewinnt!</title>

	<link rel="stylesheet" type="text/css" href="css/style.css">

	<script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/main.js">></script>

</head>
<body>
<div class="wrapper">
	<header>
    <h1>Vier gewinnt!</h1>

    <div class="userarea">
      <?php echo $userData['username'];?> | <a href="logout.php">Logout</a>
    </div>
  </header>
	<nav><a href="#">Lobby</a></nav>

	<div class="content">
    <div id="mainmenu">
      <form method="post" action="creategame.php">
        <input name="name" type="text" />
        <input type="submit" value="Erstellen" />
      </form>
      <div class="gameslist"></div>
    </div>
	</div>
</div>
</body>
</html>