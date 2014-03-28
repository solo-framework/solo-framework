<?php
/**
 * Запуск сессии
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Core\Handler;

use Solo\Core\Context;

class SessionHandler extends Handler
{

	/**
	 * Имя класса обработчика сессий
	 *
	 * @var string
	 */
	public $providerClass = "";

	/**
	 * Имя сессии
	 *
	 * @var string
	 */
	public $sessionName = "";

	/**
	 * Выполнение действия в начале
	 *
	 * @return mixed
	 */
	public function onBegin()
	{
		// Старт контекста приложения (сессии)
		$provider = new $this->providerClass();
		Context::start($this->sessionName, $provider);
	}
}

