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
require_once "../Solo/Core/Request.php";

use Solo\Core\Route;
use Solo\Core\Request;

require_once "../../../../config/routing.php";

$uri = @$argv[1];
if (!$uri)
{
	echo "use '{$argv[0]} /test/uri' to debug";
	exit();
}	
	
$_SERVER["REQUEST_METHOD"] = "GET";

echo "\n\n" . str_repeat("-", 40) . "\n";
echo "Begin debugging routing for URI: {$uri}\n";
echo str_repeat("-", 40) . "\n";

/** @var $route Route*/
echo "Matches with:\n\t" . $route->debug($uri);
echo "\n\n";
echo "Available variables:\n";

foreach ($_GET as $k => $v)
{
	echo "\t{$k} => {$v}\n";
}

echo "\n\n";
