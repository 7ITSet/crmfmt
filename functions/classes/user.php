<?
defined ('_DSITE') or die ('Access denied');
require_once(__DIR__.'/import/phpqrcode/qrlib.php');

class user{
	private $info;

	function __construct(){
		global $sql;
		$t_time=dtc(dt(),'+90 day');
		//если существует cookie uid
		if(isset($_COOKIE['uid_a'])&&$uid=val($_COOKIE['uid_a'])){
			//поиск залогиненного пользователя по cookie
			$q='SELECT * FROM `formetoo_main`.`cookies`,`formetoo_main`.`m_users` WHERE
				(
					`cookies`.`cookies_cookie`=\''.$uid.'\' AND
					`cookies`.`cookies_browser`=\''.md5($_SERVER['HTTP_USER_AGENT']).'\' AND
					`cookies`.`cookies_date`>\''.dt().'\'
				) AND
				`m_users`.`m_users_id`=`cookies`.`cookies_m_users_id` AND
				`m_users_group`=1302678614 LIMIT 1;';
			if($res=$sql->query($q)){
				$this->info=$res[0];
				//обновление cookie
				setcookie('uid_a',$uid,dtu($t_time),'/','.'.$_SERVER['HTTP_HOST'],false,true);
				//обновление сессии
				$q='UPDATE `formetoo_main`.`cookies` SET `cookies_date`=\''.$t_time.'\' WHERE `cookies_cookie`=\''.$uid.'\';';
				$sql->query($q);
			}
			//если запись не найдена удаляем COOKIE
			else{
				setcookie('uid_a','',time()-259200,'/','.'.$_SERVER['HTTP_HOST'],false,true);
			}
		}
		//если запись не найдена удаляем COOKIE
		else{
			setcookie('uid_a','',time()-259200,'/','.'.$_SERVER['HTTP_HOST'],false,true);
		}
	}

	public function getInfo($field='m_users_id'){
		return $this->info[$field];
	}

	public static function getHash($password) {
		$db_password=array();
		if (defined("CRYPT_BLOWFISH")&&CRYPT_BLOWFISH){
			$salt='$2y$10$'.substr(md5(uniqid(rand(),true)),0,22);
			for($i=0;$i<ceil(strlen($password)/8);$i++)
				$db_password[]=crypt(substr($password,$i*8),$salt);
		}
		return implode('|',$db_password);
	}
	public static function verHash($password,$hashedPassword){
		$verify=1;
		$hashedPassword=explode('|',$hashedPassword);
		if(sizeof($hashedPassword)==ceil(strlen($password)/8)){
			for($i=0;$i<ceil(strlen($password)/8);$i++)
				$verify=$verify*(crypt(substr($password,$i*8),$hashedPassword[$i])==$hashedPassword[$i])?1:0;
			return $verify;
		}
		else
			return false;
	}

	public static function login(){
		global $sql,$e;
		/* if($email_confirm=get('email_confirm')){
			$q='SELECT * FROM `m_employees` WHERE `m_employees_email_confirm`=\'0\' AND `m_users_numb` IN (SELECT `m_qr_id` FROM `m_qr` WHERE `m_qr_qr`=\''.$email_confirm.'\') LIMIT 1;';
			if($res=$sql->query($q))
				$sql->query('UPDATE `m_users` SET `m_users_email_confirm`=\'1\' WHERE `id`='.$res[0]['id'].';');
			else{
				header('Location: /?email-confirm-error');
				exit;
			}
		} */if(1==0){}
		else{
			$data['email']=array(1,null,64,null,4);
			$data['password']=array(1,6,24);
			$data['rm']=array(null,2,3);
			array_walk($data,'check');

			if(!$e){
				$q='SELECT * FROM `formetoo_main`.`m_users` WHERE (`m_users_email`=\''.$data['email'].'\') LIMIT 1;';
				if($res=$sql->query($q)){
					if(!self::verHash($data['password'],$res[0]['m_users_password']))
						$e[]='Неверный пароль пользователя с e-mail '.$data['email'];
				}
				else
					$e[]='Нет пользователя с e-mail '.$data['email'];
			}
		}
		//если запись найдена
		if($res&&!$e){
			$data['rm']=($data['rm']=='on')?1:0;
			$uid=get_id('cookies',0,'cookies_id',true);
			$t_time=dt();
			//запомнить пользователя или нет
			$t_time=$data['rm']?dtc($t_time,'+3 day'):dtc($t_time,'+1 hour');
			setcookie('uid_a',$uid,dtu($t_time),'/','.'.$_SERVER['HTTP_HOST'],false,true);
			$q='INSERT INTO `formetoo_main`.`cookies` (`cookies_m_users_id`,`cookies_cookie`,`cookies_browser`,`cookies_date`,`cookies_rm`) VALUES ('.$res[0]['m_users_id'].',\''.$uid.'\',\''.md5($_SERVER['HTTP_USER_AGENT']).'\',\''.$t_time.'\',\''.$data['rm'].'\');';
			$sql->query($q);
			header('Location: /');
		}
		//если запись не найдена
		else{
			//страница с ошибкой
			elogs();
			header('Location: /?login-error');
		}
		exit;
	}

	public static function logout(){
		global $user,$sql;
		//выход из аккаунта
		$q='DELETE FROM `formetoo_main`.`cookies` WHERE `cookies_cookie`=\''.$user->getInfo('cookie').'\';';
		$sql->query($q);
		//удаляем cookie
		setcookie('uid_a','',time()-259200,'/','.'.$_SERVER['HTTP_HOST'],false,true);
		header('Location: /');
		exit;
	}

	public function logout_all(){
		//выход из аккаунта
		$q='DELETE FROM `formetoo_main`.`cookies` WHERE `cookies_m_users_id`=\''.$user->getInfo('cookie').'\';';
		$sql->query($q);
		//удаляем cookie
		setcookie('uid_a','',time()-259200,'/','.'.$_SERVER['HTTP_HOST'],false,true);
		header('Location: /');
		exit;
	}

	public static function sendEmailPassword($password){
		global $sql,$e;
		$data['password']=val($password);

	}

	public static function group_add(){
		global $sql,$e;
		$data['m_users_groups_name']=array(1,null,80);
		$data['m_users_groups_rights_read[]']=array();
		$data['m_users_groups_rights_change[]']=array();
		$data['m_users_groups_rights_delete[]']=array();
		$data['m_users_groups_rights_create[]']=array();
		$data['m_users_groups_rights_myself[]']=array();

		array_walk($data,'check');

		if(!$e){
			$data['m_users_groups_id']=get_id('m_users_groups');

			$q='INSERT `formetoo_main`.`m_users_groups` SET
				`m_users_groups_id`='.$data['m_users_groups_id'].',
				`m_users_groups_name`=\''.$data['m_users_groups_name'].'\',
				`m_users_groups_rights_read`=\''.($data['m_users_groups_rights_read[]']?implode('|',$data['m_users_groups_rights_read[]']):'').'\',
				`m_users_groups_rights_change`=\''.($data['m_users_groups_rights_change[]']?implode('|',$data['m_users_groups_rights_change[]']):'').'\',
				`m_users_groups_rights_delete`=\''.($data['m_users_groups_rights_delete[]']?implode('|',$data['m_users_groups_rights_delete[]']):'').'\',
				`m_users_groups_rights_create`=\''.($data['m_users_groups_rights_create[]']?implode('|',$data['m_users_groups_rights_create[]']):'').'\',
				`m_users_groups_rights_myself`=\''.($data['m_users_groups_rights_myself[]']?implode('|',$data['m_users_groups_rights_myself[]']):'').'\';';

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

	public static function user_add(){
		global $sql,$e;
		$data['m_users_login']=array(1,null,64,null,4);
		$data['m_users_password']=array(1,6,20);
		$data['m_users_group']=array(1,null,null,10,1);

		array_walk($data,'check');

		$q='SELECT * FROM `formetoo_main`.`m_users` WHERE `m_users_email`=\''.$data['m_users_login'].'\' LIMIT 1;';
		if($sql->query($q))
			$e[]='Логин '.$data['m_users_login'].' уже есть в системе';

		if(!$e){
			$data['m_users_id']=get_id('m_users');

			$q='INSERT `formetoo_main`.`m_users` SET
				`m_users_id`='.$data['m_users_id'].',
				`m_users_login`=\''.$data['m_users_login'].'\',
				`m_users_password`=\''.user::getHash($data['m_users_password']).'\',
				`m_users_group`='.$data['m_users_group'].',
				`m_users_active`=1;';

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

	public static function getGroups(){
		global $sql,$e;
		return $sql->query('SELECT * FROM `formetoo_main`.`m_users_groups`;','m_users_groups_id');
	}

	public static function getUsers(){
		global $sql,$e;
		return $sql->query('SELECT * FROM `formetoo_main`.`m_users`;','m_users_id');
	}


}
?>
