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
      return true;
  } 

  public function updateUser($userid,$username, $email, $password) {
    $sqlbefehl = $this->buildUpdateQuery('user',array(
        'username' => $username,
        'email' => $email,
        'password' => md5($password)
      ),
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
    $sqlbefehl = buildDeleteQuery('challenges', array('userid' => $userid));
        
    $result = mysql_query($sqlbefehl);
    if (!$result)
      return $this->error(mysql_error());
    else
      return true; 
  }

  public function getUserByUsername ($username) {
    $sqlbefehl = "SELECT * FROM user WHERE `username` = '$username'";
        
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

  public function createGame ($name, $maxPlayers) {
    $sqlbefehl = $this->buildInsertQuery('games', array(
      'name' => $name,
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
                  AND games.id = current_players.gameid";

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