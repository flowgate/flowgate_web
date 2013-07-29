<?php

class TaskModule {
	public static $SUCCESS = false;
    public static $RESULT = null;
    private static $TASKLOC = "./Tasks/";
    private static $FILELOC = "../../Files/";
    private $dbModule = null; 

    function dbm() {
        if(is_null($this->dbModule)) {
            require_once 'db_mongo.php';
            $this->dbModule = new DatabaseModule();
        }
    }

    function runAnalysis($_tname, $_tbin, $_tden, $_fid, $_pid, $_uid) {
    	$this->dbm();

        $task = array(
            "t_id" => new MongoId().'',
            "t_name" => $_tname,
            "t_bin" => $_tbin,
            "t_density" => $_tden,
            "t_status" => 1
        );

        $destDir = $this::$TASKLOC.$task['t_id'];
        mkdir($destDir);
        chmod($destDir, 0777);

        $fileLoc = $this::$FILELOC.$_fid;
        `cd $destDir;../../bin/flock1 $fileLoc/fcs.txt $_tbin $_tden`;
        `cp $destDir/population_center.txt $destDir/population_center.txt.orig`;

        $this::$SUCCESS = $this->dbModule->addTask($_fid, $task);
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