<?php
	require_once 'modules/db_mongo.php';
    $mongo = new DatabaseModule(); 

    $userId = 'hkim';
    $pass = '1';
    $email = 'hkim@jcvi.org';
    $name = 'Hyunsoo Kim';
    $affil = "JCVI";
    $mongo->addUser($userId, hash('sha256', md5($userId.$pass)), $email, $name, $affil);

    //error_log(print_r($mongo->findUser($userId), true));

    //error_log(print_r(($mongo->findUserProject('51f6af03a9d945c814f26737')), true));

    //$mongo->addProject('test', '1', '51f6af03a9d945c814f26737');
    //error_log(print_r($mongo->findUserProject($userId), true)); 
    //error_log(print_r($mongo->addFile($file), true));
	//error_log(print_r($mongo->getFile('51f2d27fa9d945c814ebadae',null,null),true));

	//$mongo->addTask('test2', 15, 15, '51f6a1b0a9d945c814ff63a7');
	//error_log(print_r($mongo->getTask('51f2d27fa9d945c814ebadae',null,null),true));
	//error_log(print_r($mongo->getTask('51f2d27fa9d945c814ebadae',null,'51f681f8a9d945c814b8baf8'),true));
	//error_log(print_r($mongo->getTask('51f2d27fa9d945c814ebadae','51f2d761a9d945c314321568',null),true));
?>