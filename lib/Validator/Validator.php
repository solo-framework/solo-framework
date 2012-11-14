<?php
/**
 * Класс для проверки входных данных на соответствие
 * требуемым условиям
 *
 * PHP version 5
 *
 * @example
 *
 *
 <pre>
 	// можно получить значение после проверки
 	$val = Validator::check(Request::get("id"), "Идентификатор пользователя ")
			->required(true, "не указан")
			->range(20, 40, "должен попадать в интервал")->value();

	// а можно просто проверить какое то значение или переменную
	Validator::check("value", "значение value: ")
		->required(false)
		->addValidator(new ValidatorEqual("other_value", "не равно 'other_value'"))
		->minLength(3, "должно быть больше 3 символов");

	if (!Validator::isValid())
	{
		print_r(Validator::getMessages());
	}

	// вывод
	array
	  0 => string 'Идентификатор пользователя не указан' (length=69)
	  1 => string 'значение value: не равно 'other_value'' (length=53)
</pre>
 *
 * @package
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

class Validator
{

	/**
	 * Валидность значения
	 *
	 * @var boolean
	 */
	private $isValid = true;

	/**
	 * Список сообщений об ошибках проверок
	 *
	 * @var array
	 */
	private $messages = null;

	/**
	 * Проверяемое значение
	 *
	 * @var mixed
	 */
	private $val = null;

	/**
	 * Текст общего сообщения об ошибке
	 * для всех проверок
	 *
	 * @var string
	 */
	private $commonComment = "";

    /**
     * Конструктор
     *
     * @return Validator
     */
    public function __construct()
	{

	}


	/**
	 * Проверяет значение с помощью цепочки валидаторов
	 *
	 * @param mixed $val Проверяемое значение
	 * @param string $comment Общий комментарий к сообщениям об ошибках.
	 * 				Добавляется перед каждым сообщением
	 *
	 * @return Validator
	 */
	public function check($val, $comment = "")
	{
		$this->commonComment = $comment;
		$this->val = $val;
		$this->isValid = true;
		return $this;
	}

	/**
	 * Условие обязательности этого значения
	 *
	 * @param boolean $isRequired Обязательное поле или нет
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 *
	 * @return Validator
	 */
	public function required($isRequired, $comment = "")
	{
		if ($this->isValid)
		{
			// если значение обязательное и задано - проходим все проверки
			if ($isRequired && (is_null($this->val)))
				self::addMessage($comment);

			// если необязательное и не задано, то дальнейшие проверки
			// проходить не обязательно
			if (!$isRequired && is_null($this->val))
				$this->isValid = false;
		}
		return $this;
	}

	/**
	 * Проверяет значение на принадлежность к
	 * числовым типам
	 *
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 *
	 * @return Validator
	 */
	public function isNumeric($comment = "")
	{
		if ($this->isValid)
		{
			if (!is_numeric($this->val))
				self::addMessage($comment);
		}

		return $this;
	}

	/**
	 * Проверяет массив ли это
	 *
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 *
	 * @return Validator
	 */
	public function isArray($comment = "")
	{
		if ($this->isValid)
		{
			if (!is_array($this->val))
				self::addMessage($comment);
		}

		return $this;
	}

	/**
	 * Проверяет значение на соответствие регулярному выражению
	 *
	 * @param string $pattern Регулярное выражение
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 *
	 * @return Validator
	 */
	public function matchRegex($pattern, $comment = "")
	{
		if ($this->isValid)
		{
			if (!preg_match($pattern, $this->val))
				self::addMessage($comment);
		}
		return $this;
	}

	/**
	 * Добавляем сообщение об ошибке.
	 * При этом валидатор становится невалидным
	 *
	 * @param string $text
	 *
	 * @return void
	 */
	public function addMessage($text)
	{
		$this->isValid = false;
		$this->messages[] = $this->commonComment . $text;
	}

	/**
	 * Значение должно быть меньше указанного
	 *
	 * @param mixed $value Значение для сравнения
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 *
	 * @return Validator
	 */
	public function lessThen($value, $comment = "")
	{
		if ($this->isValid)
		{
			if ($this->val > $value)
				self::addMessage($comment);
		}
		return $this;
	}

	/**
	 * Значение должно быть больше указанного
	 *
	 * @param mixed $value Значение для сравнения
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 *
	 * @return Validator
	 */
	public function greateThen($value, $comment = "")
	{
		if ($this->isValid)
		{
			if ($this->val < $value)
				self::addMessage($comment);
		}
		return $this;
	}

	/**
	 * Значение должно совпадать
	 *
	 * @param mixed $value Значение для сравнения
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 *
	 * @return Validator
	 */
	public function equalTo($value, $comment = "")
	{
		if ($this->isValid)
		{
			if ($this->val !== $value)
				self::addMessage($comment);
		}
		return $this;
	}


	/**
	 * Расширяет способы проверки с помощью расширений - классов,
	 * имплементирующих интерфейс IValidatorRule
	 *
	 * @param IValidatorRule $rule Экземпляр правила
	 *
	 * @return Validator
	 */
	public function addValidator(IValidatorRule $rule)
	{
		if ($this->isValid)
		{
			if (!$rule->check($this->val))
				self::addMessage($rule->getMessage());
		}
		return $this;
	}

	/**
	 * Минимальная длина значения
	 *
	 * @param int $len Минимальное количество символов
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 * @param string $encoding Кодировка символов
	 *
	 * @return Validator
	 */
	public function minLength($len, $comment = "", $encoding = "utf-8")
	{
		if ($this->isValid)
		{
			if (iconv_strlen($this->val, $encoding) < $len)
				self::addMessage($comment);
		}
		return $this;
	}

	/**
	 * Максимальная длина значения
	 *
	 * @param int $len Максимальное количество символов
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 * @param string $encoding Кодировка символов
	 *
	 * @return Validator
	 */
	public function maxLength($len, $comment = "", $encoding = "utf-8")
	{
		if ($this->isValid)
		{
			if (iconv_strlen($this->val, $encoding) > $len)
				self::addMessage($comment);
		}
		return $this;
	}

	/**
	 * Точное совпадение длины значения
	 *
	 * @param int $len Точное количество символов
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 * @param string $encoding Кодировка символов
	 *
	 * @return Validator
	 */
	public function matchLenght($len, $comment = "", $encoding = "utf-8")
	{
		if ($this->isValid)
		{
			if (iconv_strlen($this->val, $encoding) !== $len)
				self::addMessage($comment);
		}
		return $this;
	}

	/**
	 * Возвращает проверяемое значение
	 *
	 * @return mixed
	 */
	public function value($defaultValue = null)
	{
		if ($this->val !== null)
			return $this->val;
		else
			return $defaultValue;
	}

	/**
	 * Значение должно попадать в диапазон
	 *
	 * @param int $min Минимальное значение
	 * @param int $max Максимальное значение
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 *
	 * @return Validator
	 */
	public function range($min, $max, $comment = "")
	{
		if ($this->isValid)
		{
			if ($this->val < $min || $this->val > $max)
				self::addMessage($comment);
		}
		return $this;
	}

	/**
	 * Длина значения должна быть в указанном диапазоне
	 *
	 * @param int $min Минимальное значение
	 * @param int $max Максимальное значение
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 * @param string $encoding Кодировка символов
	 *
	 * @return Validator
	 */
	public function rangeLenght($min, $max, $comment = "", $encoding = "utf-8")
	{
		if ($this->isValid)
		{
			$len = iconv_strlen($this->val, $encoding);
			if ($len < $min || $len > $max)
				self::addMessage($comment);
		}
		return $this;
	}

	/**
	 * Значение должно быть в списке
	 *
	 * @param array $array Список значений
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 *
	 * @return Validator
	 */
	public function inArray($array, $comment = "")
	{
		if ($this->isValid)
		{
			$res = array_intersect($array, array($this->val));
			if (!$res)
				self::addMessage($comment);
		}
		return $this;
	}

	/**
	 * Результат проверки
	 *
	 * @return boolean
	 */
	public function isValid()
	{
		return count($this->messages) == 0;
	}

	/**
	 * Возвращает список сообщений об ошибках
	 * в результате проверки значения
	 *
	 * @return array
	 */
	public function getMessages()
	{
		return $this->messages;
	}

	/**
	 * Сбрасывает текущее состояние валидатора в начальное
	 * Обнуляет сообщения.
	 *
	 * @return void
	 */
	public function reset()
	{
		$this->messages = null;
		$this->isValid = true;
	}
}
?>