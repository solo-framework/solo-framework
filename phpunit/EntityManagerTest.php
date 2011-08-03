<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

error_reporting(E_ALL);

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'core/db/IDBAdapter.php';
require_once 'core/db/PDOAdapter.php';
require_once 'core/db/SoloPDOStatement.php';
require_once 'core/db/SoloPDO.php';
require_once 'core/db/ISQLCondition.php';
require_once 'core/db/MySQLCondition.php';
require_once 'core/EntityManager.php';

require_once 'phpunit/resources/Test.php';
require_once 'phpunit/resources/TestManager.php';

class EntityManagerTest  extends PHPUnit_Framework_TestCase
{
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp();
		$tm = new TestManager();

		//
		// Таблица для тестирования
		//
		$dropTable = "DROP TABLE IF EXISTS `test`";
		$tm->executeNonQuery($dropTable, array());

		$table = "	CREATE TABLE `test` (
						`id` INT(11) NOT NULL AUTO_INCREMENT,
						`username` VARCHAR(200) NULL DEFAULT NULL,
						`dt` DATETIME NULL,
						`time` TIME NULL,
						`num` INT(11) NULL DEFAULT NULL,
						`ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
						INDEX `id` (`id`)
					)
					ENGINE=InnoDB
					ROW_FORMAT=DEFAULT";

		$tm->executeNonQuery($table, array());

		//
		// Хранимая процедура для тестирования
		//
		$dropSP = "DROP PROCEDURE IF EXISTS `getRows`";
		$tm->executeNonQuery($dropSP, array());

		$sp = "CREATE PROCEDURE `getRows`()
				LANGUAGE SQL
				NOT DETERMINISTIC
				CONTAINS SQL
				SQL SECURITY DEFINER
				COMMENT ''
			BEGIN
				SELECT id, num FROM `test`;
			END";
		$tm->executeNonQuery($sp, array());

		//
		// И еще одна хранимая процедура
		//

		$dropSP2 = "DROP PROCEDURE IF EXISTS `getTest`";
		$tm->executeNonQuery($dropSP2, array());

		$sp2 = "
		CREATE PROCEDURE `getTest`()
			LANGUAGE SQL
			NOT DETERMINISTIC
			CONTAINS SQL
			SQL SECURITY DEFINER
			COMMENT ''
		BEGIN
			select * from `test`;
		END
		";
		$tm->executeNonQuery($sp2, array());
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown()
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

			echo "\n!!!!! Check connection setting in resources/PDOTestManager.php !!!!! \n";
		}
	}

	public function test_defineClass()
	{
		$tm = new TestManager();
		$this->assertEquals("Test", $tm->getDefineClass());
	}

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
		$this->assertEquals("21:12:12", $object->time);

		// обновление времени как null
		$object->time = null;
		$tm->save($object);

		$obj1 = $tm->getById($object->getId());
		$this->assertEquals(null, $obj1->time);

		$object->num = null;
		$tm->save($object);
		$obj1 = $tm->getById($object->getId());
		$this->assertEquals(null, $obj1->num);

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
		$sql = "SELECT * FROM {$object->entityTable} WHERE username = ?";
		$res = $tm->getOneByAnySQL($sql, array($uname));

		$this->assertEquals($uname, $res["username"] );

		$uname = time();
		$object = new Test();
		$object->username = $uname;
		$tm->save($object);

		$object = new Test();
		$object->username = $uname;
		$tm->save($object);

		// получим по уникальному имени
		$sql = "SELECT * FROM {$object->entityTable} WHERE username = ?";
		$res = $tm->getOneByAnySQL($sql, array($uname));
	}

	/**
	 *
	 *
	 * @return
	 */
	public function test_get()
	{
		$tm = new TestManager();
		$object = new Test();

		$object->username = "get test";
		$object->num = 10;
		$tm->save($object);

		$object2 = new Test();
		$object2->num = 20;
		$object2->username = "get";
		$tm->save($object2);

		// записи по условию
		$cond = MySQLCondition::create()
			->where("num > ?")
			->setParams(10);

		$res = $tm->get($cond);
		$this->assertEquals(1, count($res));
		$this->assertEquals("Test", get_class($res[0]));

		// все записи
		$res = $tm->get();
		$this->assertEquals(2, count($res));
	}

	/**
	 *
	 *
	 * @return
	 */
	public function test_getBySQL()
	{
		$tm = new TestManager();
		$object = new Test();

		$object->username = "get test";
		$object->num = 10;
		$tm->save($object);

		$object2 = new Test();
		$object2->num = 20;
		$object2->username = "get";
		$tm->save($object2);

		// записи по условию
		$sql = "SELECT * FROM test WHERE num = ? AND username = ?";

		$res = $tm->getBySQL($sql, array(20, "get"));

		$this->assertEquals(1, count($res));
		$this->assertEquals("Test", get_class($res[0]));
		$this->assertEquals("get", $res[0]->username);
	}

	/**
	 *
	 *
	 * @return
	 */
	public function test_removeByCondition()
	{
		$tm = new TestManager();
		$object = new Test();

		$object->username = "get test";
		$object->num = 10;
		$tm->save($object);

		$object2 = new Test();
		$object2->num = 20;
		$object2->username = "get";
		$tm->save($object2);

		// Удалить записи по условию
		//$sql = "SELECT * FROM test WHERE num = ? AND username = ?";

		$res = $tm->removeByCondition(MySQLCondition::create()->where("num = ?")->setParams(20));
		$this->assertEquals(1, $res);
	}

	/**
	 *
	 *
	 * @return
	 */
	public function test_getOne()
	{
		$tm = new TestManager();
		$object = new Test();

		$object->username = "get test";
		$object->num = 10;
		$tm->save($object);

		$object2 = new Test();
		$object2->num = 20;
		$object2->username = "get";
		$tm->save($object2);

		$res = $tm->getOne(MySQLCondition::create()->where("num = ?")->setParams(20));
		$this->assertEquals(1, count($res));
	}

	/**
	 *
	 * @expectedException RuntimeException
	 */
	public function test_getOne_fail()
	{
		$tm = new TestManager();
		$object = new Test();

		$object->username = "get test";
		$object->num = 20;
		$tm->save($object);

		$object2 = new Test();
		$object2->num = 20;
		$object2->username = "get";
		$tm->save($object2);

		$res = $tm->getOne(MySQLCondition::create()->where("num = ?")->setParams(20));
		$this->assertEquals(1, count($res));
	}


	public function test_executeNonQuery()
	{
		$tm = new TestManager();
		$object = new Test();

		$object->username = "get test";
		$object->num = 20;
		$tm->save($object);

		$object2 = new Test();
		$object2->num = 20;
		$object2->username = "get";
		$tm->save($object2);

		$res = $tm->executeNonQuery("DELETE FROM test WHERE num = ?", array(20));
		$this->assertEquals(2, $res);
	}

	public function test_getColumn()
	{
		$tm = new TestManager();
		$object = new Test();

		$object->username = "get test";
		$object->num = 20;
		$tm->save($object);

		$object2 = new Test();
		$object2->num = 20;
		$object2->username = "get";
		$tm->save($object2);

		$res = $tm->getColumn("SELECT username FROM test WHERE num = ?", array(20), 0);
		$this->assertEquals(2, count($res));
	}

	/**
	 *
	 *
	 * @return
	 */
	public function test_call_stored_procedure()
	{
		$tm = new TestManager();
		$object = new Test();

		$object->username = "sp test";
		$object->num = 10;
		$tm->save($object);

		$object2 = new Test();
		$object2->num = 20;
		$object2->username = "sp test 2";
		$tm->save($object2);

		$res = $tm->callStoredProcedure("call getRows()", array());
		$this->assertEquals(2, count($res));
	}

	/**
	 *
	 *
	 * @return
	 */
	public function test_test_by_stored_procedure()
	{
		$tm = new TestManager();
		$object = new Test();

		$object->username = "sp test";
		$object->num = 10;
		$tm->save($object);

		$object2 = new Test();
		$object2->num = 20;
		$object2->username = "sp test 2";
		$tm->save($object2);

		$res = $tm->getByStoredProcedure("call getTest()", array());

		$this->assertEquals(2, count($res));
		$this->assertEquals("Test", get_class($res[0]));
	}
}
?>