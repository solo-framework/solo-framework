<?php

/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

require_once 'core/ClassLoader.php';
//require_once 'core/Application.php';

class ClassLoaderTest extends PHPUnit_Framework_TestCase
{

	private $pathToClassMap = "phpunit/resources/class.map";

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp()
	{
		parent::setUp();
		//$configFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . "resources/classloaderconfig.ini" ;
		//Application::createApplication("../", $configFile, "Application");
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown()
	{
		parent::tearDown();
	}

	/**
	 *
	 *
	 * @return
	 */
	public function test_init()
	{
		ClassLoader::init($this->pathToClassMap);
	}

	/**
	 *
	 *
	 * @return
	 */
	public function test_import_one_class()
	{
		define("BASE_DIR", realpath("."));
		ClassLoader::init($this->pathToClassMap);

		ClassLoader::import("phpunit/resources/*");
		ClassLoader::import("phpunit/resources/*");
		ClassLoader::import("phpunit/resources/Test.php");
		ClassLoader::import("phpunit/resources/TestManager.php");
		ClassLoader::import("phpunit/resources/TestManager.php");
		ClassLoader::import("phpunit/resources/TestAction.php");

		echo "!!\n";
		print_r(ClassLoader::getImported());
		print_r(ClassLoader::getClassMap());

		//define("BASE_DIR", realpath("."));
		//ClassLoader::init();

		// пытаемся
		//$className = __CLASS__;
		//$this->assertEquals(true, ClassLoader::import("phpunit/resources/Test.php"));

		//ClassLoader::import("core/*");
		//ClassLoader::import("phpunit/resources/*");


		//print_r(explode(PATH_SEPARATOR, get_include_path()));

		//new Test();

		///ClassLoader::import("phpunit/resources/TestAction.php");
	//	new  TestAction();
	}
}
?>