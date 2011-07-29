<?php
require_once 'core/EntityManager.php';

class TestManager extends EntityManager
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


	public function getDefineClass()
	{
		return $this->defineClass();
	}

	/**
	 * Для тестов переопределим метод escape
	 * т.к. он требует подключения к базе,
	 * но mysql_escape_string выполняет те же функции
	 *
	 * @param string $val Строка
	 */
//	public function escape($val)
//	{
//		return mysql_escape_string($val);
//	}
}
?>