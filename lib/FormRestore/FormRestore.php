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
			throw new Exception("FormRestore: session was not started");
	}
	
	/**
	 * Добавляем данные формы
	 * 
	 * @param string $formId Значение атрибута id формы
	 * @return void
	 */
	public static function saveData()
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
		
		$_SESSION[self::$sessionName] = $data;
	}
	
	/**
	 * Возвращает данные для формы
	 * 
	 * @return mixed
	 */
	public static function get()
	{
		self::checkSessionStarted();
		
		$res = null;
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
	 * 2. Генерирует и возвращает данные для восстановления указанных форм
	 * 
	 * @param string $formId Значение атрибута id формы, для 
	 * 		которой происходит восстановление
	 * 
	 * @return strinf
	 */
	public static function restore($formId)
	{
		$data = self::get();
		if ($data != null)
		{			
			// вставляем скрипт десериализации
			$script = self::insertScript();
			
			$script .= "
				<script language=\"javascript\" type=\"text/javascript\">
				$().ready(function() 
				{
					$('#{$formId}').deserialize({$data}, {isPHPnaming:true});
				});
				</script>
			";
			return $script;
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
$.fn.deserialize=function(d,_2){
var _3=d;
me=this;
if(d===undefined){
return me;
}
_2=$.extend({isPHPnaming:false,overwrite:true},_2);
if(d.constructor==Array){
_3={};
for(var i=0;i<d.length;i++){
if(typeof _3[d[i].name]!="undefined"){
if(_3[d[i].name].constructor!=Array){
_3[d[i].name]=[_3[d[i].name],d[i].value];
}else{
_3[d[i].name].push(d[i].value);
}
}else{
_3[d[i].name]=d[i].value;
}
}
}
$("input,select,textarea",me).each(function(){
var p=this.name;
var v=[];
if(_2.isPHPnaming){
p=p.replace(/\[\]$/,"");
}
if(p&&_3[p]!=undefined){
v=_3[p].constructor==Array?_3[p]:[_3[p]];
}
if(_2.overwrite===true||_3[p]){
switch(this.type||this.tagName.toLowerCase()){
case "radio":
case "checkbox":
this.checked=false;
for(var i=0;i<v.length;i++){
this.checked|=(this.value!=""&&v[i]==this.value);
}
break;
case "select-multiple"||"select":
for(i=0;i<this.options.length;i++){
this.options[i].selected=false;
for(var j=0;j<v.length;j++){
this.options[i].selected|=(this.options[i].value!=""&&this.options[i].value==v[j]);
}
}
break;
case "button":
case "submit":
this.value=v.length>0?v.join(","):this.value;
break;
default:
this.value=v.join(",");
}
}
});
return me;
};
</script>
EOT;
		self::$isScriptInserted = true;
		return $script;
	}
}
?>