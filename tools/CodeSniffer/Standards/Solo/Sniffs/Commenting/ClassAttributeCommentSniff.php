<?php
/**
 * Комментарии к переменным класса обязательны.
 * 
 * В этом классе проверяем только наличие комментариев у переменных и констант.
 * Формат комментариев здесь не проверяется.
 * 
 * @version $Id: ProfileManager.php 29 2008-01-24 13:04:43Z afi $
 * @author Andrey Filippov
 */
class Solo_Sniffs_Commenting_ClassAttributeCommentSniff implements PHP_CodeSniffer_Sniff
{

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register()
	{
		return array(T_VARIABLE, T_CONST);
	
	}

	public function process(PHP_CodeSniffer_File $phpcsFile,$stackPtr)
	{
		// Не используем, т.к. написан более общий класс
		//return ;
		

	
		$tokens = $phpcsFile->getTokens();
		//$phpcsFile->addError(print_r( $tokens[$stackPtr],1 ), $stackPtr);
		
		// найдем все константы
		if ($tokens[$stackPtr]["code"] == T_CONST)
		{			
			// найдем комментарии, предшествующие константе
			$commentPos = $phpcsFile->findPrevious(T_DOC_COMMENT, $stackPtr);
			
			// строка, где находится комментарий
			$commentLine = $tokens[$commentPos]["line"];
			
			// строка, где находится константа
			$constLine = $tokens[$stackPtr]["line"];
			
			// проверяем, следует ли за комментарием переменная
			if($commentLine !== ($constLine - 1))
				$phpcsFile->addError("Comment for const {$tokens[$stackPtr+2]["content"]} does not exist", $stackPtr);
		}
			

		// Находим приватные, публичные, защищенные переменные
		// ищем у них комментарии
		// если их нет, то выводим ошибку
		$search = array(T_PUBLIC, T_PRIVATE, T_PROTECTED);
		
		// 2 токена назад от имени переменной будут его модификатором
		if (in_array($tokens[$stackPtr - 2]["code"], $search))
		{			
			// строка, где находится переменная 
			$varLine = $tokens[$stackPtr - 2]["line"];
			
//		$phpcsFile->addError("Comment for var {$tokens[$stackPtr]["content"]}", 1);
			
			// найдем комментарии, предшествующие переменной
			// но т.к. они находятся все, до начала файла (баг??)
			// то, оставляем только комментарий, находящийся на предыдущей линии
			$commentPos = $phpcsFile->findPrevious(T_DOC_COMMENT, $stackPtr);
			//if($commentPos)
			//{
				// строка, где находится комментарий
				$commentLine = $tokens[$commentPos]["line"];
				
				// проверяем, следует ли за комментарием переменная
				if($commentLine + 1 !== $varLine)
					$phpcsFile->addError("Comment for var {$tokens[$stackPtr]["content"]} does not exist", $stackPtr);
			//}
			//else
			//{
				// правильно ли????
			//	$phpcsFile->addError("!!Не найден комментарий для атрибута {$tokens[$stackPtr]["content"]}", $stackPtr);
			//}
		}
	}
}

?>
