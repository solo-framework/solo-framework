<?php
/**
 * Тесты для роутинга
 *
 * PHP version 5
 *
 * @package Test
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

//require_once "../vendor/autoload.php";

//require_once "../Solo/Core/Router.php";
//require_once "../Solo/Core/ClassLoaderException.php";
//require_once "../Solo/Core/Request.php";

use PHPUnit\Framework\TestCase;
use Solo\Core\Request;
use Solo\Core\Route;

class RouteTest extends TestCase
{
	public function setUp() : void
	{
		$_SERVER["REQUEST_METHOD"] = "GET";
		$_GET = array();
	}


	public function test_qr()
	{
		$route = new \Solo\Core\Router();
		$class = '\App\Views\HomeView';
		$url = '/gtk2Kgqr/84906328/blablabla';
		$pattern = '/qr/:providerId:{any}/:account:{any}';

		$route->add($pattern, $class);
		$this->assertEquals(null, $route->getClass($url));
	}

	public function test_index_Router()
	{
		$route = new \Solo\Core\Router();
		$class = '\App\Views\HomeView';

		$route->add("/", $class);
		$this->assertEquals($class, $route->getClass("/"));

		$this->assertNull($route->getClass("/blablabla"));
	}


	public function test_wildcards_routing()
	{
		$route = new \Solo\Core\Router();
		$class = '\App\Views\HomeView';

		$route->add("/:param:{any}", $class);
		$this->assertEquals($class, $route->getClass("/some_string"));

		$route->clear();
		$route->add("/:param:{any}/", $class);
		$this->assertEquals($class, $route->getClass("/some_string"));

		$route->clear();
		$route->add("/:param1:{any}/:intParam2:{num}", $class);
		$this->assertEquals($class, $route->getClass("/some_param/10002"));
		$this->assertEquals($class, $route->getClass("/some_param/10002/"));
		$this->assertEquals($class, $route->getClass("/some_param/10002/param1/value2"));

		$route->clear();
		$route->add("/:param1:{any}/:intParam2:{num}", $class);
		$this->assertEquals(null, $route->getClass("/some_param/10002and_some_text"));
		$this->assertEquals(null, $route->getClass("/some_param/10002and_some_text/"));
		$this->assertEquals(null, $route->getClass("/some_param/10002and_some_text/param1/value1"));

	}

	public function test_simple_routing()
	{
		$route = new \Solo\Core\Router();

		$class = '\App\Views\EditUserView';
		// самые распространенные маршруты будут выглядеть как
		$route->add("/view/edituser", $class);

		$this->assertEquals($class, $route->getClass("/view/edituser"));
		$this->assertEquals($class, $route->getClass("/view/edituser/"));

		$route->clear();
		$class = '\App\Views\TaskListView';
		$route->add("/mytasks", $class);

		$this->assertEquals($class, $route->getClass("/mytasks/id"));
		$this->assertEquals($class, $route->getClass("/mytasks/id/"));
	}

	public function test_simple_routing_with_variables()
	{
		$route = new \Solo\Core\Router();

		$class = '\App\Views\EditUserView';
		// самые распространенные маршруты будут выглядеть как
		$route->add("/view/edituser", $class);

		$this->assertEquals($class, $route->getClass("/view/edituser/id"));
		$this->assertNull(Request::get("id"));

		$this->assertEquals($class, $route->getClass("/view/edituser/id/10"));
		$this->assertEquals(10, Request::get("id"));

		$this->assertEquals($class, $route->getClass("/view/edituser/id/10/param1/val1"));
		$this->assertEquals("val1", \Solo\Core\Request::get("param1"));
	}

	public function test_wildcards_routing_with_variables()
	{
		$route = new \Solo\Core\Router();
		$class = '\App\Views\HomeView';
		$route->add("/:username:{any}", $class);

		$this->assertEquals($class, $route->getClass("/some_value"));

		// должна быть переменная 'username' в GET
		$this->assertEquals(Request::get("username"), "some_value");

		// должна быть переменная id
		$this->assertEquals($class, $route->getClass("/some_value/id/10"));
		$this->assertEquals(Request::get("id"), "10");

		$route->clear();
		$route->add("/:userName:{any}/:id:{num}", $class);
		$this->assertEquals($class, $route->getClass("/user_name/10002/some_param1/some_value2"));
		$this->assertEquals("user_name", \Solo\Core\Request::get("userName"));
		$this->assertEquals(10002, \Solo\Core\Request::get("id"));
		$this->assertEquals("some_value2", \Solo\Core\Request::get("some_param1"));

	}

	public function test_add_wildcard()
	{
		$route = new \Solo\Core\Router();
		$class = '\App\Views\TimeTableView';

//		Date dd/mm/yyyy
//		01/01/1900 through 31/12/2099
//		Matches invalid dates such as February 31st
//		Accepts dashes, spaces, forward slashes and dots as date separators
		$route->addWildCard("{date}", "(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)[0-9]{2}");

		$route->add("/:startDate:{date}/:endDate:{date}", $class);
		$this->assertEquals($class, $route->getClass("/31.12.2012/07.01.2013"));

		$this->assertEquals("31.12.2012", \Solo\Core\Request::get("startDate"));
		$this->assertEquals("07.01.2013", \Solo\Core\Request::get("endDate"));
	}

	public function test_add_wildcard_fail()
	{
		$this->expectException(RuntimeException::class);
		$route = new \Solo\Core\Router();
		$route->addWildCard("{any}", "some_regex");
	}

	public function test_prefix()
	{
		$class = '\App\Views\HomeView';

		$route = new \Solo\Core\Router();
		$route->addPrefix("/index.php");
		$route->addPrefix("bo");

		$route->add("/", $class);

		// игнорируем index.php
		$this->assertEquals($class, $route->getClass("/index.php"));
		$this->assertEquals($class, $route->getClass("/index.php/"));

		// игнорируем /bo - например,если приложение находится в каталоге http://site.ru/bo
		$this->assertEquals($class, $route->getClass("/bo"));
		$this->assertEquals($class, $route->getClass("/bo/"));

		$route = new \Solo\Core\Router();
		$route->addPrefix("/bo");

		$route->add("/", "BO\\View\\OperatorIndexView");
		$route->add("/action/operatorlogin", "BO\\Action\\OperatorLoginAction");

		// игнорируем /bo - например,если приложение находится в каталоге http://site.ru/bo
		$this->assertEquals("BO\\View\\OperatorIndexView", $route->getClass("/bo"));
		$this->assertEquals("BO\\Action\\OperatorLoginAction", $route->getClass("/bo/action/operatorlogin/"));
	}

	public function test_with_get_params()
	{
		$route = new \Solo\Core\Router();
		$class = '\App\Views\SearchView';

		$route->add("/search", $class);
		$this->assertEquals($class, $route->getClass("/search?q=search_string"), "Class not Found");
	}

	public function test_with_action()
	{
		$route = new \Solo\Core\Router();
		$class = '\App\Views\SearchView';

		$route->add("/test", $class);

		// правило должно быть "/action/test"
		$this->assertNotEquals($class, $route->getClass("/action/test"));
		$this->assertNotEquals($class, $route->getClass("/action/test/id/100"));
	}

	public function test_similary_urls()
	{
		$route = new \Solo\Core\Router();
		$class1 = '\App\Views\ParametrizedView';
		$class2 = '\App\Views\TestView';

		// специфичные правила всегда впереди
		$route->add("/test/first_method/:myparam:{any}", $class1);

		// общие правила в конце
		$route->add("/test", $class2);

		$this->assertEquals($class1, $route->getClass("/test/first_method/some_value"));
		$this->assertEquals("some_value", Request::get("myparam"));

		$this->assertEquals($class2, $route->getClass("/test"));
	}
}
