<?php
    $taskId = $_GET['taskId'];

    `cp ../Tasks/$taskId/population_center.txt.orig ../Tasks/$taskId/population_center.txt`;

    `cd ../Tasks/$taskId;../../bin/cent_adjust population_center.txt coordinates.txt`;
    print "{\n";
    print "  success: true,\n";
    print "}\n";
?>
