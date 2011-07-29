<?php
/**
 * Запуск всех юнит-тестов
 *
 * PHP version 5
 *
 * @package  Test
 * @author   Andrey Filippov <afi@i-loto.ru>
 */

require_once 'PHPUnit/Framework/TestSuite.php';

require_once 'phpunit/ConfiguratorTest.php';
require_once 'phpunit/ClassLoaderTest.php';
require_once 'phpunit/RequestTest.php';
require_once 'phpunit/ValidatorTest.php';
require_once 'phpunit/IniConfiguratorTest.php';
require_once 'phpunit/PHPConfiguratorParserTest.php';

require_once 'phpunit/EntityManagerTest.php';
require_once 'phpunit/XDateTimeTest.php';
require_once 'phpunit/SessionTest.php';
//require_once 'phpunit/SQLConditionTest.php';
require_once 'phpunit/URLManagerTest.php';
require_once 'phpunit/MySQLConditionTest.php';
require_once 'phpunit/ClientScriptTest.php';

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

		$this->addTestSuite('EntityManagerTest');
		$this->addTestSuite("RequestTest");
		$this->addTestSuite("XDateTimeTest");

		$this->addTestSuite("SessionTest");
		$this->addTestSuite("MySQLConditionTest");
		$this->addTestSuite("IniConfiguratorTest");
		$this->addTestSuite("PHPConfiguratorParserTest");
		$this->addTestSuite("URLManagerTest");
		$this->addTestSuite("ClientScriptTest");
	}

	/**
	 * Creates the suite.
	 */
	public static function suite()
	{
		return new self();
	}
}

?>
