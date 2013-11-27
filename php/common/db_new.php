<?php
class DatabaseModule {
	private static $DBNAME = "gofcm";
	private static $USERTABLE = "user";
	private static $PROJECTTABLE = "dataset";
	private static $FILETABLE = "dataInputFile";
	private static $TASKTABLE = "analysis";
	private static $IMAGETABLE = "resultImages";

	function connect() {
		$username = "ifx_gofcm_adm";
        $password = "ifx_gofcm_adm";
        $hostname = "localhost:3306"; //"localhost:3306"; //"genepatt-dev.jcvi.org:3666"; 

        //connection to the database
        $con = mysqli_connect($hostname, $username, $password, DatabaseModule::$DBNAME) or die("Unable to connect to MySQL");
        if (mysqli_connect_error()) {
		    die('Connect Error ('.mysqli_connect_errno().') '.mysqli_connect_error());
		} else {
			return $con;
		}
	}
	function close($con) {
		mysqli_close($con);
	}
	function commit($con) {
		mysqli_commit($con);
		$this->close($con);
	}
	function rollback($con) {
		mysqli_rollback($con);
		$this->close($con);
	}

	function executeQuery($con, $query) {
		return mysqli_query($con , $query);	
	}

	function findMany($con, $query) {
		$result = $this->executeQuery($con, $query);

		$many = array();
		if($result) {
			while ($only = $result->fetch_assoc()) {
		        array_push($many, $only);
		    }
	    	mysqli_free_result($result);
		}

		return $many;
	}

	function findOne($con, $query) {
		$many = $this->findMany($con, $query);
		$result = null;
		if(count($many)>0) {
			$result = $many[0];
		}
		return $result;
	}

	function add($con, $query) {
		return $this->executeQuery($con, $query);	
	}

	function getNextSequence($con, $table) {
		$query = "SHOW TABLE STATUS LIKE '".($table=='f'?$this::$FILETABLE:$this::$TASKTABLE)."'";
		$result = $this->executeQuery($con, $query);
		$row = mysqli_fetch_array($result);
		$nextIdx = $row['Auto_increment'];
		mysqli_free_result($result);
		return $nextIdx;
	}

	//user
	function findUser($con, $userName) {
		$query = sprintf("SELECT * FROM %s.%s WHERE userID='%s'", $this::$DBNAME, $this::$USERTABLE, $userName);
		return $this->findOne($con, $query);
	}

	function addUser($con, $userName, $password, $email, $name, $affil) {
		$query = sprintf(
			"INSERT INTO %s.%s (userID, userName, userPass, userEmail, userAffil) values ('%s', '%s','%s', '%s',%s)", 
			$this::$DBNAME, $this::$USERTABLE,
			$userName, $name, $password, $email, (is_null($affil)?"null":"'".$affil."'")
		);

		return $this->add($con, $query);	
	}


	//project
	function findUserProject($con, $uid) {
		$query = sprintf("SELECT datasetID, datasetName FROM %s.%s WHERE userIdx=%d", $this::$DBNAME, $this::$PROJECTTABLE, $uid);
		return $this->findMany($con, $query);	
	}

	function findProject($con, $pname, $uid) {
		$query = sprintf("SELECT * FROM %s.%s WHERE datasetName='%s' and userIdx=%d", $this::$DBNAME, $this::$PROJECTTABLE, $pname, $uid);
		return $this->findOne($con, $query);
	}

	function addProject($con, $pname, $pdesc, $uid) {
		$result = 'loading project failed!';

		$old = $this->findProject($con, $pname, $uid);
		if(!is_null($old)) {
			$result = 'project name already exsits!';
		} else {
			$query = sprintf(
				"INSERT INTO %s.%s (datasetName, datasetDesc, userIdx) values ('%s', '%s', %d)", 
				$this::$DBNAME, $this::$PROJECTTABLE,
				$pname, $pdesc, $uid
			);

			if($this->add($con, $query)) {
				$result = "success";
			}
		}
		return $result;
	}

	//file
	function addFile($con, $name, $pid, $uid) {
		$query = sprintf(
			"INSERT INTO %s.%s (dataInputFileName, datasetID, userIdx) values ('%s', %d, %d)", 
			$this::$DBNAME, $this::$FILETABLE,
			$name, $pid, $uid
		);
		return $this->add($con, $query);	
	}

	function getFile($con, $uid, $pid, $fid) {
		$query = sprintf("SELECT * FROM %s.%s f, %s.%s p WHERE f.userIdx=%d AND f.datasetID=p.datasetID", 
			$this::$DBNAME, $this::$FILETABLE, $this::$DBNAME, $this::$PROJECTTABLE, $uid);
		if(isset($fid)) {
			$query = $query." AND f.dataInputFileID=".$fid;
		} elseif(isset($pid)) {
			$query = $query." AND f.datasetID=".$pid;	
		}
		return $this->findMany($con, $query);
	}

	//task
	function addTask($con, $tid, $pid, $fid, $uid) {
		$query = sprintf(
			"INSERT INTO %s.%s (analysisName, datasetID, dataInputFileID, userIdx) values ('%s', %d, %d, %d)",
				$this::$DBNAME, $this::$TASKTABLE,
				$tid, $pid, $fid, $uid);
		return $this->add($con, $query);
	}

	function getTask($con, $uid, $pid, $tid) {
		$query = sprintf("SELECT * FROM %s.%s a, %s.%s p, %s.%s f ".
			"WHERE a.userIdx=%d and a.datasetID=p.datasetID and a.dataInputFileID=f.dataInputFileID", 
			$this::$DBNAME, $this::$TASKTABLE, $this::$DBNAME, $this::$PROJECTTABLE, $this::$DBNAME, $this::$FILETABLE, $uid);
		if(!is_null($tid)) {
			$query = $query." and a.analysisID=".$tid;
		} elseif(!is_null($pid)) {
			$query = $query." and a.datasetID=".$pid;	
		}
		error_log($query);
		return $this->findMany($con, $query);
	}
}
?>