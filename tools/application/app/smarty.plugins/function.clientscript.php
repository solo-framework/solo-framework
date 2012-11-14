<?php
/**
 * Вставляет в HTML теги для подключения JS и CSS скриптов
 * Выполняет контроль загрузки JS и CSS файлов,
 * чтобы одинаковые файлы не подгружались несколько раз
 *
 * @example
 * {loadscript file='js/example.js' type='js' [media]}
 * {loadscript file='js/example.css' type='css' [media]}
 *
 * PHP version 5
 *
 * @author   Andrey Filippov <afi.work@gmail.com>
 */

function smarty_function_clientscript($params, $smarty)
{
	$loader = Application::getInstance()->getComponent("clientscript");
	$res = $loader->load($params["file"]);
	if ($res === false)
		return "";

	$type = $params['type'];
	$fileName = $params['file'];
	$media = $params['media'] ? "media=\"" . $params['media'] . "\" " : "";
	$revision = $loader->revision;
	$fileSuffix = $loader->fileSuffix;

	if ($fileSuffix != null)
	{
		$path = pathinfo($fileName);
		$fileName = $path["dirname"] . $path["filename"] . $fileSuffix . $path["extension"];
	}

	if($type == "js")
		return "<script language=\"javascript\" type=\"text/javascript\" src=\"{$fileName}?{$revision}\"></script>";
	if($type == "css")
		return "<link type=\"text/css\" href=\"{$fileName}?{$revision}\" rel=\"stylesheet\" {$media}/>";
	if($type == "swf")
		return "{$fileName}?{$revision}";
}
?>