<?php
include 'controller.php';
session_start();

$controller = new Controller();
$loginData = $controller->getLoginData();

switch ($_GET['action']) {
  case 'register':
    $controller->checkLogin(false, true);
    $controller->login($_POST);
    break;

  case 'logout':
    $controller->checkLogin(true, false);
    $controller->logout();
    break;

  case 'creategame':
    if ($loginData['loggedin'] == false)
      die();
    
    $ret = $controller->createGame($_POST);

    if (!is_array($ret)) {
      die("".$ret);
    } else {
      http_response_code(400);
      die(json_encode($ret));
    }

    break;
  
  case 'getgameinfo':
    if ($loginData['loggedin'] == false)
      die();

    $gameinfo = $controller->getGameInfo($_GET);

    die(json_encode($gameinfo));
    break;

  case 'getgames':
    if ($loginData['loggedin'] == false)
      die();
    if (isset($_GET['scope']) && $_GET['scope'] == 'user') {
      $games = $loginData['games'];
    } else {
      $games = $controller->getOpenGames();
    }

    die(json_encode($games));
    break;

  case 'getgamestate':
    if ($loginData['loggedin'] == false)
      die();

    $gamestate = $controller->getGameState($_GET);

    die(json_encode($gamestate));
    break;

  case 'join':
    $controller->checkLogin(true, false);

    $ret = $controller->joinGame($_GET);

    if ($ret === true) {
      die(json_encode($controller->getGameState($_GET)));
    } else {
      http_response_code(400);
      die(json_encode($ret));
    }
    break;

  case 'submitmove':
    if ($loginData['loggedin'] == false)
      die();

    die(json_encode($controller->submitMove($_GET)));
    break;



  default:
    die(json_encode(array("error" => true, "msg" => "Wrong action parameter.")));
    break;
}

?>