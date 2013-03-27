<?php

namespace Solo\Core;

class PHPConfiguratorParser implements IConfiguratorParser
{

	/**
	 * Содержимое конфигурационного файла в
	 * виде массива
	 *
	 * @var array
	 */
	private $config = array();

	/**
	 * Конструктор
	 *
	 * @param string $configFile Путь к файлу конфигурации
	 *
	 * @throws \Exception
	 * @return \Solo\Core\PHPConfiguratorParser
	 */
	public function __construct($configFile)
	{
		if (!file_exists($configFile))
			throw new \Exception("Config file '{$configFile}' does not exist.");

		$this->extend($configFile);
	}

	/**
	 * Обрабатывает директиву @exdends
	 * Если в конфигурации найдена директива @extends, то
	 * подключаем файл, который указан в ней и делаем его основным (main)
	 * Настройки файла, поключенного непосредственно, должны перезаписать
	 * настройки, определенные в main
	 *
	 * @param string $file Путь к файлу, указанному в директиве @exdends
	 *
	 * @throws \RuntimeException
	 * @return void
	 */
	public function extend($file)
	{
		if (!is_file($file))
			throw new \RuntimeException("Can't load config file '{$file}'");

		$parentFile = null;
		$config = require $file;

		if (isset($config["@extends"]))
		{
			$parentFile = $config["@extends"];
			// директива больше не нужна
			unset($config["@extends"]);

			$this->extend($parentFile);
		}
		$this->config = array_replace_recursive($this->config, $config);
	}

	/**
	 * Возвращает значение параметра, определенного в файле конфигурации
	 *
	 * @param string $param Имя параметра в формате section:option
	 *
	 * @throws \Exception
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
			throw new \Exception("Undefined config option : {$tmp[0]}:{$tmp[1]}");
	}

	/**
	 * Возвращает массив значений
	 *
	 * @param string $paramName Имя параметра в формате section:parameter
	 *
	 * @return array
	 */
	public function getArray($paramName)
	{
		$value = $this->get($paramName);
		if ($value == null)
			return array();
		else
			return $value;
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
	 * Возвращает массив значений из секции
	 *
	 * @param string $sectionName Имя секции конфигуратора
	 *
	 * @throws \Exception
	 * @return array
	 */
	public function getSection($sectionName)
	{
		if (isset($this->config[$sectionName]))
			return $this->config[$sectionName];
		else
			throw new \Exception("Undefined config section : {$sectionName}");
	}
}
?>
