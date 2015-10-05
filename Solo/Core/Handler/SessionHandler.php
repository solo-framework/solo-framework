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
	 * Настройки провайдера сессий, передаютсяв конструктор нужного класса
	 *
	 * @var null
	 */
	public $options = null;

	/**
	 * Выполнение действия в начале
	 *
	 * @return mixed
	 */
	public function onBegin()
	{
		// Старт контекста приложения (сессии)
		$provider = new $this->providerClass($this->options);
		Context::start($this->sessionName, $provider);
	}
}

