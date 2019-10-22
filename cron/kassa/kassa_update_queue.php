<?
/*

 *	Обновление статусов отправленных на кассу запросов.
 *	Обновляем все запросы функцией updateQueue, пробегаемся по заказам
 *	с отправленными чеками, если ответ по чеку пришёл - меняем статус заказа.
 
*/
define ('_DSITE',1);
set_time_limit(20);
ini_set('display_errors',1);
require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/classes/kassa.php');

$sql=new sql;
$kassa=new kassa;

$kassa->updateQueue();

$q='SELECT * FROM `formetoo_cdb`.`m_orders` WHERE 
	`m_orders_kassa_id` IS NOT NULL AND 
	(`m_orders_status`=4 OR `m_orders_status`=-4)
	ORDER BY `m_orders_date` DESC LIMIT 100;';
if($orders=$sql->query($q)){
	//все чеки
	$receipts=$kassa->getInfo();
	//пробегаемся по каждому заказу с отправленными на кассу чеками
	foreach($orders as $_order){
		//если чек существует и его статус = 2
		if(isset($receipts[$_order['m_orders_kassa_id']])&&$receipts[$_order['m_orders_kassa_id']][0]['m_buh_kassa_status']==2){
			//обновляем стутус заказа
			$q='UPDATE `formetoo_cdb`.`m_orders` SET 
				`m_orders_status`='.($_order['m_orders_status']>0?'5':'-5').' 
				WHERE `m_orders_id`='.$_order['m_orders_id'].' LIMIT 1;';
			$sql->query($q);
		}
		//если чек существует и его статус ошибочный (<0), удаляем его по возможности и создаём новый чек
		elseif(isset($receipts[$_order['m_orders_kassa_id']])&&$receipts[$_order['m_orders_kassa_id']][0]['m_buh_kassa_status']<0){
			//удаляем задание на чек
			$kassa->deleteResponse($_order['m_orders_kassa_id']);
			//СПОСОБ 1
			//вытаскиеваем запрос из старого чека
			/* $request_new=json_decode($receipts[$_order['m_orders_kassa_id']][0]['m_buh_kassa_request']);
			$request_new=$request_new->request;
			//отправляем чек заново
			if($receipt_new=$kassa->sendResponse(json_encode($request_new,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE))){
				//привязываем новый чек к заказу
				$q='UPDATE `m_orders` SET `m_orders_kassa_id`='.(int)$receipt_new.' WHERE `m_orders_id`='.$_order['m_orders_id'].' LIMIT 1;';
				$sql->query($q);
			} */
			//ИЛИ СПОСОБ 2
			//меняем статус заказа, чтобы чек сформировался для него заново автоматически службой
			$status=$_order['m_orders_status']==4?3:-3;
			$q='UPDATE `formetoo_cdb`.`m_orders` SET `m_orders_status`='.$status.' WHERE `m_orders_id`='.$_order['m_orders_id'].' LIMIT 1;';
			$sql->query($q);
			
			
		}
	}
}


?>