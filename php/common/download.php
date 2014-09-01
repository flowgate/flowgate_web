<?php
	include("../common/constants.php");
  include("../common/session.php"); 
  isSessionAlive();

	$filename = isset($_GET['fn']) ? $_GET['fn'] : null;
	$uidx = isset($_GET['uidx']) ? $_GET['uidx'] : null;
	$jid = $_GET['jid'];
	$filepath = "$RESULT_DIR/$jid/result.zip";

	session_write_close();

	set_time_limit(0);
	ignore_user_abort(false);
	ini_set('output_buffering', 0);
	ini_set('zlib.output_compression', 0);

	$chunk = 8 * 1024 * 1024;

	$fh = fopen($filepath, "rb");

	if($fh === false) {
		echo "Unable open file";
	}

	header('Pragma: public');
  header('Expires: 0');
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Cache-Control: private',false);
  header('Content-Type: application/zip');
  header('Content-Disposition: attachment; filename="result.zip"');
  header('Content-Transfer-Encoding: binary');
  header('Content-Length: '.filesize($filepath));

  while(!feof($fh)) {
		echo fread($fh, $chunk);
 		ob_flush();
 		flush();
 	}

  exit;

?>