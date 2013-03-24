<?php

namespace tests\Managers;

use \Solo\Core\EntityManager;
use \Solo\Core\DB\PDOAdapter;

class BaseTestEntityManager extends EntityManager
{
	/**
	 * Для тестирования нужно настроить
	 * подключение к БД
	 */
	public function __construct()
	{
		$adapter = new PDOAdapter();

		$adapter->dsn = "mysql:host=ubuntu;dbname=soloframeworktest";
//		$adapter->dsn = "mysql:host=localhost;dbname=soloframeworktest";

		$adapter->username = "root";
		$adapter->password = "password";
		$adapter->initialCommands = array("SET NAMES utf8");

		$this->setCommonConnection($adapter);
	}
}
?>