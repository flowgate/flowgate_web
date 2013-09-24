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
              <li><a href="javascript:common.menu.boot('project');">Project</a></li>
              <li id="menuFile"><a href="javascript:common.menu.boot('file');">File</a></li>
              <li id="menuResult"><a href="javascript:common.menu.boot('result');">Result</a></li>
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

    <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
    <script>
      var common = {
        error: function(t,id,d) {
          var type = t==='e'?'alert':'warning';
          $(id).prepend('<div class="'+type+'">'+
              '<button type="button" class="close" data-dismiss="'+type+'">&times;</button>'+
              '<strong>'+d+'</strong></div>');
        },
        login: function(ismodal) {
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
                if(ismodal) {
                  var cf = function() {
                    window.location = '<?php echo $context; ?>../../index.html';
                  }
                  common.modal.error('Login Required!', 'Please login or register to gofcm.', cf);  
                }
              }
            },
            error: function() {
              loggedIn = false;
            }
          });
          return loggedIn;
        },
        modal: {
          error: function(t,d, cf) {
            $('#errorModal #title').html(t);
            $('#errorModal #desc').html(d);
            if(cf) {
              $('#errmClose').click(cf);
            }
            this.open('errorModal');
          },
          open: function(id) {
            $('#'+id).modal('show');
          },
          hide: function(id) {
            $('#'+id).modal('hide');
          }
        },
        menu: {
          boot: function(t) {
            if(common.login(true)) {
              if(t==='project') {
                project.get();
              } else if(t==='result') {
                this.result();
              } else if(t==='file') {
                this.file();
              }
            }
          },
          result: function() {
            window.location ='<?php echo $context; ?>../view/result.php?taskId=777';
          },
          file: function() {
            window.location ='<?php echo $context; ?>../view/file.php'; 
          }
        }
      };
      var project = {
        mid_s: 'projectSelectModal',
        mid_n: 'newProjectModal',
        pajax: function(t, d, cb) {
          $.ajax({
            type: "POST",
            async: false,
            url: "<?php echo $context; ?>controller.php?j=p_"+t,
            dataType: 'json',
            data: d,
            success: function (obj, ts) {
              if(cb)
                cb(obj, ts);
            },
            error: function() {}
          });
        },
        add: function() {
          var errId = '#'+this.mid_n+' .divDialogElements';
          var cb = function(obj, ts) {
            if(obj && obj['success']==true) {
              common.modal.hide('newProjectModal');
            } else {
              common.error('e', errId, obj['msg']);
            }
          };
          var pname = $('#newPname').val(), pdesc = $('#newPdesc').val();
          if(pname && pname.length>0) {
            this.pajax('a', {pname:pname, pdesc:pdesc}, cb);
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
            }
          }
          sessionStorage.setItem('gofcm.pid', $currp.val());
          sessionStorage.setItem('gofcm.pname', $currp.text());
          this.pajax('s', {pname:$currp.text(), pid:$currp.val()}, cb);
        },
        get: function() {
          var cb = function(obj, ts) {
            var $ps = $('#projectSelect'),
                opts='<option value="0">Select Project</option>';
            $.each(obj.projects, function(i, p) {
              opts+='<option value="'+p.p_id+'">'+p.p_name+'</option>';
            });
            $ps.html(opts);  
          };
          this.pajax('u', {}, cb);
          common.modal.open(this.mid_s);  
        }
      };

      var c_p = document.URL;
      //highlight current menu
      c_p = c_p.substring(c_p.lastIndexOf("/")+1, c_p.lastIndexOf("."));
      $("#menu" + (c_p==="result"?"Result":c_p==="file"?"File":c_p==="register"?"Register":"")).addClass("active");

      //show/hide login/register menus
      $(function(){
        if(common.login(false)) {
          $('#logged').show();
          $('#nologged').hide();
        } else {
          $('#nologged').show();
          $('#logged').hide(); 
          sessionStorage.removeItem("gofcm.pid");
          sessionStorage.removeItem("gofcm.pname");
        } 
      });
    </script>
  </body>
</html>