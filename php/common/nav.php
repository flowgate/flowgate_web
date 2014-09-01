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
    <title>FLOWGATE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="<?php echo $context; ?>../../css/merged.css" rel="stylesheet">
    <link href="<?php echo $context; ?>../../css/bootstrap.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">
    <style type="text/css">
      .alert { margin-bottom: 0 !important; }
      .navbarText {
        vertical-align:bottom;
        display:table-cell;
        height:40px; 
      }
    </style>
  </head>

  <body>
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container" style="width:100%;">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <!-- <a class="navbar-brand" href="#">CYBERFLOW</a> -->
          <a href="javascript:nav.boot('about');">
            <img src="<?php echo $context; ?>../../images/newlogo.png" width="150px" style="margin-top:13px;"/>
          </a>
        </div>
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav afterlogin" style="margin-left: 30px;">
            <li id="menuProject" class="menuItem menuProject menuFile menuResult"><a href="javascript:nav.boot('project');">Project</a></li>
            <li class="arrow-right"></li>
            <li id="menuFile" class="menuItem menuFile menuResult"><a href="javascript:nav.boot('file');">Dataset</a></li>
            <li class="arrow-right"></li>
            <li id="menuResult" class="menuItem menuResult"><a href="javascript:nav.boot('result');">Analysis</a></li>
          </ul>
          <ul class="nav pull-right afterlogin" style="display:none;">
            <li id="logout"><a href="<?php echo $context; ?>../view/logout.php">Log out</a></li>
          </ul>
          <ul class="nav navbar-nav beforelogin" style="margin-left: 30px;">
            <li>
              <div class="navbarText">
                <strong>A Workflow-Based Scientific Gateway for Computational Analysis of Flow Cytometry Data</strong>
              </div>
            </li>
          </ul>
          <ul class="nav navbar-nav pull-right beforelogin">
            <li id="menuAbout">
              <div class="navbarText" style="font-size:1.4em;">
                <a href="javascript:nav.boot('about');">
                  <strong>About</strong>
                </a>
              </div>
            </li>
          </ul>
          <!-- <ul class="nav navbar-nav pull-right beforelogin">
            <li id="menuLogin"><a href="javascript:nav.menu.boot('login');">Sign In</a></li>
          </ul> -->
        </div><!--/.nav-collapse -->
      </div>
    </div>
    <div id="pageAlert" style="margin: 15px 25px 0 25px !important;"></div>

    <!-- MODALS -->
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

    <!-- footer -->
    <!--<div id="footer" class="container">
      <nav class="navbar navbar-default navbar-fixed-bottom">
        <div class="navbar-inner navbar-content-center">
        </div>
      </nav>
    </div>
    -->
    <!-- END footer -->

    <script>
      var nav = {
        ct: '<?php echo $context;?>',
        contr: '<?php echo $context;?>controller.php?j=',
        boot: function(t) {
          window.location = nav.ct + (t==='about' ? '../../about.html' : '../view/' + t + '.php');
        }
      };

      var c_p = document.URL;
      //highlight current menu
      c_p = c_p.substring(c_p.lastIndexOf("/")+1, c_p.lastIndexOf("."));
      if(c_p) {
        $(".menuItem").hide(); //reset menu items

        if(c_p.indexOf("add") === 0) {
          c_p = c_p.substring(3); //add what
        } else if(c_p === "run") { //run belongs to result
          c_p = "result";
        } else if(c_p === "about") { //display project in about page, if the user is looged in 
          $(".menuProject").show();
        }

        c_p = c_p.charAt(0).toUpperCase() + c_p.slice(1);
        $(".menu" + c_p).show();
        $("#menu" + c_p).addClass("active");
      }

      //show/hide login/register menus
      (function() {
        var loggedIn = "<?php echo $loggedin;?>";
        if(loggedIn && loggedIn==='1') {
          $('.afterlogin').show();
          $('.beforelogin').hide();
        } else {
          $('.beforelogin').show();
          $('.afterlogin').hide();
          common.p.drop(); 
        }

        <?php
          $page = substr($_SERVER["HTTP_REFERER"], strrpos($_SERVER["HTTP_REFERER"], '/') + 1);
          if(isset($_SESSION['error'])) {
            echo("common.alert('".($page=="login.php"?"loginAlert":"pageAlert")."', 'e', '".$_SESSION['error']."');");
            unset($_SESSION['error']);
          }
        ?>
      })();
    </script>
  </body>
</html>