<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <link href="../../css/merged.css" rel="stylesheet">
  <style type="text/css">
    #imagebox {
      height:100%;
      overflow:hidden;
      padding-left:0 !important;  
      background-image: url(../../images/login_bg1.png);
      background-repeat: repeat;
      background-position: center center;
      background-attachment: fixed;
    }
  </style>
</head>

<body>
  <div id="nav"></div>
  <div class="container" style="width:100%;height:98.4%;background-color:#f8f8f8;">
    <div class="row" style="height:100%;">
      <div id="imagebox" class="col-sm-8">
        <!-- <div style="width:100%;height:100%;">
          <img src="../../images/login_bg1.png" style="width:100%;height:100%;"/>
        </div> -->
      </div>
      <div id="loginbox" class="col-sm-4">
        <div class="row">
          <div class="col-sm-12">
            <div class="alert alert-warning" style="margin-top: 5px;">
              The system is currently under testing and will be released in September 2014
            </div>
            <div id="loginAlert" style="margin-top: 5px;"></div>
            <div style="margin-bottom: 25px;">
              <h3>Sign In</h3>
            </div>
            <div style="display:none" id="login-alert" class="alert alert-danger col-sm-12"></div>
              <form id="loginform" class="form-horizontal" role="form" action="../common/controller.php?j=u_l" method="post">

                <div style="margin-bottom: 25px" class="input-group">
                  <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                  <input id="login-username" type="text" class="form-control" name="uname" value="" placeholder="email">                                        
                </div>

                <div style="margin-bottom: 25px" class="input-group">
                  <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                  <input id="login-password" type="password" class="form-control" name="pass" placeholder="password">
                </div>

                <div style="margin-top:10px" class="form-group">
                  <div class="col-sm-12 controls">
                    <input type="hidden" name="j" value="u_l" />
                    <button class="btn btn-primary btn-lg" type="submit">Login</button>
                    <!-- <a id="btn-login" href="#" class="btn btn-success">Login</a> -->
                  </div>
                </div>

                <div class="form-group" style="margin-top:25px;">
                  <div class="col-md-12 control">
                    <div style="border-top: 1px solid#888; padding-top:15px; font-size:1em;" >
                      Don't have an account?
                      <a href="#" onClick="window.location='register.php';">Sign Up Here</a>
                    </div>
                  </div>
                </div>    
              </form>                  
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../../js/jquery.min.js"></script>
  <script src="../../js/shared.js"></script>
  <script src="../../js/bootstrap.min.js"></script>
  <script>
    $(function(){
      $("#nav").load("../common/nav.php"); 
    });
  </script>
</body>
</html>