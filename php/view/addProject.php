<?php 
    include("../common/session.php"); 
    isSessionAlive();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../../css/merged.css" rel="stylesheet">
  <link href="../../css/bootstrap-select.css" rel="stylesheet">
  <style type="text/css">
  </style>
</head>

<body>
    <div id="nav"></div>
    <div class="container">
        <h3>Add Project</h3>
        
        <form action="../common/controller.php" method="post" class="form-horizontal" role="form"> 
            <input type="hidden" name="j" value="p_a" />
            <div class="form-group">
              <label for="uname" class="col-sm-2 control-label">Project Name<strong style="color:darkred;">*</strong></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="pname" name="pname" placeholder="project name">
              </div>
            </div>
            <div class="form-group">
              <label for="newPdesc" class="col-sm-2 control-label">Description</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="pdesc" name="pdesc" placeholder="description">
              </div>
            </div>
            <div style="float:right;">
              <button type="button" class="btn btn-default" onclick="window.location='project.php';">Cancel</button>
              <button type="submit" class="btn btn-primary">Add</button>
            </div>
        </form>
    </div>

    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/shared.js"></script>
    <script src="../../js/bootstrap.min.js"></script>
    <script>
        (function() {
          $("#nav").load("../common/nav.php");
        })();
    </script>
</body>
</html>