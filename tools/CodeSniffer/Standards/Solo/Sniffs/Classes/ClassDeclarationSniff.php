<?php
/**
 * Проверка оформления тела класса и интерфейса
 * 
 * правильно:
 * class Test
 * {
 * 
 * }
 * 
 * @version $Id: ProfileManager.php 29 2008-01-24 13:04:43Z afi $
 * @author Andrey Filippov
 */
class Solo_Sniffs_Classes_ClassDeclarationSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_CLASS,
                T_INTERFACE,
               );

    }
    
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
 
        if (isset($tokens[$stackPtr]['scope_opener']) === false) {
            $error  = 'Possible parse error: ';
            $error .= $tokens[$stackPtr]['content'];
            $error .= ' missing opening or closing brace';
            $phpcsFile->addWarning($error, $stackPtr);
            return;
        }

        $curlyBrace  = $tokens[$stackPtr]['scope_opener'];
        $lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($curlyBrace - 1), $stackPtr, true);
        $classLine   = $tokens[$lastContent]['line'];
        $braceLine   = $tokens[$curlyBrace]['line'];
        if ($braceLine === $classLine) {
            $error  = 'Opening brace of a ';
            $error .= $tokens[$stackPtr]['content'];
            $error .= ' must be on the line after the definition';
            $phpcsFile->addError($error, $curlyBrace);
            return;
        } else if ($braceLine > ($classLine + 1)) {
            $difference  = ($braceLine - $classLine - 1);
            $difference .= ($difference === 1) ? ' line' : ' lines';
            $error       = 'Opening brace of a ';
            $error      .= $tokens[$stackPtr]['content'];
            $error      .= ' must be on the line following the ';
            $error      .= $tokens[$stackPtr]['content'];
            $error      .= ' declaration; found '.$difference;
            $phpcsFile->addError($error, $curlyBrace);
            return;
        }

    }
}
?>
