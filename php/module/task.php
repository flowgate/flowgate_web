<?php

class TaskModule {
	public static $SUCCESS = false;
    public static $RESULT = null;
    private static $TASKLOC = "./Tasks/";
    private static $FILELOC = "../../Files/";
    private $dbModule = null; 

    function dbm() {
        if(is_null($this->dbModule)) {
            require_once 'db_new.php';
            $this->dbModule = new DatabaseModule();
        }
    }


    function submit($pid, $fid, $uid, $bins, $density, $pop, $lsid) {
        require_once("../common/constants.php");
        $this->dbm();
        $con = $this->dbModule->connect();
        $jid = null;

        $file = $this->dbModule->getFile($con, $uid, $pid, $fid);
        
        if(!is_null($file) && count($file) == 1) {
            $file = reset($file); //gets the first
            //job id
            $jid = $this->v3UUID($uid);

            $fileDir = (!is_null($file["dataPath"]) && $file["dataPath"]!="NULL"?$file["dataPath"]:$FILE_DIR);
            $filePath = $fileDir.DIRECTORY_SEPARATOR.$file["dataInputFileName"];
            
            $flockLsid = ($lsid ? $lsid : $GP_FLOCK_LSID);

            $ran = $this->runGenepattern("hkim", $jid, $filePath, $bins, $density, $pop, $flockLsid, $GP_IMAGE_LSID);
            if($ran) {
                $this::$SUCCESS = $this->dbModule->addTask($con, $jid, $pid, $fid, $uid);
            } else {
                $jid = null;
            }
        }

        return $jid;
    }

    function runGenepattern($uid, $jid, $input, $bins, $density, $pop, $flockLsid, $imageLsid) {
        $cp = "../../lib/java";
        $executor = "java".
            " -classpath $cp/axis.jar:$cp/mail.jar:$cp/activation.jar:$cp/flockUtils.jar".
            //" -Djava.awt.headless=true".
            " org.immport.flock.utils.GenePattern ";
        $params = "$uid $input $bins $density $pop color $jid $flockLsid $imageLsid";
        //exec($executor.$params." > /dev/null 2>&1 &", $rtnVal);
        return shell_exec($executor.$params);
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

	function getTasks($uid, $pid, $tid) {
        $this->dbm();
        $con = $this->dbModule->connect();
        $result = $this->dbModule->getTask($con, $uid, $pid, $tid);
        if(!is_null($result)) {
            $this::$RESULT = $result;
        }
        $this::$SUCCESS = true;
	}
}
?>