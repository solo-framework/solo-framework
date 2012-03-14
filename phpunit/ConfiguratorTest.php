<?php
/**
 * Тестирование конфигуратора
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
require_once 'core/Configurator.php';
require_once 'core/IConfiguratorParser.php';
require_once 'lib/Configurator/IniConfiguratorParser.php';

class ConfiguratorTest extends PHPUnit_Framework_TestCase
{

	private $file = "phpunit/resources/config.ini";

	// файл, расширяющий main.ini
	private $second = "phpunit/resources/second.ini";

	// неправильно указан расширяемый файл
	private $fail = "phpunit/resources/fail.ini";

	//private $conf = null;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp()
	{
		parent::setUp();
		Configurator::reset();

		Configurator::init(new IniConfiguratorParser($this->file));

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
	 */
	public function test_fail_construct()
	{
		try
		{
			Configurator::reset();
			Configurator::init(new IniConfiguratorParser("undefined"));
		}
		catch (Exception $e)
		{
			return ;
		}

		$this->fail("An expected exception has not been raised.");
	}

	public function test_get()
	{
		$res = Configurator::get("section:test");
		$this->assertEquals("string", $res);
	}

	/**
	 *
	 */
	public function test_fail_get()
	{
		try
		{
			Configurator::get("section:undefined");
		}
		catch (Exception $e)
		{
			return ;
		}

		$this->fail("An expected exception has not been raised.");
	}

	public function test_extends_getAll()
	{
		Configurator::reset();
		Configurator::init(new IniConfiguratorParser($this->second));

		$expected = Array
			(
			    "only_in_main_defined" => Array
			        (
			            "main" => "main"
			        ),

			    "section" => Array
			        (
			            "test" => "string",
			            "int" => 10,
			            "array" => "10,12,13,14",
			            "again" => "dddd"
			        ),

			    "another" => Array
			        (
			            "test" => "redeclader in second"
			        ),

			    "main" => Array
			        (
			            "val" => "string",
			            "int" => 12,
			            "arr" => Array
			                (
			                    "0" => 3,
			                    "1" => 4
			                ),

			            "lalala" => "sdsd"
			        )

			);

		$actual = Configurator::getAll();

		$this->assertEquals($expected, $actual);
	}


	public function test_getOptions()
	{
		$res = Configurator::getAll();

		$expect = array(
			"section" => array(
				"test" => "string",
				"int" => "10",
				"array" => "10,12,13,14",
			),
			"another" => array(
				"test" => "test"
			)
		);

		$this->assertEquals($expect, $res);
	}

	public function test_getSection()
	{
		$res = Configurator::getSection("section");

		$expect = array(
				"test" => "string",
				"int" => "10",
				"array" => "10,12,13,14");

		$this->assertEquals($expect, $res);
	}

	/**
	 *
	 */
	public function test_fail_getSection()
	{
		try
		{
			$res = Configurator::getSection("undefinedsection");
		}
		catch (Exception $e)
		{
		return ;
		}

		$this->fail("An expected exception has not been raised.");
	}

	public function test_getArray()
	{
		$res = Configurator::getArray("section:array");
		$expect = array(10,12,13,14);
		$this->assertEquals($expect, $res);
	}

	/**
	 *
	 */
	public function test_fail_getArray()
	{
		try
		{
			$res = Configurator::getArray("section:undef");
		}
		catch (Exception $e)
		{
		return ;
		}

		$this->fail("An expected exception has not been raised.");
	}

	/**
	 *
	 */
	public function test_extends_fail()
	{
		Configurator::reset();

		try
		{
			Configurator::init(new IniConfiguratorParser($this->fail));
		}
		catch (Exception $e)
		{
			return ;
		}

		$this->fail("An expected exception has not been raised.");
	}

	public function test_exdends_get()
	{
		Configurator::reset();
		Configurator::init(new IniConfiguratorParser($this->second));

		$res = Configurator::get("main:val");
		$this->assertEquals("string", $res);
	}

	/**
	 *
	 */
	public function test_exdends_get_fail()
	{
		Configurator::reset();

		try
		{
			// подключили файл, не расширяющий main
			Configurator::init(new IniConfiguratorParser($this->file));

			// и пробуем получить значения, определенные в main
			$res = Configurator::get("main:val");
			$this->assertEquals("string", $res);

		}
		catch (Exception $e)
		{
			return ;
		}

		$this->fail("An expected exception has not been raised.");
	}
}

