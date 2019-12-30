<?
/*
*	Удаление дубликатов сертификатов для товаров
*/

define ('_DSITE',1);
ini_set('display_errors',1);
require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
$sql=new sql;

$q='SELECT * FROM `formetoo_main`.`m_products` WHERE `m_products_docs` NOT IN (\'\',\'[]\') AND `m_products_docs` LIKE \'%[{"name%\';';
$res=$sql->query($q,'id');

$result=array();

if($res)
	foreach($res as $_item){
		$_item=$_item[0];
		if($_item['m_products_docs']){
			$_item['m_products_docs']=json_decode($_item['m_products_docs']);
			$_item['docs_new']=array();
			foreach($_item['m_products_docs'] as $_doc){
				$file=$_SERVER['DOCUMENT_ROOT'].'/../../p-islx.Formetoo.ru/documents/'.substr($_item['id_isolux'],0,2).'/SN'.$_item['id_isolux'].'/'.$_doc->url;
				if(file_exists($file)){
					$hash=hash_file('md5',$file);
					$q='SELECT `m_products_docs_id` FROM `formetoo_main`.`m_products_docs` WHERE `m_products_docs_filemd5`=\''.$hash.'\' LIMIT 1;';
					//если такой файл уже есть в базе
					if($res1=$sql->query($q)){
						//записываем его ID для товара
						$_item['docs_new'][]=$res1[0]['m_products_docs_id'];
						//удаляем файл
						if(unlink($file))
							echo 'DELETED '.$file.'<br/>';
					}
					//если файла нет - добаляем его в базу и записываем ID для товара
					else{
						$id=get_id('m_products_docs');
						$q='INSERT INTO `formetoo_main`.`m_products_docs` SET 
							`m_products_docs_id`='.$id.',
							`m_products_docs_filename`=\''.$_doc->url.'\',
							`m_products_docs_filedir`='.$_item['id_isolux'].',
							`m_products_docs_filesize`=\''.get_filesize($file).'\',
							`m_products_docs_filemd5`=\''.$hash.'\',
							`m_products_docs_desc`=\''.$_doc->name.'\';';
echo $q.'<br/>';
						if($sql->query($q))
							$_item['docs_new'][]=$id;
					}
				}
			}
			$q='UPDATE `formetoo_main`.`m_products` SET 
				`m_products_docs`=\''.implode('|',$_item['docs_new']).'\' 
				WHERE `id`='.$_item['id'].' LIMIT 1;';
echo $q.'<br/>';				
			if($sql->query($q))
				$result[]=$_item['id'];
		}
	}

if($result){
	echo 'DOCS UPDATED FROM NEXT GOODS = <br/>';
	foreach($result as $_result)
		echo '&nbsp;&nbsp;'.$res[$_result][0]['id'].' '.$res[$_result][0]['m_products_name_full'].'<br/>';
	}
else
	echo 'ALL PRODUCTS DOCS RIGHT';

?>