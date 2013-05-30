<?php
/**
 * Created by JetBrains PhpStorm.
 * User: afi
 * Date: 29.05.13
 * Time: 22:34
 * To change this template use File | Settings | File Templates.
 */

namespace Solo\Core\Handler;


abstract class Handler
{
	/**
	 * Выполнение действия перед обработкой представления
	 *
	 * @return void
	 */
	public abstract function onBegin();

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