<?php
/**
 * Интерфейс для всех провайдеров сессий
 *
 * PHP version 5
 *
 * @package web\session
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

interface ISessionProvider
{
	public function start();
}
?>