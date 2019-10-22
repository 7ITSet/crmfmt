<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
require_once('../../functions/classes/orders.php');
require_once('../../functions/classes/documents.php');

$sql=new sql();
$orders=new orders();
$documents=new documents();

$invoice=post('invoice');
if($doc=$documents->getInfo($invoice))
	echo $doc['m_documents_performer'].'|'.$doc['m_documents_customer'];
else
	echo 'ERROR';
?>