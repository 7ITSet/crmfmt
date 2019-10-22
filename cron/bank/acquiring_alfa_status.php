<?
/*

 * Ппроверка состояние платежей со статусом 2 (получен
 * orderId от банка)

 * Если статус = 2, то ставим статус поплаченного заказа.

 * Статусы:
 * 	'0' Заказ зарегистрирован, но не оплачен;
 *	'1' Предавторизованная сумма захолдирована (для двухстадийных платежей);
 *	'2' Проведена полная авторизация суммы заказа;
 *	'3' Авторизация отменена;	- код заказа -1
 *	'4' По транзакции была проведена операция возврата;  - код заказа -3
 *	'5' Инициирована авторизация через ACS банка-эмитента;
 *	'6' Авторизация отклонена.  - код заказа -1

*/
define ('_DSITE',1);
ini_set('display_errors',1);

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/classes/bank.php');
$sql=new sql();
$bank=new bank;

$q='SELECT * FROM `formetoo_cdb`.`m_orders` WHERE 
	`m_orders_bank_order_id` IS NOT NULL AND 
	`m_orders_pay_method`=1 AND 
	`m_orders_status`=2  
	ORDER BY `m_orders_date` DESC LIMIT 100;';
if($orders=$sql->query($q))
	foreach($orders as $_order){
		return $bank->checkStatus($_order['m_orders_bank_order_id']);
	}
else return 'EMPTY_QUEUE';

unset($sql);
unset($bank);
?>