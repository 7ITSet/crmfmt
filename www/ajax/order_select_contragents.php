<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
require_once('../../functions/classes/orders.php');

$sql=new sql();
$orders=new orders();

$order=post('order');
if(isset($orders->orders_id[$order][0]))
	echo $orders->orders_id[$order][0]['m_orders_performer'].'|'.$orders->orders_id[$order][0]['m_orders_customer'];
else
	echo 'ERROR';
?>