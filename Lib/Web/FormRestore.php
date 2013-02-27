<?php
/**
 * Класс для восстановления данных формы.
 * Требует для работы jQuery
 * Скрипт десериализации http://www.reach1to1.com/sandbox/jquery/testform.html
 *
 * @version $Id: FormRestore.php 9 2010-01-19 11:00:28Z afi $
 * @author Andrey Filippov
 *
 * @example
 * CLIENT html code:
 *
 * <html>
 * <script language="javascript" type="text/javascript" src="jquery.js" />
 *
 * <?php
 * 	echo FormRestore::restore("my_form_id");
 * ?>
 *
 * <form id='my_form_id'>
 * 		<input type='text' name='age' />
 * 		<input type="checkbox" name="value" value="42"/>
 * 		<input type="submit" value="check!"/>
 * </form>
 * </html>
 *
 * SERVER php code:
 * <?php
 * 	$age = $_POST['age'];
 * 	if (!is_int($age))
 * 	{
 * 		FormRestore::saveData();
 * 		// redirect to previous page here!
 * 	}
 * ?>
 */

namespace Solo\Lib\Web;

class FormRestore
{

	/**
	 * Признак того, что javascript, восстанавливающий данные формы
	 * был вставлен в страницу
	 *
	 * @var boolean
	 */
	private static $isScriptInserted = false;

	/**
	 * Имя переменной в сессии, которая хранит данные формы
	 *
	 * @var string
	 */
	public static $sessionName = "__formrestore__";

	/**
	 * Нельзя создавать экземпляры
	 */
	private function __construct()
	{}

	/**
	 * Проверка существования сессии
	 *
	 * @throws Exception
	 * @return void
	 */
	public static function checkSessionStarted()
	{
		if (!isset($_SESSION))
			throw new \Exception("FormRestore: session was not started");
	}

	/**
	 * Добавляем данные формы
	 *
	 * @param string $formId Значение атрибута id формы
	 * @return void
	 */
	public static function saveData($formId)
	{
		self::checkSessionStarted();

		$httpMethod = strtoupper($_SERVER["REQUEST_METHOD"]);

		// удаляем все записи
		unset($_SESSION[self::$sessionName]);
		$data = null;

		// и добавляем данные формы, упакованные в JSON
		if ("POST" == $httpMethod)
			$data = json_encode($_POST);

		if ("GET" == $httpMethod)
			$data = json_encode($_GET);

		$_SESSION[self::$sessionName][$formId] = $data;
	}

	/**
	 * Возвращает данные для всех форм
	 *
	 * @return mixed
	 */
	public static function get()
	{
		self::checkSessionStarted();

		$res = array();
		if (isset($_SESSION[self::$sessionName]))
		{
			$res = $_SESSION[self::$sessionName];
			unset($_SESSION[self::$sessionName]);
		}

		return $res;
	}

	/**
	 * Восстановление форм на странице
	 * 1. Возвращает скрипт для десериализации данных
	 * 2. Генерирует и возвращает данные для восстановления всех форм
	 *
	 * @return string|void
	 */
	public static function restore()
	{
		$dataForms = self::get();

		if ($dataForms != null)
		{
			// вставляем скрипт десериализации
			$script = self::insertScript();

			foreach ($dataForms as $idForm => $dataForm)
			{
				$script .= "
					<script language=\"javascript\" type=\"text/javascript\">
					$().ready(function()
					{
						$('#{$idForm}').populate({$dataForm});
					});
					</script>
				";
				return $script;
			}
		}
	}


	/**
	 * Возвращает javascript десериализации
	 *
	 * @return void
	 */
	public static function insertScript()
	{
		if (self::$isScriptInserted)
			return null;

$script = <<<EOT
<script language="javascript" type="text/javascript">
jQuery.fn.populate=function(g,h){function parseJSON(a,b){b=b||'';if(a==undefined){}else if(a.constructor==Object){for(var c in a){var d=b+(b==''?c:'['+c+']');parseJSON(a[c],d)}}else if(a.constructor==Array){for(var i=0;i<a.length;i++){var e=h.useIndices?i:'';e=h.phpNaming?'['+e+']':e;var d=b+e;parseJSON(a[i],d)}}else{if(k[b]==undefined){k[b]=a}else if(k[b].constructor!=Array){k[b]=[k[b],a]}else{k[b].push(a)}}};function debug(a){if(window.console&&console.log){console.log(a)}}function getElementName(a){if(!h.phpNaming){a=a.replace(/\[\]$/,'')}return a}function populateElement(a,b,c){var d=h.identifier=='id'?'#'+b:'['+h.identifier+'="'+b+'"]';var e=jQuery(d,a);c=c.toString();c=c=='null'?'':c;e.html(c)}function populateFormElement(a,b,c){var b=getElementName(b);var d=a[b];if(d==undefined){d=jQuery('#'+b,a);if(d){d.html(c);return true}if(h.debug){debug('No such element as '+b)}return false}if(h.debug){_populate.elements.push(d)}elements=d.type==undefined&&d.length?d:[d];for(var e=0;e<elements.length;e++){var d=elements[e];if(!d||typeof d=='undefined'||typeof d=='function'){continue}switch(d.type||d.tagName){case'radio':d.checked=(d.value!=''&&c.toString()==d.value);case'checkbox':var f=c.constructor==Array?c:[c];for(var j=0;j<f.length;j++){d.checked|=d.value==f[j]}break;case'select-multiple':var f=c.constructor==Array?c:[c];for(var i=0;i<d.options.length;i++){for(var j=0;j<f.length;j++){d.options[i].selected|=d.options[i].value==f[j]}}break;case'select':case'select-one':d.value=c.toString()||c;break;case'text':case'button':case'textarea':case'submit':default:c=c==null?'':c;d.value=c}}}if(g===undefined){return this};var h=jQuery.extend({phpNaming:true,phpIndices:false,resetForm:true,identifier:'id',debug:false},h);if(h.phpIndices){h.phpNaming=true}var k=[];parseJSON(g);if(h.debug){_populate={arr:k,obj:g,elements:[]}}this.each(function(){var a=this.tagName.toLowerCase();var b=a=='form'?populateFormElement:populateElement;if(a=='form'&&h.resetForm){this.reset()}for(var i in k){b(this,i,k[i])}});return this};
</script>
EOT;
		self::$isScriptInserted = true;
		return $script;
	}
}
?>
