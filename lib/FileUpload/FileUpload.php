<?php
/**
 * Реализует функционал загрузки файла на сервер
 *
 * PHP version 5
 * 
 * @example
 * 		if (FileUpload::fileIsLoaded("myfile"))
		{		
			try
			{
				$file = new FileUpload("myfile");
								
				// проверяем, соответствует ли файл нашим требования
				// если нет - генерируется исключение
				$file->filterExtensionInList("exe,jpg", "Разрешенное расширение exe,jpg")
					->filterExtensionNotInList("php,gif", "Запрещены расширения!")
					->filterSizeMustBeLessThen(50 * 1024, "Размер файла д.б. меньше, чем 50кб")
					->filterFileNameRegex("/bulova[.*]?/i", "Имя файла не соответствует шаблону")
					->filterFileNameEqual("bulova.jpg", "Неправильное имя файла");
			
				// перемещаем файл в каталог 1/ с новым именем и новым расширением
				$file->moveAs("1/", "test", "png");
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
		}	
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

class FileUpload
{
	/**
	 * Наименование поля в форме
	 *
	 * @var string
	 */
	private $fieldName = null;

	/**
	 * Массив данных, полученных из браузера
	 *
	 * @var array
	 */
	private $fileData = null;

	/**
	 * Флаг ошибки
	 *
	 * @var boolean
	 */
	public $isError = false;

	/**
	 * Размер файла
	 *
	 * @var integer
	 */
	public $size = 0;

	/**
	 * MIME тип файла
	 *
	 * @var string
	 */
	public $type = "";

	/**
	 * Имя файла
	 *
	 * @var string
	 */
	public $name = "";

	/**
	 * Имя файла без разрешения
	 *
	 * @var string
	 */
	public $baseFileName = "";
	
	
	/**
	 * Код ошибки
	 *
	 * @var integer
	 */
	public $errorCode = UPLOAD_ERR_OK;

	/**
	 * Расширение файла
	 *
	 * @var string
	 */
	public  $extension = "";

	/**
	 * Временное имя загруженного файла
	 *
	 * @var string
	 */
	private $tmpName = null;

	/**
	 * Путь к файлу после его перемещения
	 *
	 * @var string
	 */
	private $newPath = null;

	/**
	 * Содержимое файла
	 *
	 * @var string
	 */
	private $content = null;


	/**
	 * Конструктор
	 *
	 * @param string $fieldName  имя поля в форме
	 *
	 * @return void
	 */
	public function __construct($fieldName)
	{
		$this->fieldName = $fieldName;
		if (isset($_FILES[$this->fieldName]))
			$this->fileData = $_FILES[$this->fieldName];
		else
			throw new Exception("Undefined field name '{$fieldName}'");

		$this->errorCode = $this->fileData['error'];
		$this->name = $this->fileData['name'];
		$this->type = $this->fileData['type'];
		$this->size = $this->fileData['size'];
		$this->tmpName = $this->fileData['tmp_name'];
		$this->isError = (bool)$this->errorCode;

		if ($this->isError)
			throw new Exception($this->errorCode, $this->errorCode);

		if (is_uploaded_file($this->tmpName))
		{
			$info = pathinfo($this->name);
			$this->baseFileName = $info["filename"];
			$this->extension = $info["extension"];
		}
		else
		{
			throw new Exception(UPLOAD_ERR_NO_FILE, UPLOAD_ERR_NO_FILE);
		}
	}

	/**
	* Перемещение загруженного файла
	*
	* @param string $path Путь к каталогу
	*
	* @return bool
	*/
	public function move($path)
	{		
		$this->newPath = $path . $this->name;
		return move_uploaded_file($this->tmpName, $path . $this->name);
	}
	
	/**
	* Перемещение загруженного файла с новым
	* именем и/или расширением
	*
	* @param string $path Путь к каталогу
	* @param string $newName Имя файла без разрешения
	* @param string $extension разрешение файла
	*
	* @return bool
	*/
	public function moveAs($path, $newName = null, $extension = null)
	{
		if ($newName !== null)
			$this->baseFileName = $newName;
		if ($extension !== null)
			$this->extension = $extension;

		$this->name = $this->baseFileName . "." . $this->extension;
		$this->newPath = $path . $this->name;
		return move_uploaded_file($this->tmpName, $path . $this->name);
	}	

	/**
	 * Проверяет, был ли загружен файл
	 *
	 * @param string $fieldName
	 *
	 * @return boolean
	 */
	public static function fileIsLoaded($fieldName)
	{
		if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]["error"] != UPLOAD_ERR_NO_FILE )
			return true;
		else
			return false;
	}

	/**
	 * Возвращает содержимое файла
	 *
	 * @return string
	 */
	public function getContent()
	{
		$path = $this->newPath !== null ? $this->newPath : $this->tmpName;
		return file_get_contents($path);
	}

	/**
	 * Фильтр по размеру файла: должен быть меньше, чем
	 *
	 * @param int $size Размер в байтах
	 * @param string $comment Комментарий, отображаемый если фильтр не пройден
	 * @throws Exception
	 *
	 * @return FileUpload
	 */
	public function filterSizeMustBeLessThen($size, $comment)
	{
		if ($this->size > $size)
			throw new Exception($comment);
		else
			return $this;
	}

	/**
	 * Фильтр по расширению
	 *
	 * @param string $ext Расширение
	 * @param string $comment Комментарий, отображаемый если фильтр не пройден
	 * @throws Exception
	 *
	 * @return FileUpload
	 */
	public function filterExtensionIs($ext, $comment)
	{
		if ($this->extension !== $ext)
			throw new Exception($comment);
		else
			return $this;
	}

	/**
	 * Фильтр по списку разрешенных расширений(указываются через запятую, напр jpg,png,gif)
	 *
	 * @param string $extList Список расширений через запятую
	 * @param string $comment Комментарий, отображаемый если фильтр не пройден
	 * @throws Exception
	 *
	 * @return FileUpload
	 */
	public function filterExtensionInList($extList, $comment)
	{
		$extList = explode(",", $extList);

		if (!in_array($this->extension, $extList))
			throw new Exception($comment);
		else
			return $this;
	}
	
	/**
	 * Фильтр по списку запрещенных расширений(указываются через запятую, напр jpg,png,gif)
	 *
	 * @param string $extList Список расширений через запятую
	 * @param string $comment Комментарий, отображаемый если фильтр не пройден
	 * @throws Exception
	 *
	 * @return FileUpload
	 */
	public function filterExtensionNotInList($extList, $comment)
	{
		$extList = explode(",", $extList);

		if (in_array($this->extension, $extList))
			throw new Exception($comment);
		else
			return $this;
	}	


	/**
	 * Фильтр имени файла по регулярному выражению
	 *
	 * @param string $pattern регулярное выражение
	 * @param string $comment Комментарий, отображаемый если фильтр не пройден
	 * @throws Exception
	 *
	 * @return FileUpload
	 */
	public function filterFileNameRegex($pattern, $comment)
	{
		if (!preg_match($pattern, $this->name))
			throw new Exception($comment);
		else
			return $this;
	}

	/**
	 * Фильтр по точному совпадению имени
	 *
	 * @param string $expected Ожидаемое имя файла
	 * @param string $comment Комментарий, отображаемый если фильтр не пройден
	 * @throws Exception
	 *
	 * @return FileUpload
	 */
	public function filterFileNameEqual($expected, $comment)
	{
		if ($expected !== $this->name)
			throw new Exception($comment);
		else
			return $this;
	}
}
?>