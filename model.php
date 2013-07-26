<?php
/**
  * Das Model tätigt alle Datenbankzugriffe und enthält ansonsten wenig Programmlogilk.
  */
class Model {
/**
  * Erstellt eine neue Modelinstanz und öffnet die Verbindung zur Datenbank.
  */
  public function __construct() {
    mysql_connect("localhost", "root", "root") or $this->error('Database Connection failed');
    mysql_select_db("viergewinnt") or $this->error('Database selection failed.');
  }

/**
  * Fügt einen neuen User in der Datenbank ein.
  * @param string $username Benutzername
  * @param string $email Email Adresse
  * @param string $password Passwort
  * @return integer|array Im Erfolgsfall die neue Userid, ansonsten Array mit Errornachricht.
  */
  public function createUser($username, $email, $password) {
    if (count($this->getUserByUsername($username)) != 0) 
      return $this->error('User'.$username.' already exists');

    $sqlbefehl = $this->buildInsertQuery('user', array(
        'username' => $username,
        'email' => $email,
        'password' => md5($password)
    )); 
        
    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else 
      return mysql_insert_id();
  } 

/**
  * Ändert die Daten eines Users in der Datenbank.
  * @param string $userid ID des zu ändernden Users
  * @param null|string $username Benutzername, oder null fall keine Änderung erwünscht
  * @param null|string $email Email Adresse, oder null fall keine Änderung erwünscht
  * @param null|string $password Passwort, oder null fall keine Änderung erwünscht
  * @param null|integer $activated 1 oder 0, oder null fall keine Änderung erwünscht
  * @return bool|array Im Erfolgsfall true, ansonsten Array mit Errornachricht.
  */
  public function updateUser($userid,$username = null, $email = null, $password = null, $activated = null) {
    $updateArray = array();

    if ($username != null) 
      $updateArray['username'] = $username;
    if ($email != null) 
      $updateArray['email'] = $email;
    if ($password != null) 
      $updateArray['password'] = md5($password);
    if ($activated != null) 
      $updateArray['activated'] = $activated;


    $sqlbefehl = $this->buildUpdateQuery('user',$updateArray,
      array('id' => $userid));
      $result = mysql_query($sqlbefehl);
      if (!$result) 
        return $this->error(mysql_error());
      else 
        return true;
  }

/**
  * Generiert einen Challenge für einen User und fügt Sie in die Datenbank.
  * @param string $userid ID des Users.
  * @return string|array Im Erfolgsfall challenge, ansonsten Array mit Errornachricht.
  */
  public function createChallenge($userid) {
    $this->removeChallenge($userid);
    $challenge = md5(rand() . time());
    $sqlbefehl = $this->buildInsertQuery('challenges', array(
        'userid' => $userid,
        'challenge' => $challenge
    )); 
        
    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else 
      return $challenge;
  }

/**
  * Holt challenge von einerm User aus der Datenbank.
  * @param string $userid ID des Users.
  * @return array 
  */
  public function getChallenge($userid) {
    $sqlbefehl = $this->buildSelectQuery('challenges', array('challenge'), array('userid' => $userid));
        
    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else if (mysql_num_rows($result) == 0) {
      return array();
    } else {
      return mysql_fetch_array($result);
    }
  }

/**
  * Löscht eine Challnege aus der Datenbank.
  * @param string $userid ID des Users.
  * @return bool|array Im Erfolgsfall true, ansonsten Array mit Errornachricht.
  */
  public function removeChallenge($userid) {
    $sqlbefehl = $this->buildDeleteQuery('challenges', array('userid' => $userid));
        
    $result = mysql_query($sqlbefehl);
    if (!$result)
      return $this->error(mysql_error());
    else
      return true; 
  }
/**
  * Sucht einen User anhand des Usernamens.
  * @param string $username Username des Users.
  * @return array 
  */
  public function getUserByUsername ($username) {
    $sqlbefehl = "SELECT * FROM user WHERE `username` = '".mysql_escape_string($username)."'";
        
    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else if (mysql_num_rows($result) == 0) {
      return array();
    } else {
      return mysql_fetch_array($result);
    }
  }

/**
  * Sucht einen User anhand der Emailadresse.
  * @param string $email Email des Users.
  * @return array 
  */
  public function getUserByEmail ($email) {
    $sqlbefehl = "SELECT * FROM user WHERE `email` = '".mysql_escape_string($email)."'";
        
    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else if (mysql_num_rows($result) == 0) {
      return array();
    } else {
      return mysql_fetch_array($result);
    }
  }

/**
  * Sucht einen User anhand der aktuellen Session.
  * @return array 
  */
  public function getLoginUser() {
    if (!isset($_SESSION['userid']))
      return false;

    $sqlbefehl = "SELECT `id` AS `userid`, `username`, `email` 
                  FROM user 
                  WHERE user.id = '".mysql_escape_string($_SESSION['userid'])."'  
                    AND user.activated = '1'";

    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else if (mysql_num_rows($result) == 0) {
      return false;
    } else {
      return mysql_fetch_array($result);
    }
  }

/**
  * Erstellt eine neue Session mit der angebenen Userid.
  * @param string $userid ID des Users.
  */
  public function createSession($userid) {
    $_SESSION['userid'] = $userid;
  }

/**
  * Löscht die aktuellen Sessioninformationen.
  */
  public function removeSession() {
    // Variablen mit leerem Array überschreiben
    $_SESSION = array();
    // Cookie löschen (Lebensdauer in der Verg.)
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-42000, '/'); }
    // Session löschen
    session_destroy();

    return true;

  }

/**
  * Erstellt eine Spiel mit den angebenen Daten.
  * @param string $name Name des Spiels
  * @param int $maxPlayers Maximale Anzahl der Spieler
  * @param string $p1color Farbe des ersten Spielers.
  * @param string $p2color Farbe des zweiten Spielers.
  * @return integer|array Im Erfolgsfall id des neuen Spiels, ansonsten Array mit Errornachricht.
  */
  public function createGame ($name, $maxPlayers, $p1color,$p2color) {
    $sqlbefehl = $this->buildInsertQuery('games', array(
      'name' => $name,
      'player1color' => $p1color,
      'player2color' => $p2color,
      'maxplayers' => $maxPlayers
    ));

    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else 
      return mysql_insert_id();
  }

/**
  * Setzt ein Spiel auf closed.
  * @param int $gameid ID des Spiels
  * @param int|null $winnderid ID des Gewinners oder null, falls kein Gewinner
  * @return bool|array Im Erfolgsfall true, ansonsten Array mit Errornachricht.
  */
  public function closeGame ($gameid,$winnerid = null) {
    if ($winnerid == null) {
      $values = array(
        'haswinner' => 0,
        'closed' => 1
      );
    } else {
      $values = array(
        'haswinner' => 1,
        'winner' => $winnerid,
        'closed' => 1
      );  
    }
    $sqlbefehl = $this->buildUpdateQuery('games',$values, array('id' => $gameid));

    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else 
      return true;
  }

  /**
    * Holt alle Spiele aus der Datenbank 
    * @return array
    */
  public function getGames() {
    $sqlbefehl = "SELECT * 
                  FROM games";

    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else {
      $array = array();
      while (($row = mysql_fetch_array($result))) {
        array_push($array, $row);  
      }
      return $array;
    }
  }

/**
  * Holt ein Spiel anhand der ID.
  * @param string $gameid ID des Spiels.
  * @return array 
  */
  public function getGame($gameid) {
    $sqlbefehl = "SELECT * FROM games WHERE `id` = '".mysql_escape_string($gameid)."'";
        
    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else if (mysql_num_rows($result) == 0) {
      return null;
    } else {
      return mysql_fetch_array($result);
    }
  }
/**
  * Gibt alle User die an einem Spiel teilnehmen zurück.
  * @param integer $gameid ID des Spiels.
  * @return array 
  */
  public function getUsersForGame($gameid) {
    $sqlbefehl = "SELECT user.id, user.email, user.username 
                  FROM user, current_players
                  WHERE user.id = current_players.userid
                    AND current_players.gameid = '". mysql_escape_string($gameid)."'";

    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else {
      $array = array();
      while (($row = mysql_fetch_array($result))) {
        array_push($array, $row);  
      }
      return $array;
    }
  }

  /**
  * Gibt alle Spiele an denen der User teilnimmt zurück.
  * @param integer $userid ID des Users.
  * @return array 
  */
  public function getGamesForUser($userid) {
    $sqlbefehl = "SELECT * 
                  FROM games, current_players
                  WHERE current_players.userid = '". mysql_escape_string($userid)."'
                  AND games.id = current_players.gameid
                  AND games.closed = 0";

    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else {
      $array = array();
      while (($row = mysql_fetch_array($result))) {
        array_push($array, $row);  
      }
      return $array;
    }
  }



/**
  * Weist einem User ein Spiel zu.
  * @param string $userid ID des Users.
  * @param string $gameid ID des Spiels.
  * @return bool|array Im Erfolgsfall true, ansonsten Array mit Errornachricht.
  */
  public function assignUserToGame($userid, $gameid) {
    $sqlbefehl = $this->buildInsertQuery('current_players', array(
      'userid' => $userid,
      'gameid' => $gameid
    ));

    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else 
      return true;
  }


/**
  * Gibt alle Spielzüge eines Spiels zurück.
  * @param string $userid ID des Users.
  * @param integer|0 $minId Min ID des Spielzugs.
  * @return array 
  */
  public function getGameMoves($gameid, $minId = 0) {
    $sqlbefehl = "SELECT * 
                  FROM moves
                  WHERE gameid = '". mysql_escape_string($gameid)."' 
                    AND id > ".mysql_escape_string($minId);

    $result = mysql_query($sqlbefehl);
    $array = array();
    if (!$result) 
      return $this->error(mysql_error());
    else {
      while (($row = mysql_fetch_array($result))) {
        array_push($array, $row);  
      }
      return $array;
    }

  }

/**
  * Gibt die Anzahl an Spielzügen eines Users bei einem Spiel zurück.
  * @param string $userid ID des Users.
  * @param string $gameid ID des Spiels.
  * @return array 
  */
  public function getNumGameMoves($gameid, $userid) {
    $sqlbefehl = "SELECT COUNT(*) AS num 
                  FROM moves
                  WHERE gameid = '". mysql_escape_string($gameid)."' 
                    AND userid =  '". mysql_escape_string($userid)."'";

    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else {
      return mysql_fetch_array($result);
    }

  }

/**
  * Erstellt einen Spielzug mit den angebenen Daten.
  * @param string $gameid ID des Spiels
  * @param string $userid ID des Spielers
  * @param int $x x Koordinate
  * @param int $y y Koordinate
  * @return integer|array Im Erfolgsfall id des neuen Spielzugs, ansonsten Array mit Errornachricht.
  */
  public function addMove($gameid, $userid, $x, $y) {
    $sqlbefehl = $this->buildInsertQuery('moves', array(
      'gameid' => $gameid,
      'userid' => $userid,
      'x' => $x,
      'y' => $y
    ));

    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else 
      return mysql_insert_id();
  }

/**
  * Überprüft ob Spieler mit letzem Zug gewonnen hat.
  * @param string $gameid ID des Spiels
  * @param string $userid ID des Spielers
  * @param int $x x Koordinate
  * @param int $y y Koordinate
  * @return bool
  */
  public function checkForWin($gameid, $userid, $x, $y) {
    $sqlbefehl = "SELECT * from moves WHERE `gameid`='$gameid' AND `userid` = '$userid'";

    
    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else {
      $moves = array();
      while (($row = mysql_fetch_array($result))) {
        $moves[$row['x']][$row['y']] = 1;
      }


      //Horizontal
      $horizontalCount = 0;
      //Count to left
      $i = $x;
      while (isset($moves[$i][$y]) && $moves[$i][$y] == 1 && $i > 0) { 
        $horizontalCount++;
        $i--;
      }
      //Count to right
      $i = $x + 1;
      while (isset($moves[$i][$y]) && $moves[$i][$y] == 1 && $i <= 7) { 
        $horizontalCount++;
        $i++;
      }

      if ($horizontalCount >= 4) {
        return true;
      }

      //vertical
      $verticalCount = 0;
      //Count to bottom
      $i = $y;
      while (isset($moves[$x][$i]) && $moves[$x][$i] == 1 && $i > 0) { 
        $verticalCount++;
        $i--;
      }
      //Count to top
      $i = $y + 1;
      while (isset($moves[$x][$i]) && $moves[$x][$i] == 1 && $i <= 6) { 
        $verticalCount++;
        $i++;
      }
      if ($verticalCount >= 4) {
        return true;
      }

      //Diagonal \
      $diag1Count = 0;
      //Count to topleft
      $i = $x;
      $j = $y;
      while (isset($moves[$i][$j]) && $moves[$i][$j] == 1 && $i > 0 && $j > 0) { 
        $diag1Count++;
        $i--;
        $j--;
      }
      //Count to bottom right
      $i = $x + 1;
      $j = $y + 1;
      while (isset($moves[$i][$j]) && $moves[$i][$j] == 1 && $i > 0 && $j > 0) { 
        $diag1Count++;
        $i++;
        $j++;
      }
      if ($diag1Count >= 4) {
        return true;
      }

      //Diagonal /
      $diag2Count = 0;
      //Count to topright
      $i = $x;
      $j = $y;
      while (isset($moves[$i][$j]) && $moves[$i][$j] == 1 && $i <= 7 && $j > 0) { 
        $diag2Count++;
        $i++;
        $j--;
      }
      //Count to bottom left
      $i = $x - 1;
      $j = $y + 1;
      while (isset($moves[$i][$j]) && $moves[$i][$j] == 1 && $i > 0 && $j <= 6) { 
        $diag2Count++;
        $i--;
        $j++;
      }
      if ($diag2Count >= 4) {
        return true;
      }

      return false;

    }
  }


  /*
   * Help functions
   */

  private function error($message) { 
    return array('error' => true, 'msg' => $message);
  }

  private function buildSelectQuery($table, $fields = null, $where = null) {    
    $sqlbefehl = "SELECT ";

    if ($fields == null) 
      $sqlbefehl = $sqlbefehl." * ";
    else
      foreach ($fields as $field) {
        $sqlbefehl = $sqlbefehl . "`".mysql_escape_string($field)."`,";
      }

    $sqlbefehl = rtrim($sqlbefehl, ',');

    $sqlbefehl  = $sqlbefehl . " FROM `$table`";

    if ($where != null) {
      $sqlbefehl = $sqlbefehl . " WHERE ";
      foreach ($where as $key => $value) {
        $sqlbefehl = $sqlbefehl . "`".mysql_escape_string($key)."`='".mysql_escape_string($value)."' AND ";
      }
    }

    $sqlbefehl = rtrim($sqlbefehl, "AND "); 

    return $sqlbefehl;
  }
  private function buildInsertQuery($table, $values) {
    $sqlbefehl = "INSERT INTO `$table` (";

    foreach ($values as $key => $value) {
      $sqlbefehl = $sqlbefehl . "`".mysql_escape_string($key)."`,";
    }

    $sqlbefehl = rtrim($sqlbefehl, ',');

    $sqlbefehl  = $sqlbefehl . ") VALUES (";

    foreach ($values as $key => $value) {
      $sqlbefehl = $sqlbefehl . "'".mysql_escape_string($value)."',";
    }
    
    $sqlbefehl = rtrim($sqlbefehl, ',');

    $sqlbefehl = $sqlbefehl . ")";

    return $sqlbefehl;
  }
  private function buildUpdateQuery($table, $values, $where) {
    $sqlbefehl = "UPDATE `$table` SET ";

    foreach ($values as $key => $value) {
      $sqlbefehl = $sqlbefehl . "`".mysql_escape_string($key)."`='".mysql_escape_string($value)."',";
    }

    $sqlbefehl = rtrim($sqlbefehl, ',');

    $sqlbefehl = $sqlbefehl . " WHERE ";

    foreach ($where as $key => $value) {
      $sqlbefehl = $sqlbefehl . "`".mysql_escape_string($key)."`='".mysql_escape_string($value)."' AND ";
    }

    $sqlbefehl = rtrim($sqlbefehl, 'AND ');

    return $sqlbefehl;
  }

  private function buildDeleteQuery($table, $where) {
    $sqlbefehl = "DELETE FROM `$table` WHERE ";

    foreach ($where as $key => $value) {
      $sqlbefehl = $sqlbefehl . "`".mysql_escape_string($key)."`='".mysql_escape_string($value)."' AND ";
    }

    $sqlbefehl = rtrim($sqlbefehl, 'AND ');

    return $sqlbefehl;
  }
}

?>