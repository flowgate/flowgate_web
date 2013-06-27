<?php
    require("./pages/common.php");
    reqValidation();
?>
<!DOCTYPE html>

<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="ext-2.2/resources/css/ext-all.css">
    <link rel="stylesheet" type="text/css" href="css/merged.css">
    <title id="page-title">Flock Main</title>
</head>
<body>
    <div id="flock_main">
        <div id="toolbars">
            <div id="main_toolbar"></div>
            <div id="ras_toolbar"></div>
        </div>
        <div id="flock_content"></div>
    </div>

    <script src="ext-2.2/adapter/jquery/jquery.js"></script>
    <script src="ext-2.2/adapter/jquery/ext-jquery-adapter.js"></script>
    <script src="ext-2.2/adapter/ext/ext-base.js"></script>
    <script src="ext-2.2/ext-all.js"></script>
    <script src="js/FileUploadField.js"></script>
    <script src="js/columnLock.js"></script>
    <!--<script src="js/RAS.js"></script>-->
    <script src="js/handler.js"></script>
    <script>
        Ext.onReady(function(){
            Ext.QuickTips.init();
            loadMainToolBar();
        });
    </script>
</body>
</html>
