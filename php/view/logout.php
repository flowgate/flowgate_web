<?php
	session_start();
	unset($_SESSION["start"], $_SESSION['authenticated'], $_SESSION['userId'], $_SESSION['userIdx']);
	header("Location:../../index.html");
?>