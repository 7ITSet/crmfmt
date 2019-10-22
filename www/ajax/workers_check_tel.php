<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
$sql=new sql();

$tel=get('tel');

$q='SELECT * FROM `formetoo_cdb`.`m_employees` WHERE `m_employees_id`IN(SELECT `m_contragents_tel_contragents_id` FROM `m_contragents_tel` WHERE `m_contragents_tel_numb`=\''.$tel.'\');';
if($res=$sql->query($q)){
	$a=array();
	foreach($res as $_res)
		$a[]=$_res['m_employees_fio'];
	echo implode(', ',$a);
}
else
	echo 'true';
?>