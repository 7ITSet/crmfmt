<?
defined ('_DSITE') or die ('Access denied');
global $folder;

require_once(__DIR__.'/img_codes.php');

class documents{

	public 
		$documents_id,
		$documents_templates;

	function __construct(){
		global $sql;

		$q='SELECT * FROM `formetoo_cdb`.`m_documents` ORDER BY `m_documents_date` DESC,`m_documents_order`,`m_documents_templates_id`;';
		$this->documents_id=$sql->query($q,'m_documents_id');
	
		$q='SELECT * FROM `formetoo_cdb`.`m_documents_templates` ORDER BY `m_documents_templates_name`,`m_documents_templates_version` DESC;';
		$this->documents_templates=$sql->query($q,'m_documents_templates_id');

	}
	
	public function getInfo($id=''){
		return $id?$this->documents_id[$id][0]:$this->documents_id;
	}
	
	public function getOrderDocs($order=''){
		$result=array();
		foreach($this->documents_id as $_doc)
			if($_doc[0]['m_documents_order']==$order)
				$result[]=$_doc[0];
		return $result;
	}
	
	//шаблоны документов, нужных для отчетности
	public function getDocsBuh(){
		$result=array();
		foreach($this->documents_templates as $_template)
			if($_template[0]['m_documents_templates_buh'])
				$result[]=$_template[0];
		return $result;
	}
	
	//документы по платежу
	public function getPayDocs($pay=''){
		$result=array();
		foreach($this->documents_id as $_doc){
			$_doc[0]['m_documents_pays']=explode("|",$_doc[0]['m_documents_pays']);
			if(in_array($pay,$_doc[0]['m_documents_pays']))
				$result[]=$_doc[0];
		}
			
		return $result;
	}
	
	public static function getSignature($contragent,$type='stamp'){
	global $contragents;
		switch($type){
			case 'director':
				if($contragents->getInfo($contragent)!==null&&$contragents->getInfo($contragent)['m_contragents_c_signature_director'])
					$img=img::alfa(__DIR__.'/../../img_signature/'.$contragent.'/director.png');
				break;
			case 'bookkeeper':
				if($contragents->getInfo($contragent)!==null&&$contragents->getInfo($contragent)['m_contragents_c_signature_bookkeeper'])
					$img=img::alfa(__DIR__.'/../../img_signature/'.$contragent.'/bookkeeper.png');
				break;
			case 'responsible':
				if($contragents->getInfo($contragent)!==null&&$contragents->getInfo($contragent)['m_contragents_c_signature_responsible'])
					$img=img::alfa(__DIR__.'/../../img_signature/'.$contragent.'/responsible.png');
				break;
			case 'logo':
			case 'logo_big_www':
			case 'logo_big':
				if($contragents->getInfo($contragent)!==null)
					$img=img::alfa(__DIR__.'/../../img_signature/'.$contragent.'/logo_big.png');
				break;
			case 'stamp':
			default:
				if($contragents->getInfo($contragent)!==null&&$contragents->getInfo($contragent)['m_contragents_c_signature_stamp'])
					$img=img::alfa(__DIR__.'/../../img_signature/'.$contragent.'/stamp.png');
				break;
		}
		if(isset($img)){
			header('Content-Type: image/png');
			imagepng($img);
			imagedestroy($img);
		}
		exit;
	}

	
	public static function copy(){
	global $user,$services,$contragents,$info,$documents,$sql,$e;
		
		$data['m_documents_id']=array(1,null,null,10,1);
		
		array_walk($data,'check',true);
		
		if(!$e){
			$document=$documents->getInfo($data['m_documents_id']);
			$id=get_id('m_documents');
			
			//папка
			$foldername=md5(time().$id);
			mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
			codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
			
			$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
				`m_documents_id`='.$id.',
				`m_documents_performer`='.$document['m_documents_performer'].',
				`m_documents_customer`='.$document['m_documents_customer'].',
				`m_documents_order`='.$document['m_documents_order'].',
				`m_documents_templates_id`='.$document['m_documents_templates_id'].',
				`m_documents_numb`=\''.$id.'\',
				`m_documents_date`=\''.dt().'\',
				`m_documents_signature`='.$document['m_documents_signature'].',
				`m_documents_bar`='.$document['m_documents_bar'].',
				`m_documents_params`=\''.str_replace('\\r\\n','\\\r\\\n',$document['m_documents_params']).'\',
				`m_documents_update`=\''.dt().'\',
				`m_documents_comment`=\'[КОПИЯ] '.$document['m_documents_comment'].'\',
				`m_documents_filesize`=\''.$document['m_documents_filesize'].'\',
				`m_documents_folder`=\''.$foldername.'\';';
			
			if($sql->query($q)){

				copy(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$document['m_documents_folder'].'/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf',__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf');
				
				header('Location: /documents/new/?copy_success&action=details&m_documents_id='.$id.'&m_documents_templates_id='.$document['m_documents_templates_id'].'&m_documents_order='.$document['m_documents_order']);
				
			}
			else{
				header('Location: '.url().'error');
			}
			
		}
		else{
			elogs();
			header('Location: '.url().'?error');
		}
	}
	
	public static function add($params=array()){
	global $user,$services,$contragents,$info,$documents;
		switch($params['m_documents_templates_id']){
			//прайс-лист
			case '7521205786':
				global $sql,$e;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_itemslist']=array(null,null,3);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_doc_message_info']=array(null,null,400);
				$data['m_services_categories_id[]']=array();
				
				array_walk($data,'check');
				
				if(!$e){
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:get_id('m_documents');
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_itemslist']=$data['m_documents_itemslist']?1:0;
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;

					$id=get_id('m_documents');
					
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
					
					$items=array();
					//категории и услуги прайса
					$categories_all=array();
					if(!$data['m_services_categories_id[]'])
						foreach($services->categories_nodes_id as $k=>$v)
							$data['m_services_categories_id[]'][]=$k.'';

					
					foreach($data['m_services_categories_id[]'] as $_categories){
						//находим все дочерние категории данной категории
						$child_c=array();
						$services->categories_childs($_categories,$child_c);
						//если у категории есть подкатегории - добавляем их id в массив
						if($child_c)
							foreach($child_c as &$_c)
								$_c=$_c['m_services_categories_id'];
						//если нет подкатегорий, но есть позиции - добавляем id в массив
						elseif($services->categories_services($_categories))
							$categories_all[]=$_categories;
						$categories_all=array_merge($categories_all,$child_c);
					}
					$categories_all=array_unique($categories_all);

					//пробегаемся по указанным для документа категориям
					foreach($categories_all as $_categories){
						$i=0;
						$n=array();
						//находим все родительские категории
						$services->categories_parents($_categories,$n);
						$items[$_categories]['info']=$services->categories_nodes_id[$_categories];
						$items[$_categories]['parents']=$n;
						
						//добавляем элементы прайса, которые находятся в выбранных категориях
						foreach($services->services_id as $_service){
							$_service=$_service[0];
							$c=explode('|',$_service['m_services_categories_id']);
							
							if(in_array($_categories,$c)){
								$items[$_categories]['items'][$i]=$_service;
								unset($items[$_categories]['items'][$i]['m_services_products']);
								$items[$_categories]['items'][$i]['m_services_products']=json_decode($_service['m_services_products'],true);
								$i++;
							}
						}
					}
					
					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"org":"'.$data['m_documents_performer'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_itemslist":"'.$data['m_documents_itemslist'].'",
							"doc_message_info":"'.$data['m_documents_doc_message_info'].'",
							"doc_logo":"logo_docs.png",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_address_f'].'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';
				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){
					
						$q='SELECT * FROM `m_documents` WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$d=$sql->query($q)[0];
						
						$params=json_decode($d['m_documents_params']);
						
						$filesize=documents::price_services(
							array(
								'org_name'=>$params->org_name,
								'org_address'=>$params->org_address,
								'org_tel'=>$params->org_tel,
								'org_director_post'=>$params->org_director_post,
								'org_director_name'=>$params->org_director_name,
								'org_director_signature'=>$params->org_director_signature,
								'org_stamp'=>$params->org_stamp,
								'doc_date'=>$params->doc_date,
								'doc_itemslist'=>$params->doc_itemslist,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'doc_message_info'=>$params->doc_message_info,
								'doc_logo'=>$params->doc_logo,
								'doc_header_org_name'=>$params->doc_header_org_name,
								'doc_header_org_address'=>$params->doc_header_org_address,
								'doc_header_org_tel'=>$params->doc_header_org_tel,
								'doc_header_org_email'=>$params->doc_header_org_email,
								'items'=>$params->items
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);

						header('Location: '.url().'?success');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//договор поставки разовый
			case '4234525325':
				global $sql,$e;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_order']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_doc_method_pay_cash']=array(null,null,3);
				$data['m_documents_doc_method_pay_bank']=array(null,null,3);
				$data['m_documents_doc_delivery_self']=array(null,null,3);
				$data['m_documents_doc_sum_pre']=array(null,null,20);
				$data['m_documents_doc_delivery_time']=array(null,null,20);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_doc_delivery_time']=$data['m_documents_doc_delivery_time']?$data['m_documents_doc_delivery_time']:3;
					$data['m_documents_doc_sum_pre']=$data['m_documents_doc_sum_pre']?$data['m_documents_doc_sum_pre']:0;
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['invoice']=0;
					
					//находим счет по заказу
					$docs=$documents->getOrderDocs($data['m_documents_order']);
					foreach($docs as $_docs){
						//если среди документов по заказу есть счет на оплату и поставщик с покупателем совпадают
						if($_docs['m_documents_templates_id']==2363374033&&$_docs['m_documents_performer']==$data['m_documents_performer']&&$_docs['m_documents_customer']==$data['m_documents_customer'])
							$data['invoice']=$_docs['m_documents_id'];
					}
	
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
 
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"order":"'.$data['m_documents_order'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"org_director_signature":"http://'.$_SERVER['HTTP_HOST'].'/img/signature/signature.php?type=director",
							"org_stamp":"http://'.$_SERVER['HTTP_HOST'].'/img/signature/signature.php?type=stamp",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_logo":"logo_docs.png",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"m_documents_doc_method_pay_cash":"'.$data['m_documents_doc_method_pay_cash'].'",
							"m_documents_doc_method_pay_bank":"'.$data['m_documents_doc_method_pay_bank'].'",
							"m_documents_doc_sum_pre":"'.$data['m_documents_doc_sum_pre'].'",
							"invoice":'.$data['invoice'].',
							"m_documents_doc_delivery_time":"'.$data['m_documents_doc_delivery_time'].'",
							"m_documents_doc_delivery_self":"'.$data['m_documents_doc_delivery_self'].'"
						}';
				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){

						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$d=$sql->query($q)[0];

						$params=json_decode($d['m_documents_params']);

						$filesize=documents::contract_goods(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'order'=>$params->order,
								'org_stamp'=>$params->org_stamp,
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_logo'=>$params->doc_logo,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'm_documents_doc_method_pay_cash'=>$params->m_documents_doc_method_pay_cash,
								'm_documents_doc_method_pay_bank'=>$params->m_documents_doc_method_pay_bank,
								'm_documents_doc_sum_pre'=>$params->m_documents_doc_sum_pre,
								'invoice'=>$params->invoice,
								'm_documents_doc_delivery_time'=>$params->m_documents_doc_delivery_time,
								'm_documents_doc_delivery_self'=>$params->m_documents_doc_delivery_self
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);
						
						header('Location: '.url().'?success&filepath=/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//договор поставки годовой
			case '4234525326':
				global $sql,$e;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_order']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_scan[]']=array(null,null,19);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['invoice']=0;
					
					//находим счет по заказу
					$docs=$documents->getOrderDocs($data['m_documents_order']);
					foreach($docs as $_docs){
						//если среди документов по заказу есть счет на оплату и поставщик с покупателем совпадают
						if($_docs['m_documents_templates_id']==2363374033&&$_docs['m_documents_performer']==$data['m_documents_performer']&&$_docs['m_documents_customer']==$data['m_documents_customer'])
							$data['invoice']=$_docs['m_documents_id'];
					}
	
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
 
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"order":"'.$data['m_documents_order'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"org_director_signature":"http://'.$_SERVER['HTTP_HOST'].'/img/signature/signature.php?type=director",
							"org_stamp":"http://'.$_SERVER['HTTP_HOST'].'/img/signature/signature.php?type=stamp",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_logo":"logo_docs.png",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"invoice":'.$data['invoice'].'
						}';
						
					//файлы
					mkdir(__DIR__.'/../../www/files/scan_docs/'.$id);
					$files=array();
					if($data['m_documents_scan[]'])
						foreach($data['m_documents_scan[]'] as $k=>$v){
							$files[$v]['file']=$v;
							$f_size=filesize(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v);
							$f_size=($f_size)?$f_size:0;
							if ($f_size>1048576)
								$f_size=round($f_size/1048576,1).' МБ';
							else
								$f_size=round($f_size/1024,1).' КБ';
							$files[$v]['size']=$f_size;
							//копируем только добавленные файлы
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v)){
								copy(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v,__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v);
							}
						}
					//print_r($files);exit;
					$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);		
					
				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_filesize`=0,
						`m_documents_scan`=\''.$files.'\',
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){

						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$d=$sql->query($q)[0];

						$params=json_decode($d['m_documents_params']);

						$filesize=documents::contract_goods_year(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'order'=>$params->order,
								'org_stamp'=>$params->org_stamp,
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_logo'=>$params->doc_logo,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'm_documents_doc_method_pay_cash'=>$params->m_documents_doc_method_pay_cash,
								'm_documents_doc_method_pay_bank'=>$params->m_documents_doc_method_pay_bank,
								'm_documents_doc_sum_pre'=>$params->m_documents_doc_sum_pre,
								'invoice'=>$params->invoice
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);
						
						header('Location: '.url().'?success&filepath=/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//спецификация к договору поставки годовому
			case '4234525327':
				global $sql,$e;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_invoice']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_scan[]']=array(null,null,400);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_doc_delivery_time']=array(null,null,20);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_doc_delivery_time']=$data['m_documents_doc_delivery_time']?$data['m_documents_doc_delivery_time']:7;
					$data['m_documents_doc_sum_pre']=$data['m_documents_doc_sum_pre']?$data['m_documents_doc_sum_pre']:0;
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					
					//основание, ищем в счете
					foreach($documents->getInfo() as $_d)
						//если есть договор поставки делаем его основанием
						if($_d[0]['m_documents_customer']==$data['m_documents_customer']&&$_d[0]['m_documents_templates_id']==4234525326)
							$data['doc_base']=$_d[0]['m_documents_id'];
					
	
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
 
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"order":"'.$data['m_documents_order'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"base_date":"'.$documents->getInfo($data['doc_base'])['m_documents_date'].'",
							"base_numb":"'.$documents->getInfo($data['doc_base'])['m_documents_numb'].'",
							"doc_logo":1,
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"invoice":'.$data['m_documents_invoice'].',
							"m_documents_doc_delivery_time":"'.$data['m_documents_doc_delivery_time'].'"
						}';
						
					//файлы
					mkdir(__DIR__.'/../../www/files/scan_docs/'.$id);
					$files=array();
					if($data['m_documents_scan[]'])
						foreach($data['m_documents_scan[]'] as $k=>$v){
							$files[$v]['file']=$v;
							$f_size=filesize(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v);
							$f_size=($f_size)?$f_size:0;
							if ($f_size>1048576)
								$f_size=round($f_size/1048576,1).' МБ';
							else
								$f_size=round($f_size/1024,1).' КБ';
							$files[$v]['size']=$f_size;
							//копируем только добавленные файлы
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v)){
								copy(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v,__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v);
							}
						}
					//print_r($files);exit;
					$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);	
						
				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`=0,
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_filesize`=0,
						`m_documents_scan`=\''.$files.'\',
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){

						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$d=$sql->query($q)[0];

						$params=json_decode($d['m_documents_params']);

						$filesize=documents::contract_goods_year_spec(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'order'=>$params->order,
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'base_date'=>$params->base_date,
								'base_numb'=>$params->base_numb,
								'doc_logo'=>$params->doc_logo,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'invoice'=>$params->invoice,
								'm_documents_doc_delivery_time'=>$params->m_documents_doc_delivery_time
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);
						
						header('Location: '.url().'?success&filepath=/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//доверенность
			case '4562002365':
				global $sql,$e;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_agent']=array(1,null,null,10,1);
				$data['m_documents_invoice']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_scan[]']=array(null,null,400);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_date_from']=array(null,null,20);
				$data['m_documents_date_to']=array(null,null,20);
				$data['print_items']=array(null,null,3);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_date_from']=$data['m_documents_date_from']?$data['m_documents_date_from']:dt();
					$data['m_documents_date_to']=$data['m_documents_date_to']?$data['m_documents_date_to']:dtc('','+3 days');
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['print_items']=$data['print_items']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					
					//основание, ищем в счете
					foreach($documents->getInfo() as $_d)
						//если есть договор поставки делаем его основанием
						if($_d[0]['m_documents_performer']==$data['m_documents_performer']&&$_d[0]['m_documents_templates_id']==4234525326)
							$data['doc_base']=$_d[0]['m_documents_id'];
					
	
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
 
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"agent":"'.$data['m_documents_agent'].'",
							"order":"'.$data['m_documents_order'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_logo":1,
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"invoice":'.$data['m_documents_invoice'].',
							"m_documents_date_from":"'.$data['m_documents_date_from'].'",
							"m_documents_date_to":"'.$data['m_documents_date_to'].'",
							"print_items":"'.$data['print_items'].'"
						}';
						
					//файлы
					mkdir(__DIR__.'/../../www/files/scan_docs/'.$id);
					$files=array();
					if($data['m_documents_scan[]'])
						foreach($data['m_documents_scan[]'] as $k=>$v){
							$files[$v]['file']=$v;
							$f_size=filesize(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v);
							$f_size=($f_size)?$f_size:0;
							if ($f_size>1048576)
								$f_size=round($f_size/1048576,1).' МБ';
							else
								$f_size=round($f_size/1024,1).' КБ';
							$files[$v]['size']=$f_size;
							//копируем только добавленные файлы
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v)){
								copy(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v,__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v);
							}
						}
					//print_r($files);exit;
					$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);	
						
				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`=0,
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_filesize`=0,
						`m_documents_scan`=\''.$files.'\',
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){

						$q='SELECT * FROM `m_documents` WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$d=$sql->query($q)[0];

						$params=json_decode($d['m_documents_params']);

						$filesize=documents::doverennost(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'agent'=>$params->agent,
								'order'=>$params->order,
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_logo'=>$params->doc_logo,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'invoice'=>$params->invoice,
								'm_documents_date_from'=>$params->m_documents_date_from,
								'm_documents_date_to'=>$params->m_documents_date_to,
								'print_items'=>$params->print_items
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);
						header('Location: '.url().'?success&filepath=/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;	
				
			//акт сверки
			case '5411000236':
				global $sql,$e;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_pays']=array(null,null,500);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_date_from']=array(null,null,19);
				$data['m_documents_date_to']=array(null,null,19);
				$data['m_documents_saldo_start']=array(null,null,null,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					//ВЫЧИСЛЯЕМ ПЕРВЫЙ МЕСЯЦ ТЕКУЩЕГО КВАРТАЛА, ЕСЛИ КВАРТАЛ НЕ ЗАПОЛНЕН
					$prev_quarter_start=ceil(dtu('m')/3)*3+1;
					$data['m_documents_date_from']=$data['m_documents_date_from']?$data['m_documents_date_from']:dtc('01.'.$prev_quarter_start.'.0000 00:00:00');
					$data['m_documents_date_to']=$data['m_documents_date_to']?$data['m_documents_date_to']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['m_documents_saldo_start']=$data['m_documents_saldo_start']?$data['m_documents_saldo_start']:0;
					
					$pays=$docs=array();
					//находим оплаты по контрагенту за выбранный период
					$q='SELECT `m_buh_id` FROM `formetoo_cdb`.`m_buh` WHERE 
						`m_buh_performer` IN ('.$data['m_documents_performer'].','.$data['m_documents_customer'].')  AND 
						`m_buh_customer` IN ('.$data['m_documents_performer'].','.$data['m_documents_customer'].') AND
						`m_buh_performer`!=`m_buh_customer` AND
						`m_buh_status_pay`=1 AND 
						`m_buh_cash`=0 AND 
						`m_buh_date` BETWEEN \''.$data['m_documents_date_from'].'\' AND \''.$data['m_documents_date_to'].'\'
						ORDER BY `m_buh_date`;';
					if($res=$sql->query($q)){
						foreach($res as $_res)
							$pays[]=$_res['m_buh_id'];
					}
					//находим реализации по контрагенту за выбранный период
					$q='SELECT `m_documents_id` FROM `formetoo_cdb`.`m_documents` WHERE 
						`m_documents_performer` IN ('.$data['m_documents_performer'].','.$data['m_documents_customer'].') AND 
						`m_documents_customer` IN ('.$data['m_documents_performer'].','.$data['m_documents_customer'].') AND
						`m_documents_performer`!=`m_documents_customer` AND
						`m_documents_templates_id` IN(3552326767,2352663637) AND
						`m_documents_date` BETWEEN \''.$data['m_documents_date_from'].'\' AND \''.$data['m_documents_date_to'].'\'
						ORDER BY `m_documents_date`;';
					if($res=$sql->query($q)){
						foreach($res as $_res)
							$docs[]=$_res['m_documents_id'];
					}
					
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
 
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"date_from":"'.$data['m_documents_date_from'].'",
							"date_to":"'.$data['m_documents_date_to'].'",
							"pays":"'.implode('|',$pays).'",
							"docs":"'.implode('|',$docs).'",
							"saldo":"'.$data['m_documents_saldo_start'].'",
							"doc_signature":'.$data['m_documents_signature'].'
						}';
				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_pays`=\''.$data['m_documents_pays'].'\',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){

						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$d=$sql->query($q)[0];

						$params=json_decode($d['m_documents_params']);

						$filesize=documents::act_sverki(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'date_from'=>$params->date_from,
								'date_to'=>$params->date_to,
								'pays'=>$params->pays,
								'docs'=>$params->docs,
								'saldo'=>$params->saldo,
								'doc_signature'=>$params->doc_signature
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);
						
						header('Location: '.url().'?success&filepath=/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;	
				
			//конверт
			case '1000223255':
				global $sql,$e;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_address_from']=array(1,null,null,null,1);
				$data['m_documents_address_to']=array(1,null,null,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
	
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
 
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"address_from":"'.$data['m_documents_address_from'].'",
							"address_to":"'.$data['m_documents_address_to'].'"
						}';
				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){

						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$d=$sql->query($q)[0];

						$params=json_decode($d['m_documents_params']);

						$filesize=documents::envelope(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'address_from'=>$params->address_from,
								'address_to'=>$params->address_to
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);
						
						header('Location: '.url().'?success&filepath=/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
				
			//договор подряда
			case '4022369852':
				global $sql,$e;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_order']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_doc_method_pay_cash']=array(null,null,3);
				$data['m_documents_doc_method_pay_bank']=array(null,null,3);
				$data['m_documents_doc_sum_pre']=array(null,null,20);
				$data['m_documents_doc_sum_phase']=array(null,null,20,null,1);
				$data['m_documents_doc_sum_end']=array(null,null,20,null,1);
				$data['m_documents_doc_guarantee']=array(null,null,20,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_doc_guarantee']=$data['m_documents_doc_guarantee']?$data['m_documents_doc_guarantee']:12;
					$data['m_documents_doc_sum_end']=$data['m_documents_doc_sum_end']?$data['m_documents_doc_sum_end']:3;
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
	
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
 
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"order":"'.$data['m_documents_order'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"org_director_signature":"http://'.$_SERVER['HTTP_HOST'].'/img/signature/signature.php?type=director",
							"org_stamp":"http://'.$_SERVER['HTTP_HOST'].'/img/signature/signature.php?type=stamp",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_logo":"logo_docs.png",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_address_f'].'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"m_documents_doc_method_pay_cash":"'.$data['m_documents_doc_method_pay_cash'].'",
							"m_documents_doc_method_pay_bank":"'.$data['m_documents_doc_method_pay_bank'].'",
							"m_documents_doc_sum_pre":"'.$data['m_documents_doc_sum_pre'].'",
							"m_documents_doc_sum_phase":"'.$data['m_documents_doc_sum_phase'].'",
							"m_documents_doc_sum_end":"'.$data['m_documents_doc_sum_end'].'",
							"m_documents_doc_guarantee":"'.$data['m_documents_doc_guarantee'].'"
						}';
				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){

						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$d=$sql->query($q)[0];

						$params=json_decode($d['m_documents_params']);

						$filesize=documents::contract_work(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'order'=>$params->order,
								'org_stamp'=>$params->org_stamp,
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_logo'=>$params->doc_logo,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'doc_header_org_name'=>$params->doc_header_org_name,
								'doc_header_org_address'=>$params->doc_header_org_address,
								'doc_header_org_tel'=>$params->doc_header_org_tel,
								'doc_header_org_email'=>$params->doc_header_org_email,
								'm_documents_doc_method_pay_cash'=>$params->m_documents_doc_method_pay_cash,
								'm_documents_doc_method_pay_bank'=>$params->m_documents_doc_method_pay_bank,
								'm_documents_doc_sum_pre'=>$params->m_documents_doc_sum_pre,
								'm_documents_doc_sum_phase'=>$params->m_documents_doc_sum_phase,
								'm_documents_doc_sum_end'=>$params->m_documents_doc_sum_end,
								'm_documents_doc_guarantee'=>$params->m_documents_doc_guarantee
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);
						
						header('Location: '.url().'?success');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//договор подряда с материалами
			case '4022369853':
				global $sql,$e;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_order']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_doc_method_pay_cash']=array(null,null,3);
				$data['m_documents_doc_method_pay_bank']=array(null,null,3);
				$data['m_documents_doc_sum_pre']=array(null,null,20);
				$data['m_documents_doc_sum_pre_m']=array(null,null,20);
				$data['m_documents_doc_sum_phase']=array(null,null,20,null,1);
				$data['m_documents_doc_sum_end']=array(null,null,20,null,1);
				$data['m_documents_doc_guarantee']=array(null,null,20,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_doc_guarantee']=$data['m_documents_doc_guarantee']?$data['m_documents_doc_guarantee']:12;
					$data['m_documents_doc_sum_end']=$data['m_documents_doc_sum_end']?$data['m_documents_doc_sum_end']:3;
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
	
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
 
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"order":"'.$data['m_documents_order'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"org_director_signature":"http://'.$_SERVER['HTTP_HOST'].'/img/signature/signature.php?type=director",
							"org_stamp":"http://'.$_SERVER['HTTP_HOST'].'/img/signature/signature.php?type=stamp",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_logo":"logo_docs.png",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_address_f'].'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"m_documents_doc_method_pay_cash":"'.$data['m_documents_doc_method_pay_cash'].'",
							"m_documents_doc_method_pay_bank":"'.$data['m_documents_doc_method_pay_bank'].'",
							"m_documents_doc_sum_pre":"'.$data['m_documents_doc_sum_pre'].'",
							"m_documents_doc_sum_pre_m":"'.$data['m_documents_doc_sum_pre_m'].'",
							"m_documents_doc_sum_phase":"'.$data['m_documents_doc_sum_phase'].'",
							"m_documents_doc_sum_end":"'.$data['m_documents_doc_sum_end'].'",
							"m_documents_doc_guarantee":"'.$data['m_documents_doc_guarantee'].'"
						}';
				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){

						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$d=$sql->query($q)[0];

						$params=json_decode($d['m_documents_params']);

						$filesize=documents::contract_work_m(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'order'=>$params->order,
								'org_stamp'=>$params->org_stamp,
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_logo'=>$params->doc_logo,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'doc_header_org_name'=>$params->doc_header_org_name,
								'doc_header_org_address'=>$params->doc_header_org_address,
								'doc_header_org_tel'=>$params->doc_header_org_tel,
								'doc_header_org_email'=>$params->doc_header_org_email,
								'm_documents_doc_method_pay_cash'=>$params->m_documents_doc_method_pay_cash,
								'm_documents_doc_method_pay_bank'=>$params->m_documents_doc_method_pay_bank,
								'm_documents_doc_sum_pre'=>$params->m_documents_doc_sum_pre,
								'm_documents_doc_sum_pre_m'=>$params->m_documents_doc_sum_pre_m,
								'm_documents_doc_sum_phase'=>$params->m_documents_doc_sum_phase,
								'm_documents_doc_sum_end'=>$params->m_documents_doc_sum_end,
								'm_documents_doc_guarantee'=>$params->m_documents_doc_guarantee
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);
						
						header('Location: '.url().'?success');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//смета по форме всеумельца
			case '1200369852':
				global $sql,$e,$orders;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_order']=array(1,null,null,10,1);
				$data['m_orders_smeta_additional']=array(null,null,3);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['idroom[]']=array(1,null,null,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['m_orders_smeta_additional']=$data['m_orders_smeta_additional']?1:0;
					
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
					
					$items=array();
					
					$sum=0;
					$nds18=0;
					
					//пробегаемся по каждой комнате
					foreach($data['idroom[]'] as $_room){
						$data2=array();
						//проверка данных по комнатам
						$data2[$_room.'m_orders_smeta_room_name[]']=array();
						$data2[$_room.'m_orders_smeta_room_length[]']=array();
						$data2[$_room.'m_orders_smeta_room_weight[]']=array();
						$data2[$_room.'m_orders_smeta_room_height[]']=array();
						$data2[$_room.'m_orders_smeta_room_square[]']=array();
						
						$data2[$_room.'m_orders_smeta_room_openings_type[]']=array();
						$data2[$_room.'m_orders_smeta_room_openings_weight[]']=array();
						$data2[$_room.'m_orders_smeta_room_openings_height[]']=array();
						$data2[$_room.'m_orders_smeta_room_openings_depth[]']=array();
						$data2[$_room.'m_orders_smeta_room_openings_length[]']=array();
						$data2[$_room.'m_orders_smeta_room_openings_square[]']=array();
						
						$data2[$_room.'m_orders_smeta_services_id[]']=array();
						$data2[$_room.'m_orders_smeta_services_count[]']=array();
						$data2[$_room.'m_orders_smeta_services_manual_changed[]']=array();
						$data2[$_room.'m_orders_smeta_services_price[]']=array();
						$data2[$_room.'m_orders_smeta_services_sum[]']=array();
						
						array_walk($data2,'check');
						
						//переводим все данные в один массив
						$items[$_room]['room']['name']=$data2[$_room.'m_orders_smeta_room_name[]'][0];
						$items[$_room]['room']['length']=$data2[$_room.'m_orders_smeta_room_length[]'][0];
						$items[$_room]['room']['weight']=$data2[$_room.'m_orders_smeta_room_weight[]'][0];
						$items[$_room]['room']['height']=$data2[$_room.'m_orders_smeta_room_height[]'][0];
						$items[$_room]['room']['square']=$data2[$_room.'m_orders_smeta_room_square[]'][0];
						//проёмы
						foreach($data2[$_room.'m_orders_smeta_room_openings_type[]'] as $k=>$v){
							//если площадь проёма не равна 0
							if($data2[$_room.'m_orders_smeta_room_openings_square[]'][$k]){
								$items[$_room]['openings'][$k]['type']=$data2[$_room.'m_orders_smeta_room_openings_type[]'][$k];
								$items[$_room]['openings'][$k]['weight']=$data2[$_room.'m_orders_smeta_room_openings_weight[]'][$k];
								$items[$_room]['openings'][$k]['height']=$data2[$_room.'m_orders_smeta_room_openings_height[]'][$k];
								$items[$_room]['openings'][$k]['depth']=$data2[$_room.'m_orders_smeta_room_openings_depth[]'][$k];
								$items[$_room]['openings'][$k]['length']=$data2[$_room.'m_orders_smeta_room_openings_length[]'][$k];
								$items[$_room]['openings'][$k]['square']=$data2[$_room.'m_orders_smeta_room_openings_square[]'][$k];
							}
						}
						//работы
						foreach($data2[$_room.'m_orders_smeta_services_id[]'] as $k=>$v){
							//если услуга выбрана и сумма по услуге не равна 0
							if($data2[$_room.'m_orders_smeta_services_id[]'][$k]&&$data2[$_room.'m_orders_smeta_services_sum[]'][$k]){
								$items[$_room]['services'][$k]['id']=$data2[$_room.'m_orders_smeta_services_id[]'][$k];
								$items[$_room]['services'][$k]['count']=$data2[$_room.'m_orders_smeta_services_count[]'][$k];
								$items[$_room]['services'][$k]['manual_changed']=$data2[$_room.'m_orders_smeta_services_manual_changed[]'][$k];
								$items[$_room]['services'][$k]['price']=$data2[$_room.'m_orders_smeta_services_price[]'][$k];
								$items[$_room]['services'][$k]['sum']=$data2[$_room.'m_orders_smeta_services_sum[]'][$k];
								$sum+=$items[$_room]['services'][$k]['sum'];
								$nds18+=$items[$_room]['services'][$k]['sum']*.20/1.20;
							}
						}	
					}
					

					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"order":"'.$data['m_documents_order'].'",
							"additional":"'.$data['m_orders_smeta_additional'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_sum":"'.$sum.'",
							"doc_nds18":"'.($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']==18?$nds18:0).'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":"logo_docs.png",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_address_f'].'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';

				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){
					
						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 2;';
						$res=$sql->query($q);
						foreach($res as $_doc)
							if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
								//договор - основание
								$b=$_doc;
							else
								//смета
								$d=$_doc;
						
						$params=json_decode($d['m_documents_params']);
						
						$filesize=documents::smeta_vseumelec(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'order'=>$params->order,
								'additional'=>$params->additional,
								'base_numb'=>isset($b['m_documents_numb'])?$b['m_documents_numb']:'',
								'base_date'=>isset($b['m_documents_date'])?$b['m_documents_date']:'',
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'doc_logo'=>$params->doc_logo,
								'doc_header_org_name'=>$params->doc_header_org_name,
								'doc_header_org_address'=>$params->doc_header_org_address,
								'doc_header_org_tel'=>$params->doc_header_org_tel,
								'doc_header_org_email'=>$params->doc_header_org_email,
								'items'=>$params->items
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);

						header('Location: '.url().'?success');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//календарный план по форме всеумельца
			case '1356783437':
				global $sql,$e,$orders;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['smeta']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['idstage[]']=array(1,null,null,null,1);
				$data['m_orders_kalendar_sum']=array(null,null,null,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['smeta']=$documents->getInfo($data['smeta']);
					$p=json_decode($data['smeta']['m_documents_params']);
					$data['m_documents_order']=$data['smeta']['m_documents_order'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
					
					$items=array();
					
					//пробегаемся по каждому этапу
					foreach($data['idstage[]'] as $_stage){
						$data2=array();
						//проверка данных по комнатам
						$data2[$_stage.'m_orders_kalendar_stage_name[]']=array();
						$data2[$_stage.'m_orders_kalendar_stage_date_start[]']=array();
						$data2[$_stage.'m_orders_kalendar_stage_date_end[]']=array();
						$data2[$_stage.'m_orders_kalendar_stage_sum[]']=array();
						
						$data2[$_stage.'m_orders_kalendar_stage_doc_id[]']=array();
						$data2[$_stage.'m_orders_kalendar_stage_services_id[]']=array();
						$data2[$_stage.'m_orders_kalendar_stage_services_room_id[]']=array();
						$data2[$_stage.'m_orders_kalendar_stage_services_count[]']=array();
						$data2[$_stage.'m_orders_kalendar_stage_services_price[]']=array();
						$data2[$_stage.'m_orders_kalendar_stage_services_sum[]']=array();
						
						array_walk($data2,'check');
						
						//если сумма работ по этапу не равна 0
						if($data2[$_stage.'m_orders_kalendar_stage_sum[]'][0]){
						
							$data2[$_stage.'m_orders_kalendar_stage_date_start[]']=$data2[$_stage.'m_orders_kalendar_stage_date_start[]']?$data2[$_stage.'m_orders_kalendar_stage_date_start[]']:dt();
							$data2[$_stage.'m_orders_kalendar_stage_date_end[]']=$data2[$_stage.'m_orders_kalendar_stage_date_end[]']?$data2[$_stage.'m_orders_kalendar_stage_date_end[]']:dt();
							
							//переводим все данные в один массив
							$items[$_stage]['stage']['name']=$data2[$_stage.'m_orders_kalendar_stage_name[]'][0];
							$items[$_stage]['stage']['date_start']=$data2[$_stage.'m_orders_kalendar_stage_date_start[]'][0];
							$items[$_stage]['stage']['date_end']=$data2[$_stage.'m_orders_kalendar_stage_date_end[]'][0];
							$items[$_stage]['stage']['sum']=$data2[$_stage.'m_orders_kalendar_stage_sum[]'][0];
							
							//работы
							foreach($data2[$_stage.'m_orders_kalendar_stage_services_id[]'] as $k=>$v){
								$items[$_stage]['services'][$k]['room_id']=$data2[$_stage.'m_orders_kalendar_stage_services_room_id[]'][$k];
								$items[$_stage]['services'][$k]['id']=$data2[$_stage.'m_orders_kalendar_stage_services_id[]'][$k];
								$items[$_stage]['services'][$k]['count']=$data2[$_stage.'m_orders_kalendar_stage_services_count[]'][$k];
								$items[$_stage]['services'][$k]['price']=$data2[$_stage.'m_orders_kalendar_stage_services_price[]'][$k];
								$items[$_stage]['services'][$k]['sum']=$data2[$_stage.'m_orders_kalendar_stage_services_sum[]'][$k];
							}
						}
					}
					
					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"smeta":"'.$data['smeta']['m_documents_id'].'",
							"additional":"'.(isset($p->additional)&&$p->additional?'1':'0').'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_sum":"'.$data['m_orders_kalendar_sum'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":"logo_docs.png",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_address_f'].'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';

				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){
					
						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 2;';
						$res=$sql->query($q);
						foreach($res as $_doc)
							if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
								//договор - основание
								$b=$_doc;
							else
								//календарный план
								$d=$_doc;
						
						$params=json_decode($d['m_documents_params']);
						
						$filesize=documents::kalendar_vseumelec(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'smeta'=>$params->smeta,
								'additional'=>$params->additional,
								'base_numb'=>isset($b['m_documents_numb'])?$b['m_documents_numb']:'',
								'base_date'=>isset($b['m_documents_date'])?$b['m_documents_date']:'',
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_sum'=>$params->doc_sum,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'doc_logo'=>$params->doc_logo,
								'doc_header_org_name'=>$params->doc_header_org_name,
								'doc_header_org_address'=>$params->doc_header_org_address,
								'doc_header_org_tel'=>$params->doc_header_org_tel,
								'doc_header_org_email'=>$params->doc_header_org_email,
								'items'=>$params->items
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);

						header('Location: '.url().'?success');
						
					}
					else{
						header('Location: '.url().'?error');
					}
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//акт по форме всеумельца
			case '8522102145':
				global $sql,$e,$orders;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['smeta']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				
				$data['m_orders_act_doc_id[]']=array();
				$data['m_orders_act_services_id[]']=array();
				$data['m_orders_act_services_room_id[]']=array();
				$data['m_orders_act_services_count[]']=array();
				$data['m_orders_act_services_price[]']=array();
				$data['m_orders_act_services_sum[]']=array();
				$data['m_orders_act_sum']=array(null,null,null,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['smeta']=$documents->getInfo($data['smeta']);
					$p=json_decode($data['smeta']['m_documents_params']);
					$data['m_documents_order']=$data['smeta']['m_documents_order'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					
					$_address=$info->getAddress($data['m_documents_performer']);
					$_address=change_key($_address,'m_address_type',true);
					$doc_header_org_address=(isset($_address['2'])&&$_address['2'])?$_address['2']['m_address_full']:((isset($_address['1'])&&$_address['1'])?$_address['1']['m_address_full']:'');
					
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
					
					$items=array();
					
					//если сумма работ по акту не равна 0
					if($data['m_orders_act_sum'])
						//работы
						foreach($data['m_orders_act_services_id[]'] as $k=>$v){
							$items[$k]['room_id']=$data['m_orders_act_services_room_id[]'][$k];
							$items[$k]['id']=$data['m_orders_act_services_id[]'][$k];
							$items[$k]['count']=$data['m_orders_act_services_count[]'][$k];
							$items[$k]['price']=$data['m_orders_act_services_price[]'][$k];
							$items[$k]['sum']=$data['m_orders_act_services_sum[]'][$k];
						}
					
					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"smeta":"'.$data['smeta']['m_documents_id'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_sum":"'.$data['m_orders_act_sum'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":"logo_docs.png",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$doc_header_org_address.'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';

				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){
					
						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 2;';
						$res=$sql->query($q);
						foreach($res as $_doc)
							if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
								//договор - основание
								$b=$_doc;
							else
								//календарный план
								$d=$_doc;
						
						$params=json_decode($d['m_documents_params']);
						
						$filesize=documents::act_vseumelec(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'smeta'=>$params->smeta,
								'base_numb'=>isset($b['m_documents_numb'])?$b['m_documents_numb']:'',
								'base_date'=>isset($b['m_documents_date'])?$b['m_documents_date']:'',
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_sum'=>$params->doc_sum,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'doc_logo'=>$params->doc_logo,
								'doc_header_org_name'=>$params->doc_header_org_name,
								'doc_header_org_address'=>$params->doc_header_org_address,
								'doc_header_org_tel'=>$params->doc_header_org_tel,
								'doc_header_org_email'=>$params->doc_header_org_email,
								'items'=>$params->items
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);

						header('Location: '.url().'?success');
						
					}
					else{
						//header('Location: '.url().'?error');
					}
				}
				else{
					elogs();
					//header('Location: '.url().'?error');
				}
				break;

			//ведомость рабочая по форме всеумельца
			case '3522102145':
				global $sql,$e,$orders;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['smeta']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_noprice']=array(null,null,3);
				
				$data['m_orders_act_doc_id[]']=array();
				$data['m_orders_act_services_id[]']=array();
				$data['m_orders_act_services_room_id[]']=array();
				$data['m_orders_act_services_count[]']=array();
				$data['m_orders_act_services_price[]']=array();
				$data['m_orders_act_services_sum[]']=array();
				$data['m_orders_act_sum']=array(null,null,null,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['smeta']=$documents->getInfo($data['smeta']);
					$p=json_decode($data['smeta']['m_documents_params']);
					$data['m_documents_order']=$data['smeta']['m_documents_order'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['m_documents_noprice']=$data['m_documents_noprice']?1:0;
					
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
					
					$items=array();
					
					//если сумма работ по акту не равна 0
					if($data['m_orders_act_sum'])
						//работы
						foreach($data['m_orders_act_services_id[]'] as $k=>$v){
							$items[$k]['room_id']=$data['m_orders_act_services_room_id[]'][$k];
							$items[$k]['id']=$data['m_orders_act_services_id[]'][$k];
							$items[$k]['count']=$data['m_orders_act_services_count[]'][$k];
							$items[$k]['price']=$data['m_orders_act_services_price[]'][$k];
							$items[$k]['sum']=$data['m_orders_act_services_sum[]'][$k];
						}
					
					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"smeta":"'.$data['smeta']['m_documents_id'].'",
							"noprice":"'.$data['m_documents_noprice'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_sum":"'.$data['m_orders_act_sum'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":"logo_docs.png",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_address_f'].'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';

				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){
					
						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 2;';
						$res=$sql->query($q);
						foreach($res as $_doc)
							if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
								//договор - основание
								$b=$_doc;
							else
								//календарный план
								$d=$_doc;
						
						$params=json_decode($d['m_documents_params']);
						
						$filesize=documents::vedomost_vseumelec(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'smeta'=>$params->smeta,
								'noprice'=>$params->noprice,
								'base_numb'=>isset($b['m_documents_numb'])?$b['m_documents_numb']:'',
								'base_date'=>isset($b['m_documents_date'])?$b['m_documents_date']:'',
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_sum'=>$params->doc_sum,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'doc_logo'=>$params->doc_logo,
								'doc_header_org_name'=>$params->doc_header_org_name,
								'doc_header_org_address'=>$params->doc_header_org_address,
								'doc_header_org_tel'=>$params->doc_header_org_tel,
								'doc_header_org_email'=>$params->doc_header_org_email,
								'items'=>$params->items
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);

						header('Location: '.url().'?success');
						
					}
					else{
						header('Location: '.url().'?error');
					}
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;		
				
			//счет на оплату
			case '2363374033':
				global $sql,$e,$orders,$buh;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_rs']=array(1,null,null,10,1);
				$data['m_documents_order']=array(1,null,null,10,1);
				$data['m_orders_smeta_additional']=array(null,null,3);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_nds_itog']=array(null,null,3);
				$data['m_documents_pdf_none']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_pays']=array(null,null,500);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_invoice_date_expire']=array(null,null,19);
				$data['m_invoice_attention']=array(null,null,200);
				$data['idroom[]']=array(1,null,null,null,1);
				$data['m_documents_scan[]']=array(null,null,400);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_invoice_date_expire']=$data['m_invoice_date_expire']?$data['m_invoice_date_expire']:dtu(dtc('','+ 3 weekdays'),'d.m.Y');
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_nds_itog']=$data['m_documents_nds_itog']?1:0;
					$data['m_documents_pdf_none']=$data['m_documents_pdf_none']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['m_invoice_attention']=transform::typography($data['m_invoice_attention']);
					$data['m_invoice_terms']=post('m_invoice_terms',array(),array(),1);
					$_address=$info->getAddress($data['m_documents_performer']);
					$_address=change_key($_address,'m_address_type',true);
					$doc_header_org_address=(isset($_address['4'])&&$_address['4'])?$_address['4']['m_address_full']:((isset($_address['1'])&&$_address['1'])?$_address['1']['m_address_full']:'');

					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
					
					$items=array();
					
					$sum=0;
					$nds18=0;
					
					//пробегаемся по каждой комнате
					foreach($data['idroom[]'] as $_room){
						$data2=array();
						//проверка данных по комнатам
						$data2[$_room.'m_orders_smeta_room_name[]']=array();
						
						$data2[$_room.'m_orders_smeta_services_id[]']=array();
						$data2[$_room.'m_orders_smeta_services_count[]']=array();
						$data2[$_room.'m_orders_smeta_services_manual_changed[]']=array();
						$data2[$_room.'m_orders_smeta_services_price[]']=array();
						$data2[$_room.'m_orders_smeta_services_nds[]']=array();
						$data2[$_room.'m_orders_smeta_services_sum[]']=array();
						$data2[$_room.'m_orders_smeta_table[]']=array();
						
						array_walk($data2,'check');
						
						//переводим все данные в один массив
						$items[$_room]['room']['name']=$data2[$_room.'m_orders_smeta_room_name[]'][0];
						
						//работы
						foreach($data2[$_room.'m_orders_smeta_services_id[]'] as $k=>$v){
							//если услуга выбрана и сумма по услуге не равна 0
							if($data2[$_room.'m_orders_smeta_services_id[]'][$k]&&$data2[$_room.'m_orders_smeta_services_sum[]'][$k]){
								$nds_item=isset($data2[$_room.'m_orders_smeta_services_nds[]'][$k])?($data2[$_room.'m_orders_smeta_services_nds[]'][$k]==-1?0:$data2[$_room.'m_orders_smeta_services_nds[]'][$k]):20;
								$items[$_room]['services'][$k]['id']=$data2[$_room.'m_orders_smeta_services_id[]'][$k];
								$items[$_room]['services'][$k]['count']=$data2[$_room.'m_orders_smeta_services_count[]'][$k];
								$items[$_room]['services'][$k]['nds']=isset($data2[$_room.'m_orders_smeta_services_nds[]'][$k])?$data2[$_room.'m_orders_smeta_services_nds[]'][$k]:$orders->orders_id[$data['m_documents_order']][0]['m_orders_nds'];
								$items[$_room]['services'][$k]['manual_changed']=$data2[$_room.'m_orders_smeta_services_manual_changed[]'][$k];
								$items[$_room]['services'][$k]['price']=$data2[$_room.'m_orders_smeta_services_price[]'][$k];
								$items[$_room]['services'][$k]['sum']=$data2[$_room.'m_orders_smeta_services_sum[]'][$k];
								$items[$_room]['services'][$k]['table']=$data2[$_room.'m_orders_smeta_table[]'][$k];
								$sum+=$items[$_room]['services'][$k]['sum'];
								$nds18+=$items[$_room]['services'][$k]['sum']*($nds_item/100)/(1+$nds_item/100);
							}
						}	
					}
					
					//если НДС общим итогом
					if($data['m_documents_nds_itog']){
						$nds18=$sum*.20;
						$sum+=$nds18;
					}

					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"order":"'.$data['m_documents_order'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_sum":"'.number_format($sum,2,'.','').'",
							"doc_date_expire":"'.$data['m_invoice_date_expire'].'",
							"doc_attention":"'.$data['m_invoice_attention'].'",
							"doc_terms":'.json_encode($data['m_invoice_terms'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).',
							"doc_nds18":"'.$nds18.'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":1,
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$doc_header_org_address.'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';
						
					//файлы
					mkdir(__DIR__.'/../../www/files/scan_docs/'.$id);
					$files=array();
					if($data['m_documents_scan[]'])
						foreach($data['m_documents_scan[]'] as $k=>$v){
							$files[$v]['file']=$v;
							$f_size=filesize(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v);
							$f_size=($f_size)?$f_size:0;
							if ($f_size>1048576)
								$f_size=round($f_size/1048576,1).' МБ';
							else
								$f_size=round($f_size/1024,1).' КБ';
							$files[$v]['size']=$f_size;
							//копируем только добавленные файлы
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v)){
								copy(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v,__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v);
							}
						}
					//print_r($files);exit;
					$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);	
					
				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_pdf_none`='.$data['m_documents_pdf_none'].',
						`m_documents_nds_itog`='.$data['m_documents_nds_itog'].',
						`m_documents_filesize`=0,
						`m_documents_pays`=\''.$data['m_documents_pays'].'\',
						`m_documents_scan`=\''.$files.'\',
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){
						if(!$data['m_documents_pdf_none']){
							$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 2;';
							$res=$sql->query($q);
							foreach($res as $_doc)
								if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
									//договор - основание
									$b=$_doc;
								else
									//смета
									$d=$_doc;
							
							$params=json_decode($d['m_documents_params']);
							
							$filesize=documents::invoice(
								array(
									'org'=>$params->org,
									'client'=>$params->client,
									'order'=>$params->order,
									'base_numb'=>isset($b['m_documents_numb'])?$b['m_documents_numb']:'',
									'base_date'=>isset($b['m_documents_date'])?$b['m_documents_date']:'',
									'doc_date'=>$params->doc_date,
									'doc_numb'=>$params->doc_numb,
									'doc_bar'=>$params->doc_bar,
									'doc_date_expire'=>$params->doc_date_expire,
									'doc_attention'=>$params->doc_attention,
									'doc_terms'=>$params->doc_terms,
									'doc_signature'=>$params->doc_signature,
									'doc_logo'=>$params->doc_logo,
									'doc_header_org_name'=>$params->doc_header_org_name,
									'doc_header_org_address'=>$params->doc_header_org_address,
									'doc_header_org_tel'=>$params->doc_header_org_tel,
									'doc_header_org_email'=>$params->doc_header_org_email,
									'items'=>$params->items
								),
								$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
							);
							
							$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
							$sql->query($q);
							
							header('Location: '.url().'?success&id='.$data['m_documents_id'].'&m_documents_templates_id='.$data['m_documents_templates_id'].'&m_documents_order='.$data['m_documents_order'].'&filepath=/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf');
						}
						else{
							header('Location: '.url().'?success');
						}
					}
					else{
						header('Location: '.url().'?error');
					}
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//УПД	
			case '3552326767':
				global $sql,$e,$orders,$buh;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_agent']=array(null,null,10,null,1);
				$data['smeta']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_base']=array(null,null,180);
				$data['m_documents_status_otchet']=array(null,null,10);
				$data['m_documents_date_ship']=array(null,null,19);
				$data['m_documents_pays']=array(null,null,500);
				$data['m_documents_nds_itog']=array(null,null,3);
				$data['m_documents_pdf_none']=array(null,null,3);
				$data['m_documents_scan[]']=array(null,null,400);
				
				$data['m_orders_act_doc_id[]']=array();
				$data['m_orders_act_services_id[]']=array();
				$data['m_orders_act_services_room_id[]']=array();
				$data['m_orders_act_services_count[]']=array();
				$data['m_orders_act_services_price[]']=array();
				$data['m_orders_act_services_sum[]']=array();
				$data['m_orders_act_services_table[]']=array();
				$data['m_orders_act_sum']=array(null,null,null,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['smeta']=$documents->getInfo($data['smeta']);
					$p=json_decode($data['smeta']['m_documents_params']);
					$data['m_documents_order']=$data['smeta']['m_documents_order'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_date_ship']=$data['m_documents_date_ship']?$data['m_documents_date_ship']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['m_documents_nds_itog']=$data['m_documents_nds_itog']?1:0;
					$data['m_documents_pdf_none']=$data['m_documents_pdf_none']?1:0;
					$data['m_documents_status_otchet']=$data['m_documents_status_otchet']?$data['m_documents_status_otchet']:'01';
					
					$_address=$info->getAddress($data['m_documents_performer']);
					$_address=change_key($_address,'m_address_type',true);
					$doc_header_org_address=(isset($_address['2'])&&$_address['2'])?$_address['2']['m_address_full']:((isset($_address['1'])&&$_address['1'])?$_address['1']['m_address_full']:'');
					
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
					
					//основание УПД, ищем в заказе договор или счет
					foreach($documents->getInfo() as $_d)
						//если есть договор поставки в том же заказе, что и смета, делаем его основанием
						if($_d[0]['m_documents_order']==$data['m_documents_order']&&$_d[0]['m_documents_templates_id']==4234525325)
							$data['doc_base']=$_d[0]['m_documents_id'];
						//если договора нет - основанием делаем счет
						else
							$data['doc_base']=$data['smeta']['m_documents_id'];
					
					$sum=0;
					$nds18=0;
					
					$items=array();
					//если сумма работ по акту не равна 0
					if($data['m_orders_act_sum'])
						//работы
						foreach($data['m_orders_act_services_id[]'] as $k=>$v){
							$items[$k]['room_id']=$data['m_orders_act_services_room_id[]'][$k];
							$items[$k]['id']=$data['m_orders_act_services_id[]'][$k];
							$items[$k]['count']=$data['m_orders_act_services_count[]'][$k];
							$items[$k]['price']=$data['m_orders_act_services_price[]'][$k];
							$items[$k]['nds']=isset($data['m_orders_act_services_nds[]'][$k])?$data['m_orders_act_services_nds[]'][$k]:($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']);
							$items[$k]['sum']=$data['m_orders_act_services_sum[]'][$k];
							$items[$k]['table']=$data['m_orders_act_services_table[]'][$k];
							$sum+=$items[$k]['sum'];
							$nds18+=$items[$k]['nds']!=-1?$items[$k]['sum']*($items[$k]['nds']/100)/(1+$items[$k]['nds']/100):0;
						}
						
					//если НДС общим итогом
					if($data['m_documents_nds_itog']){
						$nds18=$sum*($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']/100);
						$sum+=$nds18;
					}
					
					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"agent":"'.$data['m_documents_agent'].'",
							"smeta":"'.$data['smeta']['m_documents_id'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_base":"'.$data['doc_base'].'",
							"doc_status_otchet":"'.$data['m_documents_status_otchet'].'",
							"date_ship":"'.$data['m_documents_date_ship'].'",
							"doc_base_text":"'.$data['m_documents_base'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_sum":"'.number_format($sum,2,'.','').'",
							"doc_nds18":"'.round($nds18,2).'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":"logo_docs.png",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$doc_header_org_address.'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';
						
					//находим платежи по счету
					if(!$data['m_documents_pays']){
						$data['pays']=$buh->getInfoFromInvoice($data['smeta']['m_documents_id']);
						$data['pays']=implode('|',$data['pays']);
					}
					else{
						$data['pays']=$data['m_documents_pays'];
					}
					
					//файлы
					mkdir(__DIR__.'/../../www/files/scan_docs/'.$id);
					$files=array();
					if($data['m_documents_scan[]'])
						foreach($data['m_documents_scan[]'] as $k=>$v){
							$files[$v]['file']=$v;
							$f_size=filesize(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v);
							$f_size=($f_size)?$f_size:0;
							if ($f_size>1048576)
								$f_size=round($f_size/1048576,1).' МБ';
							else
								$f_size=round($f_size/1024,1).' КБ';
							$files[$v]['size']=$f_size;
							//копируем только добавленные файлы
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v)){
								copy(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v,__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v);
							}
						}
					//print_r($files);exit;
					$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_pdf_none`='.$data['m_documents_pdf_none'].',
						`m_documents_nds_itog`='.$data['m_documents_nds_itog'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_pays`=\''.$data['pays'].'\',
						`m_documents_scan`=\''.$files.'\',
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){
						if(!$data['m_documents_pdf_none']){
							$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 3;';
							$res=$sql->query($q);
							foreach($res as $_doc)
								if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
									//договор - основание
									$b=$_doc;
								else
									//сам документ
									$d=$_doc;
							
							$params=json_decode($d['m_documents_params']);
							
							$filesize=documents::upd(
								array(
									'org'=>$params->org,
									'client'=>$params->client,
									'smeta'=>$params->smeta,
									'doc_base'=>$params->doc_base,
									'date_ship'=>$params->date_ship,
									'doc_base_text'=>$params->doc_base_text,
									'doc_date'=>$params->doc_date,
									'doc_numb'=>$params->doc_numb,
									'doc_sum'=>$params->doc_sum,
									'doc_nds18'=>$params->doc_nds18,
									'doc_bar'=>$params->doc_bar,
									'doc_signature'=>$params->doc_signature,
									'doc_logo'=>$params->doc_logo,
									'doc_header_org_name'=>$params->doc_header_org_name,
									'doc_header_org_address'=>$params->doc_header_org_address,
									'doc_header_org_tel'=>$params->doc_header_org_tel,
									'doc_header_org_email'=>$params->doc_header_org_email,
									'items'=>$params->items
								),
								$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
							);
							$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
							$sql->query($q);

							header('Location: '.url().'?success&id='.$data['m_documents_id'].'&m_documents_templates_id='.$data['m_documents_templates_id'].'&m_documents_order='.$data['m_documents_order'].'&filepath=/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf');
						}
						else{
							header('Location: '.url().'?success');
						}
					}
					else{
						header('Location: '.url().'?error');
					}
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//СФ
			case '2352663637':
				global $sql,$e,$orders,$buh;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_agent']=array(null,null,10,null,1);
				$data['smeta']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_base']=array(null,null,180);
				$data['m_documents_status_otchet']=array(null,null,10);
				$data['m_documents_date_ship']=array(null,null,19);
				$data['m_documents_pays']=array(null,null,500);
				$data['m_documents_nds_itog']=array(null,null,3);
				$data['m_documents_pdf_none']=array(null,null,3);
				$data['m_documents_scan[]']=array(null,null,400);
				
				$data['m_orders_act_doc_id[]']=array();
				$data['m_orders_act_services_id[]']=array();
				$data['m_orders_act_services_room_id[]']=array();
				$data['m_orders_act_services_count[]']=array();
				$data['m_orders_act_services_price[]']=array();
				$data['m_orders_act_services_sum[]']=array();
				$data['m_orders_act_services_table[]']=array();
				$data['m_orders_act_sum']=array(null,null,null,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['smeta']=$documents->getInfo($data['smeta']);
					$p=json_decode($data['smeta']['m_documents_params']);
					$data['m_documents_order']=$data['smeta']['m_documents_order'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_date_ship']=$data['m_documents_date_ship']?$data['m_documents_date_ship']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['m_documents_nds_itog']=$data['m_documents_nds_itog']?1:0;
					$data['m_documents_pdf_none']=$data['m_documents_pdf_none']?1:0;
					$data['m_documents_status_otchet']=$data['m_documents_status_otchet']?$data['m_documents_status_otchet']:'01';
					
					$_address=$info->getAddress($data['m_documents_performer']);
					$_address=change_key($_address,'m_address_type',true);
					$doc_header_org_address=(isset($_address['2'])&&$_address['2'])?$_address['2']['m_address_full']:((isset($_address['1'])&&$_address['1'])?$_address['1']['m_address_full']:'');
					
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
					
					//основание УПД, ищем в заказе договор или счет
					foreach($documents->getInfo() as $_d)
						//если есть договор поставки в том же заказе, что и смета, делаем его основанием
						if($_d[0]['m_documents_order']==$data['m_documents_order']&&$_d[0]['m_documents_templates_id']==4234525325)
							$data['doc_base']=$_d[0]['m_documents_id'];
						//если договора нет - основанием делаем счет
						else
							$data['doc_base']=$data['smeta']['m_documents_id'];
					
					$sum=0;
					$nds18=0;
					
					$items=array();
					//если сумма работ по акту не равна 0
/* 					if($data['m_orders_act_sum'])
						//работы
						foreach($data['m_orders_act_services_id[]'] as $k=>$v){
							$items[$k]['room_id']=$data['m_orders_act_services_room_id[]'][$k];
							$items[$k]['id']=$data['m_orders_act_services_id[]'][$k];
							$items[$k]['count']=$data['m_orders_act_services_count[]'][$k];
							$items[$k]['price']=$data['m_orders_act_services_price[]'][$k];
							$items[$k]['sum']=$data['m_orders_act_services_sum[]'][$k];
							$items[$k]['table']=$data['m_orders_act_services_table[]'][$k];
							$sum+=$items[$k]['sum'];
							$nds18+=$items[$k]['sum']*.20/1.20;
						}
						
					//если НДС общим итогом
					if($data['m_documents_nds_itog']){
						$nds18=$sum*.20;
						$sum+=$nds18;
					} */
					if($data['m_orders_act_sum'])
						//работы
						foreach($data['m_orders_act_services_id[]'] as $k=>$v){
							$items[$k]['room_id']=$data['m_orders_act_services_room_id[]'][$k];
							$items[$k]['id']=$data['m_orders_act_services_id[]'][$k];
							$items[$k]['count']=$data['m_orders_act_services_count[]'][$k];
							$items[$k]['price']=$data['m_orders_act_services_price[]'][$k];
							$items[$k]['nds']=isset($data['m_orders_act_services_nds[]'][$k])?$data['m_orders_act_services_nds[]'][$k]:($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']);
							$items[$k]['sum']=$data['m_orders_act_services_sum[]'][$k];
							$items[$k]['table']=$data['m_orders_act_services_table[]'][$k];
							$sum+=$items[$k]['sum'];
							$nds18+=$items[$k]['nds']!=-1?$items[$k]['sum']*($items[$k]['nds']/100)/(1+$items[$k]['nds']/100):0;
						}
						
					//если НДС общим итогом
					if($data['m_documents_nds_itog']){
						$nds18=$sum*($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']/100);
						$sum+=$nds18;
					}
					
					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"agent":"'.$data['m_documents_agent'].'",
							"smeta":"'.$data['smeta']['m_documents_id'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_base":"'.$data['doc_base'].'",
							"doc_status_otchet":"'.$data['m_documents_status_otchet'].'",
							"date_ship":"'.$data['m_documents_date_ship'].'",
							"doc_base_text":"'.$data['m_documents_base'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_sum":"'.number_format($sum,2,'.','').'",
							"doc_nds18":"'.($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']!=-1?$nds18:0).'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":"logo_docs.png",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$doc_header_org_address.'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';
						
					//находим платежи по счету
					if(!$data['m_documents_pays']){
						$data['pays']=$buh->getInfoFromInvoice($data['smeta']['m_documents_id']);
						$data['pays']=implode('|',$data['pays']);
					}
					else{
						$data['pays']=$data['m_documents_pays'];
					}
					
					//файлы
					mkdir(__DIR__.'/../../www/files/scan_docs/'.$id);
					$files=array();
					if($data['m_documents_scan[]'])
						foreach($data['m_documents_scan[]'] as $k=>$v){
							$files[$v]['file']=$v;
							$f_size=filesize(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v);
							$f_size=($f_size)?$f_size:0;
							if ($f_size>1048576)
								$f_size=round($f_size/1048576,1).' МБ';
							else
								$f_size=round($f_size/1024,1).' КБ';
							$files[$v]['size']=$f_size;
							//копируем только добавленные файлы
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v)){
								copy(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v,__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v);
							}
						}
					//print_r($files);exit;
					$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_pdf_none`='.$data['m_documents_pdf_none'].',
						`m_documents_nds_itog`='.$data['m_documents_nds_itog'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_pays`=\''.$data['pays'].'\',
						`m_documents_scan`=\''.$files.'\',
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){
						if(!$data['m_documents_pdf_none']){
							$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 3;';
							$res=$sql->query($q);
							foreach($res as $_doc)
								if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
									//договор - основание
									$b=$_doc;
								else
									//сам документ
									$d=$_doc;
							
							$params=json_decode($d['m_documents_params']);
							
							$filesize=documents::sf(
								array(
									'org'=>$params->org,
									'client'=>$params->client,
									'smeta'=>$params->smeta,
									'doc_base'=>$params->doc_base,
									'date_ship'=>$params->date_ship,
									'doc_base_text'=>$params->doc_base_text,
									'doc_date'=>$params->doc_date,
									'doc_numb'=>$params->doc_numb,
									'doc_sum'=>$params->doc_sum,
									'doc_bar'=>$params->doc_bar,
									'doc_signature'=>$params->doc_signature,
									'doc_logo'=>$params->doc_logo,
									'doc_header_org_name'=>$params->doc_header_org_name,
									'doc_header_org_address'=>$params->doc_header_org_address,
									'doc_header_org_tel'=>$params->doc_header_org_tel,
									'doc_header_org_email'=>$params->doc_header_org_email,
									'items'=>$params->items
								),
								$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
							);
							$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
							$sql->query($q);

							header('Location: '.url().'?success');
						}
						else{
							header('Location: '.url().'?success');
						}
					}
					else{
						header('Location: '.url().'?error');
					}
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//ТН
			case '4359660563':
				global $sql,$e,$orders,$buh;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_agent']=array(null,null,10,null,1);
				$data['smeta']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_base']=array(null,null,180);
				$data['m_documents_date_ship']=array(null,null,19);
				$data['m_documents_pays']=array(null,null,500);
				$data['m_documents_nds_itog']=array(null,null,3);
				$data['m_documents_pdf_none']=array(null,null,3);
				$data['m_documents_scan[]']=array(null,null,400);
				
				$data['m_orders_act_doc_id[]']=array();
				$data['m_orders_act_services_id[]']=array();
				$data['m_orders_act_services_room_id[]']=array();
				$data['m_orders_act_services_count[]']=array();
				$data['m_orders_act_services_price[]']=array();
				$data['m_orders_act_services_sum[]']=array();
				$data['m_orders_act_services_table[]']=array();
				$data['m_orders_act_sum']=array(null,null,null,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['smeta']=$documents->getInfo($data['smeta']);
					$p=json_decode($data['smeta']['m_documents_params']);
					$data['m_documents_order']=$data['smeta']['m_documents_order'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_date_ship']=$data['m_documents_date_ship']?$data['m_documents_date_ship']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['m_documents_nds_itog']=$data['m_documents_nds_itog']?1:0;
					$data['m_documents_pdf_none']=$data['m_documents_pdf_none']?1:0;
					
					$_address=$info->getAddress($data['m_documents_performer']);
					$_address=change_key($_address,'m_address_type',true);
					$doc_header_org_address=(isset($_address['2'])&&$_address['2'])?$_address['2']['m_address_full']:((isset($_address['1'])&&$_address['1'])?$_address['1']['m_address_full']:'');
					
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
					
					//основание УПД, ищем в заказе договор или счет
					foreach($documents->getInfo() as $_d)
						//если есть договор поставки в том же заказе, что и смета, делаем его основанием
						if($_d[0]['m_documents_order']==$data['m_documents_order']&&$_d[0]['m_documents_templates_id']==4234525325)
							$data['doc_base']=$_d[0]['m_documents_id'];
						//если договора нет - основанием делаем счет
						else
							$data['doc_base']=$data['smeta']['m_documents_id'];
					
					$sum=0;
					$nds18=0;
					
					$items=array();
					//если сумма работ по акту не равна 0
					if($data['m_orders_act_sum'])
						//работы
						foreach($data['m_orders_act_services_id[]'] as $k=>$v){
							$items[$k]['room_id']=$data['m_orders_act_services_room_id[]'][$k];
							$items[$k]['id']=$data['m_orders_act_services_id[]'][$k];
							$items[$k]['count']=$data['m_orders_act_services_count[]'][$k];
							$items[$k]['price']=$data['m_orders_act_services_price[]'][$k];
							$items[$k]['nds']=isset($data['m_orders_act_services_nds[]'][$k])?$data['m_orders_act_services_nds[]'][$k]:($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']);
							$items[$k]['sum']=$data['m_orders_act_services_sum[]'][$k];
							$items[$k]['table']=$data['m_orders_act_services_table[]'][$k];
							$sum+=$items[$k]['sum'];
							$nds18+=$items[$k]['nds']!=-1?$items[$k]['sum']*($items[$k]['nds']/100)/(1+$items[$k]['nds']/100):0;
						}
						
					//если НДС общим итогом
					if($data['m_documents_nds_itog']){
						$nds18=$sum*($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']/100);
						$sum+=$nds18;
					}
					
					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"agent":"'.$data['m_documents_agent'].'",
							"smeta":"'.$data['smeta']['m_documents_id'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_base":"'.$data['doc_base'].'",
							"date_ship":"'.$data['m_documents_date_ship'].'",
							"doc_base_text":"'.$data['m_documents_base'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_sum":"'.number_format($sum,2,'.','').'",
							"doc_nds18":"'.($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']==18?$nds18:0).'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":"logo_docs.png",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$doc_header_org_address.'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';
						
					//находим платежи по счету
					if(!$data['m_documents_pays']){
						$data['pays']=$buh->getInfoFromInvoice($data['smeta']['m_documents_id']);
						$data['pays']=implode('|',$data['pays']);
					}
					else{
						$data['pays']=$data['m_documents_pays'];
					}
					
					//файлы
					mkdir(__DIR__.'/../../www/files/scan_docs/'.$id);
					$files=array();
					if($data['m_documents_scan[]'])
						foreach($data['m_documents_scan[]'] as $k=>$v){
							$files[$v]['file']=$v;
							$f_size=filesize(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v);
							$f_size=($f_size)?$f_size:0;
							if ($f_size>1048576)
								$f_size=round($f_size/1048576,1).' МБ';
							else
								$f_size=round($f_size/1024,1).' КБ';
							$files[$v]['size']=$f_size;
							//копируем только добавленные файлы
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v)){
								copy(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v,__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v);
							}
						}
					//print_r($files);exit;
					$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_pdf_none`='.$data['m_documents_pdf_none'].',
						`m_documents_nds_itog`='.$data['m_documents_nds_itog'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_pays`=\''.$data['pays'].'\',
						`m_documents_scan`=\''.$files.'\',
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){
						if(!$data['m_documents_pdf_none']){
							$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 3;';
							$res=$sql->query($q);
							foreach($res as $_doc)
								if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
									//договор - основание
									$b=$_doc;
								else
									//сам документ
									$d=$_doc;
							
							$params=json_decode($d['m_documents_params']);
							
							$filesize=documents::tn(
								array(
									'org'=>$params->org,
									'client'=>$params->client,
									'smeta'=>$params->smeta,
									'doc_base'=>$params->doc_base,
									'date_ship'=>$params->date_ship,
									'doc_base_text'=>$params->doc_base_text,
									'doc_date'=>$params->doc_date,
									'doc_numb'=>$params->doc_numb,
									'doc_sum'=>$params->doc_sum,
									'doc_bar'=>$params->doc_bar,
									'doc_signature'=>$params->doc_signature,
									'doc_logo'=>$params->doc_logo,
									'doc_header_org_name'=>$params->doc_header_org_name,
									'doc_header_org_address'=>$params->doc_header_org_address,
									'doc_header_org_tel'=>$params->doc_header_org_tel,
									'doc_header_org_email'=>$params->doc_header_org_email,
									'items'=>$params->items
								),
								$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
							);
							$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
							$sql->query($q);

							header('Location: '.url().'?success');
						}
						else{
							header('Location: '.url().'?success');
						}
					}
					else{
						header('Location: '.url().'?error');
					}
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//АКТ
			case '8522102445':
				global $sql,$e,$orders,$buh;
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_agent']=array(null,null,10,null,1);
				$data['smeta']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_base']=array(null,null,180);
				$data['m_documents_date_ship']=array(null,null,19);
				$data['m_documents_pays']=array(null,null,500);
				$data['m_documents_nds_itog']=array(null,null,3);
				$data['m_documents_pdf_none']=array(null,null,3);
				$data['m_documents_scan[]']=array(null,null,400);
				
				$data['m_orders_act_doc_id[]']=array();
				$data['m_orders_act_services_id[]']=array();
				$data['m_orders_act_services_room_id[]']=array();
				$data['m_orders_act_services_count[]']=array();
				$data['m_orders_act_services_price[]']=array();
				$data['m_orders_act_services_sum[]']=array();
				$data['m_orders_act_services_table[]']=array();
				$data['m_orders_act_sum']=array(null,null,null,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$id=get_id('m_documents');
					
					$data['smeta']=$documents->getInfo($data['smeta']);
					$p=json_decode($data['smeta']['m_documents_params']);
					$data['m_documents_order']=$data['smeta']['m_documents_order'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_date_ship']=$data['m_documents_date_ship']?$data['m_documents_date_ship']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['m_documents_nds_itog']=$data['m_documents_nds_itog']?1:0;
					$data['m_documents_pdf_none']=$data['m_documents_pdf_none']?1:0;
					
					$_address=$info->getAddress($data['m_documents_performer']);
					$_address=change_key($_address,'m_address_type',true);
					$doc_header_org_address=(isset($_address['2'])&&$_address['2'])?$_address['2']['m_address_full']:((isset($_address['1'])&&$_address['1'])?$_address['1']['m_address_full']:'');
					
					//папка
					$foldername=md5(time().$id);
					mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
					
					//основание УПД, ищем в заказе договор или счет
					foreach($documents->getInfo() as $_d)
						//если есть договор поставки в том же заказе, что и смета, делаем его основанием
						if($_d[0]['m_documents_order']==$data['m_documents_order']&&$_d[0]['m_documents_templates_id']==4234525325)
							$data['doc_base']=$_d[0]['m_documents_id'];
						//если договора нет - основанием делаем счет
						else
							$data['doc_base']=$data['smeta']['m_documents_id'];
					
					$sum=0;
					$nds18=0;
					
					$items=array();
					//если сумма работ по акту не равна 0
					if($data['m_orders_act_sum'])
						//работы
						foreach($data['m_orders_act_services_id[]'] as $k=>$v){
							$items[$k]['room_id']=$data['m_orders_act_services_room_id[]'][$k];
							$items[$k]['id']=$data['m_orders_act_services_id[]'][$k];
							$items[$k]['count']=$data['m_orders_act_services_count[]'][$k];
							$items[$k]['price']=$data['m_orders_act_services_price[]'][$k];
							$items[$k]['sum']=$data['m_orders_act_services_sum[]'][$k];
							$items[$k]['table']=$data['m_orders_act_services_table[]'][$k];
							$sum+=$items[$k]['sum'];
							$nds18+=$items[$k]['sum']*.20/1.20;
						}
						
					//если НДС общим итогом
					if($data['m_documents_nds_itog']){
						$nds18=$sum*.20;
						$sum+=$nds18;
					}
					
					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"agent":"'.$data['m_documents_agent'].'",
							"smeta":"'.$data['smeta']['m_documents_id'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_base":"'.$data['doc_base'].'",
							"date_ship":"'.$data['m_documents_date_ship'].'",
							"doc_base_text":"'.$data['m_documents_base'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_sum":"'.number_format($sum,2,'.','').'",
							"doc_nds18":"'.($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']==18?$nds18:0).'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":"logo_docs.png",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$doc_header_org_address.'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';
						
					//находим платежи по счету
					if(!$data['m_documents_pays']){
						$data['pays']=$buh->getInfoFromInvoice($data['smeta']['m_documents_id']);
						$data['pays']=implode('|',$data['pays']);
					}
					else{
						$data['pays']=$data['m_documents_pays'];
					}
					
					//файлы
					mkdir(__DIR__.'/../../www/files/scan_docs/'.$id);
					$files=array();
					if($data['m_documents_scan[]'])
						foreach($data['m_documents_scan[]'] as $k=>$v){
							$files[$v]['file']=$v;
							$f_size=filesize(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v);
							$f_size=($f_size)?$f_size:0;
							if ($f_size>1048576)
								$f_size=round($f_size/1048576,1).' МБ';
							else
								$f_size=round($f_size/1024,1).' КБ';
							$files[$v]['size']=$f_size;
							//копируем только добавленные файлы
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v)){
								copy(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v,__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v);
							}
						}
					//print_r($files);exit;
					$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

				 	$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_pdf_none`='.$data['m_documents_pdf_none'].',
						`m_documents_nds_itog`='.$data['m_documents_nds_itog'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_pays`=\''.$data['pays'].'\',
						`m_documents_scan`=\''.$files.'\',
						`m_documents_folder`=\''.$foldername.'\';';
					if($sql->query($q)){
						if(!$data['m_documents_pdf_none']){
							$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 3;';
							$res=$sql->query($q);
							foreach($res as $_doc)
								if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
									//договор - основание
									$b=$_doc;
								else
									//сам документ
									$d=$_doc;
							
							$params=json_decode($d['m_documents_params']);
							
							$filesize=documents::act_main(
								array(
									'org'=>$params->org,
									'client'=>$params->client,
									'smeta'=>$params->smeta,
									'doc_base'=>$params->doc_base,
									'date_ship'=>$params->date_ship,
									'doc_base_text'=>$params->doc_base_text,
									'doc_date'=>$params->doc_date,
									'doc_numb'=>$params->doc_numb,
									'doc_sum'=>$params->doc_sum,
									'doc_bar'=>$params->doc_bar,
									'doc_signature'=>$params->doc_signature,
									'doc_logo'=>$params->doc_logo,
									'doc_header_org_name'=>$params->doc_header_org_name,
									'doc_header_org_address'=>$params->doc_header_org_address,
									'doc_header_org_tel'=>$params->doc_header_org_tel,
									'doc_header_org_email'=>$params->doc_header_org_email,
									'items'=>$params->items
								),
								$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
							);
							$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
							$sql->query($q);

							header('Location: '.url().'?success');
						}
						else{
							header('Location: '.url().'?success');
						}
					}
					else{
						header('Location: '.url().'?error');
					}
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
		}
		exit;
	}
	
	
	public static function change($p=array()){
	global $user,$services,$contragents,$info,$documents;
		switch($p['m_documents_templates_id']){
			//прайс-лист
			case '7521205786':
				global $sql,$e;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_decuments_numb']=array(null,null,20);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_itemslist']=array(null,null,3);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_doc_message_info']=array(null,null,400);
				$data['m_services_categories_id[]']=array();
				$data['m_documents_itemslist']=array(null,null,3);
				
				array_walk($data,'check');
				
				if(!$e){
					$data['m_decuments_numb']=$data['m_decuments_numb']?$data['m_decuments_numb']:get_id('m_documents');
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_itemslist']=$data['m_documents_itemslist']?1:0;
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;

					$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$data['m_documents_id'].' LIMIT 1;';
					if($document=$sql->query($q)[0]){
						
						$foldername=$document['m_documents_folder'];
						//папка
						$items=array();
						//категории и услуги прайса
						$categories_all=array();
						if(!$data['m_services_categories_id[]'])
							foreach($services->categories_nodes_id as $k=>$v)
								$data['m_services_categories_id[]'][]=$k.'';

						
						foreach($data['m_services_categories_id[]'] as $_categories){
							//находим все дочерние категории данной категории
							$child_c=array();
							$services->categories_childs($_categories,$child_c);
							//если у категории есть подкатегории - добавляем их id в массив
							if($child_c)
								foreach($child_c as &$_c)
									$_c=$_c['m_services_categories_id'];
							//если нет подкатегорий, но есть позиции - добавляем id в массив
							elseif($services->categories_services($_categories))
								$categories_all[]=$_categories;
							$categories_all=array_merge($categories_all,$child_c);
						}
						$categories_all=array_unique($categories_all);
						
				

						//print_r($categories_all);exit;
						//пробегаемся по указанным для документа категориям
						foreach($categories_all as $_categories){
							$i=0;
							$n=array();
							//находим все родительские категории
							$services->categories_parents($_categories,$n);
							$items[$_categories]['info']=$services->categories_nodes_id[$_categories];
							$items[$_categories]['parents']=$n;
							
							//добавляем элементы прайса, которые находятся в выбранных категориях
							foreach($services->services_id as $_service){
								$_service=$_service[0];
								$c=explode('|',$_service['m_services_categories_id']);
								
								if(in_array($_categories,$c)){
									$items[$_categories]['items'][$i]=$_service;
									unset($items[$_categories]['items'][$i]['m_services_products']);
									$items[$_categories]['items'][$i]['m_services_products']=json_decode($_service['m_services_products'],true);
									$i++;
								}
							}
						}
						
						$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
						
						$data['params']='{
								"doc_template":"'.$document['m_documents_templates_id'].'",
								"org":"'.$data['m_documents_performer'].'",
								"doc_date":"'.$data['m_documents_date'].'",
								"doc_itemslist":"'.$data['m_documents_itemslist'].'",
								"doc_bar":"'.$data['m_documents_bar'].'",
								"doc_signature":"'.$data['m_documents_signature'].'",
								"doc_message_info":"'.$data['m_documents_doc_message_info'].'",
								"doc_logo":"logo_docs.png",
								"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
								"doc_header_org_address":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_address_f'].'",
								"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
								"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
								"items":'.$items.'
							}';
																			
						$params=json_decode($data['params']);
						
						$filesize=documents::price_services(
							array(
								'org'=>$params->org,
								'doc_date'=>$params->doc_date,
								'doc_itemslist'=>$params->doc_itemslist,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'doc_message_info'=>$params->doc_message_info,
								'doc_logo'=>$params->doc_logo,
								'doc_header_org_name'=>$params->doc_header_org_name,
								'doc_header_org_address'=>$params->doc_header_org_address,
								'doc_header_org_tel'=>$params->doc_header_org_tel,
								'doc_header_org_email'=>$params->doc_header_org_email,
								'items'=>$params->items
							),
							$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET 
							`m_documents_performer`='.$data['m_documents_performer'].',
							`m_documents_customer`='.$data['m_documents_customer'].',
							`m_documents_numb`=\''.$data['m_decuments_numb'].'\',
							`m_documents_date`=\''.$data['m_documents_date'].'\',
							`m_documents_params`=\''.$data['params'].'\',
							`m_documents_signature`='.$data['m_documents_signature'].',
							`m_documents_bar`='.$data['m_documents_bar'].',
							`m_documents_update`=\''.dt().'\',
							`m_documents_comment`=\''.$data['m_documents_comment'].'\',
							`m_documents_filesize`=\''.$filesize.'\' 
							WHERE `m_documents_id`='.$document['m_documents_id'].' LIMIT 1;';
						
						if($sql->query($q)){
							header('Location: '.url().'?success');}				
						else{
							header('Location: '.url().'?error');}
					}
					else{
						header('Location: '.url().'?error');}
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//договор поставки разовый
			case '4234525325':
				global $sql,$e;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_order']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_doc_method_pay_cash']=array(null,null,3);
				$data['m_documents_doc_method_pay_bank']=array(null,null,3);
				$data['m_documents_doc_delivery_self']=array(null,null,3);
				$data['m_documents_doc_sum_pre']=array(null,null,20);
				$data['m_documents_doc_delivery_time']=array(null,null,20);
				
				array_walk($data,'check');
				
				if(!$e){
					$document=$documents->getInfo($data['m_documents_id']);
					$id=$data['m_documents_id'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_doc_delivery_time']=$data['m_documents_doc_delivery_time']?$data['m_documents_doc_delivery_time']:3;
					$data['m_documents_doc_sum_pre']=$data['m_documents_doc_sum_pre']?$data['m_documents_doc_sum_pre']:0;
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['invoice']=0;
					
					//находим счет по заказу
					$docs=$documents->getOrderDocs($data['m_documents_order']);
					foreach($docs as $_docs){
						//если среди документов по заказу есть счет на оплату и поставщик с покупателем совпадают
						if($_docs['m_documents_templates_id']==2363374033&&$_docs['m_documents_performer']==$data['m_documents_performer']&&$_docs['m_documents_customer']==$data['m_documents_customer'])
							$data['invoice']=$_docs['m_documents_id'];
					}
	
					//папка
					$foldername=$document['m_documents_folder'];
					if(!file_exists(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername))
						mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$data['m_documents_id'].'00',false,20);
 
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"order":"'.$data['m_documents_order'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"org_director_signature":"http://'.$_SERVER['HTTP_HOST'].'/img/signature/signature.php?type=director",
							"org_stamp":"http://'.$_SERVER['HTTP_HOST'].'/img/signature/signature.php?type=stamp",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_logo":"logo_docs.png",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"m_documents_doc_method_pay_cash":"'.$data['m_documents_doc_method_pay_cash'].'",
							"m_documents_doc_method_pay_bank":"'.$data['m_documents_doc_method_pay_bank'].'",
							"m_documents_doc_sum_pre":"'.$data['m_documents_doc_sum_pre'].'",
							"invoice":'.$data['invoice'].',
							"m_documents_doc_delivery_time":"'.$data['m_documents_doc_delivery_time'].'",
							"m_documents_doc_delivery_self":"'.$data['m_documents_doc_delivery_self'].'"
						}';
				 	$q='UPDATE `formetoo_cdb`.`m_documents` SET 
						`m_documents_id`='.$id.',
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\'
					WHERE `m_documents_id`='.$id.';';
					if($sql->query($q)){

						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$d=$sql->query($q)[0];

						$params=json_decode($d['m_documents_params']);

						$filesize=documents::contract_goods(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'order'=>$params->order,
								'org_stamp'=>$params->org_stamp,
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_logo'=>$params->doc_logo,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'm_documents_doc_method_pay_cash'=>$params->m_documents_doc_method_pay_cash,
								'm_documents_doc_method_pay_bank'=>$params->m_documents_doc_method_pay_bank,
								'm_documents_doc_sum_pre'=>$params->m_documents_doc_sum_pre,
								'invoice'=>$params->invoice,
								'm_documents_doc_delivery_time'=>$params->m_documents_doc_delivery_time,
								'm_documents_doc_delivery_self'=>$params->m_documents_doc_delivery_self
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);
						
						header('Location: '.url().'?success&filepath=/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//договор поставки годовой
			case '4234525326':
				global $sql,$e;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_order']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_scan[]']=array(null,null,19);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				
				array_walk($data,'check');
				
				if(!$e){
					$document=$documents->getInfo($data['m_documents_id']);
					$id=$data['m_documents_id'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['invoice']=0;
					
					//находим счет по заказу
					$docs=$documents->getOrderDocs($data['m_documents_order']);
					foreach($docs as $_docs){
						//если среди документов по заказу есть счет на оплату и поставщик с покупателем совпадают
						if($_docs['m_documents_templates_id']==2363374033&&$_docs['m_documents_performer']==$data['m_documents_performer']&&$_docs['m_documents_customer']==$data['m_documents_customer'])
							$data['invoice']=$_docs['m_documents_id'];
					}
	
					//папка
					$foldername=$document['m_documents_folder'];
					if(!file_exists(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername))
						mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
 
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"order":"'.$data['m_documents_order'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"org_director_signature":"http://'.$_SERVER['HTTP_HOST'].'/img/signature/signature.php?type=director",
							"org_stamp":"http://'.$_SERVER['HTTP_HOST'].'/img/signature/signature.php?type=stamp",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_logo":"logo_docs.png",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_address_f'].'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"invoice":'.$data['invoice'].'
						}';
						
					//файлы
					if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$id))
						mkdir(__DIR__.'/../../www/files/scan_docs/'.$id);
					$files=array();
					if($data['m_documents_scan[]'])
						foreach($data['m_documents_scan[]'] as $k=>$v){
							$files[$v]['file']=$v;
							$f_size=filesize(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v);
							$f_size=($f_size)?$f_size:0;
							if ($f_size>1048576)
								$f_size=round($f_size/1048576,1).' МБ';
							else
								$f_size=round($f_size/1024,1).' КБ';
							$files[$v]['size']=$f_size;
							//копируем только добавленные файлы
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v)){
								copy(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v,__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v);
							}
						}
					//print_r($files);exit;
					$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);		
					
				 	$q='UPDATE `formetoo_cdb`.`m_documents` SET 
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_filesize`=0,
						`m_documents_scan`=\''.$files.'\',
						`m_documents_folder`=\''.$foldername.'\'
						WHERE `m_documents_id`='.$id.';';
					if($sql->query($q)){

						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$d=$sql->query($q)[0];

						$params=json_decode($d['m_documents_params']);

						$filesize=documents::contract_goods_year(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'order'=>$params->order,
								'org_stamp'=>$params->org_stamp,
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_logo'=>$params->doc_logo,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'doc_header_org_name'=>$params->doc_header_org_name,
								'doc_header_org_address'=>$params->doc_header_org_address,
								'doc_header_org_tel'=>$params->doc_header_org_tel,
								'doc_header_org_email'=>$params->doc_header_org_email,
								'm_documents_doc_method_pay_cash'=>$params->m_documents_doc_method_pay_cash,
								'm_documents_doc_method_pay_bank'=>$params->m_documents_doc_method_pay_bank,
								'm_documents_doc_sum_pre'=>$params->m_documents_doc_sum_pre,
								'invoice'=>$params->invoice
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);
						
						header('Location: '.url().'?success&filepath=/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;	
				
			//спецификация к договору поставки годовому
			case '4234525327':
				global $sql,$e;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_invoice']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_scan[]']=array(null,null,400);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_doc_delivery_time']=array(null,null,20);
				
				array_walk($data,'check');
				
				if(!$e){
					$document=$documents->getInfo($data['m_documents_id']);
					$id=$data['m_documents_id'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_doc_delivery_time']=$data['m_documents_doc_delivery_time']?$data['m_documents_doc_delivery_time']:7;
					$data['m_documents_doc_sum_pre']=$data['m_documents_doc_sum_pre']?$data['m_documents_doc_sum_pre']:0;
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					
					//основание, ищем в счете
					foreach($documents->getInfo() as $_d)
						//если есть договор поставки делаем его основанием
						if($_d[0]['m_documents_customer']==$data['m_documents_customer']&&$_d[0]['m_documents_templates_id']==4234525326)
							$data['doc_base']=$_d[0]['m_documents_id'];
					
	
					//папка
					$foldername=$document['m_documents_folder'];
					if(!file_exists(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername))
						mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
 
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"order":"'.$data['m_documents_order'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"base_date":"'.$documents->getInfo($data['doc_base'])['m_documents_date'].'",
							"base_numb":"'.$documents->getInfo($data['doc_base'])['m_documents_numb'].'",
							"doc_logo":1,
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"invoice":'.$data['m_documents_invoice'].',
							"m_documents_doc_delivery_time":"'.$data['m_documents_doc_delivery_time'].'"
						}';
						
					//файлы
					if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$id))
						mkdir(__DIR__.'/../../www/files/scan_docs/'.$id);
					$files=array();
					if($data['m_documents_scan[]'])
						foreach($data['m_documents_scan[]'] as $k=>$v){
							$files[$v]['file']=$v;
							$f_size=filesize(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v);
							$f_size=($f_size)?$f_size:0;
							if ($f_size>1048576)
								$f_size=round($f_size/1048576,1).' МБ';
							else
								$f_size=round($f_size/1024,1).' КБ';
							$files[$v]['size']=$f_size;
							//копируем только добавленные файлы
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v)){
								copy(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v,__DIR__.'/../../www/files/scan_docs/'.$id.'/'.$v);
							}
						}
					//print_r($files);exit;
					$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);	
						
				 	$q='UPDATE `formetoo_cdb`.`m_documents` SET 
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`=0,
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_filesize`=0,
						`m_documents_scan`=\''.$files.'\',
						`m_documents_folder`=\''.$foldername.'\' 
						WHERE `m_documents_id`='.$id.';';
					if($sql->query($q)){

						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$d=$sql->query($q)[0];

						$params=json_decode($d['m_documents_params']);

						$filesize=documents::contract_goods_year_spec(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'order'=>$params->order,
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'base_date'=>$params->base_date,
								'base_numb'=>$params->base_numb,
								'doc_logo'=>$params->doc_logo,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'invoice'=>$params->invoice,
								'm_documents_doc_delivery_time'=>$params->m_documents_doc_delivery_time
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);
						
						header('Location: '.url().'?success&filepath=/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;		
				
			//договор подряда
			case '4022369852':
				global $sql,$e;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_order']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_doc_method_pay_cash']=array(null,null,3);
				$data['m_documents_doc_method_pay_bank']=array(null,null,3);
				$data['m_documents_doc_sum_pre']=array(null,null,20);
				$data['m_documents_doc_sum_phase']=array(null,null,20,null,1);
				$data['m_documents_doc_sum_end']=array(null,null,20,null,1);
				$data['m_documents_doc_guarantee']=array(null,null,20,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$document=$documents->getInfo($data['m_documents_id']);
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$document['m_documents_id'];
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_doc_guarantee']=$data['m_documents_doc_guarantee']?$data['m_documents_doc_guarantee']:12;
					$data['m_documents_doc_sum_end']=$data['m_documents_doc_sum_end']?$data['m_documents_doc_sum_end']:3;
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
	
					//папка
					$foldername=$document['m_documents_folder'];
					if(!file_exists(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername))
						mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$data['m_documents_id'].'00',false,20);
 
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"order":"'.$data['m_documents_order'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"org_director_signature":"http://'.$_SERVER['HTTP_HOST'].'/img/signature/signature.php?type=director",
							"org_stamp":"http://'.$_SERVER['HTTP_HOST'].'/img/signature/signature.php?type=stamp",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_logo":"logo_docs.png",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_address_f'].'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"m_documents_doc_method_pay_cash":"'.$data['m_documents_doc_method_pay_cash'].'",
							"m_documents_doc_method_pay_bank":"'.$data['m_documents_doc_method_pay_bank'].'",
							"m_documents_doc_sum_pre":"'.$data['m_documents_doc_sum_pre'].'",
							"m_documents_doc_sum_phase":"'.$data['m_documents_doc_sum_phase'].'",
							"m_documents_doc_sum_end":"'.$data['m_documents_doc_sum_end'].'",
							"m_documents_doc_guarantee":"'.$data['m_documents_doc_guarantee'].'"
						}';
				 	$q='UPDATE `formetoo_cdb`.`m_documents` SET 
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\' 
						WHERE `m_documents_id`='.$document['m_documents_id'].';';
					if($sql->query($q)){

						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$document['m_documents_id'].' LIMIT 1;';
						$d=$sql->query($q)[0];

						$params=json_decode($d['m_documents_params']);

						$filesize=documents::contract_work(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'order'=>$params->order,
								'org_stamp'=>$params->org_stamp,
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_logo'=>$params->doc_logo,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'doc_header_org_name'=>$params->doc_header_org_name,
								'doc_header_org_address'=>$params->doc_header_org_address,
								'doc_header_org_tel'=>$params->doc_header_org_tel,
								'doc_header_org_email'=>$params->doc_header_org_email,
								'm_documents_doc_method_pay_cash'=>$params->m_documents_doc_method_pay_cash,
								'm_documents_doc_method_pay_bank'=>$params->m_documents_doc_method_pay_bank,
								'm_documents_doc_sum_pre'=>$params->m_documents_doc_sum_pre,
								'm_documents_doc_sum_phase'=>$params->m_documents_doc_sum_phase,
								'm_documents_doc_sum_end'=>$params->m_documents_doc_sum_end,
								'm_documents_doc_guarantee'=>$params->m_documents_doc_guarantee
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$document['m_documents_id'].' LIMIT 1;';
						$sql->query($q);
						
						header('Location: '.url().'?success');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
			
			//акт сверки
			case '5411000236':
				global $sql,$e;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_pays']=array(null,null,500);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_date_from']=array(null,null,19);
				$data['m_documents_date_to']=array(null,null,19);
				$data['m_documents_saldo_start']=array(null,null,null,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$document=$documents->getInfo($data['m_documents_id']);
					$id=$data['m_documents_id'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					//ВЫЧИСЛЯЕМ ПЕРВЫЙ МЕСЯЦ ТЕКУЩЕГО КВАРТАЛА, ЕСЛИ КВАРТАЛ НЕ ЗАПОЛНЕН
					$prev_quarter_start=ceil(dtu('m')/3)*3+1;
					$data['m_documents_date_from']=$data['m_documents_date_from']?$data['m_documents_date_from']:dtc('01.'.$prev_quarter_start.'.0000 00:00:00');
					$data['m_documents_date_to']=$data['m_documents_date_to']?$data['m_documents_date_to']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['m_documents_saldo_start']=$data['m_documents_saldo_start']?$data['m_documents_saldo_start']:0;
					
					$pays=$docs=array();
					//находим оплаты по контрагенту за выбранный период
					$q='SELECT `m_buh_id` FROM `formetoo_cdb`.`m_buh` WHERE 
						`m_buh_performer` IN ('.$data['m_documents_performer'].','.$data['m_documents_customer'].')  AND 
						`m_buh_customer` IN ('.$data['m_documents_performer'].','.$data['m_documents_customer'].') AND
						`m_buh_performer`!=`m_buh_customer` AND
						`m_buh_status_pay`=1 AND 
						`m_buh_cash`=0 AND 
						`m_buh_date` BETWEEN \''.$data['m_documents_date_from'].'\' AND \''.$data['m_documents_date_to'].'\'
						ORDER BY `m_buh_date`;';
					if($res=$sql->query($q)){
						foreach($res as $_res)
							$pays[]=$_res['m_buh_id'];
					}
					//находим реализации по контрагенту за выбранный период
					$q='SELECT `m_documents_id` FROM `formetoo_cdb`.`m_documents` WHERE 
						`m_documents_performer` IN ('.$data['m_documents_performer'].','.$data['m_documents_customer'].') AND 
						`m_documents_customer` IN ('.$data['m_documents_performer'].','.$data['m_documents_customer'].') AND
						`m_documents_performer`!=`m_documents_customer` AND
						`m_documents_templates_id` IN(3552326767,2352663637) AND
						`m_documents_date` BETWEEN \''.$data['m_documents_date_from'].'\' AND \''.$data['m_documents_date_to'].'\'
						ORDER BY `m_documents_date`;';
					if($res=$sql->query($q)){
						foreach($res as $_res)
							$docs[]=$_res['m_documents_id'];
					}
					//папка
					$foldername=$document['m_documents_folder'];
					if(!file_exists(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername))
						mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$id.'00',false,20);
 
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"date_from":"'.$data['m_documents_date_from'].'",
							"date_to":"'.$data['m_documents_date_to'].'",
							"pays":"'.implode('|',$pays).'",
							"docs":"'.implode('|',$docs).'",
							"saldo":"'.$data['m_documents_saldo_start'].'",
							"doc_signature":'.$data['m_documents_signature'].'
						}';
				 	$q='UPDATE `formetoo_cdb`.`m_documents` SET 
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_pays`=\''.$data['m_documents_pays'].'\',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\' 
						WHERE `m_documents_id`='.$id.' LIMIT 1;';
					if($sql->query($q)){

						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$d=$sql->query($q)[0];

						$params=json_decode($d['m_documents_params']);

						$filesize=documents::act_sverki(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'date_from'=>$params->date_from,
								'date_to'=>$params->date_to,
								'pays'=>$params->pays,
								'docs'=>$params->docs,
								'saldo'=>$params->saldo,
								'doc_signature'=>$params->doc_signature
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);
						
						header('Location: '.url().'?success&filepath=/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;	
				
			//конверт
			case '1000223255':
				global $sql,$e;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_address_from']=array(1,null,null,null,1);
				$data['m_documents_address_to']=array(1,null,null,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$document=$documents->getInfo($data['m_documents_id']);
					$id=$data['m_documents_id'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
	
					//папка
					$foldername=$document['m_documents_folder'];
					if(!file_exists(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername))
						mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$data['m_documents_id'].'00',false,20);
 
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"address_from":"'.$data['m_documents_address_from'].'",
							"address_to":"'.$data['m_documents_address_to'].'"
						}';
				 	$q='UPDATE `formetoo_cdb`.`m_documents` SET 
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\'
						WHERE `m_documents_id`='.$id.';';
					if($sql->query($q)){

						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$d=$sql->query($q)[0];

						$params=json_decode($d['m_documents_params']);

						$filesize=documents::envelope(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'address_from'=>$params->address_from,
								'address_to'=>$params->address_to
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$id.' LIMIT 1;';
						$sql->query($q);
						
						header('Location: '.url().'?success&filepath=/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//договор подряда с материалами
			case '4022369853':
				global $sql,$e;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_order']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_doc_method_pay_cash']=array(null,null,3);
				$data['m_documents_doc_method_pay_bank']=array(null,null,3);
				$data['m_documents_doc_sum_pre']=array(null,null,20);
				$data['m_documents_doc_sum_pre_m']=array(null,null,20);
				$data['m_documents_doc_sum_phase']=array(null,null,20,null,1);
				$data['m_documents_doc_sum_end']=array(null,null,20,null,1);
				$data['m_documents_doc_guarantee']=array(null,null,20,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$document=$documents->getInfo($data['m_documents_id']);
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$document['m_documents_id'];
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_doc_guarantee']=$data['m_documents_doc_guarantee']?$data['m_documents_doc_guarantee']:12;
					$data['m_documents_doc_sum_end']=$data['m_documents_doc_sum_end']?$data['m_documents_doc_sum_end']:3;
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
	
					//папка
					$foldername=$document['m_documents_folder'];
					if(!file_exists(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername))
						mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$data['m_documents_id'].'00',false,20);
 
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"order":"'.$data['m_documents_order'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"org_director_signature":"http://'.$_SERVER['HTTP_HOST'].'/img/signature/signature.php?type=director",
							"org_stamp":"http://'.$_SERVER['HTTP_HOST'].'/img/signature/signature.php?type=stamp",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_logo":"logo_docs.png",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_address_f'].'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"m_documents_doc_method_pay_cash":"'.$data['m_documents_doc_method_pay_cash'].'",
							"m_documents_doc_method_pay_bank":"'.$data['m_documents_doc_method_pay_bank'].'",
							"m_documents_doc_sum_pre":"'.$data['m_documents_doc_sum_pre'].'",
							"m_documents_doc_sum_pre_m":"'.$data['m_documents_doc_sum_pre_m'].'",
							"m_documents_doc_sum_phase":"'.$data['m_documents_doc_sum_phase'].'",
							"m_documents_doc_sum_end":"'.$data['m_documents_doc_sum_end'].'",
							"m_documents_doc_guarantee":"'.$data['m_documents_doc_guarantee'].'"
						}';
				 	$q='UPDATE `formetoo_cdb`.`m_documents` SET 
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\' 
						WHERE `m_documents_id`='.$document['m_documents_id'].';';
					if($sql->query($q)){

						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$document['m_documents_id'].' LIMIT 1;';
						$d=$sql->query($q)[0];

						$params=json_decode($d['m_documents_params']);

						$filesize=documents::contract_work_m(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'order'=>$params->order,
								'org_stamp'=>$params->org_stamp,
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_logo'=>$params->doc_logo,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'doc_header_org_name'=>$params->doc_header_org_name,
								'doc_header_org_address'=>$params->doc_header_org_address,
								'doc_header_org_tel'=>$params->doc_header_org_tel,
								'doc_header_org_email'=>$params->doc_header_org_email,
								'm_documents_doc_method_pay_cash'=>$params->m_documents_doc_method_pay_cash,
								'm_documents_doc_method_pay_bank'=>$params->m_documents_doc_method_pay_bank,
								'm_documents_doc_sum_pre'=>$params->m_documents_doc_sum_pre,
								'm_documents_doc_sum_pre_m'=>$params->m_documents_doc_sum_pre_m,
								'm_documents_doc_sum_phase'=>$params->m_documents_doc_sum_phase,
								'm_documents_doc_sum_end'=>$params->m_documents_doc_sum_end,
								'm_documents_doc_guarantee'=>$params->m_documents_doc_guarantee
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$document['m_documents_id'].' LIMIT 1;';
						$sql->query($q);
						
						header('Location: '.url().'?success');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//смета по форме всеумельца
			case '1200369852':
				global $sql,$e,$orders;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_order']=array(1,null,null,10,1);
				$data['m_orders_smeta_additional']=array(null,null,3);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['idroom[]']=array(1,null,null,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					
					$document=$documents->getInfo($data['m_documents_id']);
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$data['m_documents_id'];
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['m_orders_smeta_additional']=$data['m_orders_smeta_additional']?1:0;
					
					//папка
					$foldername=$document['m_documents_folder'];
					if(!file_exists(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername))
						mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$data['m_documents_id'].'00',false,20);
					
					$items=array();
					
					$sum=0;
					$nds18=0;
					
					//пробегаемся по каждой комнате
					foreach($data['idroom[]'] as $_room){
						$data2=array();
						//проверка данных по комнатам
						$data2[$_room.'m_orders_smeta_room_name[]']=array();
						$data2[$_room.'m_orders_smeta_room_length[]']=array();
						$data2[$_room.'m_orders_smeta_room_weight[]']=array();
						$data2[$_room.'m_orders_smeta_room_height[]']=array();
						$data2[$_room.'m_orders_smeta_room_square[]']=array();
						
						$data2[$_room.'m_orders_smeta_room_openings_type[]']=array();
						$data2[$_room.'m_orders_smeta_room_openings_weight[]']=array();
						$data2[$_room.'m_orders_smeta_room_openings_height[]']=array();
						$data2[$_room.'m_orders_smeta_room_openings_depth[]']=array();
						$data2[$_room.'m_orders_smeta_room_openings_length[]']=array();
						$data2[$_room.'m_orders_smeta_room_openings_square[]']=array();
						
						$data2[$_room.'m_orders_smeta_services_id[]']=array();
						$data2[$_room.'m_orders_smeta_services_count[]']=array();
						$data2[$_room.'m_orders_smeta_services_manual_changed[]']=array();
						$data2[$_room.'m_orders_smeta_services_price[]']=array();
						$data2[$_room.'m_orders_smeta_services_sum[]']=array();
						
						array_walk($data2,'check');
						
						//переводим все данные в один массив
						$items[$_room]['room']['name']=$data2[$_room.'m_orders_smeta_room_name[]'][0];
						$items[$_room]['room']['length']=$data2[$_room.'m_orders_smeta_room_length[]'][0];
						$items[$_room]['room']['weight']=$data2[$_room.'m_orders_smeta_room_weight[]'][0];
						$items[$_room]['room']['height']=$data2[$_room.'m_orders_smeta_room_height[]'][0];
						$items[$_room]['room']['square']=$data2[$_room.'m_orders_smeta_room_square[]'][0];
						//проёмы
						foreach($data2[$_room.'m_orders_smeta_room_openings_type[]'] as $k=>$v){
							//еслди площадь проёма не равна 0
							if($data2[$_room.'m_orders_smeta_room_openings_square[]'][$k]){
								$items[$_room]['openings'][$k]['type']=$data2[$_room.'m_orders_smeta_room_openings_type[]'][$k];
								$items[$_room]['openings'][$k]['weight']=$data2[$_room.'m_orders_smeta_room_openings_weight[]'][$k];
								$items[$_room]['openings'][$k]['height']=$data2[$_room.'m_orders_smeta_room_openings_height[]'][$k];
								$items[$_room]['openings'][$k]['depth']=$data2[$_room.'m_orders_smeta_room_openings_depth[]'][$k];
								$items[$_room]['openings'][$k]['length']=$data2[$_room.'m_orders_smeta_room_openings_length[]'][$k];
								$items[$_room]['openings'][$k]['square']=$data2[$_room.'m_orders_smeta_room_openings_square[]'][$k];
							}
						}
						//работы
						foreach($data2[$_room.'m_orders_smeta_services_id[]'] as $k=>$v){
							//если услуга выбрана и сумма по услуге не равна 0
							if($data2[$_room.'m_orders_smeta_services_id[]'][$k]&&$data2[$_room.'m_orders_smeta_services_sum[]'][$k]){
								$items[$_room]['services'][$k]['id']=$data2[$_room.'m_orders_smeta_services_id[]'][$k];
								$items[$_room]['services'][$k]['count']=$data2[$_room.'m_orders_smeta_services_count[]'][$k];
								$items[$_room]['services'][$k]['manual_changed']=$data2[$_room.'m_orders_smeta_services_manual_changed[]'][$k];
								$items[$_room]['services'][$k]['price']=$data2[$_room.'m_orders_smeta_services_price[]'][$k];
								$items[$_room]['services'][$k]['sum']=$data2[$_room.'m_orders_smeta_services_sum[]'][$k];
								$sum+=$items[$_room]['services'][$k]['sum'];
								$nds18+=$items[$_room]['services'][$k]['sum']*.20/1.20;
							}
						}	
					}
					
					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"order":"'.$data['m_documents_order'].'",
							"additional":"'.$data['m_orders_smeta_additional'].'",
							"doc_template":"'.$document['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_sum":"'.$sum.'",
							"doc_nds18":"'.($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']==18?$nds18:0).'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":"logo_docs.png",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_address_f'].'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';
						
				 	$q='UPDATE `formetoo_cdb`.`m_documents` SET 
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\' 
						WHERE `m_documents_id`='.$data['m_documents_id'].';';
					if($sql->query($q)){
					
						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$data['m_documents_id'].' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 2;';
						$res=$sql->query($q);
						foreach($res as $_doc)
							if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
								//договор - основание
								$b=$_doc;
							else
								//смета
								$d=$_doc;
						
						$params=json_decode($d['m_documents_params']);
						
						$filesize=documents::smeta_vseumelec(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'order'=>$params->order,
								'additional'=>$params->additional,
								'base_numb'=>isset($b['m_documents_numb'])?$b['m_documents_numb']:'',
								'base_date'=>isset($b['m_documents_date'])?$b['m_documents_date']:'',
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'doc_logo'=>$params->doc_logo,
								'doc_header_org_name'=>$params->doc_header_org_name,
								'doc_header_org_address'=>$params->doc_header_org_address,
								'doc_header_org_tel'=>$params->doc_header_org_tel,
								'doc_header_org_email'=>$params->doc_header_org_email,
								'items'=>$params->items
							),
							$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$data['m_documents_id'].' LIMIT 1;';
						$sql->query($q);

						header('Location: '.url().'?success');
						
					}
					else
						header('Location: '.url().'?error');
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;	

			//календарный план по форме всеумельца
			case '1356783437':
				global $sql,$e,$orders;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['idstage[]']=array(1,null,null,null,1);
				$data['m_orders_kalendar_sum']=array(null,null,null,null,1);
				array_walk($data,'check');
				
				if(!$e){
					
					$document=$documents->getInfo($data['m_documents_id']);
					$document['m_documents_params']=json_decode($document['m_documents_params']);
					
					$data['smeta']=$documents->getInfo($document['m_documents_params']->smeta);
					$p=json_decode($data['smeta']['m_documents_params']);
					$data['m_documents_order']=$data['smeta']['m_documents_order'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$document['m_documents_id'];
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					
					//папка
					$foldername=$document['m_documents_folder'];
					if(!file_exists(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername))
						mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$document['m_documents_id'].'00',false,20);
					
					$items=array();
					
					//сортируем этапы по дате начала работ
					$stage_sort=array();
					foreach($data['idstage[]'] as $_stage){
						$stage_sort[$_stage]=post($_stage.'m_orders_kalendar_stage_date_start')[0];
					}
					asort($stage_sort);
					
					//пробегаемся по каждому этапу
					//foreach($data['idstage[]'] as $_stage){
					foreach($stage_sort as $_stage=>$_date){
						$data2=array();
						//проверка данных по комнатам
						$data2[$_stage.'m_orders_kalendar_stage_name[]']=array();
						$data2[$_stage.'m_orders_kalendar_stage_date_start[]']=array();
						$data2[$_stage.'m_orders_kalendar_stage_date_end[]']=array();
						$data2[$_stage.'m_orders_kalendar_stage_sum[]']=array();
						
						$data2[$_stage.'m_orders_kalendar_stage_doc_id[]']=array();
						$data2[$_stage.'m_orders_kalendar_stage_services_id[]']=array();
						$data2[$_stage.'m_orders_kalendar_stage_services_room_id[]']=array();
						$data2[$_stage.'m_orders_kalendar_stage_services_count[]']=array();
						$data2[$_stage.'m_orders_kalendar_stage_services_price[]']=array();
						$data2[$_stage.'m_orders_kalendar_stage_services_sum[]']=array();
						
						array_walk($data2,'check');
						
						//если сумма работ по этапу не равна 0
						if($data2[$_stage.'m_orders_kalendar_stage_sum[]'][0]){
						
							$data2[$_stage.'m_orders_kalendar_stage_date_start[]']=$data2[$_stage.'m_orders_kalendar_stage_date_start[]']?$data2[$_stage.'m_orders_kalendar_stage_date_start[]']:dt();
							$data2[$_stage.'m_orders_kalendar_stage_date_end[]']=$data2[$_stage.'m_orders_kalendar_stage_date_end[]']?$data2[$_stage.'m_orders_kalendar_stage_date_end[]']:dt();
							
							//переводим все данные в один массив
							$items[$_stage]['stage']['name']=$data2[$_stage.'m_orders_kalendar_stage_name[]'][0];
							$items[$_stage]['stage']['date_start']=$data2[$_stage.'m_orders_kalendar_stage_date_start[]'][0];
							$items[$_stage]['stage']['date_end']=$data2[$_stage.'m_orders_kalendar_stage_date_end[]'][0];
							$items[$_stage]['stage']['sum']=$data2[$_stage.'m_orders_kalendar_stage_sum[]'][0];
							
							//работы
							foreach($data2[$_stage.'m_orders_kalendar_stage_services_id[]'] as $k=>$v){
								$items[$_stage]['services'][$k]['room_id']=$data2[$_stage.'m_orders_kalendar_stage_services_room_id[]'][$k];
								$items[$_stage]['services'][$k]['id']=$data2[$_stage.'m_orders_kalendar_stage_services_id[]'][$k];
								$items[$_stage]['services'][$k]['count']=$data2[$_stage.'m_orders_kalendar_stage_services_count[]'][$k];
								$items[$_stage]['services'][$k]['price']=$data2[$_stage.'m_orders_kalendar_stage_services_price[]'][$k];
								$items[$_stage]['services'][$k]['sum']=$data2[$_stage.'m_orders_kalendar_stage_services_sum[]'][$k];
							}
						}
					}
					
					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"smeta":"'.$data['smeta']['m_documents_id'].'",
							"additional":"'.(isset($p->additional)&&$p->additional?'1':'0').'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_sum":"'.$data['m_orders_kalendar_sum'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":"logo_docs.png",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_address_f'].'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';

				 	$q='UPDATE `formetoo_cdb`.`m_documents` SET 
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\' 
						WHERE `m_documents_id`='.$document['m_documents_id'].';';
					if($sql->query($q)){
					
						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$document['m_documents_id'].' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 2;';
						$res=$sql->query($q);
						foreach($res as $_doc)
							if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
								//договор - основание
								$b=$_doc;
							else
								//календарный план
								$d=$_doc;
						
						$params=json_decode($d['m_documents_params']);
						
						$filesize=documents::kalendar_vseumelec(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'smeta'=>$params->smeta,
								'additional'=>$params->additional,
								'base_numb'=>isset($b['m_documents_numb'])?$b['m_documents_numb']:'',
								'base_date'=>isset($b['m_documents_date'])?$b['m_documents_date']:'',
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_sum'=>$params->doc_sum,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'doc_logo'=>$params->doc_logo,
								'doc_header_org_name'=>$params->doc_header_org_name,
								'doc_header_org_address'=>$params->doc_header_org_address,
								'doc_header_org_tel'=>$params->doc_header_org_tel,
								'doc_header_org_email'=>$params->doc_header_org_email,
								'items'=>$params->items
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$document['m_documents_id'].' LIMIT 1;';
						$sql->query($q);

						header('Location: '.url().'?success');
						
					}
					else{
						header('Location: '.url().'?error');
					}
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//акт по форме всеумельца
			case '8522102145':
				global $sql,$e,$orders;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['smeta']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);

				$data['m_orders_act_doc_id[]']=array();
				$data['m_orders_act_services_id[]']=array();
				$data['m_orders_act_services_room_id[]']=array();
				$data['m_orders_act_services_count[]']=array();
				$data['m_orders_act_services_price[]']=array();
				$data['m_orders_act_services_sum[]']=array();
				$data['m_orders_act_sum']=array(null,null,null,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$document=$documents->getInfo($data['m_documents_id']);
					$document['m_documents_params']=json_decode($document['m_documents_params']);
					
					$data['smeta']=$documents->getInfo($data['smeta']);
					$p=json_decode($data['smeta']['m_documents_params']);
					$data['m_documents_order']=$data['smeta']['m_documents_order'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$data['m_documents_id'];
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					
					$_address=$info->getAddress($data['m_documents_performer']);
					$_address=change_key($_address,'m_address_type',true);
					$doc_header_org_address=(isset($_address['2'])&&$_address['2'])?$_address['2']['m_address_full']:((isset($_address['1'])&&$_address['1'])?$_address['1']['m_address_full']:'');
					
					//папка
					$foldername=$document['m_documents_folder'];
					if(!file_exists(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername))
						mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$data['m_documents_id'].'00',false,20);
					
					$items=array();
					
					//если сумма работ по акту не равна 0
					if($data['m_orders_act_sum'])
						//работы
						foreach($data['m_orders_act_services_id[]'] as $k=>$v){
							$items[$k]['room_id']=$data['m_orders_act_services_room_id[]'][$k];
							$items[$k]['id']=$data['m_orders_act_services_id[]'][$k];
							$items[$k]['count']=$data['m_orders_act_services_count[]'][$k];
							$items[$k]['price']=$data['m_orders_act_services_price[]'][$k];
							$items[$k]['sum']=$data['m_orders_act_services_sum[]'][$k];
						}
					
					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"smeta":"'.$data['smeta']['m_documents_id'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_sum":"'.$data['m_orders_act_sum'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":"logo_docs.png",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$doc_header_org_address.'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';

				 	$q='UPDATE `formetoo_cdb`.`m_documents` SET 
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\' 
						WHERE `m_documents_id`='.$data['m_documents_id'].';';
					if($sql->query($q)){
					
						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$data['m_documents_id'].' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 2;';
						$res=$sql->query($q);
						foreach($res as $_doc)
							if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
								//договор - основание
								$b=$_doc;
							else
								//календарный план
								$d=$_doc;
						
						$params=json_decode($d['m_documents_params']);
						
						$filesize=documents::act_vseumelec(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'smeta'=>$params->smeta,
								'base_numb'=>isset($b['m_documents_numb'])?$b['m_documents_numb']:'',
								'base_date'=>isset($b['m_documents_date'])?$b['m_documents_date']:'',
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_sum'=>$params->doc_sum,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'doc_logo'=>$params->doc_logo,
								'doc_header_org_name'=>$params->doc_header_org_name,
								'doc_header_org_address'=>$params->doc_header_org_address,
								'doc_header_org_tel'=>$params->doc_header_org_tel,
								'doc_header_org_email'=>$params->doc_header_org_email,
								'items'=>$params->items
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$data['m_documents_id'].' LIMIT 1;';
						$sql->query($q);

						header('Location: '.url().'?success');
						
					}
					else{
						header('Location: '.url().'?error');
					}
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//ведомость рабочих по форме всеумельца
			case '3522102145':
				global $sql,$e,$orders;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['smeta']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_noprice']=array(null,null,3);

				$data['m_orders_act_doc_id[]']=array();
				$data['m_orders_act_services_id[]']=array();
				$data['m_orders_act_services_room_id[]']=array();
				$data['m_orders_act_services_count[]']=array();
				$data['m_orders_act_services_price[]']=array();
				$data['m_orders_act_services_sum[]']=array();
				$data['m_orders_act_sum']=array(null,null,null,null,1);
				
				array_walk($data,'check');
				
				if(!$e){
					$document=$documents->getInfo($data['m_documents_id']);
					$document['m_documents_params']=json_decode($document['m_documents_params']);
					
					$data['smeta']=$documents->getInfo($data['smeta']);
					$p=json_decode($data['smeta']['m_documents_params']);
					$data['m_documents_order']=$data['smeta']['m_documents_order'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$data['m_documents_id'];
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['m_documents_noprice']=$data['m_documents_noprice']?1:0;
					
					//папка
					$foldername=$document['m_documents_folder'];
					if(!file_exists(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername))
						mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$data['m_documents_id'].'00',false,20);
					
					$items=array();
					
					//если сумма работ по акту не равна 0
					if($data['m_orders_act_sum'])
						//работы
						foreach($data['m_orders_act_services_id[]'] as $k=>$v){
							$items[$k]['room_id']=$data['m_orders_act_services_room_id[]'][$k];
							$items[$k]['id']=$data['m_orders_act_services_id[]'][$k];
							$items[$k]['count']=$data['m_orders_act_services_count[]'][$k];
							$items[$k]['price']=$data['m_orders_act_services_price[]'][$k];
							$items[$k]['sum']=$data['m_orders_act_services_sum[]'][$k];
						}
					
					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"smeta":"'.$data['smeta']['m_documents_id'].'",
							"noprice":"'.$data['m_documents_noprice'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_sum":"'.$data['m_orders_act_sum'].'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":"logo_docs.png",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_address_f'].'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';

				 	$q='UPDATE `formetoo_cdb`.`m_documents` SET 
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_folder`=\''.$foldername.'\' 
						WHERE `m_documents_id`='.$data['m_documents_id'].';';
					if($sql->query($q)){
					
						$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$data['m_documents_id'].' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 2;';
						$res=$sql->query($q);
						foreach($res as $_doc)
							if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
								//договор - основание
								$b=$_doc;
							else
								//календарный план
								$d=$_doc;
						
						$params=json_decode($d['m_documents_params']);
						
						$filesize=documents::vedomost_vseumelec(
							array(
								'org'=>$params->org,
								'client'=>$params->client,
								'smeta'=>$params->smeta,
								'noprice'=>$params->noprice,
								'base_numb'=>isset($b['m_documents_numb'])?$b['m_documents_numb']:'',
								'base_date'=>isset($b['m_documents_date'])?$b['m_documents_date']:'',
								'doc_date'=>$params->doc_date,
								'doc_numb'=>$params->doc_numb,
								'doc_sum'=>$params->doc_sum,
								'doc_bar'=>$params->doc_bar,
								'doc_signature'=>$params->doc_signature,
								'doc_logo'=>$params->doc_logo,
								'doc_header_org_name'=>$params->doc_header_org_name,
								'doc_header_org_address'=>$params->doc_header_org_address,
								'doc_header_org_tel'=>$params->doc_header_org_tel,
								'doc_header_org_email'=>$params->doc_header_org_email,
								'items'=>$params->items
							),
							$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
						);
						
						$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$data['m_documents_id'].' LIMIT 1;';
						$sql->query($q);

						header('Location: '.url().'?success');
						
					}
					else{
						header('Location: '.url().'?error');
					}
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//счет на оплату
			case '2363374033':
				global $sql,$e,$orders,$buh;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_rs']=array(1,null,null,10,1);
				$data['m_documents_order']=array(1,null,null,10,1);
				$data['m_orders_smeta_additional']=array(null,null,3);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_pays']=array(null,null,500);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_nds_itog']=array(null,null,3);
				$data['m_documents_pdf_none']=array(null,null,3);
				$data['m_invoice_date_expire']=array(null,null,19);
				$data['m_invoice_attention']=array(null,null,200);
				$data['idroom[]']=array(1,null,null,null,1);
				$data['m_documents_scan[]']=array(null,null,400);
				
				array_walk($data,'check');
				
				if(!$e){
					
					$document=$documents->getInfo($data['m_documents_id']);
					$document['m_documents_params']=json_decode($document['m_documents_params']);
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$data['m_documents_id'];
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_invoice_date_expire']=$data['m_invoice_date_expire']?$data['m_invoice_date_expire']:dtu(dtc('','+ 3 weekdays'),'d.m.Y');
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_nds_itog']=$data['m_documents_nds_itog']?1:0;
					$data['m_documents_pdf_none']=$data['m_documents_pdf_none']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['m_invoice_attention']=transform::typography($data['m_invoice_attention']);
					$data['m_invoice_terms']=post('m_invoice_terms',array(),array(),1);
					$_address=$info->getAddress($data['m_documents_performer']);
					$_address=change_key($_address,'m_address_type',true);
					$doc_header_org_address=(isset($_address['4'])&&$_address['4'])?$_address['4']['m_address_full']:((isset($_address['1'])&&$_address['1'])?$_address['1']['m_address_full']:'');

					//папка
					$foldername=$document['m_documents_folder'];
					if(!file_exists(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername))
						mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$data['m_documents_id'].'00',false,20);
					
					
					$items=array();
					
					$sum=0;
					$nds18=0;
					
					//пробегаемся по каждой комнате
					foreach($data['idroom[]'] as $_room){
						$data2=array();
						//проверка данных по комнатам
						$data2[$_room.'m_orders_smeta_room_name[]']=array();
						
						$data2[$_room.'m_orders_smeta_services_id[]']=array();
						$data2[$_room.'m_orders_smeta_services_count[]']=array();
						$data2[$_room.'m_orders_smeta_services_manual_changed[]']=array();
						$data2[$_room.'m_orders_smeta_services_price[]']=array();
						$data2[$_room.'m_orders_smeta_services_nds[]']=array();
						$data2[$_room.'m_orders_smeta_services_sum[]']=array();
						$data2[$_room.'m_orders_smeta_table[]']=array();
						
						array_walk($data2,'check');
						
						//переводим все данные в один массив
						$items[$_room]['room']['name']=$data2[$_room.'m_orders_smeta_room_name[]'][0];
						
						//работы
						foreach($data2[$_room.'m_orders_smeta_services_id[]'] as $k=>$v){
							//если услуга выбрана и сумма по услуге не равна 0
							if($data2[$_room.'m_orders_smeta_services_id[]'][$k]&&$data2[$_room.'m_orders_smeta_services_sum[]'][$k]){
								$nds_item=isset($data2[$_room.'m_orders_smeta_services_nds[]'][$k])?($data2[$_room.'m_orders_smeta_services_nds[]'][$k]==-1?0:$data2[$_room.'m_orders_smeta_services_nds[]'][$k]):20;
								$items[$_room]['services'][$k]['id']=$data2[$_room.'m_orders_smeta_services_id[]'][$k];
								$items[$_room]['services'][$k]['count']=$data2[$_room.'m_orders_smeta_services_count[]'][$k];
								$items[$_room]['services'][$k]['manual_changed']=$data2[$_room.'m_orders_smeta_services_manual_changed[]'][$k];
								$items[$_room]['services'][$k]['nds']=isset($data2[$_room.'m_orders_smeta_services_nds[]'][$k])?$data2[$_room.'m_orders_smeta_services_nds[]'][$k]:$orders->orders_id[$data['m_documents_order']][0]['m_orders_nds'];
								$items[$_room]['services'][$k]['price']=$data2[$_room.'m_orders_smeta_services_price[]'][$k];
								$items[$_room]['services'][$k]['sum']=$data2[$_room.'m_orders_smeta_services_sum[]'][$k];
								$items[$_room]['services'][$k]['table']=$data2[$_room.'m_orders_smeta_table[]'][$k];
								$sum+=$items[$_room]['services'][$k]['sum'];
								$nds18+=$items[$_room]['services'][$k]['sum']*($nds_item/100)/(1+$nds_item/100);
							}
						}	
					}
					
					//если НДС общим итогом
					if($data['m_documents_nds_itog']){
						$nds18=$sum*.20;
						$sum+=$nds18;
					}

					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"order":"'.$data['m_documents_order'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_sum":"'.number_format($sum,2,'.','').'",
							"doc_date_expire":"'.$data['m_invoice_date_expire'].'",
							"doc_attention":"'.$data['m_invoice_attention'].'",
							"doc_terms":'.json_encode($data['m_invoice_terms'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).',
							"doc_nds18":"'.$nds18.'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":1,
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$doc_header_org_address.'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';
						
					//файлы
					if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id']))
						mkdir(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id']);
					$files=array();
					if($data['m_documents_scan[]'])
						foreach($data['m_documents_scan[]'] as $k=>$v){
							$files[$v]['file']=$v;
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v))
								$f_size=filesize(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v);
							else
								$f_size=filesize(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v);
							$f_size=($f_size)?$f_size:0;
							if ($f_size>1048576)
								$f_size=round($f_size/1048576,1).' МБ';
							else
								$f_size=round($f_size/1024,1).' КБ';
							$files[$v]['size']=$f_size;
							//копируем только добавленные файлы
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v)){
								copy(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v,__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v);
							}
						}
					//print_r($files);exit;
					$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);	
					
				 	$q='UPDATE `formetoo_cdb`.`m_documents` SET 
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_pdf_none`='.$data['m_documents_pdf_none'].',
						`m_documents_nds_itog`='.$data['m_documents_nds_itog'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_pays`=\''.$data['m_documents_pays'].'\',
						`m_documents_scan`=\''.$files.'\',
						`m_documents_folder`=\''.$foldername.'\' 
						WHERE `m_documents_id`='.$data['m_documents_id'].';';
					if($sql->query($q)){
						if(!$data['m_documents_pdf_none']){
					
							$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$data['m_documents_id'].' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 2;';
							$res=$sql->query($q);
							foreach($res as $_doc)
								if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
									//договор - основание
									$b=$_doc;
								else
									//смета
									$d=$_doc;
							
							$params=json_decode($d['m_documents_params']);
							
							$filesize=documents::invoice(
								array(
									'org'=>$params->org,
									'client'=>$params->client,
									'order'=>$params->order,
									'base_numb'=>isset($b['m_documents_numb'])?$b['m_documents_numb']:'',
									'base_date'=>isset($b['m_documents_date'])?$b['m_documents_date']:'',
									'doc_date'=>$params->doc_date,
									'doc_numb'=>$params->doc_numb,
									'doc_bar'=>$params->doc_bar,
									'doc_date_expire'=>$params->doc_date_expire,
									'doc_attention'=>$params->doc_attention,
									'doc_terms'=>$params->doc_terms,
									'doc_signature'=>$params->doc_signature,
									'doc_logo'=>$params->doc_logo,
									'doc_header_org_name'=>$params->doc_header_org_name,
									'doc_header_org_address'=>$params->doc_header_org_address,
									'doc_header_org_tel'=>$params->doc_header_org_tel,
									'doc_header_org_email'=>$params->doc_header_org_email,
									'items'=>$params->items
								),
								$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
							);
							
							$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$data['m_documents_id'].' LIMIT 1;';
							$sql->query($q);
							
							header('Location: '.url().'?success&id='.$data['m_documents_id'].'&m_documents_templates_id='.$data['m_documents_templates_id'].'&m_documents_order='.$data['m_documents_order'].'&filepath=/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf');
						}
						else{
							header('Location: '.url().'?success');
						}
					}
					else{
						header('Location: '.url().'?error');
					}
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
		//УПД
		case '3552326767':
				global $sql,$e,$orders,$buh;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_agent']=array(null,null,10,null,1);
				$data['smeta']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_date_ship']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_base']=array(null,null,180);
				$data['m_documents_status_otchet']=array(null,null,10);
				$data['m_documents_pays']=array(null,null,500);
				$data['m_documents_nds_itog']=array(null,null,3);
				$data['m_documents_pdf_none']=array(null,null,3);
				
				$data['m_orders_act_doc_id[]']=array();
				$data['m_orders_act_services_id[]']=array();
				$data['m_orders_act_services_room_id[]']=array();
				$data['m_orders_act_services_count[]']=array();
				$data['m_orders_act_services_price[]']=array();
				$data['m_orders_act_services_sum[]']=array();
				$data['m_orders_act_services_nds[]']=array();
				$data['m_orders_act_services_table[]']=array();
				$data['m_orders_act_sum']=array(null,null,null,null,1);
				$data['m_documents_scan[]']=array(null,null,400);
				
				array_walk($data,'check');
				
				if(!$e){
					$document=$documents->getInfo($data['m_documents_id']);
					
					$data['smeta']=$documents->getInfo($data['smeta']);
					$data['m_documents_order']=$data['smeta']['m_documents_order'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_date_ship']=$data['m_documents_date_ship']?$data['m_documents_date_ship']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['m_documents_nds_itog']=$data['m_documents_nds_itog']?1:0;
					$data['m_documents_pdf_none']=$data['m_documents_pdf_none']?1:0;
					$data['m_documents_status_otchet']=$data['m_documents_status_otchet']?$data['m_documents_status_otchet']:'01';
					
					$_address=$info->getAddress($data['m_documents_performer']);
					$_address=change_key($_address,'m_address_type',true);
					$doc_header_org_address=(isset($_address['2'])&&$_address['2'])?$_address['2']['m_address_full']:((isset($_address['1'])&&$_address['1'])?$_address['1']['m_address_full']:'');
					
					//папка
					$foldername=$document['m_documents_folder'];
					if(!file_exists(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername))
						mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$data['m_documents_id'].'00',false,20);
					
					//основание УПД, ищем в заказе договор или счет
					foreach($documents->getInfo() as $_d)
						//если есть договор поставки в том же заказе, что и смета, делаем его основанием
						if($_d[0]['m_documents_order']==$data['m_documents_order']&&$_d[0]['m_documents_templates_id']==4234525325)
							$data['doc_base']=$_d[0]['m_documents_id'];
						//если договора нет - основанием делаем счет
						else
							$data['doc_base']=$data['smeta']['m_documents_id'];
			
					$sum=0;
					$nds18=0;
					
					$items=array();	
					//если сумма работ по акту не равна 0
					if($data['m_orders_act_sum'])
						//работы
						foreach($data['m_orders_act_services_id[]'] as $k=>$v){
							$items[$k]['room_id']=$data['m_orders_act_services_room_id[]'][$k];
							$items[$k]['id']=$data['m_orders_act_services_id[]'][$k];
							$items[$k]['count']=$data['m_orders_act_services_count[]'][$k];
							$items[$k]['price']=$data['m_orders_act_services_price[]'][$k];
							$items[$k]['nds']=isset($data['m_orders_act_services_nds[]'][$k])?$data['m_orders_act_services_nds[]'][$k]:($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']);
							$items[$k]['sum']=$data['m_orders_act_services_sum[]'][$k];
							$items[$k]['table']=$data['m_orders_act_services_table[]'][$k];
							$sum+=$items[$k]['sum'];
							$nds18+=$items[$k]['nds']!=-1?$items[$k]['sum']*($items[$k]['nds']/100)/(1+$items[$k]['nds']/100):0;
						}
					
					//если НДС общим итогом
					if($data['m_documents_nds_itog']){
						$nds18=$sum*($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']/100);
						$sum+=$nds18;
					}

					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"agent":"'.$data['m_documents_agent'].'",
							"smeta":"'.$data['smeta']['m_documents_id'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"date_ship":"'.$data['m_documents_date_ship'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_base":"'.$data['doc_base'].'",
							"doc_status_otchet":"'.$data['m_documents_status_otchet'].'",
							"doc_base_text":"'.$data['m_documents_base'].'",
							"doc_sum":"'.number_format($data['m_orders_act_sum'],2,'.','').'",
							"doc_nds18":"'.round($nds18,2).'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":"logo_docs.png",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$doc_header_org_address.'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';
						
					//находим платежи по счету
					if(!$data['m_documents_pays']){
						$data['pays']=$buh->getInfoFromInvoice($data['smeta']['m_documents_id']);
						$data['pays']=implode('|',$data['pays']);
					}
					else{
						$data['pays']=$data['m_documents_pays'];
					}
					
					//файлы
					if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id']))
						mkdir(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id']);
					$files=array();
					if($data['m_documents_scan[]'])
						foreach($data['m_documents_scan[]'] as $k=>$v){
							$files[$v]['file']=$v;
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v))
								$f_size=filesize(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v);
							else
								$f_size=filesize(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v);
							$f_size=($f_size)?$f_size:0;
							if ($f_size>1048576)
								$f_size=round($f_size/1048576,1).' МБ';
							else
								$f_size=round($f_size/1024,1).' КБ';
							$files[$v]['size']=$f_size;
							//копируем только добавленные файлы
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v)){
								copy(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v,__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v);
							}
						}
					//print_r($files);exit;
					$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

				 	$q='UPDATE `formetoo_cdb`.`m_documents` SET 
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_pdf_none`='.$data['m_documents_pdf_none'].',
						`m_documents_nds_itog`='.$data['m_documents_nds_itog'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_pays`=\''.$data['pays'].'\',
						`m_documents_scan`=\''.$files.'\',
						`m_documents_folder`=\''.$foldername.'\'
					WHERE `m_documents_id`='.$data['m_documents_id'].';';
					if($sql->query($q)){
						if(!$data['m_documents_pdf_none']){
					
							$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$data['m_documents_id'].' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 3;';
							$res=$sql->query($q);
							foreach($res as $_doc)
								if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
									//договор - основание
									$b=$_doc;
								else
									//сам документ
									$d=$_doc;
							
							$params=json_decode($d['m_documents_params']);
							
							$filesize=documents::upd(
								array(
									'org'=>$params->org,
									'client'=>$params->client,
									'smeta'=>$params->smeta,
									'doc_base'=>$params->doc_base,
									'date_ship'=>$params->date_ship,
									'doc_base_text'=>$params->doc_base_text,
									'doc_date'=>$params->doc_date,
									'doc_numb'=>$params->doc_numb,
									'doc_sum'=>$params->doc_sum,
									'doc_nds18'=>$params->doc_nds18,
									'doc_bar'=>$params->doc_bar,
									'doc_signature'=>$params->doc_signature,
									'doc_logo'=>$params->doc_logo,
									'doc_header_org_name'=>$params->doc_header_org_name,
									'doc_header_org_address'=>$params->doc_header_org_address,
									'doc_header_org_tel'=>$params->doc_header_org_tel,
									'doc_header_org_email'=>$params->doc_header_org_email,
									'items'=>$params->items
								),
								$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
							);
							$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$data['m_documents_id'].' LIMIT 1;';
							$sql->query($q);

							header('Location: '.url().'?success&id='.$data['m_documents_id'].'&m_documents_templates_id='.$data['m_documents_templates_id'].'&m_documents_order='.$data['m_documents_order'].'&filepath=/files/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename'].'.pdf');
						}
						else{
							header('Location: '.url().'?success');
						}
					}
					else{
						header('Location: '.url().'?error');
					}
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;	
				
			//СФ
			case '2352663637':
				global $sql,$e,$orders,$buh;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_agent']=array(null,null,10,null,1);
				$data['smeta']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_date_ship']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_base']=array(null,null,180);
				$data['m_documents_status_otchet']=array(null,null,10);
				$data['m_documents_pays']=array(null,null,500);
				$data['m_documents_nds_itog']=array(null,null,3);
				$data['m_documents_pdf_none']=array(null,null,3);
				
				$data['m_orders_act_doc_id[]']=array();
				$data['m_orders_act_services_id[]']=array();
				$data['m_orders_act_services_room_id[]']=array();
				$data['m_orders_act_services_count[]']=array();
				$data['m_orders_act_services_price[]']=array();
				$data['m_orders_act_services_sum[]']=array();
				$data['m_orders_act_services_table[]']=array();
				$data['m_orders_act_sum']=array(null,null,null,null,1);
				$data['m_documents_scan[]']=array(null,null,400);
				
				array_walk($data,'check');
				
				if(!$e){
					$document=$documents->getInfo($data['m_documents_id']);
					
					$data['smeta']=$documents->getInfo($data['smeta']);
					$data['m_documents_order']=$data['smeta']['m_documents_order'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_date_ship']=$data['m_documents_date_ship']?$data['m_documents_date_ship']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['m_documents_nds_itog']=$data['m_documents_nds_itog']?1:0;
					$data['m_documents_pdf_none']=$data['m_documents_pdf_none']?1:0;
					
					$_address=$info->getAddress($data['m_documents_performer']);
					$_address=change_key($_address,'m_address_type',true);
					$doc_header_org_address=(isset($_address['2'])&&$_address['2'])?$_address['2']['m_address_full']:((isset($_address['1'])&&$_address['1'])?$_address['1']['m_address_full']:'');
					
					//папка
					$foldername=$document['m_documents_folder'];
					if(!file_exists(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername))
						mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$data['m_documents_id'].'00',false,20);
					
					//основание УПД, ищем в заказе договор или счет
					foreach($documents->getInfo() as $_d)
						//если есть договор поставки в том же заказе, что и смета, делаем его основанием
						if($_d[0]['m_documents_order']==$data['m_documents_order']&&$_d[0]['m_documents_templates_id']==4234525325)
							$data['doc_base']=$_d[0]['m_documents_id'];
						//если договора нет - основанием делаем счет
						else
							$data['doc_base']=$data['smeta']['m_documents_id'];
			
					$sum=0;
					$nds18=0;
					
					$items=array();	
					//если сумма работ по акту не равна 0
					if($data['m_orders_act_sum'])
						//работы
						foreach($data['m_orders_act_services_id[]'] as $k=>$v){
							$items[$k]['room_id']=$data['m_orders_act_services_room_id[]'][$k];
							$items[$k]['id']=$data['m_orders_act_services_id[]'][$k];
							$items[$k]['count']=$data['m_orders_act_services_count[]'][$k];
							$items[$k]['price']=$data['m_orders_act_services_price[]'][$k];
							$items[$k]['nds']=isset($data['m_orders_act_services_nds[]'][$k])?$data['m_orders_act_services_nds[]'][$k]:($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']);
							$items[$k]['sum']=$data['m_orders_act_services_sum[]'][$k];
							$items[$k]['table']=$data['m_orders_act_services_table[]'][$k];
							$sum+=$items[$k]['sum'];
							$nds18+=$items[$k]['nds']!=-1?$items[$k]['sum']*($items[$k]['nds']/100)/(1+$items[$k]['nds']/100):0;
						}
						
					//если НДС общим итогом
					if($data['m_documents_nds_itog']){
						$nds18=$sum*($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']/100);
						$sum+=$nds18;
					}

					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"agent":"'.$data['m_documents_agent'].'",
							"smeta":"'.$data['smeta']['m_documents_id'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"date_ship":"'.$data['m_documents_date_ship'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_base":"'.$data['doc_base'].'",
							"doc_status_otchet":"'.$data['m_documents_status_otchet'].'",
							"doc_base_text":"'.$data['m_documents_base'].'",
							"doc_sum":"'.number_format($data['m_orders_act_sum'],2,'.','').'",
							"doc_nds18":"'.($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']!=-1?$nds18:0).'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":"logo_docs.png",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$doc_header_org_address.'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';
						
					//находим платежи по счету
					if(!$data['m_documents_pays']){
						$data['pays']=$buh->getInfoFromInvoice($data['smeta']['m_documents_id']);
						$data['pays']=implode('|',$data['pays']);
					}
					else{
						$data['pays']=$data['m_documents_pays'];
					}
					
					//файлы
					if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id']))
						mkdir(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id']);
					$files=array();
					if($data['m_documents_scan[]'])
						foreach($data['m_documents_scan[]'] as $k=>$v){
							$files[$v]['file']=$v;
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v))
								$f_size=filesize(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v);
							else
								$f_size=filesize(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v);
							$f_size=($f_size)?$f_size:0;
							if ($f_size>1048576)
								$f_size=round($f_size/1048576,1).' МБ';
							else
								$f_size=round($f_size/1024,1).' КБ';
							$files[$v]['size']=$f_size;
							//копируем только добавленные файлы
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v)){
								copy(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v,__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v);
							}
						}
					//print_r($files);exit;
					$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

				 	$q='UPDATE `formetoo_cdb`.`m_documents` SET 
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_pdf_none`='.$data['m_documents_pdf_none'].',
						`m_documents_nds_itog`='.$data['m_documents_nds_itog'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_pays`=\''.$data['pays'].'\',
						`m_documents_scan`=\''.$files.'\',
						`m_documents_folder`=\''.$foldername.'\'
					WHERE `m_documents_id`='.$data['m_documents_id'].';';
					if($sql->query($q)){
						if(!$data['m_documents_pdf_none']){
					
							$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$data['m_documents_id'].' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 3;';
							$res=$sql->query($q);
							foreach($res as $_doc)
								if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
									//договор - основание
									$b=$_doc;
								else
									//сам документ
									$d=$_doc;
							
							$params=json_decode($d['m_documents_params']);
							
							$filesize=documents::sf(
								array(
									'org'=>$params->org,
									'client'=>$params->client,
									'smeta'=>$params->smeta,
									'doc_base'=>$params->doc_base,
									'date_ship'=>$params->date_ship,
									'doc_base_text'=>$params->doc_base_text,
									'doc_date'=>$params->doc_date,
									'doc_numb'=>$params->doc_numb,
									'doc_sum'=>$params->doc_sum,
									'doc_bar'=>$params->doc_bar,
									'doc_signature'=>$params->doc_signature,
									'doc_logo'=>$params->doc_logo,
									'doc_header_org_name'=>$params->doc_header_org_name,
									'doc_header_org_address'=>$params->doc_header_org_address,
									'doc_header_org_tel'=>$params->doc_header_org_tel,
									'doc_header_org_email'=>$params->doc_header_org_email,
									'items'=>$params->items
								),
								$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
							);
							$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$data['m_documents_id'].' LIMIT 1;';
							$sql->query($q);

							header('Location: '.url().'?success');
						}
						else{
							header('Location: '.url().'?success');
						}
					}
					else{
						header('Location: '.url().'?error');
					}
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//ТН
			case '4359660563':
				global $sql,$e,$orders,$buh;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_agent']=array(null,null,10,null,1);
				$data['smeta']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_date_ship']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_base']=array(null,null,180);
				$data['m_documents_pays']=array(null,null,500);
				$data['m_documents_nds_itog']=array(null,null,3);
				$data['m_documents_pdf_none']=array(null,null,3);
				
				$data['m_orders_act_doc_id[]']=array();
				$data['m_orders_act_services_id[]']=array();
				$data['m_orders_act_services_room_id[]']=array();
				$data['m_orders_act_services_count[]']=array();
				$data['m_orders_act_services_price[]']=array();
				$data['m_orders_act_services_sum[]']=array();
				$data['m_orders_act_services_table[]']=array();
				$data['m_orders_act_sum']=array(null,null,null,null,1);
				$data['m_documents_scan[]']=array(null,null,400);
				
				array_walk($data,'check');
				
				if(!$e){
					$document=$documents->getInfo($data['m_documents_id']);
					
					$data['smeta']=$documents->getInfo($data['smeta']);
					$data['m_documents_order']=$data['smeta']['m_documents_order'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_date_ship']=$data['m_documents_date_ship']?$data['m_documents_date_ship']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['m_documents_nds_itog']=$data['m_documents_nds_itog']?1:0;
					$data['m_documents_pdf_none']=$data['m_documents_pdf_none']?1:0;
					
					$_address=$info->getAddress($data['m_documents_performer']);
					$_address=change_key($_address,'m_address_type',true);
					$doc_header_org_address=(isset($_address['2'])&&$_address['2'])?$_address['2']['m_address_full']:((isset($_address['1'])&&$_address['1'])?$_address['1']['m_address_full']:'');
					
					//папка
					$foldername=$document['m_documents_folder'];
					if(!file_exists(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername))
						mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$data['m_documents_id'].'00',false,20);
					
					//основание УПД, ищем в заказе договор или счет
					foreach($documents->getInfo() as $_d)
						//если есть договор поставки в том же заказе, что и смета, делаем его основанием
						if($_d[0]['m_documents_order']==$data['m_documents_order']&&$_d[0]['m_documents_templates_id']==4234525325)
							$data['doc_base']=$_d[0]['m_documents_id'];
						//если договора нет - основанием делаем счет
						else
							$data['doc_base']=$data['smeta']['m_documents_id'];
			
					$sum=0;
					$nds18=0;
					
					$items=array();	
					//если сумма работ по акту не равна 0
					if($data['m_orders_act_sum'])
						//работы
						foreach($data['m_orders_act_services_id[]'] as $k=>$v){
							$items[$k]['room_id']=$data['m_orders_act_services_room_id[]'][$k];
							$items[$k]['id']=$data['m_orders_act_services_id[]'][$k];
							$items[$k]['count']=$data['m_orders_act_services_count[]'][$k];
							$items[$k]['price']=$data['m_orders_act_services_price[]'][$k];
							$items[$k]['nds']=isset($data['m_orders_act_services_nds[]'][$k])?$data['m_orders_act_services_nds[]'][$k]:($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']);
							$items[$k]['sum']=$data['m_orders_act_services_sum[]'][$k];
							$items[$k]['table']=$data['m_orders_act_services_table[]'][$k];
							$sum+=$items[$k]['sum'];
							$nds18+=$items[$k]['nds']!=-1?$items[$k]['sum']*($items[$k]['nds']/100)/(1+$items[$k]['nds']/100):0;
						}
						
					//если НДС общим итогом
					if($data['m_documents_nds_itog']){
						$nds18=$sum*($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']/100);
						$sum+=$nds18;
					}

					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"agent":"'.$data['m_documents_agent'].'",
							"smeta":"'.$data['smeta']['m_documents_id'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"date_ship":"'.$data['m_documents_date_ship'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_base":"'.$data['doc_base'].'",
							"doc_base_text":"'.$data['m_documents_base'].'",
							"doc_sum":"'.number_format($data['m_orders_act_sum'],2,'.','').'",
							"doc_nds18":"'.($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']==18?$nds18:0).'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":"logo_docs.png",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$doc_header_org_address.'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';
						
					//находим платежи по счету
					if(!$data['m_documents_pays']){
						$data['pays']=$buh->getInfoFromInvoice($data['smeta']['m_documents_id']);
						$data['pays']=implode('|',$data['pays']);
					}
					else{
						$data['pays']=$data['m_documents_pays'];
					}
					
					//файлы
					if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id']))
						mkdir(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id']);
					$files=array();
					if($data['m_documents_scan[]'])
						foreach($data['m_documents_scan[]'] as $k=>$v){
							$files[$v]['file']=$v;
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v))
								$f_size=filesize(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v);
							else
								$f_size=filesize(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v);
							$f_size=($f_size)?$f_size:0;
							if ($f_size>1048576)
								$f_size=round($f_size/1048576,1).' МБ';
							else
								$f_size=round($f_size/1024,1).' КБ';
							$files[$v]['size']=$f_size;
							//копируем только добавленные файлы
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v)){
								copy(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v,__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v);
							}
						}
					//print_r($files);exit;
					$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

				 	$q='UPDATE `formetoo_cdb`.`m_documents` SET 
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_pdf_none`='.$data['m_documents_pdf_none'].',
						`m_documents_nds_itog`='.$data['m_documents_nds_itog'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_pays`=\''.$data['pays'].'\',
						`m_documents_scan`=\''.$files.'\',
						`m_documents_folder`=\''.$foldername.'\'
					WHERE `m_documents_id`='.$data['m_documents_id'].';';
					if($sql->query($q)){
						if(!$data['m_documents_pdf_none']){
					
							$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$data['m_documents_id'].' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 3;';
							$res=$sql->query($q);
							foreach($res as $_doc)
								if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
									//договор - основание
									$b=$_doc;
								else
									//сам документ
									$d=$_doc;
							
							$params=json_decode($d['m_documents_params']);
							
							$filesize=documents::tn(
								array(
									'org'=>$params->org,
									'client'=>$params->client,
									'smeta'=>$params->smeta,
									'doc_base'=>$params->doc_base,
									'date_ship'=>$params->date_ship,
									'doc_base_text'=>$params->doc_base_text,
									'doc_date'=>$params->doc_date,
									'doc_numb'=>$params->doc_numb,
									'doc_sum'=>$params->doc_sum,
									'doc_bar'=>$params->doc_bar,
									'doc_signature'=>$params->doc_signature,
									'doc_logo'=>$params->doc_logo,
									'doc_header_org_name'=>$params->doc_header_org_name,
									'doc_header_org_address'=>$params->doc_header_org_address,
									'doc_header_org_tel'=>$params->doc_header_org_tel,
									'doc_header_org_email'=>$params->doc_header_org_email,
									'items'=>$params->items
								),
								$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
							);
							$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$data['m_documents_id'].' LIMIT 1;';
							$sql->query($q);

							header('Location: '.url().'?success');
						}
						else{
							header('Location: '.url().'?success');
						}
					}
					else{
						header('Location: '.url().'?error');
					}
				}
				else{
					elogs();
					header('Location: '.url().'?error');
				}
				break;
				
			//АКТ
			case '8522102445':
				global $sql,$e,$orders,$buh;
				$data['m_documents_id']=array(1,null,null,10,1);
				$data['m_documents_performer']=array(1,null,null,10,1);
				$data['m_documents_customer']=array(1,null,null,10,1);
				$data['m_documents_agent']=array(null,null,10,null,1);
				$data['smeta']=array(1,null,null,10,1);
				$data['m_documents_templates_id']=array(1,null,null,10,1);
				$data['m_documents_numb']=array(null,null,50);
				$data['m_documents_date']=array(null,null,19);
				$data['m_documents_date_ship']=array(null,null,19);
				$data['m_documents_signature']=array(null,null,3);
				$data['m_documents_bar']=array(null,null,3);
				$data['m_documents_comment']=array(null,null,400);
				$data['m_documents_base']=array(null,null,180);
				$data['m_documents_pays']=array(null,null,500);
				$data['m_documents_nds_itog']=array(null,null,3);
				$data['m_documents_pdf_none']=array(null,null,3);
				
				$data['m_orders_act_doc_id[]']=array();
				$data['m_orders_act_services_id[]']=array();
				$data['m_orders_act_services_room_id[]']=array();
				$data['m_orders_act_services_count[]']=array();
				$data['m_orders_act_services_price[]']=array();
				$data['m_orders_act_services_sum[]']=array();
				$data['m_orders_act_services_table[]']=array();
				$data['m_orders_act_sum']=array(null,null,null,null,1);
				$data['m_documents_scan[]']=array(null,null,400);
				
				array_walk($data,'check');
				
				if(!$e){
					$document=$documents->getInfo($data['m_documents_id']);
					
					$data['smeta']=$documents->getInfo($data['smeta']);
					$data['m_documents_order']=$data['smeta']['m_documents_order'];
					
					$data['m_documents_numb']=$data['m_documents_numb']?$data['m_documents_numb']:$id;
					$data['m_documents_date']=$data['m_documents_date']?$data['m_documents_date']:dt();
					$data['m_documents_date_ship']=$data['m_documents_date_ship']?$data['m_documents_date_ship']:dt();
					$data['m_documents_signature']=$data['m_documents_signature']?1:0;
					$data['m_documents_bar']=$data['m_documents_bar']?1:0;
					$data['m_documents_nds_itog']=$data['m_documents_nds_itog']?1:0;
					$data['m_documents_pdf_none']=$data['m_documents_pdf_none']?1:0;
					
					$_address=$info->getAddress($data['m_documents_performer']);
					$_address=change_key($_address,'m_address_type',true);
					$doc_header_org_address=(isset($_address['2'])&&$_address['2'])?$_address['2']['m_address_full']:((isset($_address['1'])&&$_address['1'])?$_address['1']['m_address_full']:'');
					
					//папка
					$foldername=$document['m_documents_folder'];
					if(!file_exists(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername))
						mkdir(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername);
					codes::getBAR(__DIR__.'/../../www/files/'.$documents->documents_templates[$document['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/bar.png',$data['m_documents_id'].'00',false,20);
					
					//основание УПД, ищем в заказе договор или счет
					foreach($documents->getInfo() as $_d)
						//если есть договор поставки в том же заказе, что и смета, делаем его основанием
						if($_d[0]['m_documents_order']==$data['m_documents_order']&&$_d[0]['m_documents_templates_id']==4234525325)
							$data['doc_base']=$_d[0]['m_documents_id'];
						//если договора нет - основанием делаем счет
						else
							$data['doc_base']=$data['smeta']['m_documents_id'];
			
					$sum=0;
					$nds18=0;
					
					$items=array();	
					//если сумма работ по акту не равна 0
					if($data['m_orders_act_sum'])
						//работы
						foreach($data['m_orders_act_services_id[]'] as $k=>$v){
							$items[$k]['room_id']=$data['m_orders_act_services_room_id[]'][$k];
							$items[$k]['id']=$data['m_orders_act_services_id[]'][$k];
							$items[$k]['count']=$data['m_orders_act_services_count[]'][$k];
							$items[$k]['price']=$data['m_orders_act_services_price[]'][$k];
							$items[$k]['sum']=$data['m_orders_act_services_sum[]'][$k];
							$items[$k]['table']=$data['m_orders_act_services_table[]'][$k];
							$sum+=$items[$k]['sum'];
							$nds18+=$items[$k]['sum']*.2/1.2;
						}
					
					//если НДС общим итогом
					if($data['m_documents_nds_itog']){
						$nds18=$sum*.2;
						$sum+=$nds18;
					}

					$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
					
					$data['params']='{
							"org":"'.$data['m_documents_performer'].'",
							"client":"'.$data['m_documents_customer'].'",
							"agent":"'.$data['m_documents_agent'].'",
							"smeta":"'.$data['smeta']['m_documents_id'].'",
							"doc_template":"'.$data['m_documents_templates_id'].'",
							"doc_date":"'.$data['m_documents_date'].'",
							"date_ship":"'.$data['m_documents_date_ship'].'",
							"doc_numb":"'.$data['m_documents_numb'].'",
							"doc_bar":"'.$data['m_documents_bar'].'",
							"doc_base":"'.$data['doc_base'].'",
							"doc_base_text":"'.$data['m_documents_base'].'",
							"doc_sum":"'.number_format($data['m_orders_act_sum'],2,'.','').'",
							"doc_nds18":"'.($orders->orders_id[$data['m_documents_order']][0]['m_orders_nds']==18?$nds18:0).'",
							"doc_signature":"'.$data['m_documents_signature'].'",
							"doc_logo":"logo_docs.png",
							"doc_header_org_name":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_c_name_short'].'",
							"doc_header_org_address":"'.$doc_header_org_address.'",
							"doc_header_org_tel":"'.$info->getTel($data['m_documents_performer'])[0]['m_contragents_tel_numb'].'",
							"doc_header_org_email":"'.$contragents->getInfo($data['m_documents_performer'])['m_contragents_email'].'",
							"items":'.$items.'
						}';
						
					//находим платежи по счету
					if(!$data['m_documents_pays']){
						$data['pays']=$buh->getInfoFromInvoice($data['smeta']['m_documents_id']);
						$data['pays']=implode('|',$data['pays']);
					}
					else{
						$data['pays']=$data['m_documents_pays'];
					}
					
					//файлы
					if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id']))
						mkdir(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id']);
					$files=array();
					if($data['m_documents_scan[]'])
						foreach($data['m_documents_scan[]'] as $k=>$v){
							$files[$v]['file']=$v;
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v))
								$f_size=filesize(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v);
							else
								$f_size=filesize(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v);
							$f_size=($f_size)?$f_size:0;
							if ($f_size>1048576)
								$f_size=round($f_size/1048576,1).' МБ';
							else
								$f_size=round($f_size/1024,1).' КБ';
							$files[$v]['size']=$f_size;
							//копируем только добавленные файлы
							if(!file_exists(__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v)){
								copy(__DIR__.'/../../www/temp/uploads/'.$user->getInfo().'/'.$v,__DIR__.'/../../www/files/scan_docs/'.$document['m_documents_id'].'/'.$v);
							}
						}
					//print_r($files);exit;
					$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

				 	$q='UPDATE `formetoo_cdb`.`m_documents` SET 
						`m_documents_performer`='.$data['m_documents_performer'].',
						`m_documents_customer`='.$data['m_documents_customer'].',
						`m_documents_order`='.$data['m_documents_order'].',
						`m_documents_templates_id`='.$data['m_documents_templates_id'].',
						`m_documents_numb`=\''.$data['m_documents_numb'].'\',
						`m_documents_date`=\''.$data['m_documents_date'].'\',
						`m_documents_signature`='.$data['m_documents_signature'].',
						`m_documents_bar`='.$data['m_documents_bar'].',
						`m_documents_pdf_none`='.$data['m_documents_pdf_none'].',
						`m_documents_nds_itog`='.$data['m_documents_nds_itog'].',
						`m_documents_params`=\''.$data['params'].'\',
						`m_documents_update`=\''.dt().'\',
						`m_documents_comment`=\''.$data['m_documents_comment'].'\',
						`m_documents_filesize`=0,
						`m_documents_pays`=\''.$data['pays'].'\',
						`m_documents_scan`=\''.$files.'\',
						`m_documents_folder`=\''.$foldername.'\'
					WHERE `m_documents_id`='.$data['m_documents_id'].';';
					if($sql->query($q)){
						if(!$data['m_documents_pdf_none']){
					
							$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$data['m_documents_id'].' OR (`m_documents_order`='.$data['m_documents_order'].' AND `m_documents_templates_id` IN(4022369852,4022369853)) LIMIT 3;';
							$res=$sql->query($q);
							foreach($res as $_doc)
								if($_doc['m_documents_templates_id']==4022369852||$_doc['m_documents_templates_id']==4022369853)
									//договор - основание
									$b=$_doc;
								else
									//сам документ
									$d=$_doc;
							
							$params=json_decode($d['m_documents_params']);
							
							$filesize=documents::act_main(
								array(
									'org'=>$params->org,
									'client'=>$params->client,
									'smeta'=>$params->smeta,
									'doc_base'=>$params->doc_base,
									'date_ship'=>$params->date_ship,
									'doc_base_text'=>$params->doc_base_text,
									'doc_date'=>$params->doc_date,
									'doc_numb'=>$params->doc_numb,
									'doc_sum'=>$params->doc_sum,
									'doc_bar'=>$params->doc_bar,
									'doc_signature'=>$params->doc_signature,
									'doc_logo'=>$params->doc_logo,
									'doc_header_org_name'=>$params->doc_header_org_name,
									'doc_header_org_address'=>$params->doc_header_org_address,
									'doc_header_org_tel'=>$params->doc_header_org_tel,
									'doc_header_org_email'=>$params->doc_header_org_email,
									'items'=>$params->items
								),
								$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_folder'].'/'.$foldername.'/'.$documents->documents_templates[$data['m_documents_templates_id']][0]['m_documents_templates_filename']
							);
							$q='UPDATE `formetoo_cdb`.`m_documents` SET `m_documents_filesize`=\''.$filesize.'\' WHERE `m_documents_id`='.$data['m_documents_id'].' LIMIT 1;';
							$sql->query($q);

							header('Location: '.url().'?success&action=details&m_documents_id='.$data['m_documents_id'].'&m_documents_templates_id='.$data['m_documents_templates_id'].'&m_documents_order='.$data['m_documents_order']);
						}
						else{
							header('Location: '.url().'?success&action=details&m_documents_id='.$data['m_documents_id'].'&m_documents_templates_id='.$data['m_documents_templates_id'].'&m_documents_order='.$data['m_documents_order']);
						}
					}
					else{
						header('Location: '.url().'?error&action=details&m_documents_id='.$data['m_documents_id'].'&m_documents_templates_id='.$data['m_documents_templates_id'].'&m_documents_order='.$data['m_documents_order']);
					}
				}
				else{
					elogs();
					header('Location: '.url().'?error&action=details&m_documents_id='.$data['m_documents_id'].'&m_documents_templates_id='.$data['m_documents_templates_id'].'&m_documents_order='.$data['m_documents_order']);
				}
				break;	
		}
		exit;
	}	
	
	//прайс на услуги
	public static function price_services($p=array(
		'doc_template'=>'',
		'org'=>'',
		'doc_date'=>'',
		'doc_itemslist'=>'',
		'doc_message_info'=>'',
		'doc_logo'=>null,
		'doc_header_org_name'=>null,
		'doc_header_org_address'=>null,
		'doc_header_org_tel'=>null,
		'doc_header_org_email'=>null,
		'items'=>false
	),$filename){
		global $info,$contragents;
		
		$org=$contragents->getInfo($p['org']);		
		$org['tel']=$info->getTel($p['org'])[0]['m_contragents_tel_numb'];

		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('','',13,'dejavuserifcondensed',15,15,42,25,5,10);
		$mpdf->SetAuthor(transform::typography($company[0]['name']));
		$mpdf->SetCreator('vseumelec.ru');
		$mpdf->SetSubject(strcode2utf('Прайс-лист'));
		$mpdf->SetTitle(strcode2utf('Прайс-лист от '.transform::date_f(dtu($p['doc_date']))));
		$mpdf->SetKeywords(strcode2utf('Прайс-лист от '.transform::date_f(dtu($p['doc_date']))));
		
		$body='';
		
		//количество не пустых товаров
		$count_all=1;
		
		//логотип, контакты, футер
		$header='
			<div style="width:100%;padding-bottom:5;border-bottom:1px solid #bbb;">
				<div style="float:left;font-size:12;color:#333;margin-right:30px;width:30%;">';
		$header.=($p['doc_logo'])?'<img src="/img/'.$p['doc_logo'].'" width="120"/>':'';
		$header.='
				</div>
				<div style="width:45%;float:right;font-size:13;">
					<p style="margin:0 0 3px 0;padding:0;font-size:14;font-weight:bold;">'.transform::typography($p['doc_header_org_name']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_address']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$p['doc_header_org_email'].'">'.transform::typography($p['doc_header_org_email']).'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		$mpdf->SetHTMLHeader($header);
		$footer='<div style="float:left;width:29%;">
					<div style="margin-top:2.5mm;font-size:10px;color:#656565;">
						Прайс-лист от '.transform::date_f(dtu($p['doc_date'])).'
					</div>
					<div style="'.($p['doc_header_bar']?'':'margin-top:2.4mm;').'font-size:12;color:#333333;">
						{PAGENO}
					</div>
				</div>
				<div style="float:right;width:70%;text-align:right;font-size:10px;color:#656565;">
					<br/><br/>С актуальными ценами всегда можно ознакомиться по адресу: <a href="http://vseumelec.ru/price/">http://vseumelec.ru/price/</a>
				</div>';
		$mpdf->SetHTMLFooter($footer);
		
		//комментарий в начале документа
		$body.='<div style="font-size:13;color:#666;font-weight:700;text-align:center;margin-bottom:5;">'.$p['doc_message_info'].'</div>';
		
		//заголовок
		$body.='<h1 style="font-size:20;font-weight:400;text-align:center;padding-bottom:10px;">Прайс-лист от '.transform::date_f(dtu($p['doc_date'])).'</h1>';
		
		//товарные позиции
		if($p['items']){
			$prev=0;
			foreach($p['items'] as $_item){
				if($_item->items){
					//заголовки категорий
					$body.='<h2 style="font-size:16;font-weight:400;margin-bottom:5;">';
					$i=0;
					
					if($_item->parents){
						$p_name=array();
						
						foreach($_item->parents as $_parent){
							if($_parent->m_services_categories_id!=$prev&&$p['doc_itemslist'])
								$body.='<bookmark content="'.$_parent->m_services_categories_name.'" level="'.$i.'"></bookmark>';
							$prev=$_parent->m_services_categories_id;
							$p_name[]=$_parent->m_services_categories_name;
							$i++;
						}
						$body.=implode('&nbsp;/&nbsp;',$p_name).'&nbsp;/&nbsp;';
					}
					$body.=$_item->info->m_services_categories_name;
					$body.=$p['doc_itemslist']?('<bookmark content="'.$_item->info->m_services_categories_name.'" level="'.$i.'"></bookmark>'):'';
					$body.='</h2>';

					$body.='<table style="font-size:13;border-spacing:0;border-top:1px solid #bbb;margin-bottom:6mm;border-right:1px solid #bbb;" width="180mm">
								<thead>
									<tr>
										<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="28mm">Артикул</td>
										<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="110mm">Наименование</td>
										<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="17mm">Ед. изм.</td>
										<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;text-align:center;" width="25mm;">Цена</td>
									</tr>
								</thead>';
					//товарные позиции
					foreach($_item->items as $__item){
						if($__item->m_services_show_price){
							$body.='<tr>
										<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;color:#666;">'.$__item->m_services_id.'</td>
										<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.$__item->m_services_name.'</td>
										<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.$info->getUnits($__item->m_services_unit).'</td>
										<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:right;">'.transform::price_o($__item->m_services_price_general).'</td>
									</tr>';
						}
					}
					$body.='</table>';
				}
			}
		}
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);

		$mpdf->WriteHTML($body);
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}
	}
	
	//договор поставки разовый
	public static function contract_goods($p=array(
		'org'=>'',
		'client'=>'',
		'order'=>'',
		'doc_date'=>'',
		'doc_numb'=>'',
		'doc_logo'=>null,
		'doc_bar'=>null,
		'doc_signature'=>null,
		'doc_header_org_name'=>null,
		'doc_header_org_address'=>null,
		'doc_header_org_tel'=>null,
		'doc_header_org_email'=>null,		
		'm_documents_doc_method_pay_cash'=>null,
		'm_documents_doc_method_pay_bank'=>null,
		'm_documents_doc_sum_pre'=>null,
		'invoice'=>null,
		'm_documents_doc_delivery_time'=>null,
		'm_documents_doc_delivery_self'=>null
	),$filename){
		global $info,$documents,$contragents,$orders,$services,$products;
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		
		$client['m_contragents_c_director_name']=$client['m_contragents_c_director_name']?$client['m_contragents_c_director_name']:$client['m_contragents_p_fio'];
		
		$client_address=$info->getAddress($p['client']);
		$client_address=change_key($client_address,'m_address_type',true);
		
		if($client['m_contragents_consignee']){
			$client_consignee_address=$info->getAddress($client['m_contragents_consignee']);
			$client_consignee_address=change_key($client_consignee_address,'m_address_type',true);
		}
		
		$org_address=$info->getAddress($p['org']);
		$org_address=change_key($org_address,'m_address_type',true);
		
		if($org['m_contragents_consignee']){
			$org_consignee_address=$info->getAddress($org['m_contragents_consignee']);
			$org_consignee_address=change_key($org_consignee_address,'m_address_type',true);
		}
		
		$p['nds']=$orders->orders_id[$p['order']][0]['m_orders_nds'];
		
		$org['tel']=$info->getTel($p['org'])[0]['m_contragents_tel_numb'];
		$client['tel']=$info->getTel($p['client'])[0]['m_contragents_tel_numb'];
		
		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('','',9,'dejavuserifcondensed',15,15,20,25,10,10);//$mpdf=new mPDF('','',10,'dejavuserifcondensed',15,15,40,25,5,10);
		$mpdf->SetAuthor(transform::typography(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2))));
		$mpdf->SetCreator(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2)));
		$mpdf->SetSubject(strcode2utf('Договор поставки'));
		$mpdf->SetTitle(strcode2utf('Договор поставки № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));
		$mpdf->SetKeywords(strcode2utf('Договор поставки № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));		
		$mpdf->defaultCSS['P']['MARGIN']='0.9em 0';
		$mpdf->shrink_tables_to_fit=2;
		//$mpdf->curlAllowUnsafeSslRequests = true;

		$body='';
		//количество не пустых товаров
		$count_all=1;
		
		//номер договора в хедере
		$header_numb='
			<div style="margin-top:2.5mm;font-size:10px;color:#656565;float:left;width:60%;">
				Договор поставки № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'<br/>
				Лист {PAGENO}
			</div>
		';
		if($p['doc_bar'])
			$header_numb.='
			<div style="margin-top:2.5mm;float:right;width:35%;text-align:right;">
				<img src="/files/'.substr($filename,0,-13).'bar.png" style="height:24px"/>
			</div>';
		$mpdf->SetHTMLHeader($header_numb);
		
		//логотип, контакты, футер
		$header='
			<div style="width:100%;padding-bottom:5mm;border-bottom:1px solid #bbb;">
				<div style="float:left;color:#333;margin-right:30px;width:30%;">';
		$header.=($p['doc_logo'])?'<img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=logo" width="200"/>':'';
		$header.='
				</div>
				<div style="width:45%;float:right;font-size:13;">
					<p style="margin:0 0 3px 0;padding:0;font-size:14;font-weight:bold;">'.transform::typography($p['doc_header_org_name']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_address']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$p['doc_header_org_email'].'">'.transform::typography($p['doc_header_org_email']).'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		
		
		
		$body.=$header;//$mpdf->SetHTMLHeader($header);
	
		$footer='<div style="float:left;width:10%;">
					&nbsp;
				</div>
				<div style="width:90%;text-align:right">
					<table width="160mm" style="margin-top:2.5mm;">
						<thead>
							<tr>
								<td width="35mm"></td>
								<td width="2.5mm"></td>
								<td width="30mm"></td>
								<td width="20mm"></td>
								<td width="35mm"></td>
								<td width="2.5mm"></td>
								<td width="30mm"></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="border-bottom:1px solid #bbb;"></td>
								<td></td>
								<td>/ '.transform::fio($client['m_contragents_c_director_name']).' /</td>
								<td></td>
								<td style="border-bottom:1px solid #bbb;"></td>
								<td></td>
								<td>/ '.transform::fio($org['m_contragents_c_director_name']).' /</td>
							</tr>
							<tr>
								<td style="text-align:center;font-size:10;color:#656565;">Заказчик</td>
								<td></td>
								<td></td>
								<td></td>
								<td style="text-align:center;font-size:10;color:#656565;">Поставщик</td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>';
		if($p['doc_signature']){
			$footer.='<div>';
			$footer.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;margin-left:20mm;margin-top:-16mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
			$footer.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;margin-left:105mm;margin-top:-16mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			$footer.='</div>';
		}
		$mpdf->SetHTMLFooter($footer);
				
		//заголовок
		$body.='
			<h1 style="font-size:20;font-weight:400;text-align:center;padding-bottom:10px;padding-top:5mm;">Договор поставки № '.$p['doc_numb'].'</h1>
			<div>
				<table width="180mm">
					<tr>
						<td width="30%">г. Иваново</td>
						<td width="40%"></td>
						<td width="30%" style="text-align:right;">'.transform::date_f(dtu($p['doc_date'])).'</td>
					</tr>
				</table>
			</div>
		';
		
		//тело документа
		//физлицо
		if(in_array(3,explode('|',$client['m_contragents_type'])))
			$body.='
				<bookmark content="Преамбула" level="0"></bookmark>
				<p>Гражданин РФ <strong>'.$client['m_contragents_p_fio'].'</strong>'.($client['m_contragents_p_passport_sn']?' (паспорт '.$client['m_contragents_p_passport_sn'].($client['m_contragents_p_passport_v']?' выдан '.$client['m_contragents_p_passport_v']:'').($client['m_contragents_p_passport_date']?' '.dtu($client['m_contragents_p_passport_date'],'d.m.Y'):'').($client['m_contragents_p_birthday']?', дата рождения '.dtu($client['m_contragents_p_birthday'],'d.m.Y'):'').($client['m_contragents_address_j']?', зарегистрирован(а) по адресу '.$client['m_contragents_address_j']:'').')':'').', именуемый(ая) в дальнейшем &laquo;Заказчик&raquo;, с одной стороны,<br/>и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Поставщик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, заключили настоящий договор постаки (далее по тексту &ndash; &laquo;Договор&raquo;) о нижеследующем:</p>
			';		
		else{
			//ИП
			if(strlen($client['m_contragents_c_inn'])==12){
				$body.='
					<bookmark content="Преамбула" level="0"></bookmark>
					<p><strong>'.$client['m_contragents_c_name_full'].'</strong>, именуемый(ая) в дальнейшем &laquo;Заказчик&raquo;, с одной стороны,<br/>и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Поставщик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, заключили настоящий договор поставки (далее по тексту &ndash; &laquo;Договор&raquo;) о нижеследующем:</p>
				';
			}
			//ФИРМА
			else{
				$body.='
					<bookmark content="Преамбула" level="0"></bookmark>
					<p><strong>'.$client['m_contragents_c_name_full'].'</strong>, именуемое в дальнейшем &laquo;Заказчик&raquo;, в лице '.$client['m_contragents_c_director_post'].' '.$client['m_contragents_c_director_name_rp'].', действующего на основании '.$client['m_contragents_c_director_base'].', с одной стороны,<br/>и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Поставщик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, заключили настоящий договор поставки (далее по тексту &ndash; &laquo;Договор&raquo;) о нижеследующем:</p>
				';
			}
		}
		$body.='
			<bookmark content="1. Предмет договора" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">1. Предмет договора</h2>
			<p>1.1. В соответствии с настоящим договором Поставщик обязуется поставить Заказчику товар (далее по тексту &ndash; &laquo;Товар&raquo;) в количестве и по наименованиям, указанным в Спецификации (Приложение № 1 к Договору, являющеейся неотъемлемой частью Договора), а Заказчик – принять и оплатить товар согласно условиям настоящего договора.</p>
			<p>1.2. Поставщик гарантирует, что Товар принадлежит ему на праве собственности, не заложен, не находится под арестом, не является предметом исков третьих лиц.</p>
			<bookmark content="2. Сумма Договора и порядок расчетов" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">2. Сумма Договора и порядок расчетов</h2>
			<p>2.1. Стоимость Договора определяется Спецификацией.</p>
			<p>2.2. Цена Товара включает в себя себестоимость Товара, стоимость погрузки Товара на складе Поставщика, НДС ('.$orders->orders_id[$p['order']][0]['m_orders_nds'].'%)'.($p['m_documents_doc_delivery_self']?'. Доставка Товара осуществляется силами Заказчика (самовывоз).':', транспортные расходы по доставке Товара до Заказчика.').'</p>
			<p>2.3. '.(!$p['m_documents_doc_sum_pre']?'Оплата по настоящему Договору производится на условии 100% предоплаты, путем перечисления денежных средств на расчетный счет Поставщика в течение трёх рабочих дней с даты получения счета Заказчиком.':'');
		if($p['m_documents_doc_sum_pre'])
			$body.=' В течение 3 (трёх) рабочих дней с момента заключения настоящего Договора Заказчик вносит предоплату Поставщику в размере '.transform::price_o($p['m_documents_doc_sum_pre']).' ('.transform::summ_text($p['m_documents_doc_sum_pre'],true,false).')';
		if($p['m_documents_doc_sum_pre']&&$p['nds']!=-1)
			$body.=', в том числе НДС '.$orders->orders_id[$p['order']][0]['m_orders_nds'].'% — '.transform::price_o(($p['m_documents_doc_sum_pre']*($orders->orders_id[$p['order']][0]['m_orders_nds']/100)/(1+$orders->orders_id[$p['order']][0]['m_orders_nds']/100))).' ('.transform::summ_text($p['m_documents_doc_sum_pre']*.20/1.20,true,false).')';
		if($p['m_documents_doc_sum_pre'])
			$body.='.';
		//кол-во дней после сдачи для окончательной оплаты
		if($p['m_documents_doc_sum_pre'])
			$body.=' Оплата за поставленный Товар (окончательный расчёт) производится Заказчиком в течение 7 ('.transform::summ_text(3,false,false,true).') календарных дней после приёмки Товара'.($p['m_documents_doc_sum_pre']?', за вычетом предоплаты':'').'.';
		$body.='</p><p>2.4. Цена Товара на период действия Договора является фиксированной и пересмотру не подлежит.</p>
			<p>2.5. Товар поставляется после поступления предоплаты в течение срока, указанного в Спецификации.</p>
			<bookmark content="3. Права и обязанности Сторон" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">3. Права и обязанности Сторон</h2>
			<bookmark content="3.1. Поставщик обязан:" level="1"></bookmark>
			<p>3.1. Поставщик обязан:</p>
			<p style="padding-left: 30px;">3.1.1. Передать Заказчику Товар наджлежащего качества в количестве и в сроки, указанные в Спецификации.</p>
			<p style="padding-left: 30px;">3.1.2. Вместе с Товаром или отдельно почтой передать сопроводительные документы: Универсальный передаточный документ (далее — УПД).</p>
			<bookmark content="3.2. Поставщик вправе:" level="1"></bookmark>
			<p>3.2. Поставщик вправе:</p>
			<p style="padding-left: 30px;">3.2.1. Не передавать Товар в случае отсутствия у представителя Заказчика надлежаще оформленной доверенности.</p>
			<p style="padding-left: 30px;">3.2.2. В случае нарушения Заказчиком своих обязательств по Договору, а также необоснованного уклонения Заказчика от приемки Товара приостановить исполнение своих обязательств на соответствующий срок.</p>
			<p style="padding-left: 30px;">3.2.3. В случае нарушения Заказчиком сроков оплаты и/или сроков приемки Товара расторгнуть Договор в одностороннем порядке с возложением убытков, связанных с расторжением, на Заказчика.</p>
			<p style="padding-left: 30px;">3.2.4. В случае оплаты Заказчиком счёта по прошествии более 3 (трёх) рабочих дней с даты выставления счёта Поставщик вправе изменить сроки поставки Товара. Новый срок поставки Товара обговаривается Сторонами.</p>
			<p>3.3. Заказчик обязан:</p>
			<p style="padding-left: 30px;">3.3.1. Оплатить Товар в порядке и на условиях настоящего Договора.</p>
			<p style="padding-left: 30px;">3.3.2. Осуществить принятие Товара в соответсвии с условиями Договора, предоставить Поставщику правильно оформленную доверенность на получение Товара или скрепить подписью и печатью организации Заказчика экземпляр УПД Поставщика</p>
			<p style="padding-left: 30px;">3.3.3. В случае доставки автотранспортом Поставщика организовать разгрузку и приемку Товара, в т.ч. осмотреть Товар, проверить его количество и качество в течение 1.5 (полутора) часов с момента прибытия автотранспорта.</p>
			<p style="padding-left: 30px;">3.3.4. Незамедлительно уведомить Поставщика в случае, если поставленный Товар не соответствует условиям Договора о количестве, ассортименте или качестве.</p>
			<p>3.4. Заказчик вправе:</p>
			<p style="padding-left: 30px;">3.4.1. Требовать у Поставщика  все относящиеся к Товару документы (копии сертификатов соответсвия).</p>
			<p style="padding-left: 30px;">3.4.2. В случае нарушения Поставщиком сроков передачи Товара на срок более 15 (птянадцати) рабочих дней расторгнуть Договор с требованием возвращения предоплаты в течение 10 (десяти) календарных дней.</p>
			<bookmark content="4. Условия поставки" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">4. Условия поставки</h2>
			<p>4.1. Упаковка Товара должна обеспечивать его сохранность при транспортировке. В случае, если по своему характеру Товар не требует затаривания или упаковки, Поставщик отгружает его без затаривания (упаковки).</p>
			<p>4.2. Грузополучателем Товара является '.($client['m_contragents_consignee']?$contragents->getName($client['m_contragents_consignee']).'(ИНН '.$contragents->getInfo($client['m_contragents_consignee'])['m_contragents_c_inn'].', КПП '.$contragents->getInfo($client['m_contragents_consignee'])['m_contragents_c_kpp'].') ,'.$client_consignee_address[1]['m_address_full']:$client['m_contragents_c_name_short']).'.</p>
			<p>4.3. Приемка Товара по количеству и комплектности осуществляется в момент передачи Товара Заказчику и производится в соответствии с данными, указанными в товаросопроводительных документах на товар. Приемка Товара по качеству осуществляется в момент передачи товара Заказчику. Товар считается принятым Заказчиком по количеству, комплектности и по качеству в части недостатков Товара, которые могут быть очевидны в момент передачи, с момента составления и подписания УПД.</p>
			<p>4.4. Переход права собственности к Заказчику происходит с момента оплаты 100% стоимости Товара и с момента подписания Заказчиком или его доверенным лицом УПД. Риск случайной гибели и порчи Товара переходит к Заказчику в момент приемки Товара и подписания Заказчиком УПД.</p>
			<bookmark content="5. Ответственность Сторон" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">5. Ответственность Сторон</h2>
			<p>5.1. Сторона, не исполнившая или ненадлежащим образом исполнившая обязательства по настоящему договору, обязана возместить другой стороне причиненные таким неисполнением убытки.</p>
			<p>5.2. В случаях, не предусмотренных настоящим договором, имущественная ответственность определяется в соответствии с действующим законодательством РФ.</p>
			<p>5.3. Стороны освобождаются от ответственности по исполнению настоящего договора, если данные обстоятельства вызваны чрезвычайными происшествиями, стихийными бедствиями, действиями непреодолимой силы (форс-мажор).</p>
			<p>5.4. Факт возникновения обстоятельств, указанных в п. 5.3 настоящего Договора должен быть документально подтвержден компетентными государственными органами, расположенными по месту нахождения Сторон настоящего договора.</p>
			<p>5.5. В случае нарушения Заказчиком сроков оплаты Товара, установленных договором, Поставщик имеет право по письменному заявлению требовать от Заказчика уплаты штрафной неустойки (пени) в размере 0,1% от стоимости неоплаченной партии Товара, за каждый день просрочки, но не более 10% от стоимости неоплаченной партии Товара. Уплата пени не освобождает сторону от исполнения основных обязательств.</p>
			<p>5.6. В случае частичной или полной не поставки Товара Поставщиком, в установленный договором срок, Поставщик по письменному требованию Заказчика уплачивает пени в размере 0,1% от стоимости недопоставленной партии Товара, за каждый день просрочки. Уплата пени не освобождает сторону от исполнения основных обязательств.</p>
			<bookmark content="6. Порядок разрешения споров" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">6. Порядок разрешения споров</h2>
			<p>6.1. Все споры и разногласия, которые могут возникнуть между сторонами по вопросам, не нашедшим своего разрешения в тексте данного договора, будут разрешаться путем переговоров на основе действующего законодательства.</p>
			<p>6.2. Претензионный порядок досудебного урегулирования споров из Договора является для Сторон обязательным. Срок рассмотрения претензий - 15 (пятнадцать) календарных дней со дня получения претензии Стороной.</p>
			<p>6.3. В случае невозможности разрешения разногласий путем переговоров они подлежат рассмотрению в арбитражном суде Ивановской области согласно порядку, установленному законодательством Российской Федерации.</p>
			<bookmark content="7. Порядок разрешения споров" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">7. Порядок изменения и расторжения Договора</h2>
			<p>7.1. Настоящий договор вступает в силу с момента подписания уполномоченными представителями сторон и действует до полного исполнения сторонами своих обязательств по договору.</p>
			<p>7.2. Любые изменения и дополнения к настоящему Договору имеют силу только в том случае, если они оформлены в письменном виде и подписаны обеими Сторонами.</p>
			<p>7.3. Досрочное расторжение Договора может иметь место в соответствии с п. 5.3 настоящего Договора либо по соглашению Сторон, либо на основаниях, предусмотренных законодательством Российской Федерации.</p>
			<bookmark content="8. Порядок разрешения споров" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">8. Порядок изменения и расторжения Договора</h2>
			<p>8.1. Любые изменения и дополнения к настоящему договору действительны при условии, если они совершены в письменной форме и подписаны сторонами или надлежаще уполномоченными на то представителями сторон.</p>
			<p>8.2. Все уведомления и сообщения должны направляться в письменной форме.</p>
			<p>8.3. Стороны не имеют никаких сопутствующих устных договоренностей. Содержание текста Договора полностью соответствует действительному волеизъявлению Сторон.</p>
			<p>8.4. Документы, переданные факсимильной связью, имеют юридическую силу, что не освобождает стороны от последующего обязательного предоставления оригиналов документов в течение 30 (тридцати) календарных дней.</p>
			<p>8.5. Во всем остальном, что не предусмотрено настоящим договором, стороны руководствуются действующим законодательством РФ.</p>
			<p>8.6. Договор составлен в 2 (двух) подлинных экземплярах на русском языке по одному для каждой из Сторон.</p>
			<bookmark content="9. Список приложений к договору" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">9. Список приложений к договору</h2>
			<p>9.1. Приложение № 1 &mdash; Спецификация.</p>
			<bookmark content="10. Адреса, реквизиты  и подписи сторон" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">10. Адреса, реквизиты  и подписи сторон</h2>
			<table style="font-size:13;border-spacing:0;border-top:1px solid #bbb;margin-top:15mm;margin-bottom:6mm;border-right:1px solid #bbb;" width="180mm">
				<thead>
					<tr>
						<td style="font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;text-align:center;" width="30mm"></td>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;text-align:center;" width="80mm">Заказчик</td>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;text-align:center;" width="80mm">Поставщик</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Наименование</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_name_full'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_name_full'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Юридический адрес</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client_address[1]['m_address_full'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org_address[1]['m_address_full'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Почтовый адрес</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client_address[2]['m_address_full'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org_address[2]['m_address_full'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Фактический адрес</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client_address[3]['m_address_full'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org_address[3]['m_address_full'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Телефон</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['tel'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['tel'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">E-mail</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_email'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_email'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">ИНН</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_inn'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_inn'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">КПП</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_kpp'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_kpp'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">ОГРН</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_ogrn'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_ogrn'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Банк</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.transform::typography($info->getDefaultRS($client['m_contragents_id'])['m_contragents_rs_bank']).'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.transform::typography($info->getDefaultRS($org['m_contragents_id'])['m_contragents_rs_bank']).'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">БИК</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$info->getDefaultRS($client['m_contragents_id'])['m_contragents_rs_bik'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$info->getDefaultRS($org['m_contragents_id'])['m_contragents_rs_bik'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Расч. сч.</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$info->getDefaultRS($client['m_contragents_id'])['m_contragents_rs_rs'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$info->getDefaultRS($org['m_contragents_id'])['m_contragents_rs_rs'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Корр. сч.</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$info->getDefaultRS($client['m_contragents_id'])['m_contragents_rs_ks'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$info->getDefaultRS($org['m_contragents_id'])['m_contragents_rs_ks'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Должность подписанта</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_director_post'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_director_post'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Ф.И.О. подписанта</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_director_name'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_director_name'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Подпись</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;"></td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;"></td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Место печати</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;"></td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;"></td>
					</tr>
				</tbody>
			</table>
		';

		//печати и подписи
		if($p['doc_signature']){
			$body.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:50mm;margin-top:-23mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
			$body.=$client['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:45mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=stamp"/></div>':'';
			$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:125mm;margin-top:-23mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:140mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
		}
		
		//СПЕЦИФИКАЦИЯ
		$body.='
			<pagebreak resetpagenum="true"/>
			<bookmark content="Спецификация" level="0"></bookmark>
		';
		//номер договора в хедере
		$body.='
			<htmlpageheader name="spec">
				<div style="margin-top:2.5mm;font-size:10px;color:#656565;float:left;width:60%;">
					Приложение № 1 к договору поставки № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'<br/>
					Лист {PAGENO}
				</div>
		';
		if($p['doc_bar'])
			$body.='
				<div style="margin-top:2.5mm;float:right;width:35%;text-align:right;">
					<img src="/files/'.substr($filename,0,-13).'bar.png" style="height:24px"/>
				</div>';
		$body.='
			</htmlpageheader>
			<sethtmlpageheader name="spec" value="on" show-this-page="1" />
		';
		//заголовок
		$body.='
			<h1 style="font-size:20;font-weight:400;text-align:center;padding-bottom:10px;padding-top:5mm;">Спецификация № 1</h1>
			<div>
				<table width="180mm">
					<tr>
						<td width="30%">г. Иваново</td>
						<td width="40%"></td>
						<td width="30%" style="text-align:right;">'.transform::date_f(dtu($p['doc_date'])).'</td>
					</tr>
				</table>
			</div>
		';
		$body.='
			<p>1. Поставщик обязуется поставить Товар следующего сортамента:</p>
		';
		
		//загружаем товары из счета
		$p['items']=json_decode($documents->getInfo((string)$p['invoice'])['m_documents_params'])->items;
		$sum=0;
		$nds18=0;
		$items=0;
		$l=1;
		$rooms_count=count((array)$p['items']);
		foreach($p['items'] as $_room){
			//если есть позиции в разделе
			if($_room->services){
				if($rooms_count>1)
					$body.='
						<bookmark content="'.($_room->room->name?$_room->room->name:'Раздел '.$l).'" level="1"></bookmark>
						<h2 style="font-size:15;font-weight:400;margin-bottom:1mm;"><b>'.($_room->room->name?$_room->room->name:'Раздел'.$l).'</b></h2>
					';
				$body.='<table style="font-size:11;border-spacing:0;border-top:1px solid #bbb;margin-bottom:1mm;border-right:1px solid #bbb;" width="180mm">
							<thead>
								<tr>
									<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="10mm">№</td>
									<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="80mm">Наименование</td>
									<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="18mm">Ед. изм.</td>
									<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="17mm">Кол-во</td>
									<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="25mm">Цена</td>
									<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;text-align:center;" width="30mm;">Сумма</td>
								</tr>
							</thead>
							<tbody>';
				//работы
				$pre_sum=0;
				$pre_nds18=0;
				foreach($_room->services as $k=>$_service){
					$nds_item=isset($_service->nds)?$_service->nds:$orders->orders_id[$p['order']][0]['m_orders_nds'];
					$orders->orders_id[$p['order']][0]['m_orders_nds']=isset($_service->nds)?$_service->nds:$orders->orders_id[$p['order']][0]['m_orders_nds'];
					$pre_sum+=$_service->sum;
					$pre_nds18+=round($_service->sum*($nds_item/100)/(1+$nds_item/100),2);
					$body.='	<tr>
									<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.($k+1).'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$services->services_id[$_service->id][0]['m_services_name']:(isset($products->products_id[$_service->id])?$products->products_id[$_service->id][0]['m_products_name']:'')).'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$info->getUnits($services->services_id[$_service->id][0]['m_services_unit']):(isset($products->products_id[$_service->id])?$info->getUnits($products->products_id[$_service->id][0]['m_products_unit']):'')).'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.$_service->count/* str_replace('.',',',$_service->count) */.'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.transform::price_o($_service->price).'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:center;">'.transform::price_o($_service->sum).'</td>
								</tr>';
				}
				$sum+=$pre_sum;
				$nds18+=$pre_nds18;
				$items+=sizeof($_room->services);
				//промежуточные итоги - выводим, только если в счете больше одного раздела
				if($rooms_count>1){
					//сумма
					$body.='	<tr>
									<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Промежуточный итог:</td>
									<td style="padding:1mm;text-align:center;border-bottom:1px solid #bbb;">'.transform::price_o($pre_sum).'</td>
								</tr>';
					//НДС 18%
					$body.=($orders->orders_id[$p['order']][0]['m_orders_nds']!=-1)?'
								<tr>
									<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС ('.$orders->orders_id[$p['order']][0]['m_orders_nds'].'%):</td>
									<td style="vertical-align:top;padding:1mm;border-bottom:1px solid #bbb;text-align:center;">'.transform::price_o($pre_nds18).'</td>
								</tr>':'';
				}
				$body.='
							</tbody>
						</table>';
			}
		}
		
		//итоговые суммы
		$body.='
					<table style="font-size:11;border-spacing:0;margin:6mm 0 2mm;border-right:1px solid #bbb;" width="180mm">
						<thead>
							<tr>
								<td width="10mm"></td>
								<td width="80mm"></td>
								<td width="18mm"></td>
								<td width="17mm"></td>
								<td width="25mm"></td>
								<td width="30mm;"></td>
							</tr>
						</thead>
						<tbody>';
						
		$body.='			<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum).'</td>
							</tr>';
		//скидка
		if($orders->orders_id[$p['order']][0]['m_orders_discount']>0)
			$body.='		<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Скидка '.$orders->orders_id[$p['order']][0]['m_orders_discount'].'%:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		//итого со скидкой
		if($orders->orders_id[$p['order']][0]['m_orders_discount']>0)
			$body.='		<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого со скидкой:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		//итог НДС 18%
		$body.=($orders->orders_id[$p['order']][0]['m_orders_nds']!=-1)?'
							<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС ('.$orders->orders_id[$p['order']][0]['m_orders_nds'].'%):</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($nds18-$nds18*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>':'';
		//итог БЕЗ НДС
		$body.=($orders->orders_id[$p['order']][0]['m_orders_nds']==-1)?'
							<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Без налога (НДС)</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:right;text-align:center;">—</td>
							</tr>':'';
		//всего к оплате
		$body.='			<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;font-weight:bold">Всего к оплате:</td>
								<td style="vertical-align:top;padding:1mm;border-top:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;font-weight:bold">'.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		$body.='
						</tbody>
					</table>';
					
		$body.='
			<p>2. Сумма настоящей спецификации составляет '.transform::price_o($sum).' ('.transform::summ_text($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).($orders->orders_id[$p['order']][0]['m_orders_nds']!=-1?(', в том числе НДС '.$orders->orders_id[$p['order']][0]['m_orders_nds'].'% — '.transform::summ_text($nds18-$nds18*$orders->orders_id[$p['order']][0]['m_orders_discount']/100,true,false)):'').').</p>
			<p>3. Условия поставки: '.($p['m_documents_doc_delivery_self']?'Самовывоз со склдаа Поставщика.':'транспортом Поставщика по адресу: '.$client_address[4]['m_address_full']).'</p>
			<p>4. Cроки поставки: в течение '.$p['m_documents_doc_delivery_time'].' ('.transform::summ_text($p['m_documents_doc_delivery_time'],false,false).') рабочих дней с момента поступления предоплаты при условии оплаты выставленного счёта не позднее 3 (трёх) рабочих дней с даты его выставления. 
		';
		
		//таблица для подписей и печати
			$body.='<table style="font-size:13;border-spacing:0;margin:10mm 0 2mm;line-height:4.5mm" width="180mm">
						<tr>
							<td width="180mm">
								<table style="font-size:13;border-spacing:0;margin:0;line-height:4.5mm" width="180mm">
									<thead>
										<tr>
											<td width="38mm"></td>
											<td width="7mm"></td>
											<td width="38mm"></td>
											<td width="7mm"></td>
											<td width="38mm"></td>
											<td width="7mm"></td>
											<td width="38mm"></td>
										</tr>
										<tr>
											<td colspan="3" style="font-weight:bold;padding-bottom:5mm">Заказчик</td>
											<td width="7mm"></td>
											<td colspan="3" style="font-weight:bold;padding-bottom:5mm">Поставщик</td>
										</tr>
									</thead>
									<tr>
										<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$client['m_contragents_c_director_post'].'</td>
										<td></td>
										<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$org['m_contragents_c_director_post'].'</td>
									</tr>
									<tr>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
										<td></td>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
									</tr>
									<tr>
										<td></td>
										<td></td>
										<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($client['m_contragents_c_director_name']).'</td>
										<td></td>
										<td></td>
										<td></td>
										<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($org['m_contragents_c_director_name']).'</td>
									</tr>
									<tr>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
										<td></td>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
										<td></td>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
										<td></td>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
									</tr>
									<tr>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
										<td></td>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';	
			
			//печати и подписи
			if($p['doc_signature']){
				$body.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:20mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
				$body.=$client['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:35mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=stamp"/></div>':'';
				$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:115mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
				$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:130mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
			}
		
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);

		$mpdf->WriteHTML($body);
		$mpdf->SetHTMLFooter('');
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}
	}
	
	//договор поставки годовой
	public static function contract_goods_year($p=array(
		'org'=>'',
		'client'=>'',
		'order'=>'',
		'doc_date'=>'',
		'doc_numb'=>'',
		'doc_logo'=>null,
		'doc_bar'=>null,
		'doc_signature'=>null,
		'doc_header_org_name'=>null,
		'doc_header_org_address'=>null,
		'doc_header_org_tel'=>null,
		'doc_header_org_email'=>null,		
		'invoice'=>null
	),$filename){
		global $info,$documents,$contragents,$orders,$services,$products;
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		
		$client['m_contragents_c_director_name']=$client['m_contragents_c_director_name']?$client['m_contragents_c_director_name']:$client['m_contragents_p_fio'];
		
		$client_address=$info->getAddress($p['client']);
		$client_address=change_key($client_address,'m_address_type',true);
		
		if($client['m_contragents_consignee']){
			$client_consignee_address=$info->getAddress($client['m_contragents_consignee']);
			$client_consignee_address=change_key($client_consignee_address,'m_address_type',true);
		}
		
		$org_address=$info->getAddress($p['org']);
		$org_address=change_key($org_address,'m_address_type',true);
		
		if($org['m_contragents_consignee']){
			$org_consignee_address=$info->getAddress($org['m_contragents_consignee']);
			$org_consignee_address=change_key($org_consignee_address,'m_address_type',true);
		}
		
		$p['nds']=$orders->orders_id[$p['order']][0]['m_orders_nds'];
		
		$org['tel']=$info->getTel($p['org'])[0]['m_contragents_tel_numb'];
		$client['tel']=$info->getTel($p['client'])[0]['m_contragents_tel_numb'];
		
		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('','',9,'dejavuserifcondensed',15,15,20,25,10,10);//$mpdf=new mPDF('','',10,'dejavuserifcondensed',15,15,40,25,5,10);
		$mpdf->SetAuthor(transform::typography(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2))));
		$mpdf->SetCreator(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2)));
		$mpdf->SetSubject(strcode2utf('Договор поставки'));
		$mpdf->SetTitle(strcode2utf('Договор поставки № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));
		$mpdf->SetKeywords(strcode2utf('Договор поставки № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));		
		$mpdf->defaultCSS['P']['MARGIN']='0.9em 0';
		$mpdf->shrink_tables_to_fit=2;

		$body='';
		//количество не пустых товаров
		$count_all=1;
		
		//номер договора в хедере
		$header_numb='
			<div style="margin-top:2.5mm;font-size:10px;color:#656565;float:left;width:60%;">
				Договор поставки № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'<br/>
				Лист {PAGENO}
			</div>
		';
		if($p['doc_bar'])
			$header_numb.='
			<div style="margin-top:2.5mm;float:right;width:35%;text-align:right;">
				<img src="/files/'.substr($filename,0,-13).'bar.png" style="height:24px"/>
			</div>';
		$mpdf->SetHTMLHeader($header_numb);
		
		//логотип, контакты, футер
		$header='
			<div style="width:100%;padding-bottom:5mm;border-bottom:1px solid #bbb;">
				<div style="float:left;color:#333;margin-right:30px;width:30%;">';
		$header.=($p['doc_logo'])?'<img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=logo" width="200"/>':'';
		$header.='
				</div>
				<div style="width:45%;float:right;font-size:13;">
					<p style="margin:0 0 3px 0;padding:0;font-size:14;font-weight:bold;">'.transform::typography($p['doc_header_org_name']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_address']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$p['doc_header_org_email'].'">'.transform::typography($p['doc_header_org_email']).'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		
		
		
		$body.=$header;//$mpdf->SetHTMLHeader($header);
	
		$footer='<div style="float:left;width:10%;">
					&nbsp;
				</div>
				<div style="width:90%;text-align:right">
					<table width="160mm" style="margin-top:2.5mm;">
						<thead>
							<tr>
								<td width="35mm"></td>
								<td width="2.5mm"></td>
								<td width="30mm"></td>
								<td width="20mm"></td>
								<td width="35mm"></td>
								<td width="2.5mm"></td>
								<td width="30mm"></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="border-bottom:1px solid #bbb;"></td>
								<td></td>
								<td>/ '.transform::fio($client['m_contragents_c_director_name']).' /</td>
								<td></td>
								<td style="border-bottom:1px solid #bbb;"></td>
								<td></td>
								<td>/ '.transform::fio($org['m_contragents_c_director_name']).' /</td>
							</tr>
							<tr>
								<td style="text-align:center;font-size:10;color:#656565;">Заказчик</td>
								<td></td>
								<td></td>
								<td></td>
								<td style="text-align:center;font-size:10;color:#656565;">Поставщик</td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>';
		if($p['doc_signature']){
			$footer.='<div>';
			$footer.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;margin-left:20mm;margin-top:-16mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
			$footer.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;margin-left:105mm;margin-top:-16mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			$footer.='</div>';
		}
		$mpdf->SetHTMLFooter($footer);
				
		//заголовок
		$body.='
			<h1 style="font-size:20;font-weight:400;text-align:center;padding-bottom:10px;padding-top:5mm;">Договор поставки № '.$p['doc_numb'].'</h1>
			<div>
				<table width="180mm">
					<tr>
						<td width="30%">г. Иваново</td>
						<td width="40%"></td>
						<td width="30%" style="text-align:right;">'.transform::date_f(dtu($p['doc_date'])).'</td>
					</tr>
				</table>
			</div>
		';
		
		//тело документа
		//физлицо
		if(in_array(3,explode('|',$client['m_contragents_type'])))
			$body.='
				<bookmark content="Преамбула" level="0"></bookmark>
				<p>Гражданин РФ <strong>'.$client['m_contragents_p_fio'].'</strong>'.($client['m_contragents_p_passport_sn']?' (паспорт '.$client['m_contragents_p_passport_sn'].($client['m_contragents_p_passport_v']?' выдан '.$client['m_contragents_p_passport_v']:'').($client['m_contragents_p_passport_date']?' '.dtu($client['m_contragents_p_passport_date'],'d.m.Y'):'').($client['m_contragents_p_birthday']?', дата рождения '.dtu($client['m_contragents_p_birthday'],'d.m.Y'):'').($client['m_contragents_address_j']?', зарегистрирован(а) по адресу '.$client['m_contragents_address_j']:'').')':'').', именуемый(ая) в дальнейшем &laquo;Покупатель&raquo;, с одной стороны,<br/>и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Поставщик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, заключили настоящий договор постаки (далее по тексту &ndash; &laquo;Договор&raquo;) о нижеследующем:</p>
			';		
		else{
			//ИП
			if(strlen($client['m_contragents_c_inn'])==12){
				$body.='
					<bookmark content="Преамбула" level="0"></bookmark>
					<p><strong>'.$client['m_contragents_c_name_full'].'</strong>, именуемый(ая) в дальнейшем &laquo;Покупатель&raquo;, с одной стороны,<br/>и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Поставщик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, заключили настоящий договор поставки (далее по тексту &ndash; &laquo;Договор&raquo;) о нижеследующем:</p>
				';
			}
			//ФИРМА
			else{
				$body.='
					<bookmark content="Преамбула" level="0"></bookmark>
					<p><strong>'.$client['m_contragents_c_name_full'].'</strong>, именуемое в дальнейшем &laquo;Покупатель&raquo;, в лице '.$client['m_contragents_c_director_post'].' '.$client['m_contragents_c_director_name_rp'].', действующего на основании '.$client['m_contragents_c_director_base'].', с одной стороны,<br/>и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Поставщик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, заключили настоящий договор поставки (далее по тексту &ndash; &laquo;Договор&raquo;) о нижеследующем:</p>
				';
			}
		}
		$body.='
			<bookmark content="1. Предмет договора" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">1. Предмет договора</h2>
			<p>1.1. Поставщик обязуется передать, а Покупатель - принять и оплатить строительные материалы (далее — Товар), количество и ассортимент которого устанавливаются в товаросопроводительных документах (спецификации, универсальных передаточных документах (далее — УПД)), которые являются неотъемлемой частью настоящего договора.</p>
			<p>1.2. Поставщик гарантирует, что Товар принадлежит ему на праве собственности, не заложен, не находится под арестом, не является предметом исков третьих лиц.</p>
			<p>1.3. Стороны определяют периодичность и сроки поставок Товара, его количество и ассортимент в порядке, установленном п.5.1 настоящего Договора.</p>
			<p>1.4. Право собственности на Товар переходит к Покупателю в момент передачи Товара его уполномоченному представителю на складе Поставщика и подписания УПД Сторонами в случае самовывоза или с момента передачи Товара транспортной компании, уполномоченной Покупателем.</p>
			<p>1.5. Право собственности на Товар переходит к Покупателю в момент передачи Товара его уполномоченному представителю на складе Покупателя (грузополучателя) и подписания им УПД в случае доставки Товара транспортной компанией, уполномоченной Поставщиком.</p>
			<bookmark content="2. Качество товара, упаковка и маркировка" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">2. Качество товара, упаковка и маркировка</h2>
			<p>2.1. Качество Товара должно соответствовать государственным стандартам качества РФ и быть подтверждено сертификатами соответствия, а также гигиеническими сертификатами, сертификатами пожарный безопасности, выдаваемыми соответствующими уполномоченными органами РФ, и гарантируется заводом изготовителем Товара.</p>
			<p>2.2.Сертификаты соответствия и гигиенические сертификаты (их надлежаще заверенные копии) передаются Поставщиком Покупателю в составе Товаросопроводительной документации.</p>
			<p>2.3.Каждая единица поставляемого по настоящему Договору Товара должна иметь соответствующую маркировку с обязательным указанием фирмы-изготовителя, места изготовления Товара, штрихового кодирования, срока годности Товаров и необходимой информации на русском языке в соответствии с действующим законодательством РФ.</p>
			<p>2.4.Тара и упаковка Товара должны соответствовать требованиям ГОСТ и действующего законодательства России, предъявляемым к таре и упаковке данного вида Товара. Тара и упаковка должны обеспечивать полную сохранность Товара во время его хранения и транспортировки.</p>
			<p>2.5.Тара является невозвратной.</p>
			<bookmark content="3. Цена товара и общая сумма договора" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">3. Цена товара и общая сумма договора</h2>
			<p>3.1. Цена на Товар определяется исходя из действующего прейскуранта Поставщика (отпускных цен) на день получения заявки от Покупателя. При поступлении заявки Поставщик выставляет Покупателю счет на оплату Товара.</p>
			<p>3.2. Поставщик вправе изменять согласованную цену на Товар в случае изменения цены заводом изготовителем. При изменении действующего прейскуранта Поставщик письменно уведомляет об этом Покупателя путем выставления нового счета на оплату.</p>
			<p>3.3. Цена на Товары по настоящему Договору устанавливается в рублях РФ и указываются в УПД. В цену Товара включается НДС.</p>
			<p>3.4. Поставщик выставляет Покупателю УПД в соответствии с действующим законодательством РФ.</p>
			<p>3.5. Цена товара включает в себя расходы Поставщика, связанные с выполнением условий настоящего Договора.</p>
			<p>3.6. Общую сумму Договора составляют все платежи, произведенные Покупателем Поставщику за поставку Товара в рамках настоящего Договора.</p>
			<bookmark content="4. Порядок оплаты" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">4. Порядок оплаты</h2>
			<p>4.1.Оплата каждой партии Товара, поставляемой в соответствии с настоящим Договором, производится путем 100-процентной оплаты счета в течение трех календарных дней со дня выставления счета. В случае непоступления денежных средств в оплату выставленного счета в течение трех календарных дней со дня его выставления Поставщик вправе по своему усмотрению изменить цену Товара в соответствии с п. 3.2 настоящего Договора или отодвинуть срок доставки или отгрузки Товара на количество дней такой задержки, при этом Поставщик не будет считаться нарушившим условия Договора о сроках поставки. В случае превышения суммы платежа над суммой выставленного счета разница может быть зачтена Покупателю в счет предстоящих поставок или возвращена Покупателю по его письменному заявлению.</p>
			<p>4.2. Требования статьи 317.1 Гражданского кодекса РФ к условиям настоящего Договора не применяются.</p>
			<p>4.3. Оплата осуществляется путем перевода денежных средств на расчетный счет Поставщика. Датой оплаты считается дата зачисления денежных средств на расчетный счет Поставщика. В платежном поручении в назначении платежа обязательно должна быть указана ссылка на номер и дату настоящего Договора. В случае отсутствия в платежном поручении такой ссылки Поставщик будет считать, что оплата произведена по настоящему Договору.</p>
			<p>4.4. Все расчеты по настоящему Договору осуществляются в рублях РФ.</p>
			<p>4.5. В случае наличия у Покупателя задолженности за ранее поставленный Товар, Поставщик вправе без согласия Покупателя зачесть денежные средства, поступившие от него по конкретной заявке, в счет сложившейся ранее задолженности.</p>
			<bookmark content="5. Условия поставки товаров" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">5. Условия поставки товаров</h2>
			<p>5.1.Поставки Товара по настоящему Договору производятся на основании заявок (заказов) Покупателя в сроки, согласованные Сторонами отдельно для каждой партии Товара. Сроки поставки Товара указываются в Спецификации. В заявках указываются следующие реквизиты: количество, ассортимент товара, срок поставки, место доставки при транспортировке Товара Поставщиком (привлеченными им третьими лицами).</p>
			<p>5.2.Поставка Товара осуществляется на условиях самовывоза Покупателем со склада Поставщика или путем доставки Товара силами Поставщика (привлеченными им третьими лицами).</p>
			<p>5.3.В случае поставки Товара на условиях самовывоза Поставщик обязуется обеспечить загрузку Товара в транспорт Покупателя.</p>
			<p>5.4.В УПД в качестве получателя Товаров может быть указан как непосредственно Покупатель, так и иные грузополучатели в соответствии с заявкой Покупателя.</p>
			<p>5.5.Датой поставки является дата передачи Товара на складе Поставщика, указанная в УПД, подписанной обеими Сторонами или дата передачи Товара транспортной компании, уполномоченной Покупателем.</p>
			<p>5.6.Датой поставки является дата передачи Товара на складе Покупателя или грузополучателя в случае доставки Товара транспортной компанией, уполномоченной Поставщиком.</p>
			<p>5.7. Поставщик гарантирует наличие (резервирование) заказанного Покупателем Товара на складе Поставщика в течение семи календарных дней со дня оплаты счета в соответствии с п. 4.1 настоящего Договора. В случае, если по истечение семи календарных дней после оплаты счета Покупатель не вывез оплаченный Товар на условиях самовывоза либо не предоставил Поставщику информацию об адресе доставки силами Поставщика, счет на поставку аннулируется, а соответствующая заявка считается вновь выставленной. Счет на оплату Поставщик выставляет вновь с учетом п. 3.2 настоящего Договора. Денежные средства, поступившие в оплату аннулированного счета, засчитываются в оплату вновь выставленного счета. В случае заказа Поставщиком Товара специально для Покупателя, условия и порядок поставки согласуются Сторонами в Дополнительном соглашении к настоящему Договору.</p>
			<p>5.8.Поставщик информирует Покупателя о том, что уполномоченные представители Поставщика не имеют полномочий на внесение изменений в унифицированные формы первичной учетной документации, за исключением выполнения условий п.6.5 настоящего Договора.</p>
			<p>5.9 Покупатель обязуется обеспечить возможность подъезда и разворота автотранспорта поставщика, в противном случае, он обязуется возместить все убытки Поставщика связанные с прогоном автотранспорта и пр.</p>
			<bookmark content="6. Порядок приемки товаров" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">6. Порядок приемки товаров</h2>
			<p>6.1. Приемка Товара производится Покупателем на складе Поставщика, в том числе при доставке Товара транспортной компанией, уполномоченной Покупателем, или на складе Покупателя в присутствии представителя транспортной компании, уполномоченной Поставщиком.</p>
			<p>6.2. Приемка поставленных Товаров оформляется в УПД на Товар, которая подписывается полномочными представителями Покупателя (грузополучателя) и Поставщика.</p>
			<p>6.3. Приемка Товаров по количеству, ассортименту и комплектности производится на основании УПД, а по качеству — на основании сертификатов соответствия и гигиенических сертификатов. Приемка Товара по количеству, ассортименту, комплектности и качеству производится в момент передачи Товара.</p>
			<p>6.4. В случае обнаружения видимых недостатков по качеству Товара или недостающего количества Товара представитель Поставщика или представитель транспортной компании, уполномоченной Поставщиком, и уполномоченный представитель Покупателя (грузополучателя), осуществляющего прием Товара, составляют и подписывают Акт о ненадлежащем качестве или о недостаче Товара.</p>
			<p>6.5. Покупатель обязан заполнить все реквизиты, установленные в соответствующих документах экспедитора (перевозчика), подтверждающих приемку Товара, в том числе количество мест, занимаемых Товаром, имеющиеся недостатки упаковки, подпись и ее расшифровку лица, осуществляющего приемку, подпись и ее расшифровку лица, передающего Товар, печать Покупателя либо оригинал доверенности лица, уполномоченного Покупателем. В случае наличия незаполненных реквизитов на товаросопроводительных документах со стороны Покупателя Акт о ненадлежащем качестве или о недостаче Товара (п.6.4), составленный Покупателем в одностороннем порядке или с привлечением представителя экспедитора (перевозчика), не будет иметь юридической силы.</p>
			<p>6.6. В случае, когда грузополучателем Товара является третье лицо, Поставщик направляет оригиналы УПД, спецификаций в двух экземплярах в адрес Заказчика по почте. Заказчик подписывает УПД и отпраляет Поставщику второй экземляр почтой в течение 3 (трех) рабочих дней с момента получения оригиналов.</p>
			<bookmark content="7. Претензии" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">7. Претензии</h2>
			<p>7.1 В случае, если Покупатель, в течение 5 дней после приемки Товара, обнаружит недостатки по качеству поставленного Товара он обязан вызвать представителя Поставщика для составления соответствующего Акта. В случае нарушения сроков, указанных в настоящем пункте, Претензии Покупателя не принимаются.</p>
			<p>7.2.Поставщик обязан рассмотреть претензию Покупателя в течение 3 (трех) рабочих дней со дня получения и в течение указанного выше срока дать Покупателю ответ, как в случае принятия претензии, так и в случае ее отклонения. В случае отклонения претензии Покупателя, Поставщик обязан письменно изложить обоснование такого отклонения.</p>
			<p>7.3.При несоответствии Товара по качеству, Поставщик возвращает Покупателю стоимость некачественного Товара или заменяет некачественный Товар качественным, а при недопоставке – поставляет необходимое количество Товара в срок, дополнительно согласуемый Сторонами.</p>
			<p>7.4.В случае, если качество и (или) количество Товара не соответствует документам Поставщика, представленным Покупателю транспортной компанией, уполномоченной Покупателем, то претензии, предусмотренные п.7.1 настоящего Договора, предъявляются Покупателем указанной транспортной компании.</p>
			<bookmark content="8. Ответственность сторон" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">8. Ответственность сторон</h2>
			<p>8.1.Во всех вопросах, не предусмотренных настоящим Договором, ответственность Сторон определяется в соответствии с действующим законодательством РФ.</p>
			<p>8.2. Условия настоящего Договора, предусматривающие пени, а также штрафы и иные санкции, связанные с возмещением ущербов в случае нарушения договорных обязательств Сторон в соответствии с действующим законодательством РФ, применяются только в том случае (отлагательное условие), если Сторона Договора, в чью пользу установлена санкция, либо в чью пользу возмещаются убытки, после нарушения договорных обязательств другой стороной в письменном виде известит ее о намерении взыскать полагающиеся по договору пени, штрафы или иные санкции и (или) потребовать возмещения убытков по факту конкретного нарушения договорных обязательств. Если же такое извещение сделано не было, то соответствующие условия настоящего Договора считаются недействительными и применению не подлежат.</p>
			<bookmark content="9. Освобождение от ответственности. Обстоятельства непреодолимой силы" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">9. Освобождение от ответственности. Обстоятельства непреодолимой силы</h2>
			<p>9.1. Стороны освобождаются от ответственности за частичное или полное неисполнение обязательств по настоящему Договору, если это неисполнение явилось следствием обстоятельств непреодолимой силы, возникших после заключения настоящего Договора, в результате событий чрезвычайного характера, наступление которых Сторона, не выполнившая обязательства полностью или частично, не могла ни предвидеть, ни предотвратить (форс-мажор). К обстоятельствам непреодолимой силы относятся также пожары, наводнения, стихийные бедствия, военные действия и акты государственных органов законодательной и исполнительной власти, сделавшие невозможным исполнения Сторонами своих обязательств по настоящему Договору.</p>
			<p>9.2. В случае наступления обстоятельств непреодолимой силы срок исполнения Сторонами своих обязательств по настоящему Договору отодвигается соразмерно времени, в течение которого будут действовать такие обстоятельства.</p>
			<p>9.3. Сторона, подвергнувшаяся обстоятельствам непреодолимой силы, обязана в течение 3 (трех) календарных дней со дня наступления указанных обстоятельств известить об этом другую Сторону с приложением соответствующих доказательств.</p>
			<p>9.4. В случае, если срок действия обстоятельств непреодолимой силы превышает один календарный месяц, то Стороны обязуются разрешить дальнейшую юридическую судьбу настоящего Договора.</p>
			<bookmark content="10. Арбитраж" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">10. Арбитраж</h2>
			<p>10.1. Все вопросы, связанные с исполнением настоящего Договора, Стороны попытаются решить путем соглашения и подписания соответствующих документов.</p>
			<p>10.2. Претензионный порядок досудебного урегулирования споров из Договора является для Сторон обязательным. Срок рассмотрения претензий — 15 (пятнадцать) календарных дней со дня получения претензии Стороной.</p>
			<p>10.3. Стороны договорились, любые споры, возникшие между сторонами, решаются путем переговоров, а при не достижении согласия — в Арбитражном суде по месту нахождения истца.</p>
			<bookmark content="11. Прочие условия" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">11. Прочие условия</h2>
			<p>11.1. Подписывая Договор, Стороны гарантируют, что у них отсутствуют какие-либо ограничения на подписание Договора, в том числе любых приложений и дополнительных соглашений к нему.</p>
			<p>11.2. Все изменения и дополнения к настоящему Договору считаются действительными лишь в том случае, если они совершены в письменной форме и подписаны полномочными представителями обеих Сторон.</p>
			<p>11.3.Настоящий Договор вступает в силу с момента его подписания Сторонами и действует сроком на один календарный год, а в части взаиморасчетов до их полного осуществления. Если Стороны за один месяц до истечения действия Договора письменно не изъявят желания расторгнуть Договор, то настоящий Договор считается автоматически перезаключенным на следующий календарный год.</p>
			<p>11.4. С момента вступления в силу Договора все предыдущие переговоры и переписка, связанные с Договором, утрачивают свою силу. Во всем остальном, что не предусмотрено настоящим Договором, Стороны руководствуются действующим законодательством Российской Федерации (в Договоре также — РФ).</p>
			<p>11.5. Настоящий Договор составлен в 2 (Двух) экземплярах, имеющих одинаковую силу, по одному экземпляру для каждой из Сторон. Каждый лист Договора, приложений и дополнительных соглашений к Договору подписывается представителями обеих Сторон. Договор, приложения и дополнительные соглашения к Договору составляются в письменной форме в виде единого документа, подписанного Сторонами в 2 (Двух) экземплярах. Любые дописки, изменения, дополнения в тексте Договора, его приложениях и дополнительных соглашениях не допускаются.</p>
			<p>11.6. Стороны принимают и признают действительным оригиналом, заменяющим оригинал на бумажном носителе, цветные сканированные изображения на бумажном носителе договора поставки, претензий, писем, заявлений, протоколов разногласий, дополнительных соглашений, и прочих документов, оформляемых сторонами и направляемых друг другу, отправленные по электронной почте, содержащие изображение оригинальной печати стороны по Договору либо иного указанного в настоящем пункте документа, и изображение оригинальной подписи руководителя юридического лица - стороны по Договору, в связи с чем претензии одной из сторон о ненадлежащем оформлении сделки (в связи с отсутствием у Стороны оригинала договора поставки на бумажном носителе) другой стороной не принимаются. Каждая Сторона вправе отказаться от сделки до получения надлежаще оформленного оригинала Договора, если полученное электронное изображение документа вызывает сомнение в его подлинности.</p>
			<p>11.7. Стороны признают надлежащим уведомлением отправления, указанные в п. 11.3. настоящего Договора, а подтверждением такого уведомления Стороны признают сведения электронных ящиков Сторон, свидетельствующие о направлении другой Стороне электронного письма, в том числе содержащего вложения сканированных копий документов.</p>
			<p>11.8. Покупатель обязан в течение одного дня с даты получения от Поставщика по почте подлинных экземпляров Договора и Приложений к нему подписать их со своей стороны и направить один экземпляр в адрес Поставщика по почте заказным письмом с уведомлением. Поставщик имеет право не осуществлять поставку Товара (части Товара) до момента получения от Покупателя оригинала, подписанного с его стороны Договора и Приложений к нему.</p>
			<p>11.9. Стороны обязуются в течении 10 (Десяти) рабочих дней с момента изменения адреса или банковских реквизитов уведомлять друг друга об их изменении. Неисполнение Стороной настоящего пункта лишает ее права ссылаться на то, что предусмотренные Договором уведомление или платеж не были произведены надлежащим образом. За убытки, причиненные не сообщением изменений адресов и других реквизитов, отвечает не сообщившая об этом Сторона.</p>
			<p>11.10. Досрочное расторжение Договора может иметь место в соответствии с п. 9.1 настоящего Договора либо по соглашению Сторон, либо на основаниях, предусмотренных законодательством Российской Федерации.</p>
			<p>11.11. Товар, поставляемый по настоящему Договору предназначен для использования и реализации на территории Российской Федерации.</p>
			<bookmark content="12. Особые условия (керамическая плитка, керамогранит)" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">12. Особые условия (керамическая плитка, керамогранит)</h2>
			<p>12.1. При поставке керамической продукции (керамическая плитка, керамогранит) Поставщик возмещает Покупателю бой Товара, если бой составляет более 1,8 (Одна целая восемь десятых) процента от общего количества поставленной партии керамической продукции, произошедший во время транспортировки товара транспортом Поставщика, только на основании письменного уведомления с приложением фотографий поврежденного Товара. Претензии принимаются в течение 2-х дней со дня получения Товара.</p>
			<bookmark content="10. Адреса, реквизиты  и подписи сторон" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">10. Адреса, реквизиты  и подписи сторон</h2>
			<table style="font-size:13;border-spacing:0;border-top:1px solid #bbb;margin-top:15mm;margin-bottom:6mm;border-right:1px solid #bbb;" width="180mm">
				<thead>
					<tr>
						<td style="font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;text-align:center;" width="30mm"></td>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;text-align:center;" width="80mm">Заказчик</td>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;text-align:center;" width="80mm">Поставщик</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Наименование</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_name_full'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_name_full'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Юридический адрес</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client_address[1]['m_address_full'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org_address[1]['m_address_full'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Фактический адрес</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client_address[2]['m_address_full'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org_address[2]['m_address_full'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Почтовый адрес</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client_address[3]['m_address_full'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org_address[3]['m_address_full'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Телефон</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['tel'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['tel'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">E-mail</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_email'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_email'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">ИНН</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_inn'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_inn'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">КПП</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_kpp'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_kpp'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">ОГРН</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_ogrn'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_ogrn'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Банк</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.transform::typography($info->getDefaultRS($client['m_contragents_id'])['m_contragents_rs_bank']).'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.transform::typography($info->getDefaultRS($org['m_contragents_id'])['m_contragents_rs_bank']).'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">БИК</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$info->getDefaultRS($client['m_contragents_id'])['m_contragents_rs_bik'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$info->getDefaultRS($org['m_contragents_id'])['m_contragents_rs_bik'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Расч. сч.</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$info->getDefaultRS($client['m_contragents_id'])['m_contragents_rs_rs'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$info->getDefaultRS($org['m_contragents_id'])['m_contragents_rs_rs'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Корр. сч.</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$info->getDefaultRS($client['m_contragents_id'])['m_contragents_rs_ks'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$info->getDefaultRS($org['m_contragents_id'])['m_contragents_rs_ks'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Должность подписанта</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_director_post'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_director_post'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Ф.И.О. подписанта</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_director_name'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_director_name'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Подпись</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;"></td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;"></td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Место печати</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;"></td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;"></td>
					</tr>
				</tbody>
			</table>
		';

		//печати и подписи
		if($p['doc_signature']){
			$body.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:50mm;margin-top:-23mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
			$body.=$client['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:45mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=stamp"/></div>':'';
			$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:125mm;margin-top:-23mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:140mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
		}
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);

		$mpdf->WriteHTML($body);
		$mpdf->SetHTMLFooter('');
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}
	}
	
	//спецификация отдельно
	public static function contract_goods_year_spec($p=array(
		'org'=>'',
		'client'=>'',
		'order'=>'',
		'base_numb'=>'',
		'base_date'=>'',
		'doc_date'=>'',
		'doc_numb'=>'',
		'doc_logo'=>null,
		'doc_bar'=>null,
		'doc_signature'=>null,
		'doc_header_org_name'=>null,
		'doc_header_org_address'=>null,
		'doc_header_org_tel'=>null,
		'doc_header_org_email'=>null,		
		'invoice'=>null,
		'm_documents_doc_delivery_time'=>null
	),$filename){
		global $info,$documents,$contragents,$orders,$services,$products;
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		
		$client['m_contragents_c_director_name']=$client['m_contragents_c_director_name']?$client['m_contragents_c_director_name']:$client['m_contragents_p_fio'];
		
		$client_address=$info->getAddress($p['client']);
		$client_address=change_key($client_address,'m_address_type',true);
		
		if($client['m_contragents_consignee']){
			$client_consignee_address=$info->getAddress($client['m_contragents_consignee']);
			$client_consignee_address=change_key($client_consignee_address,'m_address_type',true);
		}
		
		$org_address=$info->getAddress($p['org']);
		$org_address=change_key($org_address,'m_address_type',true);
		
		if($org['m_contragents_consignee']){
			$org_consignee_address=$info->getAddress($org['m_contragents_consignee']);
			$org_consignee_address=change_key($org_consignee_address,'m_address_type',true);
		}
		
		$p['nds']=$orders->orders_id[$p['order']][0]['m_orders_nds'];
		
		$org['tel']=$info->getTel($p['org'])[0]['m_contragents_tel_numb'];
		$client['tel']=$info->getTel($p['client'])[0]['m_contragents_tel_numb'];
		
		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('','',9,'dejavuserifcondensed',15,15,20,25,10,10);//$mpdf=new mPDF('','',10,'dejavuserifcondensed',15,15,40,25,5,10);
		$mpdf->SetAuthor(transform::typography(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2))));
		$mpdf->SetCreator(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2)));
		$mpdf->SetSubject(strcode2utf('Спецификация'));
		$mpdf->SetTitle(strcode2utf('Спецификация № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));
		$mpdf->SetKeywords(strcode2utf('Спецификация № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));		
		$mpdf->defaultCSS['P']['MARGIN']='0.9em 0';
		$mpdf->shrink_tables_to_fit=2;

		$body='';
		//количество не пустых товаров
		$count_all=1;
		
		//номер договора в хедере
		$header_numb='
			<div style="margin-top:2.5mm;font-size:10px;color:#656565;float:left;width:60%;">
				Спецификация по Счёту № '.$documents->getInfo((string)$p['invoice'])['m_documents_numb'].' от '.transform::date_f(dtu($documents->getInfo((string)$p['invoice'])['m_documents_date'])).'<br/>
				Приложение № 1 к Договору поставки № '.$p['base_numb'].' от '.transform::date_f(dtu($p['base_date'])).'<br/>
				Лист {PAGENO}
			</div>
		';
		
		if($p['doc_bar'])
			$header_numb.='
			<div style="margin-top:2.5mm;float:right;width:35%;text-align:right;">
				<img src="/files/'.substr($filename,0,-4).'bar.png" style="height:24px"/>
			</div>';
		$mpdf->SetHTMLHeader($header_numb);
		
		//логотип, контакты, футер
		$header='
			<div style="width:100%;padding-bottom:5mm;border-bottom:1px solid #bbb;">
				<div style="float:left;color:#333;margin-right:30px;width:35%;">';
		$header.=($p['doc_logo'])?'<img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=logo_big" width="100%"/>':'';
		$header.='
				</div>
				<div style="width:45%;float:right;font-size:13;">
					<p style="margin:0 0 3px 0;padding:0;font-size:14;font-weight:bold;">'.transform::typography($org['m_contragents_c_name_short']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($org_address[4]['m_address_full']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($org['tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$org['m_contragents_email'].'">'.$org['m_contragents_email'].'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		
		$body.=$header;//$mpdf->SetHTMLHeader($header);
		

		$body.='
			<bookmark content="Спецификация" level="0"></bookmark>
		';
		//заголовок
		$body.='
			<h1 style="font-size:20;font-weight:400;text-align:center;padding-bottom:10px;padding-top:5mm;">Спецификация<br/><span style="color:#666;font-size:14">по Счёту № '.$documents->getInfo((string)$p['invoice'])['m_documents_numb'].' от '.transform::date_f(dtu($documents->getInfo((string)$p['invoice'])['m_documents_date'])).'</span></h1>
			<div>
				<table width="180mm">
					<tr>
						<td width="30%">г. Иваново</td>
						<td width="40%"></td>
						<td width="30%" style="text-align:right;">'.transform::date_f(dtu($p['doc_date'])).'</td>
					</tr>
				</table>
			</div>
		';
		$body.='
			<p>1. Поставщик обязуется поставить Товар следующего сортамента:</p>
		';
		
		//загружаем товары из счета
		$p['items']=json_decode($documents->getInfo((string)$p['invoice'])['m_documents_params'])->items;
		$sum=0;
		$nds18=0;
		$items=0;
		$l=1;
		$rooms_count=count((array)$p['items']);
		foreach($p['items'] as $_room){
			//если есть позиции в разделе
			if($_room->services){
				if($rooms_count>1)
					$body.='
						<bookmark content="'.($_room->room->name?$_room->room->name:'Раздел '.$l).'" level="1"></bookmark>
						<h2 style="font-size:15;font-weight:400;margin-bottom:1mm;"><b>'.($_room->room->name?$_room->room->name:'Раздел'.$l).'</b></h2>
					';
				$body.='<table style="font-size:11;border-spacing:0;border-top:1px solid #bbb;margin-bottom:1mm;border-right:1px solid #bbb;" width="180mm">
							<thead>
								<tr>
									<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="10mm">№</td>
									<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="80mm">Наименование</td>
									<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="18mm">Ед. изм.</td>
									<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="17mm">Кол-во</td>
									<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="25mm">Цена</td>
									<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;text-align:center;" width="30mm;">Сумма</td>
								</tr>
							</thead>
							<tbody>';
				//работы
				$pre_sum=0;
				$pre_nds18=0;
				foreach($_room->services as $k=>$_service){
					$nds_item=isset($_service->nds)?$_service->nds:$orders->orders_id[$p['order']][0]['m_orders_nds'];
					$orders->orders_id[$p['order']][0]['m_orders_nds']=isset($_service->nds)?$_service->nds:$orders->orders_id[$p['order']][0]['m_orders_nds'];
					$pre_sum+=$_service->sum;
					$pre_nds18+=round($_service->sum*($nds_item/100)/(1+$nds_item/100),2);
					$body.='	<tr>
									<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.($k+1).'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$services->services_id[$_service->id][0]['m_services_name']:(isset($products->products_id[$_service->id])?$products->products_id[$_service->id][0]['m_products_name']:'')).'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$info->getUnits($services->services_id[$_service->id][0]['m_services_unit']):(isset($products->products_id[$_service->id])?$info->getUnits($products->products_id[$_service->id][0]['m_products_unit']):'')).'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.$_service->count/* str_replace('.',',',$_service->count) */.'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.transform::price_o($_service->price).'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:center;">'.transform::price_o($_service->sum).'</td>
								</tr>';
				}
				$sum+=$pre_sum;
				$nds18+=$pre_nds18;
				$items+=sizeof($_room->services);
				//промежуточные итоги - выводим, только если в счете больше одного раздела
				if($rooms_count>1){
					//сумма
					$body.='	<tr>
									<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Промежуточный итог:</td>
									<td style="padding:1mm;text-align:center;border-bottom:1px solid #bbb;">'.transform::price_o($pre_sum).'</td>
								</tr>';
					//НДС 18%
					$body.=($orders->orders_id[$p['order']][0]['m_orders_nds']==18)?'
								<tr>
									<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС ('.$orders->orders_id[$p['order']][0]['m_orders_nds'].'%):</td>
									<td style="vertical-align:top;padding:1mm;border-bottom:1px solid #bbb;text-align:center;">'.transform::price_o($pre_nds18).'</td>
								</tr>':'';
				}
				$body.='
							</tbody>
						</table>';
			}
		}
		
		//итоговые суммы
		$body.='
					<table style="font-size:11;border-spacing:0;margin:6mm 0 2mm;border-right:1px solid #bbb;" width="180mm">
						<thead>
							<tr>
								<td width="10mm"></td>
								<td width="80mm"></td>
								<td width="18mm"></td>
								<td width="17mm"></td>
								<td width="25mm"></td>
								<td width="30mm;"></td>
							</tr>
						</thead>
						<tbody>';
						
		$body.='			<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum).'</td>
							</tr>';
		//скидка
		if($orders->orders_id[$p['order']][0]['m_orders_discount']>0)
			$body.='		<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Скидка '.$orders->orders_id[$p['order']][0]['m_orders_discount'].'%:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		//итого со скидкой
		if($orders->orders_id[$p['order']][0]['m_orders_discount']>0)
			$body.='		<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого со скидкой:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		//итог НДС 18%
		$body.=($orders->orders_id[$p['order']][0]['m_orders_nds']!=-1)?'
							<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС ('.$orders->orders_id[$p['order']][0]['m_orders_nds'].'%):</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($nds18-$nds18*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>':'';
		//итог БЕЗ НДС
		$body.=($orders->orders_id[$p['order']][0]['m_orders_nds']==-1)?'
							<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Без налога (НДС)</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:right;text-align:center;">—</td>
							</tr>':'';
		//всего к оплате
		$body.='			<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;font-weight:bold">Всего к оплате:</td>
								<td style="vertical-align:top;padding:1mm;border-top:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;font-weight:bold">'.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		$body.='
						</tbody>
					</table>';
					
		$body.='
			<p>2. Сумма настоящей спецификации составляет '.transform::price_o($sum).' ('.transform::summ_text($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).($orders->orders_id[$p['order']][0]['m_orders_nds']!=-1?(', в том числе НДС '.$orders->orders_id[$p['order']][0]['m_orders_nds'].'% — '.transform::summ_text($nds18-$nds18*$orders->orders_id[$p['order']][0]['m_orders_discount']/100,true,false)):'').').</p>
			<p>3. Условия поставки: '.($p['m_documents_doc_delivery_self']?'Самовывоз со склдаа Поставщика.':'транспортом Поставщика по адресу: '.$client_address[4]['m_address_full']).'</p>
			<p>4. Cроки поставки: в течение '.$p['m_documents_doc_delivery_time'].' ('.transform::summ_text($p['m_documents_doc_delivery_time'],false,false).') рабочих дней с момента поступления предоплаты.
			<p>5. Грузополучателем Товара является '.($client['m_contragents_consignee']?$contragents->getName($client['m_contragents_consignee']).'(ИНН '.$contragents->getInfo($client['m_contragents_consignee'])['m_contragents_c_inn'].', КПП '.$contragents->getInfo($client['m_contragents_consignee'])['m_contragents_c_kpp'].') ,'.$client_consignee_address[1]['m_address_full']:$client['m_contragents_c_name_short']).'.</p>
		';
		
		//таблица для подписей и печати
			$body.='<table style="font-size:13;border-spacing:0;margin:10mm 0 2mm;line-height:4.5mm" width="180mm">
						<tr>
							<td width="180mm">
								<table style="font-size:13;border-spacing:0;margin:0;line-height:4.5mm" width="180mm">
									<thead>
										<tr>
											<td width="38mm"></td>
											<td width="7mm"></td>
											<td width="38mm"></td>
											<td width="7mm"></td>
											<td width="38mm"></td>
											<td width="7mm"></td>
											<td width="38mm"></td>
										</tr>
										<tr>
											<td colspan="3" style="font-weight:bold;padding-bottom:5mm">Заказчик</td>
											<td width="7mm"></td>
											<td colspan="3" style="font-weight:bold;padding-bottom:5mm">Поставщик</td>
										</tr>
									</thead>
									<tr>
										<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$client['m_contragents_c_director_post'].'</td>
										<td></td>
										<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$org['m_contragents_c_director_post'].'</td>
									</tr>
									<tr>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
										<td></td>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
									</tr>
									<tr>
										<td></td>
										<td></td>
										<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($client['m_contragents_c_director_name']).'</td>
										<td></td>
										<td></td>
										<td></td>
										<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($org['m_contragents_c_director_name']).'</td>
									</tr>
									<tr>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
										<td></td>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
										<td></td>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
										<td></td>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
									</tr>
									<tr>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
										<td></td>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';	
			
			//печати и подписи
			if($p['doc_signature']){
				$body.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:20mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
				$body.=$client['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:35mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=stamp"/></div>':'';
				$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:115mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
				$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:130mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
			}
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);

		$mpdf->WriteHTML($body);
		$mpdf->SetHTMLFooter('');
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}	
			
	}
	
	
	//доверенность
	public static function doverennost($p=array(
		'org'=>'',
		'client'=>'',
		'agent'=>'',
		'order'=>'',
		'doc_date'=>'',
		'doc_numb'=>'',
		'doc_logo'=>null,
		'doc_bar'=>null,
		'doc_signature'=>null,	
		'invoice'=>null,
		'm_documents_date_from'=>null,
		'm_documents_date_to'=>null,
		'print_items'=>false
	),$filename){
		global $info,$documents,$contragents,$orders,$services,$products;
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		$agent=$contragents->getInfo($p['agent']);
		
		$client['m_contragents_c_director_name']=$client['m_contragents_c_director_name']?$client['m_contragents_c_director_name']:$client['m_contragents_p_fio'];
		
		$client_address=$info->getAddress($p['client']);
		$client_address=change_key($client_address,'m_address_type',true);
		
		if($client['m_contragents_consignee']){
			$client_consignee_address=$info->getAddress($client['m_contragents_consignee']);
			$client_consignee_address=change_key($client_consignee_address,'m_address_type',true);
		}
		
		$org_address=$info->getAddress($p['org']);
		$org_address=change_key($org_address,'m_address_type',true);
		
		if($org['m_contragents_consignee']){
			$org_consignee_address=$info->getAddress($org['m_contragents_consignee']);
			$org_consignee_address=change_key($org_consignee_address,'m_address_type',true);
		}
		
		$org['tel']=$info->getTel($p['org'])[0]['m_contragents_tel_numb'];
		$client['tel']=$info->getTel($p['client'])[0]['m_contragents_tel_numb'];
		
		$invoice=$documents->getInfo((string)$p['invoice']);
		
		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('','',9,'dejavuserifcondensed',15,15,20,25,10,10);
		$mpdf->SetAuthor(transform::typography(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2))));
		$mpdf->SetCreator(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2)));
		$mpdf->SetSubject(strcode2utf('Доверенность'));
		$mpdf->SetTitle(strcode2utf('Доверенность № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));
		$mpdf->SetKeywords(strcode2utf('Доверенность № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));		
		$mpdf->defaultCSS['P']['MARGIN']='0.9em 0';
		$mpdf->shrink_tables_to_fit=2;

		$body='';
		//количество не пустых товаров
		$count_all=1;
		
		//номер договора в хедере
		$header_numb='
			<div style="margin-top:2.5mm;font-size:10px;color:#656565;float:left;width:60%;">
				Доверенность № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'<br/>
				к Счету № '.$invoice['m_documents_numb'].' от '.transform::date_f(dtu($invoice['m_documents_date'])).'
				Лист {PAGENO}
			</div>
		';
		
		if($p['doc_bar'])
			$header_numb.='
			<div style="margin-top:2.5mm;float:right;width:35%;text-align:right;">
				<img src="/files/'.substr($filename,0,-11).'bar.png" style="height:24px"/>
			</div>';
		$mpdf->SetHTMLHeader($header_numb);
		
		//логотип, контакты, футер
		$header='
			<div style="width:100%;padding-bottom:5mm;border-bottom:1px solid #bbb;">
				<div style="float:left;color:#333;margin-right:30px;width:35%;">';
		$header.=($p['doc_logo'])?'<img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=logo_big" width="100%"/>':'';
		$header.='
				</div>
				<div style="width:45%;float:right;font-size:13;">
					<p style="margin:0 0 3px 0;padding:0;font-size:14;font-weight:bold;">'.transform::typography($client['m_contragents_c_name_short']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($client_address[3]['m_address_full']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($client['tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$client['m_contragents_email'].'">'.$client['m_contragents_email'].'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		
		$body.=$header;//$mpdf->SetHTMLHeader($header);
		
		//верхняя часть
		$body.='
			<bookmark content="Доверенность" level="0"></bookmark>
			<div>
				<table style="font-size:10;border-spacing:0;margin-bottom:1mm;border-right:1px solid #bbb;" width="180mm">
					<thead>
						<tr>
							<td width="20mm"></td>
							<td width="20mm"></td>
							<td width="20mm"></td>
							<td width="45mm"></td>
							<td width="45mm"></td>
							<td width="10mm"></td>
						</tr>
						<tr>
							<td style="background:#eee;font-size:10;padding:.5mm .5mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">Номер доверенности</td>
							<td style="background:#eee;font-size:10;padding:.5mm .5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">Дата выдачи</td>
							<td style="background:#eee;font-size:10;padding:.5mm .5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">Срок действия</td>
							<td style="background:#eee;font-size:10;padding:.5mm .5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" colspan="2">Должность и фамилия лица, которуму выдана доверенность</td>
							<td style="background:#eee;font-size:10;padding:.5mm .5mm;border-bottom:1px solid #bbb;text-align:center;">Расписка в получении доверенности</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">1</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">2</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">3</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" colspan="2">4</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;text-align:center;">5</td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.$p['doc_numb'].'</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.dtu($p['m_documents_date_from'],'d.m.Y').'</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.dtu($p['m_documents_date_to'],'d.m.Y').'</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" colspan="2"><b>'.$agent['m_contragents_p_fio'].'</b></td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;text-align:center;"></td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" colspan="3">Поставщик</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" colspan="2">Номер и дата наряда (заменяющего наряд документа) или извещения</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;text-align:center;">Номер, дата документа, подтверждающего выполнение поручения</td>

						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" colspan="3">6</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" colspan="2">7</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;text-align:center;">8</td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" colspan="3">'.$org['m_contragents_c_name_short'].'</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" colspan="2">Счет на оплату № '.$invoice['m_documents_numb'].' от '.dtu($invoice['m_documents_date'],'d.m.Y').'</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;text-align:center;"></td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px dotted #bbb;text-align:center;" colspan="6">Линия отреза</td>
						</tr>
						<tr>
							<td style="padding:.5mm;" colspan="4"></td>
							<td style="padding:.5mm;text-align:right;" colspan="2">Типовая межотраслевая форма № М-2 Утверждена постановлением Госкомстата России от 30.10.97 № 71а</td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-right:1px solid #bbb;text-align:center;" colspan="5"></td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;border-top:1px solid #bbb;text-align:center;">Коды</td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;" colspan="5">Форма по ОКУД</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;text-align:center;">315001</td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">Организация</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;" colspan="3"><b>'.$client['m_contragents_c_name_short'].'(ИНН '.$client['m_contragents_c_inn'].', КПП '.$client['m_contragents_c_kpp'].')</b></td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">по ОКПО</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;text-align:center;">'.$client['m_contragents_c_okpo'].'</td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;" colspan="6"><h1 style="font-size:12;font-weight:400;text-align:center;padding-bottom:10px;padding-top:5mm;">Доверенность № '.$p['doc_numb'].'</h1></td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">Дата выдачи</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;" colspan="5">'.transform::date_f(dtu($p['m_documents_date_from'])).'</td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">Доверенность действительна по</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;" colspan="4">'.transform::date_f(dtu($p['m_documents_date_to'])).'</td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;" colspan="6">'.$client['m_contragents_c_name_short'].', '.$client_address[3]['m_address_full'].'</td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;font-size:9;color:#666;" colspan="6">(наименование потребителя и его адрес)</td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;" colspan="6">он же</td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;font-size:9;color:#666;" colspan="6">(наименование плательщика и его адрес)</td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">Доверенность выдана</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;" colspan="5"><b>'.$agent['m_contragents_p_fio'].'</b></td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">Паспорт</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;" colspan="5"><b>серия '.str_replace(' ', ' № ',$agent['m_contragents_p_passport_sn']).'</b></td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">Кем выдан</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;" colspan="5"><b>'.$agent['m_contragents_p_passport_v'].'</b></td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">Дата выдачи</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;" colspan="5"><b>'.dtu($agent['m_contragents_p_passport_date'],'d.m.Y').'</b></td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">На получение от</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;" colspan="5"><b>'.$org['m_contragents_c_name_short'].'</b></td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">материальных ценностей по</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;" colspan="5"><b>счёту № '.$invoice['m_documents_numb'].' от '.transform::date_f(dtu($invoice['m_documents_date'])).'</b></td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;font-weight:700;" colspan="6">Перечень товарно-материальных ценностей, подлежащих получению</td>
						</tr>
						<tr>
							<td style="padding:.5mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">Номер по порядку</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;" colspan="3">Наименование</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">Ед. изм.</td>
							<td style="padding:.5mm;border-bottom:1px solid #bbb;text-align:center;">Кол-во</td>
						</tr>
		';
		
		//загружаем товары из счета
		$p['items']=json_decode($invoice['m_documents_params'])->items;
		$sum=0;
		$nds18=0;
		$items=0;
		$l=1;
		$rooms_count=count((array)$p['items']);
		foreach($p['items'] as $_room)
			//если есть позиции в разделе
			if($_room->services&&$p['print_items'])
				foreach($_room->services as $k=>$_service)
					$body.='<tr>
								<td style="padding:.5mm;border-left:1px solid #bbb;border-right:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;">'.($k+1).'</td>
								<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;" colspan="3">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$services->services_id[$_service->id][0]['m_services_name']:(isset($products->products_id[$_service->id])?$products->products_id[$_service->id][0]['m_products_name']:'')).'</td>
								<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$info->getUnits($services->services_id[$_service->id][0]['m_services_unit']):(isset($products->products_id[$_service->id])?$info->getUnits($products->products_id[$_service->id][0]['m_products_unit']):'')).'</td>
								<td style="padding:.5mm;border-bottom:1px solid #bbb;text-align:center;">'.$_service->count.'</td>
							</tr>';
			else{
				$body.='	<tr>
								<td style="padding:.5mm;border-left:1px solid #bbb;border-right:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;">1</td>
								<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;" colspan="3">ТМЦ</td>
								<td style="padding:.5mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">шт</td>
								<td style="padding:.5mm;border-bottom:1px solid #bbb;text-align:center;">1</td>
							</tr>';
			}
		$body.='
					</tbody>
				</table>
			</div>';
				
		//таблица для подписей и печати
		$body.='<table style="font-size:13;border-spacing:0;margin-top:10mm;line-height:3.5mm" width="180mm">
					<tr>
						<td width="180mm">
							<table style="font-size:10;border-spacing:0;margin:0;line-height:3.5mm" width="180mm">
								<thead>
									<tr>
										<td width="20mm"></td>
										<td width="7mm"></td>
										<td width="46mm"></td>
										<td width="7mm"></td>
										<td width="40mm"></td>
										<td width="7mm"></td>
										<td width="53mm"></td>
									</tr>
								</thead>	
								<tr>
									<td colspan="3" style="vertical-align:bottom;padding:0.5mm;">Подпись лица, получившего доверенность</td>
									<td></td>
									<td></td>
									<td></td>
									<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($agent['m_contragents_p_fio']).'</td>
								</tr>
								<tr>
									<td colspan="3"></td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
								</tr>
								<tr>
									<td colspan="7" style="padding:2mm"></td>
								</tr>
								<tr>
									<td style="vertical-align:bottom;padding:0.5mm;">Руководитель</td>
									<td></td>
									<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$client['m_contragents_c_director_post'].'</td>
									<td></td>
									<td></td>
									<td></td>
									<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($client['m_contragents_c_director_name']).'</td>
								</tr>
								<tr>
									<td></td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
								</tr>
								<tr>
									<td colspan="7" style="padding:2mm"></td>
								</tr>
								<tr>
									<td colspan="3" style="vertical-align:bottom;padding:0.5mm;">Главный (старший) бухгалтер</td>
									<td></td>
									<td></td>
									<td></td>
									<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($client['m_contragents_c_bookkeeper_name']).'</td>
								</tr>
								<tr>
									<td colspan="3"></td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>';	
		
		//печати и подписи
		if($p['doc_signature']){
			$body.=$client['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:55mm;margin-top:-40mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=stamp"/></div>':'';
			$body.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:95mm;margin-top:-35mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
			$body.=$client['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:95mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=bookkeeper"/></div>':'';
		}
		echo $body;
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);

		$mpdf->WriteHTML($body);
		$mpdf->SetHTMLFooter('');
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}	
			
	}
	
	//акт сверки
	public static function act_sverki($p=array(
		'org'=>'',
		'client'=>'',
		'doc_date'=>'',
		'doc_numb'=>'',
		'date_from'=>'',
		'date_to'=>'',
		'pays'=>array(),
		'docs'=>array(),
		'saldo'=>0,
		'doc_signature'=>0
	),$filename){
		global $info,$documents,$buh,$contragents;
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		
		$client['m_contragents_c_director_name']=$client['m_contragents_c_director_name']?$client['m_contragents_c_director_name']:$client['m_contragents_p_fio'];
		
		$p['pays']=explode('|',$p['pays']);
		$p['docs']=explode('|',$p['docs']);
		
		//сортировка платежей и документов по датам
		$items=array();
		if(isset($p['pays']))
			foreach($p['pays'] as $_pay){
				$_pay=$buh->getInfo($_pay);
				$items[dtu(dtu($_pay['m_buh_date'],'Y-m-d'))][]=$_pay;
			}
		if(isset($p['docs']))
			foreach($p['docs'] as $_docs){
				$_docs=$documents->getInfo($_docs);
				$items[dtu(dtu($_docs['m_documents_date'],'Y-m-d'))][]=$_docs;
			}	
		/* foreach($items as $k=>$_item)
			if(sizeof($_item)>1)
				for($i=1;$i<sizeof($_item);$i++){
					$items[$k+1][0]=$_item[$i];
				} */

		ksort($items);
	
		
		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('','',9,'dejavuserifcondensed',15,15,10,10,0,0);//$mpdf=new mPDF('','',10,'dejavuserifcondensed',15,15,40,25,5,10);
		$mpdf->SetAuthor(transform::typography(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2))));
		$mpdf->SetCreator(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2)));
		$mpdf->SetSubject(strcode2utf('Акт сверки'));
		$mpdf->SetTitle(strcode2utf('Акт сверки № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));
		$mpdf->SetKeywords(strcode2utf('Акт сверки № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));		
		$mpdf->defaultCSS['P']['MARGIN']='0.9em 0';
		$mpdf->shrink_tables_to_fit=2;

		$body='';
		//количество не пустых товаров
		$count_all=1;
				
		if($p['doc_bar'])
			$body.='
				<div style="margin-top:2.5mm;float:right;width:35%;text-align:right;">
					<img src="/files/'.substr($filename,0,-10).'bar.png" style="height:24px"/>
				</div>';
		$body.='
			</htmlpageheader>
			<sethtmlpageheader name="spec" value="on" show-this-page="1" />
		';
		//заголовок
		$body.='
			<h1 style="font-size:20;font-weight:bold;text-align:center;padding-bottom:10px;padding-top:5mm;">Акт сверки<br/><span style="font-size:14;font-weight:normal;">взаимных расчетов за период с '.dtu($p['date_from'],'d.m.Y').' по '.dtu($p['date_to'],'d.m.Y').'<br/>между '.$org['m_contragents_c_name_full'].'<br/>и '.$client['m_contragents_c_name_full'].'</span></h1>
			<div style="margin-bottom:3mm;">
				<table width="180mm">
					<tr>
						<td width="30%">г. Иваново</td>
						<td width="40%"></td>
						<td width="30%" style="text-align:right;">'.transform::date_f(dtu($p['doc_date'])).'</td>
					</tr>
				</table>
			</div>';
		
		$body.='<div>Мы, нижеподписавшиеся, ';
		
		//физлицо
		if(in_array(3,explode('|',$client['m_contragents_type'])))
			$body.='
				'.$org['m_contragents_c_name_full'].' с одной стороны, и гражданин РФ '.$client['m_contragents_p_fio'].($client['m_contragents_p_passport_sn']?' (паспорт '.$client['m_contragents_p_passport_sn'].($client['m_contragents_p_passport_v']?' выдан '.$client['m_contragents_p_passport_v']:'').($client['m_contragents_p_passport_date']?' '.dtu($client['m_contragents_p_passport_date'],'d.m.Y'):'').($client['m_contragents_p_birthday']?', дата рождения '.dtu($client['m_contragents_p_birthday'],'d.m.Y'):'').($client['m_contragents_address_j']?', зарегистрирован(а) по адресу '.$client['m_contragents_address_j']:'').')':'').', с другой стороны, составили настоящий акт сверки в том, что состояние взаимных расчетов по данным учета следующее:
			';
		else{
			//ИП
			if(strlen($client['m_contragents_c_inn'])==12){
				$body.=
					$org['m_contragents_c_name_full'].', с одной стороны, и '.$client['m_contragents_c_name_full'].', с другой стороны, составили настоящий акт сверки в том, что состояние взаимных расчетов по данным учета следующее:
				';
			}
			//ФИРМА
			else{
				$body.=
					$org['m_contragents_c_name_full'].', с одной стороны, и '.$client['m_contragents_c_name_full'].', с другой стороны, составили настоящий акт сверки в том, что состояние взаимных расчетов по данным учета следующее:
				';
			}
		}
		
		$body.='
			</div>
			<div>
				<table style="font-size:10;border-spacing:0;border-top:1px solid #bbb;margin-top:2mmm;margin-bottom:1mm;border-right:1px solid #bbb;" width="180mm">
					<thead>
						<tr>
							<td colspan="4" style="padding:1mm 1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">По данным '.$org['m_contragents_c_name_short'].', руб.</td>
							<td colspan="4" style="padding:1mm 1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">По данным '.$client['m_contragents_c_name_short'].', руб.</td>
						</tr>
						<tr>
							<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="3%">№ п/п</td>
							<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="21%">Наименование операции, документы</td>
							<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="3%">Дебет</td>
							<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="3%">Кредит</td>
							
							<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="3%">№ п/п</td>
							<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="21%">Наименование операции, документы</td>
							<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="3%">Дебет</td>
							<td style="background:#eee;font-size:11;padding:1mm 1mm;border-bottom:1px solid #bbb;text-align:center;" width="3%">Кредит</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="2" style="padding:1mm 1mm;border-right:1px solid #bbb;border-left:1px solid #bbb;border-bottom:1px solid #bbb;font-weight:bold;">Сальдо начальное</td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;font-weight:bold;" width="10%"><nobr>'.($p['saldo']>0?transform::price_o($p['saldo']):'').'</nobr></td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:right;font-weight:bold;" width="10%"><nobr>'.($p['saldo']<0?transform::price_o(abs($p['saldo'])):'').'</nobr></td>
							
							<td colspan="2" style="padding:1mm 1mm;border-right:1px solid #bbb;border-left:1px solid #bbb;border-bottom:1px solid #bbb;font-weight:bold;">Сальдо начальное</td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;font-weight:bold;" width="10%"><nobr>'.($p['saldo']<0?transform::price_o(abs($p['saldo'])):'').'</nobr></td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:right;font-weight:bold;" width="10%"><nobr>'.($p['saldo']>0?transform::price_o($p['saldo']):'').'</nobr></td>
						</tr>';
		$i=0;
		$debet=$p['saldo']>0?$p['saldo']:0;
		$kredit=$p['saldo']<0?abs($p['saldo']):0;
		foreach($items as $k=>$_items){
			foreach($_items as $_item){
			//$_item=$_item[0];
			if(isset($_item['m_documents_params'])){
				$sum=json_decode($_item['m_documents_params'])->doc_sum;
				//$debet+=$sum;
			}
			else{
				//$kredit+=$_item['m_buh_sum'];
			}
			$i++;
			
			$_debet=!isset($_item['m_buh_id'])&&$_item['m_documents_performer']==$org['m_contragents_id']?$sum:($_item['m_buh_type']<0?$_item['m_buh_sum']:0);
			$_kredit=isset($_item['m_buh_id'])&&$_item['m_buh_type']>0?$_item['m_buh_sum']:(!isset($_item['m_buh_id'])&&$_item['m_documents_customer']==$org['m_contragents_id']?$sum:0);
			
			$debet+=$_debet;
			$kredit+=$_kredit;
			
			
			$body.='
						<tr>
							<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.$i.'</td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;" width="20%">'.(isset($_item['m_buh_id'])?'Оплата № '.$_item['m_buh_payment_numb'].' от '.dtu($_item['m_buh_date'],'d.m.Y'):'Продажа № '.$_item['m_documents_numb'].' от '.dtu($_item['m_documents_date'],'d.m.Y')).'</td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;" width="10%"><nobr>'.transform::price_o($_debet).'</nobr></td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:right;" width="10%"><nobr>'.transform::price_o($_kredit).'</nobr></td>
							
							<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.$i.'</td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;" width="20%">'.(isset($_item['m_buh_id'])?'Оплата № '.$_item['m_buh_payment_numb'].' от '.dtu($_item['m_buh_date'],'d.m.Y'):'Продажа № '.$_item['m_documents_numb'].' от '.dtu($_item['m_documents_date'],'d.m.Y')).'</td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;" width="10%"><nobr>'.transform::price_o($_kredit).'</nobr></td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:right;" width="10%"><nobr>'.transform::price_o($_debet).'</nobr></td>
						</tr>';
			}
		}
		
		$body.='
						<tr>
							<td colspan="2" style="padding:1mm 1mm;border-right:1px solid #bbb;border-left:1px solid #bbb;border-bottom:1px solid #bbb;font-weight:bold;">Обороты за период</td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;font-weight:bold;" width="10%"><nobr>'.transform::price_o($p['saldo']<0?$debet:$debet-$p['saldo']).'</nobr></td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:right;font-weight:bold;" width="10%"><nobr>'.transform::price_o($p['saldo']>0?$kredit:$kredit-abs($p['saldo'])).'</nobr></td>
							
							<td colspan="2" style="padding:1mm 1mm;border-right:1px solid #bbb;border-left:1px solid #bbb;border-bottom:1px solid #bbb;font-weight:bold;">Обороты за период</td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;font-weight:bold;" width="10%"><nobr>'.transform::price_o($p['saldo']>0?$kredit:$kredit-abs($p['saldo'])).'</nobr></td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:right;font-weight:bold;" width="10%"><nobr>'.transform::price_o($p['saldo']<0?$debet:$debet-$p['saldo']).'</nobr></td>
						</tr>
						<tr>
							<td colspan="2" style="padding:1mm 1mm;border-right:1px solid #bbb;border-left:1px solid #bbb;border-bottom:1px solid #bbb;font-weight:bold;">Сальдо конечное</td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;font-weight:bold;" width="10%"><nobr>'.transform::price_o($debet>$kredit?$debet-$kredit:0).'</nobr></td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:right;font-weight:bold;" width="10%"><nobr>'.transform::price_o($kredit>$debet?$kredit-$debet:0).'</nobr></td>
							
							<td colspan="2" style="padding:1mm 1mm;border-right:1px solid #bbb;border-left:1px solid #bbb;border-bottom:1px solid #bbb;font-weight:bold;">Сальдо конечное</td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;font-weight:bold;" width="10%"><nobr>'.transform::price_o($kredit>$debet?$kredit-$debet:0).'</nobr></td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:right;font-weight:bold;" width="10%"><nobr>'.transform::price_o($debet>$kredit?$debet-$kredit:0).'</nobr></td>
						</tr>
					</tbody>
				</table>
			</div>
		';	
		
		//таблица для подписей и печати
			$body.='<table style="font-size:13;border-spacing:0;margin:10mm 0 2mm;line-height:4.5mm" width="180mm">
						<tr>
							<td width="180mm">
								<table style="font-size:13;border-spacing:0;margin:0;line-height:4.5mm" width="180mm">
									<thead>
										<tr>
											<td width="38mm"></td>
											<td width="7mm"></td>
											<td width="38mm"></td>
											<td width="7mm"></td>
											<td width="38mm"></td>
											<td width="7mm"></td>
											<td width="38mm"></td>
										</tr>
										<tr>
											<td colspan="3" style="padding-bottom:5mm">По данным '.$org['m_contragents_c_name_short'].'<br/><strong>на '.(transform::date_f(dtu($p['date_to'])).' задолженность '.($debet>$kredit?'в пользу '.$org['m_contragents_c_name_short'].' '.transform::price_o(abs($debet-$kredit)).' р. ('.transform::summ_text(abs($debet-$kredit)).')':(($debet<$kredit)?'в пользу '.$client['m_contragents_c_name_short'].' '.transform::price_o(abs($kredit-$debet)).' р. ('.transform::summ_text(abs($kredit-$debet)).')':' отсутствует'))).'.</strong></td>
											<td width="7mm"></td>
											<td colspan="3" style="padding-bottom:5mm">По данным '.$client['m_contragents_c_name_short'].'<br/><strong>на '.(transform::date_f(dtu($p['date_to'])).' задолженность '.($kredit>$debet?'в пользу '.$client['m_contragents_c_name_short'].' '.transform::price_o(abs($debet-$kredit)).' р. ('.transform::summ_text(abs($debet-$kredit)).')':(($kredit<$debet)?'в пользу '.$org['m_contragents_c_name_short'].' '.transform::price_o(abs($kredit-$debet)).' р. ('.transform::summ_text(abs($kredit-$debet)).')':' отсутствует'))).'.</strong></td>
										</tr>									
										<tr>
											<td colspan="3" style="font-weight:bold;padding-bottom:5mm">От '.$org['m_contragents_c_name_short'].'</td>
											<td width="7mm"></td>
											<td colspan="3" style="font-weight:bold;padding-bottom:5mm">От '.$client['m_contragents_c_name_short'].'</td>
										</tr>
									</thead>
									<tr>
										<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$org['m_contragents_c_director_post'].'</td>
										<td></td>
										<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$client['m_contragents_c_director_post'].'</td>
									</tr>
									<tr>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
										<td></td>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
									</tr>
									<tr>
										<td></td>
										<td></td>
										<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($org['m_contragents_c_director_name']).'</td>
										<td></td>
										<td></td>
										<td></td>
										<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($client['m_contragents_c_director_name']).'</td>
									</tr>
									<tr>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
										<td></td>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
										<td></td>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
										<td></td>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
									</tr>
									<tr>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
										<td></td>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';	
			
			//печати и подписи
			if($p['doc_signature']){
				$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:20mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
				$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:35mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
				
				$body.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:115mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
				$body.=$client['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:130mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=stamp"/></div>':'';
			}
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);
		$body=str_replace("<nobr>0,00</nobr>","",$body);
		
		$mpdf->WriteHTML($body);
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}
	}
	
	public static function envelope($p=array(
		'org'=>'',
		'client'=>'',
		'address_from'=>'',
		'address_to'=>''
	),$filename){
		global $info,$documents,$contragents;
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		
		$client['m_contragents_c_director_name']=$client['m_contragents_c_director_name']?$client['m_contragents_c_director_name']:$client['m_contragents_p_fio'];
		
		$client_address=$info->getAddressId($p['address_to'])[0];
		$org_address=$info->getAddressId($p['address_from'])[0];
		
		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('utf-8','C5-L',12,'',0,0,0,0,0,0);
		$mpdf->SetAuthor(transform::typography(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2))));
		$mpdf->SetCreator(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2)));
		$mpdf->SetSubject(strcode2utf('Конверт'));
		$mpdf->SetTitle(strcode2utf('Конверт'));
		$mpdf->SetKeywords(strcode2utf('Конверт'));		
		$mpdf->defaultCSS['P']['MARGIN']='0.9em 0';

		$body='';
		//количество не пустых товаров
		$count_all=1;
				
		//логотип, контакты, футер
		$body.='
		<html>
		<head>
		</head>
			<body>
				<div style="position:absolute;left:10mm;top:65mm;width:240px;height:60px;">
					<img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=logo_big_www" width="240"/>
				</div>
				<div style="position:absolute;left:6mm;top:10mm;width:321px;height:130px;">
					<table style="width:85mm;border-spacing:0;line-height:1.2;font-size:12;">
						<thead>
							<tr>
								<td width="15mm" style="padding:0"></td>
								<td width="35mm" style="padding:0"></td>
								<td width="35mm" style="padding:0"></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="padding:0;font-family:literaturnaya;font-style:italic;height:6.5mm">От кого</td>
								<td colspan="2" style="border-bottom:1px solid #bbb;height:6.5mm">'.$org['m_contragents_c_name_short'].'</td>
							</tr>
							<tr>
								<td style="padding:0;font-family:literaturnaya;font-style:italic;height:6.5mm">Откуда</td>
								<td colspan="2" style="border-bottom:1px solid #bbb;height:6.5mm">'.($org_address['m_address_area']?$org_address['m_address_area']:'').($org_address['m_address_district']?', '.$org_address['m_address_district']:'').'</td>
							</tr>
							<tr>
								<td colspan="3" style="border-bottom:1px solid #bbb;height:6.5mm">'.($org_address['m_address_city']?$org_address['m_address_city']:'').($org_address['m_address_street']?', '.$org_address['m_address_street']:'').($org_address['m_address_house']?', д. '.$org_address['m_address_house']:'').($org_address['m_address_corp']?', корп. '.$org_address['m_address_corp']:'').($org_address['m_address_build']?', стр. '.$org_address['m_address_build']:'').($org_address['m_address_mast']?', вл. '.$org_address['m_address_mast']:'').'</td>
							</tr>
							<tr>
								<td colspan="3" style="border-bottom:1px solid #bbb;height:6.5mm">'.$org_address['m_address_detail'].'</td>
							</tr>
							<tr>
								<td colspan="2"></td>
								<td style="border:1px solid #bbb;border-top:none;height:8mm;text-align:center;"><span style="font-family:literaturnaya;font-style:italic;font-size:8;">Индекс места отправления</span><br/><span style="font-size:13;">'.$org_address['m_address_index'].'</span></td>
							</tr>
						</tbody>
					</table>
				</div>
				<div style="position:absolute;left:6mm;bottom:7mm;width:239px;height:54px;">
					<span style="font-family:pechkin;font-size:12mm">`'.$client_address['m_address_index'].'</span>
				</div>
				<div style="position:absolute;right:10mm;top:12mm;width:9mm;height:9mm;border-top:1px solid #bbb;border-right:1px solid #bbb;padding:3mm;">
					<span style="font-family:literaturnaya;font-size:7mm;text-align:center;">место<br/>для марки</span>
				</div>
				<div style="position:absolute;right:10mm;bottom:15mm;width:377px;height:192px;">
					<table style="width:100mm;border-spacing:0;line-height:1.2;font-size:12;">
						<thead>
							<tr>
								<td width="10mm" style="padding:0"></td>
								<td width="30mm" style="padding:0"></td>
								<td width="60mm" style="padding:0"></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="padding:0;font-family:literaturnaya;font-style:italic;height:6.5mm">Кому</td>
								<td colspan="2" style="border-bottom:1px solid #bbb;height:6.5mm">'.($client_address['m_address_recipient']?$client_address['m_address_recipient']:$client['m_contragents_c_name_short']).'</td>
							</tr>
							<tr>
								<td colspan="3" style="border-bottom:1px solid #bbb;height:6.5mm"></td>
							</tr>
							<tr>
								<td style="padding:0;font-family:literaturnaya;font-style:italic;height:6.5mm">Куда</td>
								<td colspan="2" style="border-bottom:1px solid #bbb;height:6.5mm">'.($client_address['m_address_area']?$client_address['m_address_area'].', ':'').($client_address['m_address_district']?$client_address['m_address_district'].', ':'').'</td>
							</tr>
							<tr>
								<td colspan="3" style="border-bottom:1px solid #bbb;height:6.5mm">'.($client_address['m_address_city']?$client_address['m_address_city'].', ':'').($client_address['m_address_street']?$client_address['m_address_street'].', ':'').($client_address['m_address_house']?'д. '.$client_address['m_address_house'].', ':'').($client_address['m_address_corp']?'корп. '.$client_address['m_address_corp'].', ':'').($client_address['m_address_build']?'стр. '.$client_address['m_address_build'].', ':'').($client_address['m_address_mast']?'вл. '.$client_address['m_address_mast'].',':'').'</td>
							</tr>
							<tr>
								<td colspan="3" style="border-bottom:1px solid #bbb;height:6.5mm">'.$client_address['m_address_detail'].'</td>
							</tr>
							<tr>
								<td colspan="2" style="height:6.5mm;padding:0;font-family:literaturnaya;font-style:italic;font-size:8;text-align:center;vertical-align:bottom;border-bottom:1px solid #bbb;">Индекс места назначения</td>
								<td style="border-bottom:1px solid #bbb;height:6.5mm"></td>
							</tr>
							<tr>
								<td colspan="2" style="border:1px solid #bbb;border-top:none;height:8mm;text-align:center;font-size:13;">'.$client_address['m_address_index'].'</td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>
		</body>
		</html>';
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);

		//echo $body;exit;
		$mpdf->WriteHTML($body);
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=$_SERVER['DOCUMENT_ROOT'].'/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}
	}
	
	
	//договор подряда на работы
	public static function contract_work($p=array(
		'org'=>'',
		'client'=>'',
		'order'=>'',
		'doc_date'=>'',
		'doc_numb'=>'',
		'doc_logo'=>null,
		'doc_bar'=>null,
		'doc_signature'=>null,
		'doc_header_org_name'=>null,
		'doc_header_org_address'=>null,
		'doc_header_org_tel'=>null,
		'doc_header_org_email'=>null,		
		'm_documents_doc_method_pay_cash'=>null,
		'm_documents_doc_method_pay_bank'=>null,
		'm_documents_doc_sum_pre'=>null,
		'm_documents_doc_sum_phase'=>null,
		'm_documents_doc_sum_end'=>null,
		'm_documents_doc_guarantee'=>null
	),$filename){
		global $info,$contragents,$orders;
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		
		$client['m_contragents_c_director_name']=$client['m_contragents_c_director_name']?$client['m_contragents_c_director_name']:$client['m_contragents_p_fio'];
		
		$client['order_address']=$orders->orders_id[$p['order']][0]['m_orders_address_full'];
		$client['order_address']=$orders->orders_id[$p['order']][0]['m_orders_address_full'];
		
		$client_address=$info->getAddress($p['client']);
		$client_address=change_key($client_address,'m_address_type',true);
		
		$org_address=$info->getAddress($p['org']);
		$org_address=change_key($org_address,'m_address_type',true);
		
		$p['nds']=$orders->orders_id[$p['order']][0]['m_orders_nds'];
		
		$org['tel']=$info->getTel($p['org'])[0]['m_contragents_tel_numb'];
		$client['tel']=$info->getTel($p['client'])[0]['m_contragents_tel_numb'];
		

		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('','',9,'dejavuserifcondensed',15,15,20,25,10,10);//$mpdf=new mPDF('','',10,'dejavuserifcondensed',15,15,40,25,5,10);
		$mpdf->SetAuthor(transform::typography('vseumelec.ru'));
		$mpdf->SetCreator('vseumelec.ru');
		$mpdf->SetSubject(strcode2utf('Договор подряда'));
		$mpdf->SetTitle(strcode2utf('Договор подряда № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));
		$mpdf->SetKeywords(strcode2utf('Договор подряда № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));		
		$mpdf->defaultCSS['P']['MARGIN']='0.9em 0';
		$mpdf->shrink_tables_to_fit=2;

		$body='';
		//количество не пустых товаров
		$count_all=1;
		
		//номер договора в хедере
		$header_numb='
			<div style="margin-top:2.5mm;font-size:10px;color:#656565;float:left;width:60%;">
				Договор подряда № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'
			</div>
		';
		if($p['doc_bar'])
			$header_numb.='
			<div style="margin-top:2.5mm;float:right;width:35%;text-align:right;">
				<img src="/files/'.substr($filename,0,-30).'bar.png" style="height:24px"/>
			</div>';
		$mpdf->SetHTMLHeader($header_numb);
		
		//логотип, контакты, футер
		$header='
			<div style="width:100%;padding-bottom:5mm;border-bottom:1px solid #bbb;">
				<div style="float:left;color:#333;margin-right:30px;width:30%;">';
		$header.=($p['doc_logo'])?'<img src="/img/'.$p['doc_logo'].'" width="120"/>':'';
		$header.='
				</div>
				<div style="width:45%;float:right;font-size:13;">
					<p style="margin:0 0 3px 0;padding:0;font-size:14;font-weight:bold;">'.transform::typography($p['doc_header_org_name']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_address']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$p['doc_header_org_email'].'">'.transform::typography($p['doc_header_org_email']).'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		
		
		
		$body.=$header;//$mpdf->SetHTMLHeader($header);
	
		$footer='<div style="float:left;width:10%;">
					<div style="'.($p['doc_header_bar']?'':'margin-top:2.4mm;').'font-size:12;color:#333333;">
						{PAGENO}
					</div>
				</div>
				<div style="width:90%;text-align:right">
					<table width="160mm" style="margin-top:2.5mm;">
						<thead>
							<tr>
								<td width="35mm"></td>
								<td width="2.5mm"></td>
								<td width="30mm"></td>
								<td width="20mm"></td>
								<td width="35mm"></td>
								<td width="2.5mm"></td>
								<td width="30mm"></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="border-bottom:1px solid #bbb;"></td>
								<td></td>
								<td>/ '.transform::fio($client['m_contragents_c_director_name']).' /</td>
								<td></td>
								<td style="border-bottom:1px solid #bbb;"></td>
								<td></td>
								<td>/ '.transform::fio($org['m_contragents_c_director_name']).' /</td>
							</tr>
							<tr>
								<td style="text-align:center;font-size:10;color:#656565;">Заказчик</td>
								<td></td>
								<td></td>
								<td></td>
								<td style="text-align:center;font-size:10;color:#656565;">Подрядчик</td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>';
		if($p['doc_signature']){
			$footer.='<div>';
			$footer.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;margin-left:20mm;margin-top:-16mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
			$footer.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;margin-left:105mm;margin-top:-16mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			$footer.='</div>';
		}
		$mpdf->SetHTMLFooter($footer);
				
		//заголовок
		$body.='
			<h1 style="font-size:20;font-weight:400;text-align:center;padding-bottom:10px;padding-top:5mm;">Договор подряда № '.$p['doc_numb'].'<br/><span style="color:#999;font-size:12">на выполнение строительных и ремонтно-отделочных работ</span></h1>
			<div>
				<table width="180mm">
					<tr>
						<td width="30%">г. Иваново</td>
						<td width="40%"></td>
						<td width="30%" style="text-align:right;">'.transform::date_f(dtu($p['doc_date'])).'</td>
					</tr>
				</table>
			</div>
		';
		
		//тело документа
		//физлицо
		if(in_array(3,explode('|',$client['m_contragents_type'])))
			$body.='
				<bookmark content="Преамбула" level="0"></bookmark>
				<p>Гражданин РФ <strong>'.$client['m_contragents_p_fio'].'</strong>'.($client['m_contragents_p_passport_sn']?' (паспорт '.$client['m_contragents_p_passport_sn'].($client['m_contragents_p_passport_v']?' выдан '.$client['m_contragents_p_passport_v']:'').($client['m_contragents_p_passport_date']?' '.dtu($client['m_contragents_p_passport_date'],'d.m.Y'):'').($client['m_contragents_p_birthday']?', дата рождения '.dtu($client['m_contragents_p_birthday'],'d.m.Y'):'').($client['m_contragents_address_j']?', зарегистрирован(а) по адресу '.$client['m_contragents_address_j']:'').')':'').', именуемый(ая) в дальнейшем &laquo;Заказчик&raquo;, с одной стороны, и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Подрядчик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, заключили настоящий договор подряда (далее по тексту &ndash; &laquo;Договор&raquo;) о нижеследующем:</p>
			';		
		else{
			if(strlen($client['m_contragents_c_inn'])==12){
				$body.='
					<bookmark content="Преамбула" level="0"></bookmark>
					<p><strong>'.$client['m_contragents_c_name_full'].'</strong>, именуемый(ая) в дальнейшем &laquo;Заказчик&raquo;, с одной стороны, и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Подрядчик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, заключили настоящий договор подряда (далее по тексту &ndash; &laquo;Договор&raquo;) о нижеследующем:</p>
				';
			}
			else{
				$body.='
					<bookmark content="Преамбула" level="0"></bookmark>
					<p><strong>'.$client['m_contragents_c_name_full'].'</strong>, именуемое в дальнейшем &laquo;Заказчик&raquo;, в лице '.$client['m_contragents_c_director_post'].' '.$client['m_contragents_c_director_name_rp'].', действующего на основании '.$client['m_contragents_c_director_base'].', с одной стороны, и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Подрядчик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, заключили настоящий договор подряда (далее по тексту &ndash; &laquo;Договор&raquo;) о нижеследующем:</p>
				';
			}
		}
		$body.='
			<bookmark content="1. Предмет договора" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">1. Предмет договора</h2>
			<p>1.1. По настоящему Договору Подрядчик обязуется по заданию Заказчика в установленный Договором срок выполнить работы, указанные в п. 1.2 Договора (далее по тексту &ndash; &laquo;Работы&raquo;), в помещении заказчика, находящегося по адресу: <strong>'.$client['order_address'].'</strong> (далее по тексту &ndash; &laquo;Объект&raquo;), а Заказчик обязуется принять Работы и оплатить обусловленную Договором цену.';
		$body.=in_array(3,explode('|',$client['m_contragents_type']))?' Работы, выполняемые Подрядчиком, предназначены удовлетворять бытовые или другие личные потребности Заказчика.':'';
		$body.='</p>
			<p>1.2. Содержание, объем, стоимость выполнения Работ указаны в Смете работ (Приложение № 1 к Договору), являющейся неотъемлемой частью Договора.</p>
			<p>1.3. Этапы и сроки выполнения работ указаны в Календарном плане работ (Приложение № 2 к Договору), являющемся неотъемлемой частью Договора.</p>
			<p>1.4. Подрядчик самостоятельно определяет способы выполнения задания Заказчика, и выполняет задание своими силами, инструментами, механизмами с использованием материалов Заказчика.</p>
			<bookmark content="2. Срок действия договора" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">2. Срок действия договора</h2>
			<p>2.1. Договор вступает в силу с момента подписания и действует до окончания выполнения Работ Подрядчиком. Датой окончания Работ считается дата подписания Сторонами окончательного Акта сдачи-приёмки выполненных работ.</p>
			<bookmark content="3. Права и обязанности сторон" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">3. Права и обязанности сторон</h2>
			<bookmark content="3.1. Обязанности Подрядчика" level="1"></bookmark>
			<p>3.1. Подрядчик обязуется:</p>
			<p style="padding-left: 30px;">3.1.1. Выполнить все Работы в соответствии с условиями Договора в объёме и в сроки, предусмотренные Договором и сдать Работы Заказчику в состоянии, позволяющем нормальную эксплуатацию результатов Работ. Проводить работы в соответствии с требованиями действующих строительных норм и правил (СНиП), правил пожарной безопасности, техники безопасности и санитарных норм.</p>
			<p style="padding-left: 30px;">3.1.2. При непосредственном выполнении Работ в отсутствие на Объекте Заказчика не допускать нахождение на Объекте посторонних лиц.</p>
			<p style="padding-left: 30px;">3.1.3. В течение 2 (двух) рабочих дней со дня получения письменного запроса Заказчика давать Заказчику письменные объяснения о выполнении Работ, а также незамедлительно давать устные объяснения Заказчику о выполнении Работ.</p>
			<p style="padding-left: 30px;">3.1.4. Немедленно предупредить Заказчика и до получения его указаний приостановить Работы при обнаружении:</p>
			<div style="margin:0;padding:0;padding-left:60px;">
				<ul style="margin:0;padding:0;">
					<li>непригодности или недоброкачественности предоставленного Заказчиком материала</li>
					<li>возможных неблагоприятных для Заказчика последствий выполнения его указаний о способе выполнения Работ</li>
					<li>отрицательного результата или нецелесообразности дальнейшего проведения Работ</li>
					<li>иных, не зависящих от Подрядчика обстоятельств, которые грозят годности или прочности результатов выполняемых Работ либо создают невозможность их завершения в срок</li>
				</ul>
			</div>
			<p style="padding-left: 30px;">Вопрос о целесообразности продолжения Работ решается Сторонами в течение 2 (двух) рабочих дней со дня получения Заказчиком уведомления о приостановлении Работ.</p>
			<p style="padding-left: 30px;">3.1.5. Своевременно устранять недостатки и дефекты, выявленные при приёмке Работ.</p>
			<p style="padding-left: 30px;">3.1.6. Сообщать Заказчику о необходимости проведения дополнительных, не указанных в Смете работ, которые могут увеличить цену Договора, если без этих работ невозможно успешное выполнение Работ по Договору.</p>
			<bookmark content="3.2. Обязанности Заказчика" level="1"></bookmark>
			<p>3.2. Заказчик обязуется:</p>
			<p style="padding-left: 30px;">3.2.1. Обеспечить строительную готовность Объекта для производства Подрядчиком порученных ему по Договору Работ не позднее дня начала Работ.</p>
			<p style="padding-left: 30px;">3.2.2. Обеспечить беспрепятственный доступ работников Подрядчика на Объект Заказчика для выполнения Работ.</p>
			<p style="padding-left: 30px;">3.2.3. Предоставить Подрядчику в соответствии с условиями Договора необходимые материалы для проведения Работ, а также обеспечить сохранность этих материалов во время отсутствия работников Подрядчика на Объекте. Предоставление материалов Заказчиком Подрядчику производится в полном объёме перед началом выполнения Работ, либо поэтапно, в объёме, необходимом для выполнения очередного этапа Работ за 2 (два) рабочих дня до начала выполнения этого этапа согласно Календарному плану работ.</p>
			<p style="padding-left: 30px;">3.2.4. Осмотреть и принять выполненные Работы (результат Работ) в сроки и в порядке, которые предусмотрены Договором.</p>
			<p style="padding-left: 30px;">3.2.5. При обнаружении отступлений от Договора, ухудшающих результат Работ, или иных недостатков в Работах немедленно заявить об этом Подрядчику.</p>
			<p style="padding-left: 30px;">3.2.6. Оплатить выполненные Работы на условиях и в порядке, установленных Договором.</p>
			<p style="padding-left: 30px;">3.2.7. Не вмешиваться в оперативную деятельность подрядчика и не препятствовать своими действиями проведению работ.</p>
			<bookmark content="3.3. Права Подрядчика" level="1"></bookmark>
			<p>3.3. Подрядчик вправе:</p>
			<p style="padding-left: 30px;">3.3.1. Не приступать к Работам, а начатые Работы приостановить или отказаться от исполнения Договора и потребовать возмещения убытков в случаях, когда нарушение Заказчиком своих обязанностей по Договору препятствует исполнению Договора Подрядчиком, а также при наличии обстоятельств, очевидно свидетельствующих о том, что исполнение Заказчиком указанных обязанностей не будет произведено в установленный срок.</p>
			<p style="padding-left: 30px;">3.3.2. Требовать оплаты выполненных им Работ, в том числе в случае, если результат Работ не был достигнут либо достигнутый результат оказался с недостатками, которые делают его не пригодным для предусмотренного в Договоре использования, по причинам, вызванным недостатками предоставленного Заказчиком материала.&nbsp;</p>
			<p style="padding-left: 30px;">3.3.3. Приостановить выполнение Работ в случае невыполнения Заказчиком своих обязательств по оплате установленных Договором сумм, причитающихся Подрядчику в связи с выполнением Работ по Договору.</p>
			<p style="padding-left: 30px;">3.3.4. При необходимости привлекать к выполнению Работ сторонние организации в качестве субподрядчиков, при этом Подрядчик несет полную ответственность за деятельность этих организаций на Объекте.</p>
			<bookmark content="3.4. Права Заказчика" level="1"></bookmark>
			<p>3.4. Заказчик вправе:</p>
			<p style="padding-left: 30px;">3.4.1. В любое время проверять ход и качество Работ, не вмешиваясь в деятельность Подрядчика.</p>
			<p style="padding-left: 30px;">3.4.2. По своему выбору в случаях, когда Работы выполнены Подрядчиком с отступлениями от Договора, ухудшившими результат Работ, или с иными недостатками, которые делают его не пригодным для предусмотренного в Договоре использования, а также в случаях обнаружения недостатков во время приемки результата Работ:</p>
			<div style="margin:0;padding:0;padding-left:60px;">
				<ul style="margin:0;padding:0;">
					<li style="padding-left: 30px;">потребовать от Подрядчика замены результата Работ ненадлежащего качества (повторного выполнения Работ);</li>
					<li style="padding-left: 30px;">потребовать от Подрядчика безвозмездного устранения недостатков в разумный срок;</li>
					<li style="padding-left: 30px;">потребовать от Подрядчика соразмерного уменьшения установленной за Работы цены</li>
				</ul>
			</div>
			<p style="padding-left: 30px;">3.4.3. Если отступления в Работах от условий Договора или иные недостатки результата Работ в установленный Заказчиком разумный срок не были устранены либо являются существенными и неустранимыми, отказаться от исполнения Договора и потребовать возмещения причиненных убытков.</p>
			<p style="padding-left: 30px;">3.4.4. Отказаться от уплаты работ, не предусмотренных Договором или дополнительными соглашениями к Договору.</p>
			<p style="padding-left: 30px;">3.4.5. В любое время до сдачи Работ отказаться от исполнения условий Договора, уплатив Подрядчику стоимость Работ, выполненных до уведомления об отказе от исполнения Договора и возместив Подрядчику расходы, произведенные до этого момента в целях исполнения Договора.</p>
			<p>3.5. Материалы, необходимые для выполнения Работ, предоставляются Заказчиком. Заказчик несёт ответственность за ненадлежащее качество предоставленных им материалов, сроки их предоставления, а также за предоставление материалов, обремененных правами третьих лиц.</p>
			<bookmark content="4. Сроки выполнения работ" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">4. Сроки выполнения работ</h2>
			<p>4.1. Срок выполнения Работ определяется поэтапно в Календарном плане работ (Приложение № 2 к Договору), являющемся неотъемлемой частью Договора.</p>
			<p>4.2. Сроки начала и окончания Работ по Договору переносятся Подрядчиком в одностороннем порядке на период просрочки исполнения Заказчиком встречных обязательств, предусмотренных пунктами 3.2.1 &ndash; 3.2.3 и 3.2.6 Договора.</p>
			<bookmark content="5. Стоимость работ и порядок расчётов" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">5. Стоимость работ и порядок расчётов</h2>
			<p>5.1. Стоимость Работ по Договору определяется Сметой (Приложение № 1 к Договору), являющейся неотъемлемой частью Договора.</p>
			<p>5.2.';
		//сумма предоплаты
		if($p['m_documents_doc_sum_pre'])
			$body.=' В течение 3 (трёх) рабочих дней с момента заключения настоящего Договора Заказчик вносит предоплату Подрядчику в размере '.$p['m_documents_doc_sum_pre'].' ('.transform::summ_text($p['m_documents_doc_sum_pre'],true,false).')';
		if($p['nds']==18)
			$body.=', в том числе НДС '.$orders->orders_id[$p['order']][0]['m_orders_nds'].'% — '.($p['m_documents_doc_sum_pre']*($orders->orders_id[$p['order']][0]['m_orders_nds']/100)/(1+$orders->orders_id[$p['order']][0]['m_orders_nds']/100)).' ('.transform::summ_text($p['m_documents_doc_sum_pre']*.20/1.20,true,false).')';
		$body.='.';
		//кол-во дней для оплаты после каждого этапа
		if($p['m_documents_doc_sum_phase'])
			$body.=' Поэтапная оплата за выполненные работы производится Заказчиком в течение '.$p['m_documents_doc_sum_phase'].' ('.transform::summ_text($p['m_documents_doc_sum_phase'],false,false,true).') рабочих дней с момента подписания Акта сдачи-приёмки этапа Работ.';
		//кол-во дней после сдачи для окончательной оплаты
		if($p['m_documents_doc_sum_end'])
			$body.=' Оплата за выполненные работы (окончательный расчёт) производится Заказчиком по окончанию работ в течение '.$p['m_documents_doc_sum_end'].' ('.transform::summ_text($p['m_documents_doc_sum_end'],false,false,true).') календарных дней после подписания Акта выполненных работ по последнему этапу'.($p['m_documents_doc_sum_pre']?', за вычетом предоплаты':'').'.';
		if($p['nds']==18)
			$body.='
			<p>5.3. Стоимость работ по настоящему Договору включает в себя НДС '.$orders->orders_id[$p['order']][0]['m_orders_nds'].'%.</p>';
		$body.='
			<p>5.4. Способ оплаты Работ по Договору:</p>
			<div style="margin:0;padding:0;padding-left:60px;">
				<ul style="margin:0;padding:0;">
					'.($p['m_documents_doc_method_pay_cash']?'<li>передача Заказчиком наличных денежных средств Подрядчику;</li>':'').'
					'.($p['m_documents_doc_method_pay_bank']?'<li>перечисление денежных средств Заказчиком на расчётный счет Подрядчика (безналичный расчёт);</li>':'').'
				</ul>
			</div>
			<bookmark content="6. Порядок сдачи и приёмки работ" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">6. Порядок сдачи и приёмки работ</h2>
			<p>6.1. Приемка Работ подтверждается подписанием Сторонами Акта сдачи - приёмки выполненных работ, который оформляется в следующем порядке:</p>
			<p style="padding-left: 30px;">6.1.1. Подрядчик по завершении выполнения Работ в сроки, установленные п. 4.1. Договора, передает Заказчику заказным почтовым отправлением либо лично в руки: Акт сдачи-приёмки выполненных работ в двух экземплярах и счет на оплату Работ.</p>
			<p style="padding-left: 30px;">6.1.2. Заказчик обязан в течение 2 (двух) рабочих дней со дня получения документов, указанных в п. 6.1.1. Договора, с участием Подрядчика осмотреть и принять выполненные Работы (результат Работ), подписать и вернуть Подрядчику 1 (один) экземпляр Акта сдачи-приёмки выполненных работ или направить Подрядчику мотивированный отказ от приема этапа Работ путем передачи его по почте либо лично в руки. По истечении указанного срока, при отсутствии мотивированного отказа Заказчика, Работы считаются принятыми Заказчиком и подлежащими оплате на основании одностороннего Акта сдачи-приёмки выполненных работ, составленного Подрядчиком.</p>
			<p style="padding-left: 30px;">6.1.3. В случае отказа Заказчика от приемки Работ (этапа Работ) Сторонами в течение 3 (трёх) рабочих дней со дня получения Подрядчиком мотивированного отказа составляется двусторонний акт с перечнем необходимых доработок и сроков их выполнения.</p>
			<p style="padding-left: 30px;">6.1.4. Заказчик, принявший Работы без проверки, лишается права ссылаться на недостатки Работ, которые могли быть установлены при обычном способе их приемки (явные недостатки).</p>
			<p style="padding-left: 30px;">6.1.5. Заказчик, обнаружив после приемки Работ отступления в них от условий Договора или иные недостатки, которые не могли быть установлены им при обычном способе приемки (скрытые недостатки), обязан известить об этом Подрядчика в течение 3 (трёх) рабочих дней со дня их обнаружения.</p>
			<p style="padding-left: 30px;">6.1.6. При досрочном прекращении Работ по Договору, в случае, предусмотренном п. 3.4.5. Договора, Заказчик обязан принять выполненные Работы по степени их готовности на дату прекращения Работ и оплатить их стоимость (за вычетом предоплаты).</p>
			<p style="padding-left: 30px;">6.1.7. При досрочном выполнении Подрядчиком Работ Заказчик обязан принять и оплатить эти Работы на условиях Договора.</p>
			<bookmark content="7. Гарантия качества работ" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">7. Гарантия качества работ</h2>
			<p>7.1. Гарантия качества распространяется на все Работы, выполненные Подрядчиком по Договору.</p>
			<p>7.2. Гарантийный срок на результат выполненных Работ при нормальной эксплуатации устанавливается равным <strong>'.$p['m_documents_doc_guarantee'].' ('.transform::summ_text($p['m_documents_doc_guarantee'],false,false).') месяцев</strong> с даты подписания Сторонами Акта сдачи-приёмки выполненных Работ, соответствующего последнему этапу Работ в Календарном плане работ.</p>
			<p>7.3. Подрядчик несет ответственность за недостатки, обнаруженные в пределах гарантийного срока, кроме недостатков, которые произошли вследствие неправильной эксплуатации, нормального износа, усадки, или ненадлежащего ремонта объекта Работ (или его частей), произведенного самим Заказчиком или привлеченными им третьими лицами, и обязан устранить их за свой счёт в течение 20 (двадцати) рабочих дней с момента получения письменного извещения Заказчика. Гарантийный срок в этом случае продлевается на период устранения дефектов.</p>
			<bookmark content="8. Ответственность сторон" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">8. Ответственность сторон</h2>
			<p>8.1. Стороны несут ответственность за неисполнение или ненадлежащее исполнение своих обязательств по Договору в соответствии с Договором и законодательством России.</p>
			<p>8.2. Неустойка по Договору выплачивается только на основании обоснованного письменного требования Сторон.</p>
			<p>8.3. Выплата неустойки не освобождает Стороны от выполнения обязанностей, предусмотренных Договором.</p>
			<p>8.4. За ущерб, причиненный третьему лицу в процессе выполнения Работ по Договору, отвечает Подрядчик, если не докажет, что ущерб был причинен вследствие обстоятельств, за которые отвечает Заказчик.</p>
			<p>8.6. Ответственность Подрядчика:</p>
			<p style="padding-left: 30px;">8.6.1. В случае несвоевременного выполнения всех Работ Подрядчик обязуется выплатить Заказчику пени в размере 0.1% (одной десятой процента) в день от стоимости невыполненных Работ по Договору за каждый день просрочки, но не более 20% (двадцати процентов) стоимости невыполненных Работ по Договору.</p>
			<p style="padding-left: 30px;">8.6.2. Подрядчик несет ответственность за несохранность предоставленных Заказчиком материалов, оказавшихся во владении Подрядчика в связи с исполнением Договора в время непосредственного исполнения Работ, в размере стоимости указанных материалов, указанной в Приложении № 3 к Договору.</p>
			<p style="padding-left: 30px;">8.6.3. В случае нарушения Подрядчиком сроков выполнения Работ, Заказчик по своему выбору вправе:</p>
			<div style="margin:0;padding:0;padding-left:60px;">
				<ul style="margin:0;padding:0;">
					<li style="padding-left: 30px;">назначить Подрядчику новый срок;</li>
					<li style="padding-left: 30px;">потребовать уменьшения цены за выполнение Работ;</li>
					<li style="padding-left: 30px;">отказаться от исполнения Договора;</li>
				</ul>
			</div>
			<p>8.6.4. Подрядчик не несет ответственности за невыполнение обязательств по Договору, если оно вызвано действием или бездействием Заказчика, повлекшим невыполнение им собственных обязательств по Договору перед Подрядчиком.</p>
			<p>8.7. Ответственность Заказчика:</p>
			<p style="padding-left: 30px;">8.7.1. В случае несвоевременной оплаты Работ Подрядчику в соответствии с условиями Договора Заказчик обязуется выплатить Подрядчику пени в размере 0.1% (одной десятой процента) в день от стоимости несвоевременно оплаченных Работ за каждый день просрочки, но не более 20% (двадцати процентов) стоимости неоплаченных Работ по Договору.</p>
			<p style="padding-left: 30px;">8.7.2. В случае несвоевременного исполнения (ненадлежащего исполнения) Заказчиком условий предоставления материала, предусмотренных п. 3.2.3. Договора, Заказчик обязуется выплатить Подрядчику пени в размере 1% (одного процента) в день от стоимости этапа Работ по Договору за каждый день просрочки, но не более 20% (двадцати процентов) стоимости этапа Работ по Договору. При этом Подрядчик вправе приостановить выполнение Работ вплоть до исполнения Заказчиком указанных обязанностей по Договору.</p>
			<p style="padding-left: 30px;">8.7.3. Заказчик несёт ответственность за ненадлежащее качество предоставленных им материалов, а также за предоставление материалов, обременённых правами третьих лиц.</p>
			<bookmark content="9. Основания и порядок расторжения договора" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">9. Основания и порядок расторжения договора</h2>
			<p>9.1. Договор может быть расторгнут по соглашению Сторон, а также в одностороннем порядке по письменному требованию одной из Сторон по основаниям, предусмотренным Договором и законодательством.</p>
			<p>9.2. Расторжение Договора в одностороннем порядке производится только по письменному требованию Сторон в течение 3 (трёх) календарных дней со дня получения Стороной такого требования.</p>
			<p>9.3. Подрядчик вправе расторгнуть Договор в одностороннем порядке в случаях:</p>
			<p style="padding-left: 30px;">9.3.1. Предусмотренными п. 3.3.1. &ndash; 3.3.3. Договора.</p>
			<p style="padding-left: 30px;">9.3.2. Увеличения стоимости Работ или необходимости проведения дополнительных работ и отказа Заказчика от заключения дополнительного соглашения об увеличении стоимости Работ.</p>
			<p style="padding-left: 30px;">9.3.3. Задержки Заказчиком оплаты выполненных Работ более чем на 5 (пять) банковских дней.</p>
			<p style="padding-left: 30px;">9.3.4. Нарушения Заказчиком своих обязанностей, предусмотренных п. 3.2.1. &ndash; 3.2.7.</p>
			<p>9.4. Заказчик вправе расторгнуть Договор в одностороннем порядке в случаях:</p>
			<p style="padding-left: 30px;">9.4.1. Предусмотренными п. 3.4.3 Договора.</p>
			<p style="padding-left: 30px;">9.4.2. В любое время до сдачи Работ, уплатив Подрядчику стоимость Работ, выполненных до уведомления об отказе от исполнения Договора и возместив Подрядчику расходы, произведенные до этого момента в целях исполнения Договора.</p>
			<bookmark content="10. Разрешение споров между сторонами" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">10. Разрешение споров между сторонами</h2>
			<p>10.1. Претензионный порядок досудебного урегулирования споров из Договора является для Сторон обязательным.</p>
			<p>10.2. Претензионные письма направляются Сторонами заказным почтовым отправлением с уведомлением о вручении последнего адресату по местонахождению Сторон, указанным в пункте 14 Договора.</p>
			<p>10.3. Направление Сторонами претензионных писем иным способом, чем указано в<br />п. 10.2 Договора не допускается.</p>
			<p>10.4. Срок рассмотрения претензионного письма составляет 10 (десять) календарных дней со дня получения последнего адресатом.</p>
			<p>10.5. Споры из Договора разрешаются в судебном порядке в соответствии с законодательством.</p>
			<bookmark content="11. Форс-мажор" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">11. Форс-мажор</h2>
			<p>11.1. Стороны освобождаются от ответственности за полное или частичное неисполнение обязательств по Договору в случае, если неисполнение обязательств явилось следствием действий непреодолимой силы, а именно: пожара, наводнения, землетрясения, забастовки, войны, действий органов государственной власти или других независящих от Сторон обстоятельств.</p>
			<p>11.2. Сторона, которая не может выполнить обязательства по Договору, должна своевременно, но не позднее 3 (трёх) календарных дней после наступления обстоятельств непреодолимой силы, письменно известить другую Сторону, с предоставлением обосновывающих документов, выданных компетентными органами.</p>
			<p>11.3. Стороны признают, что неплатежеспособность Сторон не является форс-мажорным обстоятельством.</p>
			<bookmark content="12. Прочие условия" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">12. Прочие условия</h2>
			<p>12.1. Стороны не имеют никаких сопутствующих устных договоренностей. Содержание текста Договора полностью соответствует действительному волеизъявлению Сторон.</p>
			<p>12.2. Любая договоренность между Сторонами, влекущая за собой&nbsp;новые обстоятельства, не предусмотренные Договором, считается действительной, если она подтверждена Сторонами в письменной форме в виде дополнительного соглашения.</p>
			<p>12.3. Договор составлен в 2 (двух) подлинных экземплярах на русском языке по одному для каждой из Сторон.</p>
			<bookmark content="13. Список приложений к договору" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">13. Список приложений к договору</h2>
			<p>13.1. Приложение № 1 &mdash; Смета работ.</p>
			<p>13.2. Приложение № 2 &mdash; Календарный план работ.</p>
			<bookmark content="14. Адреса, реквизиты  и подписи сторон" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">14. Адреса, реквизиты  и подписи сторон</h2>
			<table style="font-size:13;border-spacing:0;border-top:1px solid #bbb;margin-top:15mm;margin-bottom:6mm;border-right:1px solid #bbb;" width="180mm">
				<thead>
					<tr>
						<td style="font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;text-align:center;" width="30mm"></td>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;text-align:center;" width="80mm">Заказчик</td>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;text-align:center;" width="80mm">Подрядчик</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Наименование</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_name_full'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_name_full'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Юридический адрес</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_address_j'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_address_j'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Почтовый адрес</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_address_p'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_address_p'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Фактический адрес</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_address_p'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_address_f'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Телефон</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['tel'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['tel'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">E-mail</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_email'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_email'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">ИНН</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_inn'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_inn'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">КПП</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_kpp'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_kpp'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">ОГРН</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_ogrn'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_ogrn'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Банк</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_bank_name'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_bank_name'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">БИК</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_bank_bik'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_bank_bik'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Расч. сч.</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_bank_rs'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_bank_rs'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Корр. сч.</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_bank_ks'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_bank_ks'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Должность подписанта</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_director_post'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_director_post'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Ф.И.О. подписанта</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_director_name'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_director_name'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Подпись</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;"></td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;"></td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Место печати</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;"></td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;"></td>
					</tr>
				</tbody>
			</table>
		';

		//печати и подписи
		if($p['doc_signature']){
			$body.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:50mm;margin-top:-23mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
			$body.=$client['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:45mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=stamp"/></div>':'';
			$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:125mm;margin-top:-23mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:140mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
		}
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);

		$mpdf->WriteHTML($body);
		$mpdf->SetHTMLFooter('');
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}
	}
	
	//договор подряда на работы С МАТЕРИАЛАМИ
	public static function contract_work_m($p=array(
		'org'=>'',
		'client'=>'',
		'order'=>'',
		'doc_date'=>'',
		'doc_numb'=>'',
		'doc_logo'=>null,
		'doc_bar'=>null,
		'doc_signature'=>null,
		'doc_header_org_name'=>null,
		'doc_header_org_address'=>null,
		'doc_header_org_tel'=>null,
		'doc_header_org_email'=>null,		
		'm_documents_doc_method_pay_cash'=>null,
		'm_documents_doc_method_pay_bank'=>null,
		'm_documents_doc_sum_pre'=>null,
		'm_documents_doc_sum_pre_m'=>null,
		'm_documents_doc_sum_phase'=>null,
		'm_documents_doc_sum_end'=>null,
		'm_documents_doc_guarantee'=>null
	),$filename){
		global $info,$contragents,$orders;
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		
		$client['m_contragents_c_director_name']=$client['m_contragents_c_director_name']?$client['m_contragents_c_director_name']:$client['m_contragents_p_fio'];
		
		$client['order_address']=$orders->orders_id[$p['order']][0]['m_orders_address_full'];
		$client['order_address']=$orders->orders_id[$p['order']][0]['m_orders_address_full'];
		
		$p['nds']=$orders->orders_id[$p['order']][0]['m_orders_nds'];
		
		$org['tel']=$info->getTel($p['org'])[0]['m_contragents_tel_numb'];
		$client['tel']=$info->getTel($p['client'])[0]['m_contragents_tel_numb'];
		

		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('','',9,'dejavuserifcondensed',15,15,20,25,10,10);//$mpdf=new mPDF('','',10,'dejavuserifcondensed',15,15,40,25,5,10);
		$mpdf->SetAuthor(transform::typography('vseumelec.ru'));
		$mpdf->SetCreator('vseumelec.ru');
		$mpdf->SetSubject(strcode2utf('Договор подряда'));
		$mpdf->SetTitle(strcode2utf('Договор подряда № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));
		$mpdf->SetKeywords(strcode2utf('Договор подряда № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));		
		$mpdf->defaultCSS['P']['MARGIN']='0.9em 0';
		$mpdf->shrink_tables_to_fit=2;

		$body='';
		//количество не пустых товаров
		$count_all=1;
		
		//номер договора в хедере
		$header_numb='
			<div style="margin-top:2.5mm;font-size:10px;color:#656565;float:left;width:60%;">
				Договор подряда № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'
			</div>
		';
		if($p['doc_bar'])
			$header_numb.='
			<div style="margin-top:2.5mm;float:right;width:35%;text-align:right;">
				<img src="/files/'.substr($filename,0,-32).'bar.png" style="height:24px"/>
			</div>';
		$mpdf->SetHTMLHeader($header_numb);
		
		//логотип, контакты, футер
		$header='
			<div style="width:100%;padding-bottom:5mm;border-bottom:1px solid #bbb;">
				<div style="float:left;color:#333;margin-right:30px;width:30%;">';
		$header.=($p['doc_logo'])?'<img src="/img/'.$p['doc_logo'].'" width="120"/>':'';
		$header.='
				</div>
				<div style="width:45%;float:right;font-size:13;">
					<p style="margin:0 0 3px 0;padding:0;font-size:14;font-weight:bold;">'.transform::typography($p['doc_header_org_name']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_address']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$p['doc_header_org_email'].'">'.transform::typography($p['doc_header_org_email']).'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		
		
		
		$body.=$header;//$mpdf->SetHTMLHeader($header);
	
		$footer='<div style="float:left;width:10%;">
					<div style="'.($p['doc_header_bar']?'':'margin-top:2.4mm;').'font-size:12;color:#333333;">
						{PAGENO}
					</div>
				</div>
				<div style="width:90%;text-align:right">
					<table width="160mm" style="margin-top:2.5mm;">
						<thead>
							<tr>
								<td width="35mm"></td>
								<td width="2.5mm"></td>
								<td width="30mm"></td>
								<td width="20mm"></td>
								<td width="35mm"></td>
								<td width="2.5mm"></td>
								<td width="30mm"></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="border-bottom:1px solid #bbb;"></td>
								<td></td>
								<td>/ '.transform::fio($client['m_contragents_c_director_name']).' /</td>
								<td></td>
								<td style="border-bottom:1px solid #bbb;"></td>
								<td></td>
								<td>/ '.transform::fio($org['m_contragents_c_director_name']).' /</td>
							</tr>
							<tr>
								<td style="text-align:center;font-size:10;color:#656565;">Заказчик</td>
								<td></td>
								<td></td>
								<td></td>
								<td style="text-align:center;font-size:10;color:#656565;">Подрядчик</td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>';
		if($p['doc_signature']){
			$footer.='<div>';
			$footer.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;margin-left:20mm;margin-top:-16mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
			$footer.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;margin-left:105mm;margin-top:-16mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			$footer.='</div>';
		}
		$mpdf->SetHTMLFooter($footer);
				
		//заголовок
		$body.='
			<h1 style="font-size:20;font-weight:400;text-align:center;padding-bottom:10px;padding-top:5mm;">Договор подряда № '.$p['doc_numb'].'<br/><span style="color:#999;font-size:12">на выполнение строительных и ремонтно-отделочных работ</span></h1>
			<div>
				<table width="180mm">
					<tr>
						<td width="30%">г. Иваново</td>
						<td width="40%"></td>
						<td width="30%" style="text-align:right;">'.transform::date_f(dtu($p['doc_date'])).'</td>
					</tr>
				</table>
			</div>
		';
		
		//тело документа
		//физлицо
		if(in_array(3,explode('|',$client['m_contragents_type'])))
			$body.='
				<bookmark content="Преамбула" level="0"></bookmark>
				<p>Гражданин РФ <strong>'.$client['m_contragents_p_fio'].'</strong>'.($client['m_contragents_p_passport_sn']?' (паспорт '.$client['m_contragents_p_passport_sn'].($client['m_contragents_p_passport_v']?' выдан '.$client['m_contragents_p_passport_v']:'').($client['m_contragents_p_passport_date']?' '.dtu($client['m_contragents_p_passport_date'],'d.m.Y'):'').($client['m_contragents_p_birthday']?', дата рождения '.dtu($client['m_contragents_p_birthday'],'d.m.Y'):'').($client['m_contragents_address_j']?', зарегистрирован(а) по адресу '.$client['m_contragents_address_j']:'').')':'').', именуемый в дальнейшем &laquo;Заказчик&raquo;, с одной стороны, и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Подрядчик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, заключили настоящий договор подряда (далее по тексту &ndash; &laquo;Договор&raquo;) о нижеследующем:</p>
			';
		//юрлицо
		else{
			$body.='
				<bookmark content="Преамбула" level="0"></bookmark>
				<p><strong>'.$client['m_contragents_c_name_full'].'</strong>, именуемый(ое) в дальнейшем &laquo;Заказчик&raquo;, в лице '.$client['m_contragents_c_director_post'].' '.$client['m_contragents_c_director_name_rp'].', действующего на основании '.$client['m_contragents_c_director_base'].', с одной стороны, и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Подрядчик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, заключили настоящий договор подряда (далее по тексту &ndash; &laquo;Договор&raquo;) о нижеследующем:</p>
			';
		}
		$body.='
			<bookmark content="1. Предмет договора" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">1. Предмет договора</h2>
			<p>1.1. По настоящему Договору Подрядчик обязуется по заданию Заказчика в установленный Договором срок выполнить работы, указанные в п. 1.2 Договора (далее по тексту &ndash; &laquo;Работы&raquo;), в помещении заказчика, находящегося по адресу: <strong>'.$client['order_address'].'</strong> (далее по тексту &ndash; &laquo;Объект&raquo;), а Заказчик обязуется принять Работы и оплатить обусловленную Договором цену.';
		$body.=in_array(3,explode('|',$client['m_contragents_type']))?' Работы, выполняемые Подрядчиком, предназначены удовлетворять бытовые или другие личные потребности Заказчика.':'';
		$body.='</p>
			<p>1.2. Содержание, объем и стоимость выполнения Работ указаны в Смете работ (Приложение № 1 к Договору), являющейся неотъемлемой частью Договора.</p>
			<p>1.3. Содержание, объем и стоимость используемых для выполнения Работ материалов Подрядчика указаны в Смете материалов (Приложение № 2 к Договору), являющейся неотъемлемой частью Договора. Материалы, не перечисленные в Смете материалов, предоставляются Заказчиком.</p>
			<p>1.4. Этапы и сроки выполнения работ указаны в Календарном плане работ (Приложение № 2 к Договору), являющемся неотъемлемой частью Договора.</p>
			<p>1.5. Подрядчик самостоятельно определяет способы выполнения задания Заказчика, и выполняет задание из своих материалов, своими силами, инструментами и механизмами.</p>
			<bookmark content="2. Срок действия договора" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">2. Срок действия договора</h2>
			<p>2.1. Договор вступает в силу с момента подписания и действует до окончания выполнения Работ Подрядчиком. Датой окончания Работ считается дата подписания Сторонами окончательного Акта сдачи-приёмки выполненных работ.</p>
			<bookmark content="3. Права и обязанности сторон" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">3. Права и обязанности сторон</h2>
			<bookmark content="3.1. Обязанности Подрядчика" level="1"></bookmark>
			<p>3.1. Подрядчик обязуется:</p>
			<p style="padding-left: 30px;">3.1.1. Выполнить все Работы в соответствии с условиями Договора в объёме и в сроки, предусмотренные Договором и сдать Работы Заказчику в состоянии, позволяющем нормальную эксплуатацию результатов Работ. Проводить работы в соответствии с требованиями действующих строительных норм и правил (СНиП), правил пожарной безопасности, техники безопасности и санитарных норм.</p>
			<p style="padding-left: 30px;">3.1.2. При непосредственном выполнении Работ в отсутствие на Объекте Заказчика не допускать нахождение на Объекте посторонних лиц.</p>
			<p style="padding-left: 30px;">3.1.3. В течение 2 (двух) рабочих дней со дня получения письменного запроса Заказчика давать Заказчику письменные объяснения о выполнении Работ, а также незамедлительно давать устные объяснения Заказчику о выполнении Работ.</p>
			<p style="padding-left: 30px;">3.1.4. Немедленно предупредить Заказчика и до получения его указаний приостановить Работы при обнаружении:</p>
			<div style="margin:0;padding:0;padding-left:60px;">
				<ul style="margin:0;padding:0;">
					<li>непригодности или недоброкачественности предоставленного Заказчиком материала</li>
					<li>возможных неблагоприятных для Заказчика последствий выполнения его указаний о способе выполнения Работ</li>
					<li>отрицательного результата или нецелесообразности дальнейшего проведения Работ</li>
					<li>иных, не зависящих от Подрядчика обстоятельств, которые грозят годности или прочности результатов выполняемых Работ либо создают невозможность их завершения в срок</li>
				</ul>
			</div>
			<p style="padding-left: 30px;">Вопрос о целесообразности продолжения Работ решается Сторонами в течение 2 (двух) рабочих дней со дня получения Заказчиком уведомления о приостановлении Работ.</p>
			<p style="padding-left: 30px;">3.1.5. Своевременно устранять недостатки и дефекты, выявленные при приёмке Работ.</p>
			<p style="padding-left: 30px;">3.1.6. Сообщать Заказчику о необходимости проведения дополнительных, не указанных в Смете работ, которые могут увеличить цену Договора, если без этих работ невозможно успешное выполнение Работ по Договору.</p>
			<p style="padding-left: 30px;">3.1.7. Сообщать Заказчику о необходимости использования Подрядчиком дополнительных материалов, не указанных в Смете материалов и не предоставленных Заказчиком.</p>
			<bookmark content="3.2. Обязанности Заказчика" level="1"></bookmark>
			<p>3.2. Заказчик обязуется:</p>
			<p style="padding-left: 30px;">3.2.1. Обеспечить строительную готовность Объекта для производства Подрядчиком порученных ему по Договору Работ не позднее дня начала Работ.</p>
			<p style="padding-left: 30px;">3.2.2. Обеспечить беспрепятственный доступ работников Подрядчика на Объект Заказчика для выполнения Работ.</p>
			<p style="padding-left: 30px;">3.2.3. Предоставить Подрядчику в соответствии с условиями Договора необходимые материалы, не перечисленные в Смете материалов, для проведения Работ, а также обеспечить сохранность этих материалов во время отсутствия работников Подрядчика на Объекте. Предоставление материалов Заказчиком Подрядчику производится в полном объёме перед началом выполнения Работ, либо поэтапно, в объёме, необходимом для выполнения очередного этапа Работ за 2 (два) рабочих дня до начала выполнения этого этапа согласно Календарному плану работ.</p>
			<p style="padding-left: 30px;">3.2.4. Осмотреть и принять выполненные Работы (результат Работ) в сроки и в порядке, которые предусмотрены Договором.</p>
			<p style="padding-left: 30px;">3.2.5. При обнаружении отступлений от Договора, ухудшающих результат Работ, или иных недостатков в Работах немедленно заявить об этом Подрядчику.</p>
			<p style="padding-left: 30px;">3.2.6. Оплатить выполненные Работы и предоставленные Подрядчиком материалы на условиях и в порядке, установленных Договором.</p>
			<bookmark content="3.3. Права Подрядчика" level="1"></bookmark>
			<p>3.3. Подрядчик вправе:</p>
			<p style="padding-left: 30px;">3.3.1. Не приступать к Работам, а начатые Работы приостановить или отказаться от исполнения Договора и потребовать возмещения убытков в случаях, когда нарушение Заказчиком своих обязанностей по Договору препятствует исполнению Договора Подрядчиком, а также при наличии обстоятельств, очевидно свидетельствующих о том, что исполнение Заказчиком указанных обязанностей не будет произведено в установленный срок.</p>
			<p style="padding-left: 30px;">3.3.2. Требовать оплаты выполненных им Работ, в том числе в случае, если результат Работ не был достигнут либо достигнутый результат оказался с недостатками, которые делают его не пригодным для предусмотренного в Договоре использования, по причинам, вызванным недостатками предоставленного Заказчиком материала.&nbsp;</p>
			<p style="padding-left: 30px;">3.3.3. Приостановить выполнение Работ в случае невыполнения Заказчиком своих обязательств по оплате установленных Договором сумм, причитающихся Подрядчику в связи с выполнением Работ по Договору.</p>
			<p style="padding-left: 30px;">3.3.4. При необходимости привлекать к выполнению Работ сторонние организации в качестве субподрядчиков, при этом Подрядчик несет полную ответственность за деятельность этих организаций на Объекте.</p>
			<bookmark content="3.4. Права Заказчика" level="1"></bookmark>
			<p>3.4. Заказчик вправе:</p>
			<p style="padding-left: 30px;">3.4.1. В любое время проверять ход и качество Работ, не вмешиваясь в деятельность Подрядчика.</p>
			<p style="padding-left: 30px;">3.4.2. По своему выбору в случаях, когда Работы выполнены Подрядчиком с отступлениями от Договора, ухудшившими результат Работ, или с иными недостатками, которые делают его не пригодным для предусмотренного в Договоре использования, а также в случаях обнаружения недостатков во время приемки результата Работ:</p>
			<div style="margin:0;padding:0;padding-left:60px;">
				<ul style="margin:0;padding:0;">
					<li style="padding-left: 30px;">потребовать от Подрядчика замены результата Работ ненадлежащего качества (повторного выполнения Работ);</li>
					<li style="padding-left: 30px;">потребовать от Подрядчика безвозмездного устранения недостатков в разумный срок;</li>
					<li style="padding-left: 30px;">потребовать от Подрядчика соразмерного уменьшения установленной за Работы цены</li>
				</ul>
			</div>
			<p style="padding-left: 30px;">3.4.3. Если отступления в Работах от условий Договора или иные недостатки результата Работ в установленный Заказчиком разумный срок не были устранены либо являются существенными и неустранимыми, отказаться от исполнения Договора и потребовать возмещения причиненных убытков.</p>
			<p style="padding-left: 30px;">3.4.4. Отказаться от уплаты работ, не предусмотренных Договором или дополнительными соглашениями к Договору.</p>
			<p style="padding-left: 30px;">3.4.5. В любое время до сдачи Работ отказаться от исполнения условий Договора, уплатив Подрядчику стоимость Работ, выполненных до уведомления об отказе от исполнения Договора, стоимость материалов, использованных ядл выполнения Работ и возместив Подрядчику расходы, произведенные до этого момента в целях исполнения Договора.</p>
			<bookmark content="4. Сроки выполнения работ" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">4. Сроки выполнения работ</h2>
			<p>4.1. Срок выполнения Работ определяется поэтапно в Календарном плане работ (Приложение № 2 к Договору), являющемся неотъемлемой частью Договора.</p>
			<p>4.2. Сроки начала и окончания Работ по Договору переносятся Подрядчиком в одностороннем порядке на период просрочки исполнения Заказчиком встречных обязательств, предусмотренных пунктами 3.2.1 &ndash; 3.2.3 и 3.2.6 Договора.</p>
			<bookmark content="5. Стоимость работ и порядок расчётов" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">5. Стоимость работ и порядок расчётов</h2>
			<p>5.1. Стоимость Работ по Договору определяется Сметой работ (Приложение № 1 к Договору), являющейся неотъемлемой частью Договора.</p>
			<p>5.2. Стоимость материалов для выполнения Работ по Договору определяется Сметой материалов (Приложение № 2 к Договору), являющейся неотъемлемой частью Договора.</p>
			<p>5.3.';
		//сумма предоплаты
		if($p['m_documents_doc_sum_pre'])
			$body.=' В течение 3 (трёх) рабочих дней с момента заключения настоящего Договора, но не позднее момента начала работ в соответствии с Календарным планом, Заказчик вносит предоплату за Работы Подрядчику в размере '.$p['m_documents_doc_sum_pre'].' ('.transform::summ_text($p['m_documents_doc_sum_pre'],true,false).')';
		if($p['nds']!=-1)
			$body.=', в том числе НДС '.$orders->orders_id[$p['order']][0]['m_orders_nds'].'% — '.round($p['m_documents_doc_sum_pre']*($orders->orders_id[$p['order']][0]['m_orders_nds']/100)/(1+$orders->orders_id[$p['order']][0]['m_orders_nds']/100),2).' ('.transform::summ_text($p['m_documents_doc_sum_pre']*.20/1.20,true,false).')';
		$body.='.';
		//кол-во дней для оплаты после каждого этапа
		if($p['m_documents_doc_sum_phase'])
			$body.=' Поэтапная оплата за выполненные работы производится Заказчиком в течение '.$p['m_documents_doc_sum_phase'].' ('.transform::summ_text($p['m_documents_doc_sum_phase'],false,false,true).') рабочих дней с момента подписания Акта сдачи-приёмки этапа Работ.';
		//кол-во дней после сдачи для окончательной оплаты
		if($p['m_documents_doc_sum_end'])
			$body.=' Оплата за выполненные работы (окончательный расчёт) производится Заказчиком по окончанию работ в течение '.$p['m_documents_doc_sum_end'].' ('.transform::summ_text($p['m_documents_doc_sum_end'],false,false,true).') календарных дней после подписания Акта выполненных работ по последнему этапу'.($p['m_documents_doc_sum_pre']?', за вычетом предоплаты':'').'.';
		$body.='
			<p>5.4. В течение 3 (трёх) рабочих дней с момента заключения настоящего Договора, но не позднее момента начала работ в соответствии с Календарным планом, Заказчик вносит предоплату за материалы Подрядчику в размере '.$p['m_documents_doc_sum_pre_m'].' ('.transform::summ_text($p['m_documents_doc_sum_pre_m'],true,false).')';
		if($p['nds']!=-1)
			$body.=', в том числе НДС '.$orders->orders_id[$p['order']][0]['m_orders_nds'].'% — '.round($p['m_documents_doc_sum_pre_m']*($orders->orders_id[$p['order']][0]['m_orders_nds']/100)/(1+$orders->orders_id[$p['order']][0]['m_orders_nds']/100),2).' ('.transform::summ_text($p['m_documents_doc_sum_pre_m']*.20/1.20,true,false).')';
		$body.='. В дальнейшем Заказчик производит оплату  материалов Подрядчика в течении 3 (трёх) рабочих дней с момента поступлении уведомления от Подрядчика о необходимости оплаты.</p>';
		if($p['nds']!=-1)
			$body.='
			<p>5.5. Стоимость материалов и работ по настоящему Договору включает в себя НДС '.$orders->orders_id[$p['order']][0]['m_orders_nds'].'%.</p>';
		$body.='	
			<p>5.6. Способ оплаты Работ по Договору:</p>
			<div style="margin:0;padding:0;padding-left:60px;">
				<ul style="margin:0;padding:0;">
					'.($p['m_documents_doc_method_pay_cash']?'<li>передача Заказчиком наличных денежных средств Подрядчику;</li>':'').'
					'.($p['m_documents_doc_method_pay_bank']?'<li>перечисление денежных средств Заказчиком на расчётный счет Подрядчика (безналичный расчёт);</li>':'').'
				</ul>
			</div>
			<bookmark content="6. Порядок сдачи и приёмки работ" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">6. Порядок сдачи и приёмки работ</h2>
			<p>6.1. Приемка Работ подтверждается подписанием Сторонами Акта сдачи - приёмки выполненных работ, который оформляется в следующем порядке:</p>
			<p style="padding-left: 30px;">6.1.1. Подрядчик по завершении выполнения Работ в сроки, установленные п. 4.1. Договора, передает Заказчику заказным почтовым отправлением либо лично в руки: Акт сдачи-приёмки выполненных работ в двух экземплярах, содержащий наименование, количество, стоимость выполненных Работ и использованных для выполнения Работ материалов Подрядчика. Вместе с Актом Подрядчик передает Заказчику счет на оплату Работ и материалов, с учетом полученных предоплат.</p>
			<p style="padding-left: 30px;">6.1.2. Заказчик обязан в течение 2 (двух) рабочих дней со дня получения документов, указанных в п. 6.1.1. Договора, с участием Подрядчика осмотреть и принять выполненные Работы (результат Работ) и материалы Подрядчика, использованные при выполнении Работ, подписать и вернуть Подрядчику 1 (один) экземпляр Акта сдачи-приёмки выполненных работ или направить Подрядчику мотивированный отказ от приема этапа Работ путем передачи его по почте либо лично в руки. По истечении указанного срока, при отсутствии мотивированного отказа Заказчика, Работы считаются принятыми Заказчиком и подлежащими оплате на основании одностороннего Акта сдачи-приёмки выполненных работ, составленного Подрядчиком.</p>
			<p style="padding-left: 30px;">6.1.3. В случае отказа Заказчика от приемки Работ (этапа Работ) Сторонами в течение 3 (трёх) рабочих дней со дня получения Подрядчиком мотивированного отказа составляется двусторонний акт с перечнем необходимых доработок и сроков их выполнения.</p>
			<p style="padding-left: 30px;">6.1.4. Заказчик, принявший Работы без проверки, лишается права ссылаться на недостатки Работ, которые могли быть установлены при обычном способе их приемки (явные недостатки).</p>
			<p style="padding-left: 30px;">6.1.5. Заказчик, обнаружив после приемки Работ отступления в них от условий Договора или иные недостатки, которые не могли быть установлены им при обычном способе приемки (скрытые недостатки), обязан известить об этом Подрядчика в течение 3 (трёх) рабочих дней со дня их обнаружения.</p>
			<p style="padding-left: 30px;">6.1.6. При досрочном прекращении Работ по Договору, в случае, предусмотренном п. 3.4.5. Договора, Заказчик обязан принять выполненные Работы по степени их готовности на дату прекращения Работ и оплатить их стоимость (за вычетом предоплаты).</p>
			<p style="padding-left: 30px;">6.1.7. При досрочном выполнении Подрядчиком Работ Заказчик обязан принять и оплатить эти Работы на условиях Договора.</p>
			
			<bookmark content="7. Гарантия качества работ" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">7. Гарантия качества работ</h2>
			<p>7.1. Гарантия качества распространяется на все Работы, выполненные Подрядчиком по Договору.</p>
			<p>7.2. Гарантийный срок на результат выполненных Работ при нормальной эксплуатации устанавливается равным <strong>'.$p['m_documents_doc_guarantee'].' ('.transform::summ_text($p['m_documents_doc_guarantee'],false,false).') месяцев</strong> с даты подписания Сторонами Акта сдачи-приёмки выполненных Работ, соответствующего последнему этапу Работ в Календарном плане работ.</p>
			<p>7.3. Подрядчик несет ответственность за недостатки, обнаруженные в пределах гарантийного срока, кроме недостатков, которые произошли вследствие неправильной эксплуатации, нормального износа, усадки, или ненадлежащего ремонта объекта Работ (или его частей), произведенного самим Заказчиком или привлеченными им третьими лицами, и обязан устранить их за свой счёт в течение 20 (двадцати) рабочих дней с момента получения письменного извещения Заказчика. Гарантийный срок в этом случае продлевается на период устранения дефектов.</p>
			<bookmark content="8. Ответственность сторон" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">8. Ответственность сторон</h2>
			<p>8.1. Стороны несут ответственность за неисполнение или ненадлежащее исполнение своих обязательств по Договору в соответствии с Договором и законодательством РФ.</p>
			<p>8.2. Неустойка по Договору выплачивается только на основании обоснованного письменного требования Сторон.</p>
			<p>8.3. Выплата неустойки не освобождает Стороны от выполнения обязанностей, предусмотренных Договором.</p>
			<p>8.4. За ущерб, причиненный третьему лицу в процессе выполнения Работ по Договору, отвечает Подрядчик, если не докажет, что ущерб был причинен вследствие обстоятельств, за которые отвечает Заказчик.</p>
			<p>8.6. Ответственность Подрядчика:</p>
			<p style="padding-left: 30px;">8.6.1. В случае несвоевременного выполнения всех Работ Подрядчик обязуется выплатить Заказчику пени в размере 1% (одного процента) в день от стоимости невыполненных Работ по Договору за каждый день просрочки, но не более 20% (двадцати процентов) стоимости невыполненных Работ по Договору.</p>
			<p style="padding-left: 30px;">8.6.2. Подрядчик несет ответственность за несохранность предоставленных Заказчиком материалов, оказавшихся во владении Подрядчика в связи с исполнением Договора в время непосредственного исполнения Работ, в размере стоимости указанных материалов, указанной в Приложении № 3 к Договору.</p>
			<p style="padding-left: 30px;">8.6.3. В случае нарушения Подрядчиком сроков выполнения Работ, Заказчик по своему выбору вправе:</p>
			<div style="margin:0;padding:0;padding-left:60px;">
				<ul style="margin:0;padding:0;">
					<li style="padding-left: 30px;">назначить Подрядчику новый срок;</li>
					<li style="padding-left: 30px;">потребовать уменьшения цены за выполнение Работ;</li>
					<li style="padding-left: 30px;">отказаться от исполнения Договора;</li>
				</ul>
			</div>
			<p style="padding-left: 30px;">8.6.4. Подрядчик несёт ответственность за ненадлежащее качество предоставленных им материалов, перечисленных в Смете материалов, а также за предоставление материалов, обременённых правами третьих лиц.</p>			
			<p style="padding-left: 30px;">8.6.5. Подрядчик не несет ответственности за невыполнение обязательств по Договору, если оно вызвано действием или бездействием Заказчика, повлекшим невыполнение им собственных обязательств по Договору перед Подрядчиком.</p>
			<p>8.7. Ответственность Заказчика:</p>
			<p style="padding-left: 30px;">8.7.1. В случае несвоевременной оплаты Работ Подрядчику в соответствии с условиями Договора Заказчик обязуется выплатить Подрядчику пени в размере 1% (одного процента) в день от стоимости несвоевременно оплаченных Работ за каждый день просрочки, но не более 20% (двадцати процентов) стоимости этапа Работ по Договору.</p>
			<p style="padding-left: 30px;">8.7.2. В случае несвоевременного исполнения (ненадлежащего исполнения) Заказчиком условий предоставления материала, предусмотренных п. 3.2.3. Договора, Заказчик обязуется выплатить Подрядчику пени в размере 1% (одного процента) в день от стоимости этапа Работ по Договору за каждый день просрочки, но не более 20% (двадцати процентов) стоимости этапа Работ по Договору. При этом Подрядчик вправе приостановить выполнение Работ вплоть до исполнения Заказчиком указанных обязанностей по Договору.</p>
			<p style="padding-left: 30px;">8.7.3. Заказчик несёт ответственность за ненадлежащее качество предоставленных им материалов, не перечисленных в Смете материалов, а также за предоставление материалов, обременённых правами третьих лиц.</p>
			<bookmark content="9. Основания и порядок расторжения договора" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">9. Основания и порядок расторжения договора</h2>
			<p>9.1. Договор может быть расторгнут по соглашению Сторон, а также в одностороннем порядке по письменному требованию одной из Сторон по основаниям, предусмотренным Договором и законодательством.</p>
			<p>9.2. Расторжение Договора в одностороннем порядке производится только по письменному требованию Сторон в течение 3 (трёх) календарных дней со дня получения Стороной такого требования.</p>
			<p>9.3. Подрядчик вправе расторгнуть Договор в одностороннем порядке в случаях:</p>
			<p style="padding-left: 30px;">9.3.1. Предусмотренными п. 3.3.1. &ndash; 3.3.3. Договора.</p>
			<p style="padding-left: 30px;">9.3.2. Увеличения стоимости Работ или необходимости проведения дополнительных работ и отказа Заказчика от заключения дополнительного соглашения об увеличении стоимости Работ.</p>
			<p style="padding-left: 30px;">9.3.3. Задержки Заказчиком оплаты выполненных Работ более чем на 5 (пять) банковских дней.</p>
			<p>9.4. Заказчик вправе расторгнуть Договор в одностороннем порядке в случаях:</p>
			<p style="padding-left: 30px;">9.4.1. Предусмотренными п. 3.4.3 Договора.</p>
			<p style="padding-left: 30px;">9.4.2. В любое время до сдачи Работ, уплатив Подрядчику стоимость использованных материалов и Работ, выполненных до уведомления об отказе от исполнения Договора и возместив Подрядчику расходы, произведенные до этого момента в целях исполнения Договора.</p>
			<bookmark content="10. Разрешение споров между сторонами" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">10. Разрешение споров между сторонами</h2>
			<p>10.1. Претензионный порядок досудебного урегулирования споров из Договора является для Сторон обязательным.</p>
			<p>10.2. Претензионные письма направляются Сторонами заказным почтовым отправлением с уведомлением о вручении последнего адресату по местонахождению Сторон, указанным в пункте 14 Договора.</p>
			<p>10.3. Направление Сторонами претензионных писем иным способом, чем указано в<br />п. 10.2 Договора не допускается.</p>
			<p>10.4. Срок рассмотрения претензионного письма составляет 5 (пять) рабочих дней со дня получения последнего адресатом.</p>
			<p>10.5. Споры из Договора разрешаются в судебном порядке в соответствии с законодательством.</p>
			<bookmark content="11. Форс-мажор" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">11. Форс-мажор</h2>
			<p>11.1. Стороны освобождаются от ответственности за полное или частичное неисполнение обязательств по Договору в случае, если неисполнение обязательств явилось следствием действий непреодолимой силы, а именно: пожара, наводнения, землетрясения, забастовки, войны, действий органов государственной власти или других независящих от Сторон обстоятельств.</p>
			<p>11.2. Сторона, которая не может выполнить обязательства по Договору, должна своевременно, но не позднее 3 (трёх) календарных дней после наступления обстоятельств непреодолимой силы, письменно известить другую Сторону, с предоставлением обосновывающих документов, выданных компетентными органами.</p>
			<p>11.3. Стороны признают, что неплатежеспособность Сторон не является форс-мажорным обстоятельством.</p>
			<bookmark content="12. Прочие условия" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">12. Прочие условия</h2>
			<p>12.1. Стороны не имеют никаких сопутствующих устных договоренностей. Содержание текста Договора полностью соответствует действительному волеизъявлению Сторон.</p>
			<p>12.2. Любая договоренность между Сторонами, влекущая за собой&nbsp;новые обстоятельства, не предусмотренные Договором, считается действительной, если она подтверждена Сторонами в письменной форме в виде дополнительного соглашения.</p>
			<p>12.3. Договор составлен в 2 (двух) подлинных экземплярах на русском языке по одному для каждой из Сторон.</p>
			<bookmark content="13. Список приложений к договору" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">13. Список приложений к договору</h2>
			<p>13.1. Приложение № 1 &mdash; Смета работ.</p>
			<p>13.2. Приложение № 2 &mdash; Календарный план работ.</p>
			<p>13.1. Приложение № 3 &mdash; Смета материалов.</p>
			<bookmark content="14. Адреса, реквизиты  и подписи сторон" level="0"></bookmark>
			<h2 style="text-align: center;font-size:14;">14. Адреса, реквизиты  и подписи сторон</h2>
			<table style="font-size:13;border-spacing:0;border-top:1px solid #bbb;margin-top:15mm;margin-bottom:6mm;border-right:1px solid #bbb;" width="180mm">
				<thead>
					<tr>
						<td style="font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;text-align:center;" width="30mm"></td>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;text-align:center;" width="80mm">Заказчик</td>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;text-align:center;" width="80mm">Подрядчик</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Наименование</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_name_full'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_name_full'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Юридический адрес</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_address_j'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_address_j'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Почтовый адрес</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_address_p'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_address_p'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Фактический адрес</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_address_p'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_address_f'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Телефон</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['tel'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['tel'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">E-mail</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_email'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_email'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">ИНН</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_inn'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_inn'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">КПП</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_kpp'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_kpp'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">ОГРН</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_ogrn'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_ogrn'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Банк</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_bank_name'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_bank_name'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">БИК</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_bank_bik'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_bank_bik'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Расч. сч.</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_bank_rs'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_bank_rs'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Корр. сч.</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_bank_ks'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_bank_ks'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Должность подписанта</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_director_post'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_director_post'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Ф.И.О. подписанта</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$client['m_contragents_c_director_name'].'</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;">'.$org['m_contragents_c_director_name'].'</td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Подпись</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;"></td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;"></td>
					</tr>
					<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;color:#666;">Место печати</td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;"></td>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;"></td>
					</tr>
				</tbody>
			</table>
		';

		//печати и подписи
		if($p['doc_signature']){
			$body.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:50mm;margin-top:-23mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
			$body.=$client['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:45mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=stamp"/></div>':'';
			$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:125mm;margin-top:-23mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:140mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
		}
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);

		$mpdf->WriteHTML($body);
		$mpdf->SetHTMLFooter('');
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}
	}
	
	//смета по форме всеумельца
	public static function smeta_vseumelec($p=array(
		'org'=>'',
		'client'=>'',
		'order'=>'',
		'additional'=>'',
		'base_numb'=>'',
		'base_date'=>'',
		'doc_date'=>'',
		'doc_numb'=>'',
		'doc_logo'=>null,
		'doc_bar'=>null,
		'doc_signature'=>null,
		'doc_header_org_name'=>null,
		'doc_header_org_address'=>null,
		'doc_header_org_tel'=>null,
		'doc_header_org_email'=>null,
		'items'=>false
	),$filename){
		global $info,$contragents,$orders,$services;
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		
		$client['m_contragents_c_director_name']=$contragents->getDirector($client['m_contragents_id']);

		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('','',9,'dejavuserifcondensed',15,15,20,25,10,10);
		$mpdf->SetAuthor(transform::typography('vseumelec.ru'));
		$mpdf->SetCreator('vseumelec.ru');
		$mpdf->SetSubject(strcode2utf('Смета на выполнение строительных и ремонтно-отделочных работ'));
		$mpdf->SetTitle(strcode2utf('Смета на выполнение строительных и ремонтно-отделочных работ № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));
		$mpdf->SetKeywords(strcode2utf('Смета на выполнение строительных и ремонтно-отделочных работ № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));		
		$mpdf->defaultCSS['P']['MARGIN']='0.9em 0';
		$mpdf->shrink_tables_to_fit=2;
		

		$body='';
		//количество не пустых товаров
		$count_all=1;
		
		//футер
		$footer='<div style="float:left;width:10%;">
					<div style="'.($p['doc_header_bar']?'':'margin-top:2.4mm;').'font-size:12;color:#333333;">
						{PAGENO}
					</div>
				</div>
				<div style="width:90%;text-align:right">
					<table width="160mm" style="margin-top:2.5mm;">
						<thead>
							<tr>
								<td width="35mm"></td>
								<td width="2.5mm"></td>
								<td width="30mm"></td>
								<td width="20mm"></td>
								<td width="35mm"></td>
								<td width="2.5mm"></td>
								<td width="30mm"></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="border-bottom:1px solid #bbb;"></td>
								<td></td>
								<td>/ '.transform::fio($client['m_contragents_c_director_name']).' /</td>
								<td></td>
								<td style="border-bottom:1px solid #bbb;"></td>
								<td></td>
								<td>/ '.transform::fio($org['m_contragents_c_director_name']).' /</td>
							</tr>
							<tr>
								<td style="text-align:center;font-size:10;color:#656565;">Заказчик</td>
								<td></td>
								<td></td>
								<td></td>
								<td style="text-align:center;font-size:10;color:#656565;">Подрядчик</td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>';
		if($p['doc_signature']){
			$footer.='<div>';
			$footer.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;margin-left:20mm;margin-top:-16mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
			$footer.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;margin-left:105mm;margin-top:-16mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			$footer.='</div>';
		}
		$mpdf->SetHTMLFooter($footer);
		
		//если есть метка доп. соглашения
		if($p['additional']){
			
			//номер сметы в хедере
			$header_numb='
				<div style="margin-top:2.5mm;font-size:10px;color:#656565;float:left;width:60%;">
					Смета работ № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'<br/>
					Приложение № 1 к Дополнительному соглашению № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'<br/>
					к Договору № '.$p['base_numb'].' от '.transform::date_f(dtu($p['base_date'])).'
				</div>
			';
			if($p['doc_bar'])
				$header_numb.='
				<div style="margin-top:2.5mm;float:right;width:35%;text-align:right;">
					<img src="/files/'.substr($filename,0,-18).'bar.png" style="height:24px"/>
				</div>';
			$mpdf->SetHTMLHeader($header_numb);
		
			//логотип, контакты на первой странице
			$header='
				<div style="padding-top:5mm;width:100%;padding-bottom:5mm;border-bottom:1px solid #bbb;clear:both;">
					<div style="float:left;color:#333;margin-right:30px;width:30%;">';
			$header.=($p['doc_logo'])?'<img src="/img/'.$p['doc_logo'].'" width="120"/>':'';
			$header.='
					</div>
					<div style="width:45%;float:right;font-size:13;">
						<p style="margin:0 0 3px 0;padding:0;font-size:14;font-weight:bold;">'.transform::typography($p['doc_header_org_name']).'</p>
						<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_address']).'</p>
						<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_tel']).'</p>
						<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$p['doc_header_org_email'].'">'.transform::typography($p['doc_header_org_email']).'</a></p>
					</div>
				</div>';
			$header=str_replace("\r\n","",$header);
			$header=str_replace("\t","",$header);
			$header=str_replace("\n","",$header);
			
			$body.=$header;//$mpdf->SetHTMLHeader($header);

			//заголовок
			$body.='
				<h1 style="font-size:20;font-weight:400;text-align:center;padding-bottom:10px;padding-top:5mm;">Дополнительное соглашение № '.$p['doc_numb'].'<br/><span style="color:#999;font-size:12">к Договору № '.$p['base_numb'].' от '.transform::date_f(dtu($p['base_date'])).'</span></h1>
				<div>
					<table width="180mm">
						<tr>
							<td width="30%">г. Иваново</td>
							<td width="40%"></td>
							<td width="30%" style="text-align:right;">'.transform::date_f(dtu($p['doc_date'])).'</td>
						</tr>
					</table>
				</div>
			';
			//тело документа
			//физлицо
			if(in_array(3,explode('|',$client['m_contragents_type'])))
				$body.='
					<bookmark content="Дополнительное соглашение" level="0"></bookmark>
					<p>Гражданин РФ <strong>'.$client['m_contragents_p_fio'].'</strong>, именуемый в дальнейшем &laquo;Заказчик&raquo;, с одной стороны, и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Подрядчик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, заключили настоящее Дополнительное соглашение к Договору подряда № '.$p['base_numb'].' от '.transform::date_f(dtu($p['base_date'])).' (далее — &laquo;Договор&raquo;) о нижеследующем:</p>
				';
			else{
				$body.='
					<bookmark content="Дополнительное соглашение" level="0"></bookmark>
					<p><strong>'.$client['m_contragents_c_name_full'].'</strong>, именуемый(ое) в дальнейшем &laquo;Заказчик&raquo;, в лице '.$client['m_contragents_c_director_post'].' '.$client['m_contragents_c_director_name_rp'].', действующего на основании '.$client['m_contragents_c_director_base'].', с одной стороны, и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Подрядчик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, заключили настоящее Дополнительное соглашение к Договору подряда № '.$p['base_numb'].' от '.transform::date_f(dtu($p['base_date'])).' (далее — &laquo;Договор&raquo;) о нижеследующем:</p>
				';
			}
			$body.='
				<p>1. Стороны пришли к обоюдному согласию об изменении сметы (Приложение № 1 к Договору) и календарного плана (Приложение № 2 к Договору).</p>
				<p>2. Содержание, объем, стоимость выполнения работ по Договору указаны в новой редакции сметы (Приложение № 1 к Дополнительному соглашению), являющейся неотъемлемой частью Дополнительного соглашения.</p>
				<p>3. Этапы и сроки выполнения работ по Договору указаны в новой редакции календарного плана работ (Приложение № 2 к Дополнительному соглашению), являющемся неотъемлемой частью Дополнительного соглашения.</p>
				<p>4. Настоящее Дополнительное соглашение вступает в силу с момента подписания и действует до момента окончания срока действия Договора.</p>
				<p>5. Настоящее Дополнительное соглашение составлено в двух экземплярах, имеющих равную юридическую силу, по одному для каждой из Сторон.</p>
				<p>6. Настоящее Дополнительное соглашение является неотъемлемой частью Договора.</p>';
			
			//таблица для подписей и печати
			$body.='<table style="font-size:13;border-spacing:0;margin:10mm 0 2mm;line-height:4.5mm" width="180mm">
						<tr>
							<td width="180mm">
								<table style="font-size:13;border-spacing:0;margin:0;line-height:4.5mm" width="180mm">
									<thead>
										<tr>
											<td width="38mm"></td>
											<td width="7mm"></td>
											<td width="38mm"></td>
											<td width="7mm"></td>
											<td width="38mm"></td>
											<td width="7mm"></td>
											<td width="38mm"></td>
										</tr>
										<tr>
											<td colspan="3" style="font-weight:bold;padding-bottom:5mm">Заказчик</td>
											<td width="7mm"></td>
											<td colspan="3" style="font-weight:bold;padding-bottom:5mm">Подрядчик</td>
										</tr>
									</thead>
									<tr>
										<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$client['m_contragents_c_director_post'].'</td>
										<td></td>
										<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$org['m_contragents_c_director_post'].'</td>
									</tr>
									<tr>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
										<td></td>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
									</tr>
									<tr>
										<td></td>
										<td></td>
										<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($client['m_contragents_c_director_name']).'</td>
										<td></td>
										<td></td>
										<td></td>
										<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($org['m_contragents_c_director_name']).'</td>
									</tr>
									<tr>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
										<td></td>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
										<td></td>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
										<td></td>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
									</tr>
									<tr>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
										<td></td>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';	
			
			//печати и подписи
			if($p['doc_signature']){
				$body.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:20mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
				$body.=$client['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:35mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=stamp"/></div>':'';
				$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:115mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
				$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:130mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
			}
			
			$body.='<pagebreak />';
			
		}
		//смета без доп. соглашения
		else{
			//номер сметы в хедере
			$header_numb='
				<div style="margin-top:2.5mm;font-size:10px;color:#656565;float:left;width:60%;">
					Смета работ № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'<br/>
					Приложение № 1 к Договору № '.$p['base_numb'].' от '.transform::date_f(dtu($p['base_date'])).'
				</div>
			';
			if($p['doc_bar'])
				$header_numb.='
				<div style="margin-top:2.5mm;float:right;width:35%;text-align:right;">
					<img src="http://'.$_SERVER['HTTP_HOST'].'/files/'.substr($filename,0,-18).'bar.png" style="height:24px"/>
				</div>';
			$mpdf->SetHTMLHeader($header_numb);
		}
		
		//логотип, контакты на первой странице
		$header='
			<div style="'.($p['additional']?'padding-top:5mm;':'').'width:100%;padding-bottom:5mm;border-bottom:1px solid #bbb;clear:both;">
				<div style="float:left;color:#333;margin-right:30px;width:30%;">';
		$header.=($p['doc_logo'])?'<img src="http://'.$_SERVER['HTTP_HOST'].'/img/'.$p['doc_logo'].'" width="120"/>':'';
		$header.='
				</div>
				<div style="width:45%;float:right;font-size:13;">
					<p style="margin:0 0 3px 0;padding:0;font-size:14;font-weight:bold;">'.transform::typography($p['doc_header_org_name']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_address']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$p['doc_header_org_email'].'">'.transform::typography($p['doc_header_org_email']).'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		
		$body.=$header;//$mpdf->SetHTMLHeader($header);

		//заголовок
		$body.='
			<h1 style="font-size:20;font-weight:400;text-align:center;padding-bottom:10px;padding-top:5mm;">Смета работ № '.$p['doc_numb'].'<br/><span style="color:#999;font-size:12">на выполнение строительных и ремонтно-отделочных работ</span></h1>
			<div>
				<table width="180mm">
					<tr>
						<td width="30%">г. Иваново</td>
						<td width="40%"></td>
						<td width="30%" style="text-align:right;">'.transform::date_f(dtu($p['doc_date'])).'</td>
					</tr>
				</table>
			</div>
		';

		//тело документа
		$sum=0;
		$nds18=0;
		foreach($p['items'] as $_room){
			//если есть работы по комнате
			if($_room->services){
				$body.='
					<bookmark content="'.($_room->room->name?$_room->room->name:'Помещение').'" level="0"></bookmark>
					<h2 style="font-size:16;font-weight:400;margin-bottom:5;">Помещение: <b>'.($_room->room->name?$_room->room->name:'без названия').($_room->room->square?' — '.$_room->room->square.' м<sup>2</sup>':'').'</b></h2>
				';
				$body.='<table style="font-size:13;border-spacing:0;border-top:1px solid #bbb;margin-bottom:6mm;border-right:1px solid #bbb;" width="180mm">
							<thead>
								<tr>
									<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="88mm">Наименование работ</td>
									<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="17mm">Ед. изм.</td>
									<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="20mm">Объём</td>
									<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="25mm">Цена</td>
									<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;text-align:center;" width="30mm;">Сумма</td>
								</tr>
							</thead>
							<tbody>';
				//работы
				$pre_sum=0;
				$pre_nds18=0;
				foreach($_room->services as $_service){
					
					//Коля-Коля Николай
					//$_service->sum*=1.04;
					
					$pre_sum+=$_service->sum;
					$pre_nds18+=$_service->sum*.20/1.20;
					$body.='	<tr>
									<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.$services->services_id[$_service->id][0]['m_services_name'].'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.$info->getUnits($services->services_id[$_service->id][0]['m_services_unit']).'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.$_service->count/* str_replace('.',',',$_service->count) */.'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.transform::price_o($_service->price).'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:center;">'.transform::price_o($_service->sum).'</td>
								</tr>';
				}
				$sum+=$pre_sum;
				$nds18+=$pre_nds18;
				//промежуточные итоги
				$body.='		<tr>
									<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого по помещению:</td>
									<td style="padding:1mm;text-align:center;border-bottom:1px solid #bbb;">'.transform::price_o($pre_sum).'</td>
								</tr>';
				//итог НДС 18%
				$body.=($orders->orders_id[$p['order']][0]['m_orders_nds']==18)?'
								<tr>
									<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС ('.$orders->orders_id[$p['order']][0]['m_orders_nds'].'%):</td>
									<td style="vertical-align:top;padding:1mm;border-bottom:1px solid #bbb;text-align:center;">'.transform::price_o($pre_nds18).'</td>
								</tr>':'';
				$body.='
							</tbody>
						</table>';
			}
		}
		
		$body.='
					<table style="font-size:13;border-spacing:0;margin-bottom:6mm;border-right:1px solid #bbb;" width="180mm">
						<thead>
							<tr>
								<td width="88mm"></td>
								<td width="17mm"></td>
								<td width="20mm"></td>
								<td width="25mm"></td>
								<td width="30mm;"></td>
							</tr>
						</thead>
						<tbody>';		
		//итоговые суммы
		$body.='			<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого по всем помещениям:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum).'</td>
							</tr>';
		//скидка
		if($orders->orders_id[$p['order']][0]['m_orders_discount']>0)
			$body.='		<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Скидка '.$orders->orders_id[$p['order']][0]['m_orders_discount'].'%:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		//итого со скидкой
		if($orders->orders_id[$p['order']][0]['m_orders_discount']>0)
			$body.='		<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого со скидкой:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		//итог НДС 18%
		$body.=($orders->orders_id[$p['order']][0]['m_orders_nds']!=-1)?'
							<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС ('.$orders->orders_id[$p['order']][0]['m_orders_nds'].'%):</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($nds18-$nds18*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>':'';
		//итог БЕЗ НДС
		$body.=($orders->orders_id[$p['order']][0]['m_orders_nds']==-1)?'
							<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Без налога (НДС)</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:right;text-align:center;">—</td>
							</tr>':'';
		//всего к оплате
		$body.='			<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;font-weight:bold">Всего:</td>
								<td style="vertical-align:top;padding:1mm;border-top:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;font-weight:bold">'.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		$body.='
						</tbody>
					</table>';
		
		//сумма прописью
		$body.='<div style="font-size:13;padding-bottom:10px;">
					Общая стоимость Работ по Договору составляет '.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'&nbsp;<span style="font-family:dejavurouble;font-size:12.5">r</span><br>
					<span style="font-weight:bold">'.transform::summ_text($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).($orders->orders_id[$p['order']][0]['m_orders_nds']==18?(', в том числе НДС '.$orders->orders_id[$p['order']][0]['m_orders_nds'].'% — '.transform::summ_text($nds18-$nds18*$orders->orders_id[$p['order']][0]['m_orders_discount']/100,true,false)):'').'</span>
				</div>';
	
		//таблица для подписей и печати
		
		$body.='<table style="font-size:13;border-spacing:0;margin:10mm 0 2mm;line-height:4.5mm" width="180mm">
					<tr>
						<td width="180mm">
							<table style="font-size:13;border-spacing:0;margin:0;line-height:4.5mm" width="180mm">
								<thead>
									<tr>
										<td width="38mm"></td>
										<td width="7mm"></td>
										<td width="38mm"></td>
										<td width="7mm"></td>
										<td width="38mm"></td>
										<td width="7mm"></td>
										<td width="38mm"></td>
									</tr>
									<tr>
										<td colspan="3" style="font-weight:bold;padding-bottom:5mm">Заказчик</td>
										<td width="7mm"></td>
										<td colspan="3" style="font-weight:bold;padding-bottom:5mm">Подрядчик</td>
									</tr>
								</thead>
								<tr>
									<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$client['m_contragents_c_director_post'].'</td>
									<td></td>
									<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$org['m_contragents_c_director_post'].'</td>
								</tr>
								<tr>
									<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
									<td></td>
									<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
								</tr>
								<tr>
									<td></td>
									<td></td>
									<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($client['m_contragents_c_director_name']).'</td>
									<td></td>
									<td></td>
									<td></td>
									<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($org['m_contragents_c_director_name']).'</td>
								</tr>
								<tr>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
								</tr>
								<tr>
									<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
									<td></td>
									<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>';	
		
		//печати и подписи
		if($p['doc_signature']){
			$body.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:20mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
			$body.=$client['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:35mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=stamp"/></div>':'';
			$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:115mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:130mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
		}
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);
		//echo $body;exit;

		$mpdf->WriteHTML($body);
		$mpdf->SetHTMLFooter('');
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}
	}
	
	//календарный план по форме всеумельца
	public static function kalendar_vseumelec($p=array(
		'org'=>'',
		'client'=>'',
		'smeta'=>'',
		'additional'=>'',
		'base_numb'=>'',
		'base_date'=>'',
		'doc_date'=>'',
		'doc_numb'=>'',
		'doc_sum'=>'',
		'doc_logo'=>null,
		'doc_bar'=>null,
		'doc_signature'=>null,
		'doc_header_org_name'=>null,
		'doc_header_org_address'=>null,
		'doc_header_org_tel'=>null,
		'doc_header_org_email'=>null,
		'items'=>false
	),$filename){
		global $info,$contragents,$orders,$services,$documents;
		
		$smeta=$documents->getInfo($p['smeta']);
		$p['order']=$smeta['m_documents_order'];
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		
		$client['m_contragents_c_director_name']=$contragents->getDirector($client['m_contragents_id']);

		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('','',9,'dejavuserifcondensed',15,15,25,25,10,10);
		$mpdf->SetAuthor(transform::typography('vseumelec.ru'));
		$mpdf->SetCreator('vseumelec.ru');
		$mpdf->SetSubject(strcode2utf('Календарный план на выполнение строительных и ремонтно-отделочных работ'));
		$mpdf->SetTitle(strcode2utf('Календарный план на выполнение строительных и ремонтно-отделочных работ № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));
		$mpdf->SetKeywords(strcode2utf('Календарный план на выполнение строительных и ремонтно-отделочных работ № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));		
		$mpdf->defaultCSS['P']['MARGIN']='0.9em 0';
		$mpdf->shrink_tables_to_fit=2;

		$body='';
		//количество не пустых товаров
		$count_all=1;
		
		//номер договора в хедере
		//доп. соглашение
		if($p['additional'])
			$header_numb='
				<div style="margin-top:2.5mm;font-size:10px;color:#656565;float:left;width:60%;">
					Календарный план № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'<br/>
					Приложение № 2 к Дополнительному соглашению '.$smeta['m_documents_numb'].' от '.transform::date_f(dtu($smeta['m_documents_date'])).'<br/>
					к Договору № '.$p['base_numb'].' от '.transform::date_f(dtu($p['base_date'])).'
				</div>
			';
		//без доп. соглашения
		else{
			$header_numb='
				<div style="margin-top:2.5mm;font-size:10px;color:#656565;float:left;width:60%;">
					Календарный план № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'<br/>
					Приложение № 2 Договору № '.$p['base_numb'].' от '.transform::date_f(dtu($p['base_date'])).'
				</div>
			';
		}
		if($p['doc_bar'])
			$header_numb.='
			<div style="margin-top:2.5mm;float:right;width:35%;text-align:right;">
				<img src="/files/'.substr($filename,0,-21).'bar.png" style="height:24px"/>
			</div>';
		$mpdf->SetHTMLHeader($header_numb);
		
		//логотип, контакты, футер
		$header='
			<div style="width:100%;padding-bottom:5mm;border-bottom:1px solid #bbb;clear:both;">
				<div style="float:left;color:#333;margin-right:30px;width:30%;">';
		$header.=($p['doc_logo'])?'<img src="/img/'.$p['doc_logo'].'" width="120"/>':'';
		$header.='
				</div>
				<div style="width:45%;float:right;font-size:13;">
					<p style="margin:0 0 3px 0;padding:0;font-size:14;font-weight:bold;">'.transform::typography($p['doc_header_org_name']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_address']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$p['doc_header_org_email'].'">'.transform::typography($p['doc_header_org_email']).'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		
		$body.=$header;//$mpdf->SetHTMLHeader($header);
	
		$footer='<div style="float:left;width:10%;">
					<div style="'.($p['doc_header_bar']?'':'margin-top:2.4mm;').'font-size:12;color:#333333;">
						{PAGENO}
					</div>
				</div>
				<div style="width:90%;text-align:right">
					<table width="160mm" style="margin-top:2.5mm;">
						<thead>
							<tr>
								<td width="35mm"></td>
								<td width="2.5mm"></td>
								<td width="30mm"></td>
								<td width="20mm"></td>
								<td width="35mm"></td>
								<td width="2.5mm"></td>
								<td width="30mm"></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="border-bottom:1px solid #bbb;"></td>
								<td></td>
								<td>/ '.transform::fio($client['m_contragents_c_director_name']).' /</td>
								<td></td>
								<td style="border-bottom:1px solid #bbb;"></td>
								<td></td>
								<td>/ '.transform::fio($org['m_contragents_c_director_name']).' /</td>
							</tr>
							<tr>
								<td style="text-align:center;font-size:10;color:#656565;">Заказчик</td>
								<td></td>
								<td></td>
								<td></td>
								<td style="text-align:center;font-size:10;color:#656565;">Подрядчик</td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>';
		if($p['doc_signature']){
			$footer.='<div>';
			$footer.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;margin-left:20mm;margin-top:-16mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
			$footer.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;margin-left:105mm;margin-top:-16mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			$footer.='</div>';
		}
		$mpdf->SetHTMLFooter($footer);
				
		//заголовок
		$body.='
			<h1 style="font-size:20;font-weight:400;text-align:center;padding-bottom:10px;padding-top:5mm;">Календарный план № '.$p['doc_numb'].'<br/><span style="color:#999;font-size:12">на выполнение строительных и ремонтно-отделочных работ</span></h1>
			<div>
				<table width="180mm">
					<tr>
						<td width="30%">г. Иваново</td>
						<td width="40%"></td>
						<td width="30%" style="text-align:right;">'.transform::date_f(dtu($p['doc_date'])).'</td>
					</tr>
				</table>
			</div>
		';

		//тело документа
		$sum=0;
		$nds18=0;
		$stage_numb=0;
		foreach($p['items'] as $_stage){
			//если есть работы по комнате
			if($_stage->services){
				$stage_numb++;
				$body.='
					<bookmark content="'.$stage_numb.' этап: '.($_stage->stage->name?$_stage->stage->name:'без названия').'" level="0"></bookmark>
					<table style="font-size:13;border-spacing:0;border-top:1px solid #bbb;margin-top:6mm;margin-bottom:3mm;border-right:1px solid #bbb;" width="180mm">
						<thead>
							<tr>
								<td width="40mm"></td>
								<td width="140mm"></td>
							</tr>
						</thead>
						<tr>
							<td colspan="2" style="background:#eee;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;text-align:center;">
								<h2 style="font-size:16;font-weight:400;margin-bottom:5;"><b>'.$stage_numb.' этап</b></h2>
							</td>
						</tr>
						<tr>
							<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;">Наименование</td>
							<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;"><b>'.($_stage->stage->name?$_stage->stage->name:'—').'</b></td>
						</tr>
						<tr>
							<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;">Начало работ</td>
							<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;"><b>'.($_stage->stage->date_start?transform::date_f(dtu($_stage->stage->date_start)):'').'</b></td>
						</tr>
						<tr>
							<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;">Завершение работ</td>
							<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;"><b>'.($_stage->stage->date_end?transform::date_f(dtu($_stage->stage->date_end)):'').'</b></td>
						</tr>	
					</table>
				';
				$body.='<table style="font-size:13;border-spacing:0;border-top:1px solid #bbb;margin-bottom:6mm;border-right:1px solid #bbb;" width="180mm">
							<thead>
								<tr>
									<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="88mm">Наименование работ</td>
									<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="17mm">Ед. изм.</td>
									<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="20mm">Объём</td>
									<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="25mm">Цена</td>
									<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;text-align:center;" width="30mm;">Сумма</td>
								</tr>
							</thead>
							<tbody>';
				//работы
				$pre_sum=0;
				$pre_nds18=0;
//				foreach($_stage->services as $_id=>$_smeta){
					/* $body.='
						<tr>
							<td colspan="5" style="padding:1mm;font-size:15;border-left:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;">
								Работы по смете № '.$documents->getInfo($_id)['m_documents_numb'].' от '.transform::date_f(dtu($documents->getInfo($_id)['m_documents_date'])).'
							</td>
						<tr>
					'; */
					$room_id=0;
					foreach($_stage->services as $_service){
						//название комнаты
						if($room_id!=$_service->room_id){
							$room=json_decode($documents->getInfo($p['smeta'])['m_documents_params'],true);
							$room=$room['items'][$_service->room_id]['room'];
							$body.='
								<tr>
									<td colspan="5" style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;">
										Помещение: '.($room['name']?$room['name']:'без названия').($room['square']?' — '.$room['square'].' м<sup>2</sup>':'').'
									</td>
								<tr>
							';
						}
						$room_id=$_service->room_id;
						$pre_sum+=$_service->sum;
						$pre_nds18+=$_service->sum*.20/1.20;
						$body.='	<tr>
										<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.$services->services_id[$_service->id][0]['m_services_name'].'</td>
										<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.$info->getUnits($services->services_id[$_service->id][0]['m_services_unit']).'</td>
										<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.$_service->count.'</td>
										<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.transform::price_o($_service->price).'</td>
										<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:center;">'.transform::price_o($_service->sum).'</td>
									</tr>';
						}
//				}
				$sum+=$pre_sum;
				$nds18+=$pre_nds18;
				//промежуточные итоги
				$body.='		<tr>
									<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого по этапу работ:</td>
									<td style="padding:1mm;text-align:center;border-bottom:1px solid #bbb;">'.transform::price_o($pre_sum).'</td>
								</tr>';
				//итог НДС 18%
				$body.=($orders->orders_id[$p['order']][0]['m_orders_nds']==18)?'
								<tr>
									<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС ('.$orders->orders_id[$p['order']][0]['m_orders_nds'].'%):</td>
									<td style="vertical-align:top;padding:1mm;border-bottom:1px solid #bbb;text-align:center;">'.transform::price_o($pre_nds18).'</td>
								</tr>':'';
				$body.='
							</tbody>
						</table>';
			}
		}
		
		$body.='
					<table style="font-size:13;border-spacing:0;margin-bottom:6mm;border-right:1px solid #bbb;" width="180mm">
						<thead>
							<tr>
								<td width="88mm"></td>
								<td width="17mm"></td>
								<td width="20mm"></td>
								<td width="25mm"></td>
								<td width="30mm;"></td>
							</tr>
						</thead>
						<tbody>';		
		//итоговые суммы
		$body.='			<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого по всем этапам:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum).'</td>
							</tr>';
		//скидка
		if($orders->orders_id[$p['order']][0]['m_orders_discount']>0)
			$body.='		<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Скидка '.$orders->orders_id[$p['order']][0]['m_orders_discount'].'%:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		//итого со скидкой
		if($orders->orders_id[$p['order']][0]['m_orders_discount']>0)
			$body.='		<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого со скидкой:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		//итог НДС 18%
		$body.=($orders->orders_id[$p['order']][0]['m_orders_nds']==18)?'
							<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС ('.$orders->orders_id[$p['order']][0]['m_orders_nds'].'%):</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($nds18-$nds18*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>':'';
		//итог БЕЗ НДС
		$body.=($orders->orders_id[$p['order']][0]['m_orders_nds']==-1)?'
							<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Без налога (НДС)</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:right;text-align:center;">—</td>
							</tr>':'';
		//всего к оплате
		$body.='			<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;font-weight:bold">Всего:</td>
								<td style="vertical-align:top;padding:1mm;border-top:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;font-weight:bold">'.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		$body.='
						</tbody>
					</table>';
		
		//сумма прописью
		$body.='<div style="font-size:13;padding-bottom:10px;">
					Общая стоимость Работ по Договору составляет '.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'&nbsp;<span style="font-family:dejavurouble;font-size:12.5">r</span><br>
					<span style="font-weight:bold">'.transform::summ_text($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).($orders->orders_id[$p['order']][0]['m_orders_nds']==18?(', в том числе НДС '.$orders->orders_id[$p['order']][0]['m_orders_nds'].'% — '.transform::summ_text($nds18-$nds18*$orders->orders_id[$p['order']][0]['m_orders_discount']/100,true,false)):'').'</span>
				</div>';
	
		//таблица для подписей и печати
		
		$body.='<table style="font-size:13;border-spacing:0;margin:10mm 0 2mm;line-height:4.5mm" width="180mm">
					<tr>
						<td width="180mm">
							<table style="font-size:13;border-spacing:0;margin:0;line-height:4.5mm" width="180mm">
								<thead>
									<tr>
										<td width="38mm"></td>
										<td width="7mm"></td>
										<td width="38mm"></td>
										<td width="7mm"></td>
										<td width="38mm"></td>
										<td width="7mm"></td>
										<td width="38mm"></td>
									</tr>
									<tr>
										<td colspan="3" style="font-weight:bold;padding-bottom:5mm">Заказчик</td>
										<td width="7mm"></td>
										<td colspan="3" style="font-weight:bold;padding-bottom:5mm">Подрядчик</td>
									</tr>
								</thead>
								<tr>
									<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$client['m_contragents_c_director_post'].'</td>
									<td></td>
									<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$org['m_contragents_c_director_post'].'</td>
								</tr>
								<tr>
									<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
									<td></td>
									<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
								</tr>
								<tr>
									<td></td>
									<td></td>
									<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($client['m_contragents_c_director_name']).'</td>
									<td></td>
									<td></td>
									<td></td>
									<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($org['m_contragents_c_director_name']).'</td>
								</tr>
								<tr>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
								</tr>
								<tr>
									<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
									<td></td>
									<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>';	
		
		//печати и подписи
		if($p['doc_signature']){
			$body.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:20mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
			$body.=$client['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:35mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=stamp"/></div>':'';
			$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:115mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:130mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
		}
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);

		$mpdf->WriteHTML($body);
		$mpdf->SetHTMLFooter('');
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}
	}
	
	//акт по форме всеумельца
	public static function act_vseumelec($p=array(
		'org'=>'',
		'client'=>'',
		'smeta'=>'',
		'base_numb'=>'',
		'base_date'=>'',
		'doc_date'=>'',
		'doc_numb'=>'',
		'doc_sum'=>'',
		'doc_logo'=>null,
		'doc_bar'=>null,
		'doc_signature'=>null,
		'doc_header_org_name'=>null,
		'doc_header_org_address'=>null,
		'doc_header_org_tel'=>null,
		'doc_header_org_email'=>null,
		'items'=>false
	),$filename){
		global $info,$contragents,$orders,$services,$documents;
		
		$smeta=$documents->getInfo($p['smeta']);
		$p['order']=$smeta['m_documents_order'];
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		
		$client['m_contragents_c_director_name']=$contragents->getDirector($client['m_contragents_id']);

		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('','',9,'dejavuserifcondensed',15,15,25,25,10,10);
		$mpdf->SetAuthor(transform::typography(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2))));
		$mpdf->SetCreator(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2)));
		$mpdf->SetSubject(strcode2utf('Акт сдачи-приёмки работ'));
		$mpdf->SetTitle(strcode2utf('Акт сдачи-приёмки работ № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));
		$mpdf->SetKeywords(strcode2utf('Акт сдачи-приёмки работ № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));		
		$mpdf->defaultCSS['P']['MARGIN']='0.9em 0';
		$mpdf->shrink_tables_to_fit=2;

		$body='';
		//количество не пустых товаров
		$count_all=1;
		
		//номер договора в хедере
		/* $header_numb='
			<div style="margin-top:2.5mm;font-size:10px;color:#656565;float:left;width:60%;">
				Акт сдачи-приёмки работ № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'<br/>
				к Договору № '.$p['base_numb'].' от '.transform::date_f(dtu($p['base_date'])).'
			</div>
		'; */
		$header_numb='
			<div style="margin-top:2.5mm;font-size:10px;color:#656565;float:left;width:60%;">
				Акт сдачи-приёмки работ № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'
			</div>
		';

		if($p['doc_bar'])
			$header_numb.='
			<div style="margin-top:2.5mm;float:right;width:35%;text-align:right;">
				<img src="/files/'.substr($filename,0,-16).'bar.png" style="height:24px"/>
			</div>';
		$mpdf->SetHTMLHeader($header_numb);
		
		//логотип, контакты, футер
		$header='
			<div style="width:100%;padding-bottom:5mm;border-bottom:1px solid #bbb;clear:both;">
				<div style="float:left;color:#333;margin-right:30px;width:30%;">';
		$header.=($p['doc_logo'])?'<img src="/img/'.$p['doc_logo'].'" width="120"/>':'';
		$header.='
				</div>
				<div style="width:45%;float:right;font-size:13;">
					<p style="margin:0 0 3px 0;padding:0;font-size:14;font-weight:bold;">'.transform::typography($p['doc_header_org_name']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_address']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$p['doc_header_org_email'].'">'.transform::typography($p['doc_header_org_email']).'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		
		$body.=$header;//$mpdf->SetHTMLHeader($header);
	
		$footer='<div style="float:left;width:10%;">
					<div style="'.($p['doc_header_bar']?'':'margin-top:2.4mm;').'font-size:12;color:#333333;">
						{PAGENO}
					</div>
				</div>
				<div style="width:90%;text-align:right">
					<table width="160mm" style="margin-top:2.5mm;">
						<thead>
							<tr>
								<td width="35mm"></td>
								<td width="2.5mm"></td>
								<td width="30mm"></td>
								<td width="20mm"></td>
								<td width="35mm"></td>
								<td width="2.5mm"></td>
								<td width="30mm"></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="border-bottom:1px solid #bbb;"></td>
								<td></td>
								<td>/ '.transform::fio($client['m_contragents_c_director_name']).' /</td>
								<td></td>
								<td style="border-bottom:1px solid #bbb;"></td>
								<td></td>
								<td>/ '.transform::fio($org['m_contragents_c_director_name']).' /</td>
							</tr>
							<tr>
								<td style="text-align:center;font-size:10;color:#656565;">Заказчик</td>
								<td></td>
								<td></td>
								<td></td>
								<td style="text-align:center;font-size:10;color:#656565;">Подрядчик</td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>';
		if($p['doc_signature']){
			$footer.='<div>';
			$footer.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;margin-left:20mm;margin-top:-16mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
			$footer.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;margin-left:105mm;margin-top:-16mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			$footer.='</div>';
		}
		$mpdf->SetHTMLFooter($footer);
				
		//заголовок
		$body.='
			<h1 style="font-size:20;font-weight:400;text-align:center;padding-bottom:10px;padding-top:5mm;">Акт сдачи-приёмки работ № '.$p['doc_numb'].'</h1>
			<div>
				<table width="180mm">
					<tr>
						<td width="30%">г. Иваново</td>
						<td width="40%"></td>
						<td width="30%" style="text-align:right;">'.transform::date_f(dtu($p['doc_date'])).'</td>
					</tr>
				</table>
			</div>
		';

		//тело документа
			//физлицо
			/* if(in_array(3,explode('|',$client['m_contragents_type'])))
				$body.='
					<p>Гражданин РФ <strong>'.$client['m_contragents_p_fio'].'</strong>, именуемый в дальнейшем &laquo;Заказчик&raquo;, с одной стороны, и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Подрядчик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, составили к Договору подряда № '.$p['base_numb'].' от '.transform::date_f(dtu($p['base_date'])).' (далее — &laquo;Договор&raquo;) настоящий акт  о нижеследующем:</p>
					<p>1. Подрядчик сдал, а Заказчик принял нижеперечисленные работы по Договору (далее — &laquo;Работы&raquo;):</p>
				';
			else{
				$body.='
					<p><strong>'.$client['m_contragents_c_name_full'].'</strong>, именуемый(ое) в дальнейшем &laquo;Заказчик&raquo;, в лице '.$client['m_contragents_c_director_post'].' '.$client['m_contragents_c_director_name_rp'].', действующего на основании '.$client['m_contragents_c_director_base'].', с одной стороны, и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Подрядчик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, составили к Договору подряда № '.$p['base_numb'].' от '.transform::date_f(dtu($p['base_date'])).' (далее — &laquo;Договор&raquo;) настоящий акт  о нижеследующем:</p>
					<p>1. Подрядчик сдал, а Заказчик принял нижеперечисленные работы по Договору (далее — &laquo;Работы&raquo;):</p>
				';
			} */
			if(in_array(3,explode('|',$client['m_contragents_type'])))
				$body.='
					<p>Гражданин РФ <strong>'.$client['m_contragents_p_fio'].'</strong>, именуемый в дальнейшем &laquo;Заказчик&raquo;, с одной стороны, и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Подрядчик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, составили к Договору подряда № '.$p['base_numb'].' от '.transform::date_f(dtu($p['base_date'])).' (далее — &laquo;Договор&raquo;) настоящий акт  о нижеследующем:</p>
					<p>1. Подрядчик сдал, а Заказчик принял нижеперечисленные работы (далее — &laquo;Работы&raquo;):</p>
				';
			else{
				$body.='
					<p><strong>'.$client['m_contragents_c_name_full'].'</strong>, именуемый(ое) в дальнейшем &laquo;Заказчик&raquo;, в лице '.$client['m_contragents_c_director_post'].' '.$client['m_contragents_c_director_name_rp'].', действующего на основании '.$client['m_contragents_c_director_base'].', с одной стороны, и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Подрядчик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, составили настоящий акт  о нижеследующем:</p>
					<p>1. Подрядчик сдал, а Заказчик принял нижеперечисленные работы (далее — &laquo;Работы&raquo;):</p>
				';
			}
		
		$sum=0;
		$nds18=0;
		$room_id=0;
		
		$body.='
			<table style="font-size:13;border-spacing:0;border-top:1px solid #bbb;margin-top:6mm;margin-bottom:6mm;border-right:1px solid #bbb;" width="180mm">
				<thead>
					<tr>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="88mm">Наименование работ</td>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="17mm">Ед. изм.</td>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="20mm">Объём</td>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="25mm">Цена</td>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;text-align:center;" width="30mm;">Сумма</td>
					</tr>
				</thead>
				<tbody>';
		//работы
		foreach($p['items'] as $_service){
			//если id комнаты изменился
			if($room_id!=$_service->room_id){
				//если текущая комната новая и предыдущая не пустая, выводим промежуточный итог
				if($room_id){
					//промежуточные итоги
					$body.='
					<tr>
						<td colspan="4" style="vertical-align:top;padding:1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">Итого по помещению:</td>
						<td style="padding:1mm;text-align:center;border-bottom:1px solid #bbb;">'.transform::price_o($pre_sum).'</td>
					</tr>';
					//итог НДС 18%
					/* $body.=($orders->orders_id[$p['order']][0]['m_orders_nds']==18)?'
					<tr>
						<td colspan="4" style="vertical-align:top;padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС (18%):</td>
						<td style="vertical-align:top;padding:1mm;border-bottom:1px solid #bbb;text-align:center;">'.transform::price_o($pre_nds18).'</td>
					</tr>':''; */
				}
				$pre_sum=0;
				$pre_nds18=0;
				$room=json_decode($documents->getInfo($p['smeta'])['m_documents_params'],true);
				$room=$room['items'][$_service->room_id]['room'];
				$body.='
					<tr>
						<td colspan="5" style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;">
							Помещение: '.($room['name']?$room['name']:'без названия').($room['square']?' — '.$room['square'].' м<sup>2</sup>':'').'
						</td>
					<tr>
				';
			}
			$room_id=$_service->room_id;
			$pre_sum+=$_service->sum;
			//$pre_nds18+=$_service->sum*.20/1.20;
			$body.='<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.$services->services_id[$_service->id][0]['m_services_name'].'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.$info->getUnits($services->services_id[$_service->id][0]['m_services_unit']).'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.$_service->count.'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.transform::price_o($_service->price).'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:center;">'.transform::price_o($_service->sum).'</td>
					</tr>';
			$sum+=$_service->sum;
			$nds18+=$_service->sum*.20/1.20;
		}
		//промежуточные итоги по последней комнате
		$body.='
					<tr>
						<td colspan="4" style="vertical-align:top;padding:1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">Итого по помещению:</td>
						<td style="padding:1mm;text-align:center;border-bottom:1px solid #bbb;">'.transform::price_o($pre_sum).'</td>
					</tr>';
		//итог НДС 18%
		/* $body.=($orders->orders_id[$p['order']][0]['m_orders_nds']==18)?'
					<tr>
						<td colspan="4" style="vertical-align:top;padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС (18%):</td>
						<td style="vertical-align:top;padding:1mm;border-bottom:1px solid #bbb;text-align:center;">'.transform::price_o($pre_nds18).'</td>
					</tr>':''; */

		$body.='
			</table>
				<table style="font-size:13;border-spacing:0;margin-bottom:6mm;border-right:1px solid #bbb;" width="180mm">
					<thead>
						<tr>
							<td width="88mm"></td>
							<td width="17mm"></td>
							<td width="20mm"></td>
							<td width="25mm"></td>
							<td width="30mm;"></td>
						</tr>
					</thead>
					<tbody>';		
		//итоговые суммы
		$body.='		<tr>
							<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого по акту:</td>
							<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum).'</td>
						</tr>';
		//скидка
		if($orders->orders_id[$p['order']][0]['m_orders_discount']>0)
			$body.='		<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Скидка '.$orders->orders_id[$p['order']][0]['m_orders_discount'].'%:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		//итого со скидкой
		if($orders->orders_id[$p['order']][0]['m_orders_discount']>0)
			$body.='		<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого со скидкой:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		//итог НДС 18%
		$body.=($orders->orders_id[$p['order']][0]['m_orders_nds']==18)?'
							<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС ('.$orders->orders_id[$p['order']][0]['m_orders_nds'].'%):</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($nds18-$nds18*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>':'';
		//итог БЕЗ НДС
		$body.=($orders->orders_id[$p['order']][0]['m_orders_nds']==-1)?'
							<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Без налога (НДС)</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:right;text-align:center;">—</td>
							</tr>':'';
		//всего к оплате
		$body.='			<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;font-weight:bold">Всего:</td>
								<td style="vertical-align:top;padding:1mm;border-top:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;font-weight:bold">'.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		$body.='
						</tbody>
					</table>';
		
		//продолжение акта
		$body.='
				<p>2. Работы выполнены в полном объёме, своевременно и надлежащим образом.</p>
				<p>3. Претензий у Заказчика к Подрядчику не имеется.</p>
				<p>4. Общая стоимость Работ по настоящему акту составляет '.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'&nbsp;<span style="font-family:dejavurouble;font-size:12.5">r</span> ('.transform::summ_text($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).($orders->orders_id[$p['order']][0]['m_orders_nds']==18?(', в том числе НДС '.$orders->orders_id[$p['order']][0]['m_orders_nds'].'% — '.transform::summ_text($nds18-$nds18*$orders->orders_id[$p['order']][0]['m_orders_discount']/100,true,false)):'').').</p>
				<p>5. Настоящий акт составлен в двух экземплярах, имеющих равную юридическую силу, по одному для каждой из Сторон.</p>';
	
		//таблица для подписей и печати
		
		$body.='<table style="font-size:13;border-spacing:0;margin:10mm 0 2mm;line-height:4.5mm" width="180mm">
					<tr>
						<td width="180mm">
							<table style="font-size:13;border-spacing:0;margin:0;line-height:4.5mm" width="180mm">
								<thead>
									<tr>
										<td width="38mm"></td>
										<td width="7mm"></td>
										<td width="38mm"></td>
										<td width="7mm"></td>
										<td width="38mm"></td>
										<td width="7mm"></td>
										<td width="38mm"></td>
									</tr>
									<tr>
										<td colspan="3" style="font-weight:bold;padding-bottom:5mm">Заказчик</td>
										<td width="7mm"></td>
										<td colspan="3" style="font-weight:bold;padding-bottom:5mm">Подрядчик</td>
									</tr>
								</thead>
								<tr>
									<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$client['m_contragents_c_director_post'].'</td>
									<td></td>
									<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$org['m_contragents_c_director_post'].'</td>
								</tr>
								<tr>
									<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
									<td></td>
									<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
								</tr>
								<tr>
									<td></td>
									<td></td>
									<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($client['m_contragents_c_director_name']).'</td>
									<td></td>
									<td></td>
									<td></td>
									<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($org['m_contragents_c_director_name']).'</td>
								</tr>
								<tr>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
								</tr>
								<tr>
									<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
									<td></td>
									<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>';	
		
		//печати и подписи
		if($p['doc_signature']){
			$body.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:20mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
			$body.=$client['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:35mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=stamp"/></div>':'';
			$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:115mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:130mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
		}
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);

		$mpdf->WriteHTML($body);
		$mpdf->SetHTMLFooter('');
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}
	}
	
	//ведомость по форме всеумельца
	public static function vedomost_vseumelec($p=array(
		'org'=>'',
		'client'=>'',
		'smeta'=>'',
		'noprice'=>'',
		'base_numb'=>'',
		'base_date'=>'',
		'doc_date'=>'',
		'doc_numb'=>'',
		'doc_sum'=>'',
		'doc_logo'=>null,
		'doc_bar'=>null,
		'doc_signature'=>null,
		'doc_header_org_name'=>null,
		'doc_header_org_address'=>null,
		'doc_header_org_tel'=>null,
		'doc_header_org_email'=>null,
		'items'=>false
	),$filename){
		global $info,$contragents,$orders,$services,$documents;
		
		$smeta=$documents->getInfo($p['smeta']);
		$p['order']=$smeta['m_documents_order'];
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		
		$client['m_contragents_c_director_name']=$contragents->getDirector($client['m_contragents_id']);

		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('','',9,'dejavuserifcondensed',15,15,25,25,10,10);
		$mpdf->debug = true;
		$mpdf->SetAuthor(transform::typography('vseumelec.ru'));
		$mpdf->SetCreator('vseumelec.ru');
		$mpdf->SetSubject(strcode2utf('Ведомость работ (рабочая)'));
		$mpdf->SetTitle(strcode2utf('Ведомость работ (рабочая) № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));
		$mpdf->SetKeywords(strcode2utf('Ведомость работ (рабочая) № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));		
		$mpdf->defaultCSS['P']['MARGIN']='0.9em 0';

		$body='';
		//количество не пустых товаров
		$count_all=1;
		
		//номер договора в хедере
		$header_numb='
			<div style="margin-top:2.5mm;font-size:10px;color:#656565;float:left;width:60%;">
				Ведомость работ (рабочая) № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'<br/>
				к Договору № '.$p['base_numb'].' от '.transform::date_f(dtu($p['base_date'])).'
			</div>
		';

		if($p['doc_bar'])
			$header_numb.='
			<div style="margin-top:2.5mm;float:right;width:35%;text-align:right;">
				<img src="/files/'.substr($filename,0,-21).'bar.png" style="height:24px"/>
			</div>';
		$mpdf->SetHTMLHeader($header_numb);
		
		//логотип, контакты, футер
		$header='
			<div style="width:100%;padding-bottom:5mm;border-bottom:1px solid #bbb;clear:both;">
				<div style="float:left;color:#333;margin-right:30px;width:30%;">';
		$header.=$p['doc_logo']?'<img src="/img/'.$p['doc_logo'].'" width="120"/>':'';
		$header.='
				</div>
				<div style="width:45%;float:right;font-size:13;">
					<p style="margin:0 0 3px 0;padding:0;font-size:14;font-weight:bold;">'.transform::typography($p['doc_header_org_name']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_address']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$p['doc_header_org_email'].'">'.transform::typography($p['doc_header_org_email']).'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		
		$body.=$header;//$mpdf->SetHTMLHeader($header);
	
		/* $footer='<div style="float:left;width:10%;">
					<div style="'.($p['doc_header_bar']?'':'margin-top:2.4mm;').'font-size:12;color:#333333;">
						{PAGENO}
					</div>
				</div>
				<div style="width:90%;text-align:right">
					<table width="160mm" style="margin-top:2.5mm;">
						<thead>
							<tr>
								<td width="35mm"></td>
								<td width="2.5mm"></td>
								<td width="30mm"></td>
								<td width="20mm"></td>
								<td width="35mm"></td>
								<td width="2.5mm"></td>
								<td width="30mm"></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="border-bottom:1px solid #bbb;"></td>
								<td></td>
								<td>/ '.transform::fio($client['m_contragents_c_director_name']).' /</td>
								<td></td>
								<td style="border-bottom:1px solid #bbb;"></td>
								<td></td>
								<td>/ '.transform::fio($org['m_contragents_c_director_name']).' /</td>
							</tr>
							<tr>
								<td style="text-align:center;font-size:10;color:#656565;">Заказчик</td>
								<td></td>
								<td></td>
								<td></td>
								<td style="text-align:center;font-size:10;color:#656565;">Подрядчик</td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>';
		if($p['doc_signature']){
			$footer.='<div>';
			$footer.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;margin-left:20mm;margin-top:-16mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
			$footer.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;margin-left:105mm;margin-top:-16mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			$footer.='</div>';
		}
		$mpdf->SetHTMLFooter($footer); */
				
		//заголовок
		$body.='
			<h1 style="font-size:20;font-weight:400;text-align:center;padding-bottom:10px;padding-top:5mm;">Ведомость работ (рабочая) № '.$p['doc_numb'].'</h1>
			<div>
				<table width="180mm">
					<tr>
						<td width="30%">г. Иваново</td>
						<td width="40%"></td>
						<td width="30%" style="text-align:right;">'.transform::date_f(dtu($p['doc_date'])).'</td>
					</tr>
				</table>
			</div>
		';

		/* //тело документа
			//физлицо
			if(in_array(3,explode('|',$client['m_contragents_type'])))
				$body.='
					<p>Гражданин РФ <strong>'.$client['m_contragents_p_fio'].'</strong>, именуемый в дальнейшем &laquo;Заказчик&raquo;, с одной стороны, и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Подрядчик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, составили к Договору подряда № '.$p['base_numb'].' от '.transform::date_f(dtu($p['base_date'])).' (далее — &laquo;Договор&raquo;) настоящий акт  о нижеследующем:</p>
					<p>1. Подрядчик сдал, а Заказчик принял нижеперечисленные работы по Договору (далее — &laquo;Работы&raquo;):</p>
				';
			else{
				$body.='
					<p><strong>'.$client['m_contragents_c_name_full'].'</strong>, именуемый(ое) в дальнейшем &laquo;Заказчик&raquo;, в лице '.$client['m_contragents_c_director_post'].' '.$client['m_contragents_c_director_name_rp'].', действующего на основании '.$client['m_contragents_c_director_base'].', с одной стороны, и '.$org['m_contragents_c_name_full'].', именуемое в дальнейшем &laquo;Подрядчик&raquo;, в лице '.$org['m_contragents_c_director_post'].' '.$org['m_contragents_c_director_name_rp'].', действующего на основании '.$org['m_contragents_c_director_base'].', с другой стороны, вместе именуемые &laquo;Стороны&raquo;, а индивидуально &ndash; &laquo;Сторона&raquo;, составили к Договору подряда № '.$p['base_numb'].' от '.transform::date_f(dtu($p['base_date'])).' (далее — &laquo;Договор&raquo;) настоящий акт  о нижеследующем:</p>
					<p>1. Подрядчик сдал, а Заказчик принял нижеперечисленные работы по Договору (далее — &laquo;Работы&raquo;):</p>
				';
			} */
		
		$sum=0;
		$nds18=0;
		$room_id=0;
		
		$body.='
			<table style="font-size:13;border-spacing:0;border-top:1px solid #bbb;margin-top:6mm;margin-bottom:6mm;border-right:1px solid #bbb;" width="180mm">
				<thead>
					<tr>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="'.(!$p['noprice']?'88':'143').'mm">Наименование работ</td>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="17mm">Ед. изм.</td>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;'.(!$p['noprice']?'border-right:1px solid #bbb;':'').'text-align:center;" width="20mm">Объём</td>
						'.($p['noprice']?'':'<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="25mm">Цена</td>
						<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;text-align:center;" width="30mm;">Сумма</td>').'
					</tr>
				</thead>
				<tbody>';
		//работы
		foreach($p['items'] as $_service){
			//если id комнаты изменился
			if($room_id!=$_service->room_id){
				//если текущая комната новая и предыдущая не пустая, выводим промежуточный итог
				if($room_id){
					if(!$p['noprice']){
						//промежуточные итоги
						$body.='
						<tr>
							<td colspan="4" style="vertical-align:top;padding:1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">Итого по помещению:</td>
							<td style="padding:1mm;text-align:center;border-bottom:1px solid #bbb;">'.transform::price_o($pre_sum).'</td>
						</tr>';
						//итог НДС 18%
						/* $body.=($orders->orders_id[$p['order']][0]['m_orders_nds']==18)?'
						<tr>
							<td colspan="4" style="vertical-align:top;padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС (18%):</td>
							<td style="vertical-align:top;padding:1mm;border-bottom:1px solid #bbb;text-align:center;">'.transform::price_o($pre_nds18).'</td>
						</tr>':''; */
					}
				}
				$pre_sum=0;
				$pre_nds18=0;
				$room=json_decode($documents->getInfo($p['smeta'])['m_documents_params'],true);
				$room=$room['items'][$_service->room_id]['room'];
				$body.='
					<tr>
						<td colspan="'.(!$p['noprice']?'5':'3').'" style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;">
							Помещение: '.($room['name']?$room['name']:'без названия').($room['square']?' — '.$room['square'].' м<sup>2</sup>':'').'
						</td>
					<tr>
				';
			}
			$room_id=$_service->room_id;
			$pre_sum+=$_service->sum;
			//$pre_nds18+=$_service->sum*.20/1.20;
			$body.='<tr>
						<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.$services->services_id[$_service->id][0]['m_services_name'].'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.$info->getUnits($services->services_id[$_service->id][0]['m_services_unit']).'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;'.(!$p['noprice']?'border-right:1px solid #bbb;':'').'text-align:center;">'.$_service->count.'</td>
						'.($p['noprice']?'':'<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.transform::price_o($_service->price).'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:center;">'.transform::price_o($_service->sum).'</td>').'
					</tr>';
			$sum+=$_service->sum;
			$nds18+=$_service->sum*.20/1.20;
		}
		//промежуточные итоги по последней комнате
		if(!$p['noprice']){
			$body.='
						<tr>
							<td colspan="4" style="vertical-align:top;padding:1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">Итого по помещению:</td>
							<td style="padding:1mm;text-align:center;border-bottom:1px solid #bbb;">'.transform::price_o($pre_sum).'</td>
						</tr>';
			//итог НДС 18%
			/* $body.=($orders->orders_id[$p['order']][0]['m_orders_nds']==18)?'
						<tr>
							<td colspan="4" style="vertical-align:top;padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС (18%):</td>
							<td style="vertical-align:top;padding:1mm;border-bottom:1px solid #bbb;text-align:center;">'.transform::price_o($pre_nds18).'</td>
						</tr>':''; */
			$body.='
				</table>
					<table style="font-size:13;border-spacing:0;margin-bottom:6mm;border-right:1px solid #bbb;" width="180mm">
						<thead>
							<tr>
								<td width="88mm"></td>
								<td width="17mm"></td>
								<td width="20mm"></td>
								<td width="25mm"></td>
								<td width="30mm;"></td>
							</tr>
						</thead>
						<tbody>';		
			//обнуление скидки для рабочих			
			$orders->orders_id[$p['order']][0]['m_orders_discount']=0;
			//итоговые суммы
			$body.='		<tr>
								<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum).'</td>
							</tr>';
			/* //скидка
			if($orders->orders_id[$p['order']][0]['m_orders_discount']>0)
				$body.='		<tr>
									<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Скидка '.$orders->orders_id[$p['order']][0]['m_orders_discount'].'%:</td>
									<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
								</tr>';
			//итого со скидкой
			if($orders->orders_id[$p['order']][0]['m_orders_discount']>0)
				$body.='		<tr>
									<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого со скидкой:</td>
									<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
								</tr>'; */
			//итог НДС 18%
			$body.=($orders->orders_id[$p['order']][0]['m_orders_nds']==18)?'
								<tr>
									<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС ('.$orders->orders_id[$p['order']][0]['m_orders_nds'].'%):</td>
									<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($nds18-$nds18*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
								</tr>':'';
			//итог БЕЗ НДС
			$body.=($orders->orders_id[$p['order']][0]['m_orders_nds']==-1)?'
								<tr>
									<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Без налога (НДС)</td>
									<td style="padding:1mm;border-top:1px solid #bbb;text-align:right;text-align:center;">—</td>
								</tr>':'';
			//всего к оплате
			$body.='			<tr>
									<td colspan="4" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;font-weight:bold">Всего:</td>
									<td style="vertical-align:top;padding:1mm;border-top:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;font-weight:bold">'.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
								</tr>';
			$body.='
							</tbody>
						</table>';
			
			/* //продолжение акта
			$body.='
					<p>2. Работы выполнены надлежащим образом в сроки и объёмах, предусмотренных Договором.</p>
					<p>3. Претензий у Заказчика к Подрядчику не имеется.</p>
					<p>4. Общая стоимость Работ по настоящему акту составляет '.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'&nbsp;<span style="font-family:dejavurouble;font-size:12.5">r</span> ('.transform::summ_text($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).($orders->orders_id[$p['order']][0]['m_orders_nds']==18?(', в том числе НДС 18% — '.transform::summ_text($nds18-$nds18*$orders->orders_id[$p['order']][0]['m_orders_discount']/100,true,false)):'').').</p>
					<p>5. Настоящий акт составлен в двух экземплярах, имеющих равную юридическую силу, по одному для каждой из Сторон.</p>'; */
		
			/* //таблица для подписей и печати
			$body.='<table style="font-size:13;border-spacing:0;margin:10mm 0 2mm;line-height:4.5mm" width="180mm">
						<tr>
							<td width="180mm">
								<table style="font-size:13;border-spacing:0;margin:0;line-height:4.5mm" width="180mm">
									<thead>
										<tr>
											<td width="38mm"></td>
											<td width="7mm"></td>
											<td width="38mm"></td>
											<td width="7mm"></td>
											<td width="38mm"></td>
											<td width="7mm"></td>
											<td width="38mm"></td>
										</tr>
										<tr>
											<td colspan="3" style="font-weight:bold;padding-bottom:5mm">Заказчик</td>
											<td width="7mm"></td>
											<td colspan="3" style="font-weight:bold;padding-bottom:5mm">Подрядчик</td>
										</tr>
									</thead>
									<tr>
										<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$client['m_contragents_c_director_post'].'</td>
										<td></td>
										<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$org['m_contragents_c_director_post'].'</td>
									</tr>
									<tr>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
										<td></td>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
									</tr>
									<tr>
										<td></td>
										<td></td>
										<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($client['m_contragents_c_director_name']).'</td>
										<td></td>
										<td></td>
										<td></td>
										<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($org['m_contragents_c_director_name']).'</td>
									</tr>
									<tr>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
										<td></td>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
										<td></td>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
										<td></td>
										<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
									</tr>
									<tr>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
										<td></td>
										<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';	 */
			
			/* //печати и подписи
			if($p['doc_signature']){
				$body.=$client['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:20mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=director"/></div>':'';
				$body.=$client['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:35mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$client['m_contragents_id'].'&type=stamp"/></div>':'';
				$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:115mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
				$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:130mm;margin-top:-20mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
			} */
		}
		else{
			$body.='
						</tbody>
					</table>';
		}
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);

		$mpdf->WriteHTML($body);

		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}
	}
	
	public static function act($p=array(
		'org_inn'=>'',
		'org_kpp'=>'',
		'org_name'=>'',
		'org_address'=>'',
		'org_tel'=>'',
		'org_director_post'=>'',
		'org_director_name'=>'',
		'org_director_signature'=>'',
		'org_stamp'=>'',
		'client_inn'=>'',
		'client_kpp'=>'',
		'client_name'=>'',
		'client_address_u'=>'',
		'client_tel'=>'',
		'doc_numb'=>'',
		'doc_date'=>'',
		'doc_base'=>'',
		'doc_sum'=>'',
		'doc_nds10'=>'',
		'doc_nds18'=>'',
		'doc_message_info'=>'',
		'doc_logo'=>null,
		'doc_header_org_name'=>null,
		'doc_header_org_address'=>null,
		'doc_header_org_tel'=>null,
		'doc_header_org_email'=>null,
		'doc_header_bar'=>null,
		'items'=>false
	),$filename){
		
		$filename=explode('/',$filename);
		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('','',13,'dejavuserifcondensed',15,15,30,25,5,10);
		$mpdf->SetAuthor(transform::typography($company[0]['name']));
		$mpdf->SetCreator('www.222111.ru');
		$mpdf->SetSubject(strcode2utf('Акт оказанных услуг (выполненных работ)'));
		$mpdf->SetTitle(strcode2utf('Акт № '.$p['doc_numb'].' от '.transform::date_f($p['doc_date'])));
		$mpdf->SetKeywords(strcode2utf('Акт № '.$p['doc_numb'].' от '.transform::date_f($p['doc_date'])));
		
		$body='';
		
		//количество не пустых товаров
		$count_all=1;
		
		//логотип, контакты, футер
		$header='
			<div style="width:100%;padding-bottom:0;">
				<div style="float:left;font-size:12;color:#333;margin-right:30px;width:30%;">';
		$header.=($p['doc_logo'])?'<img src="http://'.$_SERVER['HTTP_HOST'].'/img/'.$p['doc_logo'].'" width="150"/>':'';
		$header.='
				</div>
				<div style="width:40%;float:right">
					<p style="margin:0 0 3px 0;padding:0;font-size:14;font-weight:bold;">'.transform::typography($p['doc_header_org_name']).'</p>
					<p style="margin:0 0 3px 0;padding:0;font-size:11;">'.transform::typography($p['doc_header_org_address']).'</p>
					<p style="margin:0 0 3px 0;padding:0;font-size:11;">'.transform::typography($p['doc_header_org_tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;font-size:11;"><a href="mailto:'.$p['doc_header_org_email'].'">'.transform::typography($p['doc_header_org_email']).'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		$mpdf->SetHTMLHeader($header);
		$footer='<div style="float:left;width:39%;">
					<div style="margin-top:2.5mm;font-size:10px;color:#656565;">
						Акт № '.$p['doc_numb'].' от '.transform::date_f($p['doc_date']).'
					</div>
					<div style="'.($p['doc_header_bar']?'':'margin-top:2.4mm;').'font-size:12;color:#333333;">
						{PAGENO}'.($p['doc_header_bar']?'<img src="http://'.$_SERVER['HTTP_HOST'].'/files/act/'.$filename[0].'/bar.png" height="20"/>':'').'
					</div>
				</div>
				<div style="float:right;width:60%;text-align:right;font-size:10px;color:#656565;">
					<a href="http://'.$_SERVER['HTTP_HOST'].'">
						<img src="http://'.$_SERVER['HTTP_HOST'].'/img/logo_docs.png" height="30"/>
					</a>
					<br>Документ сгенерирован сервисом <a href="http://'.$_SERVER['HTTP_HOST'].'" style="color:#656565;text-decoration:none;">'.$_SERVER['HTTP_HOST'].'</a>
				</div>';
		$mpdf->SetHTMLFooter($footer);
		
		//заголовок
		$body.='<h1 style="font-size:20;font-weight:400;text-align:left;padding-bottom:10px;border-bottom:2px solid #000;">Акт № '.$p['doc_numb'].' от '.transform::date_f($p['doc_date']).'</h1>';
			
		//поставщик и покупатель
		$body.='<table style="font-size:13;border-spacing:0;margin-bottom:4mm;line-height:4.5mm" width="180mm">
					<thead>
						<tr>
							<td width="30mm"></td>
							<td width="150mm"></td>
						</tr>
					</thead>
					<tr>
						<td style="vertical-align:top;padding:0.5mm;color:#666">Исполнитель</td>
						<td style="vertical-align:top;padding:0.5mm;color:#000;width:80mm;">'.($p['org_name']?$p['org_name']:'').($p['org_inn']?', ИНН&nbsp;'.$p['org_inn']:'').($p['org_kpp']?', КПП&nbsp;'.$p['org_kpp']:'').($p['org_address']?', '.$p['org_address']:'').($p['org_tel']?', тел.:&nbsp;'.$p['org_tel']:'').'</td>
					</tr>
					<tr>
						<td style="vertical-align:top;padding:0.5mm;color:#666">Заказчик</td>
						<td style="vertical-align:top;padding:0.5mm;color:#000;width:80mm;">'.($p['client_name']?$p['client_name']:'').($p['client_inn']?', ИНН&nbsp;'.$p['client_inn']:'').($p['client_kpp']?', КПП&nbsp;'.$p['client_kpp']:'').($p['client_address_u']?', '.$p['client_address_u']:'').($p['client_tel']?', тел.:&nbsp;'.$p['client_tel']:'').'</td>
					</tr>
					<tr>
						<td style="vertical-align:top;padding:0.5mm;color:#666">Основание</td>
						<td style="vertical-align:top;padding:0.5mm;color:#000;width:80mm;">'.$p['doc_base'].'</td>
					</tr>
				</table>';
		
		//заголовок таблицы
		$body.='<table style="font-size:13;border-spacing:0;border-top:1px solid #bbb;border-right:1px solid #bbb;margin-bottom:6mm;" width="180mm">
					<thead>
						<tr>
							<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="10mm">№</td>
							<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="80mm">Наименование работы (услуги)</td>
							<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="18mm">Кол-во</td>
							<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="17mm">Ед. изм.</td>
							<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="25mm;">Цена</td>';
		$body.='			<td style="background:#eee;font-size:13;padding:2mm 1mm;border-bottom:1px solid #bbb;text-align:center;" width="30mm">Сумма</td>
						</tr>
					</thead>';
		//товарные позиции
		if($p['items'])
			foreach($p['items'] as $k=>$v){
				$body.='<tr>
							<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.($k+1).'</td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.$v['name'].'</td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.$v['count'].'</td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.$v['unit'].'</td>
							<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">'.transform::price_o($v['price']).'</td>';
				$body.='	<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:right;">'.transform::price_o($v['price']*$v['count']).'</td>
						</tr>';
			}
		//итоговые суммы
		$body.='		<tr>
							<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого:</td>
							<td style="padding:1mm;text-align:right;">'.transform::price_o($p['doc_sum']).'</td>
						</tr>';
		//итог НДС 10%
		$body.=($p['doc_nds10'])?'
						<tr>
							<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС (10%):</td>
							<td style="padding:1mm;border-top:1px solid #bbb;text-align:right;">'.transform::price_o($p['doc_nds10']).'</td>
						</tr>':'';
		//итог НДС 18%
		$body.=($p['doc_nds18'])?'
						<tr>
							<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС ('.$orders->orders_id[$p['order']][0]['m_orders_nds'].'%):</td>
							<td style="padding:1mm;border-top:1px solid #bbb;text-align:right;">'.transform::price_o($p['doc_nds18']).'</td>
						</tr>':'';
		//итог БЕЗ НДС
		$body.=(!$p['doc_nds18']&&!$p['doc_nds10'])?'
						<tr>
							<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Без налога (НДС)</td>
							<td style="padding:1mm;border-top:1px solid #bbb;text-align:right;text-align:center;">—</td>
						</tr>':'';
		//всего к оплате
		$body.='		<tr>
							<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;font-weight:bold">Всего:</td><td style="vertical-align:top;padding:1mm;border-top:1px solid #bbb;border-bottom:1px solid #bbb;text-align:right;font-weight:bold">'.transform::price_o($p['doc_sum']).'</td>
						</tr>';
		$body.='</table>';
		
		//сумма прописью
		$body.='<div style="font-size:13;padding-bottom:10px;">
					Всего оказано услуг '.sizeof($p['items']).' на сумму '.transform::price_o($p['doc_sum']).'&nbsp;<span style="font-family:dejavurouble;font-size:12.5">r</span><br>
					<span style="font-weight:bold">'.transform::summ_text($p['doc_sum']).'</span>
				</div>';
				
		//окончание акта
		$body.='<div style="font-size:13;padding-bottom:10px;border-bottom:2px solid #222;">
					Вышеперечисленные услуги выполнены полностью и&nbsp;в&nbsp;срок. Заказчик претензий по&nbsp;объему, качеству и&nbsp;срокам оказания услуг не&nbsp;имеет.
				</div>';
	
		//таблица для подписей и печати
		$body.='<table style="font-size:13;border-spacing:0;margin:10mm 0 2mm;line-height:4.5mm" width="180mm">
					<tr>
						<td width="180mm">
							<table style="font-size:13;border-spacing:0;margin:0;line-height:4.5mm" width="180mm">
								<thead>
									<tr>
										<td width="38mm"></td>
										<td width="7mm"></td>
										<td width="38mm"></td>
										<td width="7mm"></td>
										<td width="38mm"></td>
										<td width="7mm"></td>
										<td width="38mm"></td>
									</tr>
									<tr>
										<td colspan="3" style="font-weight:bold;padding-bottom:5mm">Исполнитель</td>
										<td width="7mm"></td>
										<td colspan="3" style="font-weight:bold;padding-bottom:5mm">Заказчик</td>
									</tr>
								</thead>
								<tr>
									<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$p['org_director_post'].'</td>
									<td></td>
									<td colspan="3" style="vertical-align:bottom;padding:0.5mm;text-align:center"></td>
								</tr>
								<tr>
									<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
									<td></td>
									<td colspan="3" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
								</tr>
								<tr>
									<td></td>
									<td></td>
									<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($p['org_director_name']).'</td>
									<td></td>
									<td></td>
									<td></td>
									<td style="vertical-align:bottom;padding:0.5mm;text-align:center"></td>
								</tr>
								<tr>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
								</tr>
								<tr>
									<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
									<td></td>
									<td colspan="3" style="vertical-align:top;padding:0.5mm;text-align:center;color:#666">м. п.</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>';
				
		//печатный оттиск и подписи
		$body.=($p['org_stamp'])?'<div style="overflow:visible;position:absolute;left:35mm;margin-top:-30mm;"><img src="'.$p['org_stamp'].'"/></div>':'';	
		$body.=($p['org_director_signature'])?'<div style="overflow:visible;position:absolute;left:15mm;margin-top:-30mm;"><img src="'.$p['org_director_signature'].'"/></div>':'';
		
		//комментарий в конце счета
		$body.='<div style="font-size:13;color:#666;text-align:left;margin-top:20px;">'.$p['doc_message_info'].'</div>';
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);

		$mpdf->WriteHTML($body);
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/act/'.implode('/',$filename).'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}
	}
	
	//счет на оплату
	public static function invoice($p=array(
		'org'=>'',
		'org_bank'=>'',
		'client'=>'',
		'order'=>'',
		'doc_date_expire'=>'',
		'doc_attention'=>'',
		'doc_terms'=>'',
		'base_numb'=>'',
		'base_date'=>'',
		'doc_date'=>'',
		'doc_numb'=>'',
		'doc_logo'=>null,
		'doc_bar'=>null,
		'doc_signature'=>null,
		'doc_header_org_name'=>null,
		'doc_header_org_address'=>null,
		'doc_header_org_tel'=>null,
		'doc_header_org_email'=>null,
		'items'=>false
	),$filename){
		global $info,$contragents,$orders,$services,$products,$banks;
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		
		$client['m_contragents_c_director_name']=$contragents->getDirector($client['m_contragents_id']);

		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('','',9,'roboto',15,15,15,15,5,0);
		$mpdf->SetAuthor(transform::typography(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2))));
		$mpdf->SetCreator(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2)));
		$mpdf->SetSubject(strcode2utf('Счёт на оплату'));
		$mpdf->SetTitle(strcode2utf('Счёт на оплату № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));
		$mpdf->SetKeywords(strcode2utf('Счёт на оплату № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date']))));		
		$mpdf->defaultCSS['P']['MARGIN']='0.9em 0';
		$mpdf->shrink_tables_to_fit=0;
		
		$body='';

		//номер документа в хедере
		$header_numb='
			<div style="margin-top:2.5mm;font-size:10px;color:#656565;float:left;width:60%;">
				Счёт на оплату № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'<br/>Страница {PAGENO}
			</div>
		';
		if($p['doc_bar'])
			$header_numb.='
			<div style="margin-top:2.5mm;float:right;width:35%;text-align:right;">
				<img src="/files/'.substr($filename,0,-7).'bar.png" style="height:24px"/>
			</div>';
		$mpdf->SetHTMLHeader($header_numb);

		
		//логотип, контакты на первой странице
		$header='
			<div style="width:100%;padding-bottom:2mm;clear:both;">
				<div style="float:left;color:#333;margin-right:30px;width:30%;">';
		$header.=($p['doc_logo'])?'<img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=logo" width="150"/>':'';
		$header.='
				</div>
				<div style="width:45%;float:right;font-size:11;">
					<p style="margin:0 0 3px 0;padding:0;font-weight:bold;">'.transform::typography($p['doc_header_org_name']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_address']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$p['doc_header_org_email'].'">'.transform::typography($p['doc_header_org_email']).'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		
		$body.=$header;
		
		//реквизиты
		$body.='<table style="font-size:13;border-spacing:0;margin-bottom:-2mm;border:1px solid #bbb;" width="180mm">
					<thead>
						<tr>
							<td width="50mm" style="padding:0"></td>
							<td width="50mm" style="padding:0"></td>
							<td width="20mm" style="padding:0"></td>
							<td width="70mm" style="padding:0"></td>
						</tr>
					</thead>
					<tr>
						<td colspan="2" rowspan="2" style="vertical-align:top;border-right:1px solid #bbb;padding:0.5mm 1mm;color:#000">'.$info->getDefaultRS($org['m_contragents_id'])['m_contragents_rs_bank'].'</td>
						<td style="border-right:1px solid #bbb;padding:0.5mm 1mm;vertical-align:top;color:#666">БИК</td>
						<td style="vertical-align:top;padding:0.5mm 1mm;color:#000">'.$info->getDefaultRS($org['m_contragents_id'])['m_contragents_rs_bik'].'</td>
					</tr>
					<tr>
						<td style="border-right:1px solid #bbb;border-top:1px solid #bbb;padding:0.5mm 1mm;vertical-align:top;color:#666">Сч. №</td>
						<td style="vertical-align:top;padding:0.5mm 1mm;color:#000">'.$info->getDefaultRS($org['m_contragents_id'])['m_contragents_rs_ks'].'</td>
					</tr>
					<tr>
						<td colspan="2" style="vertical-align:top;border-right:1px solid #bbb;padding:0.5mm 1mm;color:#666">Банк получателя</td>
						<td style="border-right:1px solid #bbb;"></td>
						<td></td>
					</tr>
					<tr>
						<td style="vertical-align:top;border-right:1px solid #bbb;border-top:1px solid #bbb;padding:0.5mm 1mm;color:#666">ИНН&nbsp;<span style="color:#000">'.$org['m_contragents_c_inn'].'</span></td>
						<td style="vertical-align:top;border-right:1px solid #bbb;border-top:1px solid #bbb;padding:0.5mm 1mm;color:#666">КПП&nbsp;<span style="color:#000">'.$org['m_contragents_c_kpp'].'</span></td>
						<td style="vertical-align:top;padding:0.5mm 1mm;color:#666;border-right:1px solid #bbb;border-top:1px solid #bbb;">Сч. №</td>
						<td style="vertical-align:top;padding:0.5mm 1mm;color:#000;border-top:1px solid #bbb;">'.$info->getDefaultRS($org['m_contragents_id'])['m_contragents_rs_rs'].'</td>
					</tr>
					<tr>
						<td colspan="2" rowspan="2" style="vertical-align:top;border-right:1px solid #bbb;border-top:1px solid #bbb;padding:0.5mm 1mm;color:#000;height:10mm">'.($org['m_contragents_c_name_short']?$org['m_contragents_c_name_short']:$org['m_contragents_c_name_full']).'</td>
						<td style="border-right:1px solid #bbb;"></td>
						<td></td>
					</tr>
					<tr>
						<td style="border-right:1px solid #bbb;">&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="2" style="vertical-align:top;border-right:1px solid #bbb;padding:0.5mm 1mm;color:#666">Получатель</td>
						<td style="border-right:1px solid #bbb;"></td>
						<td></td>
					</tr>
				</table>';
				
		//комментарий в рамке под реквизитами
		$body.=$p['doc_attention']?'<div style="font-size:11;font-weight:bold;font-family:dejavusanscondensed;text-align:center;margin:0 auto;margin-top:2mm;background:#f00;color:#fff;width:60%;padding:1mm;">'.$p['doc_attention'].'</div>':'<div style="padding:0.5mm;">&nbsp;</div>';

		//заголовок
		$body.='<h1 style="font-size:20;font-weight:400;text-align:left;padding-bottom:2mm;border-bottom:2px solid #000;">Счёт на оплату № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'</h1>';
		
		//поставщик и покупатель
		$body.='<table style="font-size:12;border-spacing:0;margin:3mm 0;line-height:4.5mm" width="180mm">
					<thead>
						<tr>
							<td width="30mm"></td>
							<td width="150mm"></td>
						</tr>
					</thead>
					<tr>
						<td style="vertical-align:top;padding:0.5mm;color:#666">Поставщик</td>
						<td style="vertical-align:top;padding:0.5mm;color:#000;width:80mm;padding-bottom:1.5mm;">'.($org['m_contragents_c_name_short']?$org['m_contragents_c_name_short']:$org['m_contragents_c_name_full']).($org['m_contragents_c_inn']?', ИНН&nbsp;'.$org['m_contragents_c_inn']:'').($org['m_contragents_c_kpp']?', КПП&nbsp;'.$org['m_contragents_c_kpp']:'').($org['m_contragents_address_p']?', '.$org['m_contragents_address_p']:'').($org['tel']?', тел.:&nbsp;'.$org['tel']:'').'</td>
					</tr>
					<tr>
						<td style="vertical-align:top;padding:0.5mm;color:#666">Покупатель</td>
						<td style="vertical-align:top;padding:0.5mm;color:#000;width:80mm;">'.($client['m_contragents_p_fio']?$client['m_contragents_p_fio']:($client['m_contragents_c_name_short']?$client['m_contragents_c_name_short']:$client['m_contragents_c_name_full']).($client['m_contragents_c_inn']?', ИНН&nbsp;'.$client['m_contragents_c_inn']:'').($client['m_contragents_c_kpp']?', КПП&nbsp;'.$client['m_contragents_c_kpp']:'').($client['m_contragents_address_p']?', '.$client['m_contragents_address_p']:'').($client['tel']?', тел.:&nbsp;'.$client['tel']:'')).'</td>
					</tr>
				</table>';

		//тело документа
		$sum=0;
		$nds18=0;
		$items=0;
		$l=1;
		$rooms_count=count((array)$p['items']);
		foreach($p['items'] as $_room){
			//если есть позиции в разделе
			if($_room->services){
				if($rooms_count>1)
					$body.='
						<bookmark content="'.($_room->room->name?$_room->room->name:'Раздел '.$l).'" level="0"></bookmark>
						<h2 style="font-size:15;font-weight:400;margin-bottom:1mm;"><b>'.($_room->room->name?$_room->room->name:'Раздел'.$l).'</b></h2>
					';
				$body.='<table style="font-size:11;border-spacing:0;border-top:1px solid #bbb;margin-bottom:1mm;border-right:1px solid #bbb;" width="180mm">
							<thead>
								<tr>
									<td style="background:#eee;font-size:11;padding:2mm 1mm;border-bottom:1px solid #bbb;border-left:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="10mm">№</td>
									<td style="background:#eee;font-size:11;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="80mm">Наименование</td>
									<td style="background:#eee;font-size:11;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="18mm">Кол-во</td>
									<td style="background:#eee;font-size:11;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="17mm">Ед. изм.</td>
									<td style="background:#eee;font-size:11;padding:2mm 1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;" width="25mm">Цена</td>
									<td style="background:#eee;font-size:11;padding:2mm 1mm;border-bottom:1px solid #bbb;text-align:center;" width="30mm;">Сумма</td>
								</tr>
							</thead>
							<tbody>';
				//работы
				$pre_sum=0;
				$pre_nds18=0;
				$is_nds_from_items=false;
				foreach($_room->services as $k=>$_service){
					$nds_item=isset($_service->nds)?($_service->nds!=-1?$_service->nds:0):($orders->orders_id[$p['order']][0]['m_orders_nds']?18:0);
					$is_nds_from_items=(isset($_service->nds)&&$_service->nds!=-1)||$orders->orders_id[$p['order']][0]['m_orders_nds']!=-1?true:$is_nds_from_items;
					$orders->orders_id[$p['order']][0]['m_orders_nds']=isset($_service->nds)?$_service->nds:$orders->orders_id[$p['order']][0]['m_orders_nds'];
					$pre_sum+=$_service->sum;
					$pre_nds18+=round($_service->sum*($nds_item/100)/(1+$nds_item/100),2);
					$body.='	<tr>
									<td style="padding:1mm;border-left:1px solid #bbb;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.($k+1).'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$services->services_id[$_service->id][0]['m_services_name']:(isset($products->products_id[$_service->id])?$products->products_id[$_service->id][0]['m_products_name']:'')).'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.$_service->count/* str_replace('.',',',$_service->count) */.'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$info->getUnits($services->services_id[$_service->id][0]['m_services_unit']):(isset($products->products_id[$_service->id])?$info->getUnits($products->products_id[$_service->id][0]['m_products_unit']):'')).'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">'.transform::price_o($_service->price).'</td>
									<td style="padding:1mm;border-bottom:1px solid #bbb;text-align:center;">'.transform::price_o($_service->sum).'</td>
								</tr>';
				}
				$sum+=$pre_sum;
				$nds18+=$pre_nds18;
				$items+=sizeof($_room->services);
				//промежуточные итоги - выводим, только если в счете больше одного раздела
				if($rooms_count>1){
					//сумма
					$body.='	<tr>
									<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Промежуточный итог:</td>
									<td style="padding:1mm;text-align:center;border-bottom:1px solid #bbb;">'.transform::price_o($pre_sum).'</td>
								</tr>';
					//НДС 18%
					$body.=$is_nds_from_items?'
								<tr>
									<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС ('.$orders->orders_id[$p['order']][0]['m_orders_nds'].'%):</td>
									<td style="vertical-align:top;padding:1mm;border-bottom:1px solid #bbb;text-align:center;">'.transform::price_o($pre_nds18).'</td>
								</tr>':'';
				}
				$body.='
							</tbody>
						</table>';
			}
		}
		
		//итоговые суммы
		$body.='
					<table style="font-size:11;border-spacing:0;margin:6mm 0 2mm;border-right:1px solid #bbb;" width="180mm">
						<thead>
							<tr>
								<td width="10mm"></td>
								<td width="80mm"></td>
								<td width="18mm"></td>
								<td width="17mm"></td>
								<td width="25mm"></td>
								<td width="30mm;"></td>
							</tr>
						</thead>
						<tbody>';
						
		$body.='			<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum).'</td>
							</tr>';
		//скидка
		if($orders->orders_id[$p['order']][0]['m_orders_discount']>0)
			$body.='		<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Скидка '.$orders->orders_id[$p['order']][0]['m_orders_discount'].'%:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		//итого со скидкой
		if($orders->orders_id[$p['order']][0]['m_orders_discount']>0)
			$body.='		<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Итого со скидкой:</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		//итог НДС 18%
		$body.=$is_nds_from_items?'
							<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">В т.ч. НДС ('.$orders->orders_id[$p['order']][0]['m_orders_nds'].'%):</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:center;">'.transform::price_o($nds18-$nds18*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>':'';
		//итог БЕЗ НДС
		$body.=!$is_nds_from_items?'
							<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;">Без налога (НДС)</td>
								<td style="padding:1mm;border-top:1px solid #bbb;text-align:right;text-align:center;">—</td>
							</tr>':'';
		//всего к оплате
		$body.='			<tr>
								<td colspan="5" style="vertical-align:top;padding:1mm;border-right:1px solid #bbb;text-align:right;font-weight:bold">Всего к оплате:</td>
								<td style="vertical-align:top;padding:1mm;border-top:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;font-weight:bold">'.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'</td>
							</tr>';
		$body.='
						</tbody>
					</table>';
		
		//сумма прописью
		$body.='<div style="font-size:12;border-bottom:2px solid #000;">
					Всего '.$items.' ';
		switch(substr($items.'',-1)){
			case '1':
				$body.='наименование';
				break;
			case '2':
			case '3':
			case '4':
				$body.='наименования';
				break;
			default:
				$body.='наименований';
		}			
		$body.=', на сумму '.transform::price_o($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).'&nbsp;<span style="font-family:dejavurouble;font-size:11.5">r</span><br>
					<span style="font-weight:bold">'.transform::summ_text($sum-$sum*$orders->orders_id[$p['order']][0]['m_orders_discount']/100).($is_nds_from_items?(', в том числе НДС '.$orders->orders_id[$p['order']][0]['m_orders_nds'].'% — '.transform::summ_text($nds18-$nds18*$orders->orders_id[$p['order']][0]['m_orders_discount']/100,true,false)):'').'</span>

					<div style="font-size:12;text-align:left;color:#333;padding:2mm 0 0mm;">
						<p style="margin:0;margin-bottom:-2mm;padding:0;">Условия поставки:</p>
						<p style="margin:0;padding-left:30px;padding:0;">
							<ol>
								<li>Счёт действителен до '.transform::date_f(dtu($p['doc_date_expire'])).';</li>
								<li>В назначении платежа, пожалуйста, указывайте номер счета.</li>';
			$p['doc_terms']=explode("\r\n",$p['doc_terms']);
			foreach($p['doc_terms'] as $_term)
				$body.='
								<li>'.$_term.'</li>
				';
		$body.='			</ol>
						</p>
					</div>
				</div>';		
		

	
	
		//таблица для подписей и печати
		$body.='<table style="font-size:13;border-spacing:0;margin-top:10mm;line-height:4.5mm" width="180mm">
					<tr>
						<td width="180mm">
							<table style="font-size:13;border-spacing:0;margin:0;line-height:4.5mm" width="180mm">
								<thead>
									<tr>
										<td width="20mm"></td>
										<td width="7mm"></td>
										<td width="46mm"></td>
										<td width="7mm"></td>
										<td width="40mm"></td>
										<td width="7mm"></td>
										<td width="53mm"></td>
									</tr>
								</thead>
								<tr>
									<td style="vertical-align:bottom;padding:0.5mm;">Руководитель</td>
									<td></td>
									<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.$org['m_contragents_c_director_post'].'</td>
									<td></td>
									<td></td>
									<td></td>
									<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($org['m_contragents_c_director_name']).'</td>
								</tr>
								<tr>
									<td></td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">должность</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
								</tr>
								<tr>
									<td colspan="7" style="padding:2mm"></td>
								</tr>
								<tr>
									<td colspan="3" style="vertical-align:bottom;padding:0.5mm;">Главный (старший) бухгалтер</td>
									<td></td>
									<td></td>
									<td></td>
									<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($org['m_contragents_c_bookkeeper_name']).'</td>
								</tr>
								<tr>
									<td colspan="3"></td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
								</tr>
								<tr>
									<td colspan="7" style="padding:2mm"></td>
								</tr>
								<tr>
									<td colspan="3" style="vertical-align:bottom;padding:0.5mm;">Ответственный</td>
									<td></td>
									<td></td>
									<td></td>
									<td style="vertical-align:bottom;padding:0.5mm;text-align:center">'.transform::fio($org['m_contragents_c_responsible_name']).'</td>
								</tr>
								<tr>
									<td colspan="3"></td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">подпись</td>
									<td></td>
									<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">расшифровка подписи</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>';	
		
		//печати и подписи
		if($p['doc_signature']){
			$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:55mm;margin-top:-40mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
			$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:95mm;margin-top:-55mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			$body.=$org['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:95mm;margin-top:-40mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=bookkeeper"/></div>':'';
			$body.=$org['m_contragents_c_signature_responsible']?'<div style="overflow:visible;position:absolute;left:95mm;margin-top:-25mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=responsible"/></div>':'';
		}
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);
		//echo $body;exit;

		$mpdf->WriteHTML($body);

		//создаем файл 
		$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
		$mpdf->Output($f_name,'F');

		$f_size=filesize($f_name);
		$f_size=($f_size)?$f_size:0;
		if ($f_size>1048576)
			$f_size=round($f_size/1048576,1).' МБ';
		else
			$f_size=round($f_size/1024,1).' КБ';
		return $f_size;

	}
	
	//УПД
	public static function upd($p=array(
		'org'=>'',
		'client'=>'',
		'smeta'=>'',
		'doc_base'=>'',
		'date_ship'=>'',
		'doc_base_text'=>'',
		'doc_date'=>'',
		'doc_numb'=>'',
		'doc_sum'=>'',
		'doc_nds18'=>'',
		'doc_logo'=>null,
		'doc_bar'=>null,
		'doc_signature'=>null,
		'doc_header_org_name'=>null,
		'doc_header_org_address'=>null,
		'doc_header_org_tel'=>null,
		'doc_header_org_email'=>null,
		'items'=>false
	),$filename){
		global $info,$contragents,$orders,$services,$products,$documents,$buh;
		
		$smeta=$documents->getInfo($p['smeta']);
		$p['order']=$smeta['m_documents_order'];
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		
		$client_address=$info->getAddress($p['client']);
		$client_address=change_key($client_address,'m_address_type',true);
		
		$client['m_contragents_consignee']=$client['m_contragents_consignee']?$client['m_contragents_consignee']:$p['client'];
		
		if($client['m_contragents_consignee']){
			$client_consignee_address=$info->getAddress($client['m_contragents_consignee']);
			$client_consignee_address=change_key($client_consignee_address,'m_address_type',true);
		}
		
		$org_address=$info->getAddress($p['org']);
		$org_address=change_key($org_address,'m_address_type',true);
		
		if($org['m_contragents_consignee']){
			$org_consignee_address=$info->getAddress($org['m_contragents_consignee']);
			$org_consignee_address=change_key($org_consignee_address,'m_address_type',true);
		}
		
		$client['m_contragents_c_director_name']=$contragents->getDirector($client['m_contragents_id']);
		
		$pays=explode('|',$smeta['m_documents_pays']);
		$pays=array_diff($pays,array(null));
		$pays_list=array();
		foreach($pays as &$_pay)
			$pays_list[]='№ '.$buh->getInfo($_pay)['m_buh_payment_numb'].' от '.dtu($buh->getInfo($_pay)['m_buh_date'], 'd.m.Y');
		
		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('',array(297,260),9,'dejavuserifcondensed',7,7,15,8,6,6);
		$mpdf->SetAuthor(transform::typography(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2))));
		$mpdf->SetCreator(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2)));
		$mpdf->SetSubject(strcode2utf('Универсальный передаточный документ'));
		$mpdf->SetTitle(strcode2utf('Универсальный передаточный документ № '.$p['doc_numb'].' от '.dtu($p['doc_date'],'d.m.Y')));
		$mpdf->SetKeywords(strcode2utf('Универсальный передаточный документ № '.$p['doc_numb'].' от '.dtu($p['doc_date'],'d.m.Y')));		
		$mpdf->defaultCSS['P']['MARGIN']='0.7em 0';
		$mpdf->shrink_tables_to_fit=2;

		$body='';
		//количество не пустых товаров
		$count_all=1;
		
		//номер договора в хедере
		$header_numb='
			<div style="font-size:10px;color:#656565;float:left;width:60%;">
				Универсальный передаточный документ № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'<br/>
				Лист {PAGENO}
			</div>
		';

		if($p['doc_bar'])
			$header_numb.='
			<div style="float:right;width:35%;text-align:right;">
				<img src="/files/'.substr($filename,0,-3).'bar.png" style="height:24px"/>
			</div>';
		$mpdf->SetHTMLHeader($header_numb);
		
		//логотип, контакты, футер
		$header='
			<div style="width:100%;padding-bottom:1mm;clear:both;">
				<div style="float:left;color:#333;margin-right:30px;width:30%;">';
		$header.=($p['doc_logo'])?'<img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=logo" width="200"/>':'';
		$header.='
				</div>
				<div style="width:45%;float:right;font-size:11;">
					<p style="margin:0 0 3px 0;padding:0;font-weight:bold;">'.transform::typography($p['doc_header_org_name']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_address']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$p['doc_header_org_email'].'">'.transform::typography($p['doc_header_org_email']).'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		
		$body.=$header;//$mpdf->SetHTMLHeader($header);

		//заголовок
		$body.='<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
					<thead>
						<tr>
							<td width="28mm" style="padding:0"></td>
							<td width="32mm" style="padding:0"></td>
							<td width="32mm" style="padding:0"></td>
							<td width="7mm" style="padding:0;"></td>
							<td width="35mm" style="padding:0"></td>
							<td width="5mm" style="padding:0"></td>
							<td width="140mm" style="padding:0"></td>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td rowspan="3" style="padding:0;border-right:2px solid #000;">Универсальный передаточный документ</td>
						<td style="padding-left:3mm">Счет-фактура №</td>
						<td style="text-align:center;border-bottom:1px solid #bbb">'.$p['doc_numb'].'</td>
						<td style="padding:0;">&nbsp;от </td>
						<td style="text-align:center;border-bottom:1px solid #bbb">'.transform::date_f(dtu($p['doc_date'])).'</td>
						<td style="padding:0;color:#666">&nbsp;(1)</td>
						<td rowspan="3" style="padding:0;color:#666;text-align:right;">Приложение № 1 к постановлению Правительства Российской Федерации от 26 декабря 2011 г. № 1137 <br/>(в редакции постановления Правительства Российской Федерации от 19 августа 2017 г. № 981)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm">Исправление №</td>
						<td style="text-align:center;border-bottom:1px solid #bbb">—</td>
						<td style="padding:0;">&nbsp;от </td>
						<td style="text-align:center;border-bottom:1px solid #bbb">—</td>
						<td style="padding:0;color:#666">&nbsp;(1а)</td>
					</tr>
					<tr>
						<td colspan="5" style="padding-bottom:2mm;"></td>
					</tr>
					</tbody>
				</table>';
				
		//реквизиты
		$body.='<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
					<thead>
						<tr>
							<td width="28mm" style="padding:0"></td>
							<td width="55mm" style="padding:0"></td>
							<td width="191mm" style="padding:0"></td>
							<td width="5mm" style="padding:0"></td>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td rowspan="2" style="padding:0;border-right:2px solid #000;"><table><tr><td>Стутус:&nbsp;</td><td style="border:2px solid #000;">'.($p['doc_nds18']?1:2).'</td></tr></table>
						</td>
						<td style="padding-left:3mm;font-weight:bold">Продавец:</td>
						<td style="border-bottom:1px solid #bbb">'.$org['m_contragents_c_name_short'].'</td>
						<td style="padding:0;color:#666">&nbsp;(2)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Адрес:</td>
						<td style="border-bottom:1px solid #bbb">'.$org_address[1]['m_address_full'].'</td>
						<td style="padding:0;color:#666">&nbsp;(2а)</td>
					</tr>
					<tr>
						<td rowspan="9" style="padding:0;border-right:2px solid #000;">1 — счет-фактура и передаточный документ (акт)<br/>2 — передаточный документ (акт)</td>
						<td style="padding-left:3mm;">ИНН/КПП продавца:</td>
						<td style="border-bottom:1px solid #bbb">'.$org['m_contragents_c_inn'].($org['m_contragents_c_kpp']?'/'.$org['m_contragents_c_kpp']:'').'</td>
						<td style="padding:0;color:#666">&nbsp;(2б)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Грузоотправитель и его адрес:</td>
						<td style="border-bottom:1px solid #bbb">'.($org['m_contragents_consignee']?$contragents->getName($org['m_contragents_consignee']).', '.$org_consignee_address[1]['m_address_full']:'он же').'</td>
						<td style="padding:0;color:#666">&nbsp;(3)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Грузополучатель и его адрес:</td>
						<td style="border-bottom:1px solid #bbb">'.($client['m_contragents_consignee']?$contragents->getName($client['m_contragents_consignee']).', '.($client_consignee_address[4]['m_address_full']?$client_consignee_address[4]['m_address_full']:$client_consignee_address[1]['m_address_full']):$client['m_contragents_c_name_short'].', '.$client_address[1]['m_address_full']).'</td>
						<td style="padding:0;color:#666">&nbsp;(4)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">К платежно-расчетному документу №:</td>
						<td style="border-bottom:1px solid #bbb">'.($pays_list?implode(', ',$pays_list):'&nbsp;от').'</td>
						<td style="padding:0;color:#666">&nbsp;(5)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;font-weight:bold">Покупатель:</td>
						<td style="border-bottom:1px solid #bbb">'.($client['m_contragents_c_name_short']?$client['m_contragents_c_name_short']:($client['m_contragents_c_name_full']?$client['m_contragents_c_name_full']:$client['m_contragents_p_fio'])).'</td>
						<td style="padding:0;color:#666">&nbsp;(6)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Адрес:</td>
						<td style="border-bottom:1px solid #bbb">'.$client_address[1]['m_address_full'].'</td>
						<td style="padding:0;color:#666">&nbsp;(6а)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">ИНН/КПП покупателя:</td>
						<td style="border-bottom:1px solid #bbb">'.$client['m_contragents_c_inn'].($client['m_contragents_c_kpp']?'/'.$client['m_contragents_c_kpp']:'').'</td>
						<td style="padding:0;color:#666">&nbsp;(6б)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Валюта: наименование, код</td>
						<td style="border-bottom:1px solid #bbb">Российский рубль, 643</td>
						<td style="padding:0;color:#666">&nbsp;(7)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Идентификатор государственного контракта, договора (соглашения) (при наличии)</td>
						<td style="border-bottom:1px solid #bbb"></td>
						<td style="padding:0;color:#666">&nbsp;(8)</td>
					</tr>
					<tr>
						<td colspan="3" style="padding-bottom:2mm;"></td>
					</tr>
					</tbody>
				</table>';

		//товары
		$sum=0;
		$nds18=0;
		$room_id=0;
		$body.='<table style="font-size:10;border-spacing:0;border-top:1px solid #bbb;line-height:1.2;border-left:1px solid #bbb;margin:0 auto;">
					<thead>
						<tr>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="7.5mm">№ п/п</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:2px solid #000;text-align:center;" width="20mm">Код товара/работ, услуг</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="40mm">Наименование товара (описание выполненных работ, оказанных услуг), имущественного права</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="12mm">Код вида товара</td>
							<td colspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;" width="21mm">Единица измерения</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="17mm">Коли-<br/>чество (объём)</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="18mm">Цена (тариф) за единицу измерения</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="22mm">Стоимость товаров (работ, услуг), имущест-<br/>венных прав без налога — всего</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="16mm">В том числе сумма акциза</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="12mm">Нало-<br/>говая ставка</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="20mm">Сумма налога, предъяв-<br/>ляемая покупателю</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="22mm">Стоимость товаров (работ, услуг), имущест-<br/>венных прав с налогом — всего</td>
							<td colspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;" width="26mm">Страна происхождения</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="22mm">Регистрационный номер таможенной декларации</td>
						</tr>
						<tr>
							<td style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="7mm">код</td>
							<td style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="14mm">условное обозна-<br/>чение (нацио-<br/>нальное)</td>
							<td style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="8mm">циф-<br/>ро-<br/>вой код</td>
							<td style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="18mm">краткое наиме-<br/>нование</td>
						</tr>
						<tr>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="7.5mm">А</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;border-right:2px solid #000;text-align:center;color:#666" width="20mm">Б</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="40mm">1</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="12mm">1а</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="7mm">2</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="14mm">2а</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="17mm">3</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="18mm">4</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="22mm">5</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="16mm">6</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="12mm">7</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="20mm">8</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="22mm">9</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="8mm">10</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="18mm">10а</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="22mm">11</td>
						</tr>
					</thead>
					<tbody>';
		
		//товарные позиции
		$k=0;
		$is_nds_from_items=false;
		foreach($p['items'] as $_service){
			$nds_item=isset($_service->nds)?$_service->nds:$orders->orders_id[$p['order']][0]['m_orders_nds'];
			$is_nds_from_items=$nds_item!=-1?true:$is_nds_from_items;
			
			$room_id=$_service->room_id;	
			$pre_sum+=$_service->sum;
			//$pre_nds18+=$_service->sum*.20/1.20;
			$body.='<tr>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">'.++$k.'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:2px solid #000;">'.$_service->id.'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$services->services_id[$_service->id][0]['m_services_name']:(isset($products->products_id[$_service->id])?$products->products_id[$_service->id][0]['m_products_name']:'')).'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">—</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$info->units[$services->services_id[$_service->id][0]['m_services_unit']][0]['m_info_units_numb']:(isset($products->products_id[$_service->id])?$info->units[$products->products_id[$_service->id][0]['m_products_unit']][0]['m_info_units_numb']:'')).'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$info->getUnits($services->services_id[$_service->id][0]['m_services_unit']):(isset($products->products_id[$_service->id])?$info->getUnits($products->products_id[$_service->id][0]['m_products_unit']):'')).'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.number_format($_service->count,3,',',' ').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.number_format(($nds_item!=-1?$_service->price/(1+$nds_item/100):$_service->price),2,',',' ').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.number_format(($nds_item!=-1?$_service->price*$_service->count/(1+$nds_item/100):$_service->price*$_service->count),2,',',' ').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">без акциза</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">'.($nds_item!=-1?$nds_item.'%':'без НДС').'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.($nds_item!=-1?number_format($_service->price*$_service->count*($nds_item/100)/(1+$nds_item/100),2,',',' '):'без НДС').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.number_format($_service->sum,2,',',' ').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">—</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">—</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">—</td>
					</tr>';
			$sum+=$_service->sum;
			$nds18+=$nds_item!=-1?round($_service->sum*($nds_item/100)/(1+$nds_item/100),2):0;
		}
		//итоговые суммы
		$body.='
					<tr>
						<td colspan="2" style="padding:1mm;border-bottom:1px solid #bbb;border-right:2px solid #000;"></td>
						<td colspan="6" style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;font-weight:bold">Всего к оплате</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;font-weight:bold;text-align:right;">'.number_format($sum-$nds18,2,',',' ').'</td>
						<td colspan="2" style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">×</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;font-weight:bold;text-align:right;">'.($nds18?number_format($nds18,2,',',' '):'без НДС').'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;font-weight:bold;text-align:right;">'.number_format($sum,2,',',' ').'</td>
						<td colspan="3"></td>						
					</tr>
				</tbody>
			</table>';
		//первые подписи
		$body.='
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<thead>
					<tr>
						<td width="27.5mm" style="padding:0"></td>
						<td width="50mm" style="padding:0"></td>
						<td width="33mm" style="padding:0"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="44mm" style="padding:0"></td>
						<td width="32mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="41mm" style="padding:0"></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td rowspan="4" style="padding:0;border-right:2px solid #000;vertical-align:top;padding-top:2mm;">Документ<br/>составлен на<br/>{nbpg} листе(ах)</td>
						<td style="padding-left:3mm">Руководитель организации<br/>или иное уполномоченное лицо</td>
						<td>'.($p['doc_signature']&&$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:87mm;margin-top:-5mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'').'</td>
						<td></td>
						<td style="vertical-align:bottom;">'.transform::fio($org['m_contragents_c_director_name']).'</td>
						<td></td>
						<td>Главный бухгалтер<br/>или иное уполномоченное лицо</td>
						<td>'.($p['doc_signature']&&$org['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:208mm;margin-top:-7mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=bookkeeper"/></div>':'').'</td>
						<td></td>
						<td style="vertical-align:bottom;">'.transform::fio($org['m_contragents_c_bookkeeper_name']).'</td>					
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td colspan="2"></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm">Индивидуальный предприниматель или иное уполномоченное лицо</td>
						<td style="text-align:center;"></td>
						<td></td>
						<td style="text-align:center;"></td>
						<td></td>
						<td colspan="4" style="text-align:center;"></td>					
					</tr>
					<tr>
						<td style="border-bottom:2px solid #000;"></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;border-bottom:2px solid #000;text-align:center;color:#666">(подпись)</td>
						<td style="border-bottom:2px solid #000;"></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;border-bottom:2px solid #000;text-align:center;color:#666">(ф.и.о.)</td>
						<td style="border-bottom:2px solid #000;"></td>
						<td colspan="4" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;border-bottom:2px solid #000;text-align:center;color:#666">(реквизиты свидетельства о государственной регистрации ИП)</td>
					</tr>
				</tbody>
			</table>';
			
		//основание
		if($p['doc_base_text'])
			$p['doc_base']=$p['doc_base_text'];
		else{
			$p['doc_base']=$documents->getInfo($p['doc_base']);
			$p['doc_base']=$p['doc_base']['m_documents_templates_id']==4234525325?('Договор поставки №'.$p['doc_base']['m_documents_numb'].' от '.transform::date_f(dtu($p['doc_base']['m_documents_date']))):('Счет по заявке клиента №'.$p['doc_base']['m_documents_numb'].' от '.transform::date_f(dtu($p['doc_base']['m_documents_date'])));
		}
		$body.='
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<tbody>
					<tr>
						<td width="74mm" style="padding:0;padding-top:2mm;">Основание передачи (сдачи) / получения (приемки)</td>
						<td width="200mm">'.$p['doc_base'].'</td>
						<td width="5mm" style="padding:0;color:#666">&nbsp;[8]</td>				
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(договор; доверенность и др.)</td>
						<td></td>			
					</tr>
				</tbody>
			</table>';
		//данные о транспортировке
		$body.='
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<tbody>
					<tr>
						<td width="50mm" style="padding:0;">Данные о транспортировке и грузе</td>
						<td width="224mm"></td>
						<td width="5mm" style="padding:0;color:#666">&nbsp;[9]</td>				
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(транспортная накладная, поручение экспедитору, экспедиторская / складская расписка и др. / масса нетто/ брутто груза, если не приведены ссылки на транспортные документы, содержащие эти сведения)</td>
						<td></td>			
					</tr>
				</tbody>
			</table>';
		
		//вторые подписи
		$body.='
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<thead>
					<tr>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="8mm" style="padding:0;"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="7mm" style="padding:0"></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="6" style="padding:0;border-right:2px solid #000;vertical-align:top;padding-top:2mm;">Товар (груз) передал / услуги, результаты работ, права сдал</td>
						<td></td>
						<td colspan="6" style="padding:0;vertical-align:top;padding-top:2mm;">Товар (груз) получил / услуги, результаты работ, права принял</td>			
					</tr>
				</tbody>
			</table>
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<thead>
					<tr>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="8mm" style="padding:0;"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="7mm" style="padding:0"></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="padding-top:1mm;">'.$org['m_contragents_c_director_post'].'</td>
						<td></td>
						<td>'.($p['doc_signature']&&$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:55mm;margin-top:-7mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'').'</td>
						<td></td>
						<td>'.transform::fio($org['m_contragents_c_director_name']).'</td>
						<td style="vertical-align:top;border-right:2px solid #000;">&nbsp;[10]</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td style="vertical-align:top;">&nbsp;[15]</td>
					</tr>
					<tr>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(должность)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td style="vertical-align:top;border-right:2px solid #000;"></td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(должность)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="2">Дата отгрузки, передачи (сдачи)</td>
						<td style="border-bottom:1px solid #bbb;">'.transform::date_f(dtu($p['date_ship'])).'</td>
						<td colspan="2"></td>
						<td style="border-right:2px solid #000;vertical-align:top;">&nbsp;[11]</td>
						<td></td>
						<td>Дата получения (приемки)</td>
						<td></td>
						<td style="border-bottom:1px solid #bbb;"></td>
						<td colspan="2"></td>
						<td style="padding:0;vertical-align:top;">&nbsp;[16]</td>
					</tr>
					<tr>
						<td colspan="6" style="padding:0;padding-top:1mm;border-right:2px solid #000;vertical-align:top;">Иные сведения об отгрузке, передаче</td>
						<td></td>
						<td colspan="6" style="padding:0;padding-top:1mm;vertical-align:top;">Иные сведения о получении, приемке</td>			
					</tr>
					<tr>
						<td colspan="5"></td>
						<td style="vertical-align:top;border-right:2px solid #000;">&nbsp;[12]</td>
						<td></td>
						<td colspan="5"></td>
						<td style="vertical-align:top;">&nbsp;[17]</td>						
					</tr>
					<tr>
						<td colspan="5" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ссылки на неотъемлемые приложения, сопутствующие документы, иные документы и т.п.)</td>
						<td style="border-right:2px solid #000;"></td>
						<td></td>
						<td colspan="5" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(информация о наличии/отсутствии претензии; ссылки на неотъемлемые приложения, и другие документы и т.п.)</td>
						<td></td>						
					</tr>
					<tr>
						<td colspan="6" style="padding:0;border-right:2px solid #000;vertical-align:top;">Ответственный за правильность оформления факта хозяйственной жизни</td>
						<td></td>
						<td colspan="6" style="padding:0;vertical-align:top;">Ответственный за правильность оформления факта хозяйственной жизни</td>			
					</tr>
				</tbody>
			</table>
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<thead>
					<tr>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="8mm" style="padding:0;"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="7mm" style="padding:0"></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="padding-top:1mm;">главный бухгалтер</td>
						<td></td>
						<td>'.($p['doc_signature']&&$org['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:55mm;margin-top:-6mm"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=bookkeeper"/></div>':'').'</td>
						<td></td>
						<td>'.transform::fio($org['m_contragents_c_bookkeeper_name']).'</td>
						<td style="vertical-align:top;border-right:2px solid #000;">&nbsp;[13]</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td style="vertical-align:top;">&nbsp;[18]</td>
					</tr>
					<tr>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(должность)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td style="vertical-align:top;border-right:2px solid #000;"></td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(должность)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="6" style="padding:0;padding-top:1mm;border-right:2px solid #000;vertical-align:top;">Наименование экономического субъекта – составителя документа (в т.ч. комиссионера / агента)</td>
						<td></td>
						<td colspan="6" style="padding:0;vertical-align:top;">Наименование экономического субъекта – составителя документа</td>			
					</tr>
					<tr>
						<td colspan="5" style="padding-top:1mm;">'.$contragents->getName($p['org']).', ИНН/КПП '.$org['m_contragents_c_inn'].'/'.$org['m_contragents_c_kpp'].'</td>
						<td style="vertical-align:top;border-right:2px solid #000;">&nbsp;[14]</td>
						<td></td>
						<td colspan="5">'.$contragents->getName($p['client']).', ИНН/КПП '.$client['m_contragents_c_inn'].'/'.$client['m_contragents_c_kpp'].'</td>
						<td style="vertical-align:top;">&nbsp;[19]</td>
					</tr>
					<tr>
						<td colspan="5" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(может не заполняться при проставлении печати в М.П., может быть указан ИНН / КПП)</td>
						<td style="border-right:2px solid #000;"></td>
						<td></td>
						<td colspan="5" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(может не заполняться при проставлении печати в М.П., может быть указан ИНН / КПП)</td>
						<td></td>
					</tr>
					<tr>
						<td>М.П.</td>
						<td colspan="5" style="padding:0;border-right:2px solid #000;"></td>
						<td></td>
						<td>М.П.</td>
						<td colspan="6" style="padding:0;"></td>			
					</tr>
				</tbody>
			</table>';
		
		//подписи после таблицы
		if($p['doc_signature']){
			//$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:87mm;margin-top:-105mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			//$body.=$org['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:208mm;margin-top:-105mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=bookkeeper"/></div>':'';
		}
		
		//печати и подписи в нижнем блоке
		if($p['doc_signature']){
			$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:10mm;margin-top:-37mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
		}
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);
		//echo $body;exit;

		$mpdf->WriteHTML($body);
		$mpdf->SetHTMLFooter('');
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}
	}
	
	//СФ
	public static function sf($p=array(
		'org'=>'',
		'client'=>'',
		'smeta'=>'',
		'doc_base'=>'',
		'date_ship'=>'',
		'doc_base_text'=>'',
		'doc_date'=>'',
		'doc_numb'=>'',
		'doc_sum'=>'',
		'doc_nds18'=>'',
		'doc_logo'=>null,
		'doc_bar'=>null,
		'doc_signature'=>null,
		'doc_header_org_name'=>null,
		'doc_header_org_address'=>null,
		'doc_header_org_tel'=>null,
		'doc_header_org_email'=>null,
		'items'=>false
	),$filename){
		global $info,$contragents,$orders,$services,$products,$documents,$buh;
		
		$smeta=$documents->getInfo($p['smeta']);
		$p['order']=$smeta['m_documents_order'];
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		
		$client_address=$info->getAddress($p['client']);
		$client_address=change_key($client_address,'m_address_type',true);
		
		if($client['m_contragents_consignee']){
			$client_consignee_address=$info->getAddress($client['m_contragents_consignee']);
			$client_consignee_address=change_key($client_consignee_address,'m_address_type',true);
		}
		
		$org_address=$info->getAddress($p['org']);
		$org_address=change_key($org_address,'m_address_type',true);
		
		if($org['m_contragents_consignee']){
			$org_consignee_address=$info->getAddress($org['m_contragents_consignee']);
			$org_consignee_address=change_key($org_consignee_address,'m_address_type',true);
		}
		
		$client['m_contragents_c_director_name']=$contragents->getDirector($client['m_contragents_id']);
		
		$pays=explode('|',$smeta['m_documents_pays']);
		$pays=array_diff($pays,array(null));
		$pays_list=array();
		foreach($pays as &$_pay)
			$pays_list[]='№ '.$buh->getInfo($_pay)['m_buh_payment_numb'].' от '.dtu($buh->getInfo($_pay)['m_buh_date'], 'd.m.Y');
		
		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('',array(297,260),9,'dejavuserifcondensed',7,7,15,8,6,6);
		$mpdf->SetAuthor(transform::typography(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2))));
		$mpdf->SetCreator(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2)));
		$mpdf->SetSubject(strcode2utf('Универсальный передаточный документ'));
		$mpdf->SetTitle(strcode2utf('Универсальный передаточный документ № '.$p['doc_numb'].' от '.dtu($p['doc_date'],'d.m.Y')));
		$mpdf->SetKeywords(strcode2utf('Универсальный передаточный документ № '.$p['doc_numb'].' от '.dtu($p['doc_date'],'d.m.Y')));		
		$mpdf->defaultCSS['P']['MARGIN']='0.7em 0';
		$mpdf->shrink_tables_to_fit=2;

		$body='';
		//количество не пустых товаров
		$count_all=1;
		
		//номер договора в хедере
		$header_numb='
			<div style="font-size:10px;color:#656565;float:left;width:60%;">
				Универсальный передаточный документ № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'<br/>
				Лист {PAGENO}
			</div>
		';

		if($p['doc_bar'])
			$header_numb.='
			<div style="float:right;width:35%;text-align:right;">
				<img src="/files/'.substr($filename,0,-2).'bar.png" style="height:24px"/>
			</div>';
		$mpdf->SetHTMLHeader($header_numb);
		
		//логотип, контакты, футер
		$header='
			<div style="width:100%;padding-bottom:1mm;clear:both;">
				<div style="float:left;color:#333;margin-right:30px;width:30%;">';
		$header.=($p['doc_logo'])?'<img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=logo" width="200"/>':'';
		$header.='
				</div>
				<div style="width:45%;float:right;font-size:11;">
					<p style="margin:0 0 3px 0;padding:0;font-weight:bold;">'.transform::typography($p['doc_header_org_name']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_address']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$p['doc_header_org_email'].'">'.transform::typography($p['doc_header_org_email']).'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		
		$body.=$header;//$mpdf->SetHTMLHeader($header);

		//заголовок
		$body.='<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
					<thead>
						<tr>
							<td width="28mm" style="padding:0"></td>
							<td width="32mm" style="padding:0"></td>
							<td width="32mm" style="padding:0"></td>
							<td width="7mm" style="padding:0;"></td>
							<td width="35mm" style="padding:0"></td>
							<td width="5mm" style="padding:0"></td>
							<td width="140mm" style="padding:0"></td>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td rowspan="3" style="padding:0;border-right:2px solid #000;">Универсальный передаточный документ</td>
						<td style="padding-left:3mm">Счет-фактура №</td>
						<td style="text-align:center;border-bottom:1px solid #bbb">'.$p['doc_numb'].'</td>
						<td style="padding:0;">&nbsp;от </td>
						<td style="text-align:center;border-bottom:1px solid #bbb">'.transform::date_f(dtu($p['doc_date'])).'</td>
						<td style="padding:0;color:#666">&nbsp;(1)</td>
						<td rowspan="3" style="padding:0;color:#666;text-align:right;">Приложение № 1<br>к постановлению Правительства Российской Федерации<br>от 26 декабря 2011 г. № 1137</td>
					</tr>
					<tr>
						<td style="padding-left:3mm">Исправление №</td>
						<td style="text-align:center;border-bottom:1px solid #bbb">—</td>
						<td style="padding:0;">&nbsp;от </td>
						<td style="text-align:center;border-bottom:1px solid #bbb">—</td>
						<td style="padding:0;color:#666">&nbsp;(1а)</td>
					</tr>
					<tr>
						<td colspan="5" style="padding-bottom:2mm;"></td>
					</tr>
					</tbody>
				</table>';
				
		//реквизиты
		$body.='<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
					<thead>
						<tr>
							<td width="28mm" style="padding:0"></td>
							<td width="55mm" style="padding:0"></td>
							<td width="191mm" style="padding:0"></td>
							<td width="5mm" style="padding:0"></td>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td rowspan="2" style="padding:0;border-right:2px solid #000;"><table><tr><td>Стутус:&nbsp;</td><td style="border:2px solid #000;">1</td></tr></table>
						</td>
						<td style="padding-left:3mm;font-weight:bold">Продавец:</td>
						<td style="border-bottom:1px solid #bbb">'.$org['m_contragents_c_name_short'].'</td>
						<td style="padding:0;color:#666">&nbsp;(2)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Адрес:</td>
						<td style="border-bottom:1px solid #bbb">'.$org_address[1]['m_address_full'].'</td>
						<td style="padding:0;color:#666">&nbsp;(2а)</td>
					</tr>
					<tr>
						<td rowspan="9" style="padding:0;border-right:2px solid #000;">1 — счет-фактура и передаточный документ (акт)<br/>2 — передаточный документ (акт)</td>
						<td style="padding-left:3mm;">ИНН/КПП продавца:</td>
						<td style="border-bottom:1px solid #bbb">'.$org['m_contragents_c_inn'].'</td>
						<td style="padding:0;color:#666">&nbsp;(2б)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Грузоотправитель и его адрес:</td>
						<td style="border-bottom:1px solid #bbb">'.($org['m_contragents_consignee']?$contragents->getName($org['m_contragents_consignee']).', '.$org_consignee_address[1]['m_address_full']:'он же').'</td>
						<td style="padding:0;color:#666">&nbsp;(3)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Грузополучатель и его адрес:</td>
						<td style="border-bottom:1px solid #bbb">'.($client['m_contragents_consignee']?$contragents->getName($client['m_contragents_consignee']).', '.$client_consignee_address[1]['m_address_full']:$client['m_contragents_c_name_short'].', '.$client_address[1]['m_address_full']).'</td>
						<td style="padding:0;color:#666">&nbsp;(4)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">К платежно-расчетному документу №:</td>
						<td style="border-bottom:1px solid #bbb">'.($pays_list?implode(', ',$pays_list):'&nbsp;от').'</td>
						<td style="padding:0;color:#666">&nbsp;(5)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;font-weight:bold">Покупатель:</td>
						<td style="border-bottom:1px solid #bbb">'.$client['m_contragents_c_name_short'].'</td>
						<td style="padding:0;color:#666">&nbsp;(6)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Адрес:</td>
						<td style="border-bottom:1px solid #bbb">'.$client_address[1]['m_address_full'].'</td>
						<td style="padding:0;color:#666">&nbsp;(6а)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">ИНН/КПП покупателя:</td>
						<td style="border-bottom:1px solid #bbb">'.$client['m_contragents_c_inn'].'</td>
						<td style="padding:0;color:#666">&nbsp;(6б)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Валюта: наименование, код</td>
						<td style="border-bottom:1px solid #bbb">Российский рубль, 643</td>
						<td style="padding:0;color:#666">&nbsp;(7)</td>
					</tr>
					<tr>
						<td colspan="3" style="padding-bottom:2mm;"></td>
					</tr>
					</tbody>
				</table>';
		
		//товары
		$sum=0;
		$nds18=0;
		$room_id=0;
		
		$body.='<table style="font-size:10;border-spacing:0;border-top:1px solid #bbb;line-height:1.2;border-left:1px solid #bbb;margin:0 auto;">
					<thead>
						<tr>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="7.5mm">№ п/п</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:2px solid #000;text-align:center;" width="20mm">Код товара/работ, услуг</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="55mm">Наименование товара (описание выполненных работ, оказанных услуг), имущественного права</td>
							<td colspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;" width="21mm">Единица измерения</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="17mm">Коли-<br/>чество (объём)</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="18mm">Цена (тариф) за единицу измерения</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="22mm">Стоимость товаров (работ, услуг), имущест-<br/>венных прав без налога — всего</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="16mm">В том числе сумма акциза</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="12mm">Нало-<br/>говая ставка</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="20mm">Сумма налога, предъяв-<br/>ляемая покупателю</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="22mm">Стоимость товаров (работ, услуг), имущест-<br/>венных прав с налогом — всего</td>
							<td colspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;" width="26mm">Страна происхождения</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="22mm">Номер таможенной декларации</td>
						</tr>
						<tr>
							<td style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="7mm">код</td>
							<td style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="14mm">условное обозна-<br/>чение (нацио-<br/>нальное)</td>
							<td style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="8mm">циф-<br/>ро-<br/>вой код</td>
							<td style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="18mm">краткое наиме-<br/>нование</td>
						</tr>
						<tr>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="7.5mm">А</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;border-right:2px solid #000;text-align:center;color:#666" width="20mm">Б</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="55mm">1</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="7mm">2</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="14mm">2а</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="17mm">3</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="18mm">4</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="22mm">5</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="16mm">6</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="12mm">7</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="20mm">8</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="22mm">9</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="8mm">10</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="18mm">10а</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="22mm">11</td>
						</tr>
					</thead>
					<tbody>';
		
		//товарные позиции
		$k=0;
		foreach($p['items'] as $_service){
			$room_id=$_service->room_id;	
			$pre_sum+=$_service->sum;
			//$pre_nds18+=$_service->sum*.20/1.20;
			$body.='<tr>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">'.++$k.'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:2px solid #000;">'.$_service->id.'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$services->services_id[$_service->id][0]['m_services_name']:(isset($products->products_id[$_service->id])?$products->products_id[$_service->id][0]['m_products_name']:'')).'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$info->units[$services->services_id[$_service->id][0]['m_services_unit']][0]['m_info_units_numb']:(isset($products->products_id[$_service->id])?$info->units[$products->products_id[$_service->id][0]['m_products_unit']][0]['m_info_units_numb']:'')).'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$info->getUnits($services->services_id[$_service->id][0]['m_services_unit']):(isset($products->products_id[$_service->id])?$info->getUnits($products->products_id[$_service->id][0]['m_products_unit']):'')).'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.number_format($_service->count,3,',',' ').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.number_format(($orders->orders_id[$p['order']][0]['m_orders_nds']==18?$_service->price/1.20:$_service->price),2,',',' ').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.number_format(($orders->orders_id[$p['order']][0]['m_orders_nds']==18?$_service->price*$_service->count/1.20:$_service->price*$_service->count),2,',',' ').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">без акциза</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">'.($orders->orders_id[$p['order']][0]['m_orders_nds']!=-1?$orders->orders_id[$p['order']][0]['m_orders_nds'].'%':'без НДС').'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.($orders->orders_id[$p['order']][0]['m_orders_nds']==18?number_format($_service->price*$_service->count*.20/1.20,2,',',' '):'0,00').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.number_format($_service->sum,2,',',' ').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">—</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">—</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">—</td>
					</tr>';
			$sum+=$_service->sum;
			$nds18+=$_service->sum*.20/1.20;
		}
		//итоговые суммы
		$body.='
					<tr>
						<td colspan="2" style="padding:1mm;border-bottom:1px solid #bbb;border-right:2px solid #000;"></td>
						<td colspan="5" style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;font-weight:bold">Всего к оплате</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;font-weight:bold;text-align:right;">'.number_format($sum-$nds18,2,',',' ').'</td>
						<td colspan="2" style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">×</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;font-weight:bold;text-align:right;">'.number_format($nds18,2,',',' ').'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;font-weight:bold;text-align:right;">'.number_format($sum,2,',',' ').'</td>
						<td colspan="3"></td>						
					</tr>
				</tbody>
			</table>';
		//первые подписи
		$body.='
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<thead>
					<tr>
						<td width="27.5mm" style="padding:0"></td>
						<td width="50mm" style="padding:0"></td>
						<td width="33mm" style="padding:0"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="44mm" style="padding:0"></td>
						<td width="32mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="41mm" style="padding:0"></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td rowspan="4" style="padding:0;border-right:2px solid #000;vertical-align:top;padding-top:2mm;">Документ<br/>составлен на<br/>{nbpg} листе(ах)</td>
						<td style="padding-left:3mm">Руководитель организации<br/>или иное уполномоченное лицо</td>
						<td>'.($p['doc_signature']&&$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:87mm;margin-top:-3mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'').'</td>
						<td></td>
						<td style="vertical-align:bottom;">'.transform::fio($org['m_contragents_c_director_name']).'</td>
						<td></td>
						<td>Главный бухгалтер<br/>или иное уполномоченное лицо</td>
						<td>'.($p['doc_signature']&&$org['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:208mm;margin-top:-5mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=bookkeeper"/></div>':'').'</td>
						<td></td>
						<td style="vertical-align:bottom;">'.transform::fio($org['m_contragents_c_bookkeeper_name']).'</td>					
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td colspan="2"></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm">Индивидуальный предприниматель</td>
						<td style="text-align:center;"></td>
						<td></td>
						<td style="text-align:center;"></td>
						<td></td>
						<td colspan="4" style="text-align:center;"></td>					
					</tr>
					<tr>
						<td style="border-bottom:2px solid #000;"></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;border-bottom:2px solid #000;text-align:center;color:#666">(подпись)</td>
						<td style="border-bottom:2px solid #000;"></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;border-bottom:2px solid #000;text-align:center;color:#666">(ф.и.о.)</td>
						<td style="border-bottom:2px solid #000;"></td>
						<td colspan="4" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;border-bottom:2px solid #000;text-align:center;color:#666">(реквизиты свидетельства о государственной регистрации ИП)</td>
					</tr>
				</tbody>
			</table>';
			
		//основание
		if($p['doc_base_text'])
			$p['doc_base']=$p['doc_base_text'];
		else{
			$p['doc_base']=$documents->getInfo($p['doc_base']);
			$p['doc_base']=$p['doc_base']['m_documents_templates_id']==4234525325?('Договор поставки №'.$p['doc_base']['m_documents_numb'].' от '.transform::date_f(dtu($p['doc_base']['m_documents_date']))):('Счет по заявке клиента №'.$p['doc_base']['m_documents_numb'].' от '.transform::date_f(dtu($p['doc_base']['m_documents_date'])));
		}
		$body.='
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<tbody>
					<tr>
						<td width="74mm" style="padding:0;padding-top:2mm;">Основание передачи (сдачи) / получения (приемки)</td>
						<td width="200mm">'.$p['doc_base'].'</td>
						<td width="5mm" style="padding:0;color:#666">&nbsp;[8]</td>				
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(договор; доверенность и др.)</td>
						<td></td>			
					</tr>
				</tbody>
			</table>';
		//данные о транспортировке
		$body.='
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<tbody>
					<tr>
						<td width="50mm" style="padding:0;">Данные о транспортировке и грузе</td>
						<td width="224mm"></td>
						<td width="5mm" style="padding:0;color:#666">&nbsp;[9]</td>				
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(транспортная накладная, поручение экспедитору, экспедиторская / складская расписка и др. / масса нетто/ брутто груза, если не приведены ссылки на транспортные документы, содержащие эти сведения)</td>
						<td></td>			
					</tr>
				</tbody>
			</table>';
		
		//вторые подписи
		$body.='
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<thead>
					<tr>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="8mm" style="padding:0;"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="7mm" style="padding:0"></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="6" style="padding:0;border-right:2px solid #000;vertical-align:top;padding-top:2mm;">Товар (груз) передал / услуги, результаты работ, права сдал</td>
						<td></td>
						<td colspan="6" style="padding:0;vertical-align:top;padding-top:2mm;">Товар (груз) получил / услуги, результаты работ, права принял</td>			
					</tr>
				</tbody>
			</table>
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<thead>
					<tr>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="8mm" style="padding:0;"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="7mm" style="padding:0"></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="padding-top:1mm;">'.$org['m_contragents_c_director_post'].'</td>
						<td></td>
						<td>'.($p['doc_signature']&&$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:55mm;margin-top:-5mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'').'</td>
						<td></td>
						<td>'.transform::fio($org['m_contragents_c_director_name']).'</td>
						<td style="vertical-align:top;border-right:2px solid #000;">&nbsp;[10]</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td style="vertical-align:top;">&nbsp;[15]</td>
					</tr>
					<tr>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(должность)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td style="vertical-align:top;border-right:2px solid #000;"></td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(должность)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="2">Дата отгрузки, передачи (сдачи)</td>
						<td style="border-bottom:1px solid #bbb;">'.transform::date_f(dtu($p['date_ship'])).'</td>
						<td colspan="2"></td>
						<td style="border-right:2px solid #000;vertical-align:top;">&nbsp;[11]</td>
						<td></td>
						<td>Дата получения (приемки)</td>
						<td></td>
						<td style="border-bottom:1px solid #bbb;"></td>
						<td colspan="2"></td>
						<td style="padding:0;vertical-align:top;">&nbsp;[16]</td>
					</tr>
					<tr>
						<td colspan="6" style="padding:0;padding-top:1mm;border-right:2px solid #000;vertical-align:top;">Иные сведения об отгрузке, передаче</td>
						<td></td>
						<td colspan="6" style="padding:0;padding-top:1mm;vertical-align:top;">Иные сведения о получении, приемке</td>			
					</tr>
					<tr>
						<td colspan="5"></td>
						<td style="vertical-align:top;border-right:2px solid #000;">&nbsp;[12]</td>
						<td></td>
						<td colspan="5"></td>
						<td style="vertical-align:top;">&nbsp;[17]</td>						
					</tr>
					<tr>
						<td colspan="5" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ссылки на неотъемлемые приложения, сопутствующие документы, иные документы и т.п.)</td>
						<td style="border-right:2px solid #000;"></td>
						<td></td>
						<td colspan="5" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(информация о наличии/отсутствии претензии; ссылки на неотъемлемые приложения, и другие документы и т.п.)</td>
						<td></td>						
					</tr>
					<tr>
						<td colspan="6" style="padding:0;border-right:2px solid #000;vertical-align:top;">Ответственный за правильность оформления факта хозяйственной жизни</td>
						<td></td>
						<td colspan="6" style="padding:0;vertical-align:top;">Ответственный за правильность оформления факта хозяйственной жизни</td>			
					</tr>
				</tbody>
			</table>
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<thead>
					<tr>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="8mm" style="padding:0;"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="7mm" style="padding:0"></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="padding-top:1mm;">главный бухгалтер</td>
						<td></td>
						<td>'.($p['doc_signature']&&$org['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:55mm;margin-top:-4mm"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=bookkeeper"/></div>':'').'</td>
						<td></td>
						<td>'.transform::fio($org['m_contragents_c_bookkeeper_name']).'</td>
						<td style="vertical-align:top;border-right:2px solid #000;">&nbsp;[13]</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td style="vertical-align:top;">&nbsp;[18]</td>
					</tr>
					<tr>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(должность)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td style="vertical-align:top;border-right:2px solid #000;"></td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(должность)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="6" style="padding:0;padding-top:1mm;border-right:2px solid #000;vertical-align:top;">Наименование экономического субъекта – составителя документа (в т.ч. комиссионера / агента)</td>
						<td></td>
						<td colspan="6" style="padding:0;vertical-align:top;">Наименование экономического субъекта – составителя документа</td>			
					</tr>
					<tr>
						<td colspan="5" style="padding-top:1mm;">'.$contragents->getName($p['org']).', ИНН/КПП '.$org['m_contragents_c_inn'].'/'.$org['m_contragents_c_kpp'].'</td>
						<td style="vertical-align:top;border-right:2px solid #000;">&nbsp;[14]</td>
						<td></td>
						<td colspan="5">'.$contragents->getName($p['client']).', ИНН/КПП '.$client['m_contragents_c_inn'].'/'.$client['m_contragents_c_kpp'].'</td>
						<td style="vertical-align:top;">&nbsp;[19]</td>
					</tr>
					<tr>
						<td colspan="5" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(может не заполняться при проставлении печати в М.П., может быть указан ИНН / КПП)</td>
						<td style="border-right:2px solid #000;"></td>
						<td></td>
						<td colspan="5" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(может не заполняться при проставлении печати в М.П., может быть указан ИНН / КПП)</td>
						<td></td>
					</tr>
					<tr>
						<td>М.П.</td>
						<td colspan="5" style="padding:0;border-right:2px solid #000;"></td>
						<td></td>
						<td>М.П.</td>
						<td colspan="6" style="padding:0;"></td>			
					</tr>
				</tbody>
			</table>';
		
		//подписи после таблицы
		if($p['doc_signature']){
			//$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:87mm;margin-top:-105mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			//$body.=$org['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:208mm;margin-top:-105mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=bookkeeper"/></div>':'';
		}
		
		//печати и подписи в нижнем блоке
		if($p['doc_signature']){
			//$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:55mm;margin-top:-62mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			//$body.=$org['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:55mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=bookkeeper"/></div>':'';
			$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:10mm;margin-top:-22mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
		}
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);

		$mpdf->WriteHTML($body);
		$mpdf->SetHTMLFooter('');
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}
	}
	
	//ТН
	public static function tn($p=array(
		'org'=>'',
		'client'=>'',
		'smeta'=>'',
		'doc_base'=>'',
		'date_ship'=>'',
		'doc_base_text'=>'',
		'doc_date'=>'',
		'doc_numb'=>'',
		'doc_sum'=>'',
		'doc_nds18'=>'',
		'doc_logo'=>null,
		'doc_bar'=>null,
		'doc_signature'=>null,
		'doc_header_org_name'=>null,
		'doc_header_org_address'=>null,
		'doc_header_org_tel'=>null,
		'doc_header_org_email'=>null,
		'items'=>false
	),$filename){
		global $info,$contragents,$orders,$services,$products,$documents,$buh;
		
		$smeta=$documents->getInfo($p['smeta']);
		$p['order']=$smeta['m_documents_order'];
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		
		$client_address=$info->getAddress($p['client']);
		$client_address=change_key($client_address,'m_address_type',true);
		
		if($client['m_contragents_consignee']){
			$client_consignee_address=$info->getAddress($client['m_contragents_consignee']);
			$client_consignee_address=change_key($client_consignee_address,'m_address_type',true);
		}
		
		$org_address=$info->getAddress($p['org']);
		$org_address=change_key($org_address,'m_address_type',true);
		
		if($org['m_contragents_consignee']){
			$org_consignee_address=$info->getAddress($org['m_contragents_consignee']);
			$org_consignee_address=change_key($org_consignee_address,'m_address_type',true);
		}
		
		$client['m_contragents_c_director_name']=$contragents->getDirector($client['m_contragents_id']);
		
		$pays=explode('|',$smeta['m_documents_pays']);
		$pays=array_diff($pays,array(null));
		$pays_list=array();
		foreach($pays as &$_pay)
			$pays_list[]='№ '.$buh->getInfo($_pay)['m_buh_payment_numb'].' от '.dtu($buh->getInfo($_pay)['m_buh_date'], 'd.m.Y');
		
		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('',array(297,260),9,'dejavuserifcondensed',7,7,15,8,6,6);
		$mpdf->SetAuthor(transform::typography(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2))));
		$mpdf->SetCreator(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2)));
		$mpdf->SetSubject(strcode2utf('Универсальный передаточный документ'));
		$mpdf->SetTitle(strcode2utf('Универсальный передаточный документ № '.$p['doc_numb'].' от '.dtu($p['doc_date'],'d.m.Y')));
		$mpdf->SetKeywords(strcode2utf('Универсальный передаточный документ № '.$p['doc_numb'].' от '.dtu($p['doc_date'],'d.m.Y')));		
		$mpdf->defaultCSS['P']['MARGIN']='0.7em 0';
		$mpdf->shrink_tables_to_fit=2;

		$body='';
		//количество не пустых товаров
		$count_all=1;
		
		//номер договора в хедере
		$header_numb='
			<div style="font-size:10px;color:#656565;float:left;width:60%;">
				Универсальный передаточный документ № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'<br/>
				Лист {PAGENO}
			</div>
		';

		if($p['doc_bar'])
			$header_numb.='
			<div style="float:right;width:35%;text-align:right;">
				<img src="/files/'.substr($filename,0,-2).'bar.png" style="height:24px"/>
			</div>';
		$mpdf->SetHTMLHeader($header_numb);
		
		//логотип, контакты, футер
		$header='
			<div style="width:100%;padding-bottom:1mm;clear:both;">
				<div style="float:left;color:#333;margin-right:30px;width:30%;">';
		$header.=($p['doc_logo'])?'<img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=logo" width="200"/>':'';
		$header.='
				</div>
				<div style="width:45%;float:right;font-size:11;">
					<p style="margin:0 0 3px 0;padding:0;font-weight:bold;">'.transform::typography($p['doc_header_org_name']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_address']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$p['doc_header_org_email'].'">'.transform::typography($p['doc_header_org_email']).'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		
		$body.=$header;//$mpdf->SetHTMLHeader($header);

		//заголовок
		$body.='<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
					<thead>
						<tr>
							<td width="28mm" style="padding:0"></td>
							<td width="32mm" style="padding:0"></td>
							<td width="32mm" style="padding:0"></td>
							<td width="7mm" style="padding:0;"></td>
							<td width="35mm" style="padding:0"></td>
							<td width="5mm" style="padding:0"></td>
							<td width="140mm" style="padding:0"></td>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td rowspan="3" style="padding:0;border-right:2px solid #000;">Универсальный передаточный документ</td>
						<td style="padding-left:3mm">Счет-фактура №</td>
						<td style="text-align:center;border-bottom:1px solid #bbb">'.$p['doc_numb'].'</td>
						<td style="padding:0;">&nbsp;от </td>
						<td style="text-align:center;border-bottom:1px solid #bbb">'.transform::date_f(dtu($p['doc_date'])).'</td>
						<td style="padding:0;color:#666">&nbsp;(1)</td>
						<td rowspan="3" style="padding:0;color:#666;text-align:right;">Приложение № 1<br>к постановлению Правительства Российской Федерации<br>от 26 декабря 2011 г. № 1137</td>
					</tr>
					<tr>
						<td style="padding-left:3mm">Исправление №</td>
						<td style="text-align:center;border-bottom:1px solid #bbb">—</td>
						<td style="padding:0;">&nbsp;от </td>
						<td style="text-align:center;border-bottom:1px solid #bbb">—</td>
						<td style="padding:0;color:#666">&nbsp;(1а)</td>
					</tr>
					<tr>
						<td colspan="5" style="padding-bottom:2mm;"></td>
					</tr>
					</tbody>
				</table>';
				
		//реквизиты
		$body.='<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
					<thead>
						<tr>
							<td width="28mm" style="padding:0"></td>
							<td width="55mm" style="padding:0"></td>
							<td width="191mm" style="padding:0"></td>
							<td width="5mm" style="padding:0"></td>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td rowspan="2" style="padding:0;border-right:2px solid #000;"><table><tr><td>Стутус:&nbsp;</td><td style="border:2px solid #000;">1</td></tr></table>
						</td>
						<td style="padding-left:3mm;font-weight:bold">Продавец:</td>
						<td style="border-bottom:1px solid #bbb">'.$org['m_contragents_c_name_short'].'</td>
						<td style="padding:0;color:#666">&nbsp;(2)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Адрес:</td>
						<td style="border-bottom:1px solid #bbb">'.$org_address[1]['m_address_full'].'</td>
						<td style="padding:0;color:#666">&nbsp;(2а)</td>
					</tr>
					<tr>
						<td rowspan="9" style="padding:0;border-right:2px solid #000;">1 — счет-фактура и передаточный документ (акт)<br/>2 — передаточный документ (акт)</td>
						<td style="padding-left:3mm;">ИНН/КПП продавца:</td>
						<td style="border-bottom:1px solid #bbb">'.$org['m_contragents_c_inn'].'</td>
						<td style="padding:0;color:#666">&nbsp;(2б)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Грузоотправитель и его адрес:</td>
						<td style="border-bottom:1px solid #bbb">'.($org['m_contragents_consignee']?$contragents->getName($org['m_contragents_consignee']).', '.$org_consignee_address[1]['m_address_full']:'он же').'</td>
						<td style="padding:0;color:#666">&nbsp;(3)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Грузополучатель и его адрес:</td>
						<td style="border-bottom:1px solid #bbb">'.($client['m_contragents_consignee']?$contragents->getName($client['m_contragents_consignee']).', '.$client_consignee_address[1]['m_address_full']:$client['m_contragents_c_name_short'].', '.$client_address[1]['m_address_full']).'</td>
						<td style="padding:0;color:#666">&nbsp;(4)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">К платежно-расчетному документу №:</td>
						<td style="border-bottom:1px solid #bbb">'.($pays_list?implode(', ',$pays_list):'&nbsp;от').'</td>
						<td style="padding:0;color:#666">&nbsp;(5)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;font-weight:bold">Покупатель:</td>
						<td style="border-bottom:1px solid #bbb">'.$client['m_contragents_c_name_short'].'</td>
						<td style="padding:0;color:#666">&nbsp;(6)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Адрес:</td>
						<td style="border-bottom:1px solid #bbb">'.$client_address[1]['m_address_full'].'</td>
						<td style="padding:0;color:#666">&nbsp;(6а)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">ИНН/КПП покупателя:</td>
						<td style="border-bottom:1px solid #bbb">'.$client['m_contragents_c_inn'].'</td>
						<td style="padding:0;color:#666">&nbsp;(6б)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Валюта: наименование, код</td>
						<td style="border-bottom:1px solid #bbb">Российский рубль, 643</td>
						<td style="padding:0;color:#666">&nbsp;(7)</td>
					</tr>
					<tr>
						<td colspan="3" style="padding-bottom:2mm;"></td>
					</tr>
					</tbody>
				</table>';
		
		//товары
		$sum=0;
		$nds18=0;
		$room_id=0;
		
		$body.='<table style="font-size:10;border-spacing:0;border-top:1px solid #bbb;line-height:1.2;border-left:1px solid #bbb;margin:0 auto;">
					<thead>
						<tr>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="7.5mm">№ п/п</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:2px solid #000;text-align:center;" width="20mm">Код товара/работ, услуг</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="55mm">Наименование товара (описание выполненных работ, оказанных услуг), имущественного права</td>
							<td colspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;" width="21mm">Единица измерения</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="17mm">Коли-<br/>чество (объём)</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="18mm">Цена (тариф) за единицу измерения</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="22mm">Стоимость товаров (работ, услуг), имущест-<br/>венных прав без налога — всего</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="16mm">В том числе сумма акциза</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="12mm">Нало-<br/>говая ставка</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="20mm">Сумма налога, предъяв-<br/>ляемая покупателю</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="22mm">Стоимость товаров (работ, услуг), имущест-<br/>венных прав с налогом — всего</td>
							<td colspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;" width="26mm">Страна происхождения</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="22mm">Номер таможенной декларации</td>
						</tr>
						<tr>
							<td style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="7mm">код</td>
							<td style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="14mm">условное обозна-<br/>чение (нацио-<br/>нальное)</td>
							<td style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="8mm">циф-<br/>ро-<br/>вой код</td>
							<td style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="18mm">краткое наиме-<br/>нование</td>
						</tr>
						<tr>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="7.5mm">А</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;border-right:2px solid #000;text-align:center;color:#666" width="20mm">Б</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="55mm">1</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="7mm">2</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="14mm">2а</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="17mm">3</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="18mm">4</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="22mm">5</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="16mm">6</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="12mm">7</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="20mm">8</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="22mm">9</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="8mm">10</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="18mm">10а</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="22mm">11</td>
						</tr>
					</thead>
					<tbody>';
		
		//товарные позиции
		$k=0;
		foreach($p['items'] as $_service){
			$room_id=$_service->room_id;	
			$pre_sum+=$_service->sum;
			//$pre_nds18+=$_service->sum*.20/1.20;
			$body.='<tr>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">'.++$k.'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:2px solid #000;">'.$_service->id.'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$services->services_id[$_service->id][0]['m_services_name']:(isset($products->products_id[$_service->id])?$products->products_id[$_service->id][0]['m_products_name']:'')).'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$info->units[$services->services_id[$_service->id][0]['m_services_unit']][0]['m_info_units_numb']:(isset($products->products_id[$_service->id])?$info->units[$products->products_id[$_service->id][0]['m_products_unit']][0]['m_info_units_numb']:'')).'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$info->getUnits($services->services_id[$_service->id][0]['m_services_unit']):(isset($products->products_id[$_service->id])?$info->getUnits($products->products_id[$_service->id][0]['m_products_unit']):'')).'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.number_format($_service->count,3,',',' ').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.number_format(($orders->orders_id[$p['order']][0]['m_orders_nds']==18?$_service->price/1.20:$_service->price),2,',',' ').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.number_format(($orders->orders_id[$p['order']][0]['m_orders_nds']==18?$_service->price*$_service->count/1.20:$_service->price*$_service->count),2,',',' ').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">без акциза</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">'.($orders->orders_id[$p['order']][0]['m_orders_nds']==18?'18%':'без НДС').'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.($orders->orders_id[$p['order']][0]['m_orders_nds']==18?number_format($_service->price*$_service->count*.20/1.20,2,',',' '):'0,00').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.number_format($_service->sum,2,',',' ').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">—</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">—</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">—</td>
					</tr>';
			$sum+=$_service->sum;
			$nds18+=$_service->sum*.20/1.20;
		}
		//итоговые суммы
		$body.='
					<tr>
						<td colspan="2" style="padding:1mm;border-bottom:1px solid #bbb;border-right:2px solid #000;"></td>
						<td colspan="5" style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;font-weight:bold">Всего к оплате</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;font-weight:bold;text-align:right;">'.number_format($sum-$nds18,2,',',' ').'</td>
						<td colspan="2" style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">×</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;font-weight:bold;text-align:right;">'.number_format($nds18,2,',',' ').'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;font-weight:bold;text-align:right;">'.number_format($sum,2,',',' ').'</td>
						<td colspan="3"></td>						
					</tr>
				</tbody>
			</table>';
		//первые подписи
		$body.='
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<thead>
					<tr>
						<td width="27.5mm" style="padding:0"></td>
						<td width="50mm" style="padding:0"></td>
						<td width="33mm" style="padding:0"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="44mm" style="padding:0"></td>
						<td width="32mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="41mm" style="padding:0"></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td rowspan="4" style="padding:0;border-right:2px solid #000;vertical-align:top;padding-top:2mm;">Документ<br/>составлен на<br/>{nbpg} листе(ах)</td>
						<td style="padding-left:3mm">Руководитель организации<br/>или иное уполномоченное лицо</td>
						<td>'.($p['doc_signature']&&$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:87mm;margin-top:-3mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'').'</td>
						<td></td>
						<td style="vertical-align:bottom;">'.transform::fio($org['m_contragents_c_director_name']).'</td>
						<td></td>
						<td>Главный бухгалтер<br/>или иное уполномоченное лицо</td>
						<td>'.($p['doc_signature']&&$org['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:208mm;margin-top:-5mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=bookkeeper"/></div>':'').'</td>
						<td></td>
						<td style="vertical-align:bottom;">'.transform::fio($org['m_contragents_c_bookkeeper_name']).'</td>					
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td colspan="2"></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm">Индивидуальный предприниматель</td>
						<td style="text-align:center;"></td>
						<td></td>
						<td style="text-align:center;"></td>
						<td></td>
						<td colspan="4" style="text-align:center;"></td>					
					</tr>
					<tr>
						<td style="border-bottom:2px solid #000;"></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;border-bottom:2px solid #000;text-align:center;color:#666">(подпись)</td>
						<td style="border-bottom:2px solid #000;"></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;border-bottom:2px solid #000;text-align:center;color:#666">(ф.и.о.)</td>
						<td style="border-bottom:2px solid #000;"></td>
						<td colspan="4" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;border-bottom:2px solid #000;text-align:center;color:#666">(реквизиты свидетельства о государственной регистрации ИП)</td>
					</tr>
				</tbody>
			</table>';
			
		//основание
		if($p['doc_base_text'])
			$p['doc_base']=$p['doc_base_text'];
		else{
			$p['doc_base']=$documents->getInfo($p['doc_base']);
			$p['doc_base']=$p['doc_base']['m_documents_templates_id']==4234525325?('Договор поставки №'.$p['doc_base']['m_documents_numb'].' от '.transform::date_f(dtu($p['doc_base']['m_documents_date']))):('Счет по заявке клиента №'.$p['doc_base']['m_documents_numb'].' от '.transform::date_f(dtu($p['doc_base']['m_documents_date'])));
		}
		$body.='
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<tbody>
					<tr>
						<td width="74mm" style="padding:0;padding-top:2mm;">Основание передачи (сдачи) / получения (приемки)</td>
						<td width="200mm">'.$p['doc_base'].'</td>
						<td width="5mm" style="padding:0;color:#666">&nbsp;[8]</td>				
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(договор; доверенность и др.)</td>
						<td></td>			
					</tr>
				</tbody>
			</table>';
		//данные о транспортировке
		$body.='
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<tbody>
					<tr>
						<td width="50mm" style="padding:0;">Данные о транспортировке и грузе</td>
						<td width="224mm"></td>
						<td width="5mm" style="padding:0;color:#666">&nbsp;[9]</td>				
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(транспортная накладная, поручение экспедитору, экспедиторская / складская расписка и др. / масса нетто/ брутто груза, если не приведены ссылки на транспортные документы, содержащие эти сведения)</td>
						<td></td>			
					</tr>
				</tbody>
			</table>';
		
		//вторые подписи
		$body.='
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<thead>
					<tr>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="8mm" style="padding:0;"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="7mm" style="padding:0"></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="6" style="padding:0;border-right:2px solid #000;vertical-align:top;padding-top:2mm;">Товар (груз) передал / услуги, результаты работ, права сдал</td>
						<td></td>
						<td colspan="6" style="padding:0;vertical-align:top;padding-top:2mm;">Товар (груз) получил / услуги, результаты работ, права принял</td>			
					</tr>
				</tbody>
			</table>
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<thead>
					<tr>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="8mm" style="padding:0;"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="7mm" style="padding:0"></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="padding-top:1mm;">'.$org['m_contragents_c_director_post'].'</td>
						<td></td>
						<td>'.($p['doc_signature']&&$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:55mm;margin-top:-5mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'').'</td>
						<td></td>
						<td>'.transform::fio($org['m_contragents_c_director_name']).'</td>
						<td style="vertical-align:top;border-right:2px solid #000;">&nbsp;[10]</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td style="vertical-align:top;">&nbsp;[15]</td>
					</tr>
					<tr>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(должность)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td style="vertical-align:top;border-right:2px solid #000;"></td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(должность)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="2">Дата отгрузки, передачи (сдачи)</td>
						<td style="border-bottom:1px solid #bbb;">'.transform::date_f(dtu($p['date_ship'])).'</td>
						<td colspan="2"></td>
						<td style="border-right:2px solid #000;vertical-align:top;">&nbsp;[11]</td>
						<td></td>
						<td>Дата получения (приемки)</td>
						<td></td>
						<td style="border-bottom:1px solid #bbb;"></td>
						<td colspan="2"></td>
						<td style="padding:0;vertical-align:top;">&nbsp;[16]</td>
					</tr>
					<tr>
						<td colspan="6" style="padding:0;padding-top:1mm;border-right:2px solid #000;vertical-align:top;">Иные сведения об отгрузке, передаче</td>
						<td></td>
						<td colspan="6" style="padding:0;padding-top:1mm;vertical-align:top;">Иные сведения о получении, приемке</td>			
					</tr>
					<tr>
						<td colspan="5"></td>
						<td style="vertical-align:top;border-right:2px solid #000;">&nbsp;[12]</td>
						<td></td>
						<td colspan="5"></td>
						<td style="vertical-align:top;">&nbsp;[17]</td>						
					</tr>
					<tr>
						<td colspan="5" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ссылки на неотъемлемые приложения, сопутствующие документы, иные документы и т.п.)</td>
						<td style="border-right:2px solid #000;"></td>
						<td></td>
						<td colspan="5" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(информация о наличии/отсутствии претензии; ссылки на неотъемлемые приложения, и другие документы и т.п.)</td>
						<td></td>						
					</tr>
					<tr>
						<td colspan="6" style="padding:0;border-right:2px solid #000;vertical-align:top;">Ответственный за правильность оформления факта хозяйственной жизни</td>
						<td></td>
						<td colspan="6" style="padding:0;vertical-align:top;">Ответственный за правильность оформления факта хозяйственной жизни</td>			
					</tr>
				</tbody>
			</table>
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<thead>
					<tr>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="8mm" style="padding:0;"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="7mm" style="padding:0"></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="padding-top:1mm;">главный бухгалтер</td>
						<td></td>
						<td>'.($p['doc_signature']&&$org['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:55mm;margin-top:-4mm"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=bookkeeper"/></div>':'').'</td>
						<td></td>
						<td>'.transform::fio($org['m_contragents_c_bookkeeper_name']).'</td>
						<td style="vertical-align:top;border-right:2px solid #000;">&nbsp;[13]</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td style="vertical-align:top;">&nbsp;[18]</td>
					</tr>
					<tr>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(должность)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td style="vertical-align:top;border-right:2px solid #000;"></td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(должность)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="6" style="padding:0;padding-top:1mm;border-right:2px solid #000;vertical-align:top;">Наименование экономического субъекта – составителя документа (в т.ч. комиссионера / агента)</td>
						<td></td>
						<td colspan="6" style="padding:0;vertical-align:top;">Наименование экономического субъекта – составителя документа</td>			
					</tr>
					<tr>
						<td colspan="5" style="padding-top:1mm;">'.$contragents->getName($p['org']).', ИНН/КПП '.$org['m_contragents_c_inn'].'/'.$org['m_contragents_c_kpp'].'</td>
						<td style="vertical-align:top;border-right:2px solid #000;">&nbsp;[14]</td>
						<td></td>
						<td colspan="5">'.$contragents->getName($p['client']).', ИНН/КПП '.$client['m_contragents_c_inn'].'/'.$client['m_contragents_c_kpp'].'</td>
						<td style="vertical-align:top;">&nbsp;[19]</td>
					</tr>
					<tr>
						<td colspan="5" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(может не заполняться при проставлении печати в М.П., может быть указан ИНН / КПП)</td>
						<td style="border-right:2px solid #000;"></td>
						<td></td>
						<td colspan="5" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(может не заполняться при проставлении печати в М.П., может быть указан ИНН / КПП)</td>
						<td></td>
					</tr>
					<tr>
						<td>М.П.</td>
						<td colspan="5" style="padding:0;border-right:2px solid #000;"></td>
						<td></td>
						<td>М.П.</td>
						<td colspan="6" style="padding:0;"></td>			
					</tr>
				</tbody>
			</table>';
		
		//подписи после таблицы
		if($p['doc_signature']){
			//$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:87mm;margin-top:-105mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			//$body.=$org['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:208mm;margin-top:-105mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=bookkeeper"/></div>':'';
		}
		
		//печати и подписи в нижнем блоке
		if($p['doc_signature']){
			//$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:55mm;margin-top:-62mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			//$body.=$org['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:55mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=bookkeeper"/></div>':'';
			$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:10mm;margin-top:-22mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
		}
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);

		$mpdf->WriteHTML($body);
		$mpdf->SetHTMLFooter('');
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}
	}
	
	//АКТ
	public static function act_main($p=array(
		'org'=>'',
		'client'=>'',
		'smeta'=>'',
		'doc_base'=>'',
		'date_ship'=>'',
		'doc_base_text'=>'',
		'doc_date'=>'',
		'doc_numb'=>'',
		'doc_sum'=>'',
		'doc_nds18'=>'',
		'doc_logo'=>null,
		'doc_bar'=>null,
		'doc_signature'=>null,
		'doc_header_org_name'=>null,
		'doc_header_org_address'=>null,
		'doc_header_org_tel'=>null,
		'doc_header_org_email'=>null,
		'items'=>false
	),$filename){
		global $info,$contragents,$orders,$services,$products,$documents,$buh;
		
		$smeta=$documents->getInfo($p['smeta']);
		$p['order']=$smeta['m_documents_order'];
		
		$org=$contragents->getInfo($p['org']);
		$client=$contragents->getInfo($p['client']);
		
		$client_address=$info->getAddress($p['client']);
		$client_address=change_key($client_address,'m_address_type',true);
		
		if($client['m_contragents_consignee']){
			$client_consignee_address=$info->getAddress($client['m_contragents_consignee']);
			$client_consignee_address=change_key($client_consignee_address,'m_address_type',true);
		}
		
		$org_address=$info->getAddress($p['org']);
		$org_address=change_key($org_address,'m_address_type',true);
		
		if($org['m_contragents_consignee']){
			$org_consignee_address=$info->getAddress($org['m_contragents_consignee']);
			$org_consignee_address=change_key($org_consignee_address,'m_address_type',true);
		}
		
		$client['m_contragents_c_director_name']=$contragents->getDirector($client['m_contragents_id']);
		
		$pays=explode('|',$smeta['m_documents_pays']);
		$pays=array_diff($pays,array(null));
		$pays_list=array();
		foreach($pays as &$_pay)
			$pays_list[]='№ '.$buh->getInfo($_pay)['m_buh_payment_numb'].' от '.dtu($buh->getInfo($_pay)['m_buh_date'], 'd.m.Y');
		
		require_once(__DIR__.'/import/MPDF57/mpdf.php');
		//создание pdf класса, отступы
		$mpdf=new mPDF('',array(297,260),9,'dejavuserifcondensed',7,7,15,8,6,6);
		$mpdf->SetAuthor(transform::typography(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2))));
		$mpdf->SetCreator(implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2,2)));
		$mpdf->SetSubject(strcode2utf('Универсальный передаточный документ'));
		$mpdf->SetTitle(strcode2utf('Универсальный передаточный документ № '.$p['doc_numb'].' от '.dtu($p['doc_date'],'d.m.Y')));
		$mpdf->SetKeywords(strcode2utf('Универсальный передаточный документ № '.$p['doc_numb'].' от '.dtu($p['doc_date'],'d.m.Y')));		
		$mpdf->defaultCSS['P']['MARGIN']='0.7em 0';
		$mpdf->shrink_tables_to_fit=2;

		$body='';
		//количество не пустых товаров
		$count_all=1;
		
		//номер договора в хедере
		$header_numb='
			<div style="font-size:10px;color:#656565;float:left;width:60%;">
				Универсальный передаточный документ № '.$p['doc_numb'].' от '.transform::date_f(dtu($p['doc_date'])).'<br/>
				Лист {PAGENO}
			</div>
		';

		if($p['doc_bar'])
			$header_numb.='
			<div style="float:right;width:35%;text-align:right;">
				<img src="/files/'.substr($filename,0,-9).'bar.png" style="height:24px"/>
			</div>';
		$mpdf->SetHTMLHeader($header_numb);
		
		//логотип, контакты, футер
		$header='
			<div style="width:100%;padding-bottom:1mm;clear:both;">
				<div style="float:left;color:#333;margin-right:30px;width:30%;">';
		$header.=($p['doc_logo'])?'<img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=logo" width="200"/>':'';
		$header.='
				</div>
				<div style="width:45%;float:right;font-size:11;">
					<p style="margin:0 0 3px 0;padding:0;font-weight:bold;">'.transform::typography($p['doc_header_org_name']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_address']).'</p>
					<p style="margin:0 0 3px 0;padding:0;">'.transform::typography($p['doc_header_org_tel']).'</p>
					<p style="margin:0 0 3px 0;padding:0;"><a href="mailto:'.$p['doc_header_org_email'].'">'.transform::typography($p['doc_header_org_email']).'</a></p>
				</div>
			</div>';
		$header=str_replace("\r\n","",$header);
		$header=str_replace("\t","",$header);
		$header=str_replace("\n","",$header);
		
		$body.=$header;//$mpdf->SetHTMLHeader($header);

		//заголовок
		$body.='<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
					<thead>
						<tr>
							<td width="28mm" style="padding:0"></td>
							<td width="32mm" style="padding:0"></td>
							<td width="32mm" style="padding:0"></td>
							<td width="7mm" style="padding:0;"></td>
							<td width="35mm" style="padding:0"></td>
							<td width="5mm" style="padding:0"></td>
							<td width="140mm" style="padding:0"></td>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td rowspan="3" style="padding:0;border-right:2px solid #000;">Универсальный передаточный документ</td>
						<td style="padding-left:3mm">Счет-фактура №</td>
						<td style="text-align:center;border-bottom:1px solid #bbb">'.$p['doc_numb'].'</td>
						<td style="padding:0;">&nbsp;от </td>
						<td style="text-align:center;border-bottom:1px solid #bbb">'.transform::date_f(dtu($p['doc_date'])).'</td>
						<td style="padding:0;color:#666">&nbsp;(1)</td>
						<td rowspan="3" style="padding:0;color:#666;text-align:right;">Приложение № 1<br>к постановлению Правительства Российской Федерации<br>от 26 декабря 2011 г. № 1137</td>
					</tr>
					<tr>
						<td style="padding-left:3mm">Исправление №</td>
						<td style="text-align:center;border-bottom:1px solid #bbb">—</td>
						<td style="padding:0;">&nbsp;от </td>
						<td style="text-align:center;border-bottom:1px solid #bbb">—</td>
						<td style="padding:0;color:#666">&nbsp;(1а)</td>
					</tr>
					<tr>
						<td colspan="5" style="padding-bottom:2mm;"></td>
					</tr>
					</tbody>
				</table>';
				
		//реквизиты
		$body.='<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
					<thead>
						<tr>
							<td width="28mm" style="padding:0"></td>
							<td width="55mm" style="padding:0"></td>
							<td width="191mm" style="padding:0"></td>
							<td width="5mm" style="padding:0"></td>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td rowspan="2" style="padding:0;border-right:2px solid #000;"><table><tr><td>Стутус:&nbsp;</td><td style="border:2px solid #000;">1</td></tr></table>
						</td>
						<td style="padding-left:3mm;font-weight:bold">Продавец:</td>
						<td style="border-bottom:1px solid #bbb">'.$org['m_contragents_c_name_short'].'</td>
						<td style="padding:0;color:#666">&nbsp;(2)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Адрес:</td>
						<td style="border-bottom:1px solid #bbb">'.$org_address[1]['m_address_full'].'</td>
						<td style="padding:0;color:#666">&nbsp;(2а)</td>
					</tr>
					<tr>
						<td rowspan="9" style="padding:0;border-right:2px solid #000;">1 — счет-фактура и передаточный документ (акт)<br/>2 — передаточный документ (акт)</td>
						<td style="padding-left:3mm;">ИНН/КПП продавца:</td>
						<td style="border-bottom:1px solid #bbb">'.$org['m_contragents_c_inn'].'</td>
						<td style="padding:0;color:#666">&nbsp;(2б)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Грузоотправитель и его адрес:</td>
						<td style="border-bottom:1px solid #bbb">'.($org['m_contragents_consignee']?$contragents->getName($org['m_contragents_consignee']).', '.$org_consignee_address[1]['m_address_full']:'он же').'</td>
						<td style="padding:0;color:#666">&nbsp;(3)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Грузополучатель и его адрес:</td>
						<td style="border-bottom:1px solid #bbb">'.($client['m_contragents_consignee']?$contragents->getName($client['m_contragents_consignee']).', '.$client_consignee_address[1]['m_address_full']:$client['m_contragents_c_name_short'].', '.$client_address[1]['m_address_full']).'</td>
						<td style="padding:0;color:#666">&nbsp;(4)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">К платежно-расчетному документу №:</td>
						<td style="border-bottom:1px solid #bbb">'.($pays_list?implode(', ',$pays_list):'&nbsp;от').'</td>
						<td style="padding:0;color:#666">&nbsp;(5)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;font-weight:bold">Покупатель:</td>
						<td style="border-bottom:1px solid #bbb">'.$client['m_contragents_c_name_short'].'</td>
						<td style="padding:0;color:#666">&nbsp;(6)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Адрес:</td>
						<td style="border-bottom:1px solid #bbb">'.$client_address[1]['m_address_full'].'</td>
						<td style="padding:0;color:#666">&nbsp;(6а)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">ИНН/КПП покупателя:</td>
						<td style="border-bottom:1px solid #bbb">'.$client['m_contragents_c_inn'].'</td>
						<td style="padding:0;color:#666">&nbsp;(6б)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm;">Валюта: наименование, код</td>
						<td style="border-bottom:1px solid #bbb">Российский рубль, 643</td>
						<td style="padding:0;color:#666">&nbsp;(7)</td>
					</tr>
					<tr>
						<td colspan="3" style="padding-bottom:2mm;"></td>
					</tr>
					</tbody>
				</table>';
		
		//товары
		$sum=0;
		$nds18=0;
		$room_id=0;
		
		$body.='<table style="font-size:10;border-spacing:0;border-top:1px solid #bbb;line-height:1.2;border-left:1px solid #bbb;margin:0 auto;">
					<thead>
						<tr>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="7.5mm">№ п/п</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:2px solid #000;text-align:center;" width="20mm">Код товара/работ, услуг</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="55mm">Наименование товара (описание выполненных работ, оказанных услуг), имущественного права</td>
							<td colspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;" width="21mm">Единица измерения</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="17mm">Коли-<br/>чество (объём)</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="18mm">Цена (тариф) за единицу измерения</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="22mm">Стоимость товаров (работ, услуг), имущест-<br/>венных прав без налога — всего</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="16mm">В том числе сумма акциза</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="12mm">Нало-<br/>говая ставка</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="20mm">Сумма налога, предъяв-<br/>ляемая покупателю</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="22mm">Стоимость товаров (работ, услуг), имущест-<br/>венных прав с налогом — всего</td>
							<td colspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;border-bottom:1px solid #bbb;text-align:center;" width="26mm">Страна происхождения</td>
							<td rowspan="2" style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="22mm">Номер таможенной декларации</td>
						</tr>
						<tr>
							<td style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="7mm">код</td>
							<td style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="14mm">условное обозна-<br/>чение (нацио-<br/>нальное)</td>
							<td style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="8mm">циф-<br/>ро-<br/>вой код</td>
							<td style="background:#eee;padding:1mm;border-right:1px solid #bbb;text-align:center;" width="18mm">краткое наиме-<br/>нование</td>
						</tr>
						<tr>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="7.5mm">А</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;border-right:2px solid #000;text-align:center;color:#666" width="20mm">Б</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="55mm">1</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="7mm">2</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="14mm">2а</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="17mm">3</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="18mm">4</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="22mm">5</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="16mm">6</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="12mm">7</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="20mm">8</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="22mm">9</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="8mm">10</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="18mm">10а</td>
							<td style="padding:1mm;border:1px solid #bbb;border-left:none;text-align:center;color:#666" width="22mm">11</td>
						</tr>
					</thead>
					<tbody>';
		
		//товарные позиции
		$k=0;
		$is_nds_from_items=false;
		foreach($p['items'] as $_service){
			$nds_item=isset($_service->nds)?$_service->nds:$orders->orders_id[$p['order']][0]['m_orders_nds'];
			$is_nds_from_items=$nds_item!=-1?true:$is_nds_from_items;
			
			$room_id=$_service->room_id;	
			$pre_sum+=$_service->sum;
			//$pre_nds18+=$_service->sum*.20/1.20;
			$body.='<tr>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">'.++$k.'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:2px solid #000;">'.$_service->id.'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$services->services_id[$_service->id][0]['m_services_name']:(isset($products->products_id[$_service->id])?$products->products_id[$_service->id][0]['m_products_name']:'')).'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">—</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$info->units[$services->services_id[$_service->id][0]['m_services_unit']][0]['m_info_units_numb']:(isset($products->products_id[$_service->id])?$info->units[$products->products_id[$_service->id][0]['m_products_unit']][0]['m_info_units_numb']:'')).'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">'.($_service->table=='services'&&isset($services->services_id[$_service->id])?$info->getUnits($services->services_id[$_service->id][0]['m_services_unit']):(isset($products->products_id[$_service->id])?$info->getUnits($products->products_id[$_service->id][0]['m_products_unit']):'')).'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.number_format($_service->count,3,',',' ').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.number_format(($nds_item!=-1?$_service->price/(1+$nds_item/100):$_service->price),2,',',' ').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.number_format(($nds_item!=-1?$_service->price*$_service->count/(1+$nds_item/100):$_service->price*$_service->count),2,',',' ').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">без акциза</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;">'.($nds_item!=-1?$nds_item.'%':'без НДС').'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.($nds_item!=-1?number_format($_service->price*$_service->count*($nds_item/100)/(1+$nds_item/100),2,',',' '):'0,00').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:right;"><nobr>'.number_format($_service->sum,2,',',' ').'</nobr></td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">—</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">—</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;">—</td>
					</tr>';
			$sum+=$_service->sum;
			$nds18+=$nds_item!=-1?round($_service->sum*($nds_item/100)/(1+$nds_item/100),2):0;
		}

		//итоговые суммы
		$body.='
					<tr>
						<td colspan="2" style="padding:1mm;border-bottom:1px solid #bbb;border-right:2px solid #000;"></td>
						<td colspan="5" style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;font-weight:bold">Всего к оплате</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;font-weight:bold;text-align:right;">'.number_format($sum-$nds18,2,',',' ').'</td>
						<td colspan="2" style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;text-align:center;">×</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;font-weight:bold;text-align:right;">'.number_format($nds18,2,',',' ').'</td>
						<td style="padding:1mm;border-bottom:1px solid #bbb;border-right:1px solid #bbb;font-weight:bold;text-align:right;">'.number_format($sum,2,',',' ').'</td>
						<td colspan="3"></td>						
					</tr>
				</tbody>
			</table>';
		//первые подписи
		$body.='
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<thead>
					<tr>
						<td width="27.5mm" style="padding:0"></td>
						<td width="50mm" style="padding:0"></td>
						<td width="33mm" style="padding:0"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="44mm" style="padding:0"></td>
						<td width="32mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="41mm" style="padding:0"></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td rowspan="4" style="padding:0;border-right:2px solid #000;vertical-align:top;padding-top:2mm;">Документ<br/>составлен на<br/>{nbpg} листе(ах)</td>
						<td style="padding-left:3mm">Руководитель организации<br/>или иное уполномоченное лицо</td>
						<td>'.($p['doc_signature']&&$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:87mm;margin-top:-3mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'').'</td>
						<td></td>
						<td style="vertical-align:bottom;">'.transform::fio($org['m_contragents_c_director_name']).'</td>
						<td></td>
						<td>Главный бухгалтер<br/>или иное уполномоченное лицо</td>
						<td>'.($p['doc_signature']&&$org['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:208mm;margin-top:-5mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=bookkeeper"/></div>':'').'</td>
						<td></td>
						<td style="vertical-align:bottom;">'.transform::fio($org['m_contragents_c_bookkeeper_name']).'</td>					
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td colspan="2"></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
					</tr>
					<tr>
						<td style="padding-left:3mm">Индивидуальный предприниматель</td>
						<td style="text-align:center;"></td>
						<td></td>
						<td style="text-align:center;"></td>
						<td></td>
						<td colspan="4" style="text-align:center;"></td>					
					</tr>
					<tr>
						<td style="border-bottom:2px solid #000;"></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;border-bottom:2px solid #000;text-align:center;color:#666">(подпись)</td>
						<td style="border-bottom:2px solid #000;"></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;border-bottom:2px solid #000;text-align:center;color:#666">(ф.и.о.)</td>
						<td style="border-bottom:2px solid #000;"></td>
						<td colspan="4" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;border-bottom:2px solid #000;text-align:center;color:#666">(реквизиты свидетельства о государственной регистрации ИП)</td>
					</tr>
				</tbody>
			</table>';
			
		//основание
		if($p['doc_base_text'])
			$p['doc_base']=$p['doc_base_text'];
		else{
			$p['doc_base']=$documents->getInfo($p['doc_base']);
			$p['doc_base']=$p['doc_base']['m_documents_templates_id']==4234525325?('Договор поставки №'.$p['doc_base']['m_documents_numb'].' от '.transform::date_f(dtu($p['doc_base']['m_documents_date']))):('Счет по заявке клиента №'.$p['doc_base']['m_documents_numb'].' от '.transform::date_f(dtu($p['doc_base']['m_documents_date'])));
		}
		$body.='
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<tbody>
					<tr>
						<td width="74mm" style="padding:0;padding-top:2mm;">Основание передачи (сдачи) / получения (приемки)</td>
						<td width="200mm">'.$p['doc_base'].'</td>
						<td width="5mm" style="padding:0;color:#666">&nbsp;[8]</td>				
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(договор; доверенность и др.)</td>
						<td></td>			
					</tr>
				</tbody>
			</table>';
		//данные о транспортировке
		$body.='
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<tbody>
					<tr>
						<td width="50mm" style="padding:0;">Данные о транспортировке и грузе</td>
						<td width="224mm"></td>
						<td width="5mm" style="padding:0;color:#666">&nbsp;[9]</td>				
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(транспортная накладная, поручение экспедитору, экспедиторская / складская расписка и др. / масса нетто/ брутто груза, если не приведены ссылки на транспортные документы, содержащие эти сведения)</td>
						<td></td>			
					</tr>
				</tbody>
			</table>';
		
		//вторые подписи
		$body.='
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<thead>
					<tr>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="8mm" style="padding:0;"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="7mm" style="padding:0"></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="6" style="padding:0;border-right:2px solid #000;vertical-align:top;padding-top:2mm;">Товар (груз) передал / услуги, результаты работ, права сдал</td>
						<td></td>
						<td colspan="6" style="padding:0;vertical-align:top;padding-top:2mm;">Товар (груз) получил / услуги, результаты работ, права принял</td>			
					</tr>
				</tbody>
			</table>
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<thead>
					<tr>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="8mm" style="padding:0;"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="7mm" style="padding:0"></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="padding-top:1mm;">'.$org['m_contragents_c_director_post'].'</td>
						<td></td>
						<td>'.($p['doc_signature']&&$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:55mm;margin-top:-5mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'').'</td>
						<td></td>
						<td>'.transform::fio($org['m_contragents_c_director_name']).'</td>
						<td style="vertical-align:top;border-right:2px solid #000;">&nbsp;[10]</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td style="vertical-align:top;">&nbsp;[15]</td>
					</tr>
					<tr>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(должность)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td style="vertical-align:top;border-right:2px solid #000;"></td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(должность)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="2">Дата отгрузки, передачи (сдачи)</td>
						<td style="border-bottom:1px solid #bbb;">'.transform::date_f(dtu($p['date_ship'])).'</td>
						<td colspan="2"></td>
						<td style="border-right:2px solid #000;vertical-align:top;">&nbsp;[11]</td>
						<td></td>
						<td>Дата получения (приемки)</td>
						<td></td>
						<td style="border-bottom:1px solid #bbb;"></td>
						<td colspan="2"></td>
						<td style="padding:0;vertical-align:top;">&nbsp;[16]</td>
					</tr>
					<tr>
						<td colspan="6" style="padding:0;padding-top:1mm;border-right:2px solid #000;vertical-align:top;">Иные сведения об отгрузке, передаче</td>
						<td></td>
						<td colspan="6" style="padding:0;padding-top:1mm;vertical-align:top;">Иные сведения о получении, приемке</td>			
					</tr>
					<tr>
						<td colspan="5"></td>
						<td style="vertical-align:top;border-right:2px solid #000;">&nbsp;[12]</td>
						<td></td>
						<td colspan="5"></td>
						<td style="vertical-align:top;">&nbsp;[17]</td>						
					</tr>
					<tr>
						<td colspan="5" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ссылки на неотъемлемые приложения, сопутствующие документы, иные документы и т.п.)</td>
						<td style="border-right:2px solid #000;"></td>
						<td></td>
						<td colspan="5" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(информация о наличии/отсутствии претензии; ссылки на неотъемлемые приложения, и другие документы и т.п.)</td>
						<td></td>						
					</tr>
					<tr>
						<td colspan="6" style="padding:0;border-right:2px solid #000;vertical-align:top;">Ответственный за правильность оформления факта хозяйственной жизни</td>
						<td></td>
						<td colspan="6" style="padding:0;vertical-align:top;">Ответственный за правильность оформления факта хозяйственной жизни</td>			
					</tr>
				</tbody>
			</table>
			<table style="margin:0 auto;border-spacing:0;line-height:1.2;font-size:10;">
				<thead>
					<tr>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="8mm" style="padding:0;"></td>
						<td width="3mm" style="padding:0;"></td>
						<td width="40mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="42mm" style="padding:0"></td>
						<td width="3mm" style="padding:0"></td>
						<td width="43mm" style="padding:0"></td>
						<td width="7mm" style="padding:0"></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="padding-top:1mm;">главный бухгалтер</td>
						<td></td>
						<td>'.($p['doc_signature']&&$org['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:55mm;margin-top:-4mm"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=bookkeeper"/></div>':'').'</td>
						<td></td>
						<td>'.transform::fio($org['m_contragents_c_bookkeeper_name']).'</td>
						<td style="vertical-align:top;border-right:2px solid #000;">&nbsp;[13]</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td style="vertical-align:top;">&nbsp;[18]</td>
					</tr>
					<tr>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(должность)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td style="vertical-align:top;border-right:2px solid #000;"></td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(должность)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(подпись)</td>
						<td></td>
						<td style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(ф.и.о.)</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="6" style="padding:0;padding-top:1mm;border-right:2px solid #000;vertical-align:top;">Наименование экономического субъекта – составителя документа (в т.ч. комиссионера / агента)</td>
						<td></td>
						<td colspan="6" style="padding:0;vertical-align:top;">Наименование экономического субъекта – составителя документа</td>			
					</tr>
					<tr>
						<td colspan="5" style="padding-top:1mm;">'.$contragents->getName($p['org']).', ИНН/КПП '.$org['m_contragents_c_inn'].'/'.$org['m_contragents_c_kpp'].'</td>
						<td style="vertical-align:top;border-right:2px solid #000;">&nbsp;[14]</td>
						<td></td>
						<td colspan="5">'.$contragents->getName($p['client']).', ИНН/КПП '.$client['m_contragents_c_inn'].'/'.$client['m_contragents_c_kpp'].'</td>
						<td style="vertical-align:top;">&nbsp;[19]</td>
					</tr>
					<tr>
						<td colspan="5" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(может не заполняться при проставлении печати в М.П., может быть указан ИНН / КПП)</td>
						<td style="border-right:2px solid #000;"></td>
						<td></td>
						<td colspan="5" style="vertical-align:top;padding:0.5mm;border-top:1px solid #bbb;text-align:center;color:#666">(может не заполняться при проставлении печати в М.П., может быть указан ИНН / КПП)</td>
						<td></td>
					</tr>
					<tr>
						<td>М.П.</td>
						<td colspan="5" style="padding:0;border-right:2px solid #000;"></td>
						<td></td>
						<td>М.П.</td>
						<td colspan="6" style="padding:0;"></td>			
					</tr>
				</tbody>
			</table>';
		
		//подписи после таблицы
		if($p['doc_signature']){
			//$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:87mm;margin-top:-105mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			//$body.=$org['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:208mm;margin-top:-105mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=bookkeeper"/></div>':'';
		}
		
		//печати и подписи в нижнем блоке
		if($p['doc_signature']){
			//$body.=$org['m_contragents_c_signature_director']?'<div style="overflow:visible;position:absolute;left:55mm;margin-top:-62mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=director"/></div>':'';
			//$body.=$org['m_contragents_c_signature_bookkeeper']?'<div style="overflow:visible;position:absolute;left:55mm;margin-top:-30mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=bookkeeper"/></div>':'';
			$body.=$org['m_contragents_c_signature_stamp']?'<div style="overflow:visible;position:absolute;left:10mm;margin-top:-22mm;"><img src="/img/signature/signature.php?contragent='.$org['m_contragents_id'].'&type=stamp"/></div>':'';
		}
		
		$body=str_replace("\r\n","",$body);
		$body=str_replace("\t","",$body);
		$body=str_replace("\n","",$body);

		$mpdf->WriteHTML($body);
		$mpdf->SetHTMLFooter('');
		//если были записаны позиции
		if ($count_all){
			//создаем файл 
			$f_name=__DIR__.'/../../www/files/'.$filename.'.pdf';
			$mpdf->Output($f_name,'F');

			$f_size=filesize($f_name);
			$f_size=($f_size)?$f_size:0;
			if ($f_size>1048576)
				$f_size=round($f_size/1048576,1).' МБ';
			else
				$f_size=round($f_size/1024,1).' КБ';
			return $f_size;
		}
		//если прайс пустой
		else {
			unset($mpdf);
		}
	}
	
}

?>