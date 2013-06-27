<?php
	session_start();
	unset($_SESSION["start"], $_SESSION['authenticated'], $_SESSION['userName']);
	header("Location: ./login.php");
?>