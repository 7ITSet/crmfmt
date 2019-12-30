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
$categories=$sql->query($q,'id');

$limit=get('limit')?get('limit'):20;
$page=get('page')?get('page'):1;
$start=$limit*$page-$limit;
$category=get('category');

$ql='SELECT COUNT(`product_id`) FROM `formetoo_main`.`m_products_category` WHERE `category_id`='.$category.';';

$q = 'SELECT `m_products`.*, GROUP_CONCAT(`m_products_category`.`category_id` SEPARATOR \'|\') AS categories_id FROM `formetoo_main`.`m_products` 
				LEFT JOIN `formetoo_main`.`m_products_category` 
					ON `m_products_category`.`product_id`=`m_products`.`id`
				GROUP BY `m_products_category`.`product_id`
				ORDER BY `m_products_date` DESC,`m_products_name`,`m_products_order`
				LIMIT '.$start.','.$limit.';';
	
	
if ($res=$sql->query($q))
	$p=$res;

if ($p){
	$res=$sql->query($ql);
	echo '<tr style="display:none" count="'.$res[0]['COUNT(`id`)'].'" page="'.$page.'"></tr>';
	$i = 0;
	$k = 0;

	foreach($p as $products_){
		$categories_list = array();
		$categories_id = explode('|',$products_['categories_id']);
		foreach($categories_id as $t_) {
			$categories_list[] = $categories[$t_][0]['m_products_categories_name'];
		}
		
	/* 	$m_products_links=array();
		$products_['m_products_links']=explode('|',$products_['m_products_links']);
		if($products_['m_products_links'][0])
			foreach($products_['m_products_links'] as $t_)
				$m_products_links[]=$products->products_id[$t_][0]['m_products_name']; */
		
		echo '<tr>
			<td class="check" data-foto="'.($products_['m_products_foto']&&is_object(json_decode($products_['m_products_foto']))?current(get_object_vars(json_decode($products_['m_products_foto'])))->file:'').'">
				<label class="checkbox">
					<input type="checkbox" class="checkbox tr id" value="'.$products_['id'].'">
					<i></i>
				</label>
			</td>
			<td>'.$products_['id'].'</td>
			<td>',$products_['m_products_name'],'</td>
			<td>',$units_id[$products_['m_products_unit']][0]['m_info_units_name'],'</td>
			<td>',$products_['m_products_price_general'],'</td>
			<td>',implode('<br/>',$categories_list),'</td>
			<td>',$products_['m_products_update'],'</td>
			<td>
				<label class="checkbox">
				  <input type="checkbox" class="checkbox style-0 show" data-name="m_products_show_site" '.($products_['m_products_show_site']==1?'checked':'').' data-pk="'.$products_['id'].'">
				  <span>На сайте</span>
				</label>
			</td>
			<td>
				<a href="#" class="m_products_order" data-type="text" data-pk="'.$products_['id'].'" data-name="m_products_order" data-title="Порядковый номер">',
					$products_['m_products_order'],
				'</a>
			</td>
			<td>
				<div class="btn-group btn-labeled multirow-btn" title="Поставить в очередь обновлений">
					<a class="btn btn-xs btn-info refresh" href="javascript:void(0);" data-name="m_products_check_it" data-pk="'.$products_['id'].'" ><i class="glyphicon glyphicon-refresh"></i></a>
					<a class="btn btn-xs btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<li>
							<a href="javascript:void(0);" class="refresh" data-name="m_products_check_it" data-pk="'.$products_['id'].'" >Обновить цену</a>
						</li>
						<li>
							<a href="javascript:void(0);" class="refresh-text" data-name="m_products_check_it_no_foto" data-pk="'.$products_['id'].'" >Обновить цену и тексты</a>
						</li>
						<li>
							<a href="javascript:void(0);" class="refresh-foto" data-name="m_products_check_it_all" data-pk="'.$products_['id'].'" >Обновить цену и фото/доки</a>
						</li>
						<li>
							<a href="javascript:void(0);" class="refresh-none" data-name="m_products_check_it_none" data-pk="'.$products_['id'].'" >Снять с обновления</a>
						</li>
					</ul>
				</div>
                
                
				<a title="Сделать копию товара" class="btn btn-success btn-xs copy_product" href="/copyProduct.php?id='.$products_['id'].'">
					<i class="fa fa-copy"></i>
				</a>
         &nbsp;
				<a href="javascript:void(0);" title="Изменить позицию (выше)" class="btn btn-xs btn-default changepos" data-value="'.$products_['m_products_order'].'" data-type="text" data-pk="'.$products_['id'].'" data-name="m_products_order_up" data-placement="left">
					<i class="fa fa-angle-up"></i>
				</a>
				<a href="javascript:void(0);" title="Изменить позицию (ниже)" class="btn btn-xs btn-default changepos" data-value="'.$products_['m_products_order'].'" data-type="text" data-pk="'.$products_['id'].'" data-name="m_products_order_down" data-placement="left">
					<i class="fa fa-angle-down"></i>
				</a>&nbsp;&nbsp;
				
				<a href="#" title="Посмотреть на сайте" class="btn btn-xs btn-default eye">
				  <i class="fa fa-eye"></i>
        </a>
				
				<a href="/companies/products/new/?action=change&id='.$products_['id'].'" title="Редактировать" class="btn btn-primary btn-xs btn-default change" data-type="text">
					<i class="fa fa-pencil"></i>
				</a>
				<a href="javascript:void(0);" title="Удалить" class="btn btn-xs btn-danger delete" data-type="text" data-pk="'.$products_['id'].'" data-name="id" data-title="Введите пароль для удаления записи" data-placement="left">
					<i class="fa fa-trash-o"></i>
				</a>
			</td>
		</tr>';
	}
}

unset($sql);
