<?
defined ('_DSITE') or die ('Access denied');

ini_set('display_errors',1);
require_once(__DIR__.'/../system.php');
require_once(__DIR__.'/../ccdb.php');
$sql=new sql;

class kassa{
	private $info;
	
	function __construct(){
		global $sql,$G;
		
		
	}

	function getInfo($id=null){
		global $sql;
		
		$q=$id?'SELECT * FROM `formetoo_cdb`.`m_buh_kassa` WHERE `m_buh_kassa_id`='.(int)$id.' LIMIT 1;':'SELECT * FROM `formetoo_cdb`.`m_buh_kassa` ORDER BY `m_buh_kassa_date` DESC;';
		return $sql->query($q,'m_buh_kassa_id');
	}
	
	//ОТПРАВКА ЗАПРОСА НА СЕРВЕР КАССЫ
	function sendResponse($request='',$doc_id=0,$delete=false){
		global $sql,$G;
		
		$id=get_id('m_buh_kassa');
		$request='{"uuid":"'.$id.'","request":'.$request.'}';
		
		$url='http://195.161.41.199/requests';
		// создание объекта curl
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($curl, CURLOPT_TIMEOUT, 4);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json'
			)
		);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_PORT, 25119);
		if(!$delete){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
			curl_setopt($curl, CURLOPT_URL, $url);
		}
		else{
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
			curl_setopt($curl, CURLOPT_URL, $url.'/'.$request);
		}

		if($res=curl_exec($curl)){
			if(mb_strpos($res,'201 Created')!==false)
				$status=1;
			elseif(mb_strpos($res,'400 Bad Request')!==false)
				$status=-1;
			elseif(mb_strpos($res,'409 Conflict')!==false)
				$status=-2;
			//для запроса о провере состояния обмена с ОФД
			elseif(mb_strpos($res,'notSentCount" : ')!==false){
				//все чеки отправлены
				$status='notInsert';
				//есть неотправленные чеки
				if(mb_strpos($res,'notSentCount" : 0,')===false)
					$status=2;
			}
			//для метода DELETE
			elseif(mb_strpos($res,'200 OK')!==false)
				$status=2;
			else return false;
			$q='INSERT INTO `formetoo_cdb`.`m_buh_kassa` SET 
				`m_buh_kassa_id`='.$id.',
				`m_buh_kassa_document`='.$doc_id.',
				`m_buh_kassa_date`=\''.dt().'\',
				`m_buh_kassa_status`='.$status.',
				`m_buh_kassa_request`=\''.val($request,array(),array(),1).'\'';
			//есть неотправленные чеки
			$q.=$status==2?', `m_buh_kassa_response`=\''.mb_substr($res,mb_strpos($res,'{')).'\';':';';
			if($status!='notInsert'&&$sql->query($q))
				return $id;
			else return false;
		}
		else return false;
	}
	
	//ПОЛУЧЕНИЕ ОТВЕТА С СЕРВЕРА КАССЫ
	function getResponse($id){
		global $sql,$G;
	
		$url='http://195.161.41.199/requests/'.$id;
		// создание объекта curl
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($curl, CURLOPT_TIMEOUT, 4);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json'
			)
		);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_PORT, 25119);
		curl_setopt($curl, CURLOPT_POST, 0);
		curl_setopt($curl, CURLOPT_URL, $url);

		if($res=curl_exec($curl)){
			$status=1;
			if($res_json=json_decode($res)){
				switch($res_json->results[0]->status){
					case 'ready':
						$status=2;
						break;
					case 'wait':
					case 'interrupted':
					case 'blocked':
						$status=1;
						break;
					case 'error':
						$status=-4;
						break;
					case 'error':
						$status=-3;
						break;
				}
			}
			$q='UPDATE `formetoo_cdb`.`m_buh_kassa` SET 
				`m_buh_kassa_response`=\''.val($res,array(),array(),1).'\',
				`m_buh_kassa_status`='.$status.' 
				WHERE `m_buh_kassa_id`='.$id.' LIMIT 1;';
			if($sql->query($q))
				return $res;
			else return false;
		}
		else return false;
	}
	
	//ОБНОВЛЕНИЕ СТАТУСОВ И ОТВЕТОВ ОТПРАВЛЕННЫХ ЧЕКОВ
	function updateQueue(){
		global $sql,$G;
		
		$q='SELECT * FROM `formetoo_cdb`.`m_buh_kassa` WHERE `m_buh_kassa_status`=1';
		if($res=$sql->query($q)){
			foreach($res as $_res){
				$this->getResponse($_res['m_buh_kassa_id']);
				sleep(1);
			}
			return true;
		}
		else return false;
	}
	
	//ФИСКАЛЬНЫЙ ЧЕК
	function fiscal($type='sell',$pay_type='online',$client_info='',$items=array()){
		//общие данные		
		$request['type']=$type?$type:'sell';//buy, sellReturn, buyReturn
		$request['electronically']=$pay_type=='online'?true:false;
		$request['taxationType']='usnIncomeOutcome';
		$request['operator']['name']='Будич Евгений Петрович';
		$request['operator']['vatin']='372102900582';
		$request['companyInfo']['email']='info@vendor.su';
		$request['organization']['name']='ООО "ИМ Вендор"';
		$request['organization']['vatin']='3702198893';
		$request['organization']['email']='info@vendor.su';
		$request['organization']['taxationTypes'][0]='usnIncomeOutcome';
		$request['organization']['address']='153000, Ивановская область, город Иваново, Кузнечная улица, дом 38 литер а1, помещение 1001';
		$request['ofd']['name']='ОФД "Такском"';
		$request['ofd']['vatin']='7704211201';
		$request['ofd']['host']='f1.taxcom.ru';
		$request['ofd']['port']=7777;
		$request['ofd']['dns']='8.8.8.8';

		//данные клиента
		$request['clientInfo']['emailOrPhone']=$client_info;
		//товары
		$sum=0;		
		$request['items'][0]['type']='text';
		$request['items'][0]['text']='--------------------------------';
		$request['items'][0]['alignment']='left';
		$request['items'][0]['doubleWidth']=false;
		$request['items'][0]['doubleHeight']=false;
		foreach($items as $_item){
			$j=sizeof($request['items']);
			
			$request['items'][$j]['type']='position';
			$request['items'][$j]['name']=$_item['name'].' ('.$_item['unit'].')';
			$request['items'][$j]['price']=$_item['price'];
			$request['items'][$j]['quantity']=$_item['count'];
			$request['items'][$j]['amount']=$_item['sum'];
			$request['items'][$j]['measurementUnit']=$_item['unit'];
			$request['items'][$j]['paymentObject']=$_item['type']=='products'?'commodity':'service';
			$request['items'][$j]['paymentMethod']='fullPayment';
			$request['items'][$j]['tax']['type']='none';
			
			$request['items'][$j+1]['type']='text';
			$request['items'][$j+1]['text']='--------------------------------';
			$request['items'][$j+1]['alignment']='left';
			$request['items'][$j+1]['doubleWidth']=false;
			$request['items'][$j+1]['doubleHeight']=false;
			
			$sum+=$_item['sum'];
		}
		//оплата
		$request['payments'][0]['type']=$pay_type=='online'?'electronically':'cash';
		$request['payments'][0]['sum']=$sum;
		$request['total']=$sum;
		
		return $this->sendResponse(json_encode($request,JSON_HEX_QUOT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
	}
	
	//ЗАКРЫТЬ СМЕНУ
	function closeShift(){	
		$request['type']="closeShift";
		$request['operator']['name']="Иванов";
		$request['operator']['vatin']="123654789507";
		return $this->sendResponse(json_encode($request,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
	}
	
	//НЕФИСКАЛЬНЫЙ ЧЕК
	function nonFiscal($items=array(),$separator=true){
		global $sql,$G;

		//общие данные		
		$request['type']="nonFiscal";

		//позиции
		$sum=0;
		if($separator){
			$request['items'][0]['type']='text';
			$request['items'][0]['text']='--------------------------------';
			$request['items'][0]['alignment']='left';
			$request['items'][0]['doubleWidth']=false;
			$request['items'][0]['doubleHeight']=false;
		}
		foreach($items as $_item){
			$j=sizeof($request['items']);
			
			$request['items'][$j]['type']=$_item['type']?$_item['type']:'text';
			$request['items'][$j]['alignment']=isset($_item['alignment'])&&$_item['alignment']?$_item['alignment']:'center';

			if($_item['type']=='barcode'){
				$request['items'][$j]['barcodeType']=isset($_item['barcodeType'])&&$_item['barcodeType']?$_item['barcodeType']:'QR';
				$request['items'][$j]['scale']=isset($_item['scale'])&&$_item['scale']?$_item['scale']:1;
				$request['items'][$j]['printText']=isset($_item['printDigitsBarcode'])&&$_item['printDigitsBarcode']?true:false;
			}
			else{
				$request['items'][$j]['text']=isset($_item['text'])&&$_item['text']?$_item['text']:'-';
				$request['items'][$j]['font']=isset($_item['font'])&&$_item['font']?(int)$_item['font']:3;
				$request['items'][$j]['doubleWidth']=isset($_item['doubleWidth'])&&$_item['doubleWidth']?true:false;
				$request['items'][$j]['doubleHeight']=isset($_item['doubleHeight'])&&$_item['doubleHeight']?true:false;
			}
			
			if($separator){
				$request['items'][$j+1]['type']='text';
				$request['items'][$j+1]['text']='--------------------------------';
				$request['items'][$j+1]['alignment']='left';
				$request['items'][$j+1]['doubleWidth']=false;
				$request['items'][$j+1]['doubleHeight']=false;
			}
		}		
		return $this->sendResponse(json_encode($request,JSON_HEX_QUOT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
	}
	
	//КОРРЕКЦИЯ
	function correction($params=array(
		'type'=>'sellCorrection',//buyCorrection, sellReturnCorrection, buyReturnCorrection 
		'name'=>'',
		'self'=>true,
		'base_date'=>'',
		'base_numb'=>'б/н',
		'pay_type'=>'online',
		'client_info'=>'',
		'items'=>array()
	)){
		global $sql,$G;
		
		//общие данные		
		$request['type']=$params['type'];
		$request['correctionType']=$params['self']?'self':'instruction';
		$request['correctionBaseName']=$params['name'];
		$request['correctionBaseDate']=$params['base_date']?dtu($params['base_date'],'Y.m.d'):dtu(dt(),'Y.m.d');
		$request['correctionBaseNumber']=$params['base_numb'];
		$request['electronically']=$params['pay_type']=='online'?true:false;
		$request['taxationType']='usnIncomeOutcome';
		$request['operator']['name']='Будич Евгений Петрович';
		$request['operator']['vatin']='372102900582';
		$request['companyInfo']['email']='info@vendor.su';
		$request['organization']['name']='ООО "ИМ Вендор"';
		$request['organization']['vatin']='3702198893';
		$request['organization']['email']='info@vendor.su';
		$request['organization']['taxationTypes'][0]='usnIncomeOutcome';
		$request['organization']['address']='153000, Ивановская область, город Иваново, Кузнечная улица, дом 38 литер а1, помещение 1001';
		$request['ofd']['name']='ОФД "Такском"';
		$request['ofd']['vatin']='7704211201';
		$request['ofd']['host']='f1.taxcom.ru';
		$request['ofd']['port']=7777;
		$request['ofd']['dns']='8.8.8.8';

		//данные клиента
		$request['clientInfo']['emailOrPhone']=$params['client_info'];
		//товары
		$sum=0;
		$request['items'][0]['type']='text';
		$request['items'][0]['text']='--------------------------------';
		$request['items'][0]['alignment']='left';
		$request['items'][0]['doubleWidth']=false;
		$request['items'][0]['doubleHeight']=false;
		$items=$params['items'];
		foreach($items as $_item){
			$j=sizeof($request['items']);
			
			$request['items'][$j]['type']='position';
			$request['items'][$j]['name']=$_item['name'].' ('.$_item['unit'].')';
			$request['items'][$j]['price']=$_item['price'];
			$request['items'][$j]['quantity']=$_item['count'];
			$request['items'][$j]['amount']=$_item['sum'];
			$request['items'][$j]['measurementUnit']=$_item['unit'];
			$request['items'][$j]['paymentObject']=$_item['type']=='products'?'commodity':'service';
			$request['items'][$j]['paymentMethod']='fullPayment';
			$request['items'][$j]['tax']['type']='none';
			
			$request['items'][$j+1]['type']='text';
			$request['items'][$j+1]['text']='--------------------------------';
			$request['items'][$j+1]['alignment']='left';
			$request['items'][$j+1]['doubleWidth']=false;
			$request['items'][$j+1]['doubleHeight']=false;
			
			$sum+=$_item['sum'];
		}
		//оплата
		$request['payments'][0]['type']=$params['pay_type']=='online'?'electronically':'cash';
		$request['payments'][0]['sum']=$sum;
		$request['total']=$sum;
		
		return $this->sendResponse(json_encode($request,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
	}
	
	//ОТЧЁТ О СОСТОЯНИИ РАСЧЁТОВ
	function reportOfdExchangeStatus(){	
		$request['type']='reportOfdExchangeStatus';
		$request['operator']['name']='Будич Евгений Петрович';
		$request['operator']['vatin']='372102900582';
		return $this->sendResponse(json_encode($request,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
	}
	
	//ОТЧЁТ О СОСТОЯНИИ ОБМЕНА С ОФД
	function ofdExchangeStatus(){
		$request['type']='reportOfdExchangeStatus';
		return $this->sendResponse(json_encode($request,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
	}
	
	//ДОПЕЧАТАТЬ ДОКУМЕНТ
	function continuePrint(){
		$request['type']="continuePrint";
		return $this->sendResponse(json_encode($request,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
	}
	
	//СОСТОЯНИЕ ККТ (С ЗАДЕРЖКОЙ ДО ПОЛУЧЕНИЯ ОТВЕТА, ОТМЕНА ПРИ 5 НЕУДАЧНЫХ ПОПЫТКАХ ПОЛУЧИТЬ ОТВЕТ), ЗАКРЫТИЕ ИСТЁКШЕЙ СМЕНЫ
	function getDeviceStatus(){
		$request['type']="getDeviceStatus";
		if($res=$this->sendResponse(json_encode($request,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE))){
			$status=$this->getResponse($res);
			$try=0;
			while(mb_strpos($status,'deviceStatus')===false){
				if(++$try>=5) break;
				sleep(4);
				$status=$this->getResponse($res);
			}
			if(mb_strpos($status,'deviceStatus')!==false){
				$status=json_decode($status);
				$status=$status->results[0]->result->deviceStatus;
				//если смена истекла - закрываем её
				if($status->shift=='expired')
					$this->closeShift();
				//проверяем остальные параметры готовности кассы
				if(
					$status->blocked==false&&
					$status->coverOpened==false&&
					$status->paperPresent==true&&
					$status->fiscal==true&&
					$status->fnFiscal==true&&
					$status->fnPresent==true
				) return true;
				else return $status;
			}
		}
	}
	
	//СОСТОЯНИЕ ОЧЕРЕДИ ЗАДАЧ
	function stat(){
		$url='http://195.161.41.199/stat/requests';
		// создание объекта curl
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($curl, CURLOPT_TIMEOUT, 4);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json'
			)
		);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_PORT, 25119);

		if($res=curl_exec($curl))
			return $res;
		else return false;
	}
	
		
	//ОТМЕНА ЗАДАНИЯ
	function deleteResponse($id){
		global $sql;
		
		return $this->sendResponse($id,0,true);
	}

}
?>