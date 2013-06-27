<?php
    $taskId = $_GET['taskId'];
    $type = $_GET['type'];

    $historyFile = "../Tasks/$taskId/history.txt";
    $currentRun = ($type == "color"?"genOverviewColor":"genOverviewBW");
    $runOverview = true;
    $createHistory = false;

    function executeOverview($_tid, $type) {
        `./$currentRun ../Tasks/$taskId ../Tasks/$taskId`;
        error_log("./$currentRun ../Tasks/$taskId ../Tasks/$taskId");
    }

    if(file_exists($historyFile)) {
        $fp = fopen($historyFile,"r");
        while(!feof($fp)) {
            $line = fgets($fp,999);
            if ($line) {
                $line = rtrim(rtrim($line,"\n"),"\r");
                if($line == $currentRun) {
                    $runOverview = false;
                }
            }
        } 
        fclose($fp);    
    } else {
        $createHistory = true;
    }

    if($runOverview) {
        executeOverview(); 
        $fp = fopen($historyFile, ($createHistory?"w":"a"));  
        fwrite($fp,$currentRun."\n\r");  
        fclose($fp);    
    }

    $pid = rand();

    $fp = fopen("../Tasks/$taskId/profile.txt",'r');
    if (!$fp) {
        echo "Unable to open file";
	   exit;
    }

    // Read in the first line that has the Marker names
    $line = rtrim(rtrim(fgets($fp,999),"\n"),"\r");
    $markers = explode("\t",$line);
    $markerLen = count($markers);
    for ($i = 0; $i < $markerLen; $i++) {
      $markers[$i] = str_replace("<","",$markers[$i]);
      $markers[$i] = str_replace(">","",$markers[$i]);
    }

    // Read in the second line, to find the minimum population id
    $line = rtrim(rtrim(fgets($fp,999),"\n"),"\r");
    $populations = explode("\t",$line);
    fclose($fp);

    //read population percentage file
    $fp = fopen("../Tasks/$taskId/percentage.txt", "r");
    if (!$fp) {
        echo "Unable to open file";
       exit;
    }
    $pops = "{";
    $totalPops = 0;
    while(!feof($fp)){
        $line = rtrim(rtrim(fgets($fp),"\n"),"\r");
        if(strlen($line)>0 && $totalPops++ > 0) {
            $lineData = explode("\t",$line);
            $pops.=$lineData[0].":'".$lineData[1]."',";
        }
    }
    $pops.="}";
    fclose($fp);

    $renderCode =
        "renderer: function(val) { ".
        "var data = val.split(\";\"); ".
        "return '<img height=\"85\" width=\"85\" ".
        "src=\"./bin/displayFlockImage.php?taskId=$taskId&imageName='+encodeURIComponent(data[0])+'.'+encodeURIComponent(data[2])+'.all.$type.png&rr=$pid\">'".
        "}";
    $renderCodeSpan = "renderer: function(val) { return '<div style=\"height:85px;text-align:center;\">' + val + '</div>'}";

    print "{\n";
    print "  success: true,\n";
    print "  popId: $populations[0],\n";
    print "  totalPops:".($totalPops-1).", pops: ".$pops.", \n";
    print "  columns: [\n";
    $markerArray = array();
    $markerArray[] = "    { header: ' ', dataIndex: 'm0', locked: true, $renderCodeSpan}";
    for ($i = 1; $i < $markerLen; $i++) {
	   $markerArray[] = "    { header: '$markers[$i]', dataIndex: 'm$i', locked: false, $renderCode}";
    }

    print join(",\n",$markerArray);
    print "\n";
    print "  ],\n";

    print "  fields: [\n";
    print "    'm0',\n";
    for ($i = 1; $i < $markerLen; $i++) {
	print "    'm$i',\n";
    }
    print "  ],\n";

    print "  rows: [\n";

    for ($i = 1; $i < $markerLen; $i++) {
        $markerArray = array();
        $markerArray[] =  "      m0: '$markers[$i]'";
	for ($j = 1; $j < $markerLen; $j++) {
	    $idx1 = $j -1;
	    $idx2 = $i -1;
	    $markerArray[] = "      m$j: '$markers[$j];$idx1;$markers[$i];$idx2'";
	}
	print "    {\n";
	print join(",\n",$markerArray);
	print "\n";
	if ($i < ($markerLen -1)) {
	    print "    },\n";
	} else {
	    print "    }\n";
	}
    }

    print "  ]\n";
    print "}\n";
?>
