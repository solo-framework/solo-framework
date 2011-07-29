<?php

class PDOTestManager extends PDOEntityManager
{
	/**
	 * Для тестирования нужно настроить
	 * подключение к БД
	 * и создать тестовую таблицу (SQL код смотри в Test.php)
	 *
	 */
	public function __construct()
	{
		$adapter = new PDOAdapter();

		$adapter->dsn = "mysql:host=localhost;dbname=frameworktest";
		//$adapter->dsn = "mysql:host=10.0.2.2;dbname=frameworktest";

		$adapter->username = "root";
		$adapter->password = "password";

		$this->setCommonConnection($adapter);
	}

	public function test_getById()
	{

	}
}
?>