<?
/* 
*	ПРОВЕРКА ХЕША ФОТОГРАФИЙ НА СОВПАДЕНИЕ
*	С ХЕШЕМ ФОТКИ-ЗАГЛУШКИ ИЗОЛЮКСА, УДАЛЕНИЕ ТАКИХ ФОТО
*	И СКРЫТИЕ ТОВАРА, ЕСЛИ ФОТО НЕ ОСТАЛОСЬ
*/


define ('_DSITE',1);
ini_set('display_errors',1);
require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
$sql=new sql;

$ch='2837317075c93e78ed7ba2571afb563b';
$q='SELECT `id`,`m_products_foto`,`id_isolux` FROM `formetoo_main`.`m_products` LIMIT 80000;';
$res=$sql->query($q);
foreach($res as $_res)
	if($json_foto=json_decode($_res['m_products_foto'])){
		$re_encode=0;
		$re_main=0;
		foreach($json_foto as &$_foto){
			$foto_=$_foto->file;
			$file_min=$_SERVER['DOCUMENT_ROOT'].'/../../p-islx.Formetoo.ru/documents/'.substr($_res['id_isolux'],0,2).'/SN'.$_res['id_isolux'].'/'.$foto_.'_min.jpg';
			$file_med=$_SERVER['DOCUMENT_ROOT'].'/../../p-islx.Formetoo.ru/documents/'.substr($_res['id_isolux'],0,2).'/SN'.$_res['id_isolux'].'/'.$foto_.'_med.jpg';
			$file_max=$_SERVER['DOCUMENT_ROOT'].'/../../p-islx.Formetoo.ru/documents/'.substr($_res['id_isolux'],0,2).'/SN'.$_res['id_isolux'].'/'.$foto_.'_max.jpg';
			if(file_exists($file_min)){
				$hash=hash_file('md5',$file_min);
				//если хэш фото совпадает с хешем заглушки изолюкса - удаляем фото
				if($hash==$ch){
					$re_encode++;
					if($_foto->main==1)
						$re_main=1;
					unlink($file_min);
					unlink($file_med);
					unlink($file_max);
					unset($_foto);
				}
			}
			else{
				$re_encode++;
				if($_foto->main==1)
					$re_main=1;
				unset($_foto);
			}
		}
		//если есть флаги удаления заглушки и их количество меньше вол-ва фото в товаре, пересобираем фото товара
		if($re_encode&&$re_encode<sizeof($json_foto)){
			//если удалили главное фото - назначаем другое главным
			if($re_main)
				foreach($json_foto as &$_foto){
					$_foto->main=1;
					break;
				}
			$json_foto=json_encode($json_foto,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
			$q='UPDATE `formetoo_main`.`m_products` SET 
				`m_products_foto`="'.$json_foto.'" 
				WHERE `id`='.$_res['id'].' LIMIT 1;';
			if($sql->query($q))
				echo 'Убрана заглушка у товара ID='.$_res['id'].'<br/>';
		}
		//если все фото были удалены - скрываем товар
		elseif($re_encode){
			$q='UPDATE `formetoo_main`.`m_products` SET 
				`m_products_foto`="",
				`m_products_show_site`=0
				WHERE `id`='.$_res['id'].' LIMIT 1;';
			if($sql->query($q))
				echo 'Скрыт для показа товар ID='.$_res['id'].'<br/>';
		}
				
	}

?>