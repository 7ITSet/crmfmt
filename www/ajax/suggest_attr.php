<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
require_once('../../functions/classes/info.php');
$sql=new sql();
$info=new info();

function tag_b($text,$b){
	global $all;
	if($all) return $text;
	foreach($b as &$_b)
		$_b='/('.preg_quote($_b,'/').')/iu';
	$text=preg_replace($b,'<b>$1</b>',$text); 
	return $text;
}

$all=(get('w')=='  ')?true:false;
$w=trim(get('w',array('\\','%','_','\''),array('\\\\','\%','\_','\\\'')));
$data['list_id']=array(null,null,null,10,1);
$data['type']=array(null,null,null,10,1);
$data['list_type']=array(1,null,null,1,1);

array_walk($data,'check',true);

if(!$all&&mb_strlen($w,'utf-8')<2) exit;
if (stristr($_SERVER['HTTP_USER_AGENT'],'MSIE'))//другая кодировка для IE
	$w=trim(w_only(iconv('cp1251','utf-8',$w)));
if ($w||$all){
	$w=explode(' ',$w);
	$q='SELECT DISTINCT `m_products_attributes_value` FROM `formetoo_main`.`m_products_attributes` WHERE `m_products_attributes_list_id`='.$data['list_id'].' AND `m_products_attributes_value` IN(SELECT `m_products_attributes_values_id` FROM `m_products_attributes_values` WHERE `m_products_attributes_values_value` LIKE \'%'.implode(' ',$w).'%\') LIMIT 10;';
	if($data['list_type']==1)
		$q='SELECT DISTINCT `m_products_attributes_values_value` FROM `formetoo_main`.`m_products_attributes_values` 
			WHERE `m_products_attributes_values_id` IN(
				SELECT DISTINCT `m_products_attributes_value` FROM `formetoo_main`.`m_products_attributes` 
					WHERE `m_products_attributes_list_id`='.$data['list_id'].'
			) AND 
			`m_products_attributes_values_value` LIKE \'%'.implode(' ',$w).'%\' LIMIT 10;';
	if($all){
		if($data['list_type']==1)
			$q='SELECT DISTINCT `m_products_attributes_values_value` FROM `formetoo_main`.`m_products_attributes_values` 
				WHERE `m_products_attributes_values_id` IN(
					SELECT DISTINCT `m_products_attributes_value` FROM `formetoo_main`.`m_products_attributes` 
						WHERE `m_products_attributes_list_id`='.$data['list_id'].'
				) LIMIT 30;';
		else
			$q='SELECT DISTINCT `m_products_attributes_value` FROM `formetoo_main`.`m_products_attributes` WHERE `m_products_attributes_list_id`='.$data['list_id'].' LIMIT 30;';
	}

	if($res=$sql->query($q))
		foreach($res as $record)
			echo '<a href="',
				($data['list_type']==1?$record['m_products_attributes_values_value']:$record['m_products_attributes_value']),
				'">',
				tag_b($data['list_type']==1?$record['m_products_attributes_values_value']:$record['m_products_attributes_value'],$w),
				'</a>';
	else{
		$like=[];
		foreach($w as $_w){
			if (mb_strlen($_w,'utf-8')<3) continue;
			$like[]='`m_products_attributes_value` LIKE \'%'.$_w.'%\'';
		}
		$like=implode(' OR ',$like);
		$q='SELECT * FROM `formetoo_main`.`m_products_attributes` WHERE `m_products_attributes_list_id`='.$data['list_id'].' AND '.$like.';';
		if($all)
			$q='SELECT DISTINCT `m_products_attributes_value` FROM `formetoo_main`.`m_products_attributes` WHERE `m_products_attributes_list_id`='.$data['list_id'].' LIMIT 30;';
		if($res=$sql->query($q)){
			foreach($res as &$record)
				$record['m_products_attributes_value']=tag_b($record['m_products_attributes_value'],$w);
			function cmp($a,$b){
				$c_a=mb_substr_count($a['m_products_attributes_value'],'<b>','utf-8');
				$c_b=mb_substr_count($b['m_products_attributes_value'],'<b>','utf-8');
				if($c_a==$c_b)
					return 0;
				return ($c_a>$c_b)?-1:1;
			}
			uasort($res,'cmp');
			$i=0;
			foreach($res as $record){
				if($i<=10)
					echo '<a href="',
						str_replace(array(0=>'<b>',1=>'</b>'),'',$record['m_products_attributes_value']),
						'">',
						$record['m_products_attributes_value'],
						'</a>';
				$i++;
			}
		}
	}
}	
unset($sql);
?>