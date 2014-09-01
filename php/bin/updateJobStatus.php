<?php
	
	$analysisId = null;
	$status = 0;

	if($argc != 3) {
		exit("Usage: updateJobStatus.php jobId status[running - 1, completed - 2, failed - 3] ($argc)\n");
	}

	$analysisId = $argv[1];
	$status = (int)$argv[2];

	require_once(realpath(dirname(__FILE__))."/../common/db.php");
  $dbModule = new DatabaseModule();
  $con = $dbModule->connect();
  $result = $dbModule->updateAnalysisStatus($con, $analysisId, $status);

  exit($result ? "Status updated" : "Status update failed");
?>