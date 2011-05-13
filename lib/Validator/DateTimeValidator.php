<?php
/**
 * Проверяет на соответствие форматам даты dd.mm.YYYY и YYYY.mm.dd
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

class DateTimeValidator implements IValidator
{
	/**
	 * Дата и время в формате ISO 8601 (2011-05-13T16:18:41+04:00)
	 *
	 * @var string
	 */
	const FORMAT_ISO_8601 = "FORMAT_ISO_8601";

	/**
	 * Дата и время в формате dd.mm.YYYY или YYYY.mm.dd (30.12.2000 или 2011-05-13)
	 *
	 * @var string
	 */
	const FORMAT_DDMMYYYY_OR_YYYYMMDD = "FORMAT_DDMMYYYY_OR_YYYYMMDD";

	/**
	 * Формат, в котором должно быть значение даты и времени
	 *
	 * @var string
	 */
	protected $format = self::FORMAT_ISO_8601;

	public function __construct($comment = null, $format = self::FORMAT_ISO_8601)
	{
		$this->comment = $comment;
		$this->format = $format;
	}

	/**
	 * Проверка значения
	 *
	 * @see IValidator::check()
	 *
	 * @return void
	 */
	public function check($value)
	{
		if ($this->format == self::FORMAT_DDMMYYYY_OR_YYYYMMDD)
		{
			// # dd.mm.YYYY
			$patt = '%^(0[1-9]|[12][0-9]|3[01])([- /.])(0[1-9]|1[012])\2(19|20)\d\d$%';
			// YYYY.mm.dd
			$patt1 = '%^(19|20)\d\d([- /.])(0[1-9]|1[012])\2(0[1-9]|[12][0-9]|3[01])$%';

			return (bool)(preg_match($patt, $value) || preg_match($patt1, $value));
		}

		if ($this->format == self::FORMAT_ISO_8601)
		{
			$patt = '/^(\d{4})\D?(0[1-9]|1[0-2])\D?([12]\d|0[1-9]|3[01])(\D?([01]\d|2[0-3])\D?([0-5]\d)\D?([0-5]\d)?\D?(\d{3})?([zZ]|([+-])([01]\d|2[0-3])\D?([0-5]\d)?)?)?$/';
			return (bool)preg_match($patt, $value);
		}

		throw new Exception("Undefined datetime format");
	}

	/**
	 * Возвращает сообщение об ошибке
	 *
	 * @see IValidator::getMessage()
	 *
	 * @return string
	 */
	public function getMessage()
	{
		return $this->comment;
	}
}
?>