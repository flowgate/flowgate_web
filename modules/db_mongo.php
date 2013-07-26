<?php
class MongoModule {
	private static $USER_TABLE='gofcm_user';

	private $mongo=null;

	function getCollection($colName) {
		if(is_null($this->mongo)) {
			$this->mongo = new MongoClient("mongodb://127.0.0.1:8788");
		}
		return $this->mongo->gofcm->$colName;	
	}

	function close() {
		$this->mongo->close(false);
	}

	function findOne($coll, $query, $result, $close) {
		$coll = $this->getCollection($coll);
		$one = $coll->findOne($query, $result);
		if($close) {
			$this->close();
		}
		return $one;	
	}

	function insert($coll, $obj) {
		$coll = $this->getCollection($coll);
		$result = $coll->insert($obj);
		$this->close();
		return $result;
	}

	//user
	function findUser($uid) {
		return $this->findOne($this::$USER_TABLE, array('u_id' => $uid), array(), true);
	}
	function addUser($uid, $password, $email, $name, $affil) {
		$user = array(
		    "u_id" => $uid,
		    "u_pass" => $password,
		    "u_email" => $email,
		    "u_name" => $name,
		    "u_affil" => $affil,
		    "u_projects" => array()
		);
		return $this->insert($this::$USER_TABLE, $user);
	}

	//project
	function findUserProject($uid) {
		return $this->findOne($this::$USER_TABLE, array('u_id' => $uid), array('u_projects'), true);	
		//return $this->findOne($this::$USER_TABLE, array('u_projects.p_name' => $pname), true);
	}

	function addProject($pname, $pdesc, $uid) {
		$result = 'loading project failed!';

		$userColl = $this->getCollection($this::$USER_TABLE);
		$old = $userColl->findOne(array('u_projects.p_name' => $pname), array('u_projects'));

		if(!is_null($old) && count($old)>0) {
			$result = 'project name already exsits!';
		} else {
			$project = array(
				"p_id" => new MongoId(),
			    "p_name" => $pname,
			    "p_desc" => $pdesc
			);
			$result = $userColl->update(
				array("u_id" => $uid),
				array('$push'=> array('u_projects'=>$project))
		    );
		    if($result) {
		    	$result = "success";	
		    }
		}

		$this->close();
		return $result;
	}
	/*
	//file
	function addFile($con, $name, $pid, $org, $uid) {
		$query = sprintf(
			"INSERT INTO %s.%s (f_name, f_status, f_project_id, f_org_name, f_user_id) values ('%s', 1, %d, '%s', '%s')", 
			$this::$DBNAME, $this::$FILETABLE,
			$name, $pid, $org, $uid
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
	*/
}
?>