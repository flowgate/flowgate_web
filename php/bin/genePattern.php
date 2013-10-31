<?php
    include("../common/constants.php");

	$input = $_POST['input'];
    $bins = $_POST['bins'];
    $density = $_POST['density'];
    $population = $_POST['pop'];

    require_once "../common/session.php";
    isSessionAlive();

    $success = false;

    //user id
    $uid = isset($_SESSION['authenticated'],$_SESSION['userId'])?$_SESSION['userId']:null;
    //job id
    $jid = v3UUID($uid);

    $result = runFlock($uid, $jid, "$FILE_DIR/$input", $bins, $density, $population);
    if($result != NULL) {
        $success = true;
    }
	$json = array();
    $json['success'] = $success;
    if($success) {
        $json['jid'] = $jid;
    }
    print json_encode($json);

	function runFlock($uid, $jid, $input, $bins, $density, $pop) {
        $cp = "../../lib/java";
	    $executor = "java".
	        " -classpath $cp/axis.jar:$cp/mail.jar:$cp/activation.jar:$cp/flockUtils.jar".
	        //" -Djava.awt.headless=true".
	        " org.immport.flock.utils.GenePattern ";
        $params = "$uid $input $bins $density $pop color $jid";
        //exec($executor.$params." > /dev/null 2>&1 &", $rtnVal);
        return shell_exec($executor.$params);
	}

    //Version 3 UUID from http://www.php.net/manual/en/function.uniqid.php#94959
    function v3UUID($uid) {
        // Get hexadecimal components of namespace
        $nhex = str_replace(array('-','{','}'), '', uniqid());

        // Binary Value
        $nstr = '';

        // Convert Namespace UUID to bits
        for($i = 0; $i < strlen($nhex); $i+=2) {
            $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
        }

        // Calculate hash value
        $hash = md5($nstr . $uid);

        return sprintf('%08s-%04s-%04x-%04x-%12s',
            // 32 bits for "time_low"
            substr($hash, 0, 8),
            // 16 bits for "time_mid"
            substr($hash, 8, 4),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 3
            (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
            // 48 bits for "node"
            substr($hash, 20, 12)
        );
    }
?>