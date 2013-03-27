<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */


return array
(
	// Эта директива указывает, что нужно переопределить
	// или расширить
	// настройки, указанные в файле phpunit/resources/php_main_config.php
	"@extends" => "./resources/config_base_1.php",

	"section" => array
	(
		"param" => "value_from_base_2"
	)
);