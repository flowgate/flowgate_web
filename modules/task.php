<?php

class TaskModule {
	public static $SUCCESS = false;
    public static $RESULT = null;
    private static $TASKLOC = "./Tasks/";
    private static $FILELOC = "../../Files/";
    private $dbModule = null; 

    function dbm() {
        if(is_null($this->dbModule)) {
            require_once 'db.php';
            $this->dbModule = new DatabaseModule();
        }
    }

    function runAnalysis($_tname, $_tbin, $_tden, $_fid, $_pid, $_uid) {
    	$this->dbm();
        $con = $this->dbModule->connect();

        $seq = $this->dbModule->getNextSequence($con, "t");

        $destDir = $this::$TASKLOC.$seq;
        mkdir($destDir);
        chmod($destDir, 0777);

        $fileLoc = $this::$FILELOC.$_fid;
        `cd $destDir;../../bin/flock1 $fileLoc/fcs.txt $_tbin $_tden`;
        `cp $destDir/population_center.txt $destDir/population_center.txt.orig`;

        $this::$SUCCESS = $this->dbModule->addTask($con, $_tname, $_tbin, $_tden, $_fid, $_pid, $_uid);
        $this->dbModule->close($con);
    } 

	function getTasks($_uid, $_pid, $_tid) {
        $this->dbm();
        $con = $this->dbModule->connect();

        $result = $this->dbModule->getTask($con, $_uid, $_pid, $_tid);
        $json = null;
        if(!is_null($result)) {
            $fields = array("t_id","t_name","t_bin","t_density","t_status","f_name","f_id", "p_name");
            $headers = array("Sequence", "Name", "Bin#", "Density", "Status", "File Name","Project Name");
            $columns = array();
            for($i=0;$i<count($headers);$i++) {
                $column['header'] = $headers[$i];
                $column['dataIndex'] = $fields[$i];
                array_push($columns, $column);
            }
            $json["columns"] = $columns;
            $json["fields"] = $fields;
            $rows = array();
            foreach ($result as $task) {
                $row;
                foreach(array_keys($task) as $key) {
                    $row[$key]=($key=='t_status'?($task[$key]=='1'?"Completed":"N/A"):$task[$key]);
                }
                array_push($rows, $row);
            }

            $json["rows"]=$rows;

            $this::$RESULT = $json;
            $this::$SUCCESS = true;
        }

        $this->dbModule->close($con);
	}
}
?>