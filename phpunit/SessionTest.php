<?php
/**
 * Тестирование класса Session
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
require_once 'core/Session.php';

class SessionTest extends PHPUnit_Framework_TestCase
{

	function setUp()
	{
		// т.к. в консолы перед стартом уже есть вывод
		// то будут Warnin'и. Убираем их
		error_reporting(E_ALL ^ E_WARNING);
	}

	protected function tearDown()
	{
		session_destroy();
		error_reporting(E_ALL);
	}

	public function testStartSession()
	{
		Session::start("test_session");
		$this->assertNotNull(session_id());
	}

	public function testCloseSession()
	{
		$this->startSession();
		Session::close();
		$this->assertEquals(session_id(), "");
	}

	public function test_getId()
	{
		$this->startSession();
		$this->assertNotNull(Session::getSessionId());
	}

	/**
	 *
	 */
	public function test_clone()
	{
		try
		{
			$session = Session::start("test_session");
			$new = clone $session;

		}
		catch (Exception $e)
		{
			return ;
		}

		$this->fail("An expected exception has not been raised.");
	}

	public function test_setId()
	{
		$this->startSession();
		Session::setSessionId("myid");
		$this->assertEquals("myid", Session::getSessionId());
	}

	public function test_clear()
	{
		$this->startSession();
		Session::set("test", "values");
		Session::clear("test");
		$this->assertNull(Session::get("test"));
	}

	public function testSet()
	{
		$this->startSession();
		Session::set("test", "values");
		$this->assertEquals($_SESSION["test"], "values");
	}

	public function testGet()
	{
		$this->startSession();
		Session::set("test", "test");
		$this->assertEquals(Session::get("test"), "test");
	}

	// must throw Exception
	function test_no_started()
	{
		Session::close();
		try
		{
			Session::set("test", "value");
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}

		$this->fail('An expected Exception has not been raised.');
	}


	function testSetAlreadyExists_Force()
	{
		$this->startSession();
		Session::set("test", "value");

		Session::set("test", "value_1", true);


		$this->assertEquals($_SESSION['test'], "value_1");
	}

	function testPush()
	{
		$this->startSession();
		Session::set("test", "value");
		$res = Session::push("test");

		$this->assertNotNull($res);
		$this->assertNull(Session::get("test"));
	}


	private function closeSession()
	{
		Session::close();
	}

	private function startSession()
	{
		//new Session("test_session");
		Session::start("test_session");
		$_SESSION = array();
	}
}

