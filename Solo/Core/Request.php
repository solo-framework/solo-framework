<?php
/**
 * Получение данных из HTTP запроса.
 * Их предварительная обработка.
 * Отправка HTTP заголовков
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

class Request
{
	/**
	 * Возвращает HTTP_REFERER
	 *
	 * @return string
	 */
	public static function prevUri()
	{
		if (array_key_exists("HTTP_REFERER", $_SERVER))
			return $_SERVER["HTTP_REFERER"];
		else
			return "/";
	}

	/**
	 * Возвращает REQUEST_URI
	 *
	 * @return string
	 */
	public static function requestUri()
	{
		return $_SERVER["REQUEST_URI"];
	}

	/**
	 * Возвращает true, если HTTP запрос выполнен методом POST
	 *
	 * @return boolean
	 */
	public static function isPost()
	{
		return "POST" == Request::getMethod();
	}

	/**
	 * Возвращает true, если HTTP запрос выполнен методом GET
	 *
	 * @return boolean
	 */
	public static function isGet()
	{
		return "GET" == Request::getMethod();
	}

	/**
	 * Возвращает имя метода, которым был отправлен запрос
	 *
	 * @return string
	 * */
	public static function getMethod()
	{
		return $_SERVER["REQUEST_METHOD"];
	}

	/**
	 * Возвращает клиентский IP адрес
	 *
	 * @return string
	 */
	public static function getIp()
	{
		return $_SERVER["REMOTE_ADDR"];
	}

	/**
	 * Возвращает значение переменной HTTP из запроса
	 *
	 * @param string $name Имя переменной
	 * @param mixed $default Значение переменной, которое будет возвращего по умолчанию
	 * @param boolean $allowHTML Если true, то не преобразует
	 * 				специальные символы в HTML сущности и не экранирует кавычки
	 *
	 * @return mixed
	 */
	public static function get($name, $default = null, $allowHTML = false)
	{
		$res = self::getRawData($name, $default);
		if (null === $res || "" === $res)
			return $default;

		// преобразование специальных символов в HTML сущности
		if (!$allowHTML)
			$res = htmlspecialchars($res);

		// экранирование кавычек
		if (!$allowHTML)
			$res = self::clearInput($res);
		return $res;
	}

	/**
	 * Возвращает нефильтрованный данные из запроса
	 *
	 * @param string $name Имя переменной
	 * @param mixed $default Значение по умолчанию
	 *
	 * @return mixed
	 */
	private static function getRawData($name, $default = null)
	{
		$res = null;
		switch (true)
		{
			case isset($_GET[$name]):
				$res = $_GET[$name];
				break;

			case isset($_POST[$name]):
				$res = $_POST[$name];
				break;

			default:
				return $default;
		}

		return $res;
	}

	/**
	 * Экранирует спецсимволы.
	 * Удаляет пробелы в начале и конце строки
	 *
	 * @param string $input Входящая строка
	 *
	 * @return string
	 */
	public static function clearInput($input)
	{
		$input = trim($input);
		if (get_magic_quotes_gpc())
		{
			$input = stripslashes($input);
		}
		return $input;
	}

	/**
	 * Возвращает массив с данными о загруженном файле
	 *
	 * @param string $name Имя поля
	 *
	 * @return array
	 */
	public function getFile($name)
	{
		return $_FILES[$name];
	}

	/**
	 * Возвращает массив из HTTP запроса
	 *
	 * @param string $name Имя переменной
	 * @param mixed $default Значение по умолчанию
	 * @param boolean $allowHTML Очищать данные в массиве или нет
	 *
	 * @return array
	 */
	public static function getArray($name, $default = null, $allowHTML = false)
	{
		$res = self::getRawData($name, $default);
		if (is_array($res))
		{
			Request::stripArray($res, $allowHTML);
			return $res;
		}
		else
		{
			return $default;
		}
	}

	/**
	 * Экранирует спецсимволы. Рекурсивно обходит массив со значениями
	 *
	 * @param array &$array Массив с данными
	 * @param boolean $allowHTML Очищать данные в массиве или нет
	 *
	 * @return array
	 */
	public static function stripArray(&$array, $allowHTML)
	{
		foreach ($array as $key => $value)
		{
			if (is_array($value))
			{
				self::stripArray($array[$key], $allowHTML);
			}
			else
			{
				if (!$allowHTML)
				{
					$value = htmlspecialchars($value);
					$value = self::clearInput($value);
				}
				$array[$key] = $value;
			}
		}
	}


	/**
	 * Проверяет, был ли выполнен запрос с применением AJAX
	 *
	 * @return bool
	 * */
	public static function isAJAXRequest()
	{
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
			return true;
		else
			return false;
	}


//	/**
//	 * Отправляет HTTP заголовок 404
//	 * и выводит содержимое страницы 404 в браузер
//	 *
//	 * @param string $info Дополнительная информация
//	 *
//	 * @return void
//	 */
//	public static function send404($info = null)
//	{
//		header("HTTP/1.1 404 Not Found");
//
//		$message = <<<EOT
//
//<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
//<html>
//<head>
//	<title>404 Not Found</title>
//</head>
//<body>
//<h1>Not Found</h1>
//<p>The requested URL {$_SERVER['REQUEST_URI']} was not found on this server.</p>
//<p>{$info}</p>
//</body>
//</html>
//EOT;
//		echo $message;
//
//		exit();
//	}

	/**
	 * Возвращает true, если запрос был послан через https
	 * Иначе - false
	 *
	 * @return bool
	 */
	public static function isHTTPS()
	{
		return isset($_SERVER['HTTPS']) && !strcasecmp($_SERVER['HTTPS'], 'on');
	}

	/**
	 * Возвращает URL приложения с указанием протокола
	 * Пока только HTTP и HTTPS
	 *
	 * @return string
	 */
	public static function getBaseURL()
	{
		$host = null;
		if (array_key_exists("HTTP_HOST", $_SERVER))
		$host = $_SERVER["HTTP_HOST"];
		else
			return null;

		if (self::isHTTPS())
			$host = "https://{$host}";
		else
			$host = "http://{$host}";

		return $host;
	}
}
?>
