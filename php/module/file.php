<?php

class FileModule {
	public static $SUCCESS = false;
    public static $RESULT = null;
    private static $FILELOC = "./Files/";
    private $dbModule = null; 

    function dbm() {
        if(is_null($this->dbModule)) {
            require_once 'db.php';
            $this->dbModule = new DatabaseModule();
        }
    }

    function addFile($_name, $_pid, $_uid, $_org) {
    	$this->dbm();
        $con = $this->dbModule->connect();
        $this::$SUCCESS = $this->dbModule->addFile($con, $_pid, $_name, $_org, $_uid);
    } 

	function getFiles($_uid, $_pid, $_fid) {
        $this->dbm();

        $result = $this->dbModule->getFile($_uid, $_pid, $_fid);
        $json = null;
        if(!is_null($result)) {
            $fields = array("f_num","f_name","f_org_name", "_id","f_status","p_name");
            $headers = array("Sequence", "Name", "File Name", "File ID", "Status", "Project Name");
            $columns = array();
            for($i=0;$i<count($headers);$i++) {
                $column['header'] = $headers[$i];
                $column['dataIndex'] = $fields[$i];
                array_push($columns, $column);
            }
            
            $rows = array();
            $i=1;
            foreach ($result as $file) {
                $row['f_num']=$i++;
                foreach(array_keys($file) as $key) {
                    $row[$key]=($key=='f_status'?($file[$key]=='1'?"Loaded":"N/A"):$file[$key]);
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