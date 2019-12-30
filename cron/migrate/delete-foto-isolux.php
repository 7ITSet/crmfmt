<?
/*
*	Удаление всех фото товаров, спарсенных с сайта isolux
*/

define ('_DSITE',1);
ini_set('display_errors',1);
require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/classes/contragents.php');
require_once(__DIR__.'/../../functions/classes/documents.php');
require_once(__DIR__.'/../../functions/classes/foto.php');
$sql=new sql;
$sql_islx=new sql(2);
$foto=new foto();
global $main_dir;


$units=$sql->query('SELECT * FROM `m_info_units`;');

$q='SELECT * FROM `p-islx`.`ci_goods` WHERE `isload`=1 AND `price`!=0 AND `url`!=\'\' LIMIT 0,800;';
$res_islx=$sql_islx->query($q);
$ids=array();

if($res_islx)
	foreach($res_islx as $_item){
		$_item['id']=mb_substr($_item['id'],2);
		$ids[]='\''.$_item['id'].'\'';
	}

$q='SELECT * FROM `formetoo_main`.`m_products` WHERE `id_isolux` IN (';
$ids=implode(',',$ids);
$q.=$ids.');';

$res=$sql->query($q);
foreach($res as $_p){
	$_p['m_products_foto']=json_decode($_p['m_products_foto']);
	if($_p['m_products_foto']){
		foreach($_p['m_products_foto'] as $_foto){
			if($_foto->file){
				$f_max=$_SERVER['DOCUMENT_ROOT'].'/../../p-islx.Formetoo.ru/documents/'.substr($_p['id_isolux'],0,2).'/SN'.$_p['id_isolux'].'/'.$_foto->file.'_max.jpg';
				$f_med=$_SERVER['DOCUMENT_ROOT'].'/../../p-islx.Formetoo.ru/documents/'.substr($_p['id_isolux'],0,2).'/SN'.$_p['id_isolux'].'/'.$_foto->file.'_med.jpg';
				$f_min=$_SERVER['DOCUMENT_ROOT'].'/../../p-islx.Formetoo.ru/documents/'.substr($_p['id_isolux'],0,2).'/SN'.$_p['id_isolux'].'/'.$_foto->file.'_min.jpg';
				if(file_exists($f_max)){
					list($w,$h)=getimagesize($f_max);
					if($w)
						unlink($f_max);
				}
				if(file_exists($f_med)){
					list($w,$h)=getimagesize($f_med);
					if($w)
						unlink($f_med);
				}
				if(file_exists($f_min)){
					list($w,$h)=getimagesize($f_min);
					if($w)
						unlink($f_min);
				}
			}
				
		}
	}
}
						
?>