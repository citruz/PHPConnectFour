<?php
include 'controller.php';
session_start();

$controller = new Controller();
$controller->checkLogin(false, false);

if (isset($_POST['username']))
  $_GET['msg'] = $controller->login($_POST);


if (isset($_GET['msg']))
echo $_GET['msg'];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>VierGewinnt Login</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <style type="text/css">
      /* Override some defaults */
      html, body {
        background-color: #eee;
      }
      body {
        padding-top: 40px; 
      }
      .container {
        width: 550px;
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

    </style>

</head>
<body>
  <div class="container">
    <div class="content">
        <div class="" id="loginModal">
          <div class="modal-header">
            <h3>Vier Gewinnt</h3>
          </div>
          <div class="modal-body">
            <div class="well">
              <ul class="nav nav-tabs">
                <li class="active"><a href="#login" data-toggle="tab">Login</a></li>
                <li><a href="#create" data-toggle="tab">Registrieren</a></li>
              </ul>
              <div id="myTabContent" class="tab-content">
                <div class="tab-pane active in" id="login">
                  <form class="form-horizontal" action='' method="POST">
                    <fieldset>
                      <div class="control-group">
                        <!-- Username -->
                        <label class="control-label"  for="username">Username</label>
                        <div class="controls">
                          <input type="text" id="username" name="username" placeholder="" class="input-xlarge">
                        </div>
                      </div>
 
                      <div class="control-group">
                        <!-- Password-->
                        <label class="control-label" for="password">Passwort</label>
                        <div class="controls">
                          <input type="password" id="password" name="password" placeholder="" class="input-xlarge">
                        </div>
                      </div>
 
 
                      <div class="control-group">
                        <!-- Button -->
                        <div class="controls">
                          <button class="btn btn-success">Login</button>
                        </div>
                      </div>
                    </fieldset>
                  </form>                
                </div>
                <div class="tab-pane fade" id="create">
                  <form id="tab" class="form-horizontal" method="post" action="register.php">
                      <div class="control-group">
                        <!-- Username -->
                        <label class="control-label" for="username">Username</label>
                        <div class="controls">
                          <input type="text" value="" class="input-xlarge" name="username">
                        </div>
                      </div>
                      <div class="control-group">
                        <label class="control-label" for="email">Email</label>
                        <div class="controls">
                          <input type="text" value="" class="input-xlarge" name="email">
                        </div>
                      </div>
                      <div class="control-group">
                        <label class="control-label" for="password">Passwort</label>
                        <div class="controls">
                          <input type="password" value="" class="input-xlarge" name="password">
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
    </div>
  </div> <!-- /container -->
  <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
  <script src="css/bootstrap/js/bootstrap.js"></script>
</body>
</html>