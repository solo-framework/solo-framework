<?php
/**
 * Тестируем некоторые методы EntityManager
 *
 * PHP version 5
 *
 * @category Framework
 * @package  Core
 * @author   Andrey Filippov <afi@i-loto.ru>
 * @license  %license% name
 * @version  SVN: $Id: Entity.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

error_reporting(E_ALL);

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'core/EntityManager.php';
require_once 'core/XDateTime.php';
require_once 'phpunit/resources/Test.php';
require_once 'phpunit/resources/TestManager.php';
require_once 'core/db/SQLCondition.php';
require_once 'core/db/IDBAdapter.php';
require_once 'core/db/MySQLAdapter.php';
require_once 'core/db/MySQLResult.php';

class EntityManagerTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Проверка форматирования даты в формат БД
	 */
	public function test_FormatDateTimeIn()
	{
		$stub = $this->getMockForAbstractClass("EntityManager");

		// В формате дд.мм.гггг
		$this->assertEquals("2009-12-25 00:00:00", $stub->formatDateTimeIn("25.12.2009"));
		$this->assertEquals("2009-12-25 00:00:00", $stub->formatDateTimeIn("25-12-2009"));

		$this->assertEquals("2009-12-25 00:10:12", $stub->formatDateTimeIn("25.12.2009 00:10:12"));
		$this->assertEquals("2009-09-10 00:00:00", $stub->formatDateTimeIn("10 September 2009"));
	}

	/**
	 * Проверка форматирования даты в формат приложения
	 */
	public function test_FormatDateTimeOut()
	{
		$stub = $this->getMockForAbstractClass("EntityManager");

		// В формате дд.мм.гггг
		$this->assertEquals("2009-12-25T00:00:00+03:00", $stub->formatDateTimeOut("25.12.2009"));
		$this->assertEquals("2009-12-25T00:00:00+03:00", $stub->formatDateTimeOut("25-12-2009"));

		$this->assertEquals("2009-12-25T00:10:12+03:00", $stub->formatDateTimeOut("25.12.2009 00:10:12"));
	}

	public function test_defineClass()
	{
		$tm = new TestManager();
		$this->assertEquals("Test", $tm->getDefineClass());
	}


	public function test_getSelect()
	{
		$tm = new TestManager();
		$ent = new Test();

		// Селект без условий
		$this->assertEquals(
				"SELECT * FROM `test`",
				$tm->getSelect($ent)
			);

		// С условием
		$sql = new SQLCondition("username = 'lala' AND id = 1");
		$sql->groupBy = "username";
		$sql->orderBy = "id ASC";
		$sql->rows = 10;
		$sql->offset = 100;

		$this->assertEquals(
				"SELECT * FROM `test` WHERE username = 'lala' AND id = 1 GROUP BY username ORDER BY id ASC LIMIT 100 , 10",
				$tm->getSelect($ent, $sql)
			);
	}

	/**
	 * Тестирование вставки новой сущности
	 *
	 */
	public function test_buildInsert()
	{
		$tm = new TestManager();

		$ent = new Test();
		$ent->num = 10;
		$ent->username = "user_name";
		$ent->dt = XDateTime::formatDateTime("25-12-2000");
		$ent->time = "21:00:59";

		// Обычная вставка
		$this->assertEquals(
				"INSERT INTO `test` (`username`, `dt`, `time`, `num`) VALUES('user_name', '2000-12-25 00:00:00', '21:00:59', 10)",
				$tm->getInsert($ent)
			);

		// вставка сущности со спецсимволами
		$ent->username = "O' Raily";
		$this->assertEquals(
				"INSERT INTO `test` (`username`, `dt`, `time`, `num`) VALUES('O\' Raily', '2000-12-25 00:00:00', '21:00:59', 10)",
				$tm->getInsert($ent)
			);


		// вставка сущности с неустановленным полем time (== null)
		$ent->time = null;
		$this->assertEquals(
				"INSERT INTO `test` (`username`, `dt`, `num`) VALUES('O\' Raily', '2000-12-25 00:00:00', 10)",
				$tm->getInsert($ent)
			);

		$ent->time = null;
		$this->assertEquals(
				"INSERT INTO `test` (`username`, `dt`, `num`) VALUES('O\' Raily', '2000-12-25 00:00:00', 10)",
				$tm->getInsert($ent)
			);

		// вставка сущности с неустановленным полем dt (== null)
		$ent->dt = null;
		$this->assertEquals(
				"INSERT INTO `test` (`username`, `num`) VALUES('O\' Raily', 10)",
				$tm->getInsert($ent)
			);
	}

	/**
	 * Тестируем постоение SQL запроса на обновление сущности в базе
	 *
	 */
	public function test_buildUpdate()
	{
		$ent = new Test();
		$ent->setId(1);
		$ent->num = 10;
		$ent->username = "user";
		$ent->dt = XDateTime::formatDateTime("25-12-2000");
		$ent->time = "21:00:59";

		$tm = new TestManager();

		// Обновление сущности
		$this->assertEquals(
				"UPDATE `test` SET `id` = 1, `username` = 'user', `dt` = '2000-12-25 00:00:00', `time` = '21:00:59', `num` = 10 WHERE id = 1",
				$tm->getUpdate($ent)
			);

		// Обновление сущности со спецсимволами
		$ent->username = "O' Raily";
		$this->assertEquals(
				"UPDATE `test` SET `id` = 1, `username` = 'O\' Raily', `dt` = '2000-12-25 00:00:00', `time` = '21:00:59', `num` = 10 WHERE id = 1",
				$tm->getUpdate($ent)
			);

		// Обновление сущности, когда некоторые поля убрали вручную
		// Иногда требуется, чтобы некоторые поля не обновлялись
		unset($ent->username);
		$this->assertEquals(
				"UPDATE `test` SET `id` = 1, `dt` = '2000-12-25 00:00:00', `time` = '21:00:59', `num` = 10 WHERE id = 1",
				$tm->getUpdate($ent)
			);

		// обновление сущности с неустановленным полем time (== null)
		$ent->time = null;
		$this->assertEquals(
				"UPDATE `test` SET `id` = 1, `dt` = '2000-12-25 00:00:00', `time` = null, `num` = 10 WHERE id = 1",
				$tm->getUpdate($ent)
			);

		// обновление сущности с неустановленным полем dt (== null)
		$ent->dt = null;
		$this->assertEquals(
				"UPDATE `test` SET `id` = 1, `dt` = null, `time` = null, `num` = 10 WHERE id = 1",
				$tm->getUpdate($ent)
			);
	}

	public function test_Save()
	{
		$tm = new TestManager();
		$object = new Test();

		$object->username = "UnitTest name";
		$object->time = "12:59:59";

		// create & save
		$tm->save($object);

		// retrieve & compare
		$obj1 = $tm->getById($object->getId());
		$this->assertEquals($object->username, $obj1->username);
		$this->assertEquals($object->time, $obj1->time);
	}

	public function testSaveWithUndefinedProperty()
	{
		$tm = new TestManager();
		$object = new Test();
		try
		{
			$object->username = "undef";
			$object->undefined = "ddddd";
			$tm->save($object);
		}
		catch(Exception $e)
		{
			return false;
		}
		$this->fail('An expected Exception has not been raised.');
	}

	public function test_formatEntityList()
	{
		$list = null;

		for($i = 0; $i< 10; $i++)
		{
			$object = new Test();
			$object->id = $i + 10;
			$list[] = $object;
		}

		// преобразуем список
		$res = EntityManager::formatEntityList($list);

		$this->assertNotNull($res);
		// кол-во элементов не должно измениться
		$this->assertEquals(10, count($res));

		// id = 15 должно быть у элемента массива 15
		$this->assertEquals(15, $res[15]->getId());
		// id = 10 должно быть у элемента массива 10
		$this->assertEquals(10, $res[10]->getId());

		$list = null;
		// преобразуем список
		$res = EntityManager::formatEntityList($list);
		$this->assertNull($res);
	}

	public function test_Update()
	{
		$tm = new TestManager();
		$object = new Test();

		$object->username = "update test";
		$object->num = 10;
		$tm->save($object);

		$object->username = "update test ok";
		$object->num = 0;
		$object->time = "21:12:12";
		$tm->save($object);

		// retrieve & compare
		$obj1 = $tm->getById($object->getId());
		$this->assertEquals($object->username, $obj1->username);

		$this->assertEquals(0, $obj1->num);

		// обновление времени как null
		$object->time = null;
		$tm->save($object);

		$obj1 = $tm->getById($object->getId());
		$this->assertEquals(null, $obj1->time);

	}

	public function test_getOneByAnySQL()
	{
		$tm = new TestManager();
		$object = new Test();

		// сохраним с уникальным именем
		$uname = md5("name") . time();
		$object->username = $uname;
		$tm->save($object);

		// получим по уникальному имени
		$sql = "SELECT * FROM {$object->entityTable} WHERE username = '{$uname}'";
		$res = $tm->getOneByAnySQL($sql);

		$this->assertEquals($uname, $res["username"] );

		//
		// а если найдено несколько таких строк, то должны получить исключение
		//
		try
		{
			$uname = time();
			$object = new Test();
			$object->username = $uname;
			$tm->save($object);

			$object = new Test();
			$object->username = $uname;
			$tm->save($object);

			// получим по уникальному имени
			$sql = "SELECT * FROM {$object->entityTable} WHERE username = '{$uname}'";
			$res = $tm->getOneByAnySQL($sql);
		}
		catch(Exception $e)
		{
			return false;
		}
		$this->fail('An expected Exception has not been raised.');
	}


	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		parent::tearDown();

		try
		{
			$tm = new TestManager();
			$tm->removeAll();
		}
		catch (Exception $e)
		{
			echo $e->getMessage() . "\n";

			if ($e->getCode() == 2003)
				echo "\n!!!!! Check connection setting in resources/TestManager.php !!!!! \n";
		}
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct ()
	{

	}
}

?>