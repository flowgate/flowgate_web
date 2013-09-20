<?php
  $context = dirname($_SERVER['SCRIPT_NAME']).'/';
  header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>gofcm</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="<?php echo $context; ?>../../css/merged.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
  </head>

  <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="#">gofcm</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li><a href="javascript:common.menu.boot('project');">Project</a></li>
              <li id="menuFile"><a href="#about">File</a></li>
              <li id="menuResult"><a href="javascript:common.menu.boot('result');">FLOCK Result</a></li>
            </ul>
            <ul id="logged" class="nav pull-right" style="display:none;">
              <li id="logout"><a href="<?php echo $context; ?>../view/logout.php">LogOut</a></li>
            </ul>
            <ul id="nologged" class="nav pull-right">
              <li id="menuRegister"><a href="<?php echo $context; ?>../view/register.php">Register</a></li>
              <li class="dropdown">
                <a class="dropdown-toggle" href="#" data-toggle="dropdown">Log In <strong class="caret"></strong></a>
                <div class="dropdown-menu" role="menu" style="padding: 15px; padding-bottom: 0px;">
                  <form action="<?php echo $context; ?>controller.php" method="post"> 
                    Username:<input type="text" name="uname" value="" /><br/> 
                    Password:<input type="password" name="pass" value="" />
                    <input type="hidden" name="j" value="u_l" /> 
                    <input type="submit" class="btn btn-info" value="Login" /> 
                  </form> 
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- project modals -->
    <div id="projectSelectModal" class="modal hide fade">
      <div class="modal-header">
        <a href="#" class="close" data-dismiss="modal">&times;</a>
        <h3 id="prompt">Select a project:</h3>
      </div>
      <div class="modal-body">
        <div class="divDialogElements">
          <div class="divPopupMenu">
            <div class="input">
              <select class="medium" name="mediumSelect" id="projectSelect">
                <option id="woOptionblogpost">project1</option>
                <option id="woOptionblogpost">project2</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <a href="#" class="btn btn-primary" onclick="common.project.add()">Add Project</a>
        <a href="#" class="btn btn-primary" onclick="OKClicked ()">OK</a>
      </div>
    </div>
    <div id="newProjectModal" class="modal hide fade">
      <div class="modal-header">
        <a href="#" class="close" data-dismiss="modal">&times;</a>
        <h3 id="prompt">Create new project: </h3>
      </div>
      <div class="modal-body">
        <div class="divDialogElements">
          <input class="xlarge" id="newProjectName" name="newProjectName" type="text" />
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <a href="#" class="btn btn-primary" onclick="">OK</a>
      </div>
    </div>

    <!-- login modal -->
    <div id="loginAlert" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel" class="text-error">Login Required!</h3>
      </div>
      <div class="modal-body">
        <h5>Please login or register to gofcm.</h5>
      </div>
      <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true" onclic="">Close</button>
      </div>
    </div>


    <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
    <script>
      var common = { 
        login: function() {
          var loggedIn = false;
          $.ajax({
            type: "POST",
            async: false,
            url: "<?php echo $context; ?>controller.php",
            dataType: 'json',
            data: {j: 'u_'},
            success: function (obj, textstatus) {
              loggedIn = obj['success'];
              if(!loggedIn) {
                $('#loginAlert').modal('show');  
              }
            },
            error: function() {
              loggedIn = false;
            }
          });
          return loggedIn;
        },
        project: {
          add: function() {
            common.modal.hide('projectSelectModal');
            common.modal.open('newProjectModal');   
          }
        },
        modal: {
          open: function(id) {
            $('#'+id).modal('show');
          },
          hide: function(id) {
            $('#'+id).modal('hide');
          }
        },
        menu: {
          boot: function(t) {
            if(common.login()) {
              if(t==='project') {
                this.project();
              } else if(t==='result') {
                this.result();
              }
            }
          },
          result: function() {
            window.location ='<?php echo $context; ?>../view/result.php?taskId=777';
          },
          project: function() {
            common.modal.open('projectSelectModal');  
          }
        }
      }, c_p = document.URL;

      //highlight current menu
      c_p = c_p.substring(c_p.lastIndexOf("/")+1, c_p.lastIndexOf("."));
      $("#menu" + 
          (c_p==="result"?"Result":c_p==="files"?"File":c_p==="register"?"Register":"")
      ).addClass("active");

      //show/hide login/register menus
      $(function(){
        if(common.login()) {
          $('#logged').show();
          $('#nologged').hide();
        } else {
          $('#nologged').show();
          $('#logged').hide(); 
        } 
      });
    </script>
  </body>
</html>