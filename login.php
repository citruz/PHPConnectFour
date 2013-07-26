<?php
include 'controller.php';
session_start();

$controller = new Controller();
$controller->checkLogin(false, false);

if (isset($_POST['username']))
  $_GET['msg'] = $controller->login($_POST);

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>VierGewinnt Login</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href='http://fonts.googleapis.com/css?family=Yanone+Kaffeesatz:400,200,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>

    <style type="text/css">
      /* Override some defaults */
      html, body {
        background-color: #eee;
        font-family: 'Yanone Kaffeesatz', Helvetica, Verdana, Arial, sans-serif;
        font-size: 17px;
      }
      body {
        padding-top: 40px; 
      }
      label {
        font-size: 16px;
      }
      .container {
        width: 550px;
        margin-right: auto;
        margin-left: auto;
      }

      /* The white background content wrapper */
      .container > .content {
        background-color: #fff;
        padding: 20px;
        margin: 0 -20px; 
        -webkit-border-radius: 10px 10px 10px 10px;
           -moz-border-radius: 10px 10px 10px 10px;
                border-radius: 10px 10px 10px 10px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.15);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.15);
                box-shadow: 0 1px 2px rgba(0,0,0,.15);
      }
      .login-form {
      margin-left: 65px;
      }
    
      legend {
      margin-right: -50px;
      font-weight: bold;
        color: #404040;
      }
      .modal-body { padding: 0; }
      a {
        color: #f89406;
      }
      a:hover {
        color: #AAA;
      }
    </style>

</head>
<body>
  <div class="container">
    <div class="content">
      <h1>Vier Gewinnt</h1>

      <?php if (isset($_GET['errmsg'])) { ?>
        <div class="alert alert-error">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <strong>Fehler!</strong> <?php echo htmlentities($_GET['errmsg'], ENT_COMPAT, "UTF-8"); ?>
        </div>
      <?php }?>

      <?php if (isset($_GET['msg'])) { ?>
        <div class="alert">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <strong>Hinweis:</strong> <?php echo htmlentities($_GET['msg'], ENT_COMPAT, "UTF-8"); ?>
        </div>
      <?php }?>

      <div class="modal-body">
        <div class="well">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#login" data-toggle="tab">Login</a></li>
            <li><a href="#create" data-toggle="tab">Registrieren</a></li>
          </ul>
          <div id="myTabContent" class="tab-content">

            <div class="tab-pane active in" id="login">
            <?php if (isset($_GET['challenge'])) { ?>
              <div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Erfolg!</strong> <a href="view.php?action=challenge&challenge=<?php echo $_GET['challenge']; ?>&userid=<?php echo $_GET['userid']; ?>">Challenge akzeptieren</a>
              </div>
            <?php }?>

              <form class="form-horizontal" action="view.php?action=login" method="POST">
                <fieldset>
                  <div class="control-group">
                    <!-- Username -->
                    <label class="control-label"  for="username">Username</label>
                    <div class="controls">
                      <input type="text" id="username" name="username" placeholder="" class="input-xlarge" required>
                      

                    </div>
                  </div>

                  <div class="control-group">
                    <!-- Password-->
                    <label class="control-label" for="password">Passwort</label>
                    <div class="controls">
                      <input type="password" id="password" name="password" placeholder="" class="input-xlarge" required>
                    </div>
                  </div>

                  <input type="hidden" value="<?php echo htmlentities($_GET['ref'], ENT_COMPAT, "UTF-8"); ?>" name="ref" />
                  <div class="control-group">
                    <!-- Button -->
                    <div class="controls">
                      <button class="btn btn-warning">Login</button>
                    </div>
                  </div>
                </fieldset>
              </form>                
            </div>
            <div class="tab-pane fade" id="create">
            <?php if (isset($_GET['regerror'])) { ?>
              <div class="alert alert-error">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Fehler!</strong> <?php echo htmlentities($_GET['regerror'], ENT_COMPAT, "UTF-8"); ?>
              </div>
            <?php }?>

              <form id="tab" class="form-horizontal" method="post" action="view.php?action=register">
                  <div class="control-group">
                    <!-- Username -->
                    <label class="control-label" for="username">Username</label>
                    <div class="controls">
                      <input type="text" value="<?php if (isset($_SESSION['temp_user'])) echo $_SESSION['temp_user'];?>" class="input-xlarge" name="username" required>
                      <small class="help-block">Mindestens 3, maximal 50 Zeichen.</small>
                    </div>
                  </div>
                  <div class="control-group">
                    <label class="control-label" for="email">Email</label>
                    <div class="controls">
                      <input type="email" value="<?php if (isset($_SESSION['temp_email'])) echo $_SESSION['temp_email'];?>" class="input-xlarge" name="email" required>
                    </div>
                  </div>
                  <div class="control-group">
                    <label class="control-label" for="password">Passwort</label>
                    <div class="controls">
                      <input type="password" value="" class="input-xlarge" name="password" required>
                      <small class="help-block">Mindestens 6, maximal 50 Zeichen.</small>
                    </div>
                  </div>
                <div>
                <div class="control-group">
                  <!-- Button -->
                  <div class="controls">
                    <button class="btn btn-primary">Account erstellen</button>
                  </div>
                </div>
              </form>
            </div>
        </div>
    </div>
    </div>
  </div> <!-- /container -->
  <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
  <script src="css/bootstrap/js/bootstrap.js"></script>
  <?php if (isset($_GET['regerror'])) { ?>
    <script type="text/javascript">
      $(document).ready(function(){
        $('ul.nav-tabs a:last').tab('show');
      });
    </script>
  <?php } ?>
</body>
</html>