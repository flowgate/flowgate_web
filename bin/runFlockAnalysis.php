<?php
    $fileNumber = $_POST['fileNumber'];
    $name = $_POST['name'];
    $num_of_bins = $_POST['num_of_bins'];
    $density = $_POST['density'];

    //$fileNumber = 2;
    //$name = "Task 1";
    //$num_of_bins = 0;
    //$density = 0;

    $fp = fopen("../Tasks/task_sequence.txt",'r');
    if (!$fp) {
        echo "Unable to open file";
        exit;
    }
    $sequence = fgets($fp,999);
    fclose($fp);
    $sequence = rtrim($sequence,"\n");
	$sequence = rtrim($sequence,"\r");
    $sequence++;

    $destDir = "../Tasks/$sequence/";
    mkdir($destDir);
    chmod($destDir, 0777);

    `cd ../Tasks/$sequence;../../bin/flock1 ../../Files/$fileNumber/fcs.txt $num_of_bins $density`;
    `cp ../Tasks/$sequence/population_center.txt ../Tasks/$sequence/population_center.txt.orig`;

    $fpin = fopen("../Tasks/tasks.txt",'a');
    if (!$fpin) {
        echo "Unable to open file";
        exit;
    }

//    while (!feof($fpin)) {
//        $line = fgets($fpin,999);
//    }
    fwrite($fpin,$sequence . "\t");
    fwrite($fpin,$name . "\t");
    fwrite($fpin,$fileNumber . "\t");
    fwrite($fpin,$num_of_bins . "\t");
    fwrite($fpin,$density . "\t");
    fwrite($fpin,"Completed" . "\n");
    fclose($fpin);

    $fp = fopen("../Tasks/task_sequence.txt",'w');
    fwrite($fp,$sequence);
    fclose($fp);

    echo '{success:true}';
?>
