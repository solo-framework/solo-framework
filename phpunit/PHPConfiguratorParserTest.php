<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'core/IConfiguratorParser.php';
require_once 'lib/Configurator/PHPConfiguratorParser.php';

class PHPConfiguratorParserTest extends PHPUnit_Framework_TestCase
{
	private $file = "phpunit/resources/php_config.php";

	private $second = "phpunit/resources/php_second_config.php";

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

	/**
	 * @expectedException Exception
	 */
	public function test_fail_construct()
	{
		$ini = new PHPConfiguratorParser("undefined");
	}

	public function test_get()
	{
		$ini = new PHPConfiguratorParser($this->file);

		$res = $ini->get("section:test");
		$this->assertEquals("string", $res);
	}

	/**
	 * @expectedException Exception
	 */
	public function test_fail_get()
	{
		$ini = new PHPConfiguratorParser($this->file);
		$res = $ini->get("section:undefined");
	}

	/**
	 * @expectedException Exception
	 */
	public function test_inherited_options()
	{
		// Настройки определенные в наследуемом файле
		// конфигурации, но не определенные в основном
		// недоступны

		$ini = new PHPConfiguratorParser($this->second);
		$res = $ini->get("second:secondVal");
	}


	public function test_extends()
	{
		$ini = new PHPConfiguratorParser($this->second);
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
			            "array" => array(10,12,13,14),
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

	public function test_getOptions()
	{
		$ini = new PHPConfiguratorParser($this->file);
		$res = $ini->getOptions();

		$expect = array(
			"section" => array(
				"test" => "string",
				"int" => "10",
				"array" => array(10,12,13,14),
			),
			"another" => array(
				"test" => "test"
			)
		);

		$this->assertEquals($expect, $res);
	}

	public function test_getSection()
	{
		$ini = new PHPConfiguratorParser($this->file);
		$res = $ini->getSection("section");

		$expect = array(
				"test" => "string",
				"int" => "10",
				"array" => array (10,12,13,14)
			);

		$this->assertEquals($expect, $res);
	}

	/**
	 * @expectedException Exception
	 */
	public function test_fail_getSection()
	{
		$ini = new PHPConfiguratorParser($this->file);
		$res = $ini->getSection("undefinedsection");
	}

	public function test_getArray()
	{
		$ini = new PHPConfiguratorParser($this->file);
		$res = $ini->getArray("section:array");
		$expect = array(10,12,13,14);
		$this->assertEquals($expect, $res);
	}

	/**
	 * @expectedException Exception
	 */
	public function test_fail_getArray()
	{
		$ini = new PHPConfiguratorParser($this->file);
		$res = $ini->getArray("section:undef");
	}
}
?>