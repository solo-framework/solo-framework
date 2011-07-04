<?php
/**
 * Фабрика команд
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  CommandFactory.php 27.05.11 17:37 evkur
 * @link     nolink
 */

require_once 'Commands/HelpCommand.php';
require_once 'Commands/CommitCommand.php';
require_once 'Commands/CreateCommand.php';
require_once 'Commands/InitCommand.php';
require_once 'Commands/CommitCommand.php';
require_once 'Commands/DeleteCommand.php';
require_once 'Commands/GotoCommand.php';
require_once 'Commands/LogCommand.php';
require_once 'Commands/InfoCommand.php';
require_once 'Commands/ShowdeltaCommand.php';

class CommandFactory
{
    /**
     * @static
     * @throws Exception
     * @param null $name
     * @param null $params
     * 
     * @return BaseCommand
     */
    private static $supportedCommands =
        array('commit', 'help', 'init', 'goto', 'create', 'delete', 'info', 'log', 'showdelta');

    public static function create($name = null, $params = null)
    {
        if (!in_array($name, self::$supportedCommands))
            throw new Exception('Unsupported commnad. Use <help>');

        if (!isset($params))
            $params = array();

		$className = ucfirst($name).'Command';
		$ref = new ReflectionClass($className);
        
        /**
         * @var $refConstr ReflectionMethod
         */
		$refConstr = $ref->getConstructor();

		if (count($params) < $refConstr->getNumberOfRequiredParameters())
            throw new Exception('Incorrect arguments. Use <help>');

		return $ref->newInstanceArgs($params);
    }

}
