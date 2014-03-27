<?php
/**
 * Базовый класс для всех плагинов Smarty
 *
 * PHP version 5
 *
 * @package Smarty
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Core\UI\Smarty\Plugins;

abstract class Base
{
	/**
	 * Тип плагина (function, block, modifier, etc.)
	 *
	 * @return string
	 */
	abstract function getType();

	/**
	 * Название плагина
	 *
	 * @return string
	 */
	abstract function getTag();

	/**
	 * @see http://www.smarty.net/docs/en/caching.cacheable.tpl
	 *
	 * @return bool
	 */
	public function getCacheable()
	{
		return false;
	}

	/**
	 * @see http://www.smarty.net/docs/en/caching.cacheable.tpl
	 *
	 * @return array|null
	 */
	public function getCahceAttributes()
	{
		return null;
	}


	/**
	 * Нужно реализовать этот метод в наследуемом классе.
	 * Т.к. PHP не поддерживает перегрузку методов с различной сигнатурой
	 */
//	abstract function execute();


}

