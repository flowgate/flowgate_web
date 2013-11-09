<?php

class FileModule {
	public static $SUCCESS = false;
    public static $RESULT = null;
    private static $FILELOC = "./Files/";
    private $dbModule = null; 

    function dbm() {
        if(is_null($this->dbModule)) {
            require_once 'db_new.php';
            $this->dbModule = new DatabaseModule();
        }
    }

    function addFile($_name, $_pid, $_uid, $_org) {
    	$this->dbm();
        $con = $this->dbModule->connect();
        $this::$SUCCESS = $this->dbModule->addFile($con, $_name, $_pid, $_uid);
    } 

	function getFiles($_uid, $_pid, $_fid) {
        $this->dbm();
        $con = $this->dbModule->connect();

        $result = $this->dbModule->getFile($con, $_uid, $_pid, $_fid);
        $json = null;
        if(!is_null($result)) {
            $fields = array("f_id","f_name","f_org_name","f_status","p_name");
            $rows = array();
            foreach ($result as $file) {
                $row;
                foreach(array_keys($file) as $key) {
                    $row[$key]=($key=='f_status'?($file[$key]=='1'?"Loaded":"N/A"):$file[$key]);
                }
                array_push($rows, $row);
            }
            $json["files"]=$rows;

            $this::$RESULT = $json;
            $this::$SUCCESS = true;
        }

        $this->dbModule->close($con);
    }
}
?>