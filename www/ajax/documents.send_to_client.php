<?
/*
 *	Отправка клиенту SMS или email со ссылку на оплату
*/

define ('_DSITE',1);

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/classes/user.php');
require_once(__DIR__.'/../../functions/classes/info.php');
require_once(__DIR__.'/../../functions/classes/orders.php');
require_once(__DIR__.'/../../functions/classes/contragents.php');
require_once(__DIR__.'/../../functions/classes/message.php');

global $e;

$sql=new sql;
$user=new user;
$info=new info;
$orders=new orders;
$contragents=new contragents;

$data['id']=array(1,null,null,10,1);
$data['type']=array(1,3,5);
array_walk($data,'check');

if(!$e){
	$q='SELECT * FROM `formetoo_cdb`.`m_documents`
			INNER JOIN `formetoo_cdb`.`m_documents_templates`
				ON `m_documents`.`m_documents_templates_id`=`m_documents_templates`.`m_documents_templates_id`
			WHERE `m_documents_id`='.$data['id'].' LIMIT 1;';
	$doc=$sql->query($q)[0];
	
	if($doc['m_documents_filesize']){
		$url_invoice='https://www.formetoo.ru/files/'.$doc['m_documents_templates_folder'].'/'.$doc['m_documents_folder'].'/'.$doc['m_documents_templates_filename'].'.pdf';
		$url_pay='https://www.formetoo.ru/pay-online/?order='.$doc['m_documents_order'].'&cid='.substr(md5($client['m_contragents_id']),0,10);
		
		$order=$orders->orders_id[$doc['m_documents_order']][0];
		$client=$contragents->getInfo($order['m_orders_customer']);
		$tel=$info->getTel($client['m_contragents_id'])[0];

		$q='SELECT * FROM `formetoo_main`.`m_users` WHERE `m_users_id`='.$client['m_contragents_user_id'].' LIMIT 1;';
		$userInfo=$sql->query($q)[0];
		$userEmail=$userInfo['m_users_email']?$userInfo['m_users_email']:$client['m_contragents_email'];
		$userTel=$userInfo['m_users_tel']?$userInfo['m_users_tel']:$tel['m_contragents_tel_numb'];
		
		$doc_status=json_decode($doc['m_documents_status'],true);
		
		//если есть почта пользователя - отправляем ему информацию об облате на почту
		if($userEmail&&$data['type']=='email'){
			$area=$sql->query('SELECT * FROM `formetoo_main`.`m_info_city` WHERE `m_info_city_url`=\''.$userInfo['m_users_city'].'\' OR `m_info_city_id`=\''.$userInfo['m_users_city'].'\' LIMIT 1;');
			if(!$area)
				$area=$sql->query('SELECT * FROM `formetoo_main`.`m_info_city` WHERE `m_info_city_url`=\'www\' LIMIT 1;');
			$area=$area[0];

			//состав заказа
			$data_doc_params=json_decode($doc['m_documents_params']);
			//товары
			$data_prod=array();
			$data_serv=array();
			$invoice_items=array();
			foreach($data_doc_params->items as $_item)
				foreach($_item->services as $_pos){
					$invoice_items[]=$_pos;
					if($_pos->table=='products')
						$data_prod[]=$_pos->id;
					else $data_serv[]=$_pos->id;
				}
			$q='SELECT `id`,`m_products_name_full`,`m_products_unit` FROM `formetoo_main`.`m_products` WHERE `id` IN(0,'.implode(',',$data_prod).');';	
			$data_prod=$sql->query($q,'id');
			$q='SELECT `m_services_id`,`m_services_name`,`m_services_unit` FROM `formetoo_main`.`m_services` WHERE `m_services_id` IN(0,'.implode(',',$data_serv).');';	
			$data_serv=$sql->query($q,'m_services_id');
			//ед. измерения
			$data_units=array();
			if($data_prod)
				foreach($data_prod as $_prod)
					$data_units[]=$_prod[0]['m_products_unit'];
			if($data_serv)
				foreach($data_serv as $_serv)
					$data_units[]=$_serv[0]['m_services_unit'];
			$data_units=array_unique($data_units);
			$q='SELECT * FROM `formetoo_cdb`.`m_info_units` WHERE `m_info_units_id` IN('.implode(',',$data_units).');';
			$data_units=$sql->query($q,'m_info_units_id');
			
			//параметры письма
			$message_data['data']['email']=$userEmail;
			$message_data['data']['city']=$area['m_info_city_url'];
			$message_data['user_id']=$userInfo['m_users_id'];
			$message_data['data']['subject']='Заказ № '.$doc['m_documents_order'].' подтверждён';
			$message_data['data']['name_to']=$userInfo['m_users_name']?$userInfo['m_users_name']:$client['m_contragents_p_fio'];
			if($order['m_orders_pay_method']==2){
				$message_data['data']['attach'][0]['file']='/var/www/www-root/data/www/crm.formetoo.ru/www/files/'.$doc['m_documents_templates_folder'].'/'.$doc['m_documents_folder'].'/'.$doc['m_documents_templates_filename'].'.pdf';
				$message_data['data']['attach'][0]['name']='Счёт на оплату № '.$doc['m_documents_numb'].' от '.transform::date_f(dtu($doc['m_documents_date']));
			}
			
			//письмо
			$msg='
				<table width="100%">
					<tr>
						<td width="100%" style="text-align:left;">
							<h1>Заказ подтверждён</h1>
						</td>
					</tr>
					<tr>
						<td width="100%" style="text-align:left">
							<p>Заказ № <strong>'.$doc['m_documents_order'].'</strong> в интернет-магазине formetoo.ru успешно подтверждён.</p>
							<p>Состав заказа:</p>
							<table class="info mini" width="100%">
								<tr>
									<td class="name" style="border-right:1px solid #ddd;" width="2%">№</td>
									<td class="name" style="border-right:1px solid #ddd;" width="5%">Арт.</td>
									<td class="name" style="border-right:1px solid #ddd;" width="61%">Наименование</td>
									<td class="name" style="border-right:1px solid #ddd;" width="10%">Цена</td>
									<td class="name" style="border-right:1px solid #ddd;" width="4%">Кол-во</td>
									<td class="name" style="border-right:1px solid #ddd;" width="6%"><nobr>Ед. изм.</nobr></td>
									<td class="name" width="12%">Сумма</td>
								</tr>';
				$sum_=0;
				foreach($invoice_items as $k=>$_item){
					$sum_+=$_item->sum;
					$msg.='
								<tr>
									<td class="center" style="border-right:1px solid #ddd;">'.($k+1).'</td>
									<td style="border-right:1px solid #ddd;">'.$_item->id.'</td>
									<td class="left" style="border-right:1px solid #ddd;">'.($_item->table=='products'?$data_prod[$_item->id][0]['m_products_name_full']:$data_serv[$_item->id][0]['m_services_name']).'</td>
									<td class="right" style="border-right:1px solid #ddd;">'.transform::price_o($_item->price,true,true).'&nbsp;₽</td>
									<td class="center" style="border-right:1px solid #ddd;">'.transform::price_o($_item->count,true,true).'</td>
									<td class="center" style="border-right:1px solid #ddd;">'.$data_units[$_item->table=='products'?$data_prod[$_item->id][0]['m_products_unit']:$data_serv[$_item->id][0]['m_services_unit']][0]['m_info_units_name'].'</td>
									<td class="right">'.transform::price_o($_item->sum,true,true).'&nbsp;₽</td>
								</tr>';
			
				}
				$msg.=			'<tr>
									<td class="name" style="border-right:1px solid #ddd;text-align:right;" colspan="6">Итого:</td>
									<td class="right">'.transform::price_o($sum_,true,true).'&nbsp;₽</td>
								</tr>
							</table>
							<p>'.($order['m_orders_pay_method']==2?'Выбранный способ оплаты: <strong>банковским переводом по выставленному счёту</strong>, копия документа приложена к письму. Изменить способ оплаты можно в личном кабинете или через оператора, позвонив нам. Скачать документ с нашего сервера можно по ссылке ниже:':'Выбранный способ оплаты: <strong>онлайн, банковской картой</strong>. Изменить способ оплаты можно в личном кабинете или через оператора, позвонив нам. Произвести оплату банковской картой можно по ссылке ниже (ввод данных карты производится в безопасном режиме на сайте банка):').'</p>
							<p><center><a target="_blank" href="'.($order['m_orders_pay_method']==2?$url_invoice:$url_pay).'" class="big-button-orange">'.($order['m_orders_pay_method']==2?'Скачать счёт на оплату':'Оплатить картой онлайн').'</a></center></p>
							<p></p>
							<p>Мы всегда рады помочь Вам в выборе строительных материалов. Задайте вопрос через обратную связь, электронную почту или позвоните нам. Наши контакты в '.$area['m_info_city_name_city_pr'].':</p>
							<p>телефон: <strong>'.$area['m_info_city_tel_office'].'</strong><br/>email: <strong><a href="mailto:'.$area['m_info_city_mail'].'">'.$area['m_info_city_mail'].'</a></strong></p>
							<p class="last">С уважением, formetoo.ru</p>
						</td>
					</tr>
				</table>';
			$message_data['data']['message']=base64_encode($msg);
			
			if(message::addQueueEmail($message_data)){
				//добавляем дату отправки в статус документа
				$t_['date']=dt();
				$t_['user']=$user->getInfo();
				$t_['type']='sendEmail';
				$doc_status['workflow'][]=$t_;
				$doc_status=json_encode($doc_status,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
				$q='UPDATE `formetoo_cdb`.`m_documents` SET 
					`m_documents_status`=\''.$doc_status.'\' 
					WHERE `m_documents_id`='.$doc['m_documents_id'].' LIMIT 1;';
				if($sql->query($q))
					echo 'SUCCESS';
			}
			/* print_r($message_data); */
			unset($message_data);
		}

		if($userTel&&$data['type']=='sms'){	
			//параметры письма
			$message_data['user_id']=$userInfo['m_users_id'];
			$message_data['data']['tel']=$userTel;
			
			//письмо
			$message_data['data']['message']=$order['m_orders_pay_method']==2?'Скачать счёт на оплату заказа №'.$doc['m_documents_order'].': '.$url_invoice:'Ссылка для оплаты заказа №'.$doc['m_documents_order'].': '.$url_pay;
			
			if(message::addQueueSMS($message_data)){
				//добавляем дату отправки в статус документа
				$t_['date']=dt();
				$t_['user']=$user->getInfo();
				$t_['type']='sendSMS';
				$doc_status['workflow'][]=$t_;
				$doc_status=json_encode($doc_status,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
				$q='UPDATE `formetoo_cdb`.`m_documents` SET 
					`m_documents_status`=\''.$doc_status.'\' 
					WHERE `m_documents_id`='.$doc['m_documents_id'].' LIMIT 1;';
				if($sql->query($q))
					echo 'SUCCESS';
			}
		}
		
		
		/* print_r($doc);
		print_r($order);
		print_r($client);
		print_r($tel);
		print_r($userInfo); */
		/* print_r($message_data); */
		
		
	}
	else echo 'EMPTY DOCUMENT';
}

unset($sql);
?>