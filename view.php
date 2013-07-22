<?php
include 'controller.php';
session_start();

$controller = new Controller();
$loginData = $controller->getLoginData();

switch ($_GET['action']) {
  case 'login':
    $controller->checkLogin(false, true);
    $ret = $controller->login($_POST);

    if (!is_array($ret)) { //LOGIN WAS SUCCEESFUL

      $ref =  (isset($args['ref'])) ? $args['ref'] :  Controller::$mainPage;

      header("Location: ".$ref);
      die();
    } else {
      header("Location: ".Controller::$loginPage."?errmsg=".$ret['msg']);
    }
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

  case 'register':
    $controller->checkLogin(false, true);

    $ret = $controller->register($_POST);

    $ref = explode('?', $_SERVER['HTTP_REFERER']);
    $ref = $ref[0];

    if ($ret['error'] == true) {
      header('Location: '.$ref.'?regerror='.urlencode($ret['msg']));
    } else {
      header('Location: '.$ref.'?challenge='.urlencode($ret['challenge']).'&userid='.$ret['userid']);
    }

    break;

  case 'challenge':
    $controller->checkLogin(false, true);

    $ret = $controller->checkChallenge($_GET);

    if (is_array($ret)) {
      header('Location: '.Controller::$loginPage.'?errmsg='.urlencode($ret['msg']));
    } else {
      header('Location: '.Controller::$loginPage.'?msg='.urlencode("Vielen Dank, Sie können sich nun einloggen!"));
    }

    break;

  case 'leave':
    if ($loginData['loggedin'] == false)
      die();

    $ret = $controller->leaveGame($_GET);
    if ($ret != true) {
      http_response_code(400);
      die(json_encode($ret));
    } else {
      die(json_encode(array()));
    }

    
    break;



  default:
    die(json_encode(array("error" => true, "msg" => "Wrong action parameter.")));
    break;
}

?>