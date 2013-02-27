<?php
/**
 * Описывает методы, которые должны реальзовать классы,
 * применяемые для расширения функционала класса Validator
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

namespace Solo\Lib\Validator;

interface IValidatorRule
{
	/**
	 * В этом методе реализуется логика проверки
	 * соответствия значения заданным условиям.
	 * Возвращает true, если значение соответствует условиям.
	 * Иначе, false.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function check($value);

	/**
	 * Возвращает комментарий, при несоответствии
	 * значения заданным условиям
	 *
	 */
	public function getMessage();
}
?>
