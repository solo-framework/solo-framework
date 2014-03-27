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

abstract class BaseTemplateHandler
{
	protected $render = null;

	/**
	 * Дополнительные данные из View
	 *
	 * @var mixed
	 */
	protected $extraData = null;

	/**
	 * Данные шаблона
	 *
	 * @var mixed
	 */
	protected $data = null;

	/**
	 * @param string $name Имя переменной шаблона
	 * @param mixed $value Значение переменной шаблона
	 *
	 * @return mixed
	 */
	public function assign($name, $value)
	{
		$this->data[$name] = $value;
	}

	/**
	 * Возвращает значение по имени
	 *
	 * @param string $name Имя переменной в доп. данных
	 * @param mixed $defaultValue Значение по-умолчанию
	 *
	 * @return mixed
	 */
	protected function getExtra($name, $defaultValue = null)
	{
		return isset($this->extraData[$name]) ? $this->extraData[$name] : $defaultValue;
	}
}

