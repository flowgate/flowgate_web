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

    function autheticateUser($userId,$pass) {
        $this->dbm();

        $con = $this->dbModule->connect();
        $result = $this->dbModule->findUser($con, $userId);
        if(is_null($result)) {
            $this::$RESULT = "User ID does not exist!";    
        } else {
            //hashed password
            $hashed = $this->crypt($userId, $pass);
            if(!is_null($result) && $hashed == $result['u_password']) {
                session_start();
                session_cache_expire(3600);
                ini_set("session.cookie_lifetime","3600");
                ini_set("session.gc-maxlifetime", 3600);

                $_SESSION['authenticated'] = 'true';
                $_SESSION['userId'] = $result['u_id'];

                $this::$SUCCESS = true;
            } else {
                $this::$RESULT = "Password is invalid!";    
            }
        }
    }

    function registerUser($userId, $pass, $name, $email) {

    }
}
?>