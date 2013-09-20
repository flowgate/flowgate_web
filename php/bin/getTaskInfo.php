<?php
    $taskId = $_GET['tid'];

    $taskDir = "../../Tasks/$taskId";

    $contents = file_get_contents("$taskDir/properties");
    $json = json_decode($contents, true);

    // $fp = fopen("$taskDir/profile.txt",'r');
    // if (!$fp) {
    //     echo "Unable to open file";
    //     exit;
    // }

    // // Read in the first line that has the Marker names
    // $line = trim(fgets($fp,999));
    // $markers = array_slice(explode("\t",$line), 1);
    // $markerLen = count($markers);
    // for ($i = 0; $i < $markerLen; $i++) {
    //   $markers[$i] = str_replace(">","",str_replace("<","",$markers[$i]));
    // }
    // fclose($fp);

    // $params = readParameters($taskDir);
    // $pops = readPopulations($taskDir);

    // $json = array();
    $json['success'] = 'true';
    // $json['pops'] = $pops;
    $json['taskId'] = $taskId;
    // $json['columns'] = $markers;
    // $json['params'] = $params;

    print json_encode($json);


    //---------------
    //FUNCTIONS
    function readParameters($_taskDir) {
        $params = array();
        $fp = fopen("$_taskDir/parameters.txt",'r');
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

    function readPoPulations($_taskDir) {
        //read population percentage file
        $fp = fopen("$_taskDir/percentage.txt", "r");
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
        return $pops;
    }
?>
