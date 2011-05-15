<?php
return array
(
	// Эта директива указывает, что нужно расширить и переопределить
	// настройки, указанные в файле "app/Config/common.php"
	"@extends" => "/config/common.php",
	
	"master" => array
	(
		"host" => "localhost",
		"user" => "root",
		"password" => "password",
		"encoding" => "utf-8",
		"database" => "test",
		"driver" => "MySQL",
		"persist" => "false",
		"port" => "3306"
	),
	
	"list" => array
	(
		"one",
		"two",
		"three"
	)
);
?>