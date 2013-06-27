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
                $result = $this->dbModule->addProject($con, $_pname, $_pdesc, $_uid);
                if($result == 'success') {
                    $this::$SUCCESS = true;
                    $this::$RESULT = "Added project '".$_pname."'";
                    $this::$CURRID = mysqli_insert_id($con);
                    $this->dbModule->commit($con);
                } else {
                    $this::$RESULT = $result;
                    $this->dbModule->rollback($con);
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
                $this::$SUCCESS = true;
            }
            $this->dbModule->close($con);
        }
    }
?>