<?php
    $taskId = $_GET['taskId'];
    $imageName = $_GET['imageName'];

    $im = file_get_contents("../Tasks/$taskId/$imageName");
    header('content-type: image/png');
    echo $im; 

?>
