<?php
/**
 * Базовый класс для обработчиков
 *
 * PHP version 5
 *
 * @package Core
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Core\Handler;

abstract class Handler
{
	/**
	 * Обработчик влючен\выключен
	 *
	 * @var bool
	 */
	public $isEnabled = true;

	/**
	 * Выполнение действия перед обработкой представления
	 *
	 * @return void
	 */
	public abstract function onBegin();

	/**
	 * Создает атрибуты обработчика, описанные в конфигурации
	 *
	 * @param array $params Список атрибутов
	 *
	 * @return void
	 */
	public function init($params = array())
	{
		foreach ($params as $k => $v)
		{
			$this->$k = $v;
		}
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
		return $response;
	}
}