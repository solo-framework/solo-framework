<?php

class PHPConfiguratorParser implements IConfiguratorParser
{
	
	/**
	 * Содержимое конфигурационного файла в 
	 * виде массива 
	 * 
	 * @var array
	 */
	private $config = null;	
	
	/**
	 * Конструктор
	 * 
	 * @param string $configFile Путь к файлу конфигурации
	 * 
	 * @return void
	 */
	public function __construct($configFile)
	{
		if (!file_exists($configFile))
			throw new Exception("Config file '{$configFile}' does not exists.");

		$this->config = require $configFile;
		
		// Обрабатываем директиву @exdends
		if (isset($this->config["@extends"]))
		{
			$this->extend($this->config["@extends"]);
		}
	}	
	
	/**
	 * Обрабатывает директиву @exdends
	 * Если в конфигурации найдена директива @extends, то
	 * подключаем файл, который указан в ней и делаем его основным (main)
	 * Настройки файла, поключенного непосредственно, должны перезаписать
	 * настройки, определенные в main
	 * 
	 * @param string $baseFile Путь к файлу, указанному в директиве @exdends
	 */
	public function extend($baseFile)
	{
		if (!file_exists($baseFile))
			throw new RuntimeException("Can't load parent config file '{$baseFile}'");
	
		// директива больше не нужна
		unset($this->config["@extends"]);
		
		$main = require $baseFile;
		
		//var_dump($main);
		
//		// файл, указанный в @extends
//		$main = parse_ini_file($baseFile, true);
//
		$res = null;
		foreach ($main as $k => $v)
		{
			// если есть такая секция - перезаписываем значения
			// или просто добавляем такую секцию
			if (isset($this->config[$k]))
				$res[$k] = array_merge( $main[$k], $this->config[$k]);
			else 
				$res[$k] = $v;
		}	
		$this->config = $res;
	}

	/**
	 * Возвращает значение параметра, определенного в файле конфигурации
	 * 
	 * @param string $param Имя параметра в формате section:option 
	 * 
	 * @example $dbPassword = Configurator::get("first_connection:password");
	 * 
	 * @return mixed
	 */
	public function get($param)
	{
		$tmp = explode(":", $param);
		if (isset($this->config[$tmp[0]][$tmp[1]]))
			return $this->config[$tmp[0]][$tmp[1]];
		else
			throw new Exception("Undefined config option : {$tmp[0]}:{$tmp[1]}");
	}

	/**
	 * @param unknown_type $paramName
	 */
	public function getArray($paramName)
	{
		
	}

	/**
	 * Возвращает массив с настройками
	 * 
	 * @return array
	 */
	public function getOptions()
	{
		return $this->config;
	}

	/**
	 * @param unknown_type $sectionName
	 */
	public function getSection($sectionName)
	{
		
	}	
}
?>