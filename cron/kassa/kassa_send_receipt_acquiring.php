<?
/*

 * Проверка подтверждённых заказов со статусом 3 и -3 (оплата и возврат), оплаченных
 * картой (эквайринг)

*/
define ('_DSITE',1);
ini_set('display_errors',1);
set_time_limit(50);
require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/classes/kassa.php');
require_once(__DIR__.'/../../functions/classes/info.php');
require_once(__DIR__.'/../../functions/classes/contragents.php');
$sql=new sql();

$q='SELECT * FROM `formetoo_cdb`.`m_orders` WHERE 
	`m_orders_bank_order_id` IS NOT NULL AND 
	`m_orders_pay_method`=1 AND 
	(`m_orders_status`=3 OR `m_orders_status`=-3)
	ORDER BY `m_orders_date` DESC LIMIT 100;';
if($orders=$sql->query($q))
	foreach($orders as $_order){
		//НАХОДИМ СЧЁТ
		$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE 
			`m_documents_order`='.$_order['m_orders_id'].' AND 
			`m_documents_performer` IN (1012424923,1306686034) 
			ORDER BY `m_documents_date` DESC LIMIT 1;';
		if($doc=$sql->query($q)){
			$doc=$doc[0];
		
			//КОНТАКТЫ КЛИЕНТА ДЛЯ ЧЕКА
			$contragents=new contragents;
			$info=new info;
			$client=$contragents->getInfo($doc['m_documents_customer']);
			$tel=$info->getTel($client['m_contragents_id'])[0];
			$q='SELECT * FROM `m_users` WHERE `m_users_id`='.$client['m_contragents_user_id'].' LIMIT 1;';
			$userInfo=$sql->query($q)[0];
			$userEmail=$userInfo['m_users_email']?$userInfo['m_users_email']:$client['m_contragents_email'];
			$userTel=$userInfo['m_users_tel']?$userInfo['m_users_tel']:$tel['m_contragents_tel_numb'];
			
			//ТОВАРНЫЕ ПОЗИЦИИ ДЛЯ ЧЕКА
			$data_doc_params=json_decode($doc['m_documents_params']);
			$data_prod=array();
			$data_serv=array();
			$invoice_items=array();
			foreach($data_doc_params->items as $_item)
				foreach($_item->services as $_pos){
					$invoice_items[]=$_pos;
					if($_pos->table=='products')
						$data_prod[]=$_pos->id;
					else $data_serv[]=$_pos->id;
				}
			$q='SELECT `id`,`m_products_name_full`,`m_products_unit` FROM `formetoo_main`.`m_products` WHERE `id` IN(0,'.implode(',',$data_prod).');';	
			$data_prod=$sql->query($q,'id');
			$q='SELECT `m_services_id`,`m_services_name`,`m_services_unit` FROM `formetoo_main`.`m_services` WHERE `m_services_id` IN(0,'.implode(',',$data_serv).');';	
			$data_serv=$sql->query($q,'m_services_id');
			$data_units=array();
			foreach($data_prod as $_prod)
				$data_units[]=$_prod[0]['m_products_unit'];
			foreach($data_serv as $_serv)
				$data_units[]=$_serv[0]['m_services_unit'];	
			$q='SELECT * FROM `m_info_units` WHERE `m_info_units_id` IN('.implode(',',$data_units).');';
			$data_units=$sql->query($q,'m_info_units_id');
			$receipt_items=array();
			foreach($invoice_items as $k=>$_item){
				$receipt_items_=array();
				$receipt_items_['name']=($_item->table=='products'?$data_prod[$_item->id][0]['m_products_name_full']:$data_serv[$_item->id][0]['m_services_name']);
				$receipt_items_['price']=(float)$_item->price;
				$receipt_items_['count']=(float)$_item->count;
				$receipt_items_['sum']=(float)$_item->sum;
				$receipt_items_['unit']=$data_units[$_item->table=='products'?$data_prod[$_item->id][0]['m_products_unit']:$data_serv[$_item->id][0]['m_services_unit']][0]['m_info_units_name'];
				$receipt_items_['type']=$_item->table;
				$receipt_items[]=$receipt_items_;
			}
			$kassa=new kassa;
			//ОТПРАВЛЯЕМ ЧЕК ПРИХОДА
			if($_order['m_orders_status']==3){
				if($res=$kassa->fiscal(
					'sell',
					'online',
					$userEmail?$userEmail:transform::telClean($userTel),
					$receipt_items
				)) $status=4;
			}
			//ОТПРАВЛЯЕМ ЧЕК ВОЗВРАТА
			elseif($_order['m_orders_status']==-3){
				if($res=$kassa->fiscal(
					'sellReturn',
					'online',
					$userEmail?$userEmail:transform::telClean($userTel),
					$receipt_items
				)) $status=-4;
			}
			//ЕСЛИ ЧЕК УСПЕШНО ОТПРАВЛЕН (НЕ ФАКТ ЧТО ОН СДЕЛАН)
			if(isset($status))
				$sql->query('UPDATE `formetoo_cdb`.`m_orders` SET 
					`m_orders_status`='.$status.',
					`m_orders_kassa_id`='.$res.' 
					WHERE `m_orders_id`='.$_order['m_orders_id'].' LIMIT 1;');
		}
	}
unset($sql);
?>