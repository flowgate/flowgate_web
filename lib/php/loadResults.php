<?php
    $taskId = $_GET['taskId'];
    $type = $_GET['type'];
    $popId = $_GET['popId'];

    $taskDir = "../../Tasks/$taskId";
    $historyFile = "$taskDir/history.txt";
    $currentRun = ($type == "color"?"overview_color":($type=="bw"?"overview_bw":"single_population"));
    $runBin = true;
    $createHistory = false;

    function executeBin($_type, $_taskDir, $_currentRun, $_popId) {
        $executor = "java".
            " -classpath ../java/FlockUtils.jar".
            " -Djava.awt.headless=true".
            " org.immport.flock.utils.FlockImageGenerator ";
        shell_exec("mkdir $_taskDir/$_type");
        if(strpos($_currentRun, "Overview")>0) {
            shell_exec($executor."$_currentRun $_taskDir $_taskDir/$_type");
        } else {
            shell_exec($executor."$_currentRun $_taskDir $_taskDir/$_type $_popId");
        }
    }

    function readParameters($_taskId) {
        $params = array();
        $fp = fopen("$taskDir/parameters.txt",'r');
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
                if($line == $currentRun.($type=="pop"?$popId:"")) {
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
        executeBin($type, $taskDir, $currentRun, $popId); 
        $fp = fopen($historyFile, ($createHistory?"w":"a"));  
        fwrite($fp, $currentRun.($type=="pop"?$popId:"")."\n\r");  
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
    if(strpos($currentRun, "single_population")>0) {
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
