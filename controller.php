<?php
include 'model.php';
include 'utils.php';

/**
  * Der Controller ist dafür zuständig, alle Anfragen vom User auszuwerten,
  * zu validieren und ggf. an das Model weiterzuleiten.
  */
class Controller {
  private $model;
  private $user;

  public static $loginPage = "login.php";
  public static $mainPage = "index.php";
  public static $registerPage = "register.php";
  public static $gamePage = "viewgame.php";

/**
  * Erstellt eine neue Controllerinstanz und gleichzeitig eine Modelinstanz.
  * Holt automatisch die Login Daten des Benutzers, falls vorhanden.
  */
  function __construct() {
    $this->model = new Model();
    $this->user = null; 

    $this->getLoginData();
  }

/**
  * Überprüft, ob der User eingeloggt ist und gibt dessen Logininformationen zurück.
  * @return array Array mit Userinformationen. 
  */
  public function getLoginData () {
    if ($this->user == null) {
      $user = $this->model->getLoginUser();
      if (is_array($user) && isset($user['error'])) {
        die("Kritischer Fehler bei Datenbankzugriff: ".$user['msg']);
      }
    } else {
      $user = $this->user;
    } 
    if ($user == false) {
      return array('loggedin' => false);
    } else {
      $this->user = $user;
      return array('loggedin' => true,
                  'user_obj' => $user,
                  'games' => $this->model->getGamesForUser($user['userid'])
                  );
    }
  }

/**
  * Überprüft den Login-Status des Users.
  * @param bool $needsLogin Wenn der Paramter true ist und der User nicht eingeloggt seien sollte, wird er auf die Login-Seite weitergeleitet.
  * @param bool $needsNoLogin Wenn Paramter true ist und der User eingeloggt seien sollte, wird er auf die Index-Seite weitergeleitet.  
  */
  public function checkLogin($needsLogin, $needsNoLogin) {
    $this->getLoginData();
    if ($needsLogin && $this->user == null) {
      header('Location: '.self::$loginPage.'?ref='.urlencode($this->curPageName()));    
      die();
    } else if ($needsNoLogin && $this->user != null) {
      header('Location: '.self::$mainPage.'?msg=alreadyloggedin');  
      die();
    }
  }

/**
  * Überprüft ob die Challenge erfüllt wurde. Wenn ja wird sie aus der Datenbank entfernt und der User auf activated gesetzt.
  * @param array $args 'challenge' und 'userid' müssen gesetzt sein.
  * @return true|array Im Erfolgsfall true, ansonsten Array mit Errornachricht.
  */
  public function checkChallenge($args) {
    if (!isset($args['userid']) || !isset($args['challenge'])) {
      return array("error" => true, "msg" => "Challenge: Falsche Parameter.");
    }

    $challenge_db = $this->model->getChallenge($args['userid']);
    if (count($challenge_db) == 0) {
      return array("error" => true, "msg" => "Challenge nicht gefunden.");
    }

    if ($challenge_db['challenge'] == $args['challenge']) {
      $this->model->removeChallenge($args['userid']);   
      $this->model->updateUser($args['userid'], null, null, null , '1');
      return true;
    } else {
      return array("error" => true, "msg" => "Challenge falsch.");
    }
  }

/**
  * Überprüft übergebenen Username und Passwort auf Gültigkeit. Loggt User ein, falls korrekt.
  * @param array $args 'username' und 'password' müssen gesetzt sein.
  * @return true|array Im Erfolgsfall true, ansonsten Array mit Errornachricht.
  */
  public function login($args) {
    if (!isset($args['username']) || trim($args['username']) == '') 
      return array("error" => true, "msg" => "Username ungültig.");

    if (!isset($args['password']) || trim($args['password']) == '') 
      return array("error" => true, "msg" => "Passwort ungültig.");

    $user = $this->model->getUserByUsername($args['username']);

    if ($user && $user['activated'] != '1') {
      return array("error" => true, "msg" => "Bitte bestätigen Sie zuerst den Link in ihrer Email.");     
    }

    if ($user && $user['password'] === md5($args['password'])) {
      //Login Successful
      $this->user = $user;
      $this->model->createSession($user['id']);

      return true;
    } else {
      return array("error" => true, "msg" => "Ungültige Login-Daten.");
    }

  }

/**
  * Überprüft übergebenen Argumente auf Gültigkeit. Registriert User und erzeugt eine Challenge.
  * @param array $args 'username', 'password' und 'email' müssen gesetzt sein.
  * @return array Im Erfolgsfall Array mit Challenge und neuer User ID, ansonsten Array mit Errornachricht.
  */
  public function register($args) {
    $errorMsg = "";
    if (!isset($args['username']) || (($username = $this->checkString($args['username'], 3)) === false)) {
      $errorMsg = $errorMsg . "Ungültiger Username.\n";
    }
    if (!isset($args['email']) || (($email = $this->checkString($args['email'], 5, FILTER_VALIDATE_EMAIL)) === false)) {
      $errorMsg = $errorMsg . "Ungültige Emailadresse.\n";
    }
    if (!isset($args['password']) || (($password = $this->checkString($args['password'], 6)) === false)) {
      $errorMsg = $errorMsg . "Ungültiges Passwort.\n";
    }
    $_SESSION['temp_user'] = $username;
    $_SESSION['temp_email'] = $email;
    
    if (strlen($errorMsg) > 0) {
      return array('error' => 'true', 'msg' => $errorMsg);
    }

    if (count($this->model->getUserByUsername($username)) != 0) {
      return array('error' => 'true', 'msg' => 'Username schon vorhanden.'); 
    }

    if (count($this->model->getUserByEmail($email)) != 0) {
      return array('error' => 'true', 'msg' => 'Ein User mit dieser Email Adresse ist schon vorhanden.'); 
    }

    $userid = $this->model->createUser($username, $email, $password);

    if (is_array($userid)) //Fehler
      return ret;

    $challenge = $this->model->createChallenge($userid);
    
    if (is_array($challenge)) //Fehler
      return ret;

    //Registrierung vollständig

    $_SESSION['temp_user'] = "";
    $_SESSION['temp_email'] = "";

    return array('error' => false, 'challenge' => $challenge, 'userid' => $userid);
  }

  private function checkString($str, $minLength, $filter = null) {
    $str = trim($str);
    if (strlen($str) >= $minLength) {
      if ($filter != null && !filter_var($str, $filter)) {
        return false;
      }
      return $str;
    } else {
      return false;
    }
  }

/**
  * Loggt den aktuellen User aus. Weiterleitung auf Login Seite.
  */  
  public function logout() {
    $this->model->removeSession();
    header('Location: '.self::$loginPage.'?msg='.urlencode("Logout erfolgreich!"));  
  } 

/**
  * Gibt offene Spiele zurück, d.h. nicht closed, mit einem Spieler und nicht vom Spieler selbst gestartet.
  * @return array Array mit Spielinformationen
  */
  public function getOpenGames() {
    $games = $this->model->getGames();
    $out = array();

    foreach ($games as $game) {
      if ($game['closed'] == 0) {
        $users = $this->model->getUsersForGame($game['id']);
        if (count($users) == 1 && $users[0]['id'] != $this->user['userid']) {
          $game['username'] = $users[0]['username'];
          array_push($out, $game);
        }
      }
    }

    return $out;
  }

/**
  * Erstellt ein Spiel mit den übergebenen Argumenten.
  * @param array $args 'name', 'player1color' und 'player2color' müssen gesetzt sein.
  * @return integer|array Im Erfolgsfall int mit neuer Spiel ID, ansonsten Array mit Errornachricht.
  */
  public function createGame($args) {
    if ($this->user != null) {
      if (!isset($args['name']) || trim($args['name']) == "") {
        return array('error' => 'Ungültiger Name');
      }
      if (!isset($args['player1color']) || trim($args['name']) == "player1color") {
        return array('error' => 'Ungültige Farbe für Spieler 1');
      }
      if (!isset($args['player2color']) || trim($args['player2color']) == "") {
        return array('error' => 'Ungültige Farbe für Spieler 2');
      }


      $name = $args['name'];
      $ret = $this->model->createGame($args['name'], 2, $args['player1color'], $args['player2color']);
      if (!is_array($ret)) {
        //Game created
        //Assign User to game
        if (($err = $this->model->assignUserToGame($this->user['userid'], $ret)) ===  true) {
          //Everything successful
          return $ret;
        } else {
          //Assign failed
          return array('error' => 'Erstellung des Games fehlgeschlagen: '.$err['msg'].'.');
        }
      } else {
        //Create game failed
        return array('error' => 'Erstellung des Games fehlgeschlagen: '.$ret['msg'].'.');
      }

    } else {
        return array('error' => 'Nicht authentifiziert.');
    }
  }

/**
  * Weist den aktuellen User zu einem Spiel zu,
  * @param array $args 'gameid' muss gesetzt sein.
  * @return bool|array Im Erfolgsfall true, ansonsten Array mit Errornachricht.
  */
  public function joinGame($args) {
    if (!isset($args['gameid'])) {
      return array('error' => 'true', 'msg' => 'Keine gameid geliefert');
    } else {
      $game = $this->model->getGame($args['gameid']);
      if ($game == null || isset($game['error'])) {
        return array('error' => 'true', 'msg' => 'Ungültige Gameid gegeben: "'.$args['gameid'].'".');
      } else if ($game['closed'] == 1) {
        return array('error' => 'true', 'msg' => 'Spiel ist schon beendet.');
      } else {
        return $this->model->assignUserToGame($this->user['userid'], $game['id']);
      }

    }
  }

/**
  * Gibt alle Informationen zum angegeben Spiel zurück.
  * @param array $args 'gameid' muss gesetzt sein.
  * @return array Im Erfolgsfall Array mit Infos, ansonsten Array mit Errornachricht.
  */
  public function getGameInfo($args) {
    if (!isset($args['gameid'])) {
      return array('error' => 'true', 'msg' => 'Keine gameid geliefert');
    } else {
      $game = $this->model->getGame($args['gameid']);
      if ($game == null || isset($game['error'])) {
        return array('error' => 'true', 'msg' => 'Ungültige Gameid gegeben "'.$args['gameid'].'".');
      } 
      $players = $this->model->getUsersForGame($game['id']);

      foreach ($players as $player) {
        if ($player['id'] == $this->user['userid']) {
          $ret = array('game' => $game, 'players'=> $players);
          return $ret;
        }
      }
      return array('error' => 'true', 'msg' => 'Du bist nicht Teil dieses Spiels.');
    }
  }

/**
  * Gibt alle Spielzüge zum angegeben Spiel zurück.
  * @param array $args 'gameid' muss gesetzt sein. Optional 'minid'.
  * @return array Im Erfolgsfall Array mit Infos, ansonsten Array mit Errornachricht.
  */
  public function getGameState($args) {
    $gameinfo = $this->getGameInfo($args);
    if (is_array($gameinfo) && !isset($gameinfo['error'])) {
      //User and gameid validated
      if (isset($args['minid'])) 
        $moves = $this->model->getGameMoves($gameinfo['game']['id'], $args['minid']);
      else
        $moves = $this->model->getGameMoves($gameinfo['game']['id']);

      if(!isset($moves['error'])) {
        if (count($gameinfo['players']) == 2) {
          $nummoves1 = $this->model->getNumGameMoves($gameinfo['game']['id'], $gameinfo['players'][0]['id']);
          $nummoves2 = $this->model->getNumGameMoves($gameinfo['game']['id'], $gameinfo['players'][1]['id']);
          if ($nummoves1 > $nummoves2) {
            $curplayer = $gameinfo['players'][1]['id'];
          } else {
            $curplayer = $gameinfo['players'][0]['id'];
          }
        } else {
          $curplayer = $gameinfo['players'][0]['id'];
        }
        return array('game' => $gameinfo, 'moves' => $moves, 'currentPlayer' => $curplayer);
      } else {
        return $moves;
      }
    } else {
      return $gameinfo;
    }

  }

/**
  * Speichert einen Spielzug in der Datenbank.
  * @param array $args 'gameid' und 'x' (Spaltennummer) müssen gesetzt sein.
  * @return array Im Erfolgsfall Array mit Spieldaten, ansonsten Array mit Errornachricht.
  */
  public function submitMove($args) {
    if (!isset($args['x']) || !is_numeric($args['x']))
      return array('error' => 'true', 'msg' => 'Falsches Argument für x');

    $gamestate = $this->getGameState($args);
    if (is_array($gamestate) && !isset($gameinfo['error'])) {
      //User and gameid validated
      if (count($gamestate['game']['players']) != 2)
        return array('error' => 'true', 'msg' => 'Warte auf einer zweiten Spieler.');

      if ($gamestate['currentPlayer'] == $this->user['userid']) {
        $moves = $this->model->getGameMoves($gamestate['game']['game']['id']);
        $minY = 7;
        foreach ($moves as $key => $move) {
          if ($args['x'] == $move['x']) {
            if ($move['y'] == 1) {
              //spalte is voll
              return array('error' => 'true', 'msg' => 'Hier kannst du keinen Coin spielen.');
            }
            if ($move['y']  < $minY) {
              $minY = $move['y'];
            }
          }
        }
        $ret = $this->model->addMove($gamestate['game']['game']['id'], $this->user['userid'], $args['x'], $minY-1);
        if (is_array($ret)) 
          return $ret;
        else {
          //Check for win
          $win = $this->model->checkForWin($gamestate['game']['game']['id'], $this->user['userid'], $args['x'], $minY-1);

          if ($win) {
            $ret = $this->model->closeGame($args['gameid'], $this->user['userid']);
            if ($ret != true)
              return $ret;
          }
                
          return $this->getGameState($args);
        }

      } else {
        return array('error' => 'true', 'msg' => 'Du bist nicht aktueller Spieler.');
      }
    } else {
      return $gameinfo;
    }
    
  }

/**
  * Schließt das angegebene Spiel.
  * @param array $args 'gameid' muss gesetzt sein.
  * @return bool|array Im Erfolgsfall true, ansonsten Array mit Errornachricht.
  */
  public function leaveGame($args) {

    $gamestate = $this->getGameState($args);
    if (is_array($gamestate) && !isset($gamestate['error'])) {
      //User and gameid validated
      $ret = $this->model->closeGame($args['gameid']);
      if ($ret != true)
        return $ret;

      return true;
    } else {
      return $gamestate;
    }

  }

  private function curPageName() {
    return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
  }
}