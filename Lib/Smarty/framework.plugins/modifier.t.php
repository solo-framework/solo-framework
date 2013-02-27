<?php
/**
 * Возвращает перевод строки на другой язык
 * Для работы необходимо сначала инициализировать класс Translate
 *
 * пример:
 * {"привет"|t}
 *
 * с кавычками в строке:
 * {"\"привет\""|t}
 *
 * PHP version 5
 *
 * @category i18n
 * @package  SmartyPlugins
 * @author   Andrey Filippov <afi@i-loto.ru>
 */

function smarty_modifier_t($string)
{
	return gettext($string);
}
?>