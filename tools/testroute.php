<?php
/**
 * The script is used to check and test routing rules
 *
 * PHP version 5
 *
 * @package TestRoute
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

require_once "../Solo/Core/Route.php";

use Solo\Core\Route;

require_once "../../../../config/routing.php";

$uri = $argv[1];
if (!$uri)
	echo "use '{$argv[0]} /test/uri' to debug";
	exit();

/** @var $route Route*/
$route->debug($uri);
