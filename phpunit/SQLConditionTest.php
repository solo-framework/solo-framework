<?php
/**
 * Тестирование класса для составления SQL выражений
 * 
 * PHP version 5
 * 
 * @category Framework
 * @package  Core
 * @author   Andrey Filippov <afi.work@gmail.com>
 * @license  %license% name
 * @version  SVN: $Id: SQLConditionTest.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

require_once 'core/db/SQLCondition.php';
require_once 'PHPUnit/Framework/TestCase.php';

class SQLConditionTest extends PHPUnit_Framework_TestCase
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
	
	public function test_build()
	{
		$expect = " WHERE id = 1 GROUP BY a ORDER BY b LIMIT 1 , 10";
		$sql = new SQLCondition("id = 1", "a", "b", 10, 1);
		$res = $sql->buildSQL();
		$this->assertEquals($expect, $res);
	}
}
?>