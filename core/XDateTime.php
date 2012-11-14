<?php
/**
 * Класс расширяющий встроенный DateTime
 *
 * Добавлены волшебные методы для корректной
 * сериализации объекта этого класса
 *
 * Для версий PHP >= 5.2.0
 *
 * PHP version 5.2.0
 *
 * @category Framework
 * @package  DateTime
 * @author   Andrey Filippov <afi.work@gmail.com>
 * @license  %license% name
 * @version  SVN: $Id: Entity.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

class XDateTime extends DateTime
{
	/**
	 * Переменная для хранения свойств объекта
	 * при сериализации
	 *
	 * @var string
	 */
	private $__serialized;

	/**
	 * Содержит значение даты (используется при сериализации объекта)
	 *
	 * @var mixed
	 */
	public $date;

	/**
	 * должен возвращаться массив с именами своих свойств, которые будут сохранены.
	 *
	 * @return array
	 */
	public function __sleep()
	{
		if (version_compare(phpversion(), "5.3.0", "<"))
		{
			$this->__serialized = $this->format("c");
			return array("__serialized");
		}
		else
		{
			return array("date");
		}
	}

	/**
	 * нужен для выполнения процедур инициализации объекта после десериализации.
	 *
	 * @return void
	 */
	public function __wakeup()
	{
		if (version_compare(phpversion(), "5.3.0", "<"))
			$this->__construct($this->__serialized);
		else
			$this->__construct($this->date);
	}

	/**
	 * Возвращает текущее время в заданном формате
	 *
	 * @param string $format Формат даты
	 *
	 * @return string
	 */
	public static function now($format = "c")
	{
		$tmp = new XDateTime();
		return $tmp->format($format);
	}

	/**
	 * Приводит значение времени к определенному формату
	 *
	 * @param string $time Значение времени. Может принимать значения,
	 * 					   определенные для функции strtotime()
	 * @param string $format Выходной формат времени (как для функции date())
	 *
	 * @return string
	 */
	public static function formatTime($time, $format = "H:i:s")
	{
		$tm = strtotime($time);

		if ($tm === false)
			throw new InvalidArgumentException("Incorrect time value: '{$time}'");
		else
			return date($format, $tm);
	}

	/**
	 * Приводит строку со значением даты к нужному формату. По умолчанию ISO 8601.
	 *
	 * @param string $dateTime Значение времени. Может принимать значения,
	 * 					   определенные для функции strtotime(), а также timestamp
	 * @param string $format Выходной формат времени (как для функции date())
	 *
	 * @return string
	 */
	public static function formatDateTime($dateTime, $format = "c")
	{
		$tm = strtotime($dateTime);

		if ($tm === false)
			throw new InvalidArgumentException("Incorrect datetime value: '{$dateTime}'");
		else
			return date($format, $tm);
	}
}

?>