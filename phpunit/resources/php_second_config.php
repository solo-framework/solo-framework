<?php

return array
(
	"@extends" => "phpunit/resources/php_config.php",
	
	"section" => array
	(
		"test" => "string",
		"int" => 10,
		"array" => array (10,12,13,14)
	),
	
	"another" => array
	(
		"test" => "redeclader in second"
	),
	
	"main" => array
	(
		"int" => 12,
		"lalala" => "sdsd",
		"arr" => array(3, 4)
	),
	
	"second" => array
	(
		"secondVal" => "secondVal"
	),
	
	"second2" => array
	(
		"secondVal" => "secondVal"
	),
);

?>