<?php
class Model {

  public function __construct() {
    mysql_connect("localhost", "root", "root") or $this->error('Database Connection failed');
    mysql_select_db("viergewinnt") or $this->error('Database selection failed.');
  }


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

  public function removeChallenge($userid) {
    $sqlbefehl = $this->buildDeleteQuery('challenges', array('userid' => $userid));
        
    $result = mysql_query($sqlbefehl);
    if (!$result)
      return $this->error(mysql_error());
    else
      return true; 
  }

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

  public function getLoginUser($session_id) {
    $sqlbefehl = "SELECT `userid`, `username`, `email` 
                  FROM user, sessions 
                  WHERE user.id = sessions.userid  
                    AND user.activated = '1'
                    AND sessions.session = '".mysql_escape_string($session_id)."'";

    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else if (mysql_num_rows($result) == 0) {
      return array();
    } else {
      return mysql_fetch_array($result);
    }
  }

  public function createSession($userid, $session_id) {
    $sqlbefehl = $this->buildInsertQuery('sessions', array(
      'userid' => $userid,
      'session' => $session_id
    ));

    $result = mysql_query($sqlbefehl);
    if (!$result) 
      return $this->error(mysql_error());
    else 
      return true;
  }

  public function removeSession($userid, $session_id) {
    $sqlbefehl = $this->buildDeleteQuery('sessions', array('userid' => $userid, 'session' => $session_id));
        
    $result = mysql_query($sqlbefehl);
    if (!$result)
      return $this->error(mysql_error());
    else
      return true; 
  }

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

  public function getGameMoves($gameid, $minId = 0) {
    $sqlbefehl = "SELECT * 
                  FROM moves
                  WHERE gameid = '". mysql_escape_string($gameid)."' 
                    AND id > ".mysql_escape_string($minId);

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