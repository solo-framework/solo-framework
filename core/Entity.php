<?php
/**
 * Базовый класс для всех сущностей
 * 
 * PHP version 5
 * 
 * @category Framework
 * @package  Core
 * @author   Andrey Filippov <afi@i-loto.ru>
 * @license  %license% name
 * @version  SVN: $Id: Entity.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

abstract class Entity 
{	
	/**
	 * Содержит наименование таблицы в БД, где хранятся сущности этого типа. Не является атрибутом сущности
	 * 
	 * @var string
	 */
	public $entityTable = null;
	
	/**
	 * У каждой сущности должен быть идентификатор. Является атрибутом сущности и д.б. объявлен в getFields()
	 * 
	 * @var integer
	 */
	public $id = null;
	
	/**
	 * Первичный ключ, обычно соответствует атрибуту "id".  Не является атрибутом сущности. 
	 * 
	 * @var string
	 */
	public $primaryKey = null;
	
	/** Типы данных, соотсветсвующий типам MYSQL */
	
	/**
	 * Тип integer
	 * 
	 * @var string
	 */
	const ENTITY_FIELD_INT = "ENTITY_FIELD_INT";
	
	/**
	 * Тип char, varchar, text и т.д.
	 * 
	 * @var string
	 */
	const ENTITY_FIELD_STRING = "ENTITY_FIELD_STRING";
	
	/**
	 * тип DateTime
	 * 
	 * @var string
	 */
	const ENTITY_FIELD_DATETIME = "ENTITY_FIELD_DATETIME";
	
	/**
	 * Тип TimeStamp
	 * 
	 * @var string
	 */
	const ENTITY_FIELD_TIMESTAMP = "ENTITY_FIELD_TIMESTAMP";

	/**
	 * Тип Time
	 * 
	 * @var string
	 */
	const ENTITY_FIELD_TIME = "ENTITY_FIELD_TIME";
	
	/**
	 * 	Выставляется для поля, значение которого сервер БД выставляет автоматически
	 *  Например, для полей с дефолтным значением CURRENT_TIMESTAMP или on update CURRENT_TIMESTAMP
	 *  При генерации SQL запроса вставки или обновления это поле не будет учтено
	*/
	const ENTITY_FIELD_CURRENT_TIMESTAMP = "ENTITY_FIELD_CURRENT_TIMESTAMP";
	
	/**
	* Returns ID's value
	* 
	* @return int
	*/
	public function getId()
	{
		$field = $this->primaryKey;
		return isset($this->$field) ? $this->$field : null;
	}
	
	/**
	* Установка идентификатора сущности
	* 
	* @param int $id идентификатор сущности
	* 
	* @return void
	*/
	public function setId($id)
	{
		if (!is_int($id))
			throw new Exception("Entity id must be integer. You trying set '{$id}'");
		$field = $this->primaryKey;
		$this->$field = $id;	
	}
	
	/**
	 * Represents hash like this:
	 * 
	 * @return  array ('id' => ENTITY_FIELD_INT, 'name'=>ENTITY_FIELD_STRING)
	 * */
	public abstract function getFields();
	
	/**
	* Возвращает атрибуты сущности
	* 
	* @return array
	*/
	public function getProperties()
	{
		return get_object_vars($this);
	}
	
	/**
	 * Возвращает тип поля по его имени 
	 * Если поле не найдено, возвращает null
	 * 
	 * @param string $field Имя атрибута сущности
	 * 
	 * @return string
	 */
	public function getFieldType($field)
	{
		$res = $this->getFields();
		
		if (array_key_exists($field, $res))
			return $res[$field];
		else
			return null;
	}
	
	/**
	 * Самотестирование сущности
	 * Сравнивает названия полей сущности с ее схемой
	 * 
	 * //TODO: Реализовать тестирование без хардкода 2-х полей
	 * 
	 * @return void
	 */
	public function selfTest()
	{
		$diff = array_diff_key( get_object_vars($this), $this->getFields() );
				
		unset($diff['entityTable']);
		unset($diff['primaryKey']);
				
		if (count($diff) > 0)
		{
			$list = join(", ", array_keys($diff));
			$mess = "Entity Self Test " . "'" . get_class($this) . "'. Unknown fields : {$list}";
			throw new Exception($mess);
		}
	}
}
?>