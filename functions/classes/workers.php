<?
defined ('_DSITE') or die ('Access denied');
require_once(__DIR__.'/import/phpqrcode/qrlib.php');

class workers{
	private $info;

	function __construct(){
		global $sql;
		
		$q='SELECT * FROM `formetoo_cdb`.`m_employees`;';
		$this->info=$sql->query($q,'m_employees_id');

		
	}
	
	public function getInfo($id=''){
		return $id&&!empty($this->info[$id][0])?$this->info[$id][0]:$this->info;
	}

	
	public static function add(){
		global $sql,$e,$user;
		$data['m_employees_fio']=array(1,null,130);
		$data['m_employees_fio_rp']=array(null,null,130);
		$data['m_employees_sex']=array(null,null,null,7);
		$data['m_employees_birthday']=$data['m_employees_date_start']=$data['m_employees_date_end']=$data['m_employees_passport_date']=array(null,null,null,19);
		$data['m_employees_passport_sn']=array(null,null,null,11);
		$data['m_employees_birthday']=array(null,null,null,19);
		$data['m_employees_passport_v']=array(null,null,80);
		$data['m_employees_passport_address_r']=array(null,null,180);
		$data['m_employees_address_p']=array(null,null,180);
		$data['m_employees_email']=array(null,null,64,null,4);
		$data['m_employees_post']=array(1,null,3,1);
		$data['m_employees_services_categories[]']=array();
		$data['m_employees_brigade']=array(null,null,50);
		$data['m_employees_salary_f']=array(null,null,13,null,1);
		$data['m_employees_salary_v']=array(null,null,5,null,1);
		$data['m_employees_comment']=array(null,null,60000);
		$data['m_employees_email']=array(null,null,64,null,4);
		
		$data['m_contragents_tel_numb[]']=array(null,null,null,18,2);
		$data['m_contragents_tel_type[]']=array(1,null,15);
		$data['m_contragents_tel_comment[]']=array(null,null,250);
		
		array_walk($data,'check');
		
		if(!$e){
			$data['m_employees_id']=get_id('m_users');
			$data['m_employees_date']=$data['m_employees_update']=dt();
			$data['m_employees_sex']=$data['m_employees_sex']?$data['m_employees_sex']:'мужской';
			
			$q='INSERT `formetoo_main`.`m_users` SET
				`m_users_id`='.$data['m_employees_id'].',
				`m_users_login`=\''.($data['m_employees_email']?$data['m_employees_email']:'nouser@Formetoo.ru').'\',
				`m_users_password`=\''.user::getHash('nopass').'\',
				`m_users_group`=1030959504,
				`m_users_active`=0;';
			if($sql->query($q)){
				$q='INSERT `formetoo_cdb`.`m_employees` SET
					`m_employees_id`='.$data['m_employees_id'].',
					`m_employees_fio`=\''.$data['m_employees_fio'].'\',
					`m_employees_fio_rp`=\''.$data['m_employees_fio_rp'].'\',
					`m_employees_sex`=\''.$data['m_employees_sex'].'\',
					`m_employees_birthday`=\''.$data['m_employees_birthday'].'\',
					`m_employees_passport_sn`=\''.$data['m_employees_passport_sn'].'\',
					`m_employees_passport_v`=\''.$data['m_employees_passport_v'].'\',
					`m_employees_passport_date`=\''.$data['m_employees_passport_date'].'\',
					`m_employees_passport_address_r`=\''.$data['m_employees_passport_address_r'].'\',
					`m_employees_address_p`=\''.$data['m_employees_address_p'].'\',
					`m_employees_email`=\''.$data['m_employees_email'].'\',
					`m_employees_post`='.$data['m_employees_post'].',
					`m_employees_date_start`=\''.$data['m_employees_date_start'].'\',
					`m_employees_date_end`=\''.$data['m_employees_date_end'].'\',
					`m_employees_services_categories`=\''.($data['m_employees_services_categories[]']?implode('|',$data['m_employees_services_categories[]']):'').'\',
					`m_employees_brigade`=\''.$data['m_employees_brigade'].'\',
					`m_employees_salary_f`='.(float)$data['m_employees_salary_f'].',
					`m_employees_salary_v`='.(float)$data['m_employees_salary_v'].',
					`m_employees_comment`=\''.$data['m_employees_comment'].'\',
					`m_employees_date`=\''.$data['m_employees_date'].'\',
					`m_employees_update`=\''.$data['m_employees_update'].'\';';
				if($sql->query($q)){
					if($data['m_contragents_tel_numb[]'][0]!=''&&$count=sizeof($data['m_contragents_tel_numb[]'])){
						$q='INSERT INTO `formetoo_cdb`.`m_contragents_tel` (`m_contragents_tel_contragents_id`,`m_contragents_tel_numb`,`m_contragents_tel_type`,`m_contragents_tel_comment`) VALUES ';
						for($i=0;$i<$count;$i++)
							if($data['m_contragents_tel_numb[]'][$i])
								$q.='(
									'.$data['m_employees_id'].',
									\''.$data['m_contragents_tel_numb[]'][$i].'\',
									'.$data['m_contragents_tel_type[]'][$i].',
									\''.$data['m_contragents_tel_comment[]'][$i].'\'
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
		}
		else{
			elogs();
			header('Location: '.url().'?error');
		}
		exit;
	}
	
	public static function change(){
		global $sql,$e,$user;
		$data['m_employees_id']=array(1,null,null,10,1);
		$data['m_employees_fio']=array(1,null,130);
		$data['m_employees_fio_rp']=array(null,null,130);
		$data['m_employees_sex']=array(null,null,null,7);
		$data['m_employees_birthday']=$data['m_employees_date_start']=$data['m_employees_date_end']=$data['m_employees_passport_date']=array(null,null,null,19);
		$data['m_employees_passport_sn']=array(null,null,null,11);
		$data['m_employees_birthday']=array(null,null,null,19);
		$data['m_employees_passport_v']=array(null,null,80);
		$data['m_employees_passport_address_r']=array(null,null,180);
		$data['m_employees_address_p']=array(null,null,180);
		$data['m_employees_email']=array(null,null,64,null,4);
		$data['m_employees_post']=array(1,null,3,1);
		$data['m_employees_services_categories[]']=array();
		$data['m_employees_brigade']=array(null,null,3,null,1);
		$data['m_employees_salary_f']=array(null,null,13,null,1);
		$data['m_employees_salary_v']=array(null,null,5,null,1);
		$data['m_employees_comment']=array(null,null,60000);
		$data['m_employees_email']=array(null,null,64,null,4);
		
		$data['m_contragents_tel_numb[]']=array(null,null,null,18,2);
		$data['m_contragents_tel_type[]']=array(1,null,15);
		$data['m_contragents_tel_comment[]']=array(null,null,250);
		
		array_walk($data,'check');
		
		if(!$e){
			$data['m_employees_update']=dt();
			$data['m_employees_sex']=$data['m_employees_sex']?$data['m_employees_sex']:'мужской';

			$q='UPDATE `formetoo_cdb`.`m_employees` SET
				`m_employees_fio`=\''.$data['m_employees_fio'].'\',
				`m_employees_fio_rp`=\''.$data['m_employees_fio_rp'].'\',
				`m_employees_sex`=\''.$data['m_employees_sex'].'\',
				`m_employees_birthday`=\''.$data['m_employees_birthday'].'\',
				`m_employees_passport_sn`=\''.$data['m_employees_passport_sn'].'\',
				`m_employees_passport_v`=\''.$data['m_employees_passport_v'].'\',
				`m_employees_passport_date`=\''.$data['m_employees_passport_date'].'\',
				`m_employees_passport_address_r`=\''.$data['m_employees_passport_address_r'].'\',
				`m_employees_address_p`=\''.$data['m_employees_address_p'].'\',
				`m_employees_email`=\''.$data['m_employees_email'].'\',
				`m_employees_post`='.$data['m_employees_post'].',
				`m_employees_date_start`=\''.$data['m_employees_date_start'].'\',
				`m_employees_date_end`=\''.$data['m_employees_date_end'].'\',
				`m_employees_services_categories`=\''.($data['m_employees_services_categories[]']?implode('|',$data['m_employees_services_categories[]']):'').'\',
				`m_employees_brigade`=\''.$data['m_employees_brigade'].'\',
				`m_employees_salary_f`='.(float)$data['m_employees_salary_f'].',
				`m_employees_salary_v`='.(float)$data['m_employees_salary_v'].',
				`m_employees_comment`=\''.$data['m_employees_comment'].'\',
				`m_employees_update`=\''.$data['m_employees_update'].'\' 
				WHERE `m_employees_id`='.$data['m_employees_id'].';';
			if($sql->query($q)){
				$q='DELETE FROM `formetoo_cdb`.`m_contragents_tel` WHERE `m_contragents_tel_contragents_id`='.$data['m_employees_id'].';';
				$sql->query($q);
				if($data['m_contragents_tel_numb[]'][0]!=''&&$count=sizeof($data['m_contragents_tel_numb[]'])){
					$q='INSERT INTO `formetoo_cdb`.`m_contragents_tel` (`m_contragents_tel_contragents_id`,`m_contragents_tel_numb`,`m_contragents_tel_type`,`m_contragents_tel_comment`) VALUES ';
					for($i=0;$i<$count;$i++)
						if($data['m_contragents_tel_numb[]'][$i])
							$q.='(
								'.$data['m_employees_id'].',
								\''.$data['m_contragents_tel_numb[]'][$i].'\',
								'.$data['m_contragents_tel_type[]'][$i].',
								\''.$data['m_contragents_tel_comment[]'][$i].'\'
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
	
}
?>