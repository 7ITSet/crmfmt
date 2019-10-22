<?
define ('_DSITE',1);
ini_set('display_errors',1);
require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');

$sql=new sql();

require_once(__DIR__.'/../../functions/classes/products.php');
$products=new products();

$prod=array();
$serv=array();

//ищем оплаченные счета
$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_templates_id`=2363374033 AND `m_documents_pays`!="" ORDER BY `m_documents_date` DESC;';
$docs=$sql->query($q);print_r($docs);
//добавляем позиции из них в массивы товаров и услуг
foreach($docs as $_doc){
	$p=json_decode($_doc['m_documents_params']);
	foreach($p->items as $_section)
		foreach($_section->services as $_item){
			if($_item->table=='products'){
				if(isset($products->products_id[$_item->id][0]['m_products_categories_id'])){
					$c=explode('|',$products->products_id[$_item->id][0]['m_products_categories_id']);
					//если позиция находится в "Без категории", добавляем ее для переноса в "Без категории ОПЛАЧЕННЫЕ"
					if(in_array('1263923105',$c)!==false)
						$prod[]=$_item->id;
				}
			}
			else{
				if(isset($products->products_id[$_item->id][0]['m_products_categories_id'])){
					$c=explode('|',$products->products_id[$_item->id][0]['m_products_categories_id']);
					//если позиция находится в "Без категории", добавляем ее для переноса в "Без категории ОПЛАЧЕННЫЕ"
					if(in_array('1326613691',$c)!==false)
						$serv[]=$_item->id;
				}
				
			}
		}
}
$q='UPDATE `formetoo_main`.`m_products` SET `m_products_categories_id`=1123549104 WHERE `m_products_id` IN(';
$q_a=array();
foreach($prod as $_prod)
	$q_a[]=$_prod;
$q.=implode(',',$q_a).');';
if($prod)
	$sql->query($q);

$q='UPDATE `formetoo_main`.`m_services` SET `m_services_categories_id`=1123549104 WHERE `m_services_id` IN(';
$q_a=array();
foreach($serv as $_serv)
	$q_a[]=$_serv;
$q.=implode(',',$q_a).');';
if($serv)
	$sql->query($q);
	

unset($sql);
?>