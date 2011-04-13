<?php
/**
 * Класс для работы с файлами конфигурации
 * 
 * PHP version 5
 * 
 * @example Configurator::init(new IniConfigurator(APPLICATION_DIR . "/Config/develop.php")); - подключается файл /Config/develop.php
 * 			
 * 			Есть возможность применения 2-х уровневой конфигурации, когда в базовом файле (main) указаны все настройки 
 * 			приложения, а в файле, расширяющем его - настройки для конкретного разработчика (personal) или production-сервера
 * 			Для этого в персональном файле нужно указать директиву @extends = "путь до файла с базовыми настройками".
 * 			При этом значения, указаные в personal перезаписывают значения main.
 * 			Примеры использования см. в юнит-тестах
 * 
 * @category Framework
 * @package  Core
 * @author   Andrey Filippov <afi@i-loto.ru>
 * @license  %license% name
 * @version  SVN: $Id: Configurator.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

abstract class Configurator
{
	
	/**
	 * Содержимое конфигурационного файла в 
	 * виде массива 
	 * 
	 * @var array
	 */
	private static $config = null;
	
	/**
	 * Экземпляр обработчика конфигурации
	 * 
	 * @var IConfigurator
	 */
	private static $object = null;
	

	/**
	 * Инициализация конфигуратора.
	 * 
	 * @param IConfigurator $object Обработчик файла конфигурации
	 * 
	 * @return void
	 */
	public static function init(IConfiguratorParser $object)
	{
		if (self::$config == null)
		{
			self::$object = $object;
			self::$config = $object->getOptions();
		}
		return true;
	}
	

	/**
	 * Сброс конфигуратора. Необходимо снова вызывать Configurator::init()
	 * 
	 * @return void
	 */
	public static function reset()
	{
		self::$config = null;
	}
	
	/**
	 * Возвращает значение параметра, определенного в файле конфигурации
	 * 
	 * @param string $param Имя параметра в формате section:option 
	 * 
	 * @example $dbPassword = Configurator::get("first_connection:password");
	 * 
	 * @return mixed
	 * */
	public static function get($param)
	{
		if (self::$config == null)
			throw new Exception("Configurator not initialized.");
			
		return self::$object->get($param);
	}
	
	/**
	 * Возвращает настройки для определенной секции
	 * в виде массива
	 * 
	 * @param string $sectionName Имя секции
	 * 
	 * @throws Exception 
	 * @return array
	 * */
	public static function getSection($sectionName)
	{
		if (self::$config == null)
			throw new Exception("Configurator not initialized.");
		
		return self::$object->getSection($sectionName);
	}
	
	/**
	 * Массив, содержащий полные пути к файлам 
	 * фреймворка и приложения
	 * 
	 * @var array('classname' => 'path_to_file')
	 */
	private static $repository = null;
	

	/**
	 * Возвращает значение одного параметра в виде массива.
	 * 
	 * @param string $paramName Имя параметра
	 * 
	 * @return array
	 */
	public static function getArray($paramName)
	{
		return self::$object->getArray($paramName);
	}
	
	/**
	 * Возвращает все настройки
	 * 
	 * @return mixed
	 */
	public static function getAll()
	{
		return self::$object->getOptions();
	}
}
?>