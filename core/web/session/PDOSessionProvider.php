<?php
/**
 * Провайдер для сохранения данных сессий в базе данных
 *
 * PHP version 5
 *
 * @package web\session
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

class PDOSessionProvider implements ISessionProvider, IApplicationComponent
{
	public $connectionName = null;

	private $connection = null;

	public function initComponent()
	{
		$this->connection = Application::getInstance()->getComponent($this->connectionName);

		return true;
	}

	public function start()
	{
		session_set_save_handler(
			array($this, "open"),
			array($this, "close"),
			array($this, "read"),
			array($this, "write"),
			array($this, "destroy"),
			array($this, "gc")
		);
	}
}
?>