<?

define ('_DSITE',1);
ini_set('display_errors',1);
require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
$sql_islx=new sql(2);

//ПЕРЕЗАГРУЗКА ТОВАРОВ, У КОТОРЫХ ПРИКРЕПЛЕНЫ, НО ФАКТИЧЕСКИ ОТСУТВУЮТ ФОТО ИЛИ ДОКУМЕНТЫ

$q='SELECT * FROM `p-islx`.`ci_goods` WHERE `isload`=1 AND `price`!=0 AND `url`!=\'\';';
$res_islx=$sql_islx->query($q);

$ids_reload=array();
if($res_islx)
	foreach($res_islx as $_item){
		$_item['id']=mb_substr($_item['id'],2);
		$foto=explode(';',$_item['foto']);
		if($_item['docs']){
			$_item['docs']=json_decode($_item['docs']);
			foreach($_item['docs'] as $_doc){
				if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$_doc->url))
					$ids_reload[]='\'СН'.$_item['id'].'\'';
			}
		}
		foreach($foto as $_foto){
			if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$_foto))
				$ids_reload[]='\'СН'.$_item['id'].'\'';
		}
	}
	
if($ids_reload){
	$ids_reload=array_unique($ids_reload);
	$q='UPDATE `p-islx`.`ci_goods` SET `isload`=0 WHERE `id` IN ('.implode(',',$ids_reload).');';
	print_r($ids_reload);
	if ($sql_islx->query($q))
		echo 'SET TO UPDATE '.sizeof($ids_reload).' GOODS';
	else
		echo 'ERROR';
}
else
	echo 'ALL GOODS SITE_DATA IS LOADED';


?>