<?php
    $taskId = $_GET['taskId'];
    $fileInputId = $_GET['fileInputId'];

    //$taskId = 1;
    //$fileInputId = 1;

    $fp = fopen("../Tasks/$taskId/profile.txt",'r');
    if (!$fp) {
        echo "Unable to open file";
        exit;
    }

    // Read in the first line that has the Marker names
    $line = fgets($fp,999);
    $line = rtrim($line,"\n");
	$line = rtrim($line,"\r");
    $markers = explode("\t",$line);
    $markerLen = count($markers);
    for ($i = 0; $i < $markerLen; $i++) {
      $markers[$i] = str_replace("<","",$markers[$i]);
      $markers[$i] = str_replace(">","",$markers[$i]);
    }


    print "{\n";
    print "  success: true,\n";
    print "  tableColumns: [\n";

    $markerArray = array();
    for ($i = 0; $i < $markerLen; $i++) {
      $markerArray[] = "    { header: '$markers[$i]', dataIndex: 'm$i',locked: false}";
    }

    print join(",\n",$markerArray);
    print "\n";
    print "  ],\n";

    print "  tableFields: [\n";
    $markerArray = array();
    for ($i = 0; $i < $markerLen; $i++) {
      $markerArray[] = "    'm$i'";
    }
    print join(",\n",$markerArray);
    print "\n";
    print "  ],\n";

    print "  tableRows: [\n";

    $fp = fopen("../Tasks/$taskId/population_center.txt",'r');
    if (!$fp) {
        echo "Unable to open file";
        exit;
    }

    $numPops = 0;
    while(!feof($fp)) {
        $line = fgets($fp,999);
        if ($line) {
            $numPops++;
        }
    }
    fclose($fp);
    $numPops--;

    $fp = fopen("../Tasks/$taskId/population_center.txt",'r');
    if (!$fp) {
        echo "Unable to open file";
        exit;
    }

    $cnt = 0;
    while(!feof($fp)) {
        $line = fgets($fp,999);
        if ($line) {
            $line = rtrim($line,"\n");
			$line = rtrim($line,"\r");
            $values = explode("\t",$line);
            $markerArray = array();
            for ($i = 0; $i < $markerLen; $i++) {
                $markerArray[] = "    m$i: '$values[$i]'";
            }
            print "    {\n";
            print join(",\n",$markerArray);
            print "\n";
            
            if ($cnt < $numPops) {
                print "    },\n";
            } else {
                print "    }\n";
            }
            $cnt++;
        }
    }
    fclose($fp);

    print "  ]\n";
    print "}\n";
?>
