<?php

namespace Solo\Lib\Validator;

class IdValidatorRule extends BaseValidatorRule
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
	public function check($value)
	{
		$this->comment = "Некорректный формат идентификатора";

		// формат: целое положительное число, количество цифр от 1 до 11
		$v = new Validator();
		$v->check($value)
			->required(false)
			->matchRegex('/^[\d]+/')
			->rangeLenght(1, 11);

		return $v->isValid();
	}

}
?>
