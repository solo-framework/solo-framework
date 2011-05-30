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
require_once 'core/IConfiguratorParser.php';
require_once 'lib/Configurator/IniConfiguratorParser.php';

class IniConfiguratorTest extends PHPUnit_Framework_TestCase
{

	private $file = "phpunit/resources/config.ini";

	// Этот конфиг расширяет phpunit/resources/main.ini
	private $second = "phpunit/resources/second.ini";

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
	}


	public function test_extends()
	{
		$ini = new IniConfiguratorParser($this->second);
		$res = $ini->getOptions();

		$expect = Array
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

		$this->assertEquals($expect, $res);
	}

	/**
	 * @expectedException Exception
	 */
	public function test_fail_construct()
	{
		$ini = new IniConfiguratorParser("undefined");
	}

	public function test_get()
	{
		$ini = new IniConfiguratorParser($this->file);

		$res = $ini->get("section:test");
		$this->assertEquals("string", $res);
	}

	/**
	 * @expectedException Exception
	 */
	public function test_fail_get()
	{
		$ini = new IniConfiguratorParser($this->file);
		$res = $ini->get("section:undefined");
	}

	public function test_getOptions()
	{
		$ini = new IniConfiguratorParser($this->file);
		$res = $ini->getOptions();

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
		$ini = new IniConfiguratorParser($this->file);
		$res = $ini->getSection("section");

		$expect = array(
				"test" => "string",
				"int" => "10",
				"array" => "10,12,13,14");

		$this->assertEquals($expect, $res);
	}

	/**
	 * @expectedException Exception
	 */
	public function test_fail_getSection()
	{
		$ini = new IniConfiguratorParser($this->file);
		$res = $ini->getSection("undefinedsection");
	}

	public function test_getArray()
	{
		$ini = new IniConfiguratorParser($this->file);
		$res = $ini->getArray("section:array");
		$expect = array(10,12,13,14);
		$this->assertEquals($expect, $res);
	}

	/**
	 * @expectedException Exception
	 */
	public function test_fail_getArray()
	{
		$ini = new IniConfiguratorParser($this->file);
		$res = $ini->getArray("section:undef");
	}
}

