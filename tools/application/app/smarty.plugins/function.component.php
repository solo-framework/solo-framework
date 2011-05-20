<?php
/**
 * Отрисовка компонента представления
 * {component name=ComponentView [par1="val1" par2="val2"]}
 * 
 * PHP version 5
 * 
 * @category 
 * @package  
 * @author   Andrey Filippov <afi@i-loto.ru>
 * @license  %license% name
 * @version  SVN: $Id: function.component.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

function smarty_function_component($params, $smarty)
{
	if(!array_key_exists('name', $params))
		return "Undefined component name";
		
	$className = $params["name"];
	unset($params['name']);

	$ctr = Controller::getInstance();
	return $ctr->renderComponent($className, $params);
}
?>