<?php
/**
 * Класс для работы с cookie
 *
 * PHP version 5
 *
 * @package Lib
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace Solo\Core\Web;

class Cookie
{
  /**
	 * Время жизни - сессия броузера
	 */
	const SESSION = null;

	/**
	 * Время жизни один день
	 */
	const DAY = 86400;

	/**
	 * Время жизни однна неделя
	 */
	const WEEK = 604800;

	/**
	 * Время жизни один год
	 */
	const YEAR = 31536000;

	/**
	 * Проверяет существует ли cookie с заданным именем
	 *
	 * @param string $name Имя куки
	 *
	 * @return bool
	 */
	static public function exists($name)
	{
		return isset($_COOKIE[$name]);
	}

	/**
	 * Проверяет установлено ли значение с заданным именем
	 *
	 * @param string $name Имя куки
	 *
	 * @return bool
	 */
	static public function isEmpty($name)
	{
		return empty($_COOKIE[$name]);
	}

	/**
	 * Возвращает значение куки	с заданным именем
	 *
	 * @param string $name Имя куки
	 * @param string $default Значение по умолчанию
	 *
	 * @return mixed
	 */
	static public function get($name, $default = "")
	{
		return (self::exists($name) ? $_COOKIE[$name] : $default);
	}

	/**
	 * Устанавливает значение куки
	 *
	 * @param string $name Имя куки
	 * @param string $value Значение куки
	 * @param mixed $expiry Время жизни
	 * @param string $path Путь на сервере, где куки будет доступна
	 * @param string $domain Домен, где будет доступна кукик
	 *
	 * @return void
	 */
	static public function set($name, $value, $expiry = self::YEAR, $path = '/', $domain = null)
	{
		if ($domain === false)
			$domain = $_SERVER['HTTP_HOST'];

		if (is_numeric($expiry))
			$expiry += time();
		else
			$expiry = strtotime($expiry);

		setcookie($name, $value, $expiry, $path, $domain);
	}

	/**
	 * Удаляет куки
	 *
	 * @param string $name Имя куки
	 * @param string $path Путь на сервере
	 * @param string $domain Домен
	 *
	 * @return void
	 */
	static public function delete($name, $path = '/', $domain = null)
	{
		if ($domain === false)
			$domain = $_SERVER['HTTP_HOST'];

		setcookie($name, '', time() - 3600, $path, $domain);
	}
}
