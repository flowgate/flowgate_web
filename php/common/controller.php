<?php
    require_once "./constants.php";

    # u_ : user jobs
    # f_ : file jobs
    # t_ : task jobs
    $_job = (isset($_POST['j'])?$_POST['j']:(isset($_GET['j'])?$_GET['j']:null));
        #controller error message
    $_err = "request failed.";
    $message['success'] = false;

    require_once "./session.php";
    if(strpos($_job, "u_")===false) {
        isSessionAlive();
    } else {
        session_start();
    }

    	//session_start();
    $_uid = isset($_SESSION['authenticated'],$_SESSION['userId'])?$_SESSION['userId']:null;
    $_uidx = isset($_SESSION['authenticated'],$_SESSION['userIdx'])?$_SESSION['userIdx']:null;

    $redirectTo = null;

    #assigns correct module according to the passed job type
    $_module = null;
    if($_job[0]=='f') {
        require_once '../module/file.php';
        $_module = new FileModule();
    } elseif($_job[0]=='p') {
        require_once '../module/project.php';
        $_module = new ProjectModule();
    } elseif($_job[0]=='t') {
        require_once '../module/task.php';
        $_module = new TaskModule();    
    } elseif($_job[0]=='r') {
        require_once '../module/ras.php';
        $_module = new RASModule();    
    } else {
        require_once '../module/user.php';
        $_module = new UserModule();    
    }  

    switch($_job) {
    	case "f_a": //add file

            //create user directory if not exists
            $uploaddir = $FILE_DIR."/".$_uidx."/";
            if(!file_exists($uploaddir)) {
                mkdir($uploaddir, 0755, true);
            }

            $_fname = basename($_FILES['dataset']['name']);

            //avoid file overwrite by appending current time in milliseconds
            $uploadfile = $uploaddir . $_fname;
            $duplicateCount = 1;

            $justFileName = substr($_fname, 0, strpos($_fname, '.'));
            $justExtension = substr($_fname, strpos($_fname, '.'));
            while(file_exists($uploadfile)) {
                $_fname = $justFileName."_".$duplicateCount++.$justExtension;
                $uploadfile = $uploaddir.$_fname;
            }

            if (move_uploaded_file($_FILES['dataset']['tmp_name'], $uploadfile)) {
                $_fdesc = $_POST['fdesc'];
                $_pid = $_POST['pid'];
                $_module->addFile($_fname, $_fdesc, $_pid, $_uidx);
                $redirectTo = "file";
            }
            break;
    	case "f_u": //get user files
            $_pid = $_GET['pid'];
            $_module->getFiles($_uidx, $_pid, null);
            $message = $_module::$RESULT;
            break;
        case "p_a": //add new project
            $_pname = (isset($_POST['pname'])?$_POST['pname']:null);
            $_pdesc = (isset($_POST['pdesc'])?$_POST['pdesc']:null);
            $_module->addProject($_pname, $_pdesc, $_uidx);

            if($_module::$SUCCESS){
                $_SESSION['currp'] = $_pname;
                $_SESSION['currpId'] = $_module::$CURRID;
                $redirectTo = "project";
            }
            break;
        case "p_u": //get user projects
            $_module->getUserProject($_uidx);
            $message['projects'] = $_module::$RESULT;
            break;
        case "p_g": //get current session project
            // $_currProject = isset($_SESSION['currp'])?$_SESSION['project']:null;
            $_module::$SUCCESS = isset($_SESSION['currp']);
            if($_module::$SUCCESS) {
                $message['currp'] = $_SESSION['currp'];
                $message['success'] = $_module::$SUCCESS;
            }
            break; 
        case "p_s":
            $_SESSION['currp'] = $_POST['pname'];
            $_SESSION['currpId'] = $_POST['pid'];
            $_module::$SUCCESS = true;
            $redirectTo = "file";
            break;
        case "t_s":
            set_time_limit(0);

            $params = array(
              "uid" => $_uid,
              "uidx" => $_uidx,
              "filesDir" => $FILE_DIR,
              "resultsDir" => $RESULT_DIR,
              "pid" => $_POST['pid'],
              "fid" => $_POST['fid'],
              "name" => $_POST['name'],
              "workflow" => $_POST['workflow'],
              "lsid" => $_POST['lsid'],
              "filtering" => $_POST['filtering'],
              "manual" => $_POST['manual'],
              "bins" => $_POST['bins'],
              "density" => $_POST['density'],
              "pop" => $_POST['pop'],
              "flockId" => $GP_FLOCK_LSID,
              "imageId" => $GP_IMAGE_LSID
            );

            $jid = $_module->submit($params);
            $message['jid'] = $jid;
            $redirectTo = "result";
            break;
        case "t_u": //get user jobs
            $pid = $_GET['pid'];
            $fid = $_GET['fid'];
            $_module->getAnalysis($_uidx, $pid, $fid, null);
            $message['results'] = $_module::$RESULT;
            break;
        case "u_r": //register user
            $uemail = getOrNull($_POST['uemail']);
            $ufirst = getOrNull($_POST['ufirst']);
            $ulast = getOrNull($_POST['ulast']);
            $uname = $ufirst.' '.$ulast;
            $upass = getOrNull($_POST['pass']);
            $affil = getOrNull($_POST['uaffil']);

            if(isset($uemail, $uname, $upass)) {
                if(!filter_var($uemail, FILTER_VALIDATE_EMAIL)) {
                    $_module::$RESULT = "email is not valid.";
                } elseif(empty($uname)) {
                    $_module::$RESULT = "name is required.";
                } elseif(empty($upass) || strlen($upass) < 4) {
                    $_module::$RESULT = "user id and password should be at least 4 chracters.";
                } else {
                    $_module->registerUser($upass, $uname, $uemail, $affil);
                }
            }
            $redirectTo = "about";
            break;
        case "u_l":
            $_module->authenticateUser($_POST['uname'], $_POST['pass']);
            $redirectTo = "about";
            break;
        case "u_s":
            $message['s_a'] = checkSession();
            $_module::$SUCCESS = true;
            break;    
        default: 
            $_module::$SUCCESS = isSessionAlive();
    }

    if(!$_module::$SUCCESS){ //central error handling
        $errMsg = "";
        if($_job == "u_l") {
            $errMsg = "Your username or password was incorrect."; 
        } elseif($_job == "u_r") {
            $errMsg = $_module::$RESULT;
        } elseif($_job == "p_a") {
            $errMsg = $_module::$RESULT;
        } elseif($_job == "f_a") {
            $errMsg = "File upload failed.";
        } elseif($_job == "t_s") {
            $errMsg = "Job submission failed.";
        }

        if($errMsg != "") { //send back to the origin page with an error message
            $_SESSION['error'] = $errMsg; 
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else { //error response in json
            $message['error']['msg'] = (isset($_module::$RESULT)?$_module::$RESULT:$_err);
        }        
    } else {
        if(!empty($redirectTo)) {
            redirect($redirectTo);
        } else {
            $message['success'] = true;
            echo json_encode($message);
        }
    }

    function redirect($where) {
        $loc = "../../index.html";
        switch($where) {
            case "project":
                $loc = "../view/project.php";
                break;
            case "file":
                $loc = "../view/file.php";
                break;
            case "result":
                $loc = "../view/result.php";
                break;
            case "about":
                $loc = "../../about.html";
                break;
            default:
                break;
        }
        header("Location: ".$loc);
        die("Redirecting...");    
    }

    function getOrNull(&$value, $default = null) {
      return isset($value) ? $value : $default;
    }
?>