<?php
/**
 * Сохранение данных сессий дефолтным способом (как настроено в php.ini)
 * TODO: можно доработать как здесь
 * http://socketo.me/api/source-class-Symfony.Component.HttpFoundation.Session.Storage.Handler.NativeFileSessionHandler.html
 *
 * PHP version 5
 *
 * @package web\session
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

namespace Solo\Core\Web\Session;

use Solo\Core\IApplicationComponent;

class FileSessionProvider implements ISessionProvider, IApplicationComponent
{
	public function initComponent()
	{
		return true;
	}

	public function start()
	{

	}

	/**
	 * @param $opts
	 */
	public function __construct($opts)
	{

	}
}
