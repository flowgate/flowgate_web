<?php
	$name = $_POST['name'];

	$fp = fopen("../Files/file_sequence.txt",'r');
	if (!$fp) {
		echo "Unable to open file";
		exit;
	}
	$sequence = fgets($fp,999);
	fclose($fp);
	$sequence = rtrim($sequence,"\n");
	$sequence = rtrim($sequence,"\r");
	$sequence++;

	$destDir = "../Files/$sequence/";
	mkdir($destDir);
	//$target_path = $destDir . basename($_FILES['uploadFile']['name']);
	$target_path = $destDir . 'fcs.txt';
        move_uploaded_file($_FILES['uploadFile']['tmp_name'],$target_path);

	$fp = fopen("../Files/files.txt",'a');
	$target = basename($_FILES['uploadFile']['name']);
	fwrite($fp,$sequence . "\t" . $name . "\t" . $target . "\t" . "Loaded\n");
	fclose($fp);

	$fp = fopen("../Files/file_sequence.txt",'w');
	fwrite($fp,$sequence);
	fclose($fp);

	//`php runFlock.php $sequence&`;

        echo '{success:true, file:' . json_encode($_FILES['uploadFile']['name']) . '}';
?>
