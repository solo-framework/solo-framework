<?php
/**
 * Created by JetBrains PhpStorm.
 * User: afi
 * Date: 23.03.13
 * Time: 18:40
 * To change this template use File | Settings | File Templates.
 */

require_once "../Solo/Core/IConfiguratorParser.php";
require_once "../Solo/Core/PHPConfiguratorParser.php";

use Solo\Core\PHPConfiguratorParser;

class PHPConfiguratorParserTest extends PHPUnit_Framework_TestCase
{
	private $file = "./resources/php_config.php";

	private $second = "./resources/php_second_config.php";

	/**
	 *
	 */
	public function test_fail_construct()
	{
		try
		{
			$ini = new PHPConfiguratorParser("undefined");
		}
		catch (Exception $e)
		{
			return ;
		}

		$this->fail("An expected exception has not been raised.");
	}

	public function test_get()
	{
		$ini = new PHPConfiguratorParser($this->file);

		$res = $ini->get("section:test");
		$this->assertEquals("string", $res);
	}

	/**
	 *
	 */
	public function test_fail_get()
	{
		try
		{
			$ini = new PHPConfiguratorParser($this->file);
			$res = $ini->get("section:undefined");
		}
		catch (Exception $e)
		{
			return ;
		}

		$this->fail("An expected exception has not been raised.");
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
			),

			"second" => array
			(
				"secondVal" => "secondVal"
			),

			"second2" => array
			(
				"secondVal" => "secondVal"
			),

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
	 *
	 */
	public function test_fail_getSection()
	{
		try
		{
			$ini = new PHPConfiguratorParser($this->file);
			$res = $ini->getSection("undefinedsection");
		}
		catch (Exception $e)
		{
			return ;
		}

		$this->fail("An expected exception has not been raised.");
	}

	public function test_getArray()
	{
		$ini = new PHPConfiguratorParser($this->file);
		$res = $ini->getArray("section:array");
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
			$ini = new PHPConfiguratorParser($this->file);
			$res = $ini->getArray("section:undef");
		}
		catch (Exception $e)
		{
			return ;
		}

		$this->fail("An expected exception has not been raised.");
	}

	//public function test_

	public function test_multi_inheritance()
	{
		// тестирование множественного наследования конфигов
		$base = "./resources/config_base.php";
		$base1 = "./resources/config_base_1.php";
		$base2 = "./resources/config_base_2.php";

		$parser = new PHPConfiguratorParser($base2);

		// значение переопределяется в последнем подключенном конфиге
		$res = $parser->get("section:param");
		$this->assertEquals("value_from_base_2", $res);

		// т.к. мы не хотим переопределять все настройки, должено быть доступно
		// и значение из самого базового конфига
		$this->assertEquals("value2", $parser->get("section:param2"));
	}
}
