<?php

/**
 * Validator::reset() здесь вызывается для того, чтобы исключить влияние одних тестов на другие
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'lib/Validator/IValidator.php';
require_once 'lib/Validator/Validator.php';
require_once 'lib/Validator/DateTimeValidator.php';

class ValidatorTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp()
	{
		parent::setUp();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown()
	{
		parent::tearDown();
		// Сбросим валидатор, чтобы не было влияния одних тестов на другие
		Validator::reset();
	}

	public function test_required()
	{
		Validator::check("value")->required(false, "not required");

		Validator::check(null)->required(false, "not required");

		Validator::check(null)->required(true, "required");

		$this->assertFalse(Validator::isValid());
	}

	/**
	 * Проверяет значение на принадлежность к
	 * числовым типам int, float
	 *
	 * @return
	 */
	public function test_isNumeric()
	{
		Validator::check("1")->isNumeric("Не число");
		$this->assertTrue(Validator::isValid());
		Validator::reset();

		Validator::check("1.2")->isNumeric("Не число");
		$this->assertTrue(Validator::isValid());
		Validator::reset();

		Validator::check("12e2")->isNumeric("Не число");
		$this->assertTrue(Validator::isValid());
		Validator::reset();

		Validator::check(".2")->isNumeric("Не число");
		$this->assertTrue(Validator::isValid());
		Validator::reset();

		Validator::check("123174235892350983498324698723489713478123987514987532489714239875124785324987")->isNumeric("Не число");
		$this->assertTrue(Validator::isValid());
		Validator::reset();

		Validator::check(1)->isNumeric("Не число");
		$this->assertTrue(Validator::isValid());
		Validator::reset();

		Validator::check(2.32)->isNumeric("Не число");
		$this->assertTrue(Validator::isValid());
		Validator::reset();

		Validator::check(12e4)->isNumeric("Не число");
		$this->assertTrue(Validator::isValid());
		Validator::reset();

		// not valid
		Validator::check("1,2")->isNumeric("Не число");
		$this->assertFalse(Validator::isValid());
		Validator::reset();

		Validator::check("12w4")->isNumeric("Не число");
		$this->assertFalse(Validator::isValid());
		Validator::reset();

	}

	public function test_isArray()
	{
		Validator::check(array(1))->isArray("Не список");
		$this->assertTrue(Validator::isValid());
		Validator::reset();

		Validator::check(array(1, 2, "item"))->isArray("Не список");
		$this->assertTrue(Validator::isValid());
		Validator::reset();

		// not valid
		Validator::check("value")->isArray("Не список");
		$this->assertFalse(Validator::isValid());
		Validator::reset();
	}

	public function test_matchRegex()
	{
		Validator::check(123)->matchRegex('/[\d]+/', "regex ok");
		$this->assertTrue(Validator::isValid());

		Validator::check("32123")->matchRegex('/[\d]+/', "regex ok");
		$this->assertTrue(Validator::isValid());
	}

	public function test_matchRegex_fail()
	{
		Validator::check(123)->matchRegex('/[a-z]+/', "regex ok");
		$this->assertFalse(Validator::isValid());

		Validator::reset();

		Validator::check("32123")->matchRegex('/[a-z]+/', "regex ok");
		$this->assertFalse(Validator::isValid());
	}

	public function test_addMessage()
	{
		// добавление сообщения в валидатор делает его невалидным
		Validator::addMessage("something wrong!");
		$this->assertFalse(Validator::isValid());
	}

	public function test_lessThen()
	{
		Validator::check(20)->lessThen(21);
		$this->assertTrue(Validator::isValid());

		Validator::reset();

		Validator::check(20)->lessThen(19);
		$this->assertFalse(Validator::isValid());
	}


	public function test_greateThen()
	{
		Validator::check(20)->greateThen(19);
		$this->assertTrue(Validator::isValid());

		Validator::reset();

		Validator::check(20)->greateThen(22);
		$this->assertFalse(Validator::isValid());
	}


	public function test_equalTo()
	{
		Validator::check(20)->equalTo(20);
		$this->assertTrue(Validator::isValid());

		Validator::reset();
		Validator::check(20)->equalTo(22);
		$this->assertFalse(Validator::isValid());

		Validator::reset();
		Validator::check("value")->equalTo("other_value");
		$this->assertFalse(Validator::isValid());
	}

	public function test_minLength()
	{
		Validator::check(20)->minLength(2);
		$this->assertTrue(Validator::isValid());

		Validator::reset();
		Validator::check(2)->minLength(2);
		$this->assertFalse(Validator::isValid());

		Validator::reset();
		Validator::check("value")->minLength(3);
		$this->assertTrue(Validator::isValid());

		Validator::reset();
		Validator::check("value")->minLength(30);
		$this->assertFalse(Validator::isValid());
	}

	public function test_maxLength()
	{
		Validator::check(20)->maxLength(2);
		$this->assertTrue(Validator::isValid());

		Validator::reset();
		Validator::check(222)->maxLength(2);
		$this->assertFalse(Validator::isValid());

		Validator::reset();
		Validator::check("value")->maxLength(3);
		$this->assertFalse(Validator::isValid());

		Validator::reset();
		Validator::check("value")->maxLength(30);
		$this->assertTrue(Validator::isValid());
	}

	public function test_range()
	{
		Validator::check(10)->range(8, 20);
		$this->assertTrue(Validator::isValid());

		Validator::reset();
		Validator::check(10)->range(10, 20);
		$this->assertTrue(Validator::isValid());

		Validator::reset();
		Validator::check(30)->range(10, 20);
		$this->assertFalse(Validator::isValid());
	}

	public function test_rangeLenght()
	{
		Validator::check(103)->rangeLenght(3, 10);
		$this->assertTrue(Validator::isValid());

		Validator::reset();
		Validator::check("value")->rangeLenght(4, 10);
		$this->assertTrue(Validator::isValid());

		Validator::reset();
		Validator::check(30)->rangeLenght(10, 20);
		$this->assertFalse(Validator::isValid());

		Validator::reset();
		Validator::check("hello")->rangeLenght(10, 20);
		$this->assertFalse(Validator::isValid());
	}

	/**
	 *
	 *
	 * @return
	 */
	public function test_DateTimeValidator()
	{
		Validator::check("2005-08-09")->addValidator(
			new DateTimeValidator("comment", DateTimeValidator::FORMAT_ISO_8601)
		);

		$this->assertTrue(Validator::isValid());
		Validator::reset();

		Validator::check("2005-08-09 12:33:45")->addValidator(
			new DateTimeValidator("comment", DateTimeValidator::FORMAT_ISO_8601)
		);

		$this->assertTrue(Validator::isValid());
		Validator::reset();
	}

	/**
	 * Проверка получения значения по умолчанию
	 *
	 * @return
	 */
	public function test_get_default_value()
	{
		$val = Validator::check("val")->value();
		$this->assertEquals("val", $val);
		Validator::reset();

		$val = Validator::check(null)->value("default");
		$this->assertEquals("default", $val);
		Validator::reset();

		$val = Validator::check("some")->value("default");
		$this->assertEquals("some", $val);
		Validator::reset();

	}
}
?>