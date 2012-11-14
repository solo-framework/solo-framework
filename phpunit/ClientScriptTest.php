<?php
/**
 * Тестирование класса ClientScript
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'lib/Web/ClientScript.php';

class ClientScriptTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Экземпляр ClientScript
	 *
	 * @var ClientScript
	 */
	private $cs = null;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->cs = new ClientScript();
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
	public function test_load()
	{
		// Подключен впервые
		$res = $this->cs->load("first.js");
		$this->assertTrue($res);

		// пробуем еще раз
		$res = $this->cs->load("first.js");
		$this->assertFalse($res);

		// теперь другой
		$res = $this->cs->load("another.js");
		$this->assertTrue($res);
	}
}
?>