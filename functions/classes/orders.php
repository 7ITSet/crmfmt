<?
defined ('_DSITE') or die ('Access denied');

class orders{
	public 
		$orders_id;
	
	function __construct(){
		global $sql;
		$q='SELECT * FROM `formetoo_cdb`.`m_orders` ORDER BY `m_orders_date` DESC;';
		$this->orders_id=$sql->query($q,'m_orders_id');
		
	
	}
	
	public static function orders_add(){
		global $sql,$e;
		$data['m_orders_name']=array(1,null,80);
		$data['m_orders_date']=array(null,null,19);
		$data['m_orders_performer']=array(1,null,null,10,1);
		$data['m_orders_customer']=array(1,null,null,10,1);
		$data['m_orders_nds']=array(null,null,3);
		$data['m_orders_comment']=array(null,null,500);
		$data['m_orders_address_full']=array(null,null,280);
		$data['m_orders_address_area']=array(null,null,80);
		$data['m_orders_address_district']=array(null,null,80);
		$data['m_orders_address_city']=array(null,null,80);
		$data['m_orders_address_street']=array(null,null,80);
		$data['m_orders_address_house']=array(null,null,8);
		$data['m_orders_address_corp']=array(null,null,8);
		$data['m_orders_address_build']=array(null,null,8);
		$data['m_orders_address_mast']=array(null,null,8);
		$data['m_orders_address_office']=array(null,null,80);

		array_walk($data,'check');
		
		if(!$e){
			$data['m_orders_id']=get_id('m_orders');
			$data['m_orders_update']=dt();
			$data['m_orders_date']=$data['m_orders_date']?$data['m_orders_date']:dt();
			$data['m_orders_nds']=$data['m_orders_nds']?18:-1;
			$data['m_orders_discount']=$data['m_orders_discount']?$data['m_orders_discount']:0;
			
			$q='INSERT `formetoo_cdb`.`m_orders` SET
				`m_orders_id`='.$data['m_orders_id'].',
				`m_orders_name`=\''.$data['m_orders_name'].'\',
				`m_orders_performer`='.$data['m_orders_performer'].',
				`m_orders_customer`='.$data['m_orders_customer'].',
				`m_orders_nds`='.$data['m_orders_nds'].',
				`m_orders_discount`='.$data['m_orders_discount'].',
				`m_orders_comment`=\''.$data['m_orders_comment'].'\',
				`m_orders_update`=\''.$data['m_orders_update'].'\',
				`m_orders_date`=\''.$data['m_orders_date'].'\';';
			
			if($sql->query($q))
				header('Location: '.url().'?success');
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
	
	public static function orders_change(){
		global $sql,$e;
		$data['m_orders_id']=array(1,null,null,10,1);
		$data['m_orders_name']=array(1,null,80);
		$data['m_orders_date']=array(null,null,19);
		$data['m_orders_performer']=array(1,null,null,10,1);
		$data['m_orders_customer']=array(1,null,null,10,1);
		$data['m_orders_nds']=array(null,null,3);
		$data['m_orders_comment']=array(null,null,500);
		$data['m_orders_address_full']=array(null,null,280);
		$data['m_orders_address_area']=array(null,null,80);
		$data['m_orders_address_district']=array(null,null,80);
		$data['m_orders_address_city']=array(null,null,80);
		$data['m_orders_address_street']=array(null,null,80);
		$data['m_orders_address_house']=array(null,null,8);
		$data['m_orders_address_corp']=array(null,null,8);
		$data['m_orders_address_build']=array(null,null,8);
		$data['m_orders_address_mast']=array(null,null,8);
		$data['m_orders_address_office']=array(null,null,80);

		array_walk($data,'check');
		
		if(!$e){
			$data['m_orders_update']=dt();
			$data['m_orders_nds']=$data['m_orders_nds']?18:-1;
			$data['m_orders_date']=$data['m_orders_date']?$data['m_orders_date']:dt();
			$data['m_orders_discount']=$data['m_orders_discount']?$data['m_orders_discount']:0;
			
			$q='UPDATE `formetoo_cdb`.`m_orders` SET
				`m_orders_name`=\''.$data['m_orders_name'].'\',
				`m_orders_performer`='.$data['m_orders_performer'].',
				`m_orders_customer`='.$data['m_orders_customer'].',
				`m_orders_nds`='.$data['m_orders_nds'].',
				`m_orders_discount`='.$data['m_orders_discount'].',
				`m_orders_comment`=\''.$data['m_orders_comment'].'\',
				`m_orders_date`=\''.$data['m_orders_date'].'\',
				`m_orders_update`=\''.$data['m_orders_update'].'\' 
				WHERE `m_orders_id`='.$data['m_orders_id'].' LIMIT 1;';
			
			if($sql->query($q))
				header('Location: '.url().'?success&action=details&m_orders_id='.$data['m_orders_id']);
			else{
				elogs();
				header('Location: '.url().'?error&action=details&m_orders_id='.$data['m_orders_id']);
			}
		}
		else{
			elogs();
			header('Location: '.url().'?error&action=details&m_orders_id='.$data['m_orders_id']);
		}
		exit;
	}
	
	


}
?>