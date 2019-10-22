<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
require_once('../../functions/classes/info.php');
$sql=new sql();
$info=new info();

$q='SELECT * FROM `formetoo_cdb`.`m_info_units`;';
$units_id=$sql->query($q,'m_info_units_id');

//список категорий
$q='SELECT * FROM `formetoo_main`.`m_products_categories`;';
$categories=$sql->query($q,'m_products_categories_id');

$w=trim(get('search',array('\\','%','_','\''),array('\\\\','\%','\_','\\\'')));
$w=explode(' ',$w);

$limit=get('limit')?get('limit'):20;
$page=get('page')?get('page'):1;
$start=$limit*$page-$limit;
$category=get('category');

$ql='SELECT COUNT(`m_products_id`) FROM `formetoo_main`.`m_products` WHERE (`m_products_id` LIKE \'%'.implode(' ',$w).'%\' OR `m_products_name` LIKE \'%'.implode(' ',$w).'%\') '.($category?'AND `m_products_categories_id` LIKE \'%'.$category.'%\'':'').';';

$q='SELECT `m_products_id`,
			`m_products_categories_id`,
			`m_products_name`,
			`m_products_unit`,
			`m_products_price_general`,
			`m_products_foto`,
			`m_products_show_site`,
			`m_products_order`,
			`m_products_update`
			FROM `formetoo_main`.`m_products`
			WHERE
			(`m_products_id` LIKE \'%'.implode(' ',$w).'%\' OR
			`m_products_name` LIKE \'%'.implode(' ',$w).'%\')
			'.($category?'AND `m_products_categories_id` LIKE \'%'.$category.'%\'':'').'
			ORDER BY `m_products_date` DESC,`m_products_name`,`m_products_order`
			LIMIT '.$start.','.$limit.';';
	
if($res=$sql->query($q))
	$p=$res;
else{
	$like=[];
	foreach($w as $_w){
		if (mb_strlen($_w,'utf-8')<3) continue;
		$like[]='`m_products_name` LIKE \'%'.$_w.'%\'';
	}
	$like=implode(' AND ',$like);
	$q='SELECT `m_products_id`,
			`m_products_categories_id`,
			`m_products_name`,
			`m_products_unit`,
			`m_products_price_general`,
			`m_products_foto`,
			`m_products_show_site`,
			`m_products_order`,
			`m_products_update`
			FROM `formetoo_main`.`m_products`
			WHERE (
			'.($like?$like.' OR':'').'
			`m_products_id` LIKE \'%'.implode(' ',$w).'%\')
			'.($category?'AND `m_products_categories_id` LIKE \'%'.$category.'%\'':'').' 
			ORDER BY `m_products_date` DESC, `m_products_name`,`m_products_order`
			LIMIT '.$start.','.$limit.';';
	$ql='SELECT COUNT(`m_products_id`) FROM `formetoo_main`.`m_products` WHERE (`m_products_id` LIKE \'%'.implode(' ',$w).'%\''.($like?' OR '.$like:'').') '.($category?'AND `m_products_categories_id` LIKE \'%'.$category.'%\'':'').' ;';
	if($res=$sql->query($q))
		$p=$res;
}

if($p){
	$res=$sql->query($ql);
	echo '<tr style="display:none" count="'.$res[0]['COUNT(`m_products_id`)'].'" page="'.$page.'"></tr>';
	$i=0;
	$k=0;

	foreach($p as $products_){
		$m_products_categories_id=array();
		$products_['m_products_categories_id']=explode('|',$products_['m_products_categories_id']);
		if($products_['m_products_categories_id'][0])
			foreach($products_['m_products_categories_id'] as $t_)
				$m_products_categories_id[]=$categories[$t_][0]['m_products_categories_name'];
		
	/* 	$m_products_links=array();
		$products_['m_products_links']=explode('|',$products_['m_products_links']);
		if($products_['m_products_links'][0])
			foreach($products_['m_products_links'] as $t_)
				$m_products_links[]=$products->products_id[$t_][0]['m_products_name']; */
		
		echo '<tr>
			<td class="check" data-foto="'.($products_['m_products_foto']&&is_object(json_decode($products_['m_products_foto']))?current(get_object_vars(json_decode($products_['m_products_foto'])))->file:'').'">
				<label class="checkbox">
					<input type="checkbox" class="checkbox tr m_products_id" value="'.$products_['m_products_id'].'">
					<i></i>
				</label>
			</td>
			<td>
				'.$products_['m_products_id'].'
			</td>
			<td>',
				$products_['m_products_name'],
			'</td>
			<td>',
				$units_id[$products_['m_products_unit']][0]['m_info_units_name'],
			'</td>
			<td>',
					$products_['m_products_price_general'],
				'
			</td>
			<td>',
				implode('<br/>',$m_products_categories_id),
			'</td>
			<td>',
                $products_['m_products_update'],
			'</td>
			<td>
				<label class="checkbox">
				  <input type="checkbox" class="checkbox style-0 show" data-name="m_products_show_site" '.($products_['m_products_show_site']==1?'checked':'').' data-pk="'.$products_['m_products_id'].'">
				  <span>На сайте</span>
				</label>
				
			</td>
			<td>
				<a href="#" class="m_products_order" data-type="text" data-pk="'.$products_['m_products_id'].'" data-name="m_products_order" data-title="Порядковый номер">',
					$products_['m_products_order'],
				'</a>
			</td>
			<td>
				<div class="btn-group btn-labeled multirow-btn" title="Поставить в очередь обновлений">
					<a class="btn btn-xs btn-info refresh" href="javascript:void(0);" data-name="m_products_check_it" data-pk="'.$products_['m_products_id'].'" ><i class="glyphicon glyphicon-refresh"></i></a>
					<a class="btn btn-xs btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<li>
							<a href="javascript:void(0);" class="refresh" data-name="m_products_check_it" data-pk="'.$products_['m_products_id'].'" >Обновить цену</a>
						</li>
						<li>
							<a href="javascript:void(0);" class="refresh-text" data-name="m_products_check_it_no_foto" data-pk="'.$products_['m_products_id'].'" >Обновить цену и тексты</a>
						</li>
						<li>
							<a href="javascript:void(0);" class="refresh-foto" data-name="m_products_check_it_all" data-pk="'.$products_['m_products_id'].'" >Обновить цену и фото/доки</a>
						</li>
						<li>
							<a href="javascript:void(0);" class="refresh-none" data-name="m_products_check_it_none" data-pk="'.$products_['m_products_id'].'" >Снять с обновления</a>
						</li>
					</ul>
				</div>
                
                
                			<a title="Сделать копию товара" class="btn btn-success btn-xs copy_product" href="/copyProduct.php?id='.$products_['m_products_id'].'"><i class="fa fa-copy"></i></a>
                            
                            &nbsp;
				<a href="javascript:void(0);" title="Изменить позицию (выше)" class="btn btn-xs btn-default changepos" data-value="'.$products_['m_products_order'].'" data-type="text" data-pk="'.$products_['m_products_id'].'" data-name="m_products_order_up" data-placement="left">
					<i class="fa fa-angle-up"></i>
				</a>
                

				<a href="javascript:void(0);" title="Изменить позицию (ниже)" class="btn btn-xs btn-default changepos" data-value="'.$products_['m_products_order'].'" data-type="text" data-pk="'.$products_['m_products_id'].'" data-name="m_products_order_down" data-placement="left">
					<i class="fa fa-angle-down"></i>
				</a>&nbsp;&nbsp;
				<a href="/companies/products/new/?action=change&m_products_id='.$products_['m_products_id'].'" title="Редактировать" class="btn btn-primary btn-xs btn-default change" data-type="text">
					<i class="fa fa-pencil"></i>
				</a>
				<a href="javascript:void(0);" title="Удалить" class="btn btn-xs btn-danger delete" data-type="text" data-pk="'.$products_['m_products_id'].'" data-name="m_products_id" data-title="Введите пароль для удаления записи" data-placement="left">
					<i class="fa fa-trash-o"></i>
				</a>
			</td>
		</tr>';
	}
}

unset($sql);
?>