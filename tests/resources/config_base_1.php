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
	"@extends" => "./tests/resources/config_base.php",

	"section" => array
	(
		"param" => "value_from_base_1",
		"param3" => "value3"
	)
);