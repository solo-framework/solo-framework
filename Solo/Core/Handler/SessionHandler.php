<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Core\Handler;

use App\Application;
use Solo\Core\Configurator;
use Solo\Core\Context;

class SessionHandler extends Handler
{
	/**
	 * Выполнение действия в начале
	 *
	 * @return mixed
	 */
	public function onBegin()
	{
		// Старт контекста приложения (сессии)
		$provider = Application::getInstance()->getComponent(Configurator::get("application:session.provider"));
		Context::start(Configurator::get("application:sessionname"), $provider);
	}

}

