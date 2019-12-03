<?
defined ('_DSITE') or die ('Access denied');

class settings{	
	function __construct(){
		global $sql;
		
    $q='SELECT `key`,`value` FROM `formetoo_main`.`m_settings` WHERE `module_id`="main"';
		$res=$sql->query($q);
		
		foreach($res as $item) {
			$this->{$item['key']} = $item['value'];
		}
	}
	
	public static function page_change(){
		global $sql,$e;
		$module_id = 'main';
		$data['min_total_sum_cart'] = array();
		$data['price_guest_visible'] = array();

    array_walk($data,'check');

		if(!$e){
			$data['min_total_sum_cart'] = (is_numeric($data['min_total_sum_cart']) && $data['min_total_sum_cart'] > 0) ? $data['min_total_sum_cart'] : 0;
			$data['price_guest_visible'] = $data['price_guest_visible'] ? 1 : 0;

			foreach ($data as $keyData => $valueData) {
				$valuesArray[] = '(
					\''.$module_id.'\', 
					\''.$keyData.'\', 
					\''.$valueData.'\'
				)';
			}
				
			$q='INSERT INTO `formetoo_main`.`m_settings` (`module_id`,`key`,`value`) VALUES '. implode(',', $valuesArray);
			
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