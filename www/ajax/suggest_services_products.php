<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
require_once('../../functions/classes/info.php');
$sql=new sql();
$info=new info();

function tag_b($text,$b){
	foreach($b as &$_b)
		$_b='/('.preg_quote($_b,'/').')/iu';
	$text=preg_replace($b,'<b>$1</b>',$text); 
	return $text;
}

function cmp($a,$b){
	if(isset($a['m_services_name'])&&isset($b['m_services_name'])){
		$c_a=mb_substr_count($a['m_services_name'],'<b>','utf-8');
		$c_b=mb_substr_count($b['m_services_name'],'<b>','utf-8');
		if($c_a==$c_b)
			return 0;
		return ($c_a>$c_b)?-1:1;
	}
	return 0;
}

function currency($type){
	global $info;
	switch($type){
		case 2:
			return $info->getSettings('m_info_settings_exchange_usd');
			break;
		case 3:
			return $info->getSettings('m_info_settings_exchange_eur');
			break;
		default:
			return 1;
	}
}

$products=get('products');
$services=get('services');
$w=trim(get('w',array('\\','%','_','\''),array('\\\\','\%','\_','\\\'')));
if(mb_strlen($w,'utf-8')<3) exit;
if (stristr($_SERVER['HTTP_USER_AGENT'],'MSIE'))//другая кодировка для IE
	$w=trim(w_only(iconv('cp1251','utf-8',$w)));
if ($w){
	$w=explode(' ',$w);
	
	if($services=='true'){
		//УСЛУГИ
		$q='SELECT * FROM `formetoo_main`.`m_services` WHERE `m_services_name` LIKE \'%'.implode(' ',$w).'%\' AND `m_services_categories_id`!=1326613691 LIMIT 7;';
		if($res=$sql->query($q)){
			echo '<span class="suggest-delimeter">Услуги</span>';
			foreach($res as $record)
				echo '<a data-id="',
					$record['m_services_id'],
					'" href="',
					$record['m_services_name'],
					'" data-price="',
					$record['m_services_price_general'],
					'" data-category="',
					$record['m_services_categories_id'],
					'" data-unit="',
					$info->getUnitsNoHTML($record['m_services_unit'],false),
					'" data-table="services">',
					'<span style="color:#999">['.$record['m_services_id'].']</span> ',
					tag_b($record['m_services_name'],$w),
					'</a>';
		}
		else{
			$like=[];
			foreach($w as $_w){
				if (mb_strlen($_w,'utf-8')<3) continue;
				$like[]='`m_services_name` LIKE \'%'.$_w.'%\'';
			}
			$like=implode(' AND ',$like);
			$q='SELECT * FROM `formetoo_main`.`m_services` WHERE `m_services_categories_id`!=1326613691 AND '.$like.';';
			if($res=$sql->query($q)){
				echo '<span class="suggest-delimeter">Услуги</span>';
				foreach($res as &$record)
					$record['m_services_name']=tag_b($record['m_services_name'],$w);
				uasort($res,'cmp');
				$i=0;
				foreach($res as $record){
					if($i<=10)
						echo '<a data-id="',
							$record['m_services_id'],
							'" href="',
							str_replace(array(0=>'<b>',1=>'</b>'),'',$record['m_services_name']),
							'" data-price="',
							$record['m_services_price_general'],
							'" data-category="',
							$record['m_services_categories_id'],
							'" data-unit="',
							$info->getUnitsNoHTML($record['m_services_unit'],false),
							'" data-table="services">',
							'<span style="color:#999">['.$record['m_services_id'].']</span> ',
							$record['m_services_name'],
							'</a>';
					$i++;
				}
			}
		}
	}
	
	if($products=='true'){
		//ТОВАРЫ
		$q='SELECT * FROM `formetoo_main`.`m_products` WHERE `m_products_name` LIKE \'%'.implode(' ',$w).'%\' AND `m_products_categories_id`!=1263923105 LIMIT 7;';
		if($res=$sql->query($q)){
			echo '<span class="suggest-delimeter">Товары</span>';
			foreach($res as $record)
				echo '<a data-id="',
					$record['m_products_id'],
					'" rel="',
					$record['m_products_id'],
					'" href="',
					$record['m_products_name'],
					'" data-price="',
					number_format($record['m_products_price_general']*currency($record['m_products_price_currency']),2,'.',''),
					'" data-category="',
					$record['m_products_categories_id'],
					'" data-unit="',
					$info->getUnitsNoHTML($record['m_products_unit'],false),
					'" data-table="products">',
					'<span style="color:#999">['.$record['m_products_id'].']</span> ',
					tag_b($record['m_products_name'],$w),
					'</a>';
		}
		else{
			$like=[];
			foreach($w as $_w){
				if (mb_strlen($_w,'utf-8')<3) continue;
				$like[]='`m_products_name` LIKE \'%'.$_w.'%\'';
			}
			$like=implode(' AND ',$like);
			$q='SELECT * FROM `formetoo_main`.`m_products` WHERE `m_products_categories_id`!=1263923105 AND '.$like.';';
			if($res=$sql->query($q)){
				echo '<span class="suggest-delimeter">Товары</span>';
				foreach($res as &$record)
					$record['m_products_name']=tag_b($record['m_products_name'],$w);
				uasort($res,'cmp');
				$i=0;
				foreach($res as $record){
					if($i<=10)
						echo '<a data-id="',
							$record['m_products_id'],
							'" rel="',
							$record['m_products_id'],
							'" href="',
							str_replace(array(0=>'<b>',1=>'</b>'),'',$record['m_products_name']),
							'" data-price="',
							$record['m_products_price_general'],
							'" data-category="',
							$record['m_products_categories_id'],
							'" data-unit="',
							$info->getUnitsNoHTML($record['m_products_unit'],false),
							'" data-table="products">',
							'<span style="color:#999">['.$record['m_products_id'].']</span> ',
							$record['m_products_name'],
							'</a>';
					$i++;
				}
			}
		}
	}
}	
unset($sql);
?>