<?php
require_once 'core/XDateTime.php';
require_once 'PHPUnit/Framework/TestCase.php';
/**
 * XDateTime test case.
 */
class XDateTimeTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		date_default_timezone_set("Europe/Moscow");
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		
	}
	
	
	function test_construct_with_params()
	{
		$nowDate = date("c");
		$dt = new XDateTime($nowDate);
		$this->assertEquals($nowDate, $dt->format("c"), "dates must be equals");
	}	
	
	function test_construct_no_params()
	{
		// по дефолту создается с текущим временем
		$nowDate = date("c");
		$dt = new XDateTime();
		$this->assertEquals($nowDate, $dt->format("c"), "dates must be equals");
		
		// Можно узнавать статическим методом текущее время в разных форматах
		$nowDate = date("c");
		$val = XDateTime::now("c");
		$this->assertEquals($nowDate, $val, "dates must be equals");
		
		$nowDate = date("U");
		$val = XDateTime::now("U");
		$this->assertEquals($nowDate, $val, "dates must be equals");
	}	
	
	/**
	 * @expectedException InvalidArgumentException
	 */	
	public function test_incorrect_formatTime()
	{
		XDateTime::formatTime("incorrect");
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */		
	public function test_incorrect_formatDateTime()
	{
		XDateTime::formatDateTime("incorrect");
	}

	/**
	 * @expectedException Exception
	 */
	public function test_exception()
	{
		$d = new XDateTime("wrong_value");
	}
	
	public function test_formatTime()
	{		
		$this->assertEquals("12-10-59", XDateTime::formatTime("12:10:59", "H-i-s"));
		$this->assertEquals("10-59-12", XDateTime::formatTime("12:10:59", "i-s-H"));
	}
	
	public function test_formatDateTime()
	{
		$this->assertEquals("09-26-2009", XDateTime::formatDateTime("2009-09-26", "m-d-Y"));
	}
	
	public function test_serialize()
	{
		$d = new XDateTime();
		$ser = serialize($d);
		$deser = unserialize($ser);

		$this->assertEquals($d, $deser);
	}
}

?>