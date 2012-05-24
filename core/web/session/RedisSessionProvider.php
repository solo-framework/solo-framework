<?php
/**
 * Сохранение сессий в Redis
 * Расширение для работы с redis: https://github.com/nicolasff/phpredis
 *
 * PHP version 5
 *
 * @package web\session
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

class RedisSessionProvider implements ISessionProvider, IApplicationComponent
{
	/**
	 * Инициализация компонента приложения
	 *
	 * @see IApplicationComponent::initComponent()
	 */
	public function initComponent()
	{
		return true;
	}

	/**
	 * Время жизни сессии в секундах
	 * Если не задано, то берется из session.gc_maxlifetime
	 *
	 * @var int
	 */
	public $gcMaxLifeTime = 1440;

	/**
	 * Строка подключения к Redis
	 * https://github.com/nicolasff/phpredis#session-handler-new
	 *
	 * @var string
	 */
	public $savePath = "tcp://localhost:6379?weight=1&timeout=60&persistent=0&prefix=SESSION:&auth=";

	/**
	 * Конструктор
	 *
	 * @param string $connectionString Строка подключения к Redis
	 * @param int $lifetime Время жизни сессии в секундах
	 * @throws Exception
	 *
	 * @return void
	 */

	public function start()
	{
		if ($this->gcMaxLifeTime == null)
		{
			$this->gcMaxLifeTime = (int)ini_get('session.gc_maxlifetime');
			if (0 == $this->gcMaxLifeTime)
				throw new Exception("Please set session.gc_maxlifetime to enable garbage collection");
		}

		ini_set("session.save_handler", "redis");
		ini_set("session.save_path", $this->savePath);
		ini_set("session.gc_maxlifetime", $this->gcMaxLifeTime);

		//register_shutdown_function('session_write_close');
	}


}
?>