<?php
class UserModule{
    public static $SUCCESS = false;
    public static $RESULT = null;
    private $dbModule = null; 

    function dbm() {
        if(is_null($this->dbModule)) {
            require_once '../common/db.php';
            $this->dbModule = new DatabaseModule();
        }
    } 

    function crypt($userId, $pass) {
        return hash('sha256', md5($userId.$pass));    
    } 

    function authenticateUser($email,$pass) {
        $this->dbm();

        $con = $this->dbModule->connect();
        $result = $this->dbModule->findUser($con, $email);
        if(is_null($result)) {
            $this::$RESULT = "User ID does not exist!";    
        } else {
            //hashed password
            $hashed = $this->crypt($email, $pass);
            if(!is_null($result) && $hashed == $result['userPass']) {
                session_start();
                session_cache_expire(3600);
                ini_set("session.cookie_lifetime","3600");
                ini_set("session.gc-maxlifetime", 3600);

                $_SESSION['authenticated'] = 'true';
                $_SESSION['userId'] = $result['userEmail'];
                $_SESSION['userIdx'] = $result['userIdx'];

                $this::$SUCCESS = true;
            } else {
                $this::$RESULT = "Password is invalid!";    
            }
        }
    }

    function registerUser($pass, $name, $email, $affil) {
        $this->dbm();

        $con = $this->dbModule->connect();
        $result = $this->dbModule->findUser($con, $email);
        if(isset($result)) {
            $this::$RESULT = "email already exists!";    
        } else {
            //hashed password
            $hashed = $this->crypt($email, $pass);
            $added = $this->dbModule->addUser($con, $hashed, $name, $email, $affil);
            if($added == TRUE) {
                $this->dbModule->commit($con);
                $this->authenticateUser($email, $pass);
            } else {
                $this::$RESULT = "Registration failed. Please try it later.";
            }
        }
    }
}
?>