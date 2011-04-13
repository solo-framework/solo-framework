<?php
require_once 'core/EntityManager.php';

class TestManager extends EntityManager
{
	/**
	 * Для тестирования нужно настроить
	 * подключение к БД
	 * 
	 */
	public function __construct()
	{
		$adapter = new MySQLAdapter();
		$conf = array(
			"host" => "localhost",
			"database" => "test",
			"user" => "root",
			"password" => "password"
		);
				
		$adapter->setConfig($conf);
		$this->setCommonConnection($adapter);
	}
	
	public function getUpdate(Entity $ent)
	{
		return $this->buildUpdate($ent);
	}
	
	public function getInsert(Entity $ent)
	{
		return $this->buildInsert($ent);
	}
	
	public function getSelect(Entity $object, SQLCondition $condition = null)
	{
		return $this->buildSelect($object, $condition);
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
	public function escape($val)
	{
		return mysql_escape_string($val);
	}
}
?>