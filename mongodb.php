<?php
	require_once 'modules/db_mongo.php';
    $mongo = new MongoModule(); 

    $userId = 'hkim';
    $pass = '1';
    $email = 'hkim@jcvi.org';
    $name = 'Hyunsoo Kim';
    $affil = "JCVI";
    //$mongo->addUser($userId, hash('sha256', md5($userId.$pass)), $email, $name, $affil);

    //error_log(print_r($mongo->findUser($userId), true));

    //error_log(print_r(($mongo->findUserProject($userId)), true));

    error_log($mongo->addProject('test1', 'test1', $userId));
    error_log(print_r($mongo->findUserProject($userId), true)); 
?>