<?php
    $taskId = $_GET['taskId'];
    $idx1 = $_GET['idx1'];
    $idx2 = $_GET['idx2'];
    $popIdx = $_GET['popIdx'];
    $x1Value = $_GET['x1Value'];
    $y1Value = $_GET['y1Value'];
    $x2Value = $_GET['x2Value'];
    $y2Value = $_GET['y2Value'];

//  $task = 1;
//  $idx1 = 4;
//  $idx2 = 2;
//  $popIdx = 4;
//  $x1Value = 100;
//  $y1Value = 100;
//  $x2Value = 400;
//  $y2Value = 400;

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

   $saveValues = array();
   $cnt = 0;
   $maxPopId = 0;
    while(!feof($fpr)) {
        $line = fgets($fpr,999);
        if ($line) {
            $line = rtrim($line,"\n");
			$line = rtrim($line,"\r");
            $values = explode("\t",$line);
            if ($values[0] > $maxPopId) {
              $maxPopId = $values[0];
            }

            if ($popIdx == $values[0]) {
                $saveValues = $values;
                $values[$idx1+1] = $x1Value;
                $values[$idx2+1] = $y1Value;
                fwrite($fpw,join("\t",$values) . "\n");
            } else {
                fwrite($fpw,$line . "\n");
            }
            $cnt++;
        }
    }

    $saveValues[0] = $maxPopId + 1;
    $saveValues[$idx1+1] = $x2Value;
    $saveValues[$idx2+1] = $y2Value;

    fwrite($fpw,join("\t",$saveValues) . "\n");
    fclose($fpw);

    `cd ../Tasks/$taskId;../../bin/cent_adjust population_center.txt coordinates.txt`;
    print "{\n";
    print "  success: true,\n";
    print "}\n";
?>

