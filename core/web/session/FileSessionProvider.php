<?php
/**
 * Сохранение данных сессий дефолтным способом (как настроено в php.ini)
 * TODO: можно доработать как здесь
 * http://socketo.me/api/source-class-Symfony.Component.HttpFoundation.Session.Storage.Handler.NativeFileSessionHandler.html
 *
 * PHP version 5
 *
 * @package web\session
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

class FileSessionProvider implements ISessionProvider, IApplicationComponent
{
	public function initComponent()
	{
		return true;
	}

	public function start()
	{

	}
}
?>