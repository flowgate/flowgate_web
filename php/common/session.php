<?php

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

  function isSessionAlive() {
    session_cache_expire( 20 );
    session_start();
    //session_write_close();
    $rtnVal = false;

    if(!checkSession()) {
      header("Location: ../view/logout.php");
      exit;
    }

    if(isset($_SESSION['authenticated']) && $_SESSION['authenticated']=="true" && isset($_SESSION['userId']) && isset($_SESSION['userIdx'])) {
      $rtnVal = true;
    }
    
    if(!$rtnVal) {
      header("Location: ../view/login.php");
    }

    return $rtnVal;
  }
?>