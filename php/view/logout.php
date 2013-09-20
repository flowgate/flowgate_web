<?php
	session_start();
	unset($_SESSION["start"], $_SESSION['authenticated'], $_SESSION['userId']);
	header("Location:../../index.html");
?>