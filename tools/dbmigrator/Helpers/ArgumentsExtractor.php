<?php
/**
 * Класс занимается разбором аргументов командной строки
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  ArgumentsExtractor.php 27.05.11 17:35 evkur
 * @link     nolink
 */


class ArgumentsExtractor
{
    public static function extract($args)
    {                
        if (count($args) < 2)
            throw new Exception('Incorrect arguments. Use <help>');

        $command = $args[1];
        $params = array_slice($args, 2);
        
        return array($command, $params);
    }
}
