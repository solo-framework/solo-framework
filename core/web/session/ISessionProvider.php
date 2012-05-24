<?php
/**
 * Интерфейс для всех провайдеров сессий
 *
 * PHP version 5
 *
 * @package web\session
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

interface ISessionProvider
{
	public function start();
}
?>