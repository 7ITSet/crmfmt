<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
require_once('../../functions/classes/info.php');
$sql=new sql();
$info=new info();
global $e;

$data['m_products_name']=array(1,null,280);
$data['m_products_unit']=array(1,null,3,null,1);
$data['m_products_price_general']=array(1,null,18);
$data['m_products_contragents_id']=array(1,null,null,10,1);

array_walk($data,'check',true);

if(!$e){
	$data['m_products_id']=get_id('m_products');
	$data['m_products_show_site']=0;
	$data['m_products_show_price']=0;
	$data['m_products_date']=$data['m_products_update']=dt();
	$data['m_products_price_general']=(float)str_replace(array(' ',','),array('','.'),$data['m_products_price_general']);
	
	$q='INSERT `formetoo_main`.`m_products` SET
		`m_products_id`='.$data['m_products_id'].',
		`m_products_contragents_id`='.$data['m_products_contragents_id'].',
		`m_products_name`=\''.$data['m_products_name'].'\',
		`m_products_unit`='.$data['m_products_unit'].',
		`m_products_price_general`='.$data['m_products_price_general'].',
		`m_products_show_site`='.$data['m_products_show_site'].',
		`m_products_show_price`='.$data['m_products_show_price'].',
		`m_products_date`=\''.$data['m_products_date'].'\',
		`m_products_update`=\''.$data['m_products_update'].'\';';
	if($sql->query($q))
		echo $data['m_products_id'];
	else
		echo 'false';

	//удаляем привязанные категории к продукту
	$q='DELETE FROM `formetoo_main`.`m_products_category` WHERE `product_id`=\''.$data['m_products_id'].'\';';
	if(!($sql->query($q))){
		elogs();
	}
	
	//добавляем привязанные категории к продукту
	$q='INSERT INTO `formetoo_main`.`m_products_category` (`product_id`,`category_id`) VALUES (\''.$data['m_products_id'].'\', \'1263923105\');';
		
	if(!($sql->query($q))){
		elogs();
	}
}
//else print_r($e);
unset($sql);
?>