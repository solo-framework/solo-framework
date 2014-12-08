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
	// Logging levels from syslog protocol defined in RFC 5424

	/**
	 * Detailed debug information
	 */
	const DEBUG = 100;

	/**
	 * Interesting events
	 *
	 * Examples: User logs in, SQL logs.
	 */
	const INFO = 200;

	/**
	 * Uncommon events
	 */
	const NOTICE = 250;

	/**
	 * Exceptional occurrences that are not errors
	 *
	 * Examples: Use of deprecated APIs, poor use of an API,
	 * undesirable things that are not necessarily wrong.
	 */
	const WARNING = 300;

	/**
	 * Runtime errors
	 */
	const ERROR = 400;

	/**
	 * Critical conditions
	 *
	 * Example: Application component unavailable, unexpected exception.
	 */
	const CRITICAL = 500;

	/**
	 * Action must be taken immediately
	 *
	 * Example: Entire website down, database unavailable, etc.
	 * This should trigger the SMS alerts and wake you up.
	 */
	const ALERT = 550;

	/**
	 * Urgent alert.
	 */
	const EMERGENCY = 600;

	protected static $levels = array(
			100 => 'DEBUG',
			200 => 'INFO',
			250 => 'NOTICE',
			300 => 'WARNING',
			400 => 'ERROR',
			500 => 'CRITICAL',
			550 => 'ALERT',
			600 => 'EMERGENCY',
	);

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

	private static $level = self::DEBUG;

	private static $singleFile = false;

	/**
	 * Конструктор
	 * принимает параметры в виде массива
	 * Параметры:
	 * logger.dir - путь к каталогу
	 * level - уровень логирования
	 *
	 * @param array $options список с настройками
	 *
	 * @throws \Exception
	 * @return \Solo\Core\Logger
	 */
	private function __construct($options = null)
	{
		// настройки по-умолчанию
		$defaults = array(
			"logger.dir" => ".",
			"level" => self::DEBUG,
			"singleFile" => false
		);

		self::$options =  array_merge($defaults, $options);

		self::setDir(self::$options['logger.dir']);
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
	 * @param int $level Уровень логгирования
	 *
	 * @return void
	 */
	private static function write($object, $level = self::DEBUG)
	{
		self::checkInit();
		if ($level >= self::$options["level"])
		{
			$level = self::$levels[$level];

			$fileName = "";
			if (self::$options["singleFile"])
				$fileName = self::$dir . DIRECTORY_SEPARATOR . self::$filePrefix ."{$level}.txt";
			else
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
	}

	/**
	 * Проверяет , был ли инициализирован логгер
	 *
	 * @throws \RuntimeException
	 * @return void
	 */
	private static function checkInit()
	{
		if (self::$logger == null)
			throw new \RuntimeException("Logger not initialized.");
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
		self::write($object, self::DEBUG);
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
		self::write($object, self::ERROR);
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
		self::write($object, self::WARNING);
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
		self::write($object, self::INFO);
	}

	/**
	 * Пишет сообщение в лог с уровнем NOTICE
	 *
	 * @param mixed $object Данные
	 *
	 * @return void
	 */
	public static function notice($object)
	{
		self::write($object, self::NOTICE);
	}

	/**
	 * Пишет сообщение в лог с уровнем CRITICAL
	 *
	 * @param mixed $object Данные
	 *
	 * @return void
	 */
	public static function critical($object)
	{
		self::write($object, self::CRITICAL);
	}

	/**
	 * Пишет сообщение в лог с уровнем CRITICAL
	 *
	 * @param mixed $object Данные
	 *
	 * @return void
	 */
	public static function alert($object)
	{
		self::write($object, self::ALERT);
	}

	/**
	 * Пишет сообщение в лог с уровнем CRITICAL
	 *
	 * @param mixed $object Данные
	 *
	 * @return void
	 */
	public static function emergency($object)
	{
		self::write($object, self::EMERGENCY);
	}
}

