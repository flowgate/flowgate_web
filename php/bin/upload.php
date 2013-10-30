<?php
	error_reporting(E_ALL | E_STRICT);
	require_once '../common/session.php';
	if(isSessionAlive()) {
		require('uploadHandler.php');
		$upload_handler = new UploadHandler();

		// require_once '../module/file.php';
  //       $_module = new FileModule();
  //       $_module->addFile($_fname, $_pid, $_uid, $_FILES['uploadFile']['name'], $_FILES['uploadFile']['tmp_name']);
	}
?>
