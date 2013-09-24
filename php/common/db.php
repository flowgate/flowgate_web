<?php
class DatabaseModule {
	private static $DBNAME = "gofcm";
	private static $USERTABLE = "gofcm_users";
	private static $PROJECTTABLE = "gofcm_projects";
	private static $FILETABLE = "gofcm_files";
	private static $TASKTABLE = "gofcm_tasks";

	function connect() {
		$username = "ifx_gofcm_adm";
        $password = "ifx_gofcm_adm";
        $hostname = "genepatt-dev.jcvi.org:3666"; 

        //connection to the database
        $con = mysqli_connect($hostname, $username, $password, DatabaseModule::$DBNAME) or die("Unable to connect to MySQL");

        /*$con = new Mongo("mongodb://gofcm_user:gofcm_user@localhost:27017/gofcm");
        $dbname = 'gofcm';
        $userColl = 'users';*/
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
		while ($only = $result->fetch_assoc()) {
	        array_push($many, $only);
	    }
	    mysqli_free_result($result);

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
		$query = sprintf("SELECT * FROM %s.%s WHERE u_id='%s'", $this::$DBNAME, $this::$USERTABLE, $userName);
		return $this->findOne($con, $query);
	}

	function addUser($con, $userName, $password, $email, $name, $affil) {
		$query = sprintf(
			"INSERT INTO %s.%s (u_id, u_name, u_password, u_email, u_affil) values ('%s', '%s','%s', '%s','%s')", 
			$this::$DBNAME, $this::$USERTABLE,
			$userName, $password, $email, $name, $affil
		);
		return $this->add($con, $query);	
	}


	//project
	function findUserProject($con, $uid) {
		$query = sprintf("SELECT p_id, p_name FROM %s.%s WHERE p_user_id='%s'", $this::$DBNAME, $this::$PROJECTTABLE, $uid);
		return $this->findMany($con, $query);	
	}

	function findProject($con, $pname) {
		$query = sprintf("SELECT * FROM %s.%s WHERE p_name='%s'", $this::$DBNAME, $this::$PROJECTTABLE, $pname);
		return $this->findOne($con, $query);
	}

	function addProject($con, $pname, $pdesc, $uid) {
		$result = 'loading project failed!';

		$old = $this->findProject($con, $pname);
		if(!is_null($old)) {
			$result = 'project name already exsits!';
		} else {
			$query = sprintf(
				"INSERT INTO %s.%s (p_name, p_desc, p_user_id) values ('%s', '%s', '%s')", 
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
	function addFile($con, $pid, $name, $org, $uid) {
		$query = sprintf(
			"INSERT INTO %s.%s (f_name, f_org_name, f_status, f_project_id, f_user_id) values ('%s', '%s', 1, %d, '%s')", 
			$this::$DBNAME, $this::$FILETABLE,
			$name, $org, $pid, $uid
		);
		return $this->add($con, $query);	
	}

	function getFile($con, $uid, $pid, $fid) {
		$query = sprintf("SELECT * FROM %s.%s f, %s.%s p WHERE f.f_user_id='%s' AND f_project_id=p.p_id", 
			$this::$DBNAME, $this::$FILETABLE, $this::$DBNAME, $this::$PROJECTTABLE, $uid);
		if(isset($fid)) {
			$query = $query." AND f.f_id=".$fid;
		} elseif(isset($pid)) {
			$query = $query." AND f.f_project_id=".$pid;	
		}
		return $this->findMany($con, $query);
	}

	//task
	function addTask($con, $tname, $tbin, $tden, $fid, $pid, $uid) {
		$query = sprintf(
			"INSERT INTO %s.%s (t_name, t_bin, t_density, t_status, t_file_id, t_project_id, t_user_id) values ('%s', %d, %d, %d, %d, %d, '%s')",
				$this::$DBNAME, $this::$TASKTABLE,
				$tname, $tbin, $tden, 1, $fid, $pid, $uid);
		return $this->add($con, $query);
	}

	function getTask($con, $uid, $pid, $tid) {
		$query = sprintf("SELECT * FROM %s.%s t, %s.%s p, %s.%s f ".
			"WHERE t.t_user_id='%s' AND t.t_project_id=p.p_id AND t.t_file_id=f.f_id", 
			$this::$DBNAME, $this::$TASKTABLE, $this::$DBNAME, $this::$PROJECTTABLE, $this::$DBNAME, $this::$FILETABLE, $uid);
		if(isset($tid)) {
			$query = $query." AND t.t_id=".$tid;
		} elseif(isset($pid)) {
			$query = $query." AND t.t_project_id=".$pid;	
		}
		return $this->findMany($con, $query);
	}
}
?>