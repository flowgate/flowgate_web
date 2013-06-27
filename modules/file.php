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

    function addFile($_name, $_pid, $_uid, $_org, $_tmp) {
    	$this->dbm();
        $con = $this->dbModule->connect();

        $seq = $this->dbModule->getNextSequence($con, "f");

        $destDir = $this::$FILELOC.$seq."/";
		mkdir($destDir);

		//$target_path = $destDir . basename($_FILES['uploadFile']['name']);
		$target_path = $destDir . 'fcs.txt';
	    $_uploaded = move_uploaded_file($_tmp,$target_path);
        $this::$SUCCESS = $this->dbModule->addFile($con, $_name, $_pid, $_org, $_uid);
    } 

	function getFiles($_uid, $_pid, $_fid) {
        $this->dbm();
        $con = $this->dbModule->connect();

        $result = $this->dbModule->getFile($con, $_uid, $_pid, $_fid);
        $json = null;
        if(!is_null($result)) {
            $fields = array("f_id","f_name","f_org_name","f_status","p_name");
            $headers = array("Sequence", "Name", "File Name", "Status", "Project Name");
            $columns = array();
            for($i=0;$i<count($headers);$i++) {
                $column['header'] = $headers[$i];
                $column['dataIndex'] = $fields[$i];
                array_push($columns, $column);
            }
            $json['columns'] = $columns;
            $json["fields"] = $fields;
            $rows = array();
            foreach ($result as $file) {
                $row;
                foreach(array_keys($file) as $key) {
                    $row[$key]=($key=='f_status'?($file[$key]=='1'?"Loaded":"N/A"):$file[$key]);
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