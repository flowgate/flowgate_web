<?php
  $context = dirname($_SERVER['SCRIPT_NAME']).'/';
  header('Content-Type: text/html; charset=utf-8');
  session_start();
  $loggedin = (isset($_SESSION['authenticated']) && $_SESSION['authenticated']=="true" && isset($_SESSION['userId']));
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>gofcm</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="<?php echo $context; ?>../../css/merged.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">
    <style>
      .jumbotron { padding: 10px 60px !important; }
    </style>
  </head>

  <body>
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">gofcm</a>
        </div>
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li><a href="javascript:nav.menu.boot('project');">Project</a></li>
            <li id="menuFile"><a href="javascript:nav.menu.boot('file');">File</a></li>
            <li id="menuResult"><a href="javascript:nav.menu.boot('result');">Result</a></li>
          </ul>
          <ul id="logged" class="nav pull-right" style="display:none;">
            <li id="logout"><a href="<?php echo $context; ?>../view/logout.php">LogOut</a></li>
          </ul>
          <ul id="nologged" class="nav navbar-nav pull-right">
            <li id="menuRegister"><a href="<?php echo $context; ?>../view/register.php">Register</a></li>
            <li class="dropdown">
              <a class="dropdown-toggle" href="#" data-toggle="dropdown">Log In <strong class="caret"></strong></a>
              <div class="dropdown-menu" role="menu" style="padding: 15px;">
                <form action="<?php echo $context; ?>controller.php" method="post"> 
                  Username:<input type="text" name="uname" value="" /><br/> 
                  Password:<input type="password" name="pass" value="" />
                  <input type="hidden" name="j" value="u_l" /> 
                  <input type="submit" class="btn btn-info" style="margin-top:5px;" value="Login" /> 
                </form> 
              </div>
            </li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <!-- MODALS -->
    <!-- project -->
    <div class="modal fade" id="projectSelectModal" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="prompt">Select or Change Project</h4>
          </div>
          <div class="modal-body">
            <div class="divDialogElements">
              <div class="divPopupMenu">
                <div class="input">
                  <select class="form-control" name="projectSelect" id="projectSelect"></select>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
            <a href="#" class="btn btn-primary" onclick="project.new();">Add Project</a>
            <a href="#" class="btn btn-primary" onclick="project.select();">OK</a>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="newProjectModal" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="prompt">Create new project</h4>
          </div>
          <div class="modal-body">
            <div class="divDialogElements">
                <label for="newPname" class="control-label">Project Name</label>
                <div><input type="text" class="form-control" id="newPname" placeholder="Project Name"></div>
                <label for="newPdesc" class="control-label">Description</label>
                <div><input type="text" class="form-control" id="newPdesc" placeholder="Project Description"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            <a href="#" class="btn btn-primary" onclick="project.add();">Add</a>
          </div>
        </div>
      </div>
    </div>

    <!-- error -->
    <div class="modal fade" id="errorModal" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title text-danger" id="title"></h4>
          </div>
          <div class="modal-body">
            <h5 id="desc"></h5>
          </div>
          <div class="modal-footer">
            <button id="errmClose" type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true" onclick="">Close</button>
          </div>
        </div>
      </div>
    </div>
    <!-- END MODALS -->

    <script>
      var nav = {
        ct: '<?php echo $context;?>',
        contr: '<?php echo $context;?>controller.php?j=',
        menu: {
          boot: function(t) {
            var cb = function(obj) {
              var userLogged = obj['success'];
              if(!userLogged) {
                var cf = function() {
                  window.location = nav.ct+'../../index.html';
                }
                common.modal.error('Login Required!', 'Please login or register to gofcm.', cf);  
              } else{
                if(t==='project') {
                  project.get();
                } else {
                  project.forceSelect(); //force user to select a project
                  if(t==='result') {
                    window.location =nav.ct+'../view/result.php';
                  } else if(t==='file') {
                    window.location =nav.ct+'../view/file.php'; 
                  }
                }
              }
            }

            makeAjaxCall('g', nav.contr+'u_', {}, cb);
          }
        }
      };
      var project = {
        mid_s: 'projectSelectModal',
        mid_n: 'newProjectModal',
        add: function() {
          var errId = '#'+this.mid_n+' .divDialogElements';
          var cb = function(obj, ts) {
            if(obj && obj['success']==true) {
              common.p.set(obj['newpid'], obj['newpname']);
              common.modal.hide('newProjectModal');
            } else {
              common.error('e', errId, obj['error']['msg']);
            }
          };
          var pname = $('#newPname').val(), pdesc = $('#newPdesc').val();
          if(pname && pname.length>0) {
            makeAjaxCall('p', nav.contr+'p_a', {pname:pname, pdesc:pdesc}, cb);
          } else {
            common.error('e', errId, 'Project name is empty!');
          }
        },
        new: function() {
          common.modal.hide(this.mid_s);
          common.modal.open(this.mid_n);   
        },
        select: function() {
          var $ps = $('#projectSelect'), $currp = $('option:selected', $ps);
          var cb = function(obj, ts) {
            if(obj['success']==true) {
              common.modal.hide(project.mid_s);
              if($('li.active').attr('id') === 'menuFile') { //update file list after changing project
                _page.getFiles();
              }  
            }
          }
          common.p.set($currp.val(),$currp.text());
          makeAjaxCall('p', nav.contr+'p_s', {pname:$currp.text(), pid:$currp.val()}, cb);
        },
        get: function() {
          var cb = function(obj, ts) {
            var $ps = $('#projectSelect'),
                opts='<option value="0">Select Project</option>';
            $.each(obj.projects, function(i, p) {
              opts+='<option value="'+p.datasetID+'">'+p.datasetName+'</option>';
            });
            $ps.html(opts);  
          };
          makeAjaxCall('g', nav.contr+'p_u', {}, cb);
          common.modal.open(this.mid_s);  
        },
        forceSelect: function() { //force user to select a project
          if(!common.ispset()) {
            this.get(); 
          }
        }
      };

      var c_p = document.URL;
      //highlight current menu
      c_p = c_p.substring(c_p.lastIndexOf("/")+1, c_p.lastIndexOf("."));
      $("#menu" + (c_p==="result"?"Result":c_p==="file"?"File":c_p==="register"?"Register":"")).addClass("active");

      //show/hide login/register menus
      $(function(){
        var loggedIn = "<?php echo $loggedin;?>";
        if(loggedIn && loggedIn==='1') {
          $('#logged').show();
          $('#nologged').hide();
          project.forceSelect();
        } else {

          <?php 
            if(isset($_SESSION['error'])) {
              error_log("common.modal.error('Login Failed!', ".$_SESSION['error'].", null);");
              echo("common.modal.error('Login Failed!', '".$_SESSION['error']."', null);");
              unset($_SESSION['error']); 
            }
          ?>
          $('#nologged').show();
          $('#logged').hide();
          common.p.drop(); 
        } 
      });
    </script>
  </body>
</html>