<?php
/**
 * В этом файле проверяется устанавливка значений констант, необходимых
 * для работы фреймворка.
 * 
 * PHP version 5
 * 
 * @category Framework
 * @package  Core
 * @author   Andrey Filippov <afi@i-loto.ru>
 * @license  %license% name
 * @version  SVN: $Id: Entity.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

/**
 * FRAMEWORK_ROOT_DIR - абсолютный путь к каталогу, куда установлен framework
 * APPLICATION_DIR - абсолютный путь к каталогу, где находятся файлы приложения
 */
if (!defined("FRAMEWORK_ROOT_DIR") || !defined("APPLICATION_DIR"))
	throw new Exception("You must define FRAMEWORK_ROOT_DIR and APPLICATION_DIR constants");

?>