<?php
/**
 * Класс для работы с данными сессии
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

class Session
{
	/**
	 * Экземпляр класса Session
	 * 
	 * @var Session
	 */
	private static $instance = null;
	
	/**
	 * приватный конструктор
	 * 
	 * @param string $name Имя сессии
	 * 
	 * @return void
	 */
	private function Session($name = "")
	{
		session_name($name);
		session_start();
	}
	
	/**
	 * Стартует контекст
	 * 
	 * @param string $name Имя 
	 * 
	 * @return Session
	 * */
	public static function start($name)
	{
		if (!isset(self::$instance))
		{			
			self::$instance = new Session($name);
		}
		
		return self::$instance;
	}

	/**
	 * Установка объекта в сессию
	 * 
	 * @param string $objName Имя объекта
	 * @param mixed $objValue Объект	
	 * 
	 * @return void 
	 */
	public static function set($objName, $objValue)
	{
		if (!isset($_SESSION))
			throw new Exception("Session not started");

		$_SESSION[$objName] = $objValue;
	}
	
	/**
	 * Удаление объекта из сессии
	 * 
	 * @param string $objName Имя объекта
	 * 
	 * @return boolean
	 */
	public static function clear($objName)
	{
		unset($_SESSION[$objName]);
	}
	
	/**
	 * Метод возвращает удаляет объект из сессии, возвращая его
	 * 
	 * @param string $objectName Имя объекта
	 * 
	 * @return mixed
	 * */
	public static function push($objectName)
	{
		$res = Session::get($objectName);
		Session::clear($objectName);
		return $res;
	}
	
	/**
	 * Возвращает объект из сессии
	 * 
	 * @param string $objName Имя объекта
	 * 
	 * @return mixed|NULL
	 */
	public static function get($objName)
	{
		if (isset($_SESSION[$objName]))
			return $_SESSION[$objName];
		else
			return null;
	}
	
	/**
	 * Возвращает идентификатор сессии
	 * 
	 * @return string
	 */
	public static function getSessionId()
	{
		return session_id();
	}
	
	/**
	 * Устанавливает идентификатор сессии
	 * 
	 * @param sting $id идентификатор сессии
	 * 
	 * @return void
	 */
	public static function setSessionId($id)
	{
		session_id($id);
	}
	
	/**
	 * Завершает сессию
	 * 
	 * @return void
	 */
	public static function close()
	{
		unset($_SESSION);
		@session_destroy();
	}
	
	/**
	 * Нельзя клонировать Singleton
	 * 
	 * @return void
	 */
	public function __clone()
	{
		throw new Exception("Can't clone singleton object ". __CLASS__);
	}
}
?>