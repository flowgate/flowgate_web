<?php
class DatabaseModule {
	private $USER_TABLE='gofcm_user';
	private $FILE_TABLE='gofcm_file';
	private $TASK_TABLE='gofcm_task';

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

	function find($coll, $query, $result, $close) {
		$coll = $this->getCollection($coll);
		$results = $coll->find($query, $result);
		if($close) {
			$this->close();
		}
		return iterator_to_array($results);
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
		return $this->findOne(
			$this->USER_TABLE, 
			array('u_id' => $uid), 
			array("u_projects"=>false), 
			true
		);
	}
	function addUser($uid, $password, $email, $name, $affil) {
		$user = array(
			"_id" => new MongoId().'',
		    "u_id" => $uid,
		    "u_pass" => $password,
		    "u_email" => $email,
		    "u_name" => $name,
		    "u_affil" => $affil,
		    "u_projects" => array()
		);
		return $this->insert($this->USER_TABLE, $user);
	}

	//project
	function findUserProject($uid) {
		return $this->find(
			$this->USER_TABLE, 
			array('_id' => $uid), 
			array("u_projects"=>true, "_id"=>false), 
			true
		);	
		//return $this->findOne($this::$USER_TABLE, array('u_projects.p_name' => $pname), true);
	}

	function addProject($pname, $pdesc, $uid) {
		$result = 'loading project failed!';

		$userColl = $this->getCollection($this->USER_TABLE);
		$old = $userColl->findOne(array('u_projects.p_name' => $pname), array('u_projects.$'));

		if(!is_null($old) && count($old)>0) {
			$result = 'project name already exsits!';
		} else {
			$project = array(
				"p_id" => new MongoId().'',
			    "p_name" => $pname,
			    "p_desc" => $pdesc
			);
			$result = $userColl->update(
				array("_id" => $uid),
				array('$push' => array('u_projects'=>$project)),
				array("fsync" => true)
		    );
		    if($result['ok']==1 && $result['n']>0) {
		    	$result = $project['p_id'];	
		    } else {
		    	$result = null;
		    }
		}

		$this->close();
		return $result;
	}

	function getProjectNames($uid) {
		$projects = $this->find(
			$this->USER_TABLE, 
			array("_id" => $uid), 
			array("u_projects"=>true, "_id"=>false), 
			false
		);
		$project_names = array();
		foreach($projects[0]['u_projects'] as $project) {
			$project_names[$project['p_id']]=$project['p_name'];
		}
		return $project_names;	
	}
	
	//file
	function addFile($file) {
		/*$file = array(
		    "f_name" => $name,
		    "f_status" => 1,
		    "f_project_id" => $pid,
		    "f_org_name" => $org,
		    "f_user_id" => $uid,
		    "f_tasks" => array()
		);*/
		return $this->insert($this->FILE_TABLE, $file);
	}

	function getFile($uid, $pid, $fid) {
		$project_names = $this->getProjectNames($uid);

		$search = array(
			"f_user_id" => $uid
		);
		if(isset($fid)) {
			$search["f_id"]=$fid;
		} elseif(isset($pid)) {
			$search["f_project_id"]=$pid;	
		}
		$files = $this->find($this->FILE_TABLE, $search, array('f_tasks'=>false), true);

		$_files = array();
		foreach($files as $file) {
			$file['p_name']=$project_names[$file['f_project_id']];
			array_push($_files, $file);
		}
		return $_files;
	}

	
	//task
	function addTask($fid, $task) {
		
		$fileColl = $this->getCollection($this->FILE_TABLE);
		$result = $fileColl->update(
			array("_id" => $fid),
			array('$push'=> array("f_tasks"=>$task))
	    );
	    $this->close();
		return $result;
	}

	function getTasks($uid, $pid) {
		$project_names = $this->getProjectNames($uid);

		$search = array(
			"f_user_id" => $uid,
			"f_project_id" => $pid
		); 

		$files = $this->find($this->FILE_TABLE, $search, array(), true);
		$tasks = array();
		foreach($files as $file) {
			foreach($file['f_tasks'] as $task) {
				$task['p_name']=$project_names[$file['f_project_id']];
				$task['f_name']=$file['f_name'];
				array_push($tasks, $task);
			}
		}
		return $tasks; 
	}
	
}
?>