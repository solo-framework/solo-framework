<?php
/**
 * Построение URL
 * {link action=test id=435435 name=abc} => action/test/id/435435/name/abc
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Core\UI\Smarty\Plugins;

class Link extends Base
{

	/**
	 * Тип плагина (function, block, modifier, etc.)
	 *
	 * @return string
	 */
	function getType()
	{
		return "function";
	}

	/**
	 * Название плагина
	 *
	 * @return string
	 */
	function getTag()
	{
		return "link";
	}

	/**
	 * @param  array $params Параметры
	 *
	 * @return string
	 */
	public function execute($params)
	{
		$str = "/";
		foreach ($params as $k => $v)
		{
			$str .= "{$k}/{$v}/";
		}

		return $str;
	}
}

