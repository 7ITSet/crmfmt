<?
defined ('_DSITE') or die ('Access denied');

class info{
	private $tel,$tel_type,$settings,$address_id,$address,$address_type,$contragents_type,$employees_post,$documents_templates,$orders_status,$rs;
	public $units;
	
	function __construct(){
		global $sql;
		
		$q='SELECT * FROM `formetoo_cdb`.`m_contragents_tel`;';
		$this->tel=$sql->query($q,'m_contragents_tel_contragents_id');
		
		$q='SELECT * FROM `formetoo_cdb`.`m_contragents_rs` ORDER BY `m_contragents_rs_main` DESC;';
		$this->rs=$sql->query($q,'m_contragents_rs_contragents_id');

		$q='SELECT * FROM `formetoo_cdb`.`m_info_tel_type`;';
		$this->tel_type=$sql->query($q,'m_info_tel_type_id');
		
		$q='SELECT * FROM `formetoo_cdb`.`m_contragents_address`;';
		$this->address_id=$sql->query($q,'m_address_id');
		
		$q='SELECT * FROM `formetoo_cdb`.`m_contragents_address`;';
		$this->address=$sql->query($q,'m_address_contragents_id');
		
		$q='SELECT * FROM `formetoo_cdb`.`m_info_address_type`;';
		$this->address_type=$sql->query($q,'m_info_address_type_id');
		
		$q='SELECT * FROM `formetoo_cdb`.`m_info_units`;';
		$this->units=$sql->query($q,'m_info_units_id');
		
		$q='SELECT * FROM `formetoo_cdb`.`m_info_contragents_type`;';
		$this->contragents_type=$sql->query($q,'m_info_contragents_type_id');
		
		$q='SELECT * FROM `formetoo_cdb`.`m_info_post`;';
		$this->employees_post=$sql->query($q,'m_info_post_id');
		
		$q='SELECT * FROM `formetoo_cdb`.`m_info_settings`;';
		$this->settings=$sql->query($q)[0];
		
		$q='SELECT * FROM `formetoo_cdb`.`m_documents_templates`;';
		$this->documents_templates=$sql->query($q,'m_documents_templates_id');
		
		$q='SELECT * FROM `formetoo_cdb`.`m_info_orders_status`;';
		$this->orders_status=$sql->query($q,'m_info_orders_status_id');
	}
	
	public function getSettings($name=''){
		return $name?$this->settings[$name]:$this->settings;
	}
	
	public static function getTemplate($id=''){
		return $id?$this->documents_templates[$id][0]:$this->documents_templates;
	}
	
	public function getTel($id=''){
		return $id?(isset($this->tel[$id])?$this->tel[$id]:null):$this->tel;
	}
	public function getTelType($id=''){
		return $id!=''?$this->tel_type[$id][0]['m_info_tel_type_name']:$this->tel_type;
	}
	
	public function getAddressId($id=''){
		return $id?(isset($this->address_id[$id])?$this->address_id[$id]:null):$this->address_id;
	}
	public function getAddress($id=''){
		return $id?(isset($this->address[$id])?$this->address[$id]:null):$this->address;
	}	
	public function getAddressType($id=''){
		return $id?$this->address_type[$id][0]['m_info_address_type_name']:$this->address_type;
	}
	
	public function getRS($id=''){
		return $id?(isset($this->rs[$id])?$this->rs[$id]:null):$this->rs;
	}
	
	public function getDefaultRS($id=''){
		if(isset($this->rs[$id])){
			foreach($this->rs[$id] as $_rs)
				if($_rs['m_contragents_rs_main'])
					return $_rs;
			return $this->rs[$id];
		}
	}
	
	public function getContragentsType($id=''){
		return $id==''?$this->contragents_type:$this->contragents_type[$id];
	}
	
	public function getEmployeesPost($id=''){
		return $id==''?$this->employees_post:$this->employees_post[$id][0]['m_info_post_name'];
	}
	
	public function getUnits($id=''){
		return $id==''?$this->units:$this->units[$id][0]['m_info_units_name'];
	}
	
	public function getUnitsNoHTML($id=''){
		return $id==''?'':str_replace(array('<sup>2</sup>','<sup>3</sup>'),array('²','³'),$this->units[$id][0]['m_info_units_name']);
	}
	
	public function getOrdersStatus($id=''){
		return $id==''?$this->orders_status:$this->orders_status[$id][0]['m_info_orders_status_name'];
	}

}
?>