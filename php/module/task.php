<?php

class TaskModule {

	public static $SUCCESS = false;
    public static $RESULT = null;
    private $dbModule = null;

    function dbm() {
        if(is_null($this->dbModule)) {
            require_once 'db.php';
            $this->dbModule = new DatabaseModule();
        }
    }


    function submit($params) {
        $this->dbm();
        $con = $this->dbModule->connect();
        $jid = null;
        $pid = $params["pid"];
        $fid = $params["fid"];
        $uidx = $params["uidx"];
        $resultDir = $params["resultsDir"];
        $ran = false;

        session_write_close();


        //job id
        $jid = $this->v3UUID($params["uid"]);
        $this->dbModule->addAnalysis($con, $params["name"], $jid, $pid, $fid, $uidx);

        $file = $this->dbModule->getFile($con, $uidx, $pid, $fid);
        
        if(!is_null($file) && count($file) == 1) {
            $file = reset($file); //gets the first

            $fileDir = (!is_null($file["dataPath"]) && $file["dataPath"] != "NULL" ? $file["dataPath"]:$params["filesDir"]);
            $filePath = $fileDir.DIRECTORY_SEPARATOR.$uidx.DIRECTORY_SEPARATOR.$file["dataInputFileName"];

            $justFileName = $file["dataInputFileName"];
            $outputDir = $resultDir.DIRECTORY_SEPARATOR.$jid;

            $lsid = $params["lsid"];
            $filtering = $params["filtering"] == "1" ? true : false;
            $manualParam = $params["manual"] == "1" ? true: false;
            $bins = $params["bins"];
            $density = $params["density"];
            $pop = $params["pop"];

            if($manualParam) { //auto mode
                $bins = "-1";
                $density = "-1";
                $pop = "-1";
            }

            $workflow = $params["workflow"];            
            if($workflow == "bio") {
                $this->runScript($jid, $filePath, $bins, $density, $pop, $outputDir, $justFileName);   
            } else {
                $this->runGenepattern($jid, $filePath, $bins, $density, $pop, $params["flockId"], $params["imageId"]);
            }
            $ran = true;
        }

        if(!$ran) { //&& strpos($ran, "submitted.")) {
            $this::$SUCCESS = $this->dbModule->updateAnalysisStatus($con, $jid, 3);
            $jid = null;
        } else {
            $this::$SUCCESS = true;
        }

        return $jid;
    }

    function runGenepattern($jid, $input, $bins, $density, $pop, $flockLsid, $imageLsid) {
        $cp = "../../lib/java";
        $executor = "java -classpath $cp/axis.jar:$cp/mail.jar:$cp/activation.jar:$cp/flockUtils.jar org.immport.flock.utils.GenePattern ";
        $params = "submitter $input $bins $density $pop color $jid $flockLsid $imageLsid";

        return exec($executor.$params."> /dev/null 2>&1 &");
    }

    function runScript($jid, $inputPath, $bins, $density, $pop, $outputDir, $inputFileName) {
        //flowGate_script-IOgordon-shared_wfid-robust_flkImgJar.sh inputFilePath outputFolderPath withFilterOrNot bin density population TASKID workflowID
        //flowGate_script-gordon-shared_wfid.sh /export/CDUCWebInput/Jianwu_InputTest.zip /export/CDUCWebOutput/ False 5e2bc2aa-00ad-3b08-8dcc-ec868c06208b 1
        //$script = "../../lib/cduc.sh $jid $s_input $s_output $output $result";
        $script = "(/export/scripts/flowGate_script-IOgordon-shared_wfid-robust_flkImgJar.sh $inputPath $outputDir False $bins $density $pop $jid 7"
            ." && unzip $outputDir/$inputFileName -d $outputDir)";

        return exec($script."> /dev/null 2>&1 &");
    }

    //Version 3 UUID from http://www.php.net/manual/en/function.uniqid.php#94959
    function v3UUID($uid) {
        // Get hexadecimal components of namespace
        $nhex = str_replace(array('-','{','}'), '', uniqid());

        // Binary Value
        $nstr = '';

        // Convert Namespace UUID to bits
        for($i = 0; $i < strlen($nhex); $i+=2) {
            $nstr .= chr(hexdec($nhex[$i].$nhex[($i < strlen($nhex)?$i:$i+1)]));
        }

        // Calculate hash value
        $hash = md5($nstr . $uid);

        return sprintf('%08s-%04s-%04x-%04x-%12s',
            // 32 bits for "time_low"
            substr($hash, 0, 8),
            // 16 bits for "time_mid"
            substr($hash, 8, 4),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 3
            (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
            // 48 bits for "node"
            substr($hash, 20, 12)
        );
    } 

	function getAnalysis($uid, $pid, $fid, $tid) {
        $this->dbm();
        $con = $this->dbModule->connect();
        $result = $this->dbModule->getAnalysis($con, $uid, $pid, $fid, $tid);
        if(!is_null($result)) {
            $this::$RESULT = $result;
        }
        $this::$SUCCESS = true;
	}
}
?>