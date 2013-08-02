<?php
    $taskId = $_GET['taskId'];
    $type = $_GET['type'];

    $taskDir = "../Tasks/$taskId";
    $historyFile = "$taskDir/history.txt";
    $currentRun = ($type == "color"?"genOverviewColor":"genOverviewBW");
    $runOverview = true;
    $createHistory = false;

    function executeOverview($_taskId, $_currentRun) {
        `./$_currentRun ../Tasks/$_taskId ../Tasks/$_taskId`;
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
        executeOverview($taskId, $currentRun); 
        $fp = fopen($historyFile, ($createHistory?"w":"a"));  
        fwrite($fp,$currentRun."\n\r");  
        fclose($fp);    
    }

    $pid = rand();

    $fp = fopen("$taskDir/profile.txt",'r');
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
    $fp = fopen("$taskDir/percentage.txt", "r");
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

    $json = array();
    $json['success'] = 'true';
    $json['popId'] = $populations[0];
    $json['totalPops'] = ($totalPops-1);
    $json['pops'] = $pops;
    $json['taskDir'] = $taskDir."/";
    $json['type'] = $type;
    $json['columns'] = array_slice($markers, 1);//implode(',',array_slice($markers, 1));

    /*
    $imgArr = array();
    for ($i = 1; $i < $markerLen; $i++) {
        $markerImg = array();
    	for ($j = 1; $j < $markerLen; $j++) {
            $markerImg[$markers[$j]] = "/$markers[$i].$markers[$j].$imagePostfix";
    	}
        $imgArr[$markers[$i]] = $markerImg;
    }
    $json['rows'] = $imgArr;
    */

    print json_encode($json);
?>
