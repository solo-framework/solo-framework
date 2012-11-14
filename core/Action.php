<?php 
/**
 * Абстрактный класс. Предок всех действий (actions)
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

abstract class Action
{
	/**
	 * Указывает тип HTTP-метода, который
	 * обрабатывает это Действие
	 * 
	 * @var string
	 */
	public $requestMethod = "POST";
	
	/**
	 * Выполняется перед выполнением действия
	 * 
	 * @return void
	 * */
	public function preExecute()
	{
	}

	/**
	 * Выполнение действия
	 * 
	 * @return void
	 */
	public abstract function execute();
	
	/**
	 * Выполняется после выполнения действия
	 * 
	 * @return void
	 * */
	public function postExecute()
	{
	}

}
?>