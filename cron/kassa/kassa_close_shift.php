<?
/*
/	Закрытие смены каждый день в 4-10 утра, открытие происходит с первым чеком.
*/
define ('_DSITE',1);

ini_set('display_errors',1);
require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/classes/kassa.php');

$sql=new sql;
$kassa=new kassa;

$kassa->closeShift();

?>