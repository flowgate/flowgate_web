<?php
class DatabaseModule {
	private static $DBNAME = "cyberflow";
	private static $USERTABLE = "user";
	private static $PROJECTTABLE = "dataset";
	private static $FILETABLE = "dataInputFile";
	private static $TASKTABLE = "analysis";
	private static $IMAGETABLE = "resultImages";

	function connect() {
    $username = "";
    $password = "";
    $hostname = "127.0.0.1"; 

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
		if(count($many) > 0) {
			$result = $many[0];
		}
		return $result;
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
	function findUser($con, $email) {
		$query = sprintf("SELECT * FROM %s.%s WHERE userEmail='%s'", $this::$DBNAME, $this::$USERTABLE, $email);
		return $this->findOne($con, $query);
	}

	function addUser($con, $password, $name, $email, $affil) {
		$query = sprintf(
			"INSERT INTO %s.%s (userName, userPass, userEmail, userAffil) values ('%s', '%s','%s','%s')", 
			$this::$DBNAME, $this::$USERTABLE,
			$name, $password, $email, (is_null($affil)?"null":$affil)
		);

		return $this->executeQuery($con, $query);	
	}


	//project
	function findUserProject($con, $uid) {
		$query = sprintf("SELECT datasetID, datasetName, datasetDesc, datasetTime FROM %s.%s WHERE userIdx=%d", $this::$DBNAME, $this::$PROJECTTABLE, $uid);
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
				"INSERT INTO %s.%s (datasetName, datasetDesc, userIdx, datasetTime) values ('%s', '%s', %d, now())", 
				$this::$DBNAME, $this::$PROJECTTABLE,
				$pname, $pdesc, $uid
			);

			if($this->executeQuery($con, $query)) {
				$result = "success";
			}
		}
		return $result;
	}

	//file
	function addFile($con, $name, $desc, $pid, $uid) {
		$query = sprintf(
			"INSERT INTO %s.%s (dataInputFileName, dataInputFileDesc, datasetID, userIdx, dataInputFileTime) values ('%s', '%s', %d, %d, now())", 
			$this::$DBNAME, $this::$FILETABLE,
			$name, $desc, $pid, $uid
		);
		return $this->executeQuery($con, $query);	
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
	function addAnalysis($con, $tname, $tid, $pid, $fid, $uid) {
		$query = sprintf(
			"INSERT INTO %s.%s (analysisName, analysisID, datasetID, dataInputFileID, userIdx, analysisTime) values ('%s', '%s', %d, %d, %d, now())",
				$this::$DBNAME, $this::$TASKTABLE,
				$tname, $tid, $pid, $fid, $uid);
		return $this->executeQuery($con, $query);
	}

	function getAnalysis($con, $uid, $pid, $fid, $tid) {
		$query = sprintf("SELECT * FROM %s.%s a, %s.%s p, %s.%s f ".
			"WHERE a.userIdx=%d and a.datasetID=p.datasetID and a.dataInputFileID=f.dataInputFileID", 
			$this::$DBNAME, $this::$TASKTABLE, $this::$DBNAME, $this::$PROJECTTABLE, $this::$DBNAME, $this::$FILETABLE, $uid);
		if(!is_null($tid)) {
			$query = $query." and a.analysisID=".$tid;
		} 
		if(!is_null($pid)) {
			$query = $query." and a.datasetID=".$pid;	
		}
		if(!is_null($fid)) {
			$query = $query." and a.dataInputFileID=".$fid;	
		}
		return $this->findMany($con, $query);
	}

	function updateAnalysisStatus($con, $tid, $status) {
		$analysis = $this->getAnalysis($con, null, null, null, $tid);
		if(is_null($analysis)) {
			return false;
		}

		$query = sprintf("UPDATE %s.%s SET analysisStatus = %d WHERE analysisID = '%s'", $this::$DBNAME, $this::$TASKTABLE, $status, $tid);
		return $this->executeQuery($con, $query);
	}
}
?>