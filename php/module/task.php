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


    function submit($pid, $fid, $uid, $bins, $density, $pop) {
        $this->dbm();
        $con = $this->dbModule->connect();
        $jid = null;

        $file = $this->dbModule->getFile($con, $uid, $pid, $fid);
        if(!is_null($file) && count($file) == 1) {
            //job id
            $jid = $this->v3UUID($uid);

            $filePath = $file["dataInputFilePath"].DIRECTORY_SEPARATOR.$file["dataInputFileName"];
            $ran = false; //$this->runGenepattern($uid, $jid, $filePath, $bins, $density, $pop);
            if($ran) {
                $this::$SUCCESS = $this->dbModule->addTask($con, $jid, $pid, $fid, $uid);
            } else {
                $jid = null;
            }
        }

        return $jid;
    }

    function runGenepattern($uid, $jid, $input, $bins, $density, $pop) {
        $cp = "../../lib/java";
        $executor = "java".
            " -classpath $cp/axis.jar:$cp/mail.jar:$cp/activation.jar:$cp/flockUtils.jar".
            //" -Djava.awt.headless=true".
            " org.immport.flock.utils.GenePattern ";
        $params = "$uid $input $bins $density $pop color $jid";
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
            $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
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

	function getTasks($_uid, $_pid, $_tid) {
        $this->dbm();
        $result = $this->dbModule->getTasks($_uid, $_pid);
        $json = null;
        if(!is_null($result)) {
            $fields = array("t_num","t_name","t_bin","t_density","t_status","f_name", "p_name", "t_id");
            $headers = array("Sequence", "Name", "Bin#", "Density", "Status", "File Name","Project Name", "Task ID");
            $columns = array();
            for($i=0;$i<count($headers);$i++) {
                $column['header'] = $headers[$i];
                $column['dataIndex'] = $fields[$i];
                array_push($columns, $column);
            }

            $rows = array();
            $i=1;
            foreach ($result as $task) {
                $row['t_num']=$i;
                foreach(array_keys($task) as $key) {
                    $row[$key]=($key=='t_status'?($task[$key]=='1'?"Completed":"N/A"):$task[$key]);
                }
                array_push($rows, $row);
            }

            $json["columns"] = $columns;
            $json["fields"] = $fields;
            $json["rows"]=$rows;

            $this::$RESULT = $json;
            $this::$SUCCESS = true;
        }
	}
}
?>