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
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
    <style>
      .hero-unit { padding: 10px 60px !important; }
    </style>
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
              <li><a href="javascript:nav.menu.boot('project');">Project</a></li>
              <li id="menuFile"><a href="javascript:nav.menu.boot('file');">File</a></li>
              <li id="menuResult"><a href="javascript:nav.menu.boot('result');">Result</a></li>
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

    <!-- MODALS -->
    <!-- project -->
    <div id="projectSelectModal" class="modal hide fade">
      <div class="modal-header">
        <a href="#" class="close" data-dismiss="modal">&times;</a>
        <h3 id="prompt">Select a project:</h3>
      </div>
      <div class="modal-body">
        <div class="divDialogElements">
          <div class="divPopupMenu">
            <div class="input">
              <select class="medium" name="mediumSelect" id="projectSelect"></select>
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
    <div id="newProjectModal" class="modal hide fade">
      <div class="modal-header">
        <a href="#" class="close" data-dismiss="modal">&times;</a>
        <h3 id="prompt">Create new project </h3>
      </div>
      <div class="modal-body">
        <div class="divDialogElements">
          Project Name: <input class="xlarge" id="newPname" name="newPname" type="text" /><br/>
          Description: <input class="xlarge" id="newPdesc" name="newPdesc" type="text" />
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <a href="#" class="btn btn-primary" onclick="project.add();">Add</a>
      </div>
    </div>

    <!-- error -->
    <div id="errorModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="title" aria-hidden="true">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="title" class="text-error"></h3>
      </div>
      <div class="modal-body">
        <h5 id="desc"></h5>
      </div>
      <div class="modal-footer">
        <button id="errmClose" class="btn" data-dismiss="modal" aria-hidden="true" onclick="">Close</button>
      </div>
    </div>
    <!-- END MODALS -->

    <script src="<?php echo $context;?>../../js/bootstrap.min.js"></script>
    <script src="<?php echo $context;?>../../js/shared.js"></script>
    <script>
      var nav = {
        ct: '<?php echo $context;?>',
        contr: '<?php echo $context;?>controller.php?j=',
        login: function() {
          var loggedIn = false;
          $.ajax({
            type: "POST",
            async: false,
            url: nav.contr+'u_',
            dataType: 'json',
            data: {},
            success: function (obj, ts) {
              loggedIn = obj['success'];
              if(!loggedIn) {
                var cf = function() {
                  window.location = nav.ct+'../../index.html';
                }
                common.modal.error('Login Required!', 'Please login or register to gofcm.', cf);  
              }
            },
            error: function() {
              loggedIn = false;
            }
          });
          return loggedIn;
        },
        menu: {
          boot: function(t) {
            if(nav.login()) {
              if(t==='project') {
                project.get();
              } else {
                project.forceSelect();
                if(t==='result') {
                  this.result();
                } else if(t==='file') {
                  this.file();
                }
              }
            }
          },
          result: function() {
            window.location =nav.ct+'../view/result.php';
          },
          file: function() {
            window.location =nav.ct+'../view/file.php'; 
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
            common.ajax('p', nav.contr+'p_a', {pname:pname, pdesc:pdesc}, cb);
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
          common.ajax('p', nav.contr+'p_s', {pname:$currp.text(), pid:$currp.val()}, cb);
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
          common.ajax('g', nav.contr+'p_u', {}, cb);
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
          $('#nologged').show();
          $('#logged').hide();
          common.p.drop(); 
        } 
      });
    </script>
  </body>
</html>