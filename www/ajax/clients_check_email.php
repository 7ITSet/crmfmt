<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
$sql=new sql();

$m_users_login=get('m_users_login');
$m_users_id=get('m_users_id');
$q='SELECT * FROM `formetoo_main`.`m_users` WHERE `m_users_login`=\''.$m_users_login.'\''.($m_users_id?' AND `m_users_id`!='.$m_users_id:'').' LIMIT 1;';
if($res=$sql->query($q))
	echo 'false';
else
	echo 'true';
?>