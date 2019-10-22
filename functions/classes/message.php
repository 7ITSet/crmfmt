<?
defined ('_DSITE') or die ('Access denied');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class message{
	private $info;
		
	public static function addQueueSMS($data=null){
		global $sql,$e;
		
		if($data&&$data['user_id']&&$data['data']['tel']&&$data['data']['message']){
			$q='INSERT INTO `formetoo_main`.`m_users_messages` SET 
				`m_users_messages_user_id`='.$data['user_id'].',
				`m_users_messages_data`=\''.json_encode($data['data'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'\',
				`m_users_messages_type`=2,
				`m_users_messages_date`=\''.dt().'\';';
			if($sql->query($q))
				return true;
			else return null;
		}
		return null;
	}
	
	public static function addQueueEmail($data=null){
		global $sql,$e,$G;
		
		if($data&&$data['user_id']&&$data['data']['email']&&$data['data']['message']){
			
			$q='INSERT INTO `formetoo_main`.`m_users_messages` SET 
				`m_users_messages_user_id`='.$data['user_id'].',
				`m_users_messages_data`=\''.json_encode($data['data'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'\',
				`m_users_messages_type`=1,
				`m_users_messages_date`=\''.dt().'\';';
			if($sql->query($q))
				return true;
			else return null;
		}
		return null;
	}

	
	public function sendSMS(){
		global $sql;
		
		$q='SELECT * FROM `formetoo_main`.`m_users_messages` WHERE 
			`m_users_messages_status`=0 AND 
			`m_users_messages_type`=2 AND
			`m_users_messages_delivery`=0 
			LIMIT 5;';
		if($res=$sql->query($q)){
			$ids=array();
			//$phones=$messages=array();
			foreach($res as $i=>$_res){
				if($_res['m_users_messages_data']=json_decode($_res['m_users_messages_data'])){
					//ОТПРАВКА СООБЩЕНИЙ ПО ОДНОМУ
					$ids[]=$_res['m_users_messages_id'];			
					$url='https://smsc.ru/sys/send.php';
					// ПАРАМЕТРЫ ДЛЯ ОТПРАВКИ ЗАПРОСА - ЛОГИН И ПАРОЛЬ
					$post_data=array(
						'login'=>'alta-region',
						'psw'=>'1029384756Z',
						'phones'=>preg_replace('/\D*/ui','','+'.$_res['m_users_messages_data']->tel),
						'mes'=>$_res['m_users_messages_data']->message,
						'id'=>$_res['m_users_messages_id'],
						'sender'=>'formetoo.ru',
						//'tinyurl'=>1,
						'charset'=>'utf-8');
					// создание объекта curl
					$curl = curl_init();
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
					curl_setopt($curl, CURLOPT_TIMEOUT, 4);
					curl_setopt($curl, CURLOPT_HEADER, 1);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
					curl_setopt($curl, CURLOPT_POST, 1);
					curl_setopt($curl, CURLOPT_URL, $url);
					curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
					
					if($sms_res = curl_exec($curl))
						var_dump($sms_res);
				}
				
				//$phones[]=preg_replace('/\D*/ui','','+'.$_res['m_users_messages_data']->tel);
				//$messages[]=$_res['m_users_messages_data']->message;
				
			}
			
			//изменяем статусы отправленных SMS (0 - не обработано, 1 - отправлено, 2 - принято)
			$q='UPDATE `formetoo_main`.`m_users_messages` SET `m_users_messages_status`=1 WHERE `m_users_messages_id` IN('.implode(',',$ids).');';
			$sql->query($q);
		}
	}

	
	public function sendEmail($data=array(
		'email_to'=>'',
		'name_to'=>'',
		'email_from'=>'no-reply@formetoo.ru',
		'email_feedback'=>'',
		'name_feedback'=>'',
		'name_from'=>'formetoo.ru',
		'data_charset'=>'utf-8',
		'send_charset'=>'utf-8',
		'subject'=>'',
		'body'=>'',
		'attach'=>'',
		'body_for_plain'=>'')
	){
		global $sql,$e,$msg,$city;
		
		require_once(__DIR__.'/import/PHPMailer/src/Exception.php');
		require_once(__DIR__.'/import/PHPMailer/src/PHPMailer.php');
		require_once(__DIR__.'/import/PHPMailer/src/SMTP.php');
		
		if(!$data['email_to']){
			$q='SELECT * FROM `formetoo_main`.`m_users_messages` WHERE 
				`m_users_messages_status`=0 AND 
				`m_users_messages_type`=1 AND
				`m_users_messages_delivery`=0 
				LIMIT 5;';
			if($res=$sql->query($q)){
				$ids_ok=array();
				$ids_error=array();
				foreach($res as $_res){
					if($_res['m_users_messages_data']=json_decode($_res['m_users_messages_data'])){
						$msg=$_res['m_users_messages_data']->message=base64_decode($_res['m_users_messages_data']->message);
						$city=$_res['m_users_messages_data']->city;
						$data['email_feedback']=$_res['m_users_messages_data']->email_feedback;
						$data['name_feedback']=$_res['m_users_messages_data']->name_feedback;
						
						//если письмо исходящее
						if(!$data['email_feedback']){
							//загружаем фирменный шаблон письма и заменяем теги {ТЕГ} нужными значениями (функцией
							//replaceTags этого же класса, подстановочные значения получает из глобальных переменных)
							$template=file_get_contents(__DIR__.'/../require/email_templates/base.html');
							$template=preg_replace_callback('/\{(\w+)\}/ui',array($this,'replaceTags'),$template);
							$data['body']=$template;
						}
						//если письмо входящее - отправляем текст без шаблона
						else $data['body']=$msg;
						
						$data['name_to']=$_res['m_users_messages_data']->name_to;
						$data['subject']=$_res['m_users_messages_data']->subject;
						$data['body_for_plain']=$this->html2plain($_res['m_users_messages_data']->message);
						$data['email_to']=$_res['m_users_messages_data']->email;
						$data['attach']=isset($_res['m_users_messages_data']->attach)?$_res['m_users_messages_data']->attach:'';

						$mail = new PHPMailer(true);
						try {
							//Server settings
							$mail->CharSet = "UTF-8";
							$mail->isSMTP();
							$mail->Host = 'smtp.yandex.ru';
							$mail->SMTPAuth = true; 
							$mail->Username = 'no-reply@formetoo.ru';
							$mail->Password = 'oj*GHg3t45y,';
							$mail->SMTPSecure = 'tls';
							$mail->Port = 587;

							//Recipients
							$mail->setFrom($data['email_from'],$data['name_from']);
							$mail->addAddress($data['email_to'],$data['name_to']);
							if($data['email_feedback'])
								$mail->addReplyTo($data['email_feedback'],$data['name_feedback']);

							//Content
							$mail->isHTML(true);
							$mail->Subject = $data['subject'];
							$mail->Body    = $data['body'];
							$mail->AltBody = $data['body_for_plain'];
							if($data['attach'])
								foreach($data['attach'] as $_attach)
									$mail->addAttachment($_attach->file,$_attach->name);
							
							//логотип и иконки встраиваем в письмо
							if(!$data['email_feedback']){
								$mail->AddEmbeddedImage(__DIR__.'/../require/email_templates/email_logo.png', 'email_logo', 'email_logo.png');
								$mail->AddEmbeddedImage(__DIR__.'/../require/email_templates/email_social_twitter.png', 'email_social_twitter', 'email_social_twitter.png');
								$mail->AddEmbeddedImage(__DIR__.'/../require/email_templates/email_social_ok.png', 'email_social_ok', 'email_social_ok.png');
								$mail->AddEmbeddedImage(__DIR__.'/../require/email_templates/email_social_vk.png', 'email_social_vk', 'email_social_vk.png');
								$mail->AddEmbeddedImage(__DIR__.'/../require/email_templates/email_social_gplus.png', 'email_social_gplus', 'email_social_gplus.png');
								$mail->AddEmbeddedImage(__DIR__.'/../require/email_templates/email_social_fb.png', 'email_social_fb', 'email_social_fb.png');
							}

							$mail->send();
							$ids_ok[]=$_res['m_users_messages_id'];
						} catch (Exception $e) {
							$q='UPDATE `formetoo_main`.`m_users_messages` SET `m_users_messages_response`=\''.$mail->ErrorInfo.'\' WHERE `m_users_messages_id`='.$_res['m_users_messages_id'].' LIMIT 1;';
							$sql->query($q);
						}
						unset($mail);
						
					}
							
				}				
				//изменяем статусы отправленных email (0 - не обработано, 1 - отправлено, 2 - принято)
				if($ids_ok){
					$q='UPDATE `formetoo_main`.`m_users_messages` SET `m_users_messages_status`=1 WHERE `m_users_messages_id` IN('.implode(',',$ids_ok).');';
					$sql->query($q);
					return true;
				}
				else return false;
			}
			else return false;
		}
		else return false;
	}
	
	private function html2plain($text){
		require_once(__DIR__.'/import/Html2Text.php');
		$html = new \Html2Text\Html2Text($text);
		return $html->getText();
	}
	
	private function replaceTags($tag){
		global $msg,$city;
		
		switch($tag[1]){
			case 'CITY':
				return $city;
				break;
			case 'DATE':
				return dtru('l').mb_strtolower(dtru(', j F Y'));
				break;
			case 'BODY':
				return $msg;
				break;
		}
	}
}
?>