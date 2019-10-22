<?
define ('_DSITE',1);
ini_set('display_errors',1);
require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/classes/contragents.php');
require_once(__DIR__.'/../../functions/classes/documents.php');
require_once(__DIR__.'/../../functions/classes/buh.php');
$sql=new sql();
$contragents=new contragents();
$buh=new buh();
global $e;
//print_r(json_encode($buh->bank(dtc('','-3 days')),JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));exit;
if($rspays=$buh->v1c(dtc('','-3 days'))){
//if(1==1){
	foreach($rspays as $_rspay){
		//сумма платежки
		$_rspay['sum']=$_rspay['in']?$_rspay['in']:$_rspay['out'];
		//если нет ИНН - контрагент сам банк (заполнить ИНН контрагента банка)
		$_rspay['inn']=$_rspay['inn']?$_rspay['inn']:7702021163;
		//если не найден контрагент по ИНН - добаляем его
		if(!$customer=$contragents->getInfoFromINN($_rspay['inn'])){
			if($company=$contragents->getCompanyFromINN($_rspay['inn']))
				if(isset($company['suggestions'][0])){
					$company=$company['suggestions'][0];
					//добавляем найденого контрагента
					$_POST['m_contragents_c_name_full']=$company['data']['name']['full_with_opf'];
					$_POST['m_contragents_c_name_short']=$company['data']['name']['short_with_opf'];
					$_POST['m_contragents_c_inn']=$company['data']['inn'];
					$_POST['m_contragents_c_kpp']=isset($company['data']['kpp'])?$company['data']['kpp']:'';
					$_POST['m_contragents_c_ogrn']=$company['data']['ogrn'];
					$_POST['m_contragents_c_okpo']=$company['data']['okpo'];
					$_POST['m_contragents_c_okved']=$company['data']['okved'];
					$_POST['m_contragents_c_oktmo']=isset($company['data']['address']['data']['oktmo'])?$company['data']['address']['data']['oktmo']:'';
					$_POST['m_contragents_c_nds']=1;
					$_POST['m_address_full'][0]=($company['data']['address']['data']['postal_code']?$company['data']['address']['data']['postal_code'].', ':'').$company['data']['address']['unrestricted_value'];
					$_POST['m_address_area'][0]=isset($company['data']['address']['data']['region_with_type'])?$company['data']['address']['data']['region_with_type'].'.':'';
					$_POST['m_address_district'][0]=isset($company['data']['address']['data']['city_district_with_type'])?$company['data']['address']['data']['city_district_with_type']:'';
					$_POST['m_address_city'][0]=isset($company['data']['address']['data']['city_with_type'])?$company['data']['address']['data']['city_type'].'. '.$company['data']['address']['data']['city']:'';
					$_POST['m_address_street'][0]=isset($company['data']['address']['data']['street_with_type'])?$company['data']['address']['data']['street_type'].'. '.$company['data']['address']['data']['street']:'';
					$_POST['m_address_house'][0]=isset($company['data']['address']['data']['house'])?$company['data']['address']['data']['house']:'';
					$_POST['m_address_corp'][0]=isset($company['data']['address']['data']['block'])?$company['data']['address']['data']['block']:'';
					$_POST['m_address_detail'][0]=isset($company['data']['address']['data']['flat'])?$company['data']['address']['data']['flat_type'].'. '.$company['data']['address']['data']['flat']:'';
					$_POST['m_address_index'][0]=isset($company['data']['address']['data']['postal_code'])?$company['data']['address']['data']['postal_code']:'';
					$_POST['m_address_build'][0]=$_POST['m_address_mast'][0]='';
					$_POST['m_address_type'][0]=1;
					$_POST['m_contragents_c_director_post']=isset($company['data']['management']['post'])?$company['data']['management']['post']:'Индивидуальный предприниматель';
					$_POST['m_contragents_c_director_name']=isset($company['data']['management']['name'])?$company['data']['management']['name']:$company['data']['name']['full'];
					$_POST['m_contragents_c_director_name_rp']=$_POST['m_contragents_c_director_name'];
					$_POST['m_contragents_consignee']='0';
					$_POST['m_tel_numb'][0]='';
					$_POST['m_tel_type'][0]=1;
					$_POST['m_tel_comment'][0]='';
					$_POST['m_contragents_rs_bik'][0]='';
					$_POST['m_contragents_email']='';
					$contragents->m_clients_company_add(true);
					//обновляем таблицу контрагентов
					$contragents=new contragents;
				}
		}
		//если есть контрагент
		if($customer=$contragents->getInfoFromINN($_rspay['inn'],$_rspay['kpp'])){
			//параметры, если не будет найден счет
			//если поступление средств
			if($_rspay['in']){
				//исполнитель - formetoo
				$_POST['m_buh_performer']=3363726835;
				$_POST['m_buh_customer']=$contragents->getInfoFromINN($_rspay['inn'])['m_contragents_id'];
			}
			//если расход средств
			elseif($_rspay['out']){
				$_POST['m_buh_performer']=$contragents->getInfoFromINN($_rspay['inn'])['m_contragents_id'];
				//заказчик - formetoo
				$_POST['m_buh_customer']=3363726835;
			}
			$_POST['m_buh_order']='0';
			$_POST['smeta']=array();
			$_POST['m_buh_sum_nds']=mb_strpos($_rspay['target'],'в т.ч. НДС (18.00%)',0,'utf-8')?round($_rspay['sum']/1.18*.18,2):0;
			//находим последний счет контрагента с заданным ИНН, суммой равной сумме платежки
			$d=array();
			$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE 
				(
					`m_documents_customer`=\''.$customer['m_contragents_id'].'\' 
					OR
					`m_documents_performer`=\''.$customer['m_contragents_id'].'\'
				)
				AND `m_documents_params` LIKE \'%"doc_sum":"'.$_rspay['sum'].'"%\' 
				AND `m_documents_templates_id`=2363374033 
				ORDER BY `m_documents_date` DESC 
				LIMIT 1';
			if($res=$sql->query($q)){				
				$p=json_decode($res[0]['m_documents_params'],true);
				$_POST['m_buh_order']=$res[0]['m_documents_order'];
				$_POST['m_buh_performer']=$res[0]['m_documents_performer'];
				$_POST['m_buh_customer']=$res[0]['m_documents_customer'];
				//привязываем к счету, если в назначении платежки есть номер счета
				$_POST['smeta']=(mb_strpos($_rspay['target'],$res[0]['m_documents_numb'],0,'utf-8')!==false)?array($res[0]['m_documents_numb']):0;
				//если документ с НДС, считаем НДС по платежу
				$_POST['m_buh_sum_nds']=$p['doc_nds18']?round($_rspay['sum']/1.18*.18,2):0;
			}
			$_POST['m_buh_type']=$_rspay['in']?1:-1;
			$_POST['m_buh_cash']='0';
			$_POST['m_buh_sum']=$_rspay['sum'];
			$_POST['m_buh_date']=dtc($_rspay['date'].' '.dtu('','H:i:s'));
			$_POST['m_buh_status_pay']=1;
			$_POST['m_buh_status_doc']='1';
			$_POST['m_buh_year']=dtu($_POST['m_buh_date'],'Y');
			$_POST['m_buh_quarter']=ceil(dtu($_POST['m_buh_date'],'m')/3);
			$_POST['m_buh_target']=1;
			$_POST['m_buh_payment_numb']=(int)$_rspay['paynumb'];
			$_POST['m_buh_comment']=$_rspay['target'];
			$_POST['m_buh_no_calc']=0;
			$_POST['m_buh_tags']='';
			//типы отслеживаемых документов в зависимости от контрагента
			switch($customer['m_contragents_id']){
				//СДЭК, Яндекс
				case '1118839343':
				case '1185978467':
					$_POST['m_buh_documents_templates']=array('2363374033','8522102445','2352663637');
					break;
				//Билайн
				case '7927438125':
					$_POST['m_buh_documents_templates']=array('2363374033','2352663637');
					break;
				default:
					$_POST['m_buh_documents_templates']=array('2363374033','3552326767');
			}
			//если платежку еще не добавляли
			$new=1;
			foreach($buh->getInfo() as $_buh)
				if(
					$_buh[0]['m_buh_performer']==$_POST['m_buh_performer']&&
					$_buh[0]['m_buh_sum']==$_POST['m_buh_sum']&&
//					$_buh[0]['m_buh_date']==$_POST['m_buh_date']&&
					$_buh[0]['m_buh_payment_numb']==$_POST['m_buh_payment_numb']
				){
					$new=0;
					break;
				}
			if($new)
				$buh->buh_pay_add(true);
		}
	}
}

unset($sql);
unset($contragents);
unset($buh);
?>