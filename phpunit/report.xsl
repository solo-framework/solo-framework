<?xml version='1.0' encoding='UTF-8'?>
<!-- designed by andrey filippov --> 
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" exclude-result-prefixes="fo">
<xsl:output method="html" encoding="UTF-8" indent="yes"/>

<xsl:template match="/">
	<html>
	<head>
	<style rel="stylesheet" type="text/css" media="all" >
	<![CDATA[
	
		body
		{
			font-family: Arial;
		}
		
		.errorTestSuite
		{
			background-color: #FF0C24;
			font-family: Arial;
			font-weight: bold;
			font-size: 25px;
			color: White;
			height: 40px;
			padding: 5px 5px 5px 5px ;
		}
		.okTestSuite
		{
			background-color: #2EFF23;
			font-family: Arial;
			font-weight: bold;
			font-size: 25px;
			color: White;	
			height: 40px;
			padding: 5px 5px 5px 5px ;	
		}
		.failTr
		{
			background-color: #FF0C24;
			font-family: Arial;	
			font-weight: bold;		
			font-size: 16px;
			color: White;
	
		}
		.errorTr
		{
			background-color: #FF9523;
			font-family: Arial;			
			font-size: 12px;
			color: black;			
		}
		.commonTr
		{
			background-color: white;
			font-family: Arial;			
			font-size: 12px;
			color: black;
		}
		.tableHeader
		{
			font-family: Arial;
			font-weight: bold;
			font-size: 14px;
			background-color: #EEEFE3;
		}
		.testName
		{
			font-family: Arial;
			font-size: 12px;
			font-weight: bold;			
		}
	
		]]>
	</style>
	
	</head>
		<body>
			<h1>Результаты unit-тестов</h1>
			<h2>
				<xsl:call-template name="MainTitle"/>
			</h2>
			<strong>Общие сведения:</strong>
			<table width="100%" cellpadding="5" cellspacing="0" border="1">
				<tbody>
					<tr>						
						<td>
							<b>Общее число тестов: <xsl:value-of select="/testsuites/testsuite/@tests"/></b>
						</td>
						<td>
							<b>Провалено тестов: <xsl:value-of select="/testsuites/testsuite/@failures"/></b>
						</td>
						<td>
							<b>Общее число ошибок: <xsl:value-of select="/testsuites/testsuite/@errors"/></b>
						</td>
						<td>
							<b>Общее время выполнения: <xsl:value-of select="/testsuites/testsuite/@time"/></b>
						</td>
					</tr>
					
				</tbody>
			</table>
			<br />
			<br />

			<xsl:if test="count(testsuites/testsuite/testsuite) > 0">
				<xsl:call-template name="ShowSuite">	
					<xsl:with-param name="nodeset" select="testsuites/testsuite/testsuite" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="count(testsuites/testsuite/testsuite) = 0">
				<xsl:call-template name="ShowSuite">	
					<xsl:with-param name="nodeset" select="//testsuite" />
				</xsl:call-template>						
			</xsl:if>
		</body>
	</html>
</xsl:template>

<xsl:template name="MainTitle">
	<xsl:value-of select="testsuites/testsuite/@name"/>
</xsl:template>


<xsl:template name="br-replace">
    <xsl:param name="string"/>
    <xsl:choose>
        <xsl:when test="contains($string,'&#10;')">
            <xsl:value-of select="substring-before($string,'&#10;')"/>
            <br/>
            <xsl:call-template name="br-replace">
                <xsl:with-param name="string" select="substring-after($string,'&#10;')"/>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$string"/>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>



<xsl:template name="ShowSuite">
	<xsl:param name="nodeset" />

	<xsl:for-each select="$nodeset">	 
		<div class="okTestSuite">
		<xsl:if test="@failures > 0">
			<xsl:attribute name="class">errorTestSuite</xsl:attribute>
		</xsl:if>
		Набор тестов
		<xsl:value-of select="@name" />
		</div>
			<table width="100%" cellpadding="5" cellspacing="0" border="1">
				<tbody>
					<tr>						
						<td>
							<b>Число тестов: <xsl:value-of select="@tests"/></b>
						</td>
						<td>
							<b>Провалено тестов: <xsl:value-of select="@failures"/></b>
						</td>
						<td>
							<b>Ошибок: <xsl:value-of select="@errors"/></b>
						</td>
						<td>
							<b>Время выполнения: <xsl:value-of select="@time"/></b>
						</td>
						<td>
							<b>Файл: <xsl:value-of select="@file"/></b>
						</td>
					</tr>
					
				</tbody>
			</table>

			<br></br>
			<table width="100%" cellpadding="3" cellspacing="0" border="1">
				<tr class="tableHeader">
					<td>Имя теста</td>
					<td>Класс теста</td>
					<td>Номер строки</td>
					<td>Время</td>
					<td>Причина провала</td>
					<td>Ошибка</td>
				</tr>
				<xsl:for-each select="testcase">
				<tr>
				<xsl:if test="failure/text() != ''">
					<xsl:attribute name="class">failTr</xsl:attribute>
				</xsl:if>
				<xsl:if test="error/text() != ''">
					<xsl:attribute name="class">errorTr</xsl:attribute>
				</xsl:if>
					<td class="testName"><xsl:value-of select="@name"></xsl:value-of></td>
					<td><xsl:value-of select="@class" /></td>
					<td><xsl:value-of select="@line" /></td>
					<td><xsl:value-of select="@time" /></td>
					<td class="commonTr">
						<xsl:call-template name="br-replace">
							<xsl:with-param name="string" select="failure/text()"/>							
						</xsl:call-template>						
					</td>
					<td><xsl:value-of select="error/@type" /> : <xsl:value-of select="error/text()" /></td>
				</tr>
				</xsl:for-each>
			</table>
			<br/><br/>
			
	</xsl:for-each>
</xsl:template>

</xsl:stylesheet><!-- Stylus Studio meta-information - (c) 2004-2006. Progress Software Corporation. All rights reserved.
<metaInformation>
<scenarios ><scenario default="yes" name="onereport" userelativepaths="yes" externalpreview="yes" url="onereport.xml" htmlbaseurl="" outputurl="" processortype="internal" useresolver="yes" profilemode="0" profiledepth="" profilelength="" urlprofilexml="" commandline="" additionalpath="" additionalclasspath="" postprocessortype="none" postprocesscommandline="" postprocessadditionalpath="" postprocessgeneratedext="" validateoutput="no" validator="custom" customvalidator=""/><scenario default="no" name="report" userelativepaths="yes" externalpreview="yes" url="report.xml" htmlbaseurl="" outputurl="" processortype="internal" useresolver="yes" profilemode="0" profiledepth="" profilelength="" urlprofilexml="" commandline="" additionalpath="" additionalclasspath="" postprocessortype="none" postprocesscommandline="" postprocessadditionalpath="" postprocessgeneratedext="" validateoutput="no" validator="internal" customvalidator=""/></scenarios><MapperMetaTag><MapperInfo srcSchemaPathIsRelative="yes" srcSchemaInterpretAsXML="no" destSchemaPath="" destSchemaRoot="" destSchemaPathIsRelative="yes" destSchemaInterpretAsXML="no" ><SourceSchema srcSchemaPath="log.xml" srcSchemaRoot="testsuites" AssociatedInstance="" loaderFunction="document" loaderFunctionUsesURI="no"/></MapperInfo><MapperBlockPosition><template match="/"></template></MapperBlockPosition><TemplateContext></TemplateContext><MapperFilter side="source"></MapperFilter></MapperMetaTag>
</metaInformation>
-->