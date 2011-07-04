ALTER TABLE `testmigration`.`actor`     CHANGE `first_name` `first_name` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `testmigration`.`address`     ADD COLUMN `new_fiels` INT(10) NULL AFTER `last_update`;