<?php
/**
 * Скрипт для генерации файла с классом сущности
 *
 * PHP version 5
 *
 * @category Framework
 * @package  Core
 * @author   Andrey Filippov <afi.work@gmail.com>
 * @license  %license% name
 * @version  SVN: $Id: genentity.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

//Все аргументы, переданные скрипту
$host = @$argv[1];
$dbName = @$argv[2];
$user = @$argv[3];
$password = @$argv[4];
$entityTable = @$argv[5];
$entityName = @$argv[6];

echo <<<EOT

=========================================
	Entity class generator
	Output file: your_entity_name.php
=========================================

EOT;

if (!$host || !$dbName || !$user || !$password || !$entityTable || !$entityName)
{
	echo <<<EOT

Usage: php -f genentity.php host_name database_name mysql_user mysql_password entity_table entity_name

EOT;
	exit();
}

$conn = mysql_connect($host, $user, $password);
mysql_set_charset("utf8", $conn);
mysql_selectdb($dbName);

#
# Комментарии к сущности - это комментарии к таблице в БД
#
$sql = "SHOW TABLE STATUS FROM {$dbName} WHERE Name = '{$entityTable}'";
$link = mysql_query($sql, $conn);
$tableComment = "";
$tableMetaData = mysql_fetch_array($link, MYSQL_ASSOC);
$tableComment = $tableMetaData["Comment"];

#
# Список полей и инфа по ним
#
$sql = "SHOW FULL COLUMNS FROM {$entityTable}";
$link = mysql_query($sql, $conn);

$primaryKey = null;

$fieldsMetaData = null;
$fieldList = null;

while ($item = mysql_fetch_array($link, MYSQL_ASSOC))
{
	$type = preg_replace('/\(.*\)/', "", $item["Type"]);
	$item["Type"] = recognizeType($type);

	if ($item["Key"] == "PRI")
		$primaryKey = $item["Field"];

	if ($item["Field"] !== $primaryKey)
	{
		$varType = recognizeType($type, true);

	// описания полей
$fieldsMetaData .=<<<TTT
	/**
	 * {$item["Comment"]}
	 *
	 * @var {$varType}
	 */
	public \${$item["Field"]} = null;


TTT;
	}

	// массив с полями и типами
$fieldList .=<<<FT
		\t"{$item["Field"]}" => {$item["Type"]},\n
FT;

}


function recognizeType($type, $asPHPTypes = false)
{
    switch ($type)
    {
        case "int":
        case "int unsigned":
        case "tinyint":
        case "bit":
        case "float":
            return !$asPHPTypes ? "self::ENTITY_FIELD_INT" : "integer";
            break;
        case "decimal":
            return !$asPHPTypes ? "self::ENTITY_FIELD_DECIMAL" : "decimal";
            break;
        case "char":
            return !$asPHPTypes ? "self::ENTITY_FIELD_STRING" : "string";
            break;
        case "date":
           	return !$asPHPTypes ? "self::ENTITY_FIELD_DATETIME" : "DateTime";
            break;
        case "varchar":
        case "text":
        case "tinytext":
            return !$asPHPTypes ? "self::ENTITY_FIELD_STRING" : "string";
            break;
        case "timestamp":
        	return !$asPHPTypes ? "self::ENTITY_FIELD_TIMESTAMP": "DateTime";
        case "datetime":
        case "time":
        	return !$asPHPTypes ? "self::ENTITY_FIELD_DATETIME" : "DateTime";

        default:
            throw new Exception("Undefined type '{$type}'") ;
    }
}

$template = <<<EOT
<?php
/**
 * {$tableComment}
 *
 * PHP version 5
 *
 * @category BL
 * @package  Entity
 * @author   Andrey Filippov <afi.work@gmail.com>
 * @license  %license% name
 * @version  SVN: \$Id: {$entityName}.php 9 2007-12-25 11:26:03Z afi \$
 * @link     nolink
 */

class {$entityName} extends Entity
{
	/**
	 * Содержит наименование таблицы в БД, где хранятся сущности этого типа. Не является атрибутом сущности
	 *
	 * @var string
	 */
	public \$entityTable = "{$entityTable}";

	/**
	 * Первичный ключ, обычно соответствует атрибуту "id".  Не является атрибутом сущности.
	 *
	 * @var string
	 */
	public \$primaryKey = "{$primaryKey}";

{$fieldsMetaData}

	/**
	 * Возвращает список полей сущности и их типы
	 *
	 * @return array
	 */
	public function getFields()
	{
		return array(
{$fieldList}
		);
	}
}

?>
EOT;

echo "\n{$entityName}.php was created";

file_put_contents("{$entityName}.php", $template);

?>