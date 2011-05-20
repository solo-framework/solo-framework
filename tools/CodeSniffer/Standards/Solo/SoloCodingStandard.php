<?php
/**
 * Solo coding standard
 * 
 * @version $Id: ProfileManager.php 29 2008-01-24 13:04:43Z afi $
 * @author Andrey Filippov
 */


class PHP_CodeSniffer_Standards_Solo_SoloCodingStandard extends PHP_CodeSniffer_Standards_CodingStandard
{

	/**
	 * Return a list of external sniffs to include with this standard.
	 *
	 * The PEAR standard uses some generic sniffs.
	 *
	 * @return array
	 */
	public function getIncludedSniffs ()
	{		
		return array(
		
			// не дожно быть функций вне классов
			'Squiz/Sniffs/Functions/GlobalFunctionSniff.php',
		
			// проверим, нет ли одинаковых параметров у метода
			'Squiz/Sniffs/Functions/FunctionDuplicateArgumentSniff.php',
			
			// использовать только полные теги открывающие теги PHP
			'Generic/Sniffs/PHP/DisallowShortOpenTagSniff.php',
		
			// имена классов в нотации Camel
			'Squiz/Sniffs/Classes/ValidClassNameSniff.php',
		
			// Имя класса должно быть одинаковым с именем файла
			'Squiz/Sniffs/Classes/ClassFileNameSniff.php',
		
			// для переменных класса и методов всегду нужно указывать 
			// модификатор доступа
			'Squiz/Sniffs/Scope/MemberVarScopeSniff.php',
			'Squiz/Sniffs/Scope/MethodScopeSniff.php',
		
			// чтобы не писали $this в статических методах
			'Squiz/Sniffs/Scope/StaticThisUsageSniff.php',

			
			// пробелы между операторами
			'Squiz/Sniffs/WhiteSpace/OperatorSpacingSniff.php',		
		
			// заголовок файла с комментариями: между комментариями и определением класса
			// должна быть пустая строка
			'PEAR/Sniffs/Commenting/FileCommentSniff.php',
			
			// открывающая скобка метода должна быть на новой строке
			'Generic/Sniffs/Functions/OpeningFunctionBraceBsdAllmanSniff.php',
	
			// константы заглавными буквами
		 	'Generic/Sniffs/NamingConventions/UpperCaseConstantNameSniff.php',
			
			// блочные комментарии к переменным
			'Squiz/Sniffs/Commenting/BlockCommentSniff.php',
			
			// Неиспользуемые параметры метода
			'Generic/Sniffs/CodeAnalysis/UnusedFunctionParameterSniff.php',

			// Открывающая и закрывающая скобки в выражении должны 
			// находиться на одном вертикальном уровне
			'Squiz/Sniffs/WhiteSpace/ScopeClosingBraceSniff.php',
		
			// 2 пустые строки после функции
			// надо ли????
			//'Squiz/Sniffs/WhiteSpace/FunctionSpacingSniff.php'
			
			// оформление пробелов в выражениях цикла foreach
			'Squiz/Sniffs/ControlStructures/ForEachLoopDeclarationSniff.php',
			
			//оформление пробелов в выражениях цикла for
			'Squiz/Sniffs/ControlStructures/ForLoopDeclarationSniff.php',

			// ключевые слова управляющих структур в нижнем регистре
			'Squiz/Sniffs/ControlStructures/LowercaseDeclarationSniff.php',
		);
	}
}
?>