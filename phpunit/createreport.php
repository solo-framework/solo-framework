<?php 
/**
 * Скрипт для создания отчета о прохождении юнит тестов с использованием PHPUnit
 * 
 * report.xml - xml лог, созданный PHPUnit при использовании директивы --log-xml report.xml
 * phpunitreport.xsl - XSL документ для отображения лога
 * 
 * @version $Id: createreport.php 186 2008-05-05 07:50:51Z afi $
 * @author Andrey Filippov
 */

$xmlFile = "report.xml";
$xslFile = "report.xsl";

if (!file_exists($xmlFile))
	die("XML report not exists\n");

if (filesize($xmlFile) == 0)
	die("report.xml is empty!\n");

$xml = new DOMDocument;
$xml->load($xmlFile);

$xsl = new DOMDocument;
$xsl->load($xslFile);

$proc = new XSLTProcessor;
$proc->importStyleSheet($xsl);

$proc->transformToURI($xml, 'report.html');
echo "\nreport.html was generated\n\n";
?>