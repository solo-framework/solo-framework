<?php
/**
 * Запрещаем отступы в виде пробелов. Только TAB
 * 
 * @version $Id: ProfileManager.php 29 2008-01-24 13:04:43Z afi $
 * @author Andrey Filippov
 */
class Solo_Sniffs_Space_DisallowSpaceIndentSniff implements PHP_CodeSniffer_Sniff 
{
	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() 
	{
		return array (T_WHITESPACE );
	
	}
	
	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile All the tokens found in the document.
	 * @param int                  $stackPtr  The position of the current token in
	 *                                        the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens ();
		
		// Make sure this is whitespace used for indentation.
		$line = $tokens [$stackPtr] ['line'];
		if ($stackPtr > 0 && $tokens [($stackPtr - 1)] ['line'] === $line) 
		{
			return;
		}

		// отступ только табами
		if (strpos ( $tokens [$stackPtr] ['content'], " " ) !== false) 
		{
			$error = 'Tabs must be used to indent lines; Spaces are not allowed';
			$phpcsFile->addError ( $error, $stackPtr );
		}
	}
}
?>