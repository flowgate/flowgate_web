<?php
	$taskId = $_GET['tid'];
    $popIds = $_GET['pp'];
    $popCount = $_GET['ppl'];
    $xmarker = $_GET['x'];
    $ymarker = $_GET['y'];
    $param = $_GET['pr'];

    $taskDir = "../../Tasks/$taskId";
    $historyFile = "$taskDir/history.txt";

    $type = "color";
    $popIds_arr = split(",", $popIds);
    if(intval($popCount)!=count($popIds_arr)) {
        $type = "pop";
    } 

    $currentRun = (
        $type == "color"?
            "overview_color":($type=="bw"?
                "overview_bw":((strpos($popIds, ",")?"multi":"single")."_population")
            )
    );
 
	$runBin = true;
	$createHistory = false;
	//check the history file to run bins only once
	if(file_exists($historyFile)) {
	    $fp = fopen($historyFile,"r");
	    while(!feof($fp)) {
	        $line = fgets($fp,999);
	        if ($line) {
	            $line = trim($line);
	            if($line == $currentRun.($type=="pop"?$popIds:"")) {
	                $runBin = false;
	                break;
	            }
	        }
	    } 
	    fclose($fp);    
	} else {
	    $createHistory = true;
	}
	//only runs a bin if it never run
	if($runBin) {
	    executeBin($type, $taskDir, $currentRun, $popIds); 
	    $fp = fopen($historyFile, ($createHistory?"w":"a"));  
	    fwrite($fp, $currentRun.($type=="pop"?$popIds:"")."\n\r");  
	    fclose($fp);    
	}

	$json = array();
    $json['success'] = 'true';
    $json['taskDir'] = $taskDir."/";
    $json['type'] = $type;
    $json['popIds'] = $popIds;
    $json['xmarker'] = $xmarker;
    $json['ymarker'] = $ymarker;
    $json['param'] = $param;
    print json_encode($json);	

	function executeBin($_type, $_taskDir, $_currentRun, $_popIds) {
	    $executor = "java".
	        " -classpath ../../lib/java/FlockUtils.jar".
	        //" -Djava.awt.headless=true".
	        " org.immport.flock.utils.FlockImageGenerator ";
	    if(strpos($_currentRun, "Overview")>0) {
	        shell_exec($executor."$_currentRun $_taskDir $_taskDir/$_type");
	    } else {
	        shell_exec($executor."$_currentRun $_taskDir $_taskDir/$_type $_popIds");
	    }
	}
?>