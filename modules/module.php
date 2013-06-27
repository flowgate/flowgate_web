<?php
abstract class module {
	public $_success = false;
	public $_msg = null;

    function dbm() {
        if(is_null($this->dbModule)) {
            require_once '../databaseModule.php';
            $this->dbModule = new DatabaseModule();
        }
    }

}
?>