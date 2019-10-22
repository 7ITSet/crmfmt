<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
$sql=new sql();

$tel=preg_replace('/[^0-9]/','',get('tel'));
if(strlen($tel)<11)
	echo 'SHORT_CODE';
else{
	
	$q='SELECT * FROM `formetoo_cdb`.`m_info_tel_city_code` WHERE m_info_tel_city_code_numb LIKE \''.substr($tel,1,3).'%\';';
	if($res=$sql->query($q)){
		foreach($res as $_code){
			$_code_digit=str_replace('-','',$_code['m_info_tel_city_code_numb']);
			$_tel_9=preg_replace('/\d/ui','9',$tel);
			for($i=10;$i>3;$i--)
				if($_code_digit==substr($tel,1,$i)){
					$c=preg_replace('/\d/ui','9',$_code_digit);
					$result=preg_replace_callback(
						"/(\d{0,3})(\d{1,2})(\d{2})/ui",
						function($a){
							switch(strlen($a[0])){
								case 7:
									$result=$a[1].'-'.$a[2].'-'.$a[3];
									break;
								case 6:
									$result=substr($a[1],0,2).'-'.substr($a[1],2,1).$a[2].'-'.$a[3];
									break;
								case 5:
									$result=substr($a[1],0,1).'-'.substr($a[1],1,1).$a[2].'-'.$a[3];
									break;
								case 4:
									$result=substr($a[1],0,1).$a[2].'-'.$a[3];
									break;
								case 3:
									$result=$a[2].'-'.$a[3];
									break;
							}
							return $result;
						},
						substr($_tel_9,strlen($_code_digit)+1,10)
					);
					echo "+7 ($c) ".$result;
					exit;
				}
		}
	}
	else
		echo '+7 (999) 999-99-99';
}

?>