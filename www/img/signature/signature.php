<?
define ('_DSITE',1);
require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/system.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/ccdb.php');
$sql=new sql;
require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/classes/contragents.php');
$contragents=new contragents;
require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/classes/documents.php');
$documents=new documents;

$documents->getSignature(get('contragent'),get('type'));
?>