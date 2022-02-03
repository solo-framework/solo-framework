<?php
/**
 * Базовый класс для всех представлений
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

namespace Solo\Core\UI;

abstract class View
{
	/**
	 * Файл общего шаблона страницы
	 *
	 * @var string
	 */
	public ?string $layout = null;

	/**
	 * Имя файла с шаблоном для текущего Представления
	 *
	 * @var string
	 */
	public ?string $templateFile = null;

	/**
	 * Путь к каталогу, где находится шаблон представления
	 *
	 * @var string
	 */
	public ?string $templateFolder = null;

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
	public function postRender() : void
	{

	}

	/**
	 * Дополнительные данные для обработчика шаблонов
	 *
	 * @return array
	 */
	abstract function getExtraData(): array;

}

