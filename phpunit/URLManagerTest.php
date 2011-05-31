<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

require_once 'lib/Web/URLManager.php';
require_once 'core/Request.php';

class URLManagerTest extends PHPUnit_Framework_TestCase
{
	private $config = null;

	protected $object;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->config  = require 'resources/urlmanager.config.php';
		$this->object = new URLManager();

		$this->object->filters = $this->config["URLManager"]["filters"];
		$this->object->rules = $this->config["URLManager"]["rules"];

		$_SERVER["REQUEST_METHOD"] = "POST";
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown()
	{
		parent::tearDown();
	}


	public function test_Parse_View()
	{
		$this->object->parse("/viewname");
		$this->assertEquals("viewname", Request::get("view"), "view name not equals");

		$this->object->parse("/viewname/");
		$this->assertEquals("viewname", Request::get("view"), "view name not equals");

		$this->object->parse("viewname/");
		$this->assertEquals("viewname", Request::get("view"), "view name not equals");

		$this->object->parse("viewname/param/value/");
		$this->assertEquals("value", Request::get("param"));

		$this->object->parse("viewname/param/value/param2");
		$this->assertEquals("value", Request::get("param"));
		$this->assertEquals(null, Request::get("param2"));

		$this->object->parse("viewname/param[]/value/param[]/value2");
		$arr = array("value", "value2");
		$this->assertEquals($arr, Request::getArray("param"));
	}

	public function test_Parse_Action()
	{
		$this->object->parse("/action/confirm");
		$this->assertEquals("confirm", Request::get("action"), "action name not equals");
		$this->assertEquals(null, Request::get("view"), "view name is not null");

		$this->object->parse("action/confirm");
		$this->assertEquals("confirm", Request::get("action"), "action name not equals");
		$this->assertEquals(null, Request::get("view"), "module name not equals");

		$this->object->parse("action/confirm/id/45");
		$this->assertEquals("confirm", Request::get("action"), "action name not equals");
		$this->assertEquals(null, Request::get("view"), "module name not equals");
		$this->assertEquals(45, Request::get("id"));

		$this->object->parse("action/confirm/id/");
		$this->assertEquals("confirm", Request::get("action"), "action name not equals");
		$this->assertEquals(null, Request::get("view"), "module name not equals");
		$this->assertEquals(null, Request::get("id"));
	}
}
?>