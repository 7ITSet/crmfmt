<?
define ('_DSITE',1);

global $e;

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
$sql=new sql;

$data['bik']=array(1,null,null,9,1);
array_walk($data,'check',true);

if(!$e){
	$ch = curl_init('https://dadata.ru/api/v2/suggest/bank');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$headers = array
	(
		'Content-Type: application/json',
		'Accept: application/json',
		'Authorization: Token aecb50049a606c2efc98246621d8527651bd84ea',
		'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7'
	);
	curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
	curl_setopt($ch, CURLOPT_POSTFIELDS, '{
		"query": "'.$data['bik'].'"
	}');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$result = curl_exec($ch);
	curl_close($ch);
	
	if(strpos($result,'suggestions')!==false)
		echo $result;
	else
		echo 'ERROR';
}
else 
	echo 'ERROR';

unset($sql);
?>