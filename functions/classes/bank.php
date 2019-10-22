<?
/*

 * Ппроверка состояние платежей со статусом 2 (получен
 * orderId от банка)

 * Если статус = 2, то ставим статус поплаченного заказа.

 * Статусы:
 * 	'0' Заказ зарегистрирован, но не оплачен;
 *	'1' Предавторизованная сумма захолдирована (для двухстадийных платежей);
 *	'2' Проведена полная авторизация суммы заказа;
 *	'3' Авторизация отменена;	- код заказа -1
 *	'4' По транзакции была проведена операция возврата;  - код заказа -3
 *	'5' Инициирована авторизация через ACS банка-эмитента;
 *	'6' Авторизация отклонена.  - код заказа -1

*/
defined ('_DSITE') or die ('Access denied');
ini_set('display_errors',1);

class bank{
	private $username='',
			$password='',
			$url='https://web.rbsuat.com/ab/rest/';
	
	function register($orderId,$doc){
		/*
		 * Регистрация нового заказа в банке

		 * Ответ:
		 * 	id заказа в банке, ссылка для оплаты

		*/
		global $sql;
		
		$url=$this->url.'register.do';
		//если банк уже регистрировал заказ - отправляем готовую ссылку для оплаты
		if($doc['m_orders_bank_order_id'])
			return 'https://web.rbsuat.com/ab/merchants/typical/payment.html?mdOrder='.$doc['m_orders_bank_order_id'].'&language=ru';
		$doc_params=json_decode($doc['m_documents_params']);
		// ПАРАМЕТРЫ ДЛЯ ОТПРАВКИ ЗАПРОСА - ЛОГИН И ПАРОЛЬ
		$post_data=array(
			'userName'=>$this->username,
			'password'=>$this->password,
			'orderNumber'=>$orderId,
			'amount'=>$doc_params->doc_sum*100,
			'returnUrl'=>'https://www.formetoo.ru/pay-online/?order='.$orderId.'&hash='.md5($doc['m_documents_params'].'jh2o5gkf7'),
			'failUrl'=>'https://www.formetoo.ru/pay-online/?order='.$orderId.'&hash=fail',
			'clientId'=>$doc['m_documents_customer'],
			'sessionTimeoutSecs'=>259200,//счёт действителен 3 дня
			'description'=>'Заказ № '.$orderId.' (счёт на оплату № '.$doc['m_documents_numb'].') в интернет-магазине formetoo.ru'
		);
		// создание объекта curl
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($curl, CURLOPT_TIMEOUT, 4);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
		
		if($res=curl_exec($curl)){
			$res=json_decode($res,true);
			if(isset($res['orderId'])&&$res['orderId']&&isset($res['formUrl'])&&$res['formUrl']){
				$q='UPDATE `formetoo_cdb`.`m_orders` SET 
					`m_orders_bank_order_id`=\''.val($res['orderId'],array(),array(),1).'\',
					`m_orders_status`=2 
					WHERE
						`m_orders_id`='.(int)$orderId.' LIMIT 1;';
				if($sql->query($q))
					return $res['formUrl'];
			}
			else return 'SOME ERROR';
			
		}
		else return 'ERROR BANK REGISTER PAY';
		curl_close($curl);
	}
	
	function checkStatus($bankOrderId){
		/*
		 * Проверка состояние платежей со статусом 2 (получен
		 * orderId от банка)

		 * Если статус = 2, то ставим статус поплаченного заказа.

		 * Статусы:
		 * 	'0' Заказ зарегистрирован, но не оплачен;
		 *	'1' Предавторизованная сумма захолдирована (для двухстадийных платежей);
		 *	'2' Проведена полная авторизация суммы заказа;
		 *	'3' Авторизация отменена;	- код заказа -1
		 *	'4' По транзакции была проведена операция возврата;  - код заказа -3
		 *	'5' Инициирована авторизация через ACS банка-эмитента;
		 *	'6' Авторизация отклонена.  - код заказа -1

		*/
		global $sql;
		
		$url=$this->url.'getOrderStatus.do';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($curl, CURLOPT_TIMEOUT, 4);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_URL, $url);
		// ПАРАМЕТРЫ ДЛЯ ОТПРАВКИ ЗАПРОСА - ЛОГИН И ПАРОЛЬ
		$post_data=array(
			'userName'=>$this->username,
			'password'=>$this->password,
			'orderId'=>$bankOrderId
		);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
		if($res=curl_exec($curl)){
			$res_json=json_decode($res);
			var_dump($res_json);
			//если есть статус платежа
			if(isset($res_json->orderStatus)){
				$status=2;
				//если платёж с карты прошёл
				if($res_json->orderStatus==2)
					$status=3;
				//если отклонён, отменён
				elseif($res_json->orderStatus==3||$res_json->orderStatus==6)
					$status=-1;
				//если по платежу был возврат
				elseif($res_json->orderStatus==4)
					$status=-3;
				if($status!=2){
					$q='UPDATE `formetoo_cdb`.`m_orders` SET 
						`m_orders_bank_response`=\''.val($res,array(),array(),1).'\',
						`m_orders_status`='.$status.' 
						WHERE `m_orders_bank_order_id`=\''.$bankOrderId.'\' LIMIT 1;';
					if($sql->query($q))
						return true;
					else return false;
				}
				else return false;
			}
			else return false;
				
		}
		curl_close($curl);
	}

}
?>