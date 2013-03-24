<?php
/**
 *
 * PHP version 5
 *
 * CREATE TABLE `family` (
	`id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
	`ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp',
	PRIMARY KEY (`id`)
	)
	COMMENT='test'
	COLLATE='utf8_general_ci'
	ENGINE=InnoDB;


 *
 * @category BL
 * @package  Entity
 * @author   Andrey Filippov <afi.work@gmail.com>
 * @license  %license% name
 * @version  SVN: $Id: Family.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

use Solo\Core\Entity;

class Family extends Entity
{
	/**
	 * Содержит наименование таблицы в БД, где хранятся сущности этого типа. Не является атрибутом сущности
	 *
	 * @var string
	 */
	public $entityTable = "family";

	/**
	 * Первичный ключ, обычно соответствует атрибуту "id".  Не является атрибутом сущности.
	 *
	 * @var string
	 */
	public $primaryKey = "id";

	/**
	 * Время создания сущности
	 *
	 * @var DateTime
	 */
	public $ts = null;



	/**
	 * Возвращает список полей сущности и их типы
	 *
	 * @return array
	 */
	public function getFields()
	{
		return array(
			"id" => self::ENTITY_FIELD_INT,
			"ts" => self::ENTITY_FIELD_TIMESTAMP,

		);
	}
}

?>