<?php
if($_SERVER['REMOTE_ADDR']=='192.168.1.50'){
	return [
	    'class' => 'yii\db\Connection',
	    'dsn' => 'mysql:host=localhost;dbname=squibdri_squibhub',
	    'username' => 'root',
	    'password' => '',
	    'charset' => 'utf8',
	];
}else{
	return [
	    'class' => 'yii\db\Connection',
	    'dsn' => 'mysql:host=localhost;dbname=squibdri_squibhub',
	    'username' => 'squibdri_squib',
	    'password' => 'v~TZXm?^oW!B',
	    'charset' => 'utf8',
	];
}