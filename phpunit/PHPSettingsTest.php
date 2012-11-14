<?php
/**
 * Проверяем настройки PHP и загруженные расширения
 *
 * PHP version 5
 *
 * @category Framework
 * @package  Core
 * @author   Andrey Filippov <afi.work@gmail.com>
 * @license  %license% name
 * @version  SVN: $Id: Entity.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */


require_once 'PHPUnit/Framework/TestCase.php';

class PHPSettingsTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp();

		// Загруженные расширения
		$this->ext = get_loaded_extensions();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		parent::tearDown();
	}

	private function getIni($name)
	{
		return ini_get($name);
	}

	private function inArray($name)
	{
		return (bool)in_array($name, $this->ext);
	}



	public function testSettings()
	{
		// версия PHP
		$this->assertEquals(version_compare("5.2.5", phpversion(), "<="), true, "PHP VERSION MUST BE 5.2.5 >");

		// необходимые настройки
		$this->assertEquals($this->getIni("error_reporting"), E_ALL);
		//$this->assertEquals((bool)$this->getIni("display_errors"), true);
		$this->assertEquals((bool)$this->getIni("register_globals"), false);
		$this->assertEquals((bool)$this->getIni("register_long_arrays"), false);
		$this->assertEquals((bool)$this->getIni("magic_quotes_gpc"), false);
		$this->assertEquals((bool)$this->getIni("magic_quotes_runtime"), false);
		$this->assertEquals((bool)$this->getIni("allow_url_fopen"), true);
		//$this->assertEquals((bool)$this->getIni("short_open_tag"), false);
		$this->assertEquals((bool)$this->getIni("session.auto_start"), false);

		// нужные расширения
		$this->assertEquals($this->inArray("mbstring"), true, "mbstring is required");
		$this->assertEquals($this->inArray("iconv"), true, "iconv is required");

	}
}

?>