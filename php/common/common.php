<?php
  $context = dirname($_SERVER['SCRIPT_NAME']).'/';

  function checkSession() {
    $inactive = 3600;
    
    if(isset($_SESSION['start']) ) {
      $session_life = time() - $_SESSION['start'];
      if($session_life > $inactive){
        return false;  
      }
    }
    $_SESSION['start'] = time();
    return true;
  }

  function reqValidation() {
    session_cache_expire( 20 );
    session_start();

    if(!checkSession()) {
      header("Location: pages/logout.php");
      exit;
    }
    if(!isset($_SESSION['authenticated']) || $_SESSION['authenticated']!="true" || !isset($_SESSION['userId'])) {
      header("Location: pages/logout.php");
      exit;
    }
  }
?>