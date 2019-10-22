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
	$q='SELECT * FROM `formetoo_cdb`.`m_info_units` WHERE `m_info_units_name` LIKE \'%'.implode(' ',$w).'%\' OR `m_info_units_name_full` LIKE \'%'.implode(' ',$w).'%\' LIMIT 7;';
	if($res=$sql->query($q))
		foreach($res as $record)
			echo '<a href="',
				$info->getUnitsNoHTML($record['m_info_units_id'],false),
				'" data-unit="',
				$record['m_info_units_id'],
				'">',
				tag_b($info->getUnitsNoHTML($record['m_info_units_id'],false),$w),
				' <span style="opacity:.3">('.tag_b($record['m_info_units_name_full'],$w).')</span>',
				'</a>';
	else{
		$like=[];
		foreach($w as $_w){
			if (mb_strlen($_w,'utf-8')<3) continue;
			$like[]='`m_info_units_name` LIKE \'%'.$_w.'%\' OR `m_info_units_name_full` LIKE \'%'.$_w.'%\'';
		}
		$like=implode(' OR ',$like);
		$q='SELECT * FROM `formetoo_cdb`.`m_info_units` WHERE '.$like.';';
		if($res=$sql->query($q)){
			foreach($res as &$record)
				$record['m_info_units_name']=tag_b($info->getUnitsNoHTML($record['m_info_units_id'],false),$w);
			function cmp($a,$b){
				$c_a=mb_substr_count($a['m_info_units_name'],'<b>','utf-8');
				$c_b=mb_substr_count($b['m_info_units_name'],'<b>','utf-8');
				if($c_a==$c_b)
					return 0;
				return ($c_a>$c_b)?-1:1;
			}
			uasort($res,'cmp');
			$i=0;
			foreach($res as $record){
				if($i<=10)
					echo '<a href="',
						str_replace(array(0=>'<b>',1=>'</b>'),'',$record['m_info_units_name']),
						'" data-unit="',
						$record['m_info_units_id'],
						'">',
						$record['m_info_units_name'],
						' <span style="opacity:.3">('.$record['m_info_units_name_full'].')</span>',
						'</a>';
				$i++;
			}
		}
	}
}	
unset($sql);
?>