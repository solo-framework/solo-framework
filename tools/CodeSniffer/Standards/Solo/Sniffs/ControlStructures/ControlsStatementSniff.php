<?php
/**
 * проверяем правильность скобок управляющих структур и циклов 
 * 
 * Для всех выражений, открывающая скобка должна быть на новой строке
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

class Solo_Sniffs_ControlStructures_ControlsStatementSniff implements PHP_CodeSniffer_Sniff
{
	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register ()
	{
		return array(
				T_TRY, 
				T_CATCH, 
				T_IF, 
				T_ELSE, 
				T_ELSEIF,
				T_FOR,
				T_FOREACH,
				T_DO,
				T_WHILE,
				T_SWITCH
			);
	}
	
		/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int                  $stackPtr  The position of the current token in the
	 * stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process (PHP_CodeSniffer_File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();
		
		// не будем использовать ELSEIF, вместо этого - ELSE IF 
		if($tokens[$stackPtr]["code"] == T_ELSEIF)
			$phpcsFile->addError("Usage of ELSEIF not allowed. Use ELSE IF instead.", $stackPtr);
	
		// линия, где находится анализируемое выражение
		$currentLine = $tokens[$stackPtr]["line"];
		
		// Найти позицию, где находится закрывающая скобка "}" предыдущего выражения
		// Она не должна находиться на одной линии с выражением
		
		$prevBrace = $phpcsFile->findPrevious(T_CLOSE_CURLY_BRACKET, $stackPtr);
		$prevBraceLine = $tokens[$prevBrace]["line"];
		
		if ($currentLine == $prevBraceLine)
			$phpcsFile->addError("'{$tokens[$stackPtr]["content"]}' must be on a new line ", $stackPtr);

		// ищем позицию, на которой находится открывающая скобка после выражения
		// Она должна быть на новой строке
	
		$nextBrace = $phpcsFile->findNext(T_OPEN_CURLY_BRACKET, $stackPtr);
		$nextBraceLine = $tokens[$nextBrace]["line"];
		
		if ($currentLine == $nextBraceLine)
			$phpcsFile->addError("After '{$tokens[$stackPtr]["content"]}' brace '{' must be on a new line ", $stackPtr);
			
		// между между выражением и скобками (напр. catch ()) должен быть пробел
		if ($tokens[$stackPtr + 1]["code"] !== T_WHITESPACE)
			$phpcsFile->addError("Expected 1 space between '{$tokens[$stackPtr]["content"]}' and '(' ", $stackPtr);

	}
}
?>