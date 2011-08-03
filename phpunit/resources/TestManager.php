<?php
require_once 'core/EntityManager.php';

class TestManager extends EntityManager
{
	/**
	 * Для тестирования нужно настроить
	 * подключение к БД
	 */
	public function __construct()
	{
		$adapter = new PDOAdapter();

		$adapter->dsn = "mysql:host=localhost;dbname=frameworktest";
		//$adapter->dsn = "mysql:host=10.0.2.2;dbname=frameworktest";

		$adapter->username = "root";
		$adapter->password = "password";
		$adapter->initialCommands = array("SET NAMES utf8");

		$this->setCommonConnection($adapter);
	}


	public function getDefineClass()
	{
		return $this->defineClass();
	}


}
?>