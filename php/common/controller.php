<?php
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
    }

	//session_start();
    $_uid = isset($_SESSION['authenticated'],$_SESSION['userId'])?$_SESSION['userId']:null;
    $_uidx = isset($_SESSION['authenticated'],$_SESSION['userIdx'])?$_SESSION['userIdx']:null;


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
            $_fname = $_POST['fname'];
            $_pid = $_POST['pid'];
    		$_module->addFile($_fname, $_pid, $_uid, '');
            //$message['file'] = $_FILES['uploadFile']['name'];
    		break;
    	case "f_u": //get user files
            $_pid = $_POST['pid'];
            $_module->getFiles($_uid, $_pid, null);
            $message = $_module::$RESULT;
    		break;
        case "p_a": //add new project
            $_pname = (isset($_POST['pname'])?$_POST['pname']:null);
            $_pdesc = (isset($_POST['pdesc'])?$_POST['pdesc']:null);
            $_module->addProject($_pname, $_pdesc, $_uidx);
            $message['msg'] = $_module::$RESULT;
            $_SESSION['currp'] = $_pname;
            $_SESSION['currpId'] = $_module::$CURRID;
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
            break;
        case "t_a":
            $_tname = $_POST['tname'];
            $_tbin = $_POST['tbin'];
            $_tden = $_POST['tden'];
            $_fid = $_POST['fid'];
            $_pid = $_SESSION['currpId'];
            
            $_module->runAnalysis($_tname,$_tbin,$_tden,$_fid,$_pid,$_uid);
            $message['tname'] = $_tname;
            break;
        case "t_u": //get user files
            $_module->getTasks($_uid, (isset($_SESSION['currpId'])?$_SESSION['currpId']:null), null);
            $message = $_module::$RESULT;
            $message['currp'] = $_SESSION['currp'];
            break;
        case "u_r": //register user
            if(isset($_POST['uemail'], $_POST['uname'], $_POST['pass'], $_POST['uid'])) {
                $_module->registerUser($_POST['uid'], $_POST['pass'], $_POST['uname'], $_POST['uemail'], $_POST['uaffil']);
            }
            break;
        case "u_l":
            $_module->authenticateUser($_POST['uname'], $_POST['pass']);
            break;
        case "u_s":
            $message['s_a'] = checkSession();
            $_module::$SUCCESS = true;
            break;    
        default: 
            $_module::$SUCCESS = isSessionAlive();
    }

    if(!$_module::$SUCCESS){
        $message['error']['reason'] = (isset($_module::$RESULT)?$_module::$RESULT:$_err);
    } else {
        if($_job == "u_l") {
            redirectMain();
        } else {
            $message['success'] = true;
        }
    }
    echo json_encode($message);

    function redirectMain() {
        header("Location: ../../index.html");
        die("Redirecting to: index.html");    
    }

?>