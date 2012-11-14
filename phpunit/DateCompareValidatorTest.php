<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'core/XDateTime.php';
require_once 'lib/Validator/IValidator.php';
require_once 'lib/Validator/Validator.php';
require_once 'lib/Validator/DateCompareValidator.php';

class DateCompareValidatorTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp()
	{
		parent::setUp();
		// Сбросим валидатор, чтобы не было влияния одних тестов на другие
		Validator::reset();
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

	public function test_less()
	{
		$val = XDateTime::formatDateTime("-1 day", "c");
		$now = XDateTime::formatDateTime("now", "c");

		// валидный
		Validator::check($val)
			->addValidator(new DateCompareValidator($now, DateCompareValidator::CONDITION_LESS, "Must be less"));

		$this->assertTrue(Validator::isValid());
		Validator::reset();

		// не валидный
		Validator::check($now)
			->addValidator(new DateCompareValidator($val, DateCompareValidator::CONDITION_LESS));

		$this->assertFalse(Validator::isValid());
		Validator::reset();
	}

	public function test_greate()
	{
		$val = XDateTime::formatDateTime("+1 day", "c");
		$now = XDateTime::formatDateTime("now", "c");

		// валидный
		Validator::check($val)
			->addValidator(new DateCompareValidator($now, DateCompareValidator::CONDITION_GREATE, "Must be greate"));

		$this->assertTrue(Validator::isValid());
		Validator::reset();

		// не валидный
		Validator::check($now)
			->addValidator(new DateCompareValidator($val, DateCompareValidator::CONDITION_GREATE));

		$this->assertFalse(Validator::isValid());
	}

	public function test_equals()
	{
		$val = XDateTime::formatDateTime("now", "c");
		$now = XDateTime::formatDateTime("now", "c");
		$fail = XDateTime::formatDateTime("-1 day", "c");

		// валидный
		Validator::check($val)
			->addValidator(new DateCompareValidator($now, DateCompareValidator::CONDITION_EQUALS, "Must be eq"));

		$this->assertTrue(Validator::isValid());
		Validator::reset();

		// не валидный
		Validator::check($now)
			->addValidator(new DateCompareValidator($fail, DateCompareValidator::CONDITION_EQUALS));

		$this->assertFalse(Validator::isValid());
	}

	public function test_greate_or_equals()
	{
		$val = XDateTime::formatDateTime("now", "c");
		$eq = XDateTime::formatDateTime("now", "c");
		$less =  XDateTime::formatDateTime("-1 day", "c");
		$fail = XDateTime::formatDateTime("+1 day", "c");

		// валидный (=)
		Validator::check($val)
			->addValidator(new DateCompareValidator(
					$eq,
					DateCompareValidator::CONDITION_GREATE_OR_EQUALS,
					"Must be gr || eq")
				);

		$this->assertTrue(Validator::isValid());
		Validator::reset();

		// валидный (>)
		Validator::check($val)
			->addValidator(new DateCompareValidator(
					$less,
					DateCompareValidator::CONDITION_GREATE_OR_EQUALS,
					"Must be gr || eq")
				);

		$this->assertTrue(Validator::isValid());
		Validator::reset();

		// не валидный (<)
		Validator::check($val)
			->addValidator(new DateCompareValidator($fail, DateCompareValidator::CONDITION_GREATE_OR_EQUALS));

		$this->assertFalse(Validator::isValid());
	}

	public function test_less_or_equals()
	{
		$val = XDateTime::formatDateTime("now", "c");
		$eq = XDateTime::formatDateTime("now", "c");
		$greate =  XDateTime::formatDateTime("+1 day", "c");
		$fail = XDateTime::formatDateTime("-1 day", "c");

		// валидный (=)
		Validator::check($val)
			->addValidator(new DateCompareValidator(
					$eq,
					DateCompareValidator::CONDITION_LESS_OR_EQUALS,
					"Must be gr || eq")
				);

		$this->assertTrue(Validator::isValid());
		Validator::reset();

		// валидный (>)
		Validator::check($val)
			->addValidator(new DateCompareValidator(
					$greate,
					DateCompareValidator::CONDITION_LESS_OR_EQUALS,
					"Must be gr || eq")
				);

		$this->assertTrue(Validator::isValid());
		Validator::reset();

		// не валидный (<)
		Validator::check($val)
			->addValidator(new DateCompareValidator($fail, DateCompareValidator::CONDITION_LESS_OR_EQUALS));

		$this->assertFalse(Validator::isValid());
	}

	public function test_not_equals()
	{
		$val = $eq = XDateTime::formatDateTime("now", "c");
		$notEq = XDateTime::formatDateTime("+1 second", "c");

		Validator::check($val)
			->addValidator(
					new DateCompareValidator($notEq, DateCompareValidator::CONDITION_NOT_EQUALS)
				);

		$this->assertTrue(Validator::isValid());
		Validator::reset();

		Validator::check($val)
			->addValidator(
					new DateCompareValidator($eq, DateCompareValidator::CONDITION_NOT_EQUALS)
				);

		$this->assertFalse(Validator::isValid());
		Validator::reset();

	}
}
?>