<?
defined ('_DSITE') or die ('Access denied');

class settings{	
	function __construct(){
		global $sql;
		
    $q='SELECT `value` FROM `formetoo_main`.`m_settings_cart` WHERE `key`="min_total_sum" LIMIT 1;';
    $res=$sql->query($q);
    $this->min_total_sum_cart = (isset($res) && is_numeric($res[0]['value']) && $res[0]['value'] > 0) ? $res[0]['value'] : 0;
	}
	
	public static function page_change(){
		global $sql,$e;
		$data['min_total_sum']=array(null,null,255);

    array_walk($data,'check');

		if(!$e){
      $data['min_total_sum'] = (is_numeric($data['min_total_sum']) && $data['min_total_sum'] > 0) ? $data['min_total_sum'] : 0;
			
			$q='UPDATE `formetoo_main`.`m_settings_cart` SET `value`=\''.$data['min_total_sum'].'\';';
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
}