<?php
    require("../common/constants.php");

	$taskId = $_GET['tid'];
	$file = rtrim($_GET['f'], ",");
    $popIds = rtrim($_GET['pp'], ",");
    $popCount = $_GET['ppl'];
    $xmarker = $_GET['x'];
    $ymarker = $_GET['y'];
    $param = rtrim($_GET['pr'], ",");

    $taskDir = "$RESULT_DIR$taskId/";
    $dirSuffix = "_out";

    //handles file parameter
    $fileArr = array();
    if(strlen($file)>0 && strpos($file, ',')!==false) {
    	$fileArr = explode(",", $file);
    } else {
    	array_push($fileArr, $file);
    }

    //handles multiple or single parameters
    $paramArr = array();
    if(strlen($param)>0 && strpos($param, ',')!==false) {
    	$tempParams = explode(",", $param);
    	foreach($tempParams as $eachParam) {
    		array_push($paramArr, explode(":", $eachParam));
    	}
    } else {
    	array_push($paramArr, explode(":", $param));
    }

    $type = "color";
    $popIds_arr = explode(",", $popIds);
    if(intval($popCount)!=count($popIds_arr)) {
        $type = "pop";
    } else {
        $popIds = "all"; //overview
    }

    $currentRun = (
        $type == "color"?"overview_color":($type=="bw"?"overview_bw":((strpos($popIds, ",")?"multi":"single")."_population"))
    );


    $dirs = array();
    foreach($fileArr as $eachFile) {
    	$currResult = $eachFile.$dirSuffix; //"file_out"
    	foreach($paramArr as $eachParam) {
    		$currDir = "$currResult/$currResult"."_$eachParam[0]_$eachParam[1]/";

            $totalPopulation = countPopulation($taskDir.$currDir);
            
            if($currentRun == "multi_population" && $totalPopulation > count($popIds_arr)) { 
                //skip overview and individual population, since they are pre-generated
                run($type, $taskDir.$currDir, $currentRun, $popIds);
            }
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
    $json['params'] = $paramArr;
    $json['files'] = $fileArr;
    $json['m_f'] = count($fileArr)>1;
    $json['dirs'] = $dirs;
    $json['m_p'] = count($paramArr)>1;
    print json_encode($json);	

    function countPopulation($dir) { 
        //count lines of population_center file to get the number of population for a result
        $file= $dir."population_center.txt";
        $linecount = 0;
        $handle = fopen($file, "r");
        while(!feof($handle)){
            $line = fgets($handle, 4096);
            $linecount = $linecount + substr_count($line, PHP_EOL);
        }
        fclose($handle);
        return $linecount;
    }

    function run($type, $dir, $currentRun, $popIds) {
    	$historyFile = $dir."history.txt";
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
		    runJar($type, $dir, $currentRun, $popIds); 
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
        shell_exec($executor."$_currentRun $_taskDir $_taskDir/images".(strpos($_currentRun, "Overview")>0?"":" $_popIds"));
	}
?>