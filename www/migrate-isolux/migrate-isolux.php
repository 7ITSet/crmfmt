<script type="text/javascript" src="jquery.min.js"></script>
<script type="text/javascript" src="buzz.min.js"></script>
<script>
$(document).ready(function(){
	function sendNotification(title, options) {
		// Проверим, поддерживает ли браузер HTML5 Notifications
		if ("Notification" in window) {
			// Проверим, есть ли права на отправку уведомлений
			if (Notification.permission === "granted") {
				// Если права есть, отправим уведомление
				var notification = new Notification(title, options);
				function clickFunc(){ alert('Пользователь кликнул на уведомление'); }
				notification.onclick = clickFunc;
			}
			// Если прав нет, пытаемся их получить
			else{
				if (Notification.permission !== 'denied') {
					Notification.requestPermission(function (permission) {
						// Если права успешно получены, отправляем уведомление
						if (permission === "granted"){
							var notification = new Notification(title, options);
						}
						else {
							alert('Вы запретили показывать уведомления'); // Юзер отклонил наш запрос на показ уведомлений
						}
					});
				}
			}
		}
	}


/*	if($('div:contains("Error")').length||$('div:contains("Warning")').length||$('div:contains("Notice")').length){
		sendNotification('Заголовок', {
			body: 'Тестирование HTML5 Notifications',
			icon: 'http://habrastorage.org/storage2/cf9/50b/e87/cf950be87f8c34e63c07217a009f1d17.jpg',
			dir: 'auto'
		});
		
		var sound = new buzz.sound("Transformery_-_Uvedomlenie", {
			formats: ["mp3"]
		});

		sound.play()
			 .fadeIn()
			 .loop()
			 .bind("timeupdate", function() {
				var timer = buzz.toTimer(this.getTime());
				document.getElementById("timer").innerHTML = timer;
			 });
	}*/
	if(false){}
	else{
		setTimeout(function(){
			window.location=$('a').attr('href');
		},1000);
	}
})
</script>
<div>
<?
define ('_DSITE',1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(1200);
ini_set('memory_limit', '1024M');

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/classes/foto.php');
$sql=new sql;
$sql_islx=new sql(2);
$foto=new foto();
global $main_dir;

$start=get('start')?get('start'):0;
$length=get('length')?get('length'):50;
$update_foto=false;
$update_docs=false;
$update_attr=false;
$update_text=false;

$units=$sql->query('SELECT * FROM `m_info_units`;');

$q='SELECT * FROM `ci_goods` WHERE `isload`=1 AND `price`!=\'0\' AND `url`!=\'\' ORDER BY `date` DESC LIMIT '.$start.','.$length.';';
//$q='SELECT * FROM `ci_goods` WHERE `id`=\'СН210494\' LIMIT 1';
$res_islx=$sql_islx->query($q);

$categories=array();
if($res_islx)
	foreach($res_islx as &$_item){
		$_item['attributes']=json_decode($_item['attributes']);
		$_item['docs']=json_decode($_item['docs']);
		$_item['docs']=$_item['docs']?$_item['docs']:array();
		$_item['foto']=explode(';',$_item['foto']);
		$_item['links']=$_item['links']?explode('|',$_item['links']):0;
		$_item['id']=mb_substr($_item['id'],2);
		$_item['category']=explode(';',htmlspecialchars_decode($_item['category']));
		//убираем наименование товара из хлебных крошек
		array_pop($_item['category']);

		//добавляем товар
		//проверяем, нет ли такого товара, прайс запрашиваем для обновления цены, если товар уже есть
		$q='SELECT `id`,`m_products_price_general`,`m_products_check_it`,`m_products_docs`,`m_products_foto` FROM `m_products` WHERE 
			`id_isolux`=\''.$_item['id'].'\' LIMIT 1;';
		//если товара нет
		if(!$res=$sql->query($q)){
			//добавляем категории
			if($product_cat=addCategory(implode('|',$_item['category']))){
				$attributes=$sql->query('SELECT * FROM `m_products_attributes_list`;','m_products_attributes_list_name');
				$attr_values_all=$sql->query('SELECT * FROM `m_products_attributes_values`;','m_products_attributes_values_value');
				
				//добавляем товар
				$product_id=get_id('m_products');
				
				//ОБРАБОТКА ДОКУМЕНТОВ К ТОВАРУ
				$docs=array();
				foreach($_item['docs'] as $_doc){
					$file=__DIR__.'/../../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$_doc->url;
					//если файл существует и его расширение из разрешенных
					if(file_exists($file)){
						$ext=explode('.',$_doc->url);
						$ext=array_pop($ext);
						if(in_array($ext,array('pdf','doc','jpg','xls'))){
							$hash=hash_file('md5',$file);
							$q='SELECT `m_products_docs_id` FROM `m_products_docs` WHERE `m_products_docs_filemd5`=\''.$hash.'\' LIMIT 1;';
							//если такой файл уже есть в базе
							if($res1=$sql->query($q)){
								//записываем его ID для товара
								$docs[]=$res1[0]['m_products_docs_id'];
								//удаляем файл
								if(unlink($file))
									echo 'DELETED '.$file.'<br/>';
							}
							//если файла нет - добаляем его в базу и записываем ID для товара
							else{
								$id=get_id('m_products_docs');
								$q='INSERT INTO `m_products_docs` SET 
									`m_products_docs_id`='.$id.',
									`m_products_docs_filename`=\''.$_doc->url.'\',
									`m_products_docs_filedir`='.$_item['id'].',
									`m_products_docs_filesize`=\''.get_filesize($file).'\',
									`m_products_docs_filemd5`=\''.$hash.'\',
									`m_products_docs_desc`=\''.val($_doc->name).'\';';
								if($sql->query($q))
									$docs[]=$id;
							}
						}
					}
				}
				
				//ЕД. ИЗМЕРЕНИЯ (ПО УМОЛЧАНИЮ ШТ)
				$product_unit=3;
				foreach($units as $_unit)
					if($_item['unit']==$_unit['m_info_units_name'])
						$product_unit=$_unit['m_info_units_id'];
						
				//СВЯЗАННЫЕ ТОВАРЫ
				$product_links=array();
				if($_item['links']){
					//находим id товаров в рабочей базе по id isolux
					$q='SELECT `id` FROM `m_products` WHERE `id_isolux` IN ('.implode(',',$_item['links']).');';
					if($res=$sql->query($q))
						foreach($res as $_res)
							$product_links[]=$_res['id'];
				}
						
				$q='INSERT INTO `m_products` SET 
					`id`='.$product_id.',
					`id_isolux`=\''.$_item['id'].'\',
					`id`='.$product_cat.',
					`m_products_name`=\''.val($_item['price_name']).'\',
					`m_products_name_full`=\''.($_item['name']?val($_item['name']):val($_item['price_name'])).'\',
					`m_products_unit`='.$product_unit.',
					`m_products_links`=\''.($product_links?implode('|',$product_links):'').'\',
					`m_products_price_general`='.($_item['price']*1.096).',
					`m_products_multiplicity`='.($_item['miltiplicity']?$_item['miltiplicity']:1).',
					'.($docs?'`m_products_docs`=\''.implode('|',$docs).'\',':'').'
					'.($_item['video']?'`m_products_video`=\''.val($_item['video']).'\',':'').'
					`m_products_update`=\''.dt().'\',
					`m_products_date`=\''.dt().'\';';
				if($sql->query($q)){
				
					//ДОБАВЛЯЕМ ТЕКСТ
					$q='INSERT INTO `m_products_desc` SET 
						`m_products_desc_id`='.$product_id.',
						`m_products_desc_text`=\''.str_replace(array('<div>','</div>','<a>','</a>','<p>&nbsp;</p>','</ol>'),array('','','','','','<span class="ol-fade"></span></ol>'),preg_replace("#(</?\w+)(?:\s(?:[^<>/]|/[^<>])*)?(/?>)#ui",'$1$2',$_item['description'])).'\';';
					$sql->query($q);
					
					//ДОБАВЛЯЕМ АТРИБУТЫ
					if($_item['attributes'])
						foreach($_item['attributes'] as $_attr_item){
							if(!is_object($_attr_item)) continue;
							$_a_n=val($_attr_item->name);
							$_a_v=val($_attr_item->value);
							$_a_h=val($_attr_item->hint);
							//если атрибут с пустым значением - переходим к следующему
							if(!$_a_v) continue;
							//разделяем по пробелу значение атрибута и ед. измерения
							$__a_v=explode(' ',$_a_v);
							//извлекаем значение атрибута (перед первым пробелом)
							$_a_vv=array_shift($__a_v);
							//соединяем обратно - все, что осталось, - ед. измерения
							$_a_vu=implode(' ',$__a_v);
							//узнаём типа атрибута по символам до первого пробела
							$attr_type=is_numeric(str_replace(array(',',' '),array('.',''),$_a_vv))?2:1;
							//если такого атрибута ещё не было или его тип не совпадает с тем, что был раньше для атрибутов с таким именем, добавляем новый атрибут с нужным типом
							//находим всевозможные типы атрибута с таким же именем
							if(isset($attributes[$_a_n])){
								$attr_id_type_index=0;
								$_a_types=array();
								//определяем индекс типа, соотвествующего типу проверяемого атрибута, в наборе существующих атрибутов
								foreach($attributes[$_a_n] as $_index=>$_existattr){
									if($_existattr['m_products_attributes_list_type']==$attr_type)
										$attr_id_type_index=$_index;
									$_a_types[]=$_existattr['m_products_attributes_list_type'];
								}
							}
							if(!isset($attributes[$_a_n])||(isset($attributes[$_a_n])&&!in_array($attr_type,$_a_types))){
								$attr_id=get_id('m_products_attributes_list');						
								$q='INSERT INTO `m_products_attributes_list` SET 
									`m_products_attributes_list_id`='.$attr_id.',
									`m_products_attributes_list_name`=\''.$_a_n.'\',
									`m_products_attributes_list_name_url`=\''.transform::translit($_a_n).'\',
									`m_products_attributes_list_type`='.$attr_type.',
									`m_products_attributes_list_hint`=\''.$_a_h.'\',
									`m_products_attributes_list_unit`=\''.($attr_type==1?'':$_a_vu).'\';';
								$sql->query($q);
							}
							//если атрибут с нужным именем и типом уже есть в базе, указываем его
							else{
								$attr_id=$attributes[$_a_n][$attr_id_type_index]['m_products_attributes_list_id'];
								//если у атрибута не добалялась подсказка, но она есть - добаляем
								if(!$attributes[$_a_n][$attr_id_type_index]['m_products_attributes_list_hint']&&$_a_h)
									$sql->query('UPDATE `m_products_attributes_list` SET `m_products_attributes_list_hint`=\''.$_a_h.'\' WHERE `m_products_attributes_list_id`='.$attr_id.' LIMIT 1;');
							}
							//если атрибут текстовый, то оставляем в значении всю первоначальную строку, если числовой - присваиваем приведенное к числу значение
							$attr_value=$attr_type==1?$_a_v:(float)str_replace(array(',',' '),array('.',''),$_a_vv);
							//проверяем значение атрибута (только для чекбокс и радио, текстовые)
							//если атрибут текстовый и его значение уже есть в базе, то используем его ID
							if($attr_type==1&&isset($attr_values_all[$_a_v]))
								$attr_value=$attr_values_all[$_a_v][0]['m_products_attributes_values_id'];
							//если нет - добавляем значение атрибута
							elseif($attr_type==1){
								$attr_value=get_id('m_products_attributes_values');
								$sql->query('INSERT INTO `m_products_attributes_values` SET
									`m_products_attributes_values_id`='.$attr_value.',
									`m_products_attributes_values_value`=\''.$_a_v.'\';');
							}
							$q='INSERT INTO `m_products_attributes` SET 
								`m_products_attributes_product_id`='.$product_id.',
								`m_products_attributes_list_id`='.$attr_id.',
								`m_products_attributes_value`=\''.$attr_value.'\';';
							$sql->query($q);
						}
					
					//ДОБАЛЯЕМ ФОТО
					if($_item['foto']){
						$_foto_files=array();
						$_foto_db=array();
						foreach($_item['foto'] as $_foto)
							$_foto_files[]=__DIR__.'/../../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$_foto;
						foreach($_foto_files as $_file)
							$_foto_db[]=json_decode($foto::loadProductFoto($_file,true,'1111111111'));
						//добавляем фото
						$product_foto=array();
						if($_foto_db){
							$mainfoto_id=0;
							//главная фотка - первая
							foreach($_foto_db as $v){
								if(is_object($v)){
									$v=$v->file;
									$mainfoto_id=$v->id;
									break;
								}
							}
							foreach($_foto_db as $v){
								if(is_object($v)){
									$v=$v->file;
									$product_foto[$v->id]['file']=$v->id;
									$product_foto[$v->id]['main']=($mainfoto_id==$v->id?1:0);
									//копируем только добавленные фотки
									if(!file_exists(__DIR__.'/../../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$v->id.'_min.jpg')){
										copy(__DIR__.'/../../www/temp/uploads/1111111111/'.$v->id.'_min.jpg',__DIR__.'/../../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$v->id.'_min.jpg');
										copy(__DIR__.'/../../www/temp/uploads/1111111111/'.$v->id.'_med.jpg',__DIR__.'/../../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$v->id.'_med.jpg');
										copy(__DIR__.'/../../www/temp/uploads/1111111111/'.$v->id.'_max.jpg',__DIR__.'/../../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$v->id.'_max.jpg');
									}
								}
							}
						}
						if($product_foto){	
							$product_foto=json_encode($product_foto,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
							$q='UPDATE `m_products` SET `m_products_foto`=\''.$product_foto.'\' WHERE `id`=\''.$product_id.'\' LIMIT 1;';
						}
						//не показываем товары, у которых нет фото
						else{
							$q='UPDATE `m_products` SET `m_products_show_site`=0 WHERE `id`='.$product_id.' LIMIT 1;';
						}
						$sql->query($q);
					}
					//если фото нет - отменяем показ товара
					else{
						$q='UPDATE `m_products` SET `m_products_show_site`=0 WHERE `id`='.$product_id.' LIMIT 1;';
						$sql->query($q);
					}
				}
			}
		}
		//ЕСЛИ ТОВАР ЕСТЬ - ОБНОВЛЯЕМ ЦЕНУ (ЕСЛИ ЦЕНА ИЗМЕНИЛАСЬ НЕ БОЛЕЕ 30%)
		elseif(abs($_item['price']-$res[0]['m_products_price_general'])<$res[0]['m_products_price_general']*.3){
			$q='UPDATE `m_products` SET 
				`m_products_price_general`='.($_item['price']*1.096).'
				WHERE `id`='.$res[0]['id'].' LIMIT 1;';
			$sql->query($q);
			//ЕСЛИ СТОИТ ПОМЕТКА ПРОВЕРКИ ТОВАРА
			if($res[0]['m_products_check_it']){
				//добавляем категории
				if($product_cat=addCategory(implode('|',$_item['category']))){
					$attributes=$sql->query('SELECT * FROM `m_products_attributes_list`;','m_products_attributes_list_name');
					$attr_values_all=$sql->query('SELECT * FROM `m_products_attributes_values`;','m_products_attributes_values_value');
					
					//добавляем товар
					$product_id=$res[0]['id'];
					
					//ОБНОВЛЯЕМ ДОКУМЕНТЫ К ТОВАРУ
					//если стоит пометка обновления или документы не добавлялись ранее
					if($update_docs||!$res[0]['m_products_docs']){
						$res[0]['m_products_docs']=$res[0]['m_products_docs']?$res[0]['m_products_docs']:'0';
						$q='SELECT * FROM `m_products_docs` WHERE 
							`m_products_docs_id` IN ('.str_replace('|',',',$res[0]['m_products_docs']).');';
						if($res_d=$sql->query($q)){
							foreach($res_d as $_d){
								$_d_file=__DIR__.'/../../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$_d['m_products_docs_filename'];
								if(file_exists($_d_file)) unlink($_d_file);
							}
							$sql->query('DELETE `m_products_docs` WHERE `m_products_docs_id` IN ('.str_replace('|',',',$res[0]['m_products_docs']).');');
						}
						$docs=array();
						foreach($_item['docs'] as $_doc){
							$file=__DIR__.'/../../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$_doc->url;
							//если файл существует и его расширение из разрешенных
							if(file_exists($file)){
								$ext=explode('.',$_doc->url);
								$ext=trim(array_pop($ext));
								if(in_array($ext,array('pdf','doc','jpg','xls'))){
									$hash=hash_file('md5',$file);
									$q='SELECT `m_products_docs_id` FROM `m_products_docs` WHERE `m_products_docs_filemd5`=\''.$hash.'\' LIMIT 1;';
									//если такой файл уже есть в базе
									if($res1=$sql->query($q)){
										//записываем его ID для товара
										$docs[]=$res1[0]['m_products_docs_id'];
										//удаляем файл
										if(unlink($file))
											echo 'DELETED '.$file.'<br/>';
									}
									//если файла нет - добаляем его в базу и записываем ID для товара
									else{
										$id=get_id('m_products_docs');
										$q='INSERT INTO `m_products_docs` SET 
											`m_products_docs_id`='.$id.',
											`m_products_docs_filename`=\''.$_doc->url.'\',
											`m_products_docs_filedir`='.$_item['id'].',
											`m_products_docs_filesize`=\''.get_filesize($file).'\',
											`m_products_docs_filemd5`=\''.$hash.'\',
											`m_products_docs_desc`=\''.val($_doc->name).'\';';
										if($sql->query($q))
											$docs[]=$id;
									}
								}
							}
						}
					}
					
					//СВЯЗАННЫЕ ТОВАРЫ
					$product_links=array();
					if($_item['links']){
						//находим id товаров в рабочей базе по id isolux
						$q='SELECT `id` FROM `m_products` WHERE `id_isolux` IN ('.implode(',',$_item['links']).');';
						if($res_links=$sql->query($q))
							foreach($res_links as $_res_links)
								$product_links[]=$_res_links['id'];
					}
					
					//ЕД. ИЗМЕРЕНИЯ (ПО УМОЛЧАНИЮ ШТ)
					$product_unit=3;
					foreach($units as $_unit)
						if($_item['unit']==$_unit['m_info_units_name'])
							$product_unit=$_unit['m_info_units_id'];		
					
					$q='UPDATE `m_products` SET 
						`id_isolux`=\''.$_item['id'].'\',
						`id`='.$product_cat.',
						`m_products_name`=\''.val($_item['price_name']).'\',
						`m_products_name_full`=\''.($_item['name']?val($_item['name']):val($_item['price_name'])).'\',
						`m_products_unit`='.$product_unit.',
						`m_products_links`=\''.($product_links?implode('|',$product_links):'').'\',
						`m_products_price_general`='.($_item['price']*1.096).',
						`m_products_multiplicity`='.($_item['miltiplicity']?$_item['miltiplicity']:1).',
						'.(isset($docs)&&$docs?'`m_products_docs`=\''.implode('|',$docs).'\',':'').'
						'.($_item['video']?'`m_products_video`=\''.val($_item['video']).'\',':'').'
						`m_products_update`=\''.dt().'\',
						`m_products_date`=\''.dt().'\' 
						WHERE `id`='.$product_id.' LIMIT 1;';
					if($sql->query($q)){
					
						//ОБНОВЛЯЕМ ТЕКСТ
						if($update_text){
							$q='INSERT INTO `m_products_desc` SET 
								`m_products_desc_id`='.$product_id.',
								`m_products_desc_text`=\''.str_replace(array('<div>','</div>','<a>','</a>','<p>&nbsp;</p>','</ol>'),array('','','','','','<span class="ol-fade"></span></ol>'),preg_replace("#(</?\w+)(?:\s(?:[^<>/]|/[^<>])*)?(/?>)#ui",'$1$2',$_item['description'])).'\' 
								ON DUPLICATE KEY UPDATE 
									`m_products_desc_text`=\''.str_replace(array('<div>','</div>','<a>','</a>','<p>&nbsp;</p>','</ol>'),array('','','','','','<span class="ol-fade"></span></ol>'),preg_replace("#(</?\w+)(?:\s(?:[^<>/]|/[^<>])*)?(/?>)#ui",'$1$2',$_item['description'])).'\';';
							$sql->query($q);
						}
						
						//ОБНОВЛЯЕМ АТРИБУТЫ
						if($update_attr){
							if($_item['attributes']){
								$sql->query('DELETE FROM `m_products_attributes` WHERE `m_products_attributes_product_id`='.$product_id.';');
								foreach($_item['attributes'] as $_attr_item){
									if(!isset($_attr_item->name)||!isset($_attr_item->value)) continue;
									$_a_n=val($_attr_item->name);
									$_a_v=val($_attr_item->value);
									$_a_h=val($_attr_item->hint);
									//если атрибут с пустым значением - переходим к следующему
									if(!$_a_v) continue;
									//разделяем по пробелу значение атрибута и ед. измерения
									$__a_v=explode(' ',$_a_v);
									//извлекаем значение атрибута (перед первым пробелом)
									$_a_vv=array_shift($__a_v);
									//соединяем обратно - все, что осталось, - ед. измерения
									$_a_vu=implode(' ',$__a_v);
									//узнаём типа атрибута по символам до первого пробела
									$attr_type=is_numeric(str_replace(array(',',' '),array('.',''),$_a_vv))?2:1;
									//если такого атрибута ещё не было или его тип не совпадает с тем, что был раньше для атрибутов с таким именем, добавляем новый атрибут с нужным типом
									//находим всевозможные типы атрибута с таким же именем
									if(isset($attributes[$_a_n])){
										$attr_id_type_index=0;
										$_a_types=array();
										//определяем индекс типа, соотвествующего типу проверяемого атрибута, в наборе существующих атрибутов
										foreach($attributes[$_a_n] as $_index=>$_existattr){
											if($_existattr['m_products_attributes_list_type']==$attr_type)
												$attr_id_type_index=$_index;
											$_a_types[]=$_existattr['m_products_attributes_list_type'];
										}
									}
									if(!isset($attributes[$_a_n])||(isset($attributes[$_a_n])&&!in_array($attr_type,$_a_types))){
										$attr_id=get_id('m_products_attributes_list');						
										$q='INSERT INTO `m_products_attributes_list` SET 
											`m_products_attributes_list_id`='.$attr_id.',
											`m_products_attributes_list_name`=\''.$_a_n.'\',
											`m_products_attributes_list_name_url`=\''.transform::translit($_a_n).'\',
											`m_products_attributes_list_type`='.$attr_type.',
											`m_products_attributes_list_hint`=\''.$_a_h.'\',
											`m_products_attributes_list_unit`=\''.($attr_type==1?'':$_a_vu).'\';';
										$sql->query($q);
									}
									//если атрибут с нужным именем и типом уже есть в базе, указываем его
									else{
										$attr_id=$attributes[$_a_n][$attr_id_type_index]['m_products_attributes_list_id'];
										//если у атрибута не добавлялась подсказка, но она есть - добавляем
										if(!$attributes[$_a_n][$attr_id_type_index]['m_products_attributes_list_hint']&&$_a_h)
											$sql->query('UPDATE `m_products_attributes_list` SET `m_products_attributes_list_hint`=\''.$_a_h.'\' WHERE `m_products_attributes_list_id`='.$attr_id.' LIMIT 1;');
									}
									//если атрибут текстовый, то оставляем в значении всю первоначальную строку, если числовой - присваиваем приведенное к числу значение
									$attr_value=$attr_type==1?$_a_v:(float)str_replace(array(',',' '),array('.',''),$_a_vv);
									//проверяем значение атрибута (только для чекбокс и радио, текстовые)
									//если атрибут текстовый и его значение уже есть в базе, то используем его ID
									if($attr_type==1&&isset($attr_values_all[$_a_v]))
										$attr_value=$attr_values_all[$_a_v][0]['m_products_attributes_values_id'];
									//если нет - добавляем значение атрибута
									elseif($attr_type==1){
										$attr_value=get_id('m_products_attributes_values');
										$sql->query('INSERT INTO `m_products_attributes_values` SET
											`m_products_attributes_values_id`='.$attr_value.',
											`m_products_attributes_values_value`=\''.$_a_v.'\';');
									}
									$q='INSERT INTO `m_products_attributes` SET 
										`m_products_attributes_product_id`='.$product_id.',
										`m_products_attributes_list_id`='.$attr_id.',
										`m_products_attributes_value`=\''.$attr_value.'\';';
									$sql->query($q);
								}
							}
						}
						
						//ОБНОВЛЯЕМ ФОТО
						//если стоит пометка обновления или фото не добавлялись ранее
						if($update_foto||!$res[0]['m_products_foto']){
							if($_item['foto']){
								$q='SELECT `m_products_foto` FROM `m_products` WHERE 
									`id`='.$product_id.' LIMIT 1;';
								if($res_f=$sql->query($q)){
									$res_f[0]['m_products_foto']=json_decode($res_f[0]['m_products_foto']);
									if($res_f[0]['m_products_foto'])
										foreach($res_f[0]['m_products_foto'] as $_f){
											$_f=$_f->file;
											$f_min=__DIR__.'/../../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$_f.'_min.jpg';
											$f_med=__DIR__.'/../../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$_f.'_med.jpg';
											$f_max=__DIR__.'/../../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$_f.'_max.jpg';
											if(file_exists($f_min)) unlink($f_min);
											if(file_exists($f_med)) unlink($f_med);
											if(file_exists($f_max)) unlink($f_max);
										}
								}
								$_foto_files=array();
								$_foto_db=array();
								foreach($_item['foto'] as $_foto)
									$_foto_files[]=__DIR__.'/../../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$_foto;
								print_r($_foto_files);
								foreach($_foto_files as $_file)
									$_foto_db[]=json_decode($foto::loadProductFoto($_file,true,'1111111111'));
								//добавляем фото
								$product_foto=array();
								if($_foto_db){
									$mainfoto_id=0;
									//главная фотка - первая
									foreach($_foto_db as $v){
										if(is_object($v)){
											$v=$v->file;
											$mainfoto_id=$v->id;
											break;
										}
									}
									foreach($_foto_db as $v){
										if(is_object($v)){
											$v=$v->file;
											$product_foto[$v->id]['file']=$v->id;
											$product_foto[$v->id]['main']=($mainfoto_id==$v->id?1:0);
											//копируем только добавленные фотки
											if(!file_exists(__DIR__.'/../../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$v->id.'_min.jpg')){
												copy(__DIR__.'/../../www/temp/uploads/1111111111/'.$v->id.'_min.jpg',__DIR__.'/../../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$v->id.'_min.jpg');
												copy(__DIR__.'/../../www/temp/uploads/1111111111/'.$v->id.'_med.jpg',__DIR__.'/../../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$v->id.'_med.jpg');
												copy(__DIR__.'/../../www/temp/uploads/1111111111/'.$v->id.'_max.jpg',__DIR__.'/../../../p-islx.Formetoo.ru/documents/'.substr($_item['id'],0,2).'/SN'.$_item['id'].'/'.$v->id.'_max.jpg');
											}
										}
									}
								}
								if($product_foto){	
									$product_foto=json_encode($product_foto,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
									$q='UPDATE `m_products` SET `m_products_show_site`=1,`m_products_check_it`=0,`m_products_foto`=\''.$product_foto.'\' WHERE `id`=\''.$product_id.'\' LIMIT 1;';
								}
								//не показываем товары, у которых нет фото
								else{
									$q='UPDATE `m_products` SET `m_products_show_site`=0 WHERE `id`='.$product_id.' LIMIT 1;';
								}
								$sql->query($q);
							}
							//если фото нет - отменяем показ товара
							else{
								$q='UPDATE `m_products` SET `m_products_show_site`=0 WHERE `id`='.$product_id.' LIMIT 1;';
								$sql->query($q);
							}
						}
					}
				}
			}
		}
		//ЕСЛИ ЦЕНА СИЛЬНО ИЗМЕНИЛАСЬ - НЕ ПОКАЗЫВЕМ ТОВАР НА САЙТЕ И СТАВИМ У НЕГО МЕТКУ ПРОВЕРКИ
		else{
			$q='UPDATE `m_products` SET 
				`m_products_price_general`='.($_item['price']*1.096).',
				`m_products_check_it`=1,
				`m_products_show_site`=0 
				WHERE `id`='.$res[0]['id'].' LIMIT 1;';
			$sql->query($q);
			
		}
	}
	
echo '<a href="http://crm.formetoo.ru/migrate-isolux/migrate-isolux.php?start='.($start+$length).'">Продолжить с позиции '.($start+$length).'</a>';

function addCategory($_cat){
	global $sql;
	if(strpos($_cat,'Главная')===false){
		return false;
	}
	if(strpos($_cat,'Краны шаровые&nbsp;')!==false)
		return $id_cat=2564271711;
	
	$_cat=explode('|',$_cat);
	array_shift($_cat);
	foreach($_cat as &$item){
		$a=array();
		$a['id']=0;
		$a['name']=trim($item);
		$item=$a;
	}
	$id_cat=0;
	$id_menu=2000000000;
	//для каждой категории текущего товара
	for($i=0;$i<sizeof($_cat);$i++){
		$q='';
		//пробегаемся обратно от неё до родительской, смотрим есть ли нужный ID по совокупности имен родительских категорий
		for($j=$i;$j>=0;$j--)
			$q.='SELECT `id` FROM `m_products_categories` WHERE 
				`m_products_categories_name`=\''.$_cat[$j]['name'].'\' AND 
				`m_products_categories_parent`=(';
		//для первой категории родительская = 0
		$q=substr($q,0,-1).'0';
		//закрываем скобки
		for($k=0;$k<$i;$k++)
			$q.=')';
		//если категории пока нет - добавляем ее, используя как родительский предыдущий присвоенный id	
		if(!$res=$sql->query($q)){
			$q='INSERT INTO `m_products_categories` SET 
				`m_products_categories_parent`='.($id_cat?$id_cat:0).',
				`id`='.($id_cat=get_id('m_products_categories')).',
				`m_products_categories_name`=\''.$_cat[$i]['name'].'\',
				`m_products_categories_name_seo`=\''.transform::translit($_cat[$i]['name']).'\';';
			if($sql->query($q)){
				//добавляем запись категории в таблицу меню
				$q='INSERT INTO `menu` SET 
					`parent`='.($id_menu?$id_menu:2000000000).',
					`id`='.($id_menu=get_id('menu',0,'id')).',
					`name`=\''.$_cat[$i]['name'].'\',
					`url`=\''.transform::translit($_cat[$i]['name']).'\',
					`type`=\'top|top-catalog\',
					`category`='.$id_cat.';';
				//добавляем запись категории в таблицу контента
				if($sql->query($q)){
					$q='INSERT INTO `content` SET 
						`menu`='.$id_menu.',
						`id`='.get_id('content',0,'id').';';
					if($sql->query($q)){
						echo 'Добавлена категория ';
						print_r($_cat);
						echo '<br/>';
					}
				}
			}
		}
		//если категория есть - сохраняем как текущий её ID, чтобы присвоить новым дочерним категориям, то же самое с menu_id
		else{
			$id_cat=$res[0]['id'];
			$q='SELECT `id` FROM `menu` WHERE 
				`category`='.$id_cat.' LIMIT 1;';
			if($res=$sql->query($q))
				$id_menu=$res[0]['id'];
		}
	}
	//возвращаем ID самой дочерней категории
	return $id_cat;
}
?>
</div>