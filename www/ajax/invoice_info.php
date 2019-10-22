<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
require_once('../../functions/classes/documents.php');

$sql=new sql();
$documents=new documents();

$invoice=post('invoice');
if(isset($documents->documents_id[$invoice][0]))
	echo $documents->documents_id[$invoice][0]['m_documents_params'];	
else
	echo 'ERROR';
?>