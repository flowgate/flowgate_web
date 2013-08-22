<?php
    $taskId = $_GET['taskId'];
    $popIdx = $_GET['popIdx'];
//    $taskId = 1;
//    $popIdx = 2;

    `mv ../Tasks/$taskId/population_center.txt ../Tasks/$taskId/population_center.txt.bck`;

    $fpw = fopen("../Tasks/$taskId/population_center.txt",'w');
    $fpr = fopen("../Tasks/$taskId/population_center.txt.bck",'r');
    if (!$fpr) {
        echo "Unable to open file for reading";
        exit;
    }
    if (!$fpw) {
        echo "Unable to open file for writing";
        exit;
    }

    $cnt = 0;
    while(!feof($fpr)) {
        $line = fgets($fpr,999);
        if ($line) {
            $values = explode("\t",$line);
            if ($popIdx != $values[0]) {
                fwrite($fpw,$line);
            }
        }
    }

    fclose($fpw);
    fclose($fpr);

    `cd ../Tasks/$taskId;../../bin/cent_adjust population_center.txt coordinates.txt`;

    $fpr = fopen("../Tasks/$taskId/population_center.txt",'r');
    if (!$fpr) {
        echo "Unable to open file for writing";
        exit;
    }
    $line = fgets($fpr,999);
    $line = rtrim($line,"\n");
	$line = rtrim($line,"\r");
    $values = explode("\t",$line);

    print "{\n";
    print "  success: true,\n";
    print "  popId: '$values[0]'\n";
    print "}\n";
?>
