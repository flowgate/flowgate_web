<?php
  $context = dirname($_SERVER['SCRIPT_NAME']).'/';
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
              <li><a data-toggle="modal" href="#projectSelectModal">Project</a></li>
              <li id="menuFile"><a href="#about">File</a></li>
              <li id="menuResult"><a href="<?php echo $context; ?>../view/result.php?taskId=777">FLOCK Result</a></li>
            </ul>
            <ul class="nav pull-right">
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
        <a href="#" class="btn" onclick="common.modal.close('projectSelectModal')">Cancel</a>
        <a href="#" class="btn btn-primary" onclick="common.modal.add()">Add Project</a>
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
        <a href="#" class="btn" onclick="common.modal.close('newProjectModal')">Cancel</a>
        <a href="#" class="btn btn-primary" onclick="">OK</a>
      </div>
    </div>

    <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
    <script>
      var common = { 
        modal: {
          add: function() {
            $('#projectSelectModal').modal('hide');
            $('#newProjectModal').modal('show');  
          },
          close: function(id) {
            $('#'+id).modal('hide');
          }
        }
      }, c_p = document.URL;

      c_p = c_p.substring(c_p.lastIndexOf("/")+1, c_p.lastIndexOf("."));
      $("#menu" + 
          (c_p==="result"?"Result":c_p==="files"?"File":c_p==="register"?"Register":"")
      ).addClass("active");
    </script>
  </body>
</html>