<?php
/**
 * Тег для использования восстановления данных в форме после
 * постбека (если форма оказалась невалидной)
 * @version $Id: function.formrestore.php 606 2010-05-13 14:34:36Z anton $
 * @author Andrey Filippov
 * @example {formrestore}
 */
function smarty_function_formrestore($params, &$smarty)
{
	return FormRestore::restore();
}
?>