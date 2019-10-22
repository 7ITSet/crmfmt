<?
define ('_DSITE',1);
ini_set('display_errors',1);
require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');

$sql=new sql();

$cbr_today=simplexml_load_file('http://www.cbr.ru/scripts/XML_daily.asp?date_req='.dtu('','d/m/Y'));
$cbr_tomorrow=simplexml_load_file('http://www.cbr.ru/scripts/XML_daily.asp?date_req='.dtu(dtc('','+1 day'),'d/m/Y'));

foreach($cbr_today->Valute as $_valute){
	if($_valute->NumCode==840)
		$usd_exchange_today=(float)str_replace(array(' ',','),array('','.'),$_valute->Value);
	if($_valute->NumCode==978)
		$eur_exchange_today=(float)str_replace(array(' ',','),array('','.'),$_valute->Value);
}
foreach($cbr_tomorrow->Valute as $_valute){
	if($_valute->NumCode==840)
		$usd_exchange_tomorrow=(float)str_replace(array(' ',','),array('','.'),$_valute->Value);
	if($_valute->NumCode==978)
		$eur_exchange_tomorrow=(float)str_replace(array(' ',','),array('','.'),$_valute->Value);
}

$usd_exchange=max(array($usd_exchange_today,$usd_exchange_tomorrow));
$eur_exchange=max(array($eur_exchange_today,$eur_exchange_tomorrow));
$sql->query('UPDATE `formetoo_cdb`.`m_info_settings` SET 
	`m_info_settings_exchange_usd`=\''.(float)str_replace(array(' ',','),array('','.'),$usd_exchange).'\',
	`m_info_settings_exchange_eur`=\''.(float)str_replace(array(' ',','),array('','.'),$eur_exchange).'\'
	WHERE `m_info_settings_id`=1;');

unset($sql);
?>