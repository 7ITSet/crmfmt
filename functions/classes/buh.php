<?
defined ('_DSITE') or die ('Access denied');

class buh{
	private $info;

	function __construct(){
		global $sql;
		
		$q='SELECT * FROM `formetoo_cdb`.`m_buh` ORDER BY `m_buh_date` DESC;';
		$this->info=$sql->query($q,'m_buh_id');
		
	}
	
	public function getInfo($id=''){
		return $id?$this->info[$id][0]:$this->info;
	}
	
	//платежки по счету
	public function getInfoFromInvoice($id=''){
		$result=array();
		foreach($this->info as $_pay)
			//если в номере счета платежа есть номер нужного счета
			if(mb_strpos($_pay[0]['m_buh_invoice_numb'],$id.'',0,'utf-8')!==false)
				$result[]=$_pay[0]['m_buh_id'];
		return $result;
	}
	
	public static function buh_pay_add($no_redirect=false){
		global $sql,$e,$documents;

		$data['m_buh_order']=array(1,null,null,null,1);
		$data['m_buh_performer']=array(1,null,null,null,1);
		$data['m_buh_customer']=array(1,null,null,null,1);
		$data['m_buh_type']=array(1,null,null,null,1);
		$data['m_buh_cash']=array(1,null,null,null,1);
		$data['m_buh_sum']=array(1,null,null,null,1);
		$data['m_buh_sum_nds']=array(null,null,null,null,1);
		$data['m_buh_date']=array(null,null,19);
		$data['m_buh_status_pay']=array(null,null,3);
		$data['m_buh_status_doc']=array(null,null,3);
		$data['m_buh_no_calc']=array(null,null,3);
		$data['m_buh_avans']=array(null,null,3);
		$data['m_buh_year']=array(null,null,4,null,1);
		$data['m_buh_quarter']=array(null,null,null,1,1);
		$data['m_buh_target']=array(null,null,null,1,1);
		$data['m_buh_payment_numb']=array(null);
		$data['m_buh_comment']=array(null,null,1000);
		$data['m_buh_tags']=array(null,null,1000);
		$data['m_buh_documents_templates[]']=array(null,null,null,null,1);
		$data['smeta[]']=array();

		array_walk($data,'check');

		
		if(!$e){
			$data['m_buh_id']=get_id('m_buh');
			$data['m_buh_date']=$data['m_buh_date']?$data['m_buh_date']:dt();
			$data['m_buh_status_pay']=$data['m_buh_status_pay']?$data['m_buh_status_pay']:0;
			$data['m_buh_status_doc']=$data['m_buh_status_doc']?$data['m_buh_status_doc']:0;
			$data['m_buh_no_calc']=$data['m_buh_no_calc']?$data['m_buh_no_calc']:0;
			$data['m_buh_avans']=$data['m_buh_avans']?$data['m_buh_avans']:0;
			$data['m_buh_year']=$data['m_buh_year']?$data['m_buh_year']:dtu('','Y');
			$data['m_buh_quarter']=$data['m_buh_quarter']?$data['m_buh_quarter']:ceil(dtu('','m')/3);
			$data['m_buh_target']=$data['m_buh_target']?$data['m_buh_target']:1;
			$data['m_buh_payment_numb']=$data['m_buh_payment_numb']?$data['m_buh_payment_numb']:0;
			$data['m_buh_sum_nds']=$data['m_buh_sum_nds']?$data['m_buh_sum_nds']:0;
			$data['m_buh_documents_templates[]']=is_array($data['m_buh_documents_templates[]'])?$data['m_buh_documents_templates[]']:array();
			
			//обновляем документы
			$documents=new documents;
			//привязываем платеж к нужным документам
			foreach($documents->documents_id as $_document){
				$_document=$_document[0];
				//если исполнитель, заказчик, заказ, счет и шаблоны документа совпадают с платежкой
				if(
					$_document['m_documents_performer']==$data['m_buh_performer']&&
					$_document['m_documents_customer']==$data['m_buh_customer']&&
					$_document['m_documents_order']==$data['m_buh_order']&&
					$_document['m_documents_templates_id']==2363374033
					//in_array($_document['m_documents_templates_id'],$data['m_buh_documents_templates[]'])
				){
					//если среди удовлетворивших документов есть документ с нужным номером
					if(in_array($_document['m_documents_numb'],$data['smeta[]'])){
						//находим уже привязанные платежи
						$_pays=explode('|',$documents->getInfo($_document['m_documents_id'])['m_documents_pays']);
						//добавляем текущий платеж и убираем дубли, обновляем информацию в документе
						$_pays[]=$data['m_buh_id'];
						$_pays=array_unique($_pays);
						$q='UPDATE `formetoo_cdb`.`m_documents` 
							SET `m_documents_pays`=\''.implode('|',$_pays).'\' 
							WHERE `m_documents_id`='.$_document['m_documents_id'].' 
							LIMIT 1;';
						$sql->query($q);
					}
				}
			}
			
			$data['m_buh_documents_templates[]']=is_array($data['m_buh_documents_templates[]'])?implode('|',$data['m_buh_documents_templates[]']):'';				
			$data['smeta[]']=is_array($data['smeta[]'])?implode('|',$data['smeta[]']):'';
			
			$q='INSERT `formetoo_cdb`.`m_buh` SET
				`m_buh_id`='.$data['m_buh_id'].',
				`m_buh_orders_id`='.$data['m_buh_order'].',
				`m_buh_performer`='.$data['m_buh_performer'].',
				`m_buh_customer`='.$data['m_buh_customer'].',
				`m_buh_type`='.$data['m_buh_type'].',
				`m_buh_cash`='.$data['m_buh_cash'].',
				`m_buh_sum`='.$data['m_buh_sum'].',
				`m_buh_sum_nds`='.$data['m_buh_sum_nds'].',
				`m_buh_date`=\''.$data['m_buh_date'].'\',
				`m_buh_status_pay`='.$data['m_buh_status_pay'].',
				`m_buh_status_doc`='.$data['m_buh_status_doc'].',
				`m_buh_year`='.$data['m_buh_year'].',
				`m_buh_quarter`='.$data['m_buh_quarter'].',
				`m_buh_target`='.$data['m_buh_target'].',
				`m_buh_payment_numb`=\''.$data['m_buh_payment_numb'].'\',
				`m_buh_comment`=\''.$data['m_buh_comment'].'\',
				`m_buh_documents_templates`=\''.$data['m_buh_documents_templates[]'].'\',
				`m_buh_invoice_numb`=\''.$data['smeta[]'].'\',
				`m_buh_no_calc`='.$data['m_buh_no_calc'].',
				`m_buh_avans`='.$data['m_buh_avans'].',
				`m_buh_tags`=\''.$data['m_buh_tags'].'\';';
			
			if($sql->query($q)&&$data['m_buh_status_pay']){
				if($data['m_buh_cash']){
					$q='UPDATE `formetoo_cdb`.`m_info_settings` SET `m_info_settings_balance_cash`=`m_info_settings_balance_cash`+('.$data['m_buh_sum']*$data['m_buh_type'].');';
					$sql->query($q);
				}
				
				if(!$no_redirect) header('Location: '.url().'?success');
			}
			else{
				elogs();
				if(!$no_redirect) header('Location: '.url().'?error');
			}
		}
		else{
			elogs();
			if(!$no_redirect) header('Location: '.url().'?error');
		}
		if(!$no_redirect) exit;
	}
	
	public static function buh_pay_change(){
		global $sql,$e,$documents;
		$data['m_buh_id']=array(1,null,null,10,1);
		$data['m_buh_order']=array(1,null,null,10,1);
		$data['m_buh_performer']=array(1,null,null,10,1);
		$data['m_buh_customer']=array(1,null,null,10,1);
		$data['m_buh_type']=array(1,null,null,null,1);
		$data['m_buh_cash']=array(1,null,null,null,1);
		$data['m_buh_sum']=array(1,null,null,null,1);
		$data['m_buh_sum_nds']=array(null,null,null,null,1);
		$data['m_buh_date']=array(null,null,19);
		$data['m_buh_status_pay']=array(null,null,3);
		$data['m_buh_status_doc']=array(null,null,3);
		$data['m_buh_no_calc']=array(null,null,3);
		$data['m_buh_avans']=array(null,null,3);
		$data['m_buh_year']=array(null,null,4,null,1);
		$data['m_buh_quarter']=array(null,null,null,1,1);
		$data['m_buh_target']=array(null,null,null,1,1);
		$data['m_buh_payment_numb']=array(null,null,null,null,1);
		$data['m_buh_comment']=array(null,null,1000);
		$data['m_buh_tags']=array(null,null,1000);
		$data['m_buh_documents_templates[]']=array(null,null,null,null,1);
		$data['smeta[]']=array();
	
		array_walk($data,'check');
		
		if(!$e){
			$data['m_buh_date']=$data['m_buh_date']?$data['m_buh_date']:dt();
			$data['m_buh_status_pay']=$data['m_buh_status_pay']?$data['m_buh_status_pay']:0;
			$data['m_buh_status_doc']=$data['m_buh_status_doc']?$data['m_buh_status_doc']:0;
			$data['m_buh_no_calc']=$data['m_buh_no_calc']?$data['m_buh_no_calc']:0;
			$data['m_buh_avans']=$data['m_buh_avans']?$data['m_buh_avans']:0;
			$data['m_buh_year']=$data['m_buh_year']?$data['m_buh_year']:dtu('','Y');
			$data['m_buh_quarter']=$data['m_buh_quarter']?$data['m_buh_quarter']:ceil(dtu('','m')/3);
			$data['m_buh_target']=$data['m_buh_target']?$data['m_buh_target']:1;
			$data['m_buh_payment_numb']=$data['m_buh_payment_numb']?$data['m_buh_payment_numb']:0;
			$data['m_buh_sum_nds']=$data['m_buh_sum_nds']?$data['m_buh_sum_nds']:0;
			
			//привязываем платеж к нужным документам
			/* foreach($documents->documents_id as $_document){
				$_document=$_document[0];
				if(
					$_document['m_documents_performer']==$data['m_buh_performer']&&
					$_document['m_documents_customer']==$data['m_buh_customer']&&
					$_document['m_documents_order']==$data['m_buh_order']&&
					$_document['m_documents_templates_id']==2363374033
					//in_array($_document['m_documents_templates_id'],$data['m_buh_documents_templates[]'])
				){
					//находим уже привязанные платежи
					$_pays=explode('|',$documents->getInfo($_document['m_documents_id'])['m_documents_pays']);
					$_pays[]=$data['m_buh_id'];
					$_pays=array_unique($_pays);
					$q='UPDATE `m_documents` SET `m_documents_pays`=\''.implode('|',$_pays).'\' WHERE `m_documents_id`='.$_document['m_documents_id'].' LIMIT 1;';
					$sql->query($q);
				}
			} */
			
			$data['m_buh_documents_templates[]']=is_array($data['m_buh_documents_templates[]'])?implode('|',$data['m_buh_documents_templates[]']):'';				
			$data['smeta[]']=is_array($data['smeta[]'])?implode('|',$data['smeta[]']):'';
			
			$q='UPDATE `formetoo_cdb`.`m_buh` SET
				`m_buh_orders_id`='.$data['m_buh_order'].',
				`m_buh_performer`='.$data['m_buh_performer'].',
				`m_buh_customer`='.$data['m_buh_customer'].',
				`m_buh_type`='.$data['m_buh_type'].',
				`m_buh_cash`='.$data['m_buh_cash'].',
				`m_buh_sum`='.$data['m_buh_sum'].',
				`m_buh_sum_nds`='.$data['m_buh_sum_nds'].',
				`m_buh_date`=\''.$data['m_buh_date'].'\',
				`m_buh_status_pay`='.$data['m_buh_status_pay'].',
				`m_buh_status_doc`='.$data['m_buh_status_doc'].',
				`m_buh_year`='.$data['m_buh_year'].',
				`m_buh_quarter`='.$data['m_buh_quarter'].',
				`m_buh_target`='.$data['m_buh_target'].',
				`m_buh_payment_numb`=\''.$data['m_buh_payment_numb'].'\',
				`m_buh_comment`=\''.$data['m_buh_comment'].'\',
				`m_buh_documents_templates`=\''.$data['m_buh_documents_templates[]'].'\',
				`m_buh_invoice_numb`=\''.$data['smeta[]'].'\',
				`m_buh_no_calc`='.$data['m_buh_no_calc'].',
				`m_buh_avans`='.$data['m_buh_avans'].',
				`m_buh_tags`=\''.$data['m_buh_tags'].'\' 
				WHERE `m_buh_id`='.$data['m_buh_id'].' LIMIT 1;';
			
			if($sql->query($q)&&$data['m_buh_status_pay']){
				if($data['m_buh_cash']){
					$q='UPDATE `formetoo_cdb`.`m_info_settings` SET `m_info_settings_balance_cash`=`m_info_settings_balance_cash`+('.$data['m_buh_sum']*$data['m_buh_type'].');';
					$sql->query($q);
				}
				header('Location: '.url().'?success');
			}
			else{
				elogs();
				header('Location: '.url().'?error');
			}
		}
		else{
			elogs();
			header('Location: '.url().'?error');
		}
		exit;
	}

	public static function bank($date_start='',$date_end=''){
		require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/classes/simple_html_dom.php');
		
		function inn($t_el){
			if (strlen($t_el)==10){
				if(substr($t_el,-1)!=((2*substr($t_el,0,1)+4*substr($t_el,1,1)+10*substr($t_el,2,1)+3*substr($t_el,3,1)+5*substr($t_el,4,1)+9*substr($t_el,5,1)+4*substr($t_el,6,1)+6*substr($t_el,7,1)+8*substr($t_el,8,1))%11)%10)
					return false;
			}
			elseif (strlen($t_el)==12){
				if(substr($t_el,-2,1)!=((7*substr($t_el,0,1)+2*substr($t_el,1,1)+4*substr($t_el,2,1)+10*substr($t_el,3,1)+3*substr($t_el,4,1)+5*substr($t_el,5,1)+9*substr($t_el,6,1)+4*substr($t_el,7,1)+6*substr($t_el,8,1)+8*substr($t_el,9,1))%11)%10||substr($t_el,-1,1)!=((3*substr($t_el,0,1)+7*substr($t_el,1,1)+2*substr($t_el,2,1)+4*substr($t_el,3,1)+10*substr($t_el,4,1)+3*substr($t_el,5,1)+5*substr($t_el,6,1)+9*substr($t_el,7,1)+4*substr($t_el,8,1)+6*substr($t_el,9,1)+8*substr($t_el,10,1))%11)%10)
					return false;
			}
			return true;
		}

		$date_start=$date_start?dtu($date_start,'d.m.Y'):dtu(dtc('','-1 day'),'d.m.Y');
		$date_end=$date_end?dtu($date_end,'d.m.Y'):dtu('','d.m.Y');
		// URL скрипта авторизации
		$login_url = 'https://www.avangard.ru/client4/afterlogin';
		  
		// ПАРАМЕТРЫ ДЛЯ ОТПРАВКИ ЗАПРОСА - ЛОГИН И ПАРОЛЬ
		$post_data=array('login'=>'avg500080','passwd'=>'r0m%rvx4');
		
		// создание объекта curl
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_COOKIESESSION, 1); 
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); 
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_URL, $login_url);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));

		// ВЫПОЛНЯЕМ ЗАПРОС ДЛЯ АВТОРИЗАЦИИ
		$res = curl_exec($curl);

		//ПОЛУЧАЕМ ТОКЕН
		$token=substr(mb_substr($res,strpos($res,'Location: '),87),-29,29);
		echo $token;

		//ОТПРАВЛЯЕМ ЗАПРОС С ТОКЕНОМ ДЛЯ ОТКРЫТИЯ ПЕРВОЙ СТРАНИЦЫ
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_COOKIESESSION, 1); 
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
		curl_setopt($curl, CURLOPT_COOKIEFILE, "cookiefile"); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); 
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_POST, 0);
		curl_setopt($curl, CURLOPT_URL, 'https://www.avangard.ru/clbAvn/faces/pages/firstpage?ticket='.$token);
		$res = curl_exec($curl);

		//ПЕРЕХОДИМ НА СТРАНИЦУ С ВЫПИСКАМИ
		//$jsessionid=substr(mb_substr($res,strpos($res,'JSESSIONID='),106),-95,95);
		$post_data='oracle.adf.faces.FORM=menu:menu_form&oracle.adf.faces.STATE_TOKEN=1&event=&source=menu:menu_form:_id51:2:_id54&partialTargets=&partial=';
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		$res = curl_exec($curl);

		curl_setopt($curl, CURLOPT_POST, 0);
		curl_setopt($curl, CURLOPT_URL, 'https://www.avangard.ru/clbAvn/faces/facelet-pages/con_acc_stat.jspx');
		$res = curl_exec($curl);

		//ПАРАМЕТРЫ ДЛЯ ПОЛУЧЕНИЯ ВЫПИСКИ (МЕНЯЛСЯ ID В КОНЦЕ ПАРАМЕТРОВ, МОЖНО УЗНАТЬ В КНОПКЕ "ПОКАЗАТЬ" ЧЕРЕЗ DEVTOOLS)
		$post_data='docslist___jeniaPopupFrame=&docslist:main:startdate='.$date_start.'&docslist:main:finishdate='.$date_end.'&docslist:main:selSort=2&docslist:main:selVal=0&docslist:main:clTbl:_s=0&docslist:main:clTbl:_us=0&docslist:main:clTbl:rangeStart=0&docslist:main:accTbl:_s=0&docslist:main:accTbl:_us=0&docslist:main:accTbl:rangeStart=0&oracle.adf.faces.FORM=docslist&oracle.adf.faces.STATE_TOKEN=2&docslist:main:clTbl:_sm=&docslist:main:accTbl:_sm=&event=&source=docslist:main:_id755';
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		//curl_setopt($curl, CURLOPT_VERBOSE, true);
		//$verbose = fopen('1.txt', 'w+');
		//curl_setopt($curl, CURLOPT_STDERR, $verbose);
		
		if($res=curl_exec($curl)){
			$html=str_get_html($res);
			//$html=str_get_html(file_get_contents('3.html'));
			$balance=str_replace(' ','',$html->find('div.panelFull',0)->find('div',3)->find('span',1)->plaintext);

			$html_pays=$html->find('div[id="form:_id391"]',0)->find('table',2)->find('tr');
			$pays=array();
			foreach($html_pays as $k=>$pay){
				if($k==0) continue;
				if(!$pay->find('td[headers="form:_id391:_id393"]',0)) continue;
				
				//расчетный счет контрагента
				$pays[$k-1]['rs']=str_replace(' ','',$pay->find('td[headers="form:_id391:_id397"]',0)->find('div',0)->find('span',0)->plaintext);
				$pay->find('td[headers=form:_id391:_id397]',0)->find('div',0)->find('span',0)->outertext='';		
				//наименование контрагента
				$pays[$k-1]['name']=html_entity_decode($pay->find('td[headers="form:_id391:_id397"]',0)->find('div',0)->plaintext);
				//ИНН контрагента
				$pays[$k-1]['inn']=html_entity_decode($pay->find('td[headers="form:_id391:_id397"]',0)->find('div',0)->plaintext);
				$inn_pos=mb_strpos($pays[$k-1]['inn'],'ИНН ',0,'utf-8')+4;
				if(is_numeric(mb_substr($pays[$k-1]['inn'],$inn_pos,12,'utf-8'))&&inn(mb_substr($pays[$k-1]['inn'],$inn_pos,12,'utf-8')))
					$pays[$k-1]['inn']=mb_substr($pays[$k-1]['inn'],$inn_pos,12,'utf-8');
				elseif(is_numeric(mb_substr($pays[$k-1]['inn'],$inn_pos,10,'utf-8'))&&inn(mb_substr($pays[$k-1]['inn'],$inn_pos,10,'utf-8')))
					$pays[$k-1]['inn']=mb_substr($pays[$k-1]['inn'],$inn_pos,10,'utf-8');
				else
					$pays[$k-1]['inn']='';
				//входящий платеж
				$pays[$k-1]['in']=str_replace(' ','',$pay->find('td[headers="form:_id391:_id402"]',0)->plaintext);
				//исходящий платеж
				$pays[$k-1]['out']=str_replace(' ','',$pay->find('td[headers="form:_id391:_id406"]',0)->plaintext);
				//дата
				$pays[$k-1]['date']=$pay->find('td[headers="form:_id391:_id410"]',0)->plaintext;
				//номер платежки
				$pays[$k-1]['paynumb']=$pay->find('td[headers="form:_id391:_id413"]',0)->plaintext;
				$pays[$k-1]['target']=html_entity_decode($pay->find('td[headers="form:_id391:_id417"]',0)->plaintext);
			}
			return $pays;
		}
	}
	
	public static function setRSBalance($sum){
		global $sql;
		$q='UPDATE `formetoo_cdb`.`m_info_settings` SET `m_info_settings_balance_rs`=\''.(float)$sum.'\';';
		return $sql->query($q);
	}
	public static function getCashBalance(){
		global $sql;
		$q='SELECT `formetoo_cdb`.`m_info_settings_balance_cash` FROM `m_info_settings` LIMIT 1;';
		return $sql->query($q)[0]['m_info_settings_balance_cash'];
	}
	public static function getRSBalance(){
		global $sql;
		$q='SELECT `formetoo_cdb`.`m_info_settings_balance_rs` FROM `m_info_settings` LIMIT 1;';
		return $sql->query($q)[0]['m_info_settings_balance_rs'];
	}
	
	public static function v1c($date_start='',$date_end=''){
		global $buh;
	
		require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/classes/simple_html_dom.php');

		$v=file($_SERVER['DOCUMENT_ROOT'].'/../v.txt');
		
		$html_pays=array();
		$k=0;
		foreach($v as $_line){
			$_line=trim($_line);
			$_line = mb_convert_encoding($_line, 'utf-8', 'windows-1251');
			if(mb_strpos($_line,'СекцияДокумент',0,'utf-8')!==false)
				$k++;
				//парсим платежку
			$_line=explode('=',$_line);
			if(sizeof($_line)==2)
				$html_pays[$k][$_line[0]]=$_line[1];
		
		}
		
		if(isset($html_pays[0]['КонечныйОстаток']))
			$buh->setRSBalance($html_pays[0]['КонечныйОстаток']);
			
		foreach($html_pays as $k=>$pay){
			if($k==0) continue;
			elseif(isset($pay['СекцияДокумент'])){
				//входящий платеж
				if(!$pay['ДатаСписано']){
					//наименование контрагента
					$pays[$k-1]['name']=$pay['Плательщик'];
					//ИНН контрагента
					$pays[$k-1]['inn']=$pay['ПлательщикИНН'];
					//КПП контрагента
					$pays[$k-1]['kpp']=$pay['ПлательщикКПП'];
					//БИК контрагента
					$pays[$k-1]['bik']=$pay['ПлательщикБИК'];
					//р/с контрагента
					$pays[$k-1]['rs']=$pay['ПлательщикРасчСчет'];
					//сумма
					$pays[$k-1]['in']=$pay['Сумма'];
					$pays[$k-1]['out']=0;
				}
				//исходящий платеж
				else{
					//наименование контрагента
					$pays[$k-1]['name']=$pay['Получатель'];
					//ИНН контрагента
					$pays[$k-1]['inn']=$pay['ПолучательИНН'];
					//КПП контрагента
					$pays[$k-1]['kpp']=$pay['ПолучательКПП'];
					//БИК контрагента
					$pays[$k-1]['bik']=$pay['ПолучательБИК'];
					//р/с контрагента
					$pays[$k-1]['rs']=$pay['ПолучательРасчСчет'];
					//сумма
					$pays[$k-1]['out']=$pay['Сумма'];
					$pays[$k-1]['in']=0;
				}
				//дата
				$pays[$k-1]['date']=$pay['Дата'];
				//номер платежки
				$pays[$k-1]['paynumb']=$pay['Номер'];
				//назначение
				$pays[$k-1]['target']=$pay['НазначениеПлатежа'];
			}
		}
		return $pays;
		
	}
	
	

}
?>