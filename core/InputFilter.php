<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

class InputFilter
{
	private static $instance = null;

	private static $isValid = true;

	private static $comment = null;

	private static $val = null;

	private function __construct()
	{

	}


	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $val
	 *
	 * @return InputFilter
	 */
	public static function addValue($val)
	{
		if (self::$instance == null)
			self::$instance = new self();
	
		self::$val = $val;
		return self::$instance;
	}

	/**
	 * Условие обязательности этого значения
	 *
	 * @param boolean $isRequired Обязательное поле или нет
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 *
	 * @return InputFilter
	 */
	public function required($isRequired, $comment = "")
	{
		if (!self::$isValid)
			return false;

		if ($isRequired && (is_null(self::$val)))
		{
			self::$isValid = false;
			self::$comment = $comment;
		}

		return $this;
	}

	/**
	 * Значение должно быть меньше указанного
	 *
	 * @param mixed $value Значение для сравнения
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 *
	 * @return InputFilter
	 */
	public function lessThen($value, $comment = "")
	{
		if (!self::$isValid)
			return false;

		if (self::$val > $value)
		{
			self::$isValid = false;
			self::$comment = $comment;
		}

		return $this;
	}
	
	/**
	 * Значение должно попадать в диапазон
	 *
	 * @param mixed $value Значение для сравнения
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 *
	 * @return InputFilter
	 */
	public function range($start, $end, $comment = "")
	{
		if (!self::$isValid)
			return false;

		if ($start < self::$val && self::$val > $end)
		{
			self::$isValid = false;
			self::$comment = $comment;
		}

		return $this;
	}	

	public static function isValid()
	{
		return self::$isValid;
	}
	
	public static function getComment()
	{
		return self::$comment;
	}
	
}
?>