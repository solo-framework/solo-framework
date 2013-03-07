<?php
/**
 * Тесты для роутинга
 *
 * PHP version 5
 *
 * @package Test
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

require_once "../Solo/Core/Route.php";
require_once "../Solo/Core/ClassLoaderException.php";
require_once "../Solo/Core/Request.php";

use Solo\Core\Request;

class RouteTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$_SERVER["REQUEST_METHOD"] = "GET";
	}

	public function test_wildcards_routing()
	{
		$route = new \Solo\Core\Route();
		$class = '\App\Views\HomeView';
		$route->add("/:username:{any}", $class);

		$this->assertEquals($class, $route->get("/some_value"));

		// должна быть переменная 'username' в GET
		$this->assertEquals(Request::get("username"), "some_value");

		// должна быть переменная id
		$this->assertEquals($class, $route->get("/some_value/id/10"));
		$this->assertEquals(Request::get("id"), "10");
	}

	public function test_simple_routing()
	{
		// строка /(userName:any) должна переписаться в /userName/(:any) и передаваться в обработчик request_uri
		// чтобы сгенерировать пары param/value
		//$r->add("/(userName:any)/", "className");

		$route = new \Solo\Core\Route();

		$class = '\App\Views\EditUserView';
		// самые распространенные маршруты будут выглядеть как
		$route->add("/view/edituser", $class);

		$this->assertEquals($class, $route->get("/view/edituser"));
		$this->assertEquals($class, $route->get("/view/edituser/"));
		$this->assertEquals($class, $route->get("/view/edituser/id"));
		$this->assertEquals($class, $route->get("/view/edituser/id/10"));
		$this->assertEquals($class, $route->get("/view/edituser/id/10/param1/val1"));


		$class = '\App\Views\TaskListView';
		$route->add("/mytasks", $class);

		$this->assertEquals($class, $route->get("/mytasks/id"));
		$this->assertEquals($class, $route->get("/mytasks/id/"));
		$this->assertEquals($class, $route->get("/mytasks/id/10"));
		$this->assertEquals($class, $route->get("/mytasks/id/10/"));



//		$this->assertEquals($class, $r->get("/view/edituser/"));
//		$this->assertEquals($class, $r->get("/view/edituser/id"));
//		$this->assertEquals($class, $r->get("/view/edituser/id/10"));

		// и так
		//$r->add("/action/saveuser", '\App\Actions\SaveUserAction');

		// соответствует запросам типа http://site.ru/blablabla
		// placeholder 'username' будет преобразован в переменную запроса
		// и будет доступен как Request::get("username")
		// если placeholder задан, то эта часть URI всегда должна быть в запросе
		//$r->add("/:username", '\App\Views\HomeView');

		// можно указывать непосредственно
		// при этом, если URI будет выглядеть как
		// http://site.ru/mytasks/date/2012-12-12/status/new, то
		// часть 'date/2012-12-12/status/new' будет преобразована в
		// переменные запроса 'date' и 'status' со значениями
		// '2012-12-12' и 'new', соответственно
		//$r->add("/mytasks", '\App\Views\TaskListView');

		// самые распространенные маршруты будут выгладеть как
		//$r->add("/view/edituser", '\App\Views\EditUserView');

		// и так
		//$r->add("/action/saveuser", '\App\Actions\SaveUserAction');

		// можно усложнить, добавив гибкости, указывая паттерн
		// где, :any - это wildcard, соответствующий '([a-zA-Z0-9\.\-_%=]+)'
		// :num => '([0-9]+)'
		//$r->add("/:username:any", '\App\Views\HomeView');

		// т.о. можно будет различать запросы
		// пример: http://site.ru/blablabla
		//$r->add("/:username:any", '\App\Views\HomeView');

		// и
		// пример: http://site.ru/122332
	//	$r->add("/:id:num", '\App\Views\ArticleView');
		// но нужно ли это реально?

		// http://site.ru/blog/my_tag/2011-12-12
		//$r->add("/blog/:tag/:date", '\App\Views\ArticleView');


//		$r->add("/userName(:any)/time(:num-:num)/", "className");
//		$r->add("/userName(:any)/time(:num)", "className");

		//$r->add("/:username{:any}", '\App\Views\HomeView');
		// ==>
		//$r->add("/(?<username>:([a-zA-Z0-9\.\-_%=]+))", '\App\Views\HomeView');

	}

}
