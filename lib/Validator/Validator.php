<?php
/**
 * Класс для проверки входных данных на соответствие
 * требуемым условиям
 *
 * PHP version 5
 *
 * @example
 * 		
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

 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

class Validator
{
	/**
	 * ссылка на экземпляр валидатора
	 * 
	 * @var Validator
	 */
	private static $instance = null;

	/**
	 * Валидность значения
	 * 
	 * @var boolean
	 */
	private static $isValid = true;

	/**
	 * Список сообщений об ошибках проверок
	 * 
	 * @var array
	 */
	private static $messages = null;

	/**
	 * Проверяемое значение
	 * 
	 * @var mixed
	 */
	private static $val = null;
	
	/**
	 * Текст общего сообщения об ошибке 
	 * для всех проверок
	 * 
	 * @var string
	 */
	private static $commonComment = "";

	/**
	 * Конструктор
	 * 
	 * @return void
	 */
	private function __construct()
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
	public static function check($val, $comment = "")
	{
		if (self::$instance == null)
			self::$instance = new self();

		self::$commonComment = $comment;
		self::$val = $val;
		self::$isValid = true;
		return self::$instance;
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
		if (self::$isValid)
		{
			// если значение обязательное и задано - проходим все проверки
			if ($isRequired && (is_null(self::$val)))
				self::addMessage($comment);
				
			// если необязательное и не задано, то дальнейшие проверки
			// проходить не обязательно
			if (!$isRequired && is_null(self::$val))
				self::$isValid = false;
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
		if (self::$isValid)
		{
			if (!preg_match($pattern, self::$val))
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
	public static function addMessage($text)
	{
		self::$isValid = false;
		self::$messages[] = self::$commonComment . $text;
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
		if (self::$isValid)
		{
			if (self::$val > $value)
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
		if (self::$isValid)
		{
			if (self::$val < $value)
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
		if (self::$isValid)
		{
			if (self::$val !== $value)
				self::addMessage($comment);
		}
		return $this;		
	}
	

	/**
	 * Расширяет способы проверки с помощью расширений - классов,
	 * имплементирующих интерфейс IValidator
	 *
	 * @param IValidator $filter Экземпляр фильтра
	 *
	 * @return Validator
	 */
	public function addValidator(IValidator $filter)
	{
		if (self::$isValid)
		{
			if (!$filter->check(self::$val))
				self::addMessage($filter->getMessage());
		}
		return $this;
	}

	/**
	 * Минимальная длина значения
	 * 
	 * @param int $len Минимальное количество символов
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 * 
	 * @return Validator
	 */
	public function minLength($len, $comment = "")
	{
		if (self::$isValid)
		{
			if (mb_strlen(self::$val) < $len)
				self::addMessage($comment);
		}
		return $this;
	}

	/**
	 * Максимальная длина значения
	 * 
	 * @param int $len Максимальное количество символов
	 * @param string $comment Комментарий, отображаемый если Условие не выполнено
	 * 
	 * @return Validator
	 */	
	public function maxLength($len, $comment = "")
	{
		if (self::$isValid)
		{
			if (mb_strlen(self::$val) > $len)
				self::addMessage($comment);
		}
		return $this;
	}

	/**
	 * Возвращает проверяемое значение
	 * 
	 * @return mixed
	 */
	public function value()
	{
		return self::$val;
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
		if (self::$isValid)
		{
			if (self::$val < $min || self::$val > $max)
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
	 * 
	 * @return Validator
	 */
	public function rangeLenght($min, $max, $comment = "")
	{
		if (self::$isValid)
		{
			$len = mb_strlen(self::$val);
			if ($len < $min || $len > $max)
				self::addMessage($comment);
		}
		return $this;
	}

	/**
	 * Результат проверки
	 * 
	 * @return boolean
	 */
	public static function isValid()
	{
		return count(self::$messages) == 0;
	}

	/**
	 * Возвращает список сообщений об ошибках 
	 * в результате проверки значения 
	 *
	 * @return array
	 */
	public static function getMessages()
	{
		return self::$messages;
	}
}
?>