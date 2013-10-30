<?php 
    require("../common/session.php"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>mockup with bootstrap</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../../css/merged.css" rel="stylesheet">
  <link href="../../css/bootstrap-select.css" rel="stylesheet">
  <style type="text/css">
  </style>
</head>

<body>
    <div id="nav"></div>
    <div class="container hero-unit">
        <h3>Register</h3>
        <form action="register.php" method="post"> 
            <label>Name:<strong style="color:darkred;">*</strong></label> 
            <input type="text" name="uname" value="" /> 
            <label>Email: <strong style="color:darkred;">*</strong></label> 
            <input type="text" name="uemail" value="" />
            <label>ID:<strong style="color:darkred;">*</strong></label> 
            <input type="text" name="uid" value="" />  
            <label>Password:<strong style="color:darkred;">*</strong></label> 
            <input type="password" name="pass" value="" />
            <label>Affilate:</label> 
            <input type="text" name="uaffil" value="" /> <br /><br />
            <input type="submit" class="btn btn-info" value="Register" /> 
        </form>
    </div>

    <script src="../../js/jquery.min.js"></script>
    <script>
        $(function(){
            $("#nav").load("../common/nav.php");
        });
    </script>
</body>
</html>