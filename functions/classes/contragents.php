<?
defined ('_DSITE') or die ('Access denied');
require_once(__DIR__.'/import/phpqrcode/qrlib.php');

class contragents{
	private $info,$employees,$services,$products;
	public $contragents_type;

	function __construct(){
		global $sql;
		
		$q='SELECT * FROM `formetoo_cdb`.`m_contragents` ORDER BY `m_contragents_date` DESC;';
		$this->info=$sql->query($q,'m_contragents_id');
		
		$q='SELECT * FROM `formetoo_cdb`.`m_info_contragents_type`;';
		$this->contragents_type=$sql->query($q,'m_info_contragents_type_id');
		
		
	}
	
	public function getInfo($id=''){
		return $id&&!empty($this->info[$id][0])?$this->info[$id][0]:$this->info;
	}
	
	public function getInfoFromINN($inn='',$kpp=null){
		if($kpp){
			foreach($this->info as $_c)
				if($_c[0]['m_contragents_c_inn']==$inn&&$_c[0]['m_contragents_c_kpp']==$kpp)
					return $_c[0];
		}
		foreach($this->info as $_c)
			if($_c[0]['m_contragents_c_inn']==$inn)
				return $_c[0];		
		return 0;
	}
	
	public function getName($id=''){
		if($id){
			if(!empty($this->info[$id][0]['m_contragents_c_name_short']))
				return $this->info[$id][0]['m_contragents_c_name_short'];
			elseif(!empty($this->info[$id][0]['m_contragents_c_name_full']))
				return $this->info[$id][0]['m_contragents_c_name_full'];
			elseif(!empty($this->info[$id][0]['m_contragents_p_fio']))
				return $this->info[$id][0]['m_contragents_p_fio'];
		}
		return false;
	}
	
	public function getDirector($id=''){
		if($id){
			if($this->info[$id][0]['m_contragents_c_inn'])
				return $this->info[$id][0]['m_contragents_c_director_name'];
			elseif($this->info[$id][0]['m_contragents_p_fio'])
				return $this->info[$id][0]['m_contragents_p_fio'];
		}
		return false;
	}

	public function getMy(){
		$my=array();
		foreach($this->info as $t_)
			if(in_array('0',explode('|',$t_[0]['m_contragents_type'])))
				$my[]=$t_[0];
		return $my;
	}
	
	public static function getCompanyFromINN($inn=''){
		if(!$inn)
			return false;
		$ch = curl_init('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/party');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$headers = array
		(
			'Content-Type: application/json',
			'Accept: application/json',
			'Authorization: Token 7d5d388b09a1ac08817ad3bcb8dc1e6e09dbc738'
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, '{
			"query": "'.$inn.'",
			"type": "'.(strlen($inn)==10?'LEGAL':'INDIVIDUAL').'"
		}');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$result = curl_exec($ch);
		curl_close($ch);
		
		if(strpos($result,'suggestions')!==false)
			return json_decode($result,true);
		else
			return false;
	}
	
	public function getEmployees($id='',$field='m_employees_id'){
		return $id?$this->employees[$id][0][$field]:$this->employees;
	}
	
	public function getServices($id='',$field='m_services_id'){
		return $id?$this->services[$id][0][$field]:$this->services;
	}
	
	public function getProducts($id='',$field='id'){
		return $id?$this->products[$id][0][$field]:$this->products;
	}
	
	public static function m_clients_company_add($no_redirect=false){
		global $sql,$e,$user;
		$data['m_contragents_c_name_full']=array(1,null,180);
		$data['m_contragents_c_name_short']=array(null,null,80);

		$data['m_contragents_c_inn']=array(1,10,12,null,null,5);
		$data['m_contragents_c_kpp']=array(null,null,20,null,1);
		$data['m_contragents_c_ogrn']=array(null,null,20,null,1);
		$data['m_contragents_c_okpo']=array(null,null,40);
		$data['m_contragents_c_okved']=array(null,null,40);
		$data['m_contragents_c_okato']=array(null,null,40);
		$data['m_contragents_c_oktmo']=array(null,null,40);
		$data['m_contragents_c_nds']=array(null,null,3);
		
		$data['m_contragents_rs_bik[]']=array(null,null,null,9,1);
		$data['m_contragents_rs_rs[]']=array(null,null,null,20,1);
		$data['m_contragents_rs_main[]']=array(null,null,3);
		$data['m_contragents_rs_bank[]']=array(null,null,180);
		$data['m_contragents_rs_ks[]']=array(null,null,null,20);
		
		$data['m_address_area[]']=array(null,null,80);
		$data['m_address_full[]']=array(null,null,480);
		$data['m_address_district[]']=array(null,null,80);
		$data['m_address_city[]']=array(null,null,80);
		$data['m_address_street[]']=array(null,null,80);
		$data['m_address_house[]']=array(null,null,8);
		$data['m_address_corp[]']=array(null,null,8);
		$data['m_address_build[]']=array(null,null,8);
		$data['m_address_mast[]']=array(null,null,8);
		$data['m_address_detail[]']=array(null,null,180);
		$data['m_address_type[]']=array(null,null,2);
		$data['m_address_index[]']=array(null,null,6);
		$data['m_address_recipient[]']=array(null,null,180);
		
		$data['m_contragents_c_director_post']=array(null,null,280);
		$data['m_contragents_c_director_name']=array(null,null,80);
		$data['m_contragents_c_director_name_rp']=array(null,null,80);
		$data['m_contragents_c_director_base']=array(null,null,180);
		$data['m_contragents_c_bookkeeper_name']=array(null,null,80);
		$data['m_contragents_c_responsible_name']=array(null,null,80);
		
		$data['m_contragents_consignee']=array(1,null,10,null,1);

		$data['m_contragents_tel_numb[]']=array(null,null,null,18,2);
		$data['m_contragents_tel_type[]']=array(1,null,15);
		$data['m_contragents_tel_comment[]']=array(null,null,250);
		
		$data['m_contragents_email']=array(null,null,64,null,4);
		$data['m_contragents_www']=array(null,null,64);
		$data['m_contragents_comment']=array(null,null,900);

		array_walk($data,'check');
			
		//проверяем занятость email
		/* $q='SELECT `m_contragents_id` FROM `m_contragents` WHERE `m_contragents_email`!=\'\' AND `m_contragents_email`=\''.$data['m_contragents_email'].'\';';
		if($res=$sql->query($q))
			$e[]='Пользователь с e-mail='.$data['m_contragents_email'].' уже существует'; */
	
		if(!$e){
			$data['m_contragents_id']=get_id('m_users');
			$data['m_contragents_date']=$data['m_contragents_update']=dt();
			$data['m_contragents_c_nds']=$data['m_contragents_c_nds']?1:0;
			
			$q='INSERT INTO `formetoo_cdb`.`m_contragents` SET
				`m_contragents_id`='.$data['m_contragents_id'].',
				`m_contragents_c_name_full`=\''.$data['m_contragents_c_name_full'].'\',
				`m_contragents_c_name_short`=\''.$data['m_contragents_c_name_short'].'\',
				`m_contragents_c_inn`=\''.$data['m_contragents_c_inn'].'\',
				`m_contragents_c_kpp`=\''.$data['m_contragents_c_kpp'].'\',
				`m_contragents_c_ogrn`=\''.$data['m_contragents_c_ogrn'].'\',
				`m_contragents_c_okpo`=\''.$data['m_contragents_c_okpo'].'\',
				`m_contragents_c_okved`=\''.$data['m_contragents_c_okved'].'\',
				`m_contragents_c_okato`=\''.$data['m_contragents_c_okato'].'\',
				`m_contragents_c_oktmo`=\''.$data['m_contragents_c_oktmo'].'\',
				`m_contragents_c_bank_name`=\'\',
				`m_contragents_c_bank_bik`=\'\',
				`m_contragents_c_bank_rs`=\'\',
				`m_contragents_c_bank_ks`=\'\',
				`m_contragents_c_nds`='.$data['m_contragents_c_nds'].',
				`m_contragents_c_director_post`=\''.$data['m_contragents_c_director_post'].'\',
				`m_contragents_c_director_name`=\''.$data['m_contragents_c_director_name'].'\',
				`m_contragents_c_director_name_rp`=\''.$data['m_contragents_c_director_name_rp'].'\',
				`m_contragents_c_director_base`=\''.$data['m_contragents_c_director_base'].'\',
				`m_contragents_c_bookkeeper_name`=\''.$data['m_contragents_c_bookkeeper_name'].'\',
				`m_contragents_c_responsible_name`=\''.$data['m_contragents_c_responsible_name'].'\',
				`m_contragents_consignee`=\''.$data['m_contragents_consignee'].'\',
				`m_contragents_date`=\''.$data['m_contragents_date'].'\',
				`m_contragents_update`=\''.$data['m_contragents_update'].'\',
				`m_contragents_comment`=\''.$data['m_contragents_comment'].'\',
				`m_contragents_www`=\''.$data['m_contragents_www'].'\',
				`m_contragents_type`=2,
				`m_contragents_email`=\''.$data['m_contragents_email'].'\';';
				
			if($sql->query($q)){
				
				$q='INSERT INTO `formetoo_main`.`m_users` SET 
					`m_users_id`='.$data['m_contragents_id'].',
					`m_users_login`=\'U'.get_id('m_users',0,'m_users_login').'\',
					`m_users_password`=\'0\',
					`m_users_group`=1054927507,
					`m_users_active`=0
				;';
				$sql->query($q);
				//добавляем телефоны
				if($data['m_contragents_tel_numb[]'][0]!=''&&$count=sizeof($data['m_contragents_tel_numb[]'])){
					$q='INSERT INTO `formetoo_cdb`.`m_contragents_tel` (`m_contragents_tel_contragents_id`,`m_contragents_tel_numb`,`m_contragents_tel_type`,`m_contragents_tel_comment`) VALUES ';
					for($i=0;$i<$count;$i++)
						if($data['m_contragents_tel_numb[]'][$i])
							$q.='(
								'.$data['m_contragents_id'].',
								\''.$data['m_contragents_tel_numb[]'][$i].'\',
								'.$data['m_contragents_tel_type[]'][$i].',
								\''.$data['m_contragents_tel_comment[]'][$i].'\'
							),';
					if(!($sql->query(substr($q,0,-1).';')))
						elogs();
				}
				//добаляем счета
				if($data['m_contragents_rs_bik[]'][0]!=''&&$count=sizeof($data['m_contragents_rs_bik[]'])){
					$q='INSERT INTO `formetoo_cdb`.`m_contragents_rs` (`m_contragents_rs_contragents_id`,`m_contragents_rs_bik`,`m_contragents_rs_rs`,`m_contragents_rs_main`,`m_contragents_rs_bank`,`m_contragents_rs_ks`) VALUES ';
					for($i=0;$i<$count;$i++)
						if($data['m_contragents_rs_bik[]'][$i]){
							$data['m_contragents_rs_main[]'][$i]=$data['m_contragents_rs_main[]'][$i]?1:0;
							$q.='(
									'.$data['m_contragents_id'].',
									\''.$data['m_contragents_rs_bik[]'][$i].'\',
									'.$data['m_contragents_rs_rs[]'][$i].',
									\''.$data['m_contragents_rs_main[]'][$i].'\',
									\''.$data['m_contragents_rs_bank[]'][$i].'\',
									\''.$data['m_contragents_rs_ks[]'][$i].'\'
								),';
							}
					if(!($sql->query(substr($q,0,-1).';')))
						elogs();
				}
				//добаляем адреса
				if($data['m_address_full[]'][0]!=''&&$count=sizeof($data['m_address_full[]'])){
					$q='INSERT INTO `formetoo_cdb`.`m_contragents_address` (`m_address_contragents_id`,`m_address_full`,`m_address_index`,`m_address_area`,`m_address_district`,`m_address_city`,`m_address_street`,`m_address_house`,`m_address_corp`,`m_address_build`,`m_address_mast`,`m_address_detail`,`m_address_type`,`m_address_recipient`) VALUES ';
					for($i=0;$i<$count;$i++)
						if($data['m_address_full[]'][$i]){
							$q.='(
									'.$data['m_contragents_id'].',
									\''.$data['m_address_full[]'][$i].'\',
									\''.$data['m_address_index[]'][$i].'\',
									\''.$data['m_address_area[]'][$i].'\',
									\''.$data['m_address_district[]'][$i].'\',
									\''.$data['m_address_city[]'][$i].'\',
									\''.$data['m_address_street[]'][$i].'\',
									\''.$data['m_address_house[]'][$i].'\',
									\''.$data['m_address_corp[]'][$i].'\',
									\''.$data['m_address_build[]'][$i].'\',
									\''.$data['m_address_mast[]'][$i].'\',
									\''.$data['m_address_detail[]'][$i].'\',
									\''.$data['m_address_type[]'][$i].'\',
									\''.$data['m_address_recipient[]'][$i].'\'
								),';
							}
					if(!($sql->query(substr($q,0,-1).';')))
						elogs();
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
	
	public static function m_clients_company_change(){
		global $sql,$e,$user;
		$data['m_contragents_id']=array(1,null,10,null,1);
		$data['m_contragents_c_name_full']=array(1,null,180);
		$data['m_contragents_c_name_short']=array(null,null,80);

		$data['m_contragents_c_inn']=array(1,10,12,null,null,5);
		$data['m_contragents_c_kpp']=array(null,null,20,null,1);
		$data['m_contragents_c_ogrn']=array(null,null,20,null,1);
		$data['m_contragents_c_okpo']=array(null,null,40);
		$data['m_contragents_c_okved']=array(null,null,40);
		$data['m_contragents_c_okato']=array(null,null,40);
		$data['m_contragents_c_oktmo']=array(null,null,40);
		$data['m_contragents_c_nds']=array(null,null,3);
		
		$data['m_contragents_rs_bik[]']=array(null,null,null,9,1);
		$data['m_contragents_rs_rs[]']=array(null,null,null,20,1);
		$data['m_contragents_rs_main[]']=array(null,null,3);
		$data['m_contragents_rs_bank[]']=array(null,null,180);
		$data['m_contragents_rs_ks[]']=array(null,null,null,20);
		
		$data['m_address_area[]']=array(null,null,80);
		$data['m_address_full[]']=array(null,null,480);
		$data['m_address_district[]']=array(null,null,80);
		$data['m_address_city[]']=array(null,null,80);
		$data['m_address_street[]']=array(null,null,80);
		$data['m_address_house[]']=array(null,null,8);
		$data['m_address_corp[]']=array(null,null,8);
		$data['m_address_build[]']=array(null,null,8);
		$data['m_address_mast[]']=array(null,null,8);
		$data['m_address_detail[]']=array(null,null,180);
		$data['m_address_type[]']=array(null,null,2);
		$data['m_address_index[]']=array(null,null,6);
		$data['m_address_recipient[]']=array(null,null,180);
		
		$data['m_contragents_c_director_post']=array(null,null,280);
		$data['m_contragents_c_director_name']=array(null,null,80);
		$data['m_contragents_c_director_name_rp']=array(null,null,80);
		$data['m_contragents_c_director_base']=array(null,null,180);
		$data['m_contragents_c_bookkeeper_name']=array(null,null,80);
		$data['m_contragents_c_responsible_name']=array(null,null,80);
		
		$data['m_contragents_consignee']=array(1,null,10,null,1);

		$data['m_contragents_tel_numb[]']=array(null,null,null,18,2);
		$data['m_contragents_tel_type[]']=array(1,null,15);
		$data['m_contragents_tel_comment[]']=array(null,null,250);
		
		$data['m_contragents_email']=array(null,null,64,null,4);
		$data['m_contragents_www']=array(null,null,64);
		$data['m_contragents_comment']=array(null,null,900);

		array_walk($data,'check');
			
		/* //проверяем занятость email
		$q='SELECT `m_contragents_id` FROM `m_contragents` WHERE `m_contragents_id`!='.$data['m_contragents_id'].' AND `m_contragents_email`!=\'\' AND `m_contragents_email`=\''.$data['m_contragents_email'].'\';';
		if($res=$sql->query($q))
			$e[]='Пользователь с e-mail='.$data['m_contragents_email'].' уже существует'; */
	
		if(!$e){
			$data['m_contragents_update']=dt();
			$data['m_contragents_c_nds']=$data['m_contragents_c_nds']?1:0;
			
			$q='UPDATE `formetoo_cdb`.`m_contragents` SET
				`m_contragents_c_name_full`=\''.$data['m_contragents_c_name_full'].'\',
				`m_contragents_c_name_short`=\''.$data['m_contragents_c_name_short'].'\',
				`m_contragents_c_inn`=\''.$data['m_contragents_c_inn'].'\',
				`m_contragents_c_kpp`=\''.$data['m_contragents_c_kpp'].'\',
				`m_contragents_c_ogrn`=\''.$data['m_contragents_c_ogrn'].'\',
				`m_contragents_c_okpo`=\''.$data['m_contragents_c_okpo'].'\',
				`m_contragents_c_okved`=\''.$data['m_contragents_c_okved'].'\',
				`m_contragents_c_okato`=\''.$data['m_contragents_c_okato'].'\',
				`m_contragents_c_oktmo`=\''.$data['m_contragents_c_oktmo'].'\',
				`m_contragents_c_nds`='.$data['m_contragents_c_nds'].',
				`m_contragents_c_director_post`=\''.$data['m_contragents_c_director_post'].'\',
				`m_contragents_c_director_name`=\''.$data['m_contragents_c_director_name'].'\',
				`m_contragents_c_director_name_rp`=\''.$data['m_contragents_c_director_name_rp'].'\',
				`m_contragents_c_director_base`=\''.$data['m_contragents_c_director_base'].'\',
				`m_contragents_c_bookkeeper_name`=\''.$data['m_contragents_c_bookkeeper_name'].'\',
				`m_contragents_c_responsible_name`=\''.$data['m_contragents_c_responsible_name'].'\',
				`m_contragents_consignee`=\''.$data['m_contragents_consignee'].'\',
				`m_contragents_update`=\''.$data['m_contragents_update'].'\',
				`m_contragents_comment`=\''.$data['m_contragents_comment'].'\',
				`m_contragents_www`=\''.$data['m_contragents_www'].'\',
				`m_contragents_email`=\''.$data['m_contragents_email'].'\' 
				WHERE `m_contragents_id`='.$data['m_contragents_id'].';';
			
			if($sql->query($q)){
				//удаляем текущие телефоны и заносим переданные в форме
				$q='DELETE FROM `formetoo_cdb`.`m_contragents_tel` WHERE `m_contragents_tel_contragents_id`='.$data['m_contragents_id'].';';
				$sql->query($q);
				if($data['m_contragents_tel_numb[]'][0]!=''&&$count=sizeof($data['m_contragents_tel_numb[]'])){
					$q='INSERT INTO `formetoo_cdb`.`m_contragents_tel` (`m_contragents_tel_contragents_id`,`m_contragents_tel_numb`,`m_contragents_tel_type`,`m_contragents_tel_comment`) VALUES ';
					for($i=0;$i<$count;$i++)
						if($data['m_contragents_tel_numb[]'][$i])
							$q.='(
								'.$data['m_contragents_id'].',
								\''.$data['m_contragents_tel_numb[]'][$i].'\',
								'.$data['m_contragents_tel_type[]'][$i].',
								\''.$data['m_contragents_tel_comment[]'][$i].'\'
							),';
					if(!($sql->query(substr($q,0,-1).';')))
						elogs();
				}
				//удаляем текущие счета и занос им переданные в форме
				$q='DELETE FROM `formetoo_cdb`.`m_contragents_rs` WHERE `m_contragents_rs_contragents_id`='.$data['m_contragents_id'].';';
				$sql->query($q);
				
				if($data['m_contragents_rs_bik[]'][0]!=''&&$count=sizeof($data['m_contragents_rs_bik[]'])){
					$q='INSERT INTO `formetoo_cdb`.`m_contragents_rs` (`m_contragents_rs_contragents_id`,`m_contragents_rs_bik`,`m_contragents_rs_rs`,`m_contragents_rs_main`,`m_contragents_rs_bank`,`m_contragents_rs_ks`) VALUES ';
					for($i=0;$i<$count;$i++)
						if($data['m_contragents_rs_bik[]'][$i]){
							$data['m_contragents_rs_main[]'][$i]=isset($data['m_contragents_rs_main[]'][$i])&&$data['m_contragents_rs_main[]'][$i]?1:0;
							$q.='(
									'.$data['m_contragents_id'].',
									\''.$data['m_contragents_rs_bik[]'][$i].'\',
									\''.$data['m_contragents_rs_rs[]'][$i].'\',
									\''.$data['m_contragents_rs_main[]'][$i].'\',
									\''.$data['m_contragents_rs_bank[]'][$i].'\',
									\''.$data['m_contragents_rs_ks[]'][$i].'\'
								),';
							}
					if(!($sql->query(substr($q,0,-1).';')))
						elogs();
				}
				
				//добаляем адреса
				$q='DELETE FROM `formetoo_cdb`.`m_contragents_address` WHERE `m_address_contragents_id`='.$data['m_contragents_id'].';';
				$sql->query($q);
				
				if($data['m_address_area[]'][0]!=''&&$count=sizeof($data['m_address_area[]'])){
					$q='INSERT INTO `formetoo_cdb`.`m_contragents_address` (`m_address_contragents_id`,`m_address_full`,`m_address_index`,`m_address_area`,`m_address_district`,`m_address_city`,`m_address_street`,`m_address_house`,`m_address_corp`,`m_address_build`,`m_address_mast`,`m_address_detail`,`m_address_type`,`m_address_recipient`) VALUES ';
					for($i=0;$i<$count;$i++)
						if($data['m_address_area[]'][$i]){
							$q.='(
									'.$data['m_contragents_id'].',
									\''.$data['m_address_full[]'][$i].'\',
									\''.$data['m_address_index[]'][$i].'\',
									\''.$data['m_address_area[]'][$i].'\',
									\''.$data['m_address_district[]'][$i].'\',
									\''.$data['m_address_city[]'][$i].'\',
									\''.$data['m_address_street[]'][$i].'\',
									\''.$data['m_address_house[]'][$i].'\',
									\''.$data['m_address_corp[]'][$i].'\',
									\''.$data['m_address_build[]'][$i].'\',
									\''.$data['m_address_mast[]'][$i].'\',
									\''.$data['m_address_detail[]'][$i].'\',
									\''.$data['m_address_type[]'][$i].'\',
									\''.$data['m_address_recipient[]'][$i].'\'
								),';
							}
					if(!($sql->query(substr($q,0,-1).';')))
						elogs();
				}
				
				header('Location: '.url().'?success&action=details&m_contragents_id='.$data['m_contragents_id']);
			}
			else{
				elogs();
				header('Location: '.url().'?error&action=details&m_contragents_id='.$data['m_contragents_id']);
			}
		}
		else{
			elogs();
			header('Location: '.url().'?error&action=details&m_contragents_id='.$data['m_contragents_id']);
		}
		exit;
	}
	
	public static function m_clients_personal_add(){
		global $sql,$e,$user;
		$data['m_contragents_p_fio']=array(1,null,130);
		$data['m_contragents_p_fio_rp']=array(null,null,130);

		$data['m_contragents_p_passport_sn']=array(null,null,null,11);
		$data['m_contragents_p_passport_v']=array(null,null,80);
		$data['m_contragents_p_passport_date']=array(null,null,null,19);
		$data['m_contragents_address_j']=array(null,null,180);

		$data['m_contragents_tel_numb[]']=array(null,null,null,18,2);
		$data['m_contragents_tel_type[]']=array(1,null,15);
		$data['m_contragents_tel_comment[]']=array(null,null,250);
		
		$data['m_contragents_email']=array(null,null,64,null,4);
		$data['m_address_full']=array(null,null,480);
		$data['m_contragents_comment']=array(null,null,900);

		$data['m_contragents_p_sex']=array(null,null,null,7);
		$data['m_contragents_p_birthday']=array(null,null,null,19);

		array_walk($data,'check');
			
		/* //проверяем занятость email
		$q='SELECT `m_contragents_id` FROM `m_contragents` WHERE `m_contragents_email`!=\'\' AND `m_contragents_email`=\''.$data['m_contragents_email'].'\';';
		if($res=$sql->query($q))
			$e[]='Пользователь с e-mail='.$data['m_contragents_email'].' уже существует'; */
	
		if(!$e){
			$data['m_contragents_id']=get_id('m_users');
			$data['m_contragents_date']=$data['m_contragents_update']=dt();
			$data['m_contragents_p_sex']=$data['m_contragents_p_sex']?$data['m_contragents_p_sex']:'мужской';
			
			$q='INSERT INTO `formetoo_cdb`.`m_contragents` SET
				`m_contragents_id`='.$data['m_contragents_id'].',
				`m_contragents_p_fio`=\''.$data['m_contragents_p_fio'].'\',
				`m_contragents_p_fio_rp`=\''.$data['m_contragents_p_fio_rp'].'\',
				`m_contragents_p_passport_sn`=\''.$data['m_contragents_p_passport_sn'].'\',
				`m_contragents_p_passport_v`=\''.$data['m_contragents_p_passport_v'].'\',
				`m_contragents_p_passport_date`='.($data['m_contragents_p_passport_date']?('\''.$data['m_contragents_p_passport_date'].'\''):'NULL').',
				`m_contragents_date`=\''.$data['m_contragents_date'].'\',
				`m_contragents_update`=\''.$data['m_contragents_update'].'\',
				`m_contragents_email`=\''.$data['m_contragents_email'].'\',
				`m_contragents_p_sex`=\''.$data['m_contragents_p_sex'].'\',
				`m_contragents_type`=3,
				`m_contragents_comment`=\''.$data['m_contragents_comment'].'\',
				`m_contragents_p_birthday`='.($data['m_contragents_p_birthday']?('\''.$data['m_contragents_p_birthday'].'\''):'NULL').';';
			
			if($sql->query($q)){
				
				$q='INSERT INTO `formetoo_main`.`m_users` SET 
					`m_users_id`='.$data['m_contragents_id'].',
					`m_users_login`=\'U'.get_id('m_users',0,'m_users_login').'\',
					`m_users_password`=\'0\',
					`m_users_group`=1054927507,
					`m_users_active`=0
				;';
				$sql->query($q);
				
				if($data['m_contragents_tel_numb[]'][0]!=''&&$count=sizeof($data['m_contragents_tel_numb[]'])){
					$q='INSERT INTO `formetoo_cdb`.`m_contragents_tel` (`m_contragents_tel_contragents_id`,`m_contragents_tel_numb`,`m_contragents_tel_type`,`m_contragents_tel_comment`) VALUES ';
					for($i=0;$i<$count;$i++)
						if($data['m_contragents_tel_numb[]'][$i])
							$q.='(
								'.$data['m_contragents_id'].',
								\''.$data['m_contragents_tel_numb[]'][$i].'\',
								'.$data['m_contragents_tel_type[]'][$i].',
								\''.$data['m_contragents_tel_comment[]'][$i].'\'
							),';
					if(!($sql->query(substr($q,0,-1).';')))
						elogs();
				}
				//добаляем адреса
				if($data['m_address_full']){
					$q='INSERT INTO `formetoo_cdb`.`m_contragents_address` (`m_address_contragents_id`,`m_address_full`,`m_address_type`) VALUES ';
					$q.='(
							'.$data['m_contragents_id'].',
							\''.$data['m_address_full'].'\',
							3
						),';
					if(!($sql->query(substr($q,0,-1).';')))
						elogs();
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
	
	public static function m_clients_personal_change(){
		global $sql,$e,$user;
		$data['m_contragents_id']=array(1,null,10,null,1);
		$data['m_contragents_p_fio']=array(1,null,130);
		$data['m_contragents_p_fio_rp']=array(null,null,130);

		$data['m_contragents_p_passport_sn']=array(null,null,null,11);
		$data['m_contragents_p_passport_v']=array(null,null,80);
		$data['m_contragents_p_passport_date']=array(null,null,null,19);
		$data['m_contragents_address_j']=array(null,null,180);

		$data['m_contragents_tel_numb[]']=array(null,null,null,18,2);
		$data['m_contragents_tel_type[]']=array(1,null,15);
		$data['m_contragents_tel_comment[]']=array(null,null,250);
		
		$data['m_contragents_email']=array(null,null,64,null,4);
		$data['m_address_full']=array(null,null,480);
		$data['m_contragents_comment']=array(null,null,900);

		$data['m_contragents_p_sex']=array(null,null,null,7);
		$data['m_contragents_p_birthday']=array(null,null,null,19);

		array_walk($data,'check');
			
		/* //проверяем занятость email
		$q='SELECT `m_contragents_id` FROM `m_contragents` WHERE `m_contragents_id`!='.$data['m_contragents_id'].' AND `m_contragents_email`!=\'\' AND `m_contragents_email`=\''.$data['m_contragents_email'].'\';';
		if($res=$sql->query($q))
			$e[]='Пользователь с e-mail='.$data['m_contragents_email'].' уже существует'; */
	
		if(!$e){
			$data['m_contragents_update']=dt();
			$data['m_contragents_p_sex']=$data['m_contragents_p_sex']?$data['m_contragents_p_sex']:'мужской';
			
			$q='UPDATE `formetoo_cdb`.`m_contragents` SET
				`m_contragents_p_fio`=\''.$data['m_contragents_p_fio'].'\',
				`m_contragents_p_fio_rp`=\''.$data['m_contragents_p_fio_rp'].'\',
				`m_contragents_p_passport_sn`=\''.$data['m_contragents_p_passport_sn'].'\',
				`m_contragents_p_passport_v`=\''.$data['m_contragents_p_passport_v'].'\',
				`m_contragents_p_passport_date`='.($data['m_contragents_p_passport_date']?('\''.$data['m_contragents_p_passport_date'].'\''):'NULL').',
				`m_contragents_address_j`=\''.$data['m_contragents_address_j'].'\',
				`m_contragents_address_p`=\'\',
				`m_contragents_update`=\''.$data['m_contragents_update'].'\',
				`m_contragents_email`=\''.$data['m_contragents_email'].'\',
				`m_contragents_p_sex`=\''.$data['m_contragents_p_sex'].'\',
				`m_contragents_comment`=\''.$data['m_contragents_comment'].'\',
				`m_contragents_p_birthday`='.($data['m_contragents_p_birthday']?('\''.$data['m_contragents_p_birthday'].'\''):'NULL').'  
				WHERE `m_contragents_id`='.$data['m_contragents_id'].';';
			
			if($sql->query($q)){
				$q='DELETE FROM `formetoo_cdb`.`m_contragents_tel` WHERE `m_contragents_tel_contragents_id`='.$data['m_contragents_id'].';';
				$sql->query($q);
				if($data['m_contragents_tel_numb[]'][0]!=''&&$count=sizeof($data['m_contragents_tel_numb[]'])){
					$q='INSERT INTO `formetoo_cdb`.`m_contragents_tel` (`m_contragents_tel_contragents_id`,`m_contragents_tel_numb`,`m_contragents_tel_type`,`m_contragents_tel_comment`) VALUES ';
					for($i=0;$i<$count;$i++)
						if($data['m_contragents_tel_numb[]'][$i])
							$q.='(
								'.$data['m_contragents_id'].',
								\''.$data['m_contragents_tel_numb[]'][$i].'\',
								'.$data['m_contragents_tel_type[]'][$i].',
								\''.$data['m_contragents_tel_comment[]'][$i].'\'
							),';
					if(!($sql->query(substr($q,0,-1).';')))
						elogs();
				}
				
				//добаляем адреса
				$q='DELETE FROM `formetoo_cdb`.`m_contragents_address` WHERE `m_address_contragents_id`='.$data['m_contragents_id'].';';
				$sql->query($q);
				
				if($data['m_address_full']){
					$q='INSERT INTO `formetoo_cdb`.`m_contragents_address` (`m_address_contragents_id`,`m_address_full`,`m_address_type`) VALUES ';
					$q.='(
							'.$data['m_contragents_id'].',
							\''.$data['m_address_full'].'\',
							3
						),';
					if(!($sql->query(substr($q,0,-1).';')))
						elogs();
				}
				header('Location: '.url().'?success&action=details&m_contragents_id='.$data['m_contragents_id']);
			}
			else{
				elogs();
				header('Location: '.url().'?error&action=details&m_contragents_id='.$data['m_contragents_id']);
			}
		}
		else{
			elogs();
			header('Location: '.url().'?error&action=details&m_contragents_id='.$data['m_contragents_id']);
		}
		exit;
	}
}
?>