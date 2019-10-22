<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
require_once('../../functions/classes/info.php');
$sql=new sql();
$info=new info();
global $e;

$data['m_services_name']=array(1,null,280);
$data['m_services_unit']=array(1,null,3,null,1);
$data['m_services_price_general']=array(1,null,18);
$data['m_services_contragents_id']=array(1,null,null,10,1);

array_walk($data,'check',true);

if(!$e){
	$data['m_services_id']=get_id('m_services');
	$data['m_services_show_site']=0;
	$data['m_services_show_price']=0;
	$data['m_services_date']=$data['m_services_update']=dt();
	$data['m_services_categories_id[]']='1326613691';
	$data['m_services_price_general']=(float)str_replace(array(' ',','),array('','.'),$data['m_services_price_general']);
	$data['m_services_price_general_w']=$data['m_services_price_general']*.7;
	
	$q='INSERT `formetoo_main`.`m_services` SET
		`m_services_id`='.$data['m_services_id'].',
		`m_services_contragents_id`='.$data['m_services_contragents_id'].',
		`m_services_name`=\''.$data['m_services_name'].'\',
		`m_services_unit`='.$data['m_services_unit'].',
		`m_services_price_general`='.$data['m_services_price_general'].',
		`m_services_price_general_w`='.$data['m_services_price_general_w'].',
		`m_services_categories_id`=\''.$data['m_services_categories_id[]'].'\',
		`m_services_show_site`='.$data['m_services_show_site'].',
		`m_services_show_price`='.$data['m_services_show_price'].',
		`m_services_date`=\''.$data['m_services_date'].'\',
		`m_services_update`=\''.$data['m_services_update'].'\';';
	if($sql->query($q))
		echo $data['m_services_id'];
	else
		echo 'false';
}
//else print_r($e);
unset($sql);
?>