<?php
/**
 * 
 * 
 * PHP version 5
 * 
 * @category Core
 * @package  Core
 * @author   Andrey Filippov <afi@i-loto.ru>
 * @license  %license% name
 * @version  SVN: $Id: View.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

abstract class View
{
	/**
	 * Файл общего шаблона страницы
	 * 
	 * @var string
	 */
	public $layout = null;
	
	/**
	 * Данные шаблона
	 * 
	 * @var mixed
	 */
	protected $data = null;	
	
	/**
	 * Путь к файлу с шаблоном для текущего Представления
	 *
	 * @var string
	 */
	public $templateFile = null;
	

	public function preRender()
	{
		
	}
	
	public abstract function render();
	
	public function postRender()
	{
		
	}
	
	/**
	* Добавляет данные в Представление
	* 
	* @param string $name Имя объекта, по которому он будет доступен в шаблоне
	* @param mixed $data Данные
	* 
	* @return void
	*/
	public function addData($name, $data)
	{
		$this->data[$name] = $data;
	}
	
	/**
	* Возвращает данные контрола
	* 
	* @return mixed
	*/
	public function getData()
	{
		return $this->data;
	}	
}
?>