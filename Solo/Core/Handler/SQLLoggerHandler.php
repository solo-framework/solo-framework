<?php
/**
 * Выводит в браузер лог SQL запросов
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Core\Handler;

use App\Application;
use Solo\Core\HTTP404Exception;

class SQLLoggerHandler extends Handler
{

	/**
	 * Выполнение действия перед обработкой представления
	 *
	 * @return void
	 */
	public function onBegin()
	{

	}

	/**
	 * Выполнение действия в конце
	 * Принимает на вход HTML-код представления,
	 * в теле метода он может подвергаться доп.обработке
	 *
	 * @param string $response HTML-код
	 *
	 * @return string преобразованный HTML-код
	 */
	public function onFinish($response)
	{
		/** @var $db PDOAdapter*/
		$db = Application::getInstance()->getComponent("db");
		$log = $db->getLog();

		$log = implode("<br/>", $log);
		return $response . $log;
	}
}

