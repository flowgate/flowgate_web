<?php 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../../css/merged.css" rel="stylesheet">
  <style type="text/css">
  </style>
</head>

<body>
    <div id="nav"></div>
    <div class="container">
        <h3>Register</h3>
        <form action="../common/controller.php" method="post" class="form-horizontal" role="form"> 
            <input type="hidden" name="j" value="u_r" />
            <div class="form-group">
              <label for="uname" class="col-sm-2 control-label">Name<strong style="color:darkred;">*</strong></label>
              <div class="col-sm-10">
                <div class="row">
                  <div class="col-sm-6">
                    <input type="text" class="form-control" id="ufirst" name="ufirst" placeholder="First Name">
                  </div>
                  <div class="col-sm-6">
                    <input type="text" class="form-control" id="ulast" name="ulast" placeholder="Last Name">
                  </div>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="uemail" class="col-sm-2 control-label">Email<strong style="color:darkred;">*</strong></label>
              <div class="col-sm-10">
                <input type="email" class="form-control" id="uemail" name="uemail" placeholder="Email">
              </div>
            </div>
            <!-- <div class="form-group">
              <label for="uid" class="col-sm-2 control-label">ID<strong style="color:darkred;">*</strong></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="uid" name="uid" placeholder="USER ID">
              </div>
            </div> -->
            <div class="form-group">
              <label for="pass" class="col-sm-2 control-label">Password<strong style="color:darkred;">*</strong></label>
              <div class="col-sm-10">
                <input type="password" class="form-control" id="pass" name="pass" placeholder="Password">
              </div>
            </div>  
            <div class="form-group">
              <label for="uaffil" class="col-sm-2 control-label">Institution/Affiliation</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="uaffil" name="uaffil" placeholder="Institution or Affiliation">
              </div>
            </div>
            <button type="submit" class="btn btn-default" style="float:right;">Register</button> 
        </form>
    </div>

    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/shared.js"></script>
    <script src="../../js/bootstrap.min.js"></script>
    <script>
        (function(){
            $("#nav").load("../common/nav.php");
        })();
    </script>
</body>
</html>