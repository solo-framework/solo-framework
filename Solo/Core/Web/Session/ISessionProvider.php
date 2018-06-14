<?php
/**
 * Интерфейс для всех провайдеров сессий
 *
 * PHP version 5
 *
 * @package web\session
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

namespace Solo\Core\Web\Session;

interface ISessionProvider
{
	public function start();

	public function getSessionManager();
}