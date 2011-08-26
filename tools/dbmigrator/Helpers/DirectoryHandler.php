<?php
/**
 * Класс реализует работу с директориями
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  DirectoryHandler.php 27.05.11 17:37 evkur
 * @link     nolink
 */

class DirectoryHandler
{
    public static function clean($path)
    {
        $iterator = new RecursiveIteratorIterator
        (
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $path)
        {
            /* @var DirectoryIterator $path */
            if ($path->getFilename() == "." || $path->getFilename() == "..")
                continue;
            else if ($path->isDir())
                rmdir($path->getPathname());
            else
                unlink($path->getPathname());
        }
    }

    public static function copy($startDir, $endDir)
    {
        if (is_dir($startDir))
        {
            if (!is_dir($endDir))
                mkdir($endDir);

            for ($source = new DirectoryIterator($startDir); $source->valid(); $source->next())
            {
                if ($source->getFilename() == '.' || $source->getFilename() == '..')
                    continue;
                else
                {
                    if ($source->isDir())
                    {
                        mkdir($endDir . DIRECTORY_SEPARATOR . $source->getFilename());
                        self::copy
                        (
                            $startDir . DIRECTORY_SEPARATOR . $source->getFilename(),
                            $endDir . DIRECTORY_SEPARATOR . $source->getFilename()
                        );
                    }
                    else
                    {
                        $content = @file_get_contents($startDir . DIRECTORY_SEPARATOR . $source->getFilename());
                        $openedfile = fopen($endDir . DIRECTORY_SEPARATOR . $source->getFilename(), "w");
                        fwrite($openedfile, $content);
                        fclose($openedfile);
                    }
                }
            }

        }
    }

    public static function delete($target)
    {
        if (is_dir($target))
        {
            chmod($target, 0777);
            for ($source = new DirectoryIterator($target); $source->valid(); $source->next())
            {
                if ($source->getFilename() == '.' || $source->getFilename() == '..')
                    continue;
                else
                {
                    if($source->isDir())
                    {
                        self::delete($target.DIRECTORY_SEPARATOR.$source->getFilename());
                        if (is_dir( $target.DIRECTORY_SEPARATOR.$source->getFilename()))
                            rmdir($target.DIRECTORY_SEPARATOR.$source->getFilename());
                    }
                    else
                    {
                        unlink($target.DIRECTORY_SEPARATOR.$source->getFilename());
                    }
                }
            }
            rmdir($target);
        }
    }

    public static function move($startDir, $endDir)
    {
        self::copy($startDir, $endDir);
        self::delete($startDir);
    }

	public static function dirList($target, $pattern)
	{
		$list = array();
		if (is_dir($target))
        {
            for ($source = new DirectoryIterator($target); $source->valid(); $source->next())
            {
                if ($source->isDot())
                    continue;

				if($source->isDir() && preg_match($pattern, $source->getFilename()))
				{
					$list[] = $source->getFilename();
				}
            }
        }
		sort($list, SORT_STRING | SORT_ASC);
		return $list;
	}

}
