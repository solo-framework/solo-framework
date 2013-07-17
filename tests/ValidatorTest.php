<?php

require_once "../Solo/Lib/Validator/IValidatorRule.php";
require_once "../Solo/Lib/Validator/BaseValidatorRule.php";
require_once "../Solo/Lib/Validator/Validator.php";
require_once "../Solo/Lib/Validator/DateTimeValidator.php";
require_once "../Solo/Lib/Validator/IdValidatorRule.php";

use Solo\Lib\Validator\IdValidatorRule;
use Solo\Lib\Validator\Validator;
use Solo\Lib\Validator\DateTimeValidator;



class ValidatorTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Экземпляр валидатора
	 *
	 * @var Validator
	 */
	private $val = null;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->val = new Validator();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown()
	{
		parent::tearDown();
		// Сбросим валидатор, чтобы не было влияния одних тестов на другие
		$this->val->reset();
	}

	public function test_required()
	{
		$this->val->check("value")->required(false, "not required");
		$this->val->check(null)->required(false, "not required");
		$this->val->check(null)->required(true, "required");

		$this->assertFalse($this->val->isValid());
	}

	public function test_inArray()
	{
		$arr = array(0, 1, 2);
		$this->val->check("0", "not found")->inArray($arr);
		$this->assertTrue($this->val->isValid());
		$this->val->reset();

		$this->val->check(2, "not found")->inArray($arr);
		$this->assertTrue($this->val->isValid());
		$this->val->reset();

		$this->val->check("string", "not found")->inArray($arr, "in");
		$this->assertFalse($this->val->isValid());
		$this->val->reset();

		$this->val->check("string", "not found")->inArray(array(), "in");
		$this->assertFalse($this->val->isValid());
		$this->val->reset();
	}

	/**
	 * Проверяет значение на принадлежность к
	 * числовым типам int, float
	 *
	 * @return
	 */
	public function test_isNumeric()
	{
		$this->val->check("1")->isNumeric("Не число");
		$this->assertTrue($this->val->isValid());
		$this->val->reset();

		$this->val->check("1.2")->isNumeric("Не число");
		$this->assertTrue($this->val->isValid());
		$this->val->reset();

		$this->val->check("12e2")->isNumeric("Не число");
		$this->assertTrue($this->val->isValid());
		$this->val->reset();

		$this->val->check(".2")->isNumeric("Не число");
		$this->assertTrue($this->val->isValid());
		$this->val->reset();

		$this->val->check("123174235892350983498324698723489713478123987514987532489714239875124785324987")->isNumeric("Не число");
		$this->assertTrue($this->val->isValid());
		$this->val->reset();

		$this->val->check(1)->isNumeric("Не число");
		$this->assertTrue($this->val->isValid());
		$this->val->reset();

		$this->val->check(2.32)->isNumeric("Не число");
		$this->assertTrue($this->val->isValid());
		$this->val->reset();

		$this->val->check(12e4)->isNumeric("Не число");
		$this->assertTrue($this->val->isValid());
		$this->val->reset();

		// not valid
		$this->val->check("1,2")->isNumeric("Не число");
		$this->assertFalse($this->val->isValid());
		$this->val->reset();

		$this->val->check("12w4")->isNumeric("Не число");
		$this->assertFalse($this->val->isValid());
		$this->val->reset();
	}

	public function test_isArray()
	{
		$this->val->check(array(1))->isArray("Не список");
		$this->assertTrue($this->val->isValid());
		$this->val->reset();

		$this->val->check(array(1, 2, "item"))->isArray("Не список");
		$this->assertTrue($this->val->isValid());
		$this->val->reset();

		// not valid
		$this->val->check("value")->isArray("Не список");
		$this->assertFalse($this->val->isValid());
		$this->val->reset();
	}

	public function test_matchRegex()
	{
		$this->val->check(123)->matchRegex('/[\d]+/', "regex ok");
		$this->assertTrue($this->val->isValid());

		$this->val->check("32123")->matchRegex('/[\d]+/', "regex ok");
		$this->assertTrue($this->val->isValid());
	}

	public function test_matchRegex_fail()
	{
		$this->val->check(123)->matchRegex('/[a-z]+/', "regex ok");
		$this->assertFalse($this->val->isValid());

		$this->val->reset();

		$this->val->check("32123")->matchRegex('/[a-z]+/', "regex ok");
		$this->assertFalse($this->val->isValid());
	}

	public function test_addMessage()
	{
		// добавление сообщения в валидатор делает его невалидным
		$this->val->addMessage("something wrong!");
		$this->assertFalse($this->val->isValid());
	}

	public function test_lessThen()
	{
		$this->val->check(20)->lessThen(21);
		$this->assertTrue($this->val->isValid());

		$this->val->reset();

		$this->val->check(20)->lessThen(19);
		$this->assertFalse($this->val->isValid());
	}


	public function test_greateThen()
	{
		$this->val->check(20)->greateThen(19);
		$this->assertTrue($this->val->isValid());

		$this->val->reset();

		$this->val->check(20)->greateThen(22);
		$this->assertFalse($this->val->isValid());
	}


	public function test_equalTo()
	{
		$this->val->check(20)->equalTo(20);
		$this->assertTrue($this->val->isValid());

		$this->val->reset();
		$this->val->check(20)->equalTo(22);
		$this->assertFalse($this->val->isValid());

		$this->val->reset();
		$this->val->check("value")->equalTo("other_value");
		$this->assertFalse($this->val->isValid());
	}

	public function test_minLength()
	{
		$this->val->check(20)->minLength(2);
		$this->assertTrue($this->val->isValid());

		$this->val->reset();
		$this->val->check(2)->minLength(2);
		$this->assertFalse($this->val->isValid());

		$this->val->reset();
		$this->val->check("value")->minLength(3);
		$this->assertTrue($this->val->isValid());

		$this->val->reset();
		$this->val->check("value")->minLength(30);
		$this->assertFalse($this->val->isValid());

		$this->val->reset();
		$this->val->check("строка")->minLength(6);
		$this->assertTrue($this->val->isValid());
	}

	public function test_maxLength()
	{
		$this->val->check(20)->maxLength(2);
		$this->assertTrue($this->val->isValid());

		$this->val->reset();
		$this->val->check(222)->maxLength(2);
		$this->assertFalse($this->val->isValid());

		$this->val->reset();
		$this->val->check("value")->maxLength(3);
		$this->assertFalse($this->val->isValid());

		$this->val->reset();
		$this->val->check("value")->maxLength(30);
		$this->assertTrue($this->val->isValid());

		$this->val->reset();
		$this->val->check("стро")->maxLength(6);
		$this->assertTrue($this->val->isValid());
	}

	public function test_range()
	{
		$this->val->check(10)->range(8, 20);
		$this->assertTrue($this->val->isValid());

		$this->val->reset();
		$this->val->check(10)->range(10, 20);
		$this->assertTrue($this->val->isValid());

		$this->val->reset();
		$this->val->check(30)->range(10, 20);
		$this->assertFalse($this->val->isValid());
	}

	public function test_rangeLenght()
	{
		$this->val->check(103)->rangeLenght(3, 10);
		$this->assertTrue($this->val->isValid());

		$this->val->reset();
		$this->val->check("value")->rangeLenght(4, 10);
		$this->assertTrue($this->val->isValid());

		$this->val->reset();
		$this->val->check(30)->rangeLenght(10, 20);
		$this->assertFalse($this->val->isValid());

		$this->val->reset();
		$this->val->check("hello")->rangeLenght(10, 20);
		$this->assertFalse($this->val->isValid());
	}

	/**
	 *
	 *
	 * @return
	 */
	public function test_DateTimeValidator()
	{
		$this->val->check("2005-08-09")->addValidator(
			new DateTimeValidator("comment", DateTimeValidator::FORMAT_ISO_8601)
		);

		$this->assertTrue($this->val->isValid());
		$this->val->reset();

		$this->val->check("2005-08-09 12:33:45")->addValidator(
			new DateTimeValidator("comment", DateTimeValidator::FORMAT_ISO_8601)
		);

		$this->assertTrue($this->val->isValid());
		$this->val->reset();
	}

	/**
	 * Проверка получения значения по умолчанию
	 *
	 * @return
	 */
	public function test_get_default_value()
	{
		$val = $this->val->check("val")->value();
		$this->assertEquals("val", $val);
		$this->val->reset();

		$val = $this->val->check(null)->value("default");
		$this->assertEquals("default", $val);
		$this->val->reset();

		$val = $this->val->check("some")->value("default");
		$this->assertEquals("some", $val);
		$this->val->reset();

	}

	/**
	 * Точное совпадение длины значения
	 *
	 * @return void
	 */
	public function test_matchLenght()
	{
		$this->val->check("тестовая строка")->matchLenght(15);
		$this->assertTrue($this->val->isValid());
		$this->val->reset();

		$this->val->check(222)->matchLenght(3);
		$this->assertTrue($this->val->isValid());
		$this->val->reset();

		$this->val->check("тест")->matchLenght(3);
		$this->assertFalse($this->val->isValid());
		$this->val->reset();
	}

	public function test_id_validator()
	{
		$this->val->check("ddd")
			->addValidator(new IdValidatorRule());

		// только числовые значения
		$this->assertFalse($this->val->isValid());
		$this->val->reset();

		// длина ID от 1 - 11 цифр
		$this->val->check(1111111111111111111)
			->addValidator(new IdValidatorRule());
		$this->assertFalse($this->val->isValid());
		$this->val->reset();

		// идентификатор может и не быть указан
		$this->val->check(null)
			->addValidator(new IdValidatorRule());
		$this->assertTrue($this->val->isValid());
		$this->val->reset();
	}

	public function test_post()
	{
		$_POST["subId"] = 1;
		$_POST["boxId"] = "boxId";
	}
}