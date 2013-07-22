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
  <link href='http://fonts.googleapis.com/css?family=Yanone+Kaffeesatz:400,200,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>

	<script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
  <script type="text/javascript" src="js/jquery.form.min.js"></script>
  <script type="text/javascript" src="js/main.js"></script>

</head>
<body>
<div class="wrapper">
	<header>
    <h1>Vier gewinnt!</h1>

    <div class="userarea">
      <?php echo $userData['username'];?> | <a href="view.php?action=logout">Logout</a>
    </div>
  </header>
	<nav>
    <div class="nav-inner">
      <div class="elem active"><a href="#" class="lobby">Lobby</a></div>
    </div>
    <div class="clear"></div>
  </nav>
	<div class="content">
    <div id="mainmenu">
      <div class="leftCol">
        <h3>Offene Spiele</h3>

        <div class="toolbar">
          <a href="#" class="new">Neues Spiel</a>
          <a href="#" class="refresh">Aktualisieren</a>
        </div>
        <div class="gameslist"></div>
      </div>
      <div class="rightCol">
        <h3>Neues Spiel erstellen</h3>
        <a href="#" class="close">×</a>
        <form method="post" action="view.php?action=creategame" class="creategame">
          <input name="name" type="text" placeholder="Spielname"/>

          <div id="p1color" class="colorPicker">
            Spieler 1 Farbe:
            <?php include 'colorPicker.html'; ?>
          </div>
          <div id="p2color" class="colorPicker">
            Spieler 2 Farbe:
            <?php include 'colorPicker.html'; ?>
          </div>

          <input type="submit" value="Erstellen" />
        </form>
      </div>
      <div class="clear"></div>
    </div>
	</div>
</div>
</body>
</html>