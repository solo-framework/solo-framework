<?php

require_once 'PHPUnit/Framework/TestSuite.php';

require_once 'phpunit/ConfiguratorTest.php';
require_once 'phpunit/ClassLoaderTest.php';
require_once 'phpunit/RequestTest.php';
require_once 'phpunit/ValidatorTest.php';

//require_once 'phpunit/EntityManagerTest.php';
//require_once 'phpunit/PHPSettingsTest.php';
//
require_once 'phpunit/XDateTimeTest.php';
//require_once 'phpunit/ControlTest.php';
//
require_once 'phpunit/SessionTest.php';
require_once 'phpunit/SQLConditionTest.php';
//require_once 'phpunit/IniConfiguratorTest.php';

/**
 * Запуск всех юнит-тестов
 *
 * PHP version 5
 *
 * @category Framework
 * @package  Test
 * @author   Andrey Filippov <afi@i-loto.ru>
 * @license  %license% name
 * @version  SVN: $Id: Entity.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

class runAllTests extends PHPUnit_Framework_TestSuite
{

	/**
	 * Constructs the test suite handler.
	 */
	public function __construct ()
	{
		date_default_timezone_set("Europe/Moscow");

		$this->setName('Framework tests');
		$this->addTestSuite("ConfiguratorTest");
		$this->addTestSuite("ClassLoaderTest");
		$this->addTestSuite("ValidatorTest");

//		$this->addTestSuite('EntityManagerTest');
//		$this->addTestSuite("PHPSettingsTest");
		$this->addTestSuite("RequestTest");
		$this->addTestSuite("XDateTimeTest");
//		$this->addTestSuite("ControlTest");
//
		$this->addTestSuite("SessionTest");
		$this->addTestSuite("SQLConditionTest");
//		$this->addTestSuite("IniConfiguratorTest");
	}

	/**
	 * Creates the suite.
	 */
	public static function suite ()
	{
		return new self();
	}
}

?>
