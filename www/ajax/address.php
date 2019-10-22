<?
define ('_DSITE',1);

global $e;

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
$sql=new sql;

$data['address']=array(1,null,400);
array_walk($data,'check',true);

if(!$e){
	$fields=array(
		"query"=>$data['address'],
		"count"=>1
	);
	$ch = curl_init('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	$headers = array
	(
		'Content-Type: application/json',
		'Accept: application/json',
		'Authorization: Token 7d5d388b09a1ac08817ad3bcb8dc1e6e09dbc738'
	);
	curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$result = curl_exec($ch);
	curl_close($ch);
	
	if(strpos($result,'suggestions')!==false)
		echo $result;
	else
		print_r($result);// 'ERROR';
}
else 
	echo 'ERROR';

unset($sql);
?>