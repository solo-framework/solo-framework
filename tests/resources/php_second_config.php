<?php

return array
(
	// Эта директива указывает, что нужно переопределить
	// или расширить
	// настройки, указанные в файле phpunit/resources/php_main_config.php
	"@extends" => "./resources/php_main_config.php",

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

	// этих секции не будет в результирующем наборе
	// данных, т.к. они не определены в основном файле
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