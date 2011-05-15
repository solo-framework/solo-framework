<?php

/**
 * Validator::reset() здесь вызывается для того, чтобы исключить влияние одних тестов на другие
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'lib/Validator/IValidator.php';
require_once 'lib/Validator/Validator.php';

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
}
?>