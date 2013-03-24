<?php
/**
 * Created by JetBrains PhpStorm.
 * User: afi
 * Date: 23.03.13
 * Time: 17:01
 * To change this template use File | Settings | File Templates.
 */

require_once "../Solo/Core/DB/ISQLCondition.php";
require_once "../Solo/Core/DB/MySQLCondition.php";

use \Solo\Core\DB\MySQLCondition;

class MySQLConditionTest extends PHPUnit_Framework_TestCase
{
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
