<?php
    $taskId = $_GET['taskId'];
    $idx1 = $_GET['idx1'];
    $idx2 = $_GET['idx2'];
    $popIdx = $_GET['popIdx'];
    $xValue = $_GET['xValue'];
    $yValue = $_GET['yValue'];

//  $idx1 = 4;
//  $idx2 = 2;
//  $popIdx = 4;
//  $xValue = 100;
//  $yValue = 100;

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
            $line = rtrim($line,"\n");
			$line = rtrim($line,"\r");
            $values = explode("\t",$line);

            if ($popIdx == $values[0]) {
                $values[$idx1+1] = $xValue;
                $values[$idx2+1] = $yValue;
                fwrite($fpw,join("\t",$values) . "\n");
            } else {
                fwrite($fpw,$line . "\n");
            }
        }
    }

    `cd ../Tasks/$taskId;../../bin/cent_adjust population_center.txt coordinates.txt`;
    print "{\n";
    print "  success: true,\n";
    print "}\n";
?>
