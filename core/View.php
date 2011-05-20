<?php
/**
 * Базовый класс для всех представлений
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
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
	 * Путь к файлу с шаблоном для текущего Представления
	 *
	 * @var string
	 */
	public $templateFile = null;

	/**
	 * Путь к каталогу, где находится шаблон представления
	 *
	 * @var string
	 */
	public $templateFolder = null;


	public $cacheId = null;

	public $compileId = null;

	/**
	 * Метод вызывается перед render()
	 *
	 * @return void
	 */
	public function preRender()
	{

	}

	/**
	 * Метод вызывается после preRender()
	 *
	 * @return void
	 */
	public abstract function render();


	/**
	 * Метод вызывается после render()
	 *
	 * @return void
	 */
	public function postRender()
	{

	}
}
?>