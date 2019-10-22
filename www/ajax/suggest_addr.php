<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
$sql=new sql(1);

/* function tag_b($text,$b){ 
	$text= preg_replace('/('.$b.')/iu','<b>$1</b>',$text); 
	return $text;} */
function tag_b($text,$b){
	$b='/('.preg_quote($b,'/').')/iu';
	$text=preg_replace($b,'<b>$1</b>',$text); 
	return $text;
}

function sd($sokr){
	 switch ($sokr){
		case 'г':
		case 'Аобл':
		case 'Респ':
		case 'обл':
		case 'тер':
		case 'у':
		case 'д':
		case 'м':
		case 'мкр':
		case 'п':
		case 'с':
		case 'сл':
		case 'ст':
		case 'х':
		case 'дор':
		case 'наб':
		case 'пер':
		case 'пл':
		case 'платф':
		case 'стр':
		case 'туп':
		case 'ул':
		case 'ш':
			$sokr.='.';
			break;}
	return $sokr;}

function w_only($w){//получение названий без типа
	$we='';
	if (strpos($w,'.'))
		$tw=explode('.',$w);
	elseif (strpos($w,' '))
		$tw=explode(' ',$w);
	elseif (strpos($w,'. '))
		$tw=explode('. ',$w);
	if (isset($tw)){
		if (preg_match('/г|Аобл|Респ|обл|тер|у|д|м|мкр|п|с|сл|ст|х|дор|наб|пер|пл|платф|стр|туп|ул|ш/iu',$tw[0])){
			for ($i=1;$i<sizeof($tw);$i++)
				$we.=' '.$tw[$i];
			return $we;}
		else return $w;}
	else return $w;}
	
function swap($name,$type){//прикрепление типа к названию при выборе из списка
	if (preg_match('/г.|тер.|д.|м.|п.|с.|ст.|х.|дор.|наб.|пер.|пл.|платф.|стр.|туп.|ул.|пр-кт/iu',$type))
		return $type.' '.$name;
	else
		return $name.' '.$type;}

$w=trim(get('w',array('\\','%','_','\''),array('\\\\','\%','\_','\\\'')));
if (stristr($_SERVER['HTTP_USER_AGENT'],'MSIE'))//другая кодировка для IE
	$w=trim(w_only(iconv('cp1251','utf-8',$w)));
$area=get('area');//субъект федерации
$subarea=get('subarea');//район
$city=get('city');//город
$type=get('type');//тип текущего поля для ввода
if ($w){
	switch($type){
		case 'area': 
			$query='SELECT * FROM `u0023354_address`.`'.$type.'` WHERE `name` LIKE \'%'.$w.'%\' OR `clid` LIKE \''.$w.'%\' LIMIT 10;';
			break;
		case 'subarea':
			$query='SELECT * FROM `u0023354_address`.`'.$type.'` WHERE `clid` LIKE \''.substr($area,0,2).'___00000000\' AND `name` LIKE \'%'.$w.'%\' LIMIT 10;';
			break;
		case 'city':
			if (!empty($subarea))
				$query='SELECT * FROM `u0023354_address`.`'.$type.'` WHERE `clid` LIKE \''.substr($subarea,0,5).'________\' AND `name` LIKE \'%'.$w.'%\' LIMIT 10;';
			else
				$query='SELECT * FROM `u0023354_address`.`'.$type.'` WHERE `clid` LIKE \''.substr($area,0,5).'________\' AND `name` LIKE \'%'.$w.'%\' LIMIT 10;';
			break;
		case 'street':
			if (!empty($city))
				$query='SELECT * FROM `u0023354_address`.`'.$type.'` WHERE `clid` LIKE \''.substr($city,0,11).'______\' AND `name` LIKE \'%'.$w.'%\' LIMIT 10;';
			else
				$query='SELECT * FROM `u0023354_address`.`'.$type.'` WHERE `clid` LIKE \''.substr($area,0,5).'000000______\' AND `name` LIKE \'%'.$w.'%\' LIMIT 10;';
			break;
		default: 
			$query='SELECT * FROM `u0023354_address`.`area` WHERE 0=1';
			break;}
	if($res=$sql->query($query))
		foreach($res as $record){
			echo '<a rel="',
				$record['clid'],
				'" href="',
				swap($record['name'],sd($record['type'])), //тип города (г., д., пгт, ...)
				'">',
				swap(tag_b($record['name'],$w),sd($record['type'])),
				'</a>';
		}
}
unset($sql);
?>