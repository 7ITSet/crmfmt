<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
$sql=new sql();

$url=get('url');
$parent=get('parent');
$parent=$parent?$parent:0;
$id=get('id');

$q='SELECT `id` FROM `formetoo_main`.`menu` WHERE `url`=\''.$url.'\' AND `parent`='.$parent.''.($id?' AND `id`!='.$id:'').' LIMIT 1;';
if($res=$sql->query($q))
	echo 'false';
else
	echo 'true';
?>