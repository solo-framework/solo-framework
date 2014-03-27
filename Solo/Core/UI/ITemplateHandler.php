<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Core\UI;

interface ITemplateHandler
{

	/**
	 * Ctor
	 *
	 * @param array $config Список настроек
	 * @param array $extraData Дополнительные данные из View
	 */
	public function __construct($config, $extraData);

	/**
	 * Возвращает результат обработки шаблона
	 *
	 * @param string $template Путь к шаблону
	 *
	 * @return string
	 */
	public function fetch($template);

	/**
	 * @param string $name Имя переменной шаблона
	 * @param mixed $value Значение переменной шаблона
	 *
	 * @return mixed
	 */
	public function assign($name, $value);

}

