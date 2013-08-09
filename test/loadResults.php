<?php
    $taskId = $_GET['taskId'];
    $type = $_GET['type'];
    $popId = $_GET['popId'];

    $taskDir = "../Tasks/$taskId";
    $historyFile = "$taskDir/history.txt";
    $currentRun = ($type == "color"?"genOverviewColor":($type=="bw"?"genOverviewBW":"genMarker2Pop"));
    $runBin = true;
    $createHistory = false;

    function executeBin($_type, $_taskId, $_currentRun, $_markerLen) {
        `mkdir ../Tasks/$_taskId/$_type`;
        if(strpos($_currentRun, "Overview")>0) {
            `../bin/$_currentRun ../Tasks/$_taskId ../Tasks/$_taskId/$_type`;
        } else {
            for($i=0;$i<$_markerLen;$i++) {
                for($j=0;$j<$_markerLen;$j++) {
                    `../bin/$_currentRun ../Tasks/$_taskId ../Tasks/$_taskId/$_type $i $j`;    
                }
            }
        }
    }

    function readParameters($_taskId) {
        $params = array();
        $fp = fopen("../Tasks/$_taskId/parameters.txt",'r');
        if (!$fp) {
            echo "Unable to open file";
            exit;
        }

        while(!feof($fp)) {
            $line = fgets($fp,999);
            if ($line) {
                $vals = explode("\t",trim($line));
                $params[$vals[0]] = $vals[1];
            }
        } 
        fclose($fp);
        return $params;
    }

    //check the history file to run bins only once
    if(file_exists($historyFile)) {
        $fp = fopen($historyFile,"r");
        while(!feof($fp)) {
            $line = fgets($fp,999);
            if ($line) {
                $line = trim($line);
                if($line == $currentRun) {
                    $runBin = false;
                    break;
                }
            }
        } 
        fclose($fp);    
    } else {
        $createHistory = true;
    }

    $fp = fopen("$taskDir/profile.txt",'r');
    if (!$fp) {
        echo "Unable to open file";
        exit;
    }

    // Read in the first line that has the Marker names
    $line = trim(fgets($fp,999));
    $markers = array_slice(explode("\t",$line), 1);
    $markerLen = count($markers);
    for ($i = 0; $i < $markerLen; $i++) {
      $markers[$i] = str_replace(">","",str_replace("<","",$markers[$i]));
    }
    // Read in the second line, to find the minimum population id
    $line = trim(fgets($fp,999));
    $populations = explode("\t",$line);
    fclose($fp);

    //only runs a bin if it never run
    if($runBin) {
        executeBin($type, $taskId, $currentRun, $markerLen); 
        $fp = fopen($historyFile, ($createHistory?"w":"a"));  
        fwrite($fp, $currentRun."\n\r");  
        fclose($fp);    
    }

    //read population percentage file
    $fp = fopen("$taskDir/percentage.txt", "r");
    if (!$fp) {
        echo "Unable to open file";
       exit;
    }
    $pops = array();
    $totalPops = 0;
    while(!feof($fp)){
        $line = trim(fgets($fp));
        if(strlen($line)>0 && $totalPops++ > 0) {
            $lineData = explode("\t",$line);
            $pops[(int)$lineData[0]]=$lineData[1];
        }
    }
    fclose($fp);

    $params = null;
    if(strpos($currentRun, "Marker2Pop")>0) {
        $params = readParameters($taskId);
    }

    $json = array();
    $json['success'] = 'true';
    $json['totalPops'] = ($totalPops-1);
    $json['pops'] = $pops;
    $json['taskId'] = $taskId;
    $json['taskDir'] = $taskDir."/";
    $json['type'] = $type;
    $json['popId'] = $popId;
    $json['columns'] = $markers;

    print json_encode($json);
?>
