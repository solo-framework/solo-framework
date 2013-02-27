<?php
/**
 * Валидатор для сравнения дат
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

namespace Solo\Lib\Validator;

class DateCompareValidator extends BaseValidatorRule
{

	/**
	 * Условие "меньше, чем"
	 *
	 * @var string
	 */
	const CONDITION_LESS = "CONDITION_LESS";

	/**
	 * Условие "больше, чем"
	 *
	 * @var string
	 */
	const CONDITION_GREATE = "CONDITION_GREATE";

	/**
	 * Условие "больше или равно"
	 *
	 * @var string
	 */
	const CONDITION_GREATE_OR_EQUALS = "CONDITION_GREATE_OR_EQUALS";

	/**
	 * Условие "меньше или равно"
	 *
	 * @var string
	 */
	const CONDITION_LESS_OR_EQUALS = "CONDITION_LESS_OR_EQUALS";

	/**
	 * Условие "равно"
	 *
	 * @var string
	 */
	const CONDITION_EQUALS = "CONDITION_EQUALS";

	/**
	 * Условие "не равно"
	 *
	 * @var string
	 */
	const CONDITION_NOT_EQUALS = "CONDITION_NOT_EQUALS";

	/**
	 * Условие "не определено"
	 *
	 * @var string
	 */
	const CONDITION_UNDEFINED = "CONDITION_UNDEFINED";

	/**
	 * Дата, с которой сравнивается значение
	 *
	 * @var string
	 */
	protected $compareDate = null;

	/**
	 * Условие сравнения
	 *
	 * @var string
	 */
	protected $condition = null;

	/**
	 * Комментарий, отображаемый если Условие не выполнено
	 *
	 * @var string
	 */
	protected $comment = null;

	/**
	 * Конструктор
	 *
	 * @param string $compareDate
	 * @param string $condition Условие по которому сравниваются даты
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 *
	 * @return void
	 */
	public function __construct($compareDate, $condition = self::CONDITION_UNDEFINED, $comment = null)
	{
		$this->compareDate = $compareDate;
		$this->condition = $condition;
		$this->comment = $comment;
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
		$valueTS = strtotime($value);
		$compareTS = strtotime($this->compareDate);

		switch ($this->condition)
		{
			case self::CONDITION_LESS:
				return $valueTS < $compareTS;
			break;

			case self::CONDITION_GREATE:
				return $valueTS > $compareTS;
			break;

			case self::CONDITION_GREATE_OR_EQUALS:
				return $valueTS >= $compareTS;
			break;

			case self::CONDITION_LESS_OR_EQUALS:
				return $valueTS <= $compareTS;
			break;

			case self::CONDITION_EQUALS:
				return $valueTS == $compareTS;
			break;

			case self::CONDITION_NOT_EQUALS:
				return $valueTS != $compareTS;
			break;

			default:
				throw new Exception("Please, define a condition to compare");
			break;
		}

	}
}

?>
