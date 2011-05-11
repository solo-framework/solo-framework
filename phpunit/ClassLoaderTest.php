<?php

/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

require_once 'core/ClassLoader.php';
//require_once 'core/Application.php';

class ClassLoaderTest extends PHPUnit_Framework_TestCase
{

	private $pathToClassMap = "phpunit/resources/class.map";

	private $baseDir = null;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->baseDir = realpath(".");

		if (file_exists($this->pathToClassMap))
			unlink($this->pathToClassMap);
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown()
	{
		parent::tearDown();

		if (file_exists($this->pathToClassMap))
			unlink($this->pathToClassMap);
	}

	/**
	 *
	 *
	 * @return
	 */
	public function test_init()
	{
		ClassLoader::init("../", $this->pathToClassMap);
	}

	/**
	 *
	 *
	 * @return
	 */
	public function test_import_one_class()
	{
		ClassLoader::init($this->baseDir, $this->pathToClassMap);
		ClassLoader::import("phpunit/resources/Test.php");

		// Класс должен быть доступен в репозитории классов по имени файла
		$map = ClassLoader::getClassMap();
		$this->assertEquals($this->baseDir . DIRECTORY_SEPARATOR ."phpunit/resources/Test.php", $map["test"]);
	}

	public function test_import_one_class_by_name()
	{
		ClassLoader::init($this->baseDir, $this->pathToClassMap);

		// Указываем особое имя для класса
		ClassLoader::import("phpunit/resources/Test.php", "MySpecialClassName");

		// Класс должен быть доступен в репозитории классов по имени файла
		$map = ClassLoader::getClassMap();
		$this->assertEquals($this->baseDir . DIRECTORY_SEPARATOR ."phpunit/resources/Test.php", $map["myspecialclassname"]);
	}

	public function test_import_autoload()
	{
		ClassLoader::init($this->baseDir, $this->pathToClassMap);
		ClassLoader::import("phpunit/ClassLoaderTest.php");

		// Теперь класс должен автоматически загрузиться
		new ClassLoaderTest();
	}

	public function test_import_autoload_2()
	{
		ClassLoader::init($this->baseDir, $this->pathToClassMap);

		// Импортируем его под именем Test2
		ClassLoader::import("phpunit/resources/Test2.class.php", "test2");
		// Теперь класс должен автоматически загрузиться
		new Test2();
	}

	/**
	 * Нельзя импортировать классы с одинаковым названием, они
	 * должны быть уникальны
	 *
	 * @expectedException Exception
	 */
	public function test_exception_import_not_once()
	{
		ClassLoader::init($this->baseDir, $this->pathToClassMap);
		ClassLoader::import("phpunit/resources/Test.php");
		ClassLoader::import("phpunit/resources/directory/Test.php");
	}

	/**
	 * Если файла нет - исключение
	 *
	 * @expectedException Exception
	 */
	public function test_exception_import_wrong_file()
	{
		ClassLoader::init($this->baseDir, $this->pathToClassMap);
		ClassLoader::import("phpunit/resources/FileDoesNotExists.php");
	}

	/**
	 * Должны импортироваться все классы в каталоге
	 *
	 * @return
	 */
	public function test_import_directory()
	{
		ClassLoader::init($this->baseDir, $this->pathToClassMap);
		ClassLoader::reset();

		ClassLoader::import("phpunit/resources/*");

		// должны быть доступны все классы из каталога,
		// имена которых соответствуют правилам именования
		new Test();
	}

	/**
	 *
	 *
	 * @return
	 */
	public function test_import_by_alias()
	{
		ClassLoader::init($this->baseDir, $this->pathToClassMap);
		ClassLoader::reset();
		ClassLoader::setPathByAlias("tests", $this->baseDir . DIRECTORY_SEPARATOR . "phpunit/resources");

		ClassLoader::import("@tests/Test.php");
		new Test();
	}

	/**
	 * Если файла нет - исключение
	 *
	 * @expectedException Exception
	 */
	public function test_exception_undefined_alias()
	{
		ClassLoader::init($this->baseDir, $this->pathToClassMap);
		ClassLoader::getPathByAlias("undefined");
	}

}
?>