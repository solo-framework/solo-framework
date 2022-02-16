<?php
/**
 * Created by JetBrains PhpStorm.
 * User: afi
 * Date: 23.03.13
 * Time: 17:23
 * To change this template use File | Settings | File Templates.
 */

use \Solo\Core\Request;

class RequestTest extends \PHPUnit\Framework\TestCase
{
	private function setPost()
	{
		$_SERVER["REQUEST_METHOD"] = "POST";
	}

	private function setGet()
	{
		$_SERVER["REQUEST_METHOD"] = "GET";
	}

	public function testGetMethod()
	{
		// set GET
		$this->setGet();
		$m = Request::getMethod();
		$this->assertEquals("GET", $m);

		// set POST
		$this->setPost();
		$m = Request::getMethod();
		$this->assertEquals("POST", $m);
	}

	public function testGetVar()
	{
		//
		// set GET
		//
		$this->setGet();
		$_GET['test'] = "value";
		$this->assertEquals("value", Request::get("test"));
		// проверка дефолтного значения
		$this->assertEquals(null, Request::get("undef", null), "must be default value");
		$this->assertEquals("default", Request::get("undef", "default"), "must be default value");

		//
		// set POST
		//
		$this->setPost();
		$_POST['posttest'] = "value";
		$this->assertEquals("value", Request::get("posttest"));
		// проверка дефолтного значения
		$this->assertEquals(null, Request::get("undef", null), "must be default value");
		$this->assertEquals("default", Request::get("undef", "default"), "must be default value");

		//
		// есть данные в  GET, но данные пробуем брать POST
		//
		$_GET['data'] = "data_value";
		$this->setPost();
		$this->assertEquals("data_value", Request::get("data"), "must be null");

		//
		// есть данные в  POST, но данные пробуем брать GET
		//
		$_POST['post_data'] = "post_data_value";
		$this->setGet();
		$this->assertEquals("post_data_value", Request::get("post_data"), "must be null");

		//
		// устранение концевых пробелов
		//
		$this->setGet();
		$_GET['test'] = " value ";
		$this->assertEquals("value", Request::get("test"));

		//
		// Очистка входных данных
		//
// 		$this->setGet();
// 		$_GET["test"] = "O' Raily";
// 		$this->assertEquals("O\' Raily", Request::get("test"));

		//
		// "граязные" входные данные
		//
		$this->setGet();
		$_GET["test"] = "O' Raily";
		$this->assertEquals("O' Raily", Request::get("test", null, true));
	}

	public function testGetInt()
	{
		$this->setPost();
		$_POST['int'] = 10;
		$this->assertEquals(10, Request::get('int'));

		$this->setGet();
		// в GET значения нет, так что должны получить дефолтное значение
		$this->assertEquals(10, Request::get('int', 10), "must be default : 10");
	}

	public function testGetFloat()
	{
		$this->setPost();
		$_POST['float'] = 10.11;
		$this->assertEquals(10.11, Request::get('float'));

		$this->setGet();
		// в GET значения нет, так что должны получить дефолтное значение
		$this->assertEquals(10.11, Request::get('float', 10.11), "must be default : 10.11");
	}

	public function testGetArray2()
	{
		$this->setPost();
		$data = [
			"part1\part2",
			"part3",
			"part1\part2\part3",
        ];

		$_POST = [];
		$_GET = [];
		$_POST["data"] = $data;

		$this->assertEquals($data, Request::getArray("data"), "Data is not array");
	}

	public function testGetArray()
	{
		$this->setPost();
		$arr = array(
			"t1" => "val1",
			"t2" => "val2",
			"t3" => "val3",
			"t4" => "val4",
		);
		$_POST['array'] = $arr;

		$this->assertEquals($arr, Request::getArray('array'));

		$this->setGet();
		// в GET значения нет, так что должны получить дефолтное значение
		$this->assertEquals(1, Request::getArray('array1', 1), "must be default : 1");

		//
		// проверим, очищаются ли опасные данные в массиве
		//
		$arrIn = array(
			"t1" => "val'1",
			"t2" => "val2",
			"t3" => "val3",
			"t4" => "val4",
			"t5" => array("la" => "ll'll")
		);

		$arrOut = array(
			"t1" => "val'1",
			"t2" => "val2",
			"t3" => "val3",
			"t4" => "val4",
			"t5" => array("la" => "ll'll")
		);

		$_POST['array'] = $arrIn;
		$this->assertEquals($arrOut, Request::getArray('array'));
	}

	public function test_clearInput()
	{
		$this->assertEquals("tes't", Request::clearInput("tes't"));
	}

	public function test_getIp()
	{
		$_SERVER["REMOTE_ADDR"] = "127.0.0.1";
		$this->assertEquals("127.0.0.1", Request::getIp());
	}

	public function test_prevUri()
	{
		$this->assertEquals("/", Request::prevUri());

		$_SERVER["HTTP_REFERER"] = "/test";
		$this->assertEquals("/test", Request::prevUri());
	}

	public function test_HTTPMethods()
	{
		$this->setGet();
		$this->assertEquals( true, Request::isGet() );

		$this->setPost();
		$this->assertEquals( true, Request::isPost() );
	}
}
