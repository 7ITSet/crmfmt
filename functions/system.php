<?
defined ('_DSITE') or die ('Access denied');

$sitename='crm.Formetoo.ru';
$main_dir1=explode('/',$_SERVER['DOCUMENT_ROOT']);
$main_dir=array();
foreach($main_dir1 as $_dir)
	if($_dir==$sitename){
		$main_dir[]=$_dir;
		break;
	}	
	else
		$main_dir[]=$_dir;
$main_dir=implode('/',$main_dir);	
		

//очистка переменных
function val($param,$a=array(),$b=array(),$html=0){
global $sql;
	//если a не пустой - дозаполняем b до размера a пустыми элементами для замены
	$b=($a)?array_pad($b,sizeof($a),''):$b;
	//соединяем массивы поиска и массивы замены
	//$s=array_merge(array('\\','..','|','¦',chr(0)),$a);
	$s=array_merge(array('\\','..',chr(0)),$a);
	$r=array_merge(array('/','',''),$b);
	if(is_array($param)){
		foreach($param as &$param_){
			$param_=str_replace($s,$r,$param_);
			if(!$html && !is_array($param)){
				$param_=htmlspecialchars($param_);
				$param_=strip_tags($param_);
			}
			if(is_array($param_)){
				$param_=htmlspecialchars($param_[0]);
				$param_=strip_tags($param_);
			}
			$param_=$sql->real_escape($param_);
		}
	}
	else{
		$param=str_replace($s,$r,$param);
		if(!$html){
			$param=htmlspecialchars($param);
			$param=strip_tags($param);
		}
		$param=$sql->real_escape($param);
	}
	return $param;
}
function get($param,$a=array(),$b=array(),$html=0){
	if(isset($_GET[$param]))
		return val($_GET[$param],$a,$b,$html);
	else
		return '';
}
function post($param,$a=array(),$b=array(),$html=0){
	if(isset($_POST[$param]))
		return val($_POST[$param],$a,$b,$html);
	else
		return '';
}

function url(){
	$uri=explode('?',$_SERVER['REQUEST_URI'],2);
	return $uri[0];
}

//текущая или заданная дата и время
function dt($time=''){
	$time=$time?$time:time();
	return date('Y-m-d H:i:s',$time);
}
//текущая дата и время в unix или другом формате
function dtu($time='',$format=''){
	$date = new DateTime($time,new DateTimeZone('Europe/Minsk'));
	if(!$format)
		return $date->getTimestamp();
	return $date->format($format);	
}
//смена формата даты и времени
function dtc($time='',$change=''){
	$date = new DateTime($time,new DateTimeZone('Europe/Minsk'));
	if($change)
		$date->modify($change);
	return $date->format('Y-m-d H:i:s');	
}
function dtru(){
	$translate = array(
		"am" => "дп",
		"pm" => "пп",
		"AM" => "ДП",
		"PM" => "ПП",
		"Monday" => "Понедельник",
		"Mon" => "Пн",
		"Tuesday" => "Вторник",
		"Tue" => "Вт",
		"Wednesday" => "Среда",
		"Wed" => "Ср",
		"Thursday" => "Четверг",
		"Thu" => "Чт",
		"Friday" => "Пятница",
		"Fri" => "Пт",
		"Saturday" => "Суббота",
		"Sat" => "Сб",
		"Sunday" => "Воскресенье",
		"Sun" => "Вс",
		"January" => "Января",
		"Jan" => "Янв",
		"February" => "Февраля",
		"Feb" => "Фев",
		"March" => "Марта",
		"Mar" => "Мар",
		"April" => "Апреля",
		"Apr" => "Апр",
		"May" => "Мая",
		"May" => "Мая",
		"June" => "Июня",
		"Jun" => "Июн",
		"July" => "Июля",
		"Jul" => "Июл",
		"August" => "Августа",
		"Aug" => "Авг",
		"September" => "Сентября",
		"Sep" => "Сен",
		"October" => "Октября",
		"Oct" => "Окт",
		"November" => "Ноября",
		"Nov" => "Ноя",
		"December" => "Декабря",
		"Dec" => "Дек",
		"st" => "ое",
		"nd" => "ое",
		"rd" => "е",
		"th" => "ое"
	);
	if (func_num_args()>1){
		$time=func_get_arg(1);
		$date = new DateTime($time,new DateTimeZone('Europe/Minsk'));
		$time=$date->getTimestamp();
		return strtr(date(func_get_arg(0),$time),$translate);
	}
	else return strtr(date(func_get_arg(0)),$translate);
}

function get_id($table='',$count=0,$field='',$str=false){
global $sql;
	$a=array();
	$field=$field?$field:$table.'_id';
	if($str){
		$s_a=str_split('QWXZASERGDFCVTYHBNUOPILMKJ');
		$s_b=str_split('plmkoiujytgnhbvcrdfeqwsazx');
		$s_c=str_split('7896523014');
		//объединяем массивы
		$s=array_merge($s_a,$s_b,$s_c);
		shuffle($s);
		$s=implode('',$s);
	}
	
	if($table){
		$vcdb_tables=array(
			'm_buh',
			'm_buh_kassa',
			'm_contragents',
			'm_contragents_address',
			'm_contragents_rs',
			'm_contragents_tel',
			'm_delivery_transport',
			'm_documents',
			'm_documents_templates',
			'm_employees',
			'm_info_address_type',
			'm_info_contragents_type',
			'm_info_ip',
			'm_info_ip_city',
			'm_info_orders_status',
			'm_info_post',
			'm_info_settings',
			'm_info_tel_city_code',
			'm_info_tel_type',
			'm_info_units',
			'm_orders'			
		);
		$query=(in_array($table,$vcdb_tables))?'SELECT `'.$field.'` FROM `formetoo_cdb`.`'.$table.'`;':'SELECT `'.$field.'` FROM `formetoo_main`.`'.$table.'`;';
		if($res=$sql->query($query))
			foreach($res as $record)
				$a[]=$record[$field];
	}
	if($count==0){
		$id=$str?substr($s,mt_rand(0,(strlen($s)-13)),12):mt_rand(1000000000,9999999999);
		//пока id не будет уникальным, генерируем новый
		while(in_array($id,$a))
			$id=$str?substr($s,mt_rand(0,(strlen($s)-13)),12):mt_rand(1000000000,9999999999);
	}
	else{
		$id=array();
		for($i=0;$i<$count;$i++){
			$id[$i]=$str?substr($s,mt_rand(0,(strlen($s)-13)),12):mt_rand(1000000000,9999999999);
			//пока id не будет уникальным, генерируем новый
			while(in_array($id[$i],$a))
				$id[$i]=$str?substr($s,mt_rand(0,(strlen($s)-13)),12):mt_rand(1000000000,9999999999);
		}
	}
	return $id;
}

function get_pass($length=10){
	//преобразуем строки в массивы
	$a=str_split('!#&:;$%)^*(@+,_-.');
	$b=str_split('QWXZASERGDFCVTYHBNUOPILMKJ');
	$c=str_split('plmkoiujytgnhbvcrdfeqwsazx');
	$d=str_split('7896523014');
	//объединяем массивы
	$a=array_merge($b,$c,$d);
	
	//перемешиваем массив
	shuffle($a);
	$t_a=implode('',$a);
	//собираем произвольную строку
	return substr($t_a,mt_rand(0,(strlen($t_a)-$length+1)),$length);
}

function recursive_cast_to_array($o) {
	$a = (array)$o;
	foreach ($a as &$value) {
		if (is_object($value)) {
			$value = recursive_cast_to_array($value);
		}
	}
	return $a;
}

function change_key($array,$key_field,$unique=false){
	$result=array();
	if($array)
		foreach($array as $el)
			if($unique)
				$result[$el[$key_field]]=$el;
			else{
				$result[$el[$key_field]][]=$el;
			}
	return $result;
}

/* $data['tcity']=array(обязательный,минимум,максимум,число символов,(1 - число, 2 - телефонный номер, 3 - IP адрес, 4 - электронная почта, 5 - ИНН));
array_walk($data,'check'); */
//проверка переменных
function check(&$el,$key,$get=false){
global $e;
	//если переменная - массив
	if(strpos($key,'[]')!==false)
		$key=substr($key,0,-2);
	//читаем переменную, не очищая её
	if(!$get)
		$t_el=isset($_POST[$key])?$_POST[$key]:'';
	else
		$t_el=isset($_GET[$key])?$_GET[$key]:'';
	//если переменная не является массивом

	if(!is_array($t_el)){
		if (isset($el[0]))
			if ($t_el=='')
				$e[]='Не заполнено обязательное поле ['.$key.'], данные: «'.$t_el.'»';
		if ($t_el&&isset($el[1])&&isset($el[2]))
			if (mb_strlen($t_el,'utf-8')<$el[1]||mb_strlen($t_el,'utf-8')>$el[2])
				$e[]='Длина строки не входит в допустимый диапазон от '.$el[1].' до '.$el[2].' символов в поле ['.$key.'], данные: «'.$t_el.'» ('.mb_strlen($t_el,'utf-8').' символов)';
		if ($t_el&&isset($el[1])&&!isset($el[2]))
			if (mb_strlen($t_el,'utf-8')<$el[1])
				$e[]='Минимальное количество символов '.$el[1].' в поле ['.$key.'], данные: «'.$t_el.'» ('.mb_strlen($t_el,'utf-8').' символов)';
		if ($t_el&&isset($el[2])&&!isset($el[1]))
			if (mb_strlen($t_el,'utf-8')>$el[2])
				$e[]='Максимальное количество символов '.$el[2].' в поле ['.$key.'], данные: «'.$t_el.'» ('.mb_strlen($t_el,'utf-8').' символов)';
		if ($t_el&&isset($el[3]))
			if (mb_strlen($t_el,'utf-8')!=$el[3])
				$e[]='Количество символов должно быть '.$el[3].' в поле ['.$key.'], данные: «'.$t_el.'» ('.mb_strlen($t_el,'utf-8').' символов)';
		if ($t_el&&isset($el[4]))
			switch($el[4]){
				case 1:
					if (!is_numeric($t_el))
						$e[]='Возможен ввод только цифр в поле ['.$key.'], данные: «'.$t_el.'»';
					break;
				case 2:
					if (!preg_match('/^\+7 \d{3,5} \d{1,3}-\d{2}-\d{2}$/i',$t_el)||strlen($t_el)!=16)
						$e[]='Телефон не верный в поле ['.$key.'], данные: «'.$t_el.'»';
					break;
				case 3:
					if (!preg_match('/\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/',$t_el))
						$e[]='IP-адрес не верный в поле ['.$key.'], данные: «'.$t_el.'»';
					break;
				case 4:
					if (!preg_match('/^[a-z0-9]+[a-z0-9_.-]*@[a-z0-9]+[a-z0-9.-]*\\.[a-z]{2,}$/is',$t_el))
						$e[]='E-mail не верный в поле ['.$key.'], данные: «'.$t_el.'»';
					break;
				case 5:
					if (strlen($t_el)==10){
						if(substr($t_el,-1)!=((2*substr($t_el,0,1)+4*substr($t_el,1,1)+10*substr($t_el,2,1)+3*substr($t_el,3,1)+5*substr($t_el,4,1)+9*substr($t_el,5,1)+4*substr($t_el,6,1)+6*substr($t_el,7,1)+8*substr($t_el,8,1))%11)%10)
							$e[]='ИНН не верный в поле ['.$key.'], данные: «'.$t_el.'»';
					}
					elseif (strlen($t_el)==12){
						if(substr($t_el,-2,1)!=((7*substr($t_el,0,1)+2*substr($t_el,1,1)+4*substr($t_el,2,1)+10*substr($t_el,3,1)+3*substr($t_el,4,1)+5*substr($t_el,5,1)+9*substr($t_el,6,1)+4*substr($t_el,7,1)+6*substr($t_el,8,1)+8*substr($t_el,9,1))%11)%10||substr($t_el,-1,1)!=((3*substr($t_el,0,1)+7*substr($t_el,1,1)+2*substr($t_el,2,1)+4*substr($t_el,3,1)+10*substr($t_el,4,1)+3*substr($t_el,5,1)+5*substr($t_el,6,1)+9*substr($t_el,7,1)+4*substr($t_el,8,1)+6*substr($t_el,9,1)+8*substr($t_el,10,1))%11)%10)
							$e[]='ИНН не верный в поле ['.$key.'], данные: «'.$t_el.'»';
					}
					else
						$e[]='ИНН не верный в поле ['.$key.'], данные: «'.$t_el.'»';
					break;
			}
	}
	//если переменная - массив
	else{
		foreach($t_el as $_t_el){
			if (isset($el[0]))
				if ($_t_el=='')
					$e[]='Не заполнено обязательное поле ['.$key.'], данные: «'.$_t_el.'»';
			if ($_t_el&&isset($el[1])&&isset($el[2]))
				if (mb_strlen($_t_el,'utf-8')<$el[1]||mb_strlen($_t_el,'utf-8')>$el[2])
					$e[]='Длина строки не входит в допустимый диапазон от '.$el[1].' до '.$el[2].' символов в поле ['.$key.'], данные: «'.$_t_el.'» ('.mb_strlen($_t_el,'utf-8').' символов)';
			if ($_t_el&&isset($el[1])&&!isset($el[2]))
				if (mb_strlen($_t_el,'utf-8')<$el[1])
					$e[]='Минимальное количество символов '.$el[1].' в поле ['.$key.'], данные: «'.$_t_el.'» ('.mb_strlen($_t_el,'utf-8').' символов)';
			// if ($_t_el&&isset($el[2])&&!isset($el[1]))
			// 	if (mb_strlen($_t_el,'utf-8')>$el[2])
			// 		$e[]='Максимальное количество символов '.$el[2].' в поле ['.$key.'], данные: «'.$_t_el.'» ('.mb_strlen($_t_el,'utf-8').' символов)';
			// if ($_t_el&&isset($el[3]))
			// 	if (mb_strlen($_t_el,'utf-8')!=$el[3])
			// 		$e[]='Количество символов должно быть '.$el[3].' в поле ['.$key.'], данные: «'.$_t_el.'» ('.mb_strlen($_t_el,'utf-8').' символов)';
			if ($_t_el&&isset($el[4]))
				switch($el[4]){
					case 1:
						if (!is_numeric($_t_el))
							$e[]='Возможен ввод только цифр в поле ['.$key.'], данные: «'.$_t_el.'»';
						break;
					case 2:
						if (!preg_match('/^\+7 \(\d{3,5}\) \d{1,3}-\d{2}-\d{2}$/i',$_t_el)||strlen($_t_el)!=18)
							$e[]='Телефон не верный в поле ['.$key.'], данные: «'.$_t_el.'»';
						break;
					case 3:
						if (!preg_match('/\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/',$_t_el))
							$e[]='IP-адрес не верный в поле ['.$key.'], данные: «'.$_t_el.'»';
						break;
					case 4:
						if (!preg_match('/^[a-z0-9]+[a-z0-9_.-]*@[a-z0-9]+[a-z0-9.-]*\\.[a-z]{2,}$/is',$_t_el))
							$e[]='E-mail не верный в поле ['.$key.'], данные: «'.$_t_el.'»';
						break;
					case 5:
						if (strlen($_t_el)==10){
							if(substr($_t_el,-1)!=((2*substr($_t_el,0,1)+4*substr($_t_el,1,1)+10*substr($_t_el,2,1)+3*substr($_t_el,3,1)+5*substr($_t_el,4,1)+9*substr($_t_el,5,1)+4*substr($_t_el,6,1)+6*substr($_t_el,7,1)+8*substr($_t_el,8,1))%11)%10)
								$e[]='ИНН не верный в поле ['.$key.'], данные: «'.$_t_el.'»';
						}
						elseif (strlen($_t_el)==12){
							if(substr($_t_el,-2,1)!=((7*substr($_t_el,0,1)+2*substr($_t_el,1,1)+4*substr($_t_el,2,1)+10*substr($_t_el,3,1)+3*substr($_t_el,4,1)+5*substr($_t_el,5,1)+9*substr($_t_el,6,1)+4*substr($_t_el,7,1)+6*substr($_t_el,8,1)+8*substr($_t_el,9,1))%11)%10||substr($_t_el,-1,1)!=((3*substr($_t_el,0,1)+7*substr($_t_el,1,1)+2*substr($_t_el,2,1)+4*substr($_t_el,3,1)+10*substr($_t_el,4,1)+3*substr($_t_el,5,1)+5*substr($_t_el,6,1)+9*substr($_t_el,7,1)+4*substr($_t_el,8,1)+6*substr($_t_el,9,1)+8*substr($_t_el,10,1))%11)%10)
								$e[]='ИНН не верный в поле ['.$key.'], данные: «'.$_t_el.'»';
						}
						else
							$e[]='ИНН не верный в поле ['.$key.'], данные: «'.$_t_el.'»';
						break;
				}
		}
	}
	//если не было ошибок, то возвращаем очищенную переменную
	if (!is_array($e)||sizeof($e)==0)
		$el=val($t_el);
	else
		return false;
}

function elogs(){
	global $e,$sql,$user;
	if(!$e) {
		$e[]='Ошибка в запросе БД';
	}
		
	$q='INSERT INTO `formetoo_main`.`m_logs_error` (
			`m_logs_error_logs_m_users_id`,
			`m_logs_error_log_message`,
			`m_logs_error_logs_date`
		) VALUES (
			'.(isset($user->getInfo['m_users_id'])?$user->getInfo['m_users_id']:0).',
			\''.implode('<br/>',$e).'\',
			\''.dt().'\'
		);';
	$sql->query($q);
}

function l_search($word,$list){
	//кратчайшее расстояние пока еще не найдено
	$shortest = -1;
	//проходим по словам для нахождения самого близкого варианта
	foreach($list as $_list){
		//вычисляем расстояние между входным словом и текущим
		$lev=levenshtein($word,$_list);
		//проверяем полное совпадение
		if($lev==0){
			//это ближайшее слово (точное совпадение)
			$closest=$_list;
			$shortest=0;
			//выходим из цикла - мы нашли точное совпадение
			break;
		}
		//если это расстояние меньше следующего наименьшего расстояния или если следующее самое короткое слово еще не было найдено
		if($lev<=$shortest||$shortest<0){
			$closest=$_list;
			$shortest=$lev;
		}
	}
	return $closest;
}

if(!function_exists('mb_ucfirst')){ 
	function mb_ucfirst($str,$encoding='utf-8'){ 
		$str=mb_ereg_replace('^[\ ]+','',$str); 
		$str=mb_strtoupper(mb_substr($str,0,1,$encoding),$encoding). 
		mb_substr($str,1,mb_strlen($str),$encoding); 
		return $str;
	}
}

class transform{
	public static function typography($text='',$html=1){
		$pattern=array(
			//заменяем вывод параметров тегов ="значение" меняем на =[значение]
			'/=&quot;(.*?)&quot;/ui',
			//пробелы после знаков препинания
			'/([,\!\?])(\w)/ui',
			//многоточие
			'/(\.{3,})/ui',
			//(",',`)слово -> «слово
			'/(^|\s)&quot;(\S)/ui',
			//слово(",',`) -> слово»
			'/(\S)&quot;($|\W|[ .,?!])/ui',
			//- -> —
			'/ - /',
			//ул. Советская -> ул.&nbsp;Советская
			'/(^|\s)(тер\.|ст\.|дор\.|наб\.|пер\.|пл\.|платф\.|стр\.|вл\.|корп\.|туп\.|ул\.|пр-кт|№|ТЦ|ТРЦ|оф.|оф|о-ва|в|во|без|до|из|к|на|по|а|о|обо|от|перед|при|через|с|у|за|над|для|об|под|про|не|ни|из-под|из-за|по-над|по-за|и|р\/с|к\/с|ИНН|КПП|БИК|тел\.)($|\s)/ui',
			//ул. Советская -> ул.&nbsp;Советская (не влияет на инициалы)
			'/(^|\s)(г\.|д\.|м\.|п\.|с\.|х\.)($|\s)/',
			//Московский мкр. -> Московский&nbsp;мкр.
			'/(\s)(Аобл\.|обл\.|мкр\.|р-н|ш\.|этаж|оф.|кг|г|м|см|км|бы|ли|же|ж)($|\s)/ui',
			//офис №100 -> офис&nbsp;№100
			'/(офис) ([№ 0-9])/ui',
			//20 офис -> 20&nbsp;офис
			'/([№0-9]) (офис)/ui',
			//+7 (123) 456-48-90 -> <nobr>+7 (123) 456-48-90</nobr>
			//'/(\+7[- ]?\(?\d{3,5}\)?[- ]?\d{1,3}[- ]?\d{2}[- ]?\d{2})/ui',
			//обратно меняем значения тегов
			'/=\[(.*?)\]/ui',
			//удаляем лишние пробелы перед закрытием тегов
			'/( |&nbsp;)+<\//ui',
			//пробелы между неразрывными пробелами
			'/(\s{1,})?(&nbsp;)(\s{1,})?/ui',
			//лишние неразрывные пробелы
			'/(&nbsp;)+/ui',
			//лишние пробелы (в т.ч. неразрывные пробелы) перед знаками препинания
			'/( |&nbsp;)+([.,:;\!\?])/ui',
			'/(<p>)(\{require: ?[\w]*\})(<\/p>)/ui'
		);
		$replacement=array(
			'=[$1]',
			'$1 $2',
			'…',
			'$1«$2',
			'$1»$2',
			'&nbsp;— ',
			'$1$2&nbsp;$3',
			'$1$2&nbsp;$3',
			'$1&nbsp;$2$3',
			'$1&nbsp;$2',
			'$1&nbsp;$2',
			//'<nobr>$1</nobr>',
			'=\\"$1\\"',
			'<\/',
			'&nbsp;',
			'&nbsp;',
			'$2',
			'$2'
		);
		$text=preg_replace($pattern,$replacement,$text);
		if($html)
			return $text;
		else
			return html_entity_decode(htmlspecialchars_decode($text),ENT_COMPAT|ENT_HTML401,'utf-8');
	}
	
	//изменение суммы (+10%)
	public static function sum_change($change){
		return preg_replace_callback(
			'/^([+,\-,\*,\/])?(\d+)[.,]?(\d+)?([%])?$/ui',
			function($a){
				$s=(float)($a[2].'.'.$a[3]);
				if($a[4]=='%')
					switch($a[1]){
						case '+':
							return '*'.(1+$s/100);
							break;
						case '-':
							return '*'.(1-$s/100);
							break;
						case '*':
							return '*'.($s/100);
							break;
						case '/':
							return '/'.($s/100);
							break;
					}
				else{
					switch($a[1]){
						case '+':
						case '*':
						case '-':
						case '/':
							return $a[1].$s;
							break;
						default:
							return '*0+'.$s;
					}
				}
					
			},
			$change
		);
	}
	
	//вывод определенного количества слов из текста	
	public static function some($text,$count,$symbols=0){
		if(!$symbols){
			$t=explode(' ',$text);
			$t=array_slice($t,0,$count);
			$t=implode(' ',$t);
			if ($t!=$text)
				$t.='&nbsp;…';
			return $t;
		}
		if(mb_strlen($text,'utf-8')!=mb_strlen(mb_substr($text,0,$count,'utf-8'),'utf-8'))
			return	mb_substr($text,0,$count,'utf-8').'…';
		return $text;
	}
	
	//фамилия и.о.
	public static function fio($text){
		$text=explode(' ',$text);
		$text[1]=isset($text[1])?mb_substr($text[1],0,1,'utf-8').'.':'';
		$text[2]=isset($text[2])?mb_substr($text[2],0,1,'utf-8').'.':'';
		return implode('&nbsp;',$text);
	}
	
	//цвет по id
	public static function colorid($id,$rgba=array(51,180,20,230,30,245,1)){
		return 'rgba('.(floor(substr($id,0,3)*.001*($rgba[1]-$rgba[0]+1))+$rgba[0]).','.(floor(substr($id,3,3)*.001*($rgba[3]-$rgba[2]+1))+$rgba[2]).','.(floor(substr($id,5,3)*.001*($rgba[5]-$rgba[4]+1))+$rgba[4]).','.$rgba[6].')';
	}
		
	public static function translit($text){
		$replace=array('А'=>'a','Б'=>'b','В'=>'v','Г'=>'g','Д'=>'d','Е'=>'e','Ё'=>'e','Ж'=>'j','З'=>'z','И'=>'i','Й'=>'y','К'=>'k','Л'=>'l','М'=>'m','Н'=>'n','О'=>'o','П'=>'p','Р'=>'r','С'=>'s','Т'=>'t','У'=>'u','Ф'=>'f','Х'=>'h','Ц'=>'ts','Ч'=>'ch','Ш'=>'sh','Щ'=>'sch','Ъ'=>'','Ы'=>'y','Ь'=>'','Э'=>'e','Ю'=>'yu','Я'=>'ya','а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'y','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',' '=>'_','.'=>'','/'=>'_','&quot;'=>'','&nbsp;'=>'','<br/>'=>'');
		$text=strtr($text,$replace);
		//убираем недопустимые символы
		$text=preg_replace('/[^A-Za-z0-9_\-]/', '', $text);
		//замена повторов
		$text=preg_replace('#(\_){1,}#', '\1',$text);
		return $text;
	}
		
	public static function price_o($price,$floats=true,$nbsp=false){
		$price=number_format($price,2,',',' ');
		if(substr($price,-2)=='00'&&!$floats)
			$price=substr($price,0,-3);
		if($nbsp)
			$price=str_replace(' ','&nbsp;',$price);
		return $price;
	}
	public static function stock_o($num){
		
		return rtrim(rtrim(number_format($num,4,',',' '),'0'),',');
	}
	
	public static function tel($t){
		$t=preg_replace('/D/i','',$t);
		if(strlen($t)==6)
			$t=preg_replace('/(\d{2})(\d{2})(\d{2})/i','+7 (4932) $1-$2-$3',$t);
		elseif(strlen($t)==10)
			$t=preg_replace('/(\d{3})(\d{3})(\d{2})(\d{2})/i','+7 ($1) $2-$3-$4',$t);
		else
			$t='';
		return $t;
	}
	public static function telClean($t){
		$t=preg_replace('/[^0-9]/ui','',$t);
		return '+'.$t;
	}

	//преобразование даты в формат "дд месяц гггг", 1 - время (без параметра текущее), 2 - показывать "сегодня", "вчера" вместо текущей и прошедшей даты
	public static function date_f($t=false,$words=false,$month_only=false){
		$m=array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
		if(strlen($t)!=14){
			if(!$month_only){
				if ($t==false)
					$t=time();
				if ($words){
					if (date('Ymd',$t)==date('Ymd')+1)
						return 'завтра';
					if (date('Ymd',$t)==date('Ymd'))
						return 'сегодня';
					if (date('Ymd',$t)+1==date('Ymd'))
						return 'вчера';}
				return date('j',$t).' '.$m[date('m',$t)-1].' '.date('Y',$t);
			}
			else
				return $m[date('m',$t)-1];
		}
		else{
			$t=preg_split('//',$t,-1,PREG_SPLIT_NO_EMPTY);
			return $t[6].$t[7].' '.$m[($t[4].$t[5])*1-1].' '.$t[0].$t[1].$t[2].$t[3];
		}
	}
	
	//сумма прописью
	private static function morph($n,$f1,$f2,$f5){
		$n=abs(intval($n))%100;
		if($n>10&&$n<20)
			return $f5;
		$n=$n%10;
		if($n>1 && $n<5)
			return $f2;
		if($n==1)
			return $f1;
		return $f5;
	}
	public static function summ_text($summ,$rouble=true,$upper=true,$rp=false){
		$nul='ноль';
		$ten=array(
			array('','один','два','три','четыре','пять','шесть','семь','восемь','девять'),
			array('','одна','две','три','четыре','пять','шесть','семь','восемь','девять'),
		);
		$a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
		$tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
		$hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
		$unit=array(
			array('копейка','копейки','копеек',1),
			array('рубль','рубля','рублей',0),
			array('тысяча','тысячи','тысяч',1),
			array('миллион','миллиона','миллионов',0),
			array('миллиард','милиарда','миллиардов',0),
		);
		if($rp==true){
			$nul='ноля';
			$ten=array(
				array('','одного','двух','трёх','четырёх','пяти','шести','семи','восьми','девяти'),
				array('','одного','двух','трёх','четырёх','пяти','шести','семи','восьми','девяти'),
			);
			$a20=array('десяти','одиннадцати','двенадцати','тринадцати','четырнадцати' ,'пятнадцати','шестнадцати','семнадцати','восемнадцати','девятнадцати');
			$tens=array(2=>'двадцати','тридцати','сорока','пятьдесяти','шестьдесяти','семьдесяти' ,'восемьдесяти','девяноста');
			$hundred=array('','ста','двухсот','трёхсот','четырёхсот','пятисот','шестисот', 'семисот','восмьисот','девятисот');
		}
		list($rub,$kop)=explode('.',sprintf("%015.2f",floatval($summ)));
		$out=array();
		if (intval($rub)>0){
			foreach(str_split($rub,3) as $uk=>$v){
				if(!intval($v))
					continue;
				$uk=sizeof($unit)-$uk-1;
				$gender=$unit[$uk][3];
				list($i1,$i2,$i3)=array_map('intval',str_split($v,1));
				$out[]=$hundred[$i1];
				if ($i2>1) 
					$out[]=$tens[$i2].' '.$ten[$gender][$i3];
				else 
					$out[]=($i2>0)?$a20[$i3]:$ten[$gender][$i3];
				if ($uk>1)
					$out[]=transform::morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
			}
		}
		else 
			$out[]=$nul;
		$out[]=transform::morph(intval($rub),$unit[1][0],$unit[1][1],$unit[1][2]);
		if($rouble==true)
			$out[]=$kop.' '.transform::morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]);
		$result=trim(preg_replace('/ {2,}/',' ',implode(' ',$out)));
		if($upper==true)
			$result=mb_strtoupper(mb_substr($result,0,1,'utf-8'),'utf-8').mb_substr($result,1,mb_strlen($result),'utf-8');
		//если нужно написать число без рублей и копеек
		if($rouble!=true){
			$array_rouble=array('рубль','рубля','рублей');
			$result=str_replace($array_rouble,'',$result);
		}
		return trim($result);
	}

	private static function get_include_contents($filename) {
	global $user;	
		ob_start();
		include_once 'require/'.$filename[1].'.php';
		return ob_get_clean();
	}
	
	private static function get_include_js() {
	global $content;	
		ob_start();
		echo $content->js;
		return ob_get_clean();
	}
	
	public static function optimize($html){
		global $current_area;
		//включение файлов, перечисленных в фигурных скобках {file: filename}
		$html=preg_replace_callback('/\{require: ?(\w+)\}/ui','transform::get_include_contents',$html);
		$html=preg_replace_callback('/\{requireJS\}/ui','transform::get_include_js',$html);
		$useragent=$_SERVER['HTTP_USER_AGENT'];
		if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
			$current_area['tel']=strip_tags($current_area['tel']);	
		$pattern=array("\r\n","\t","\n",'{icq_status}');
		$replacement=array('','','','<img src="http://status.icq.com/online.gif?icq=662833405&img=27&rand='.mt_rand(0,10000).'" alt="Статус ICQ" title="Статус ICQ"/>');
		//$html=str_replace($pattern,$replacement,$html);
		print_r($html);
	}
	
}



class user_info{
	//определение IP адреса
	public static function ip(){ 	
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) 
			$ip=$_SERVER['HTTP_CLIENT_IP'];
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) 
			$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		elseif(!empty($_SERVER['REMOTE_ADDR']))
			$ip=$_SERVER['REMOTE_ADDR'];
		else $ip='127.0.0.1';
		return $ip;
	}
}	

class send{
	
	function mail_header_encode($str,$data_charset,$send_charset){
		if($data_charset!=$send_charset)
			$str=iconv($data_charset,$send_charset,$str);
		return '=?'.$send_charset.'?B?'.base64_encode($str).'?=';
	}
	
	function text($body){
		if(file_exists('temp/eml.html'))
			unlink('temp/eml.html');
		if(file_exists('temp/eml.txt'))
			unlink('temp/eml.txt');
		file_put_contents('temp/eml.html',$body);
		passthru('lynx -dump temp/eml.html > temp/eml.txt');
		return file_get_contents('temp/eml.txt');
	}
	
	//отправка почты (email получателя, имя получателя, email отправителя, имя отправителя, кодировка переданных данных, кодировка письма, тема письма, текст письма)
	public static function email($email_to,$name_to,$email_from,$name_from,$data_charset,$send_charset,$subject,$body){
		$to=$name_to?self::mail_header_encode($name_to,$data_charset,$send_charset).' <'.$email_to.'>':$email_to;
		$subject_e=self::mail_header_encode($subject,$data_charset,$send_charset);
		$from=self::mail_header_encode($name_from,$data_charset,$send_charset).' <'.$email_from.'>';
		$body=str_replace('\n',"\n",$body);
		$body='<!DOCTYPE html><html><head><title>'.$subject.'</title></head>'.$body.'</html>';
		if($data_charset!=$send_charset)
			$body=iconv($data_charset,$send_charset,$body);
		$text=self::text($body)."\r\n\r\nДля просмотра полной версии (HTML) письма воспользуйтесь почтовыми программами.";
		$headers="From: $from\r\n";
		$headers.="Precedence: bulk\r\n";
		$headers.="List-Unsubscribe: http://".$_SERVER['HTTP_HOST']."/unsubscribe/?email=$email_from\r\n";
		$headers.="List-Owner: mailto:$email_from\r\n";
		$headers.="MIME-Version: 1.0\r\n";
		$boundary1=uniqid("part");
		$headers.="Content-Type: multipart/mixed; boundary=\"$boundary1\"";
		$boundary2=uniqid("part");
		$headers.="\r\n\r\n--$boundary1\r\nContent-Type: multipart/alternative; boundary=\"$boundary2\"";
		$headers.="\r\n\r\n--$boundary2\r\nContent-Type: text/plain; charset=\"utf-8\"\r\nContent-Transfer-Encoding: base64\r\n\r\n";
		$headers.=chunk_split(base64_encode($text));
		$headers.="\r\n\r\n--$boundary2\r\nContent-Type: text/html; charset=\"utf-8\"\r\nContent-Transfer-Encoding: base64\r\n\r\n";
		$headers.=chunk_split(base64_encode($body));
		$headers.="\r\n\r\n--$boundary2--";
		$headers.="\r\n\r\n--$boundary1--";
		return mail($to,$subject_e,'',$headers);
	}
	public static function email_text($email_to,$name_to,$email_from,$name_from,$data_charset,$send_charset,$subject,$body,$signature){
		$to=self::mail_header_encode($name_to,$data_charset,$send_charset).' <'.$email_to.'>';
		$subject=self::mail_header_encode($subject,$data_charset,$send_charset);
		$from= self::mail_header_encode($name_from,$data_charset,$send_charset).' <'.$email_from.'>';
		$body.="\n\n-- \n".$signature;
		$body=str_replace('\n',"\n",$body);
		if($data_charset!=$send_charset)
			$body=iconv($data_charset,$send_charset,$body);
		$headers="From: $from\r\n";
		$headers.="Content-type: text/plain; charset=$send_charset\r";
		return mail($to, $subject, $body, $headers);
	}
		
}

class img{
	
	public static function resize($file_input,$file_output,$quality=85,$w_o=200,$h_o=0,$percent=false,$transparent=false){
		list($w_i,$h_i,$type)=getimagesize($file_input);
		if(!$w_i||!$h_i){
			echo 'Невозможно получить длину и ширину изображения';
			return;}
		$types=array('','gif','jpeg','png');
		$ext=$types[$type];
		if($ext){
			$func='imagecreatefrom'.$ext;
			$img_i=$func($file_input);
		}
		else{
			echo 'Некорректный формат файла';
			return;
		}
		if ($percent){
			$w_o=$percent*$w_i/100;
			$h_o=$percent*$h_i/100;
		}
		if(!$h_o)
			$h_o=$w_o/($w_i/$h_i);
		if(!$w_o)
			$w_o=$h_o/($h_i/$w_i);
		$img_o=imagecreatetruecolor($w_o,$h_o);
		//если оставлять прозрачность
		if($transparent){
			imagealphablending($img_o,false);
			imagesavealpha($img_o,true);
		}
		else
			imagefill($img_o,0,0,16777215);
		imagecopyresampled($img_o,$img_i,0,0,0,0,$w_o,$h_o,$w_i,$h_i);
		
		$exts=array('jpg','png','gif');
		$ext=pathinfo(strtolower($file_output),PATHINFO_EXTENSION);
		if (!in_array($ext,$exts)){
			echo 'Неверное расширение файла с результатом.';
			return;
		}
		if($ext=='jpg')
			return imagejpeg($img_o,$file_output,$quality);
		else{
			$func='image'.$ext;
			return $func($img_o,$file_output);
		}
		imagedestroy($img_o);
		imagedestroy($img_i);
	}
	
	//обрезка фоток
	public static function crop($file_input,$file_output,$quality,$crop='square',$percent=false,$transparent=false){
		list($w_i,$h_i,$type)=getimagesize($file_input);
		if (!$w_i||!$h_i){
			echo 'Невозможно получить длину и ширину изображения';
			return;
		}
		$types=array('','gif','jpeg','png');
		$ext=$types[$type];
		if ($ext){
			$func='imagecreatefrom'.$ext;
			$img_i=$func($file_input);} 
		else {
			echo 'Некорректный формат файла';
			return;
		}
		if ($crop=='square'){
			$min=$w_i;
			if ($w_i>$h_i)$min=$h_i;
			$w_o=$h_o=$min;}
		else {
			list($x_o,$y_o,$w_o,$h_o)=$crop;
			if ($percent){
				$w_o*=$w_i/100;
				$h_o*=$h_i/100;
				$x_o*=$w_i/100;
				$y_o*=$h_i/100;}
			if ($w_o<0){
				$w_o+=$w_i;
				$w_o-=$x_o;}
			if ($h_o<0){
				$h_o+=$h_i;
				$h_o-=$y_o;}}
		$img_o=imagecreatetruecolor($w_o,$h_o);
		//если оставлять прозрачность
		if($transparent){
			imagealphablending($img_o,false);
			imagesavealpha($img_o,true);
		}
		else
			imagefill($img_o,0,0,16777215);
		imagecopy($img_o,$img_i,0,0,$x_o,$y_o,$w_o,$h_o);
		
		$exts=array('jpg','png','gif');
		$ext=explode('.',strtolower($file_output));
		$ext=end($ext); 
		if (!in_array($ext,$exts)){
			echo 'Неверное расширение файла с результатом.';
			return;
		}
		if($ext=='jpg')
			return imagejpeg($img_o,$file_output,$quality);
		else{
			$func='image'.$ext;
			return $func($img_o,$file_output);}
		imagedestroy($img_o);
		imagedestroy($img_i);
	}
	
	//прозрачность
	public static function alfa($filename){
		$img=imagecreatefromstring(file_get_contents($filename));
		imagealphablending($img,false);
		imagesavealpha($img,true);
		$width=imagesx($img);
		$height=imagesy($img);
		//$result=imagepng($img);
		//imagedestroy($img);
		return $img;
	}
	
	//поворот фоток
	public static function rotate($file,$degree,$qality=100,$transparent=false,$display=false){
		list($w_i,$h_i,$type)=getimagesize($file);
		if (!$w_i||!$h_i){
			echo 'Невозможно получить длину и ширину изображения';
			return;
		}
		$types=array('','gif','jpeg','png');
		$ext=$types[$type];
		if($ext){
			$func='imagecreatefrom'.$ext;
			$img=$func($file);} 
		else{
			echo 'Некорректный формат файла';
			return;
		}
		$img_o=imagecreatetruecolor($w_i,$h_i);
		//если оставлять прозрачность
		if($transparent){
			imagealphablending($img_o,false);
			imagesavealpha($img_o,true);
		}
		else
			imagefill($img_o,0,0,16777215);
		$img_i=imagecreatefromjpeg($file);
		imagecopy($img_o,$img_i,0,0,0,0,$w_i,$h_i);
		$img_o=imagerotate($img_o,$degree,0);
		if($type==2){
			imagejpeg($img_o,$file,$qality);
			if($display)
				imagejpeg($img_o,null,$qality);
		}
		else{
			$func='image'.$ext;
			$func($img_o,$file);
			if($display)
				$func($img_o);
		}
		imagedestroy($img_o);
		imagedestroy($img_i);
	}
		
	//скругленные углы с прозрачностью
	public static function round_crop($filename,$radius,$rate){
		$img=imagecreatefromstring(file_get_contents($filename));
		imagealphablending($img,false);
		imagesavealpha($img,true);
		$width=imagesx($img);
		$height=imagesy($img);
		$rs_radius=$radius*$rate;
		$rs_size=$rs_radius*2;
		$corner=imagecreatetruecolor($rs_size,$rs_size);
		imagealphablending($corner,false);
		$trans=imagecolorallocatealpha($corner,255,255,255,127);
		imagefill($corner,0,0,$trans);
		$positions=array(
			array(0,0,0,0),
			array($rs_radius,0,$width-$radius,0),
			array($rs_radius,$rs_radius,$width-$radius,$height-$radius),
			array(0,$rs_radius,0,$height-$radius),
		);
		foreach($positions as $pos)
			imagecopyresampled($corner,$img,$pos[0],$pos[1],$pos[2],$pos[3],$rs_radius,$rs_radius,$radius,$radius);
		$lx=$ly=0;
		$i=-$rs_radius;
		$y2=-$i;
		$r_2=$rs_radius * $rs_radius;
		for (;$i<=$y2;$i++){
			$y=$i;
			$x=sqrt($r_2-$y*$y);
			$y+=$rs_radius;
			$x+=$rs_radius;
			imageline($corner,$x,$y,$rs_size,$y,$trans);
			imageline($corner,0,$y,$rs_size-$x,$y,$trans);
			$lx=$x;
			$ly=$y;
		}
		foreach($positions as $i=>$pos)
			imagecopyresampled($img,$corner,$pos[2],$pos[3],$pos[0],$pos[1],$radius,$radius,$rs_radius,$rs_radius);
		$result=imagepng($img);
		imagedestroy($img);
		return $result;
	}
	
	//удаление фона с фотографий с печатями и подписями
	public static function stamp($file_input,$file_output,$border=2,$autodetect=true){
		$border_array=array(1=>1000000,2=>4000000,3=>6000000);
		$border=$border_array[$border];
		$img=imagecreatefromjpeg($file_input);
		//переводим в градации серого
		imagefilter($img,IMG_FILTER_GRAYSCALE);
		//выделяем границы
		imagefilter($img,IMG_FILTER_EDGEDETECT);
		//уменьшаем контраст
		imagefilter($img,IMG_FILTER_CONTRAST,-40);
		//получаем ширину и высоту
		$w=imagesx($img);
		$h=imagesy($img);
		//создаем массив с точками и их цветами
		$colors=array();
		for($x=0;$x<$w;$x++) {
			$colors[$x]=array();
			for($y=0;$y<$h;$y++)
				$colors[$x][$y]=imagecolorat($img,$x,$y);
		}
		//разделяем цвета на синий и белый, используя border
		$blue=511;
		for($x=0;$x<$w;$x++)
			for($y=0;$y<$h;$y++)
				$colors[$x][$y]=($colors[$x][$y]>$border)?16777215:$blue;
		//с автораспознаванием - убрать фон и обрезать до границ оттиска
		if($autodetect){
			//ищем точные координаты оттиска
			$X=$Y=array(); 
			for($x=0;$x<$w;$x++)
				for($y=0;$y<$h;$y++)
					if($colors[$x][$y]==$blue){
						$X[1]=$x;
						$Y[1]=$y;
						break(2);
						break(1);
					}
			for($y=0;$y<$h;$y++)
				for($x=0;$x<$w;$x++)
					if($colors[$x][$y]==$blue){
						$X[2]=$x;
						$Y[2]=$y;
						break(2);
						break(1);
					}
			for($x=$w-1;$x>=0;$x--)
				for($y=$h-1;$y>=0;$y--)
					if($colors[$x][$y]==$blue){
						$X[3]=$x;
						$Y[3]=$y;
						break(2);
						break(1);
					}
			for($y=$h-1;$y>=0;$y--)
				for($x=$w-1;$x>=0;$x--)
					if($colors[$x][$y]==$blue){
						$X[4]=$x;
						$Y[4]=$y;
						break(2);
						break(1);
					}
			//создаем изображение			
			$res=imagecreatetruecolor($X[3]-$X[1]+1,$Y[4]-$Y[2]+1);
			//включаем прозрачность
			imagealphablending($res,false);
			imagesavealpha($res,true);
			$trans=imagecolorallocatealpha($res,255,255,255,127);
			//копируем полученное изображение в новое, заменяя белый цвет прозрачным
			for($x=$X[1],$i=0;$x<=$X[3],$i<=$X[3]-$X[1];$x++,$i++)
				for($y=$Y[2],$j=0;$y<=$Y[4],$j<=$Y[4]-$Y[2];$y++,$j++)
					($colors[$x][$y]==$blue)?imagesetpixel($res,$i,$j,$colors[$x][$y]):imagesetpixel($res,$i,$j,$trans);
		}
		//без автораспознавания - только убрать фон, размеры оставить
		else{
			//создаем новое изображение
			$res=imagecreatetruecolor($w,$h);
			//включаем прозрачность
			imagealphablending($res,false);
			imagesavealpha($res,true);
			$trans=imagecolorallocatealpha($res,255,255,255,127);
			//копируем полученное изображение в новое, заменяя белый цвет прозрачным
			for($x=0;$x<$w;$x++)
				for($y=0;$y<$h;$y++)
					($colors[$x][$y]==$blue)?imagesetpixel($res,$x,$y,$colors[$x][$y]):imagesetpixel($res,$x,$y,$trans);
		}
		//выводим результат
		imagepng($res,$file_output);
		imagedestroy($res);
	}

}

function get_filesize($f_name){
	$f_size=filesize($f_name);
	$f_size=($f_size)?$f_size:0;
	if ($f_size>1048576)
		$f_size=round($f_size/1048576,1).' МБ';
	else
		$f_size=round($f_size/1024,1).' КБ';
	return $f_size;
}

class file{
	//удаление директории со всем содержимым	
	public static function deldir($dir){
		if (file_exists($dir)){
			if (stristr($dir,'/..')||stristr($dir,'/*')||$dir=='') return false;
			if ($objs=glob($dir.'/*')) 
				foreach($objs as $obj)
					is_dir($obj)?file::deldir($obj):unlink($obj);
			rmdir($dir);
		}
	}
	
	public static function findfiles($dir,&$res,&$type=''){
			if (file_exists($dir)){
				if (stristr($dir,'/..')||stristr($dir,'/*')||$dir=='') return false;
				if ($objs=glob($dir.'/*')) 
					foreach($objs as $obj)
						is_dir($obj)?findfiles($obj,$res,$type):($type?(stristr(basename($obj),$type)?$res[]=basename($obj):null):$res[]=basename($obj));
			}
		}
}

$area_list=array(
	'www'=>array(
		'ГОРОД'=>'Москва',
		'города'=>'Москвы',
		'городу'=>'Москве',
		'город'=>'Москву',
		'городом'=>'Москвой',
		'городе'=>'Москве',
		'РЕГИОН'=>'Московская область',
		'региона'=>'Московской области',
		'региону'=>'Московской области',
		'регион'=>'Московскую область',
		'регионом'=>'Московской областью',
		'регионе'=>'Московской области',
		'url'=>'www',
		'mail'=>'msk@Formetoo.ru',
		'map'=>'<script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=frkczrnh5gRTnzFiF9dTTbSk4LdP-rSC"></script>',
		'address'=>'МО, Одинцовский район, село Немчиновка, Советский проспект, д.&nbsp;104',
		'tel_office'=>'+7 495 798-11-65',
		'tel_mob'=>'',
		'TEL'=>'8 800 200-04-70'),
	'spb'=>array(
		'ГОРОД'=>'Санкт-Петербург',
		'города'=>'Санкт-Петербурга',
		'городу'=>'Санкт-Петербургу',
		'город'=>'Санкт-Петербург',
		'городом'=>'Санкт-Петербургом',
		'городе'=>'Санкт-Петербурге',
		'РЕГИОН'=>'Лениградская область',
		'региона'=>'Лениградской области',
		'региону'=>'Лениградской области',
		'регион'=>'Лениградскую область',
		'регионом'=>'Лениградской областью',
		'регионе'=>'Лениградской области',
		'url'=>'spb',
		'mail'=>'spb@Formetoo.ru',
		//'address'=>'г.&nbsp;Санкт-Петербург, просп.&nbsp;Авиаконструкторов, д.&nbsp;20, корп.&nbsp;1',
		//'map'=>'<script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=KBl9MKxcsD834XvI5eA-gk8PI4plOw_s"></script>',
		'address'=>'',
		'map'=>'',
		'tel_office'=>'+7 812 985-97-42',
		'tel_mob'=>'',
		'TEL'=>'8 800 200-04-70'),
	'vladimir'=>array(
		'ГОРОД'=>'Владимир',
		'города'=>'Владимира',
		'городу'=>'Владимиру',
		'город'=>'Владимир',
		'городом'=>'Владимиром',
		'городе'=>'Владимире',
		'РЕГИОН'=>'Владимирская область',
		'региона'=>'Владимирской области',
		'региону'=>'Владимирской области',
		'регион'=>'Владимирскую область',
		'регионом'=>'Владимирской областью',
		'регионе'=>'Владимирской области',
		'url'=>'vladimir',
		'mail'=>'vladimir@Formetoo.ru',
		//'address'=>'г.&nbsp;Владимир, ул.&nbsp;Сакко и Ванцетти, д.&nbsp;23',
		//'map'=>'<script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=-Zi8YiR2PDGSVNUtb4vO1pFEOu_T1CCD"></script>',
		'address'=>'',
		'map'=>'',
		'tel_office'=>'',
		'tel_mob'=>'+7 960 721-53-30',
		'TEL'=>'8 800 200-04-70'),
	'vologda'=>array(
		'ГОРОД'=>'Вологда',
		'города'=>'Вологды',
		'городу'=>'Вологде',
		'город'=>'Вологду',
		'городом'=>'Вологдой',
		'городе'=>'Вологде',
		'РЕГИОН'=>'Вологодская область',
		'региона'=>'Вологодской области',
		'региону'=>'Вологодской области',
		'регион'=>'Вологодскую область',
		'регионом'=>'Вологодской областью',
		'регионе'=>'Вологодской области',
		'url'=>'vologda',
		'mail'=>'vologda@Formetoo.ru',
		//'address'=>'Вологодская&nbsp;обл., г.&nbsp;Череповец, ул.&nbsp;Ленина, д.&nbsp;90',
		//'map'=>'<script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=hj0JFGeg9Kuo_vdhpb8oIJywFYKpWgxz"></script>',
		'address'=>'',
		'map'=>'',
		'tel_office'=>'+7 8202 73-53-63',
		'tel_mob'=>'',
		'TEL'=>'8 800 200-04-70'),
	'voronezh'=>array(
		'ГОРОД'=>'Воронеж',
		'города'=>'Воронежа',
		'городу'=>'Воронежу',
		'город'=>'Воронеж',
		'городом'=>'Воронежем',
		'городе'=>'Воронеже',
		'РЕГИОН'=>'Воронежская область',
		'региона'=>'Воронежской области',
		'региону'=>'Воронежской области',
		'регион'=>'Воронежскую область',
		'регионом'=>'Воронежской областью',
		'регионе'=>'Воронежской области',
		'url'=>'voronezh',
		'mail'=>'voronezh@Formetoo.ru',
		//'address'=>'г.&nbsp;Воронеж, ул.&nbsp;Беговая, д.&nbsp;138А',
		//'map'=>'<script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=aCmqyrrUoCJEnWCUGJrMGTUpRL3eVQTi"></script>',
		'address'=>'',
		'map'=>'',
		'tel_office'=>'+7 4732 28-97-44',
		'tel_mob'=>'',
		'TEL'=>'8 800 200-04-70'),
	'ivanovo'=>array(
		'ГОРОД'=>'Иваново',
		'города'=>'города Иванова',
		'городу'=>'городу Иванову',
		'город'=>'Иваново',
		'городом'=>'Ивановом',
		'городе'=>'Иванове',
		'РЕГИОН'=>'Ивановская область',
		'региона'=>'Ивановской области',
		'региону'=>'Ивановской области',
		'регион'=>'Ивановскую область',
		'регионом'=>'Ивановской областью',
		'регионе'=>'Ивановской области',
		'url'=>'ivanovo',
		'mail'=>'ivanovo@Formetoo.ru',
		'address'=>'г.&nbsp;Иваново, ул.&nbsp;Кузнечная, д.&nbsp;38 <span class="comment">(вход со стороны ул. Почтовой)</span>',
		'map'=>'<script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=jlg2fcYxsRmvXWdFvmSUUeI__kM2xFyH&id=ymap"></script>',
		'tel_office'=>'+7 4932 92-98-67',
		'tel_mob'=>'+7 961 247-55-08',
		'TEL'=>'8 800 200-04-70'),
	'kaluga'=>array(
		'ГОРОД'=>'Калуга',
		'города'=>'Калуги',
		'городу'=>'Калуге',
		'город'=>'Калугу',
		'городом'=>'Калугой',
		'городе'=>'Калуге',
		'РЕГИОН'=>'Калужская область',
		'региона'=>'Калужской области',
		'региону'=>'Калужской области',
		'регион'=>'Калужскую область',
		'регионом'=>'Калужской областью',
		'регионе'=>'Калужской области',
		'url'=>'kaluga',
		'mail'=>'kaluga@Formetoo.ru',
		//'address'=>'г.&nbsp;Калуга, ул.&nbsp;Тарутинская, д.&nbsp;2А',
		//'map'=>'<script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=Pu7z6un2Y5K2Gmll6Hw_rc6Qpxlx399X"></script>',
		'address'=>'',
		'map'=>'',
		'tel_office'=>'+7 4842 59-58-92',
		'tel_mob'=>'',
		'TEL'=>'8 800 200-04-70'),
	'kazan'=>array(
		'ГОРОД'=>'Казань',
		'города'=>'Казани',
		'городу'=>'Казани',
		'город'=>'Казань',
		'городом'=>'Казанью',
		'городе'=>'Казани',
		'РЕГИОН'=>'Республика Татарстан',
		'региона'=>'Республики Татарстан',
		'региону'=>'Республике Татарстан',
		'регион'=>'Республику Татарстан',
		'регионом'=>'Республикой Татарстан',
		'регионе'=>'Республике Татарстан',
		'url'=>'kazan',
		'mail'=>'kazan@Formetoo.ru',
		//'address'=>'г.&nbsp;Казань, мкр.&nbsp;Горки-1, ул.&nbsp;Сыртлановой, д.&nbsp;16',
		//'map'=>'<script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=Jkc7sJFJvJn_jBt9xPTbbpaOCw0tC_n-"></script>',
		'address'=>'',
		'map'=>'',
		'tel_office'=>'+7 843 259-51-18',
		'tel_mob'=>'',
		'TEL'=>'8 800 200-04-70'),
	'kostroma'=>array(
		'ГОРОД'=>'Кострома',
		'города'=>'Костромы',
		'городу'=>'Костроме',
		'город'=>'Кострому',
		'городом'=>'Костромой',
		'городе'=>'Костроме',
		'РЕГИОН'=>'Костромская область',
		'региона'=>'Костромской области',
		'региону'=>'Костромской области',
		'регион'=>'Костромскую область',
		'регионом'=>'Костромской областью',
		'регионе'=>'Костромской области',
		'url'=>'kostroma',
		'mail'=>'kostroma@Formetoo.ru',
		//'address'=>'г.&nbsp;Кострома, ул.&nbsp;Северной Правды, д.&nbsp;41А',
		//'map'=>'<script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=2wcb6nEFX1312KSztfvSCSJmmIRdPymM"></script>',
		'address'=>'',
		'map'=>'',
		'tel_office'=>'+7 4942 30-22-58',
		'tel_mob'=>'',
		'TEL'=>'8 800 200-04-70'),
	'lipetsk'=>array(
		'ГОРОД'=>'Липецк',
		'города'=>'Липецка',
		'городу'=>'Липецку',
		'город'=>'Липецк',
		'городом'=>'Липецком',
		'городе'=>'Липецке',
		'РЕГИОН'=>'Липецкая область',
		'региона'=>'Липецкой области',
		'региону'=>'Липецкой области',
		'регион'=>'Липецкую область',
		'регионом'=>'Липецкой областью',
		'регионе'=>'Липецкой области',
		'url'=>'lipetsk',
		'mail'=>'lipetsk@Formetoo.ru',
		//'address'=>'г.&nbsp;Липецк, ул.&nbsp;Московская, д.&nbsp;117',
		//'map'=>'<script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=d_t9nKsZYvJEpm_KrOTrpp6_wejuyw3X"></script>',
		'address'=>'',
		'map'=>'',
		'tel_office'=>'+7 4742 37-52-94',
		'tel_mob'=>'',
		'TEL'=>'8 800 200-04-70'),
	'nn'=>array(
		'ГОРОД'=>'Нижний Новгород',
		'города'=>'Нижнего Новгорода',
		'городу'=>'Нижнему Новгороду',
		'город'=>'Нижний Новгород',
		'городом'=>'Нижним Новгородом',
		'городе'=>'Нижнем Новгороде',
		'РЕГИОН'=>'Нижегородская область',
		'региона'=>'Нижегородской области',
		'региону'=>'Нижегородской области',
		'регион'=>'Нижегородскую область',
		'регионом'=>'Нижегородской областью',
		'регионе'=>'Нижегородской области',
		'url'=>'nn',
		'mail'=>'nn@Formetoo.ru',
		//'address'=>'г.&nbsp;Нижний Новгород, ул.&nbsp;Космическая, д.&nbsp;42',
		//'map'=>'<script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=e6Ut02lzuL4X0E-FFFTBhMPmWFDez_uJ"></script>',
		'address'=>'',
		'map'=>'',
		'tel_office'=>'+7 831 213-57-46',
		'tel_mob'=>'',
		'TEL'=>'8 800 200-04-70'),
	'ryazan'=>array(
		'ГОРОД'=>'Рязань',
		'города'=>'Рязани',
		'городу'=>'Рязани',
		'город'=>'Рязань',
		'городом'=>'Рязанью',
		'городе'=>'Рязани',
		'РЕГИОН'=>'Рязанская область',
		'региона'=>'Рязанской области',
		'региону'=>'Рязанской области',
		'регион'=>'Рязанскую область',
		'регионом'=>'Рязанской областью',
		'регионе'=>'Рязанской области',
		'url'=>'ryazan',
		'mail'=>'ryazan@Formetoo.ru',
		'address'=>'г.&nbsp;Рязань, Заводской проезд, д.&nbsp;1, оф.&nbsp;28',
		'map'=>'<script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=d_1_621cirDmaC8HiI5XwmblEkjYzz82&lang=ru_RU&sourceType=constructor"></script>',
		'tel_office'=>'+7 4912 51-29-19',
		'tel_mob'=>'+7 900 907-93-05',
		'TEL'=>'8 800 200-04-70'),
	'tula'=>array(
		'ГОРОД'=>'Тула',
		'города'=>'Тулы',
		'городу'=>'Туле',
		'город'=>'Тула',
		'городом'=>'Тулой',
		'городе'=>'Туле',
		'РЕГИОН'=>'Тульская область',
		'региона'=>'Тульской области',
		'региону'=>'Тульской области',
		'регион'=>'Тульскую область',
		'регионом'=>'Тульской областью',
		'регионе'=>'Тульской области',
		'url'=>'tula',
		'mail'=>'tula@Formetoo.ru',
		'address'=>'г.&nbsp;Тула, ул.&nbsp;Чмутова, д.&nbsp;158В',
		//'map'=>'<script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=nq8UPZ_3fLz4oAHC6KLFDcKte26gII5Y"></script>',
		'map'=>'',
		'tel_office'=>'+7 4872 71-64-87',
		'tel_mob'=>'',
		'TEL'=>'8 800 200-04-70'),
	'tver'=>array(
		'ГОРОД'=>'Тверь',
		'города'=>'Твери',
		'городу'=>'Твери',
		'город'=>'Тверь',
		'городом'=>'Тверью',
		'городе'=>'Твери',
		'городе'=>'Твери',
		'РЕГИОН'=>'Тверская область',
		'региона'=>'Тверской области',
		'региону'=>'Тверской области',
		'регион'=>'Тверскую область',
		'регионом'=>'Тверской областью',
		'регионе'=>'Тверской области',
		'url'=>'tver',
		'mail'=>'tver@Formetoo.ru',
		//'address'=>'г.&nbsp;Тверь, ул.&nbsp;Можайского, д.&nbsp;78',
		//'map'=>'<script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=DdyWVRPAELkADHyC3XLullO6GKxqirG8"></script>',
		'address'=>'',
		'map'=>'',
		'tel_office'=>'+7 4822 69-16-53',
		'tel_mob'=>'',
		'TEL'=>'8 800 200-04-70'),
	'yaroslavl'=>array(
		'ГОРОД'=>'Ярославль',
		'города'=>'Ярославля',
		'городу'=>'Ярославлю',
		'город'=>'Ярославль',
		'городом'=>'Ярославлем',
		'городе'=>'Ярославле',
		'РЕГИОН'=>'Ярославская область',
		'региона'=>'Ярославской области',
		'региону'=>'Ярославской области',
		'регион'=>'Ярославскую область',
		'регионом'=>'Ярославской областью',
		'регионе'=>'Ярославской области',
		'url'=>'yaroslavl',
		'mail'=>'yaroslavl@Formetoo.ru',
		//'address'=>'г.&nbsp;Ярославль, ул.&nbsp;Комсомольская, д.&nbsp;14',
		//'map'=>'<script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=JsqJDN8sBTYT1ceukUao6EHBUFLPwsqL"></script>',
		'address'=>'',
		'map'=>'',
		'tel_office'=>'+7 4852 66-34-77',
		'tel_mob'=>'',
		'TEL'=>'8 800 200-04-70'),
/*	'belgorod'=>array(
		'ГОРОД'=>'Белгород',
		'города'=>'Белгорода',
		'городу'=>'Белгороду',
		'город'=>'Белгород',
		'городом'=>'Белгородом',
		'городе'=>'Белгороде',
		'РЕГИОН'=>'Белгородская область',
		'региона'=>'Белгородской области',
		'региону'=>'Белгородской области',
		'регион'=>'Белгородскую область',
		'регионом'=>'Белгородской областью',
		'регионе'=>'Белгородской области',
		'url'=>'belgorod',
		'mail'=>'belgorod@Formetoo.ru',
		'tel_mob'=>'<span>+7 4932</span> 92-99-66'),
	'samara'=>array(
		'ГОРОД'=>'Самара',
		'города'=>'Самары',
		'городу'=>'Самаре',
		'город'=>'Самару',
		'городом'=>'Самарой',
		'городе'=>'Самаре',
		'РЕГИОН'=>'Самарская область',
		'региона'=>'Самарской области',
		'региону'=>'Самарской области',
		'регион'=>'Самарскую область',
		'регионом'=>'Самарской областью',
		'регионе'=>'Самарской области',
		'url'=>'samara',
		'mail'=>'samara@Formetoo.ru',
		'tel_mob'=>'<span>+7 4932</span> 92-99-66'),
	'saratov'=>array(
		'ГОРОД'=>'Саратов',
		'города'=>'Саратова',
		'городу'=>'Саратову',
		'город'=>'Саратов',
		'городом'=>'Саратовом',
		'городе'=>'Саратове',
		'РЕГИОН'=>'Саратовская область',
		'региона'=>'Саратовской области',
		'региону'=>'Саратовской области',
		'регион'=>'Саратовскую область',
		'регионом'=>'Саратовской областью',
		'регионе'=>'Саратовской области',
		'url'=>'saratov',
		'mail'=>'saratov@Formetoo.ru',
		'tel_mob'=>'<span>+7 4932</span> 92-99-66'),
	'cheboksary'=>array(
		'ГОРОД'=>'Чебоксары',
		'города'=>'Чебоксар',
		'городу'=>'Чебоксарам',
		'город'=>'Чебоксары',
		'городом'=>'Чебоксарами',
		'городе'=>'Чебоксарах',
		'РЕГИОН'=>'Чувашская Республика',
		'региона'=>'Чувашской Республики',
		'региону'=>'Чувашской Республике',
		'регион'=>'Чувашскую Республику',
		'регионом'=>'Чувашской Республикой',
		'регионе'=>'Чувашской Республике',
		'url'=>'cheboksary',
		'mail'=>'cheboksary@Formetoo.ru',
		'tel_mob'=>'<span>+7 4932</span> 92-99-66'), */
);

?>