<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
$sql=new sql();
require_once('../../functions/classes/user.php');
$user=new user;
require_once('../../functions/classes/services.php');
$services=new services;
require_once('../../functions/classes/products.php');
$products=new products;
require_once('../../functions/classes/documents.php');
$documents=new documents;

$data['name']=array(1,null,100);
$data['pk']=array(1,null,null,10,1);
$data['value']=array(1);

array_walk($data,'check');

if(!$e){
	$q='';
	switch($data['name']){
		
//УСЛУГИ
		//изменение организации
		case 'm_services_contragents_id':
			$q='UPDATE `formetoo_main`.`m_services` SET 
				`m_services_contragents_id`=\''.$data['value'].'\' 
				WHERE `m_services_id`='.$data['pk'].' LIMIT 1;';
			break;
		//изменение наименования
		case 'm_services_name':
			$q='UPDATE `formetoo_main`.`m_services` SET 
				`m_services_name`=\''.$data['value'].'\' 
				WHERE `m_services_id`='.$data['pk'].' LIMIT 1;';
			break;
		//изменение единицы измерения
		case 'm_services_unit':
			$q='UPDATE `formetoo_main`.`m_services` SET 
				`m_services_unit`=\''.$data['value'].'\' 
				WHERE `m_services_id`='.$data['pk'].' LIMIT 1;';
			break;
		//изменение цены
		case 'm_services_price_general':
			$data['value']=str_replace(array(' ',','),array('','.'),$data['value']);
			$q='UPDATE `formetoo_main`.`m_services` SET 
				`m_services_price_general`='.$data['value'].' 
				WHERE `m_services_id`='.$data['pk'].' LIMIT 1;';
			break;
		case 'm_services_price_general_w':
			$data['value']=str_replace(array(' ',','),array('','.'),$data['value']);
			$q='UPDATE `formetoo_main`.`m_services` SET 
				`m_services_price_general_w`='.$data['value'].' 
				WHERE `m_services_id`='.$data['pk'].' LIMIT 1;';
			break;
		case 'm_services_price_wholesale':
			$data['value']=str_replace(array(' ',','),array('','.'),$data['value']);
			$q='UPDATE `formetoo_main`.`m_services` SET 
				`m_services_price_wholesale`='.$data['value'].' 
				WHERE `m_services_id`='.$data['pk'].' LIMIT 1;';
			break;
		case 'm_services_price_wholesale_w':
			$data['value']=str_replace(array(' ',','),array('','.'),$data['value']);
			$q='UPDATE `formetoo_main`.`m_services` SET 
				`m_services_price_wholesale_w`='.$data['value'].' 
				WHERE `m_services_id`='.$data['pk'].' LIMIT 1;';
			break;
		//изменение связанных работ
		case 'm_services_links':
			$q='UPDATE `formetoo_main`.`m_services` SET 
				`m_services_links`=\''.$data['value'].'\' 
				WHERE `m_services_id`='.$data['pk'].' LIMIT 1;';
			break;
		/* //изменение категорий
		case 'm_services_categories_id':
			$q='UPDATE `formetoo_main`.`m_services` SET 
				`m_services_categories_id`=\''.$data['value'].'\' 
				WHERE `m_services_id`='.$data['pk'].' LIMIT 1;';
			break; */
		//изменение значения показа в прайсе
		case 'm_services_show_price':
			$data['value']=$data['value']=='true'?1:0;
			$q='UPDATE `formetoo_main`.`m_services` SET 
				`m_services_show_price`='.$data['value'].'
				WHERE `m_services_id`='.$data['pk'].' LIMIT 1;';
			break;
		//изменение значения показа на сайте
		case 'm_services_show_site':
			$data['value']=$data['value']=='true'?1:0;
			$q='UPDATE `formetoo_main`.`m_services` SET 
				`m_services_show_site`='.$data['value'].'
				WHERE `m_services_id`='.$data['pk'].' LIMIT 1;';
			break;
		//изменение порядкового номера
		case 'm_services_order':
			$data['value']=str_replace(array(' ',','),array('','.'),$data['value']);
			$q='UPDATE `formetoo_main`.`m_services` SET 
				`m_services_order`='.$data['value'].'
				WHERE `m_services_id`='.$data['pk'].' LIMIT 1;';
			break;
		//удаление услуги
		case 'm_services_id':
			if($data['value']==fmt)
				$q='DELETE FROM `formetoo_main`.`m_services` WHERE `m_services_id`='.$data['pk'].' LIMIT 1;';
			else{
				header('HTTP 400 Bad Request',true,400);
				echo "неверный пароль".$q;
			}
			break;
			
//ТОВАРЫ
		//изменение цены
		case 'm_products_price_general':
			$data['value']=str_replace(array(' ',','),array('','.'),$data['value']);
			$q='UPDATE `formetoo_main`.`m_products` SET 
				`m_products_price_general`='.$data['value'].' 
				WHERE `m_products_id`='.$data['pk'].' LIMIT 1;';
			break;
		//изменение значения показа в прайсе
		case 'm_products_show_price':
			$data['value']=$data['value']=='true'?1:0;
			$q='UPDATE `formetoo_main`.`m_products` SET 
				`m_products_show_price`='.$data['value'].'
				WHERE `m_products_id`='.$data['pk'].' LIMIT 1;';
			break;
		//изменение значения показа на сайте
		case 'm_products_show_site':
			$data['value']=$data['value']=='true'?1:0;
			$q='UPDATE `formetoo_main`.`m_products` SET 
				`m_products_show_site`='.$data['value'].'
				WHERE `m_products_id`='.$data['pk'].' LIMIT 1;';
			break;
		//изменение порядкового номера
		case 'm_products_order':
			$q='UPDATE `formetoo_main`.`m_products` SET 
				`m_products_order`='.$data['value'].'
				WHERE `m_products_id`='.$data['pk'].' LIMIT 1;';
			break;
		//изменение порядкового номера
		case 'm_products_check_it':
			$q='UPDATE `formetoo_main`.`m_products` SET 
				`m_products_check_it`=1
				WHERE `m_products_id`='.$data['pk'].' LIMIT 1;';
			break;	
		//удаление товара
		case 'm_products_id':
			if($data['value']==fmt)
				$q='DELETE FROM `formetoo_main`.`m_products` WHERE `m_products_id`='.$data['pk'].' LIMIT 1;';
			else{
				header('HTTP 400 Bad Request',true,400);
				echo "неверный пароль".$q;
			}
			break;
		

//АТРИБУТЫ ТОВАРОВ
	//изменение значения чекбоксов
		case 'm_products_attributes_list_required':
			$data['value']=$data['value']=='true'?1:0;
			$q='UPDATE `formetoo_main`.`m_products_attributes_list` SET 
				`m_products_attributes_list_required`='.$data['value'].'
				WHERE `m_products_attributes_list_id`='.$data['pk'].' LIMIT 1;';
			break;
		case 'is_multiply':
			$data['value']=$data['value']=='true'?1:0;
			$q='UPDATE `formetoo_main`.`m_products_attributes_list` SET 
				`is_multiply`='.$data['value'].'
				WHERE `m_products_attributes_list_id`='.$data['pk'].' LIMIT 1;';
			break;
		case 'm_products_attributes_list_site_search':
			$data['value']=$data['value']=='true'?1:0;
			$q='UPDATE `formetoo_main`.`m_products_attributes_list` SET 
				`m_products_attributes_list_site_search`='.$data['value'].'
				WHERE `m_products_attributes_list_id`='.$data['pk'].' LIMIT 1;';
			break;
		case 'm_products_attributes_list_site_filter':
			$data['value']=$data['value']=='true'?1:0;
			$q='UPDATE `formetoo_main`.`m_products_attributes_list` SET 
				`m_products_attributes_list_site_filter`='.$data['value'].'
				WHERE `m_products_attributes_list_id`='.$data['pk'].' LIMIT 1;';
			break;
		case 'm_products_attributes_list_site_open':
			$data['value']=$data['value']=='true'?1:0;
			$q='UPDATE `formetoo_main`.`m_products_attributes_list` SET 
				`m_products_attributes_list_site_open`='.$data['value'].'
				WHERE `m_products_attributes_list_id`='.$data['pk'].' LIMIT 1;';
			break;
		case 'is_active':
			$data['value']=$data['value']=='true'?1:0;
			$q='UPDATE `formetoo_main`.`m_products_attributes_list` SET 
				`is_active`='.$data['value'].'
				WHERE `m_products_attributes_list_id`='.$data['pk'].' LIMIT 1;';
			break;
		//удаление атрибута
		case 'm_products_attributes_list_id':
			if($data['value']==fmt)
				$q='DELETE FROM `formetoo_main`.`m_products_attributes_list` WHERE `m_products_attributes_list_id`='.$data['pk'].' LIMIT 1;';
			else{
				header('HTTP 400 Bad Request',true,400);
				echo "неверный пароль".$q;
			}
			break;
		//удаление группы
		case 'products_attributes_groups_id':
			if($data['value']==fmt)
				$q='DELETE FROM `formetoo_main`.`m_products_attributes_groups` WHERE `products_attributes_groups_id`='.$data['pk'].' LIMIT 1;';
			else{
				header('HTTP 400 Bad Request',true,400);
				echo "неверный пароль".$q;
			}
			break;
		
//КАТЕГОРИИ УСЛУГ			
		//изменение имени категории услуги
		case 'm_services_categories_name':
			$q='UPDATE `formetoo_main`.`m_services_categories` SET 
				`m_services_categories_name`=\''.$data['value'].'\' 
				WHERE `m_services_categories_id`='.$data['pk'].' LIMIT 1;';
			break;
		//изменение значения показа категории услуги в прайсе
		case 'm_services_categories_show_goods':
			$data['value']=$data['value']=='true'?1:0;
			$q='UPDATE `formetoo_main`.`m_services_categories` SET 
				`m_services_categories_show_goods`='.$data['value'].'
				WHERE `m_services_categories_id`='.$data['pk'].' LIMIT 1;';
			break;
		//изменение значения показа категории услуги на сайте
		case 'm_services_categories_show_site':
			$data['value']=$data['value']=='true'?1:0;
			$q='UPDATE `formetoo_main`.`m_services_categories` SET 
				`m_services_categories_show_site`='.$data['value'].'
				WHERE `m_services_categories_id`='.$data['pk'].' LIMIT 1;';
			break;
		//удаление категории услуги
		case 'm_services_categories_id':
			if($data['value']==fmt)
				$q='DELETE FROM `formetoo_main`.`m_services_categories` WHERE `m_services_categories_id`='.$data['pk'].' LIMIT 1;';
			else{
				header('HTTP 400 Bad Request',true,400);
				echo "неверный пароль".$q;
			}
			break;
		//изменение порядковых номеров и структуры категорий услуги
		case 'list':
			//print_r(json_decode(html_entity_decode($data['value'],ENT_COMPAT,'utf-8'),1));
			function categories($categories,$parent=0){
				global $services,$sql;
				foreach($categories as $k=>$v){
					//в БД порядковый номер начинается с 1
					$k++;
					$q='UPDATE `formetoo_main`.`m_services_categories` SET ';
					//если порядковй номер текущего пункта обновился
					if($services->categories_nodes_id[$v['id']]['m_services_categories_order']!=$k)
						$q.='`m_services_categories_order`='.$k;
					//если родительский пункт обновился
					if($services->categories_nodes_id[$v['id']]['m_services_categories_parent']!=$parent)
						$q.=($services->categories_nodes_id[$v['id']]['m_services_categories_order']!=$k?',':'').'`m_services_categories_parent`='.$parent;
					$q.=' WHERE `m_services_categories_id`='.$v['id'].' LIMIT 1;';
					//если есть изменения - выполняем запрос
					if(strlen($q)>88)
						$sql->query($q);
					//если у каетгории есть дочерние категории - рекурсивно проверяем их
					if(isset($v['children']))
						categories($v['children'],$v['id']);
				}
			}
			categories(json_decode(html_entity_decode($data['value'],ENT_COMPAT,'utf-8'),1));
			break;
			
//КАТЕГОРИИ ТОВАРОВ
		//изменение имени категории товара
		case 'm_products_categories_name':
			$q='UPDATE `formetoo_main`.`m_products_categories` SET 
				`m_products_categories_name`=\''.$data['value'].'\',
				`m_products_categories_name_seo`=\''.transform::translit($data['value']).'\'		
				WHERE `m_products_categories_id`='.$data['pk'].' LIMIT 1;';
			break;
		case 'm_products_categories_show_goods':
			$data['value']=$data['value']=='true'?1:0;
			$q='UPDATE `formetoo_main`.`m_products_categories` SET 
				`m_products_categories_show_goods`='.$data['value'].'
				WHERE `m_products_categories_id`='.$data['pk'].' LIMIT 1;';
			break;
		case 'm_products_categories_show_attributes':
			$data['value']=$data['value']=='true'?1:0;
			$q='UPDATE `formetoo_main`.`m_products_categories` SET 
				`m_products_categories_show_attributes`='.$data['value'].'
				WHERE `m_products_categories_id`='.$data['pk'].' LIMIT 1;';
			break;
		case 'm_products_categories_show_categories':
			$data['value']=$data['value']=='true'?1:0;
			$q='UPDATE `formetoo_main`.`m_products_categories` SET 
				`m_products_categories_show_categories`='.$data['value'].'
				WHERE `m_products_categories_id`='.$data['pk'].' LIMIT 1;';
			break;
		//удаление категории товара
		case 'm_products_categories_id':
			if($data['value']==fmt)
				$q='DELETE FROM `formetoo_main`.`m_products_categories` WHERE `m_products_categories_id`='.$data['pk'].' LIMIT 1;';
			else{
				header('HTTP 400 Bad Request',true,400);
				echo "неверный пароль".$q;
			}
			break;
		//изменение порядковых номеров и структуры категорий товара
		case 'products_list':
			function categories($categories,$parent=0){
				global $products,$sql;
				foreach($categories as $k=>$v){
					//в БД порядковый номер начинается с 1
					$k++;
					$q='UPDATE `formetoo_main`.`m_products_categories` SET ';
					//если порядковй номер текущего пункта обновился
					if($products->categories_nodes_id[$v['id']]['m_products_categories_order']!=$k)
						$q.='`m_products_categories_order`='.$k;
					//если родительский пункт обновился
					if($products->categories_nodes_id[$v['id']]['m_products_categories_parent']!=$parent)
						$q.=($products->categories_nodes_id[$v['id']]['m_products_categories_order']!=$k?',':'').'`m_products_categories_parent`='.$parent;
					$q.=' WHERE `m_products_categories_id`='.$v['id'].' LIMIT 1;';
					//если есть изменения - выполняем запрос
					if(strlen($q)>88)
						$sql->query($q);
					//если у каетгории есть дочерние категории - рекурсивно проверяем их
					if(isset($v['children']))
						categories($v['children'],$v['id']);
				}
			}
			categories(json_decode(html_entity_decode($data['value'],ENT_COMPAT,'utf-8'),1));
			break;
			
//ГРУППЫ ПОЛЬЗОВАТЕЛЕЙ			
			//изменение названия у групп пользователей
			case 'm_users_groups_name':
				$q='UPDATE `formetoo_main`.`m_users_groups` SET 
					`m_users_groups_name`=\''.$data['value'].'\' 
					WHERE `m_users_groups_id`='.$data['pk'].' LIMIT 1;';
				break;
			//изменение разрешений у групп пользователей
			case 'm_users_groups_rights_read':
				$q='UPDATE `formetoo_main`.`m_users_groups` SET 
					`m_users_groups_rights_read`=\''.$data['value'].'\' 
					WHERE `m_users_groups_id`='.$data['pk'].' LIMIT 1;';
				break;
			//изменение разрешений у групп пользователей
			case 'm_users_groups_rights_change':
				$q='UPDATE `formetoo_main`.`m_users_groups` SET 
					`m_users_groups_rights_change`=\''.$data['value'].'\' 
					WHERE `m_users_groups_id`='.$data['pk'].' LIMIT 1;';
				break;
			//изменение разрешений у групп пользователей
			case 'm_users_groups_rights_delete':
				$q='UPDATE `formetoo_main`.`m_users_groups` SET 
					`m_users_groups_rights_delete`=\''.$data['value'].'\' 
					WHERE `m_users_groups_id`='.$data['pk'].' LIMIT 1;';
				break;
			//изменение разрешений у групп пользователей
			case 'm_users_groups_rights_create':
				$q='UPDATE `formetoo_main`.`m_users_groups` SET 
					`m_users_groups_rights_create`=\''.$data['value'].'\' 
					WHERE `m_users_groups_id`='.$data['pk'].' LIMIT 1;';
				break;
			//изменение разрешений у групп пользователей
			case 'm_users_groups_rights_myself':
				$q='UPDATE `formetoo_main`.`m_users_groups` SET 
					`m_users_groups_rights_myself`=\''.$data['value'].'\' 
					WHERE `m_users_groups_id`='.$data['pk'].' LIMIT 1;';
				break;
			//удаление группы
			case 'm_users_groups_id':
				if($data['value']==fmt)
					$q='DELETE FROM `formetoo_main`.`m_users_groups` WHERE `m_users_groups_id`='.$data['pk'].' LIMIT 1;';
				else{
					header('HTTP 400 Bad Request',true,400);
					echo "неверный пароль".$q;
				}
				break;
//ПОЛЬЗОВАТЕЛИ				
			//изменение группы пользователя
			case 'm_users_group':
				$q='UPDATE `formetoo_main`.`m_users` SET 
					`m_users_group`=\''.$data['value'].'\' 
					WHERE `m_users_id`='.$data['pk'].' LIMIT 1;';
				break;
			//изменение активности пользователя
			case 'm_users_active':
				$data['value']=$data['value']=='true'?1:0;
				$q='UPDATE `formetoo_main`.`m_users` SET 
					`m_users_active`='.$data['value'].'
					WHERE `m_users_id`='.$data['pk'].' LIMIT 1;';
				break;
			//изменение пароля пользователя
			case 'm_users_password':
				$q='UPDATE `formetoo_main`.`m_users` SET 
					`m_users_password`=\''.user::getHash($data['value']).'\'
					WHERE `m_users_id`='.$data['pk'].' LIMIT 1;';
				break;
			//удаление пользователя
			case 'm_users_id':
				if($data['value']==fmt)
					$q='DELETE FROM `formetoo_main`.`m_users` WHERE `m_users_id`='.$data['pk'].' LIMIT 1;';
				else{
					header('HTTP 400 Bad Request',true,400);
					echo "неверный пароль".$q;
				}
				break;
				
//СОТРУДНИКИ				
			//удаление сотрудника
			case 'm_employees_id':
				if($data['value']==fmt)
					$q='DELETE FROM `formetoo_cdb`.`m_employees` WHERE `m_employees_id`='.$data['pk'].' LIMIT 1;';
				else{
					header('HTTP 400 Bad Request',true,400);
					echo "неверный пароль".$q;
				}
				break;

//КОНТРАГЕНТЫ				
			//удаление контрагента
			case 'm_contragents_id':
				if($data['value']==fmt)
					$q='DELETE FROM `formetoo_cdb`.`m_contragents`,`formetoo_main`.`m_users` USING `formetoo_cdb`.`m_contragents`,`formetoo_main`.`m_users`  WHERE `m_contragents`.`m_contragents_id`=`m_users`.`m_users_id` AND `m_users`.`m_users_id`='.$data['pk'].';';
				else{
					header('HTTP 400 Bad Request',true,400);
					echo "неверный пароль".$q;
				}
				break;
			//изменение типа контрагента
			case 'm_contragents_type':
				$q='UPDATE `formetoo_cdb`.`m_contragents` SET 
					`m_contragents_type`=\''.$data['value'].'\' 
					WHERE `m_contragents_id`='.$data['pk'].' LIMIT 1;';
				break;
				
//ДОКУМЕНТЫ				
			//удаление
			case 'm_documents_id':
				if($data['value']==fmt){
					$q='DELETE FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$data['pk'].' LIMIT 1;';
					$docs=$documents->getInfo($data['pk']);
					$type=$documents->documents_templates[$docs['m_documents_templates_id']][0];
					$file=$_SERVER['DOCUMENT_ROOT'].'/files/'.$type['m_documents_templates_folder'].'/'.$docs['m_documents_folder'].'/'.$type['m_documents_templates_filename'].'.pdf';
					$dir=$file=$_SERVER['DOCUMENT_ROOT'].'/files/'.$type['m_documents_templates_folder'].'/'.$docs['m_documents_folder'];
					//if(file_exists($file))
						//file::deldir($dir);
				}
				else{
					header('HTTP 400 Bad Request',true,400);
					echo "неверный пароль".$q;
				}
				break;
				
//ЗАКАЗЫ				
			//удаление заказа
			case 'm_orders_id':
				if($data['value']==fmt)
					$q='DELETE FROM `formetoo_cdb`.`m_orders` WHERE `m_orders_id`='.$data['pk'].' LIMIT 1;';
				else{
					header('HTTP 400 Bad Request',true,400);
					echo "неверный пароль".$q;
				}
				break;
			//изменение статуса заказа услуги
			case 'm_orders_status':
				$data['value']=$data['value']>=8?8:$data['value'];
				$q='UPDATE `formetoo_cdb`.`m_orders` SET 
					`m_orders_status`=\''.$data['value'].'\' 
					WHERE `m_orders_id`='.$data['pk'].' LIMIT 1;';
				break;

//БУХГАЛТЕРИЯ
			//удаление
			case 'm_buh_id':
				if($data['value']==fmt)
					$q='DELETE FROM `formetoo_cdb`.`m_buh` WHERE `m_buh_id`='.$data['pk'].' LIMIT 1;';
				else{
					header('HTTP 400 Bad Request',true,400);
					echo "неверный пароль".$q;
				}
				break;
				
		default:
			break;
	}
	if($q&&$sql->query($q))
		echo 'true';
}
else{
	header('HTTP 400 Bad Request',true,400);
	echo "ошибка";
}
?>