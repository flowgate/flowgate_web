<?php
	class ProjectModule {
        public static $SUCCESS = false;
        public static $RESULT;
        public static $CURRID;
        private $dbModule = null; 

        function dbm() {
            if(is_null($this->dbModule)) {
                require_once 'db_mongo.php';
                $this->dbModule = new DatabaseModule();
            }
        } 

        function addProject($_pname, $_pdesc, $_uid) {
            if($_pname && $_uid) {
                $this->dbm();
                $result = $this->dbModule->addProject($_pname, $_pdesc, $_uid);
                if(isset($result)) {
                    $this::$SUCCESS = true;
                    $this::$RESULT = "Added project '".$_pname."'";
                    $this::$CURRID = $result;
                } else {
                    $this::$RESULT = $result;
                }
            } else {
                $this::$RESULT = "Project name or user information is missing.";
            }
        }

        function getUserProject($_uid) {
            $this->dbm();
            $result = $this->dbModule->findUserProject($_uid);
            if(!is_null($result) && count($result)>0) {
                $this::$RESULT = $result[0]['u_projects'];
                $this::$SUCCESS = true;
            }
        }
    }
?>