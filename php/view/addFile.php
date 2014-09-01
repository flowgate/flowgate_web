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
    <h3 id="pageHeader">Upload Dataset</h3>
    
    <form action="../common/controller.php" enctype="multipart/form-data" method="post" class="form-horizontal" role="form"> 
      <input type="hidden" name="j" value="f_a" />
      <div class="form-group">
        <label for="dataset" class="col-sm-2 control-label">Dataset<strong style="color:darkred;">*</strong></label>
        <div class="col-sm-10">
          <input type="file" class="form-control" id="dataset" name="dataset" placeholder="select a file...">
        </div>
      </div>
      <div class="form-group">
        <label for="fdesc" class="col-sm-2 control-label">Description</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="fdesc" name="fdesc" placeholder="description">
        </div>
      </div>

      <div style="float:right;">
        <button type="button" class="btn btn-default" onclick="window.location='file.php';">Cancel</button>
        <button type="submit" class="btn btn-primary">Upload</button>
      </div>
    </form>
  </div>

  <script src="../../js/jquery.min.js"></script>
  <script src="../../js/shared.js"></script>
  <script src="../../js/bootstrap.min.js"></script>
  <script>
      (function() {
        $("#nav").load("../common/nav.php");

        common.p.toHeader();
        $('<input>').attr({type: 'hidden', name: 'pid', value: common.ss_g(common.p.id) }).appendTo('form');
      })();
  </script>
</body>
</html>