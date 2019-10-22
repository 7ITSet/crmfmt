<?
/*
 *	Смена способа оплаты счёта в таблице документов
*/

define ('_DSITE',1);

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');

global $e;

$sql=new sql;

$data['order']=array(1,null,null,10,1);
$data['method']=array(1,null,null,1,1);
array_walk($data,'check');

if(!$e){
	if(in_array($data['method'],array(1,2,3)))
		if($sql->query('UPDATE `formetoo_cdb`.`m_orders` SET `m_orders_pay_method`='.$data['method'].' WHERE `m_orders_id`='.$data['order'].' LIMIT 1;'))
			echo 'SUCCESS';
		else echo 'ERROR SQL';
	else echo 'ERROR INPUT RANGE';
}
else echo 'ERROR INPUT DATA';

unset($sql);
?>