<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
$sql=new sql();
require_once('../../functions/classes/user.php');
$user=new user;
require_once('../../functions/classes/site.php');
$site=new site;

$data['name']=array(1,null,50);
$data['pk']=array(1,1,10,null,1);
$data['value']=array(1);

array_walk($data,'check');

if(!$e){
	$q='';
	switch($data['name']){
//ПУНКТЫ МЕНЮ
		//изменение значения показа
		case 'site_menu_active':
			$data['value']=$data['value']=='true'?1:0;
			$q='UPDATE `formetoo_main`.`menu` SET
				`active`='.$data['value'].'
				WHERE `id`='.$data['pk'].' LIMIT 1;';
			if($sql->query($q))
				echo 'true';
			break;
		//удаление
		case 'id':
			if($data['value']=='fmt'){
				$q='DELETE FROM `formetoo_main`.`menu`,`formetoo_main`.`content` USING `formetoo_main`.`menu` INNER JOIN `formetoo_main`.`content` ON `formetoo_main`.`menu`.`id`=`formetoo_main`.`content`.`menu` WHERE `menu`.`id`=\''.$data['pk'].'\';';
				if($sql->query($q))
					echo 'true';
			}
			else{
				header('HTTP 400 Bad Request',true,400);
				echo "неверный пароль";
			}
			break;
		//изменение порядковых номеров и структуры страниц
		case 'list':
			//print_r(json_decode(html_entity_decode($data['value'],ENT_COMPAT,'utf-8'),1));
			function categories($categories,$parent=0){
				global $site,$sql;
				foreach($categories as $k=>$v){
					//в БД порядковый номер начинается с 1
					$k++;
					//выход id за пределы INT
					$v['id'].='';
					$q='UPDATE `formetoo_main`.`menu` SET ';
					//если порядковй номер текущего пункта обновился
					$q.= is_bool($v['active']) ? '`active`='.(int)filter_var($v['active'], FILTER_VALIDATE_BOOLEAN) : '';
					//если порядковй номер текущего пункта обновился
					if($site->menu_id[$v['id']][0]['order']!=$k)
						$q.='`order`='.$k;
					//если родительский пункт обновился
					if($site->menu_id[$v['id']][0]['parent']!=$parent)
						$q.=($site->menu_id[$v['id']][0]['order']!=$k?',':'').'`parent`='.$parent;
					$q.=' WHERE `id`='.$v['id'].' LIMIT 1;';
					//если есть изменения - выполняем запрос
					if(strpos($q,'order')!==false || strpos($q,'active')!==false) {
						$sql->query($q);
					}
					//если у категории есть дочерние категории - рекурсивно проверяем их
					if(isset($v['children']))
						categories($v['children'],$v['id']);
				}
				$q='';
			}
			categories(json_decode(html_entity_decode($data['value'],ENT_COMPAT,'utf-8'),1));
			break;

		default:
			break;
	}
}
else{
	header('HTTP 400 Bad Request',true,400);
	echo "ошибка",implode(' ',$e);
}
?>
