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
	
$w=trim(get('w',array('\\','%','_','\''),array('\\\\','\%','\_','\\\'')));
if(mb_strlen($w,'utf-8')<3) exit;
if (stristr($_SERVER['HTTP_USER_AGENT'],'MSIE'))//другая кодировка для IE
	$w=trim(w_only(iconv('cp1251','utf-8',$w)));
if ($w){
	$w=explode(' ',$w);
	$q='SELECT * FROM `formetoo_main`.`m_products` WHERE `m_products_name` LIKE \'%'.implode(' ',$w).'%\' LIMIT 7;';
	if($res=$sql->query($q))
		foreach($res as $record)
			echo '<a data-id="',
				$record['m_products_id'],
				'" rel="',
				$record['m_products_id'],
				'" href="',
				$record['m_products_name'],
				'" data-price="',
				$record['m_products_price_general'],
				'" data-unit="',
				$info->getUnitsNoHTML($record['m_products_unit'],false),
				'">',
				tag_b($record['m_products_name'],$w),
				'</a>';
	else{
		$like=[];
		foreach($w as $_w){
			if (mb_strlen($_w,'utf-8')<3) continue;
			$like[]='`m_products_name` LIKE \'%'.$_w.'%\'';
		}
		$like=implode(' OR ',$like);
		$q='SELECT * FROM `formetoo_main`.`m_products` WHERE '.$like.';';
		if($res=$sql->query($q)){
			foreach($res as &$record)
				$record['m_products_name']=tag_b($record['m_products_name'],$w);
			function cmp($a,$b){
				$c_a=mb_substr_count($a['m_products_name'],'<b>','utf-8');
				$c_b=mb_substr_count($b['m_products_name'],'<b>','utf-8');
				if($c_a==$c_b)
					return 0;
				return ($c_a>$c_b)?-1:1;
			}
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
						'" data-unit="',
						$info->getUnitsNoHTML($record['m_products_unit'],false),
						'">',
						$record['m_products_name'],
						'</a>';
				$i++;
			}
		}
	}
}	
unset($sql);
?>