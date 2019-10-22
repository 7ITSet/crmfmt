<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
$sql=new sql;

$m_clients_personal_birthday_=get('m_clients_personal_birthday_');
if($m_clients_personal_birthday_){
	$date=date_create($m_clients_personal_birthday_);
	if(time()-date_format($date,'U')<441849600)
		echo 'false';
	else
		echo 'true';
}
?>