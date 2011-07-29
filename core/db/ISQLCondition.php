<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

interface ISQLCondition
{
	/**
	* Формирует SQL запрос
	*
	* @return string SQL запрос
	*/
	public function buildSQL();

	public function getParams();

}
?>