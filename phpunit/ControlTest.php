<?php
/**
 * Тестирование класса Control
 * 
 * PHP version 5
 * 
 * @category Framework
 * @package  Core
 * @author   Andrey Filippov <afi@i-loto.ru>
 * @license  %license% name
 * @version  SVN: $Id: Entity.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */


require_once 'PHPUnit/Framework/TestCase.php';
require_once 'core/IControl.php';
require_once 'core/Control.php';
require_once 'phpunit/resources/TestControl.php';


class ControlTest extends PHPUnit_Framework_TestCase
{
	
	public function test_addData()
	{
		$control = new TestControl();
		
		// добавляем данные
		$control->addData("data", "1");
		
		// добавляем массив
		$arr =  array("key" => "value");		
		$control->addData("array_data", $arr);
		
		$data = $control->getData();
		
		// Данные д.б. доступны в массиве по имени класса контрола в качестве ключа
		
		$res = array(
			"TestControl" => array(
				"data" => 1,		
				"array_data" => array("key" => "value")
			)
		);
		
		$this->assertEquals($res, $data);
	}

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp();
		// TODO Auto-generated ControlTest::setUp()
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		// TODO Auto-generated ControlTest::tearDown()
		parent::tearDown();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct ()
	{	// TODO Auto-generated constructor
	}
}

?>