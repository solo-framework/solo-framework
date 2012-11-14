<?php
/**
 * Тест для DefaultClassLoaderTest
 * 
 * PHP version 5
 * 
 * @package 
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'core/DefaultClassLoader.php';

class DefaultClassLoaderTest extends PHPUnit_Framework_TestCase
{
	private $pathToRep = "phpunit/resources/repository.rep";
	
	public function test_ok()
	{
		DefaultClassLoader::init($this->pathToRep);
	}
	
	/**
	 * 
	 * @expectedException LogicException
	 */
	public function test_init_exception()
	{
		DefaultClassLoader::init($this->pathToRep, "wrong method name");
	}
	
	/**
	 * 
	 */
	public function test_load_success()
	{
		// пока не тестируем
		///DefaultClassLoader::autoload("IPublicAction");
	}
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp()
	{
		parent::setUp();
		DefaultClassLoader::init($this->pathToRep);
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown()
	{
		parent::tearDown();
	}
}
?>