<?php
class LoginModule{
    public static $SUCCESS = false;
    public static $RESULT = null;
    private $dbModule = null; 

    function dbm() {
        if(is_null($this->dbModule)) {
            require_once 'db.php';
            $this->dbModule = new DatabaseModule();
        }
    } 

    function crypt($userName, $pass) {
        return hash('sha256', md5($userName.$pass));    
    } 

    function autheticateUser($userName,$pass) {
        $this->dbm();
        $con = $this->dbModule->connect();
        $result = $this->dbModule->findUser($con, $userName);

        if(is_null($result)) {
            $this::$RESULT = "User ID does not exist!";    
        } else {
            //hashed password
            $hashed = $this->crypt($userName, $pass);

            if(!is_null($result) && $hashed == $result['u_password']) {
                session_cache_expire( 20 );
                session_start();
                ini_set("session.cookie_lifetime","20");
                ini_set('session.gc-maxlifetime', 20);

                $_SESSION['authenticated'] = 'true';
                $_SESSION['userName'] = $userName;

                $this::$SUCCESS = true;
            } else {
                $this::$RESULT = "Password is invalid!";    
            }
        }
        $this->dbModule->close($con);
    }

    function registerUser($userId, $pass, $name, $email) {

    }
}
?>