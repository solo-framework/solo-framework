<?php
/**
 * Нельзя использовать global
 * 
 * PHP version 5
 * 
 * @category Coding Standard
 * @package  Sniffs
 * @author   Andrey Filippov <afi.work@gmail.com>
 * @license  %license% name
 * @version  SVN: $Id: Entity.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

class Solo_Sniffs_Php_NoGlobalSniff implements PHP_CodeSniffer_Sniff
{
	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register ()
	{
		return array(T_GLOBAL);
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

		$phpcsFile->addError("Use of the 'global' keyword is forbidden, mazafaka!", $stackPtr);

	}	
}
?>