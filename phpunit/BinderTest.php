<?php
/**
 * Тестирование обработчика действий и представлений
 *
 * PHP version 5
 *
 * @package
 *
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'core/Action.php';
require_once 'core/IPage.php';
require_once 'core/IAjaxView.php';
require_once 'core/View.php';
require_once 'core/IComponent.php';
require_once 'core/Request.php';
require_once 'core/Binder.php';

class BinderTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 * Enter description here ...
	 * @var Binder
	 */
	private $binder = null;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->binder = Binder::getInstance();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown()
	{
		parent::tearDown();
	}

	/**
	 * Когда запрашиваем действие методом GET, а ожидаем POST
	 *
	 * @expectedException RuntimeException
	 */
	public function test_Action_Check_HTTP_Method()
	{
		require_once "resources/TestAction.php";
		$_SERVER["REQUEST_METHOD"] = "GET";

		$this->binder->executeAction("Test");
	}

	/**
	 * Когда забыли завершить действие редиректом
	 *
	 * @expectedException RuntimeException
	 */
	public function test_Action_Check_Redirect_Exception()
	{
		require_once "resources/TestAction.php";
		$_SERVER["REQUEST_METHOD"] = "POST";

		$this->binder->executeAction("Test");
	}

	/**
	 * Пытаемся построить компонент напрямую из запроса
	 *
	 * @expectedException RuntimeException
	 */
	public function test_View_Component_Exception()
	{
		require_once "resources/TestComponentView.php";

		$this->binder->handleView("TestComponent");
	}

	/**
	 * Для представления имплем-го IPage
	 * всегда нужно указывать layout
	 *
	 * @expectedException RuntimeException
	 */
	public function test_View_No_Layout()
	{
		require_once "resources/TestView.php";

		$this->binder->handleView("Test");
	}
}
?>