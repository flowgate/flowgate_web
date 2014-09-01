<?php
	class ProjectModule {
        public static $SUCCESS = false;
        public static $RESULT;
        public static $CURRID;
        private $dbModule = null; 

        function dbm() {
            if(is_null($this->dbModule)) {
                require_once 'db.php';
                $this->dbModule = new DatabaseModule();
            }
        } 

        function addProject($_pname, $_pdesc, $_uid) {
            if($_pname && $_uid) {
                $this->dbm();
                $con = $this->dbModule->connect();
                $duplicate = $this->dbModule->findProject($con, $_pname, $_uid);
                
                if($duplicate) {
                    $this::$RESULT = "\"$_pname\" already exists!";
                } else {
                    $result = $this->dbModule->addProject($con, $_pname, $_pdesc, $_uid);
                    if(isset($result) && $result == "success") {
                        $result = $this->dbModule->findProject($con, $_pname, $_uid); 
                        if(!is_null($result)) {
                            $this::$CURRID = $result['datasetID']; 
                            $this::$SUCCESS = true;
                            $this::$RESULT = "Added project '".$_pname."'";   
                        }
                    } else {
                        $this::$RESULT = $result;
                    }
                }
            } else {
                $this::$RESULT = "Project name or user information is missing.";
            }
        }

        function getUserProject($_uid) {
            $this->dbm();
            $con = $this->dbModule->connect();
            $result = $this->dbModule->findUserProject($con, $_uid);
            if(!is_null($result)) {
                $this::$RESULT = $result;
            }
            $this::$SUCCESS = true;
        }
    }
?>