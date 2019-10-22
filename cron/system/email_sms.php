<?
define ('_DSITE',1);
ini_set('display_errors',1);
ini_set('max_execution_time', 0);
require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/classes/message.php');

$sql=new sql(0,'error');
$message=new message;
$file_block='messages_block.tmp';

//if(file_exists($file_block)===false&&fopen($file_block,'x')){
//	for($i=0;$i<1;$i++){
	//while(true){
		$message->sendSMS();
		$message->sendEmail();
	//}
//	unlink($file_block);
//}


?>