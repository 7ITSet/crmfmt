<?
/*

 *	Удаление пользователей с одинаковым IP с >6 различными логинами за день и без имени
 *	(боты), запуск ежедневно.

*/ 
define ('_DSITE',1);
ini_set('display_errors',1);
require_once('../functions/system.php');
require_once('../functions/ccdb.php');

$sql=new sql();

$ip_crawlers=array();
$cookies_from_ip=$sql->query('SELECT * FROM cookies;','cookies_ip');
array_multisort(array_map('count', $cookies_from_ip), SORT_DESC, $cookies_from_ip);
foreach($cookies_from_ip as $_ip=>$_c)
	if(sizeof($_c)>6&&$_ip!='192.168.1.4')
		$ip_crawlers[]='"'.$_ip.'"';
pre($ip_crawlers);

$q='SELECT `cookies_m_users_id` FROM `formetoo_main`.`cookies` WHERE `cookies_ip` IN ('.implode(',',$ip_crawlers).');';
if($r=$sql->query($q)){
	$id=array();
	foreach($r as $_r)
		$id[]=$_r['cookies_m_users_id'];
	$q='DELETE FROM `formetoo_main`.`m_users` WHERE `m_users_id` IN('.implode(',',$id).') AND `m_users_name` IS NULL;';
	pre($sql->query($q));
}
$sql->query('DELETE FROM `formetoo_main`.`cookies` WHERE `cookies_ip` IN ('.implode(',',$ip_crawlers).');');

unset($sql);
?>