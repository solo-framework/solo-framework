<?php
/**
 * Интерфейс для всех парсеров конфигурационных файлов
 *
 * PHP version 5
 *
 * @category Framework
 * @package  Core
 * @author   Andrey Filippov <afi.work@gmail.com>
 * @license  %license% name
 * @version  SVN: $Id: Entity.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

namespace Solo\Core;

interface IConfiguratorParser
{

	/**
	 * Возвращает значение параметра, определенного в файле конфигурации
	 *
	 * @param string $param Имя параметра в формате section:option
	 *
	 * @example $dbPassword = Configurator::get("first_connection:password");
	 *
	 * @return mixed
	 * */
	public function get($param);

	/**
	 * Возвращает настройки для определенной секции
	 * в виде массива
	 *
	 * @param string $sectionName Имя секции
	 *
	 * @throws Exception
	 * @return array
	 * */
	public function getSection($sectionName);

	/**
	 * Возвращает значение одного параметра в виде массива.
	 *
	 * @param string $paramName Имя параметра
	 *
	 * @return array
	 */
	public function getArray($paramName);

	/**
	 * Возвращает массив со всеми настройками
	 *
	 * @return array
	 */
	public function getOptions();

	/**
	 * Обрабатывает директиву @exdends
	 * Если в конфигурации найдена директива @extends, то
	 * подключаем файл, который указан в ней и делаем его основным (main)
	 * Настройки файла, поключенного непосредственно, должны перезаписать
	 * настройки, определенные в main
	 *
	 * @param string $baseFile Путь к файлу, указанному в директиве @exdends
	 */
	public function extend($baseFile);

}
?>
