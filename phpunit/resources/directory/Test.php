<?php
require_once 'core/Entity.php';

/**
 * Сущность для тестирования
 *
 *
 *
	CREATE TABLE `test` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`username` VARCHAR(200) NULL DEFAULT NULL,
		`dt` DATETIME NULL,
		`time` TIME NULL,
		`num` INT(11) NULL DEFAULT NULL,
		`ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		INDEX `id` (`id`)
	)
	ENGINE=InnoDB
	ROW_FORMAT=DEFAULT
 *
 */

class Test extends Entity
{
	public $entityTable = "test";
	
	public $primaryKey = 'id';
	
	public $username = null;

	public $dt = null;

	public $time = null;

	public $num = null;

	public $ts = null;

    function getFields()
    {
        return array
        (
   			"id" => self::ENTITY_FIELD_INT,
			"username" => self::ENTITY_FIELD_STRING,
			"dt" => self::ENTITY_FIELD_DATETIME,
        	"time" =>self::ENTITY_FIELD_TIME,
			"num" => self::ENTITY_FIELD_INT,
			"ts" => self::ENTITY_FIELD_CURRENT_TIMESTAMP
        );
    }	
}
?>