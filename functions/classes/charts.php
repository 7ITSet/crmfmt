<?
//проверка на существование uid в cookies
defined ('_DSITE') or die ('Access denied');

class charts{
	
	public static function linear($date_s=0,$date_e=0,$limit=0,$group='day'){
		global $sql,$user,$e;
		
		if(!is_numeric($date_s))
			$date_s=strtotime(preg_replace('/[^0-9]{1,}/ui','.',$date_s));
		if(!is_numeric($date_e))
			$date_e=strtotime(preg_replace('/[^0-9]{1,}/ui','.',$date_e));
		if(!$date_s)
			$date_s=1262304000;
		if(!$date_e||$date_e>time())
			$date_e=time();
	echo	$q='SELECT * FROM `formetoo_cdb`.`m_buh` WHERE `m_buh_date` > "'.dtc('','-1 month').'" ORDER BY `m_buh_date`'.($limit?' LIMIT '.$limit:'').';';
		if($res=$sql->query($q)){
			switch($group){
				case 'week':
					break;
				case 'month':
					break;
				case 'year':
					break;
				default:
					$t=array();
					for($i=0;$i<sizeof($res);$i++){
						$t[date('Y-m-d',dtu($res[$i]['m_buh_date'],'Y-m-d'))]['m_buh_sum'][]=$res[$i]['m_buh_sum']*$res[$i]['m_buh_type'];
					}
					foreach($t as &$t_)
						$t_['m_buh_sum']=array_sum($t_['m_buh_sum']);
					ksort($t);
					return $t;
					break;
			}
		}
		return false;
	}
	
}
?>