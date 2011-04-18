<?php
/**
 * Автоматический загрузчик классов
 * 
 * PHP version 5
 * 
 * @package 
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

class DefaultClassLoader
{
	
	/**
	 * Массив, содержащий полные пути к файлам 
	 * фреймворка и приложения array ('classname' => 'path_to_file')
	 * 
	 * @var array
	 */
	private static $repository = null;	
	
	/**
	 * Имя файла, в котором содержится информация
	 * 				о местонахождении файлов с классами
	 * 
	 * @var string
	 */
	private static $repositoryFile = null;
	
	/**
	 * Приватный конструктор
	 * 
	 * @return void
	 */
	private function __construct()
	{
	
	}
	
	
	/**
	 * Регистрация метода для автозагрузки файлов
	 *
	 * @param string $repositoryFile Имя файла, в котором содержится информация
	 * 				о местонахождении файлов с классами
	 * @param string $method Имя метода, выполняющего загрузку классов
	 *
	 * @return void
	 */
	public static function init($repositoryFile, $method = "DefaultClassLoader::autoload")
	{
		spl_autoload_register($method);
		self::$repositoryFile = $repositoryFile;
	}	
	
	/**
	 * Реализует автозагрузку файлов с классами
	 * Читает файл репозитория
	 * 
	 * @param string $class Имя класса
	 * 
	 * @return void
	 */
	public static function autoload($class)
	{
		if (strpos($class, "Smarty_") !== false)
			return true;
		
		if (self::$repository == null)
		{
			$file = file_get_contents(self::$repositoryFile);
			self::$repository = unserialize($file);
		}
		$file = @self::$repository[strtolower($class)];
		if ($file === null)
			throw new Exception("DefaultClassLoader : Class '{$class}' does not exists in repository");
		require_once $file;
	}
}
?>