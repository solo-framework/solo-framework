<?php

abstract class BaseValidatorRule implements IValidatorRule
{
	/**
	 * Комментарий, отображаемый, если правило не выполнилось
	 *
	 * @var string
	 */
	protected $comment = null;

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