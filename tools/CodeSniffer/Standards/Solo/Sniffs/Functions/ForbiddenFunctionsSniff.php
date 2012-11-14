<?php
/**
 * Проверка использования запрещенных функции
 * 
 * PHP version 5
 * 
 * @category Framework
 * @package  Core
 * @author   Andrey Filippov <afi.work@gmail.com>
 * @license  %license% name
 * @version  SVN: $Id: Entity.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */
class Solo_Sniffs_Functions_ForbiddenFunctionsSniff extends Squiz_Sniffs_PHP_ForbiddenFunctionsSniff
{

	/**
	 * A list of forbidden functions with their alternatives.
	 *
	 * The value is NULL if no alternative exists. IE, the
	 * function should just not be used.
	 *
	 * @var array(string => string|null)
	 */
	protected $forbiddenFunctions = array(
		'sizeof' => 'count', 
		'delete' => 'unset', 
		'print' => 'echo', 
		'ereg' => 'preg_match', 
		'eregi' => 'preg_match', 
		'split' => 'explode() or preg_split()',
		'spliti' => 'preg_split() with the \'i\' modifier',
		'mysql_db_query' => 'use mysql_select_db() and mysql_query()',
		'ereg_replace' => 'preg_replace()',
	);
}
?>