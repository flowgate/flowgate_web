<?php
	$taskId = $_GET['tid'];
	$file = rtrim($_GET['f'], ",");
    $popIds = rtrim($_GET['pp'], ",");
    $popCount = $_GET['ppl'];
    $xmarker = $_GET['x'];
    $ymarker = $_GET['y'];
    $param = rtrim($_GET['pr'], ",");

    $dirSuffix = "_out";
    $taskDir = "../../Tasks/$taskId/";

    //handles file parameter
    $fileArr = array();
    if(strlen($file)>0 && strpos($file, ',')!==false) {
    	$fileArr = split(",", $file);
    } else {
    	array_push($fileArr, $file);
    }

    //handles multiple or single parameters
    $paramArr = array();
    if(strlen($param)>0 && strpos($param, ',')!==false) {
    	$tempParams = split(",", $param);
    	foreach($tempParams as $eachParam) {
    		array_push($paramArr, split(":", $eachParam));
    	}
    } else {
    	array_push($paramArr, split(":", $param));
    }

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

    $dirs = array();
    foreach($fileArr as $eachFile) {
    	$currResult = $eachFile.$dirSuffix; //"file_out"
    	foreach($paramArr as $eachParam) {
    		$currDir = "$currResult/$currResult"."_$eachParam[0]_$eachParam[1]/";
    		run($type, $taskDir.$currDir, $currentRun, $popIds);
    		array_push($dirs, $currDir);
    	}
    }

	$json = array();
    $json['success'] = 'true';
    $json['taskDir'] = $taskDir;
    $json['taskId'] = $taskId;
    $json['type'] = $type;
    $json['popIds'] = $popIds;
    $json['xmarker'] = $xmarker;
    $json['ymarker'] = $ymarker;
    $json['param'] = $param;
    $json['files'] = $file;
    $json['dirs'] = $dirs;
    print json_encode($json);	


    function run($type, $taskDir, $currentRun, $popIds) {
    	$historyFile = $taskDir."history.txt";
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
		    runJar($type, $taskDir, $currentRun, $popIds); 
		    $fp = fopen($historyFile, ($createHistory?"w":"a"));  
		    fwrite($fp, $currentRun.($type=="pop"?$popIds:"")."\n\r");  
		    fclose($fp);    
		}
    }

	function runJar($_type, $_taskDir, $_currentRun, $_popIds) {
	    $executor = "java".
	        " -classpath ../../lib/java/flockUtils.jar".
	        //" -Djava.awt.headless=true".
	        " org.immport.flock.utils.FlockImageGenerator ";
	    if(strpos($_currentRun, "Overview")>0) {
	        shell_exec($executor."$_currentRun $_taskDir $_taskDir/$_type");
	    } else {
	        shell_exec($executor."$_currentRun $_taskDir $_taskDir$_type $_popIds");
	    }
	}
?>