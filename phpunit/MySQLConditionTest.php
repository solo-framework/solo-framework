<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi.work@gmail.com>
 */
require_once 'PHPUnit/Framework/TestCase.php';

require_once 'core/db/ISQLCondition.php';
require_once 'core/db/MySQLCondition.php';

class MySQLConditionTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{

	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{

	}

	public function test_buildSQL()
	{
		$c = MySQLCondition::create()
			->where("id < ? AND name = ? AND age")
			->in(array(1,2,3,4))
			->orderBy("name DESC")
			->limit(100, 50);

		$this->assertEquals(
			trim(" WHERE id < ? AND name = ? AND age IN (1, 2, 3, 4) ORDER BY name DESC LIMIT 100, 50"),
			trim($c->buildSQL())
			);
	}

	public function test_getParams()
	{
		$c = MySQLCondition::create()
			->where("id < ? AND name = ? AND age")
			->in(array(1,2,3,4))
			->orderBy("name DESC")
			->limit(100, 50)
			->setParams(45, "name");

		$this->assertEquals(array(45, "name"), $c->getParams());
	}
}
?>