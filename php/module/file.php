<?php

class FileModule {
	public static $SUCCESS = false;
    public static $RESULT = null;
    private static $FILELOC = "./Files/";
    private $dbModule = null; 

    function dbm() {
        if(is_null($this->dbModule)) {
            require_once 'db_mongo.php';
            $this->dbModule = new DatabaseModule();
        }
    }

    function addFile($_name, $_pid, $_uid, $_org, $_tmp) {
    	$this->dbm();
        $file = array(
            "_id" => new MongoId().'',
            "f_name" => $_name,
            "f_status" => 1,
            "f_project_id" => $_pid,
            "f_org_name" => $_org,
            "f_user_id" => $_uid,
            "f_tasks" => array()
        );
        $destDir = $this::$FILELOC.$file['_id']."/";
		mkdir($destDir);

		//$target_path = $destDir . basename($_FILES['uploadFile']['name']);
		$target_path = $destDir . 'fcs.txt';
	    $_uploaded = move_uploaded_file($_tmp, $target_path);
        $this::$SUCCESS = $this->dbModule->addFile($file);
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