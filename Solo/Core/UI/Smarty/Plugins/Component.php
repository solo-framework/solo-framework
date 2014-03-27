<?php
/**
 * Отрисовка компонента представления
 * {component name=App\View\ComponentView [par1="val1" par2="val2"]}
 *
 * PHP version 5
 *
 * @package Smarty
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Core\UI\Smarty\Plugins;

use App\Application;

class Component extends Base
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
		return "component";
	}

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	public function execute($params)
	{
		if(!array_key_exists('name', $params))
			return "Undefined component name";

		$className = $params["name"];
		unset($params['name']);

		$className = str_replace(".", "\\", $className);

		$ctr = Application::getInstance()->getComponent("controller");
		return $ctr->renderViewComponent($className, $params);
	}

}

