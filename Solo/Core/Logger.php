<?php
/**
 * Класс для логгирования
 *
 * PHP version 5
 *
 * @example
 *
 * Logger::init();
 * Logger::info($a);
 * Logger::debug($a);
 * Logger::error($a);
 * Logger::warning($a);
 *
 * OR
 * Logger::init(array("logger.dir" => "logs"));
 *
 *
 * @category Framework
 * @package  Core
 * @author   Andrey Filippov <afi.work@gmail.com>
 * @license  %license% name
 * @version  SVN: $Id: Entity.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

namespace Solo\Core;

class Logger
{
	/**
	 * Путь к каталогу для хранения файлов лога
	 *
	 * @var string
	 */
	private static $dir = ".";

	/**
	 * Массив с настройками
	 *
	 * @var array
	 */
	private static $options = null;

	/**
	 * Экземпляр класса Logger
	 *
	 * @var Logger
	 */
	private static $logger = null;

	/**
	 * Префикс для файлов лога
	 *
	 * @var string
	 */
	public static $filePrefix = "";

	/**
	 * Конструктор
	 * принимает параметры в виде массива
	 * Параметры:
	 * logger.dir - путь к каталогу
	 *
	 * @param array $options список с настройками
	 *
	 * @return void
	 */
	private function __construct($options = null)
	{
		self::setDir(@$options['logger.dir']);
		if (!is_dir(self::$dir))
			throw new \Exception("Logger directory does not exist : " . self::$dir);
	}

	/**
	 * Инициализация логгера
	 *
	 * @param array $options Набор настроек из конфигуратора
	 * 				Если нет настроек, пишет файл в текущий каталог
	 *
	 * @return void
	 */
	public static function init($options = null)
	{
		if (!isset(self::$logger))
		{
			self::$logger = new Logger($options);
		}
	}

	/**
	 * Установка каталога для хранения файлов
	 *
	 * @param string $path Каталог для хранения файлов
	 *
	 * @return void
	 */
	private static function setDir($path)
	{
		if ($path != null)
			self::$dir = $path;
	}

	/**
	 * Возвращает символ конца строки
	 *
	 * @return string
	 */
	public static function getEOL()
	{
		if (substr(php_uname(), 0, 7) == "Windows")
			return "\r\n";
		else
			return "\n";
	}

	/**
	 * Возвращает метку времени в опред. формате
	 *
	 * @return string
	 */
	private static function getDateTime()
	{
		list($usec, $sec) = explode(" ",microtime());
		$ms = round((float)$usec, 3);
		$res = date("d-m-Y H:i:s",  (float)$sec) ." [{$ms}] ";
		return $res;
	}

	/**
	 * Возвращает дамп объекта
	 *
	 * @param mixed $object Данные, записываемые в файл
	 *
	 * @return string
	 */
	public static function parseEvent($object)
	{
		if (is_object($object) || is_array($object))
			return print_r($object, true);
		if (is_resource($object))
			return  "Resource type: " . get_resource_type($object);

		return $object;
	}

	/**
	 * Запись в файл
	 *
	 * @param object $object Данные, записываемые в файл
	 * @param string $level Уровень логгирования
	 *
	 * @return void
	 */
	private static function write($object, $level = "INFO")
	{
		$fileName = self::$dir . DIRECTORY_SEPARATOR . self::$filePrefix ."{$level}_" .  date("Y.m.d_H-i-s") . ".txt";
		$str = self::getEOL() . self::getDateTime();
		$str .= "$level: ";
		$str .= self::parseEvent($object);

		$fp = fopen($fileName, "a");
		flock($fp, LOCK_EX);
		fwrite($fp, $str);
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	/**
	 * Проверяет , был ли инициализирован логгер
	 *
	 * @return void
	 */
	private static function checkInit()
	{
		if (self::$logger == null)
			throw new \Exception("Logger not initialized.");
	}

	/**
	* Пишет сообщение в лог с уровнем DEBUG
	*
	* @param mixed $object Данные
	*
	* @return void
	*/
	public static function debug($object)
	{
		self::checkInit();
		self::write($object, "DEBUG");
	}

	/**
	* Пишет сообщение в лог с уровнем ERROR
	*
	* @param mixed $object Данные
	*
	* @return void
	*/
	public static function error($object)
	{
		self::checkInit();
		self::write($object, "ERROR");
	}

	/**
	* Пишет сообщение в лог с уровнем WARNING
	*
	* @param mixed $object Данные
	*
	* @return void
	*/
	public static function warning($object)
	{
		self::checkInit();
		self::write($object, "WARNING");
	}

	/**
	 * Пишет сообщение в лог с уровнем INFO
	 *
	 * @param mixed $object Данные
	 *
	 * @return void
	 */
	public static function info($object)
	{
		self::checkInit();
		self::write($object, "INFO");
	}
}
?>
