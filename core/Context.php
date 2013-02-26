<?php
/**
 * Контекст приложения
 *
 * Представляет собой репозиторий основных объектов приложения.
 * Стартует сессию.
 * Управление и контроль за данными сессии.
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

namespace Solo\Core;
use Solo\Core\Web\Session\ISessionProvider;

class Context
{
	private static $instance = null;

	private function __construct($name, ISessionProvider $provider)
	{
		$provider->start();
		session_name($name);
		session_start();
	}

	public static function start($name, ISessionProvider $provider)
	{
		if (!isset(self::$instance))
		{
			self::$instance = new Context($name, $provider);
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
			throw new \Exception("Context not started");

		$_SESSION[$objName] = $objValue;
	}

	/**
	 * Удаление объекта из сессии
	 *
	 * @param string $objName Имя объекта
	 *
	 * @return void
	 */
	public static function clear($objName)
	{
		if (isset($_SESSION))
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
		$res = self::get($objectName);
		self::clear($objectName);
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
	 * Уничтожает все данные контекста
	 *
	 * @return session
	 */
	public static function destroy()
	{
		unset($_SESSION);
		@session_destroy();
	}

	/**
	 * Нельзя клонировать
	 *
	 * @return void
	 */
	public function __clone()
	{
		throw new \Exception("Can't clone singleton object ". __CLASS__);
	}

	/**
	 * Установка пользователя в контекст
	 *
	 * @param mixed $user Данные пользователя
	 *
	 * @return void
	 */
	public static function setActor($user)
	{
		self::set("__solo__user", $user);
	}

	/**
	 * Возвращает текущего пользователя
	 *
	 * @return mixed
	 */
	public static function getActor()
	{
		return self::get("__solo__user");
	}

	/**
	 * Установка flash-сообщения в контекст
	 *
	 * @param mixed|Exception $message Сообщение (текст, массив или исключение)
	 * @param string $flashMessageId Идентификатор сообщения.
	 * 			Например: error, если нужно отобразить как ошибку.
	 * 			Отображение настраивается в представлении.
	 *
	 * @return void
	 */
	public static function setFlashMessage($message, $flashMessageId)
	{
		$flash["message"] = $message;
		$flash["id"] = $flashMessageId;
		self::set("__solo_flash_message", $flash);
	}

	/**
	 * Получение flash-сообщения из контекста.
	 * Само сообщение очищается
	 *
	 * @return mixed
	 */
	public static function getFlashMessage()
	{
		return self::push("__solo_flash_message");
	}
}
?>
