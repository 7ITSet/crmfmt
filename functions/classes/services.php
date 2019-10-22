<?
defined ('_DSITE') or die ('Access denied');

class services{
	public 
		$categories_nodes,
		$categories_nodes_id,
		$categories_nodes_parent,
		$services_id,
		$units_id;
	
	function __construct(){
		global $sql;
		$q='SELECT * FROM `formetoo_main`.`m_services_categories` ORDER BY `m_services_categories_parent`,`m_services_categories_order`;';
		if($res=$sql->query($q)){
			$this->categories_nodes=$res;
			foreach($res as $item)
				$nodes_id[$item['m_services_categories_id']]=$item;
			$this->categories_nodes_id=$nodes_id;
			foreach($res as $item)
				$nodes_parent[$item['m_services_categories_parent']][]=$item;
			$this->categories_nodes_parent=$nodes_parent;
		}
		
		$q='SELECT * FROM `formetoo_main`.`m_services` ORDER BY `m_services_categories_id`,`m_services_order`;';
		$this->services_id=$sql->query($q,'m_services_id');
		
		$q='SELECT * FROM `formetoo_cdb`.`m_info_units`;';
		$this->units_id=$sql->query($q,'m_info_units_id');
		
	}
	
	public function categories_services($el){
		$a=array();
		foreach($this->services_id as $_s)
			if(strpos($_s[0]['m_services_categories_id'],$el)!==false)
				$a[]=$_s[0];
		return $a;
	}
	
	public function services_display_li(){
		$categories=array();
		$this->categories_childs(0,$categories);
		foreach($categories as &$categories_)
			$categories[$categories_['m_services_categories_id']]=$categories_;
		//привязываем услуги к категориям
		foreach($this->services_id as $t)
			if($ct=explode('|',$t[0]['m_services_categories_id']))
				//пробегаемся по каждой категории услуги
				foreach($ct as $ct_)
					//если эта категория существует
					if(isset($this->categories_nodes_id[$ct_]))
						//добавляем услугу в категорию
						$categories[$ct_]['items'][]=$t[0];
		return $categories;
	}
	
	public function categories_parent($el,&$nodes){
		foreach($this->categories_nodes_id as $t)
			//если очередная категория является родительской для заданной
			if($t['m_services_categories_id']==$this->categories_nodes_id[$el]['m_services_categories_parent']){
				//добавляем ее в массив
				$nodes[]=$t;
				break;
			}
	}
	
	public function categories_parents($el,&$nodes){
		foreach($this->categories_nodes_id as $t)
			//если текущая категория является родительской для заданной
			if(isset($this->categories_nodes_id[$el])&&$t['m_services_categories_id']==$this->categories_nodes_id[$el]['m_services_categories_parent']){
				//добавляем ее в массив
				$nodes[$t['m_services_categories_id']]=$t;
				//ищем родительскую категорию для найденной, если нужно найти все родительские
					$this->categories_parents($t['m_services_categories_id'],$nodes);
				break;
			}
	}
	
	public function categories_child($el,&$nodes){
		foreach($this->categories_nodes_id as $t)
			//если текущая категория является дочерней для заданной
			if($t['m_services_categories_parent']==$el){
				//добавляем ее в массив
				$nodes[]=$t;
			}
	}
	
	public function categories_childs($el,&$nodes,$tab=0,$level=0){
		foreach($this->categories_nodes_id as $t)
			//если текущая категория является дочерней для заданной
			if ($t['m_services_categories_parent']==$el){
				//добавляем ее в массив
				if($tab===1)
					for($i=0;$i<$level;$i++)
						$t['m_services_categories_name']='&nbsp;&nbsp;&nbsp;&nbsp;'.$t['m_services_categories_name'];
				elseif($tab===2){
					$parents=array();
					$this->categories_parents($t['m_services_categories_id'],$parents);
					$parents=array_reverse($parents);
					$name='';
					foreach($parents as $parents_)
						$name.=$parents_['m_services_categories_name'].'&nbsp;→&nbsp;';
					$t['m_services_categories_name']=$name.$t['m_services_categories_name'];
				}
				$nodes[]=$t;
				//ищем дочернюю категорию для найденной
				$this->categories_childs($t['m_services_categories_id'],$nodes,$tab,$level+1);
			}
		--$level;
	}
	
	public function categories_display_li($parent=0){
		echo '<ol class="dd-list">';
		//перебираем дочерние пункты текущего пункта
		foreach($this->categories_nodes_parent[$parent] as $nodes_parent__){
			//выводим пункт меню
			echo '<li class="dd-item dd3-item" data-id="'.$nodes_parent__['m_services_categories_id'].'">
					<div class="dd-handle dd3-handle">&nbsp;</div>
					<div class="dd3-content">
						<a href="#" class="editable" id="m_services_categories_name_'.$nodes_parent__['m_services_categories_id'].'" data-type="text" data-pk="'.$nodes_parent__['m_services_categories_id'].'" data-name="m_services_categories_name" data-title="Название категории">',
						$nodes_parent__['m_services_categories_name'],
						'</a>
						<span class="pull-right">
							<a href="javascript:void(0);" class="editable btn btn-xs btn-default delete" style="margin-left:20px!important;" data-type="text" data-pk="'.$nodes_parent__['m_services_categories_id'].'" data-name="m_services_categories_id" data-title="Введите пароль для удаления записи" data-placement="right">
								<i class="fa fa-lg fa-times"></i>
							</a>
						</span>
						<span class="pull-right">
							<div class="checkbox no-margin" style="margin-left:20px!important;">
								<label>
								  <input type="checkbox" class="checkbox style-0 show" data-name="m_services_categories_show_goods" '.($nodes_parent__['m_services_categories_show_goods']?'checked':'').' data-pk="'.$nodes_parent__['m_services_categories_id'].'">
								  <span class="font-xs">Выгружать в прайс</span>
								</label>
							</div>
						</span>
						<span class="pull-right">
							<div class="checkbox no-margin">
								<label>
								  <input type="checkbox" class="checkbox style-0 show" data-name="m_services_categories_show_site" '.($nodes_parent__['m_services_categories_show_site']?'checked':'').' data-pk="'.$nodes_parent__['m_services_categories_id'].'">
								  <span class="font-xs">Показывать на сайте</span>
								</label>
							</div>
						</span>
					</div>',
					//если у текущего дочернего пункта есть подпункты рекурсивно выводим их
					isset($this->categories_nodes_parent[$nodes_parent__['m_services_categories_id']])?$this->categories_display_li($nodes_parent__['m_services_categories_id']):'',
				'</li>';
		}
		echo '</ol>';
	}
	
	public static function categories_add(){
		global $sql,$e;
		$data['m_services_categories_name']=array(1,null,180);
		$data['m_services_categories_parent']=array(null,null,null,10,1);
		$data['m_services_categories_show_site']=array(null,null,3);
		$data['m_services_categories_show_goods']=array(null,null,3);

		array_walk($data,'check');
		
		if(!$e){
			$data['m_services_categories_id']=get_id('m_services_categories');
			$data['m_services_categories_parent']=$data['m_services_categories_parent']?$data['m_services_categories_parent']:0;
			$data['m_services_categories_show_site']=$data['m_services_categories_show_site']?1:0;
			$data['m_services_categories_show_goods']=$data['m_services_categories_show_goods']?1:0;
			
			$q='INSERT `formetoo_main`.`m_services_categories` SET
				`m_services_categories_id`='.$data['m_services_categories_id'].',
				`m_services_categories_name`=\''.$data['m_services_categories_name'].'\',
				`m_services_categories_parent`='.$data['m_services_categories_parent'].',
				`m_services_categories_show_site`='.$data['m_services_categories_show_site'].',
				`m_services_categories_show_goods`='.$data['m_services_categories_show_goods'].';';
			
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
	
	public static function services_add(){
		global $sql,$e;
		
		$data['m_services_name']=array(1,null,280);
		$data['m_services_unit']=array(1,null,3,null,1);
		$data['m_services_price_general']=array(1,null,18);
		$data['m_services_price_general_w']=array(1,null,18);
		$data['m_services_categories_id[]']=array(1);
		$data['m_services_links[]']=array();
		$data['m_services_contragents_id']=array(1,null,null,10,1);
		$data['m_services_show_site']=array(null,null,3);
		$data['m_services_show_price']=array(null,null,3);
		$data['m_services_products_id[]']=array(null,null,null,10);
		$data['m_services_products_count[]']=array(null,null,20);

		array_walk($data,'check');
		
		if(!$e){
			$data['m_services_id']=get_id('m_services');
			$data['m_services_show_site']=$data['m_services_show_site']?1:0;
			$data['m_services_show_price']=$data['m_services_show_price']?1:0;
			$data['m_services_date']=$data['m_services_update']=dt();
			//преобразуем массив используемых товаров в вид ID:количество
			$t=array();
			foreach($data['m_services_products_id[]'] as $k=>$v)
				$t[$v]=isset($t[$v])?$t[$v]+(float)str_replace(array(' ',','),array('','.'),$data['m_services_products_count[]'][$k]):(float)str_replace(array(' ',','),array('','.'),$data['m_services_products_count[]'][$k]);
			$data['m_services_products']=json_encode($t);
			
			$q='INSERT `formetoo_main`.`m_services` SET
				`m_services_id`='.$data['m_services_id'].',
				`m_services_contragents_id`='.$data['m_services_contragents_id'].',
				`m_services_name`=\''.$data['m_services_name'].'\',
				`m_services_unit`='.$data['m_services_unit'].',
				`m_services_price_general`='.(float)str_replace(array(' ',','),array('','.'),$data['m_services_price_general']).',
				`m_services_price_general_w`='.(float)str_replace(array(' ',','),array('','.'),$data['m_services_price_general_w']).',
				`m_services_categories_id`=\''.implode('|',$data['m_services_categories_id[]']).'\',
				`m_services_links`=\''.($data['m_services_links[]']?implode('|',$data['m_services_links[]']):'').'\',
				`m_services_products`=\''.($data['m_services_products']?$data['m_services_products']:'').'\',
				`m_services_show_site`='.$data['m_services_show_site'].',
				`m_services_show_price`='.$data['m_services_show_price'].',
				`m_services_date`=\''.$data['m_services_date'].'\',
				`m_services_update`=\''.$data['m_services_update'].'\';';
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

	public static function services_change(){
		global $sql,$e;
		$data['m_services_id']=array(1,null,null,10,1);
		$data['m_services_name']=array(1,null,280);
		$data['m_services_unit']=array(1,null,3,null,1);
		$data['m_services_price_general']=array(1,null,18);
		$data['m_services_price_general_w']=array(1,null,18);
		$data['m_services_categories_id[]']=array(1);
		$data['m_services_comment']=array(null,null,1000);
		$data['m_services_links[]']=array();
		$data['m_services_show_site']=array(null,null,3);
		$data['m_services_show_price']=array(null,null,3);
		$data['m_services_products_id[]']=array(null,null,null,null,1);
		$data['m_services_products_count[]']=array(null,null,20);

		array_walk($data,'check');
		
		if(!$e){
			$data['m_services_show_site']=$data['m_services_show_site']?1:0;
			$data['m_services_show_price']=$data['m_services_show_price']?1:0;
			$data['m_services_update']=dt();
			//преобразуем массив используемых товаров в вид ID:количество
			$t=array();
			foreach($data['m_services_products_id[]'] as $k=>$v)
				$t[$v]=isset($t[$v])?$t[$v]+(float)str_replace(array(' ',','),array('','.'),$data['m_services_products_count[]'][$k]):(float)str_replace(array(' ',','),array('','.'),$data['m_services_products_count[]'][$k]);
			$data['m_services_products']=json_encode($t);
			
			$q='UPDATE `formetoo_main`.`m_services` SET
				`m_services_name`=\''.$data['m_services_name'].'\',
				`m_services_unit`='.$data['m_services_unit'].',
				`m_services_price_general`='.(float)str_replace(array(' ',','),array('','.'),$data['m_services_price_general']).',
				`m_services_price_general_w`='.(float)str_replace(array(' ',','),array('','.'),$data['m_services_price_general_w']).',
				`m_services_categories_id`=\''.implode('|',$data['m_services_categories_id[]']).'\',
				`m_services_links`=\''.($data['m_services_links[]']?implode('|',$data['m_services_links[]']):'').'\',
				`m_services_products`=\''.($data['m_services_products']?$data['m_services_products']:'').'\',
				`m_services_show_site`='.$data['m_services_show_site'].',
				`m_services_show_price`='.$data['m_services_show_price'].',
				`m_services_comment`=\''.$data['m_services_comment'].'\',
				`m_services_update`=\''.$data['m_services_update'].'\' 
				WHERE `m_services_id`='.$data['m_services_id'].';';
			
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
	
	public static function services_group_change(){
		global $sql,$e;
		$data['group_m_services_id[]']=array();
		$data['m_services_unit']=array(null,null,3,null,1);
		$data['on_m_services_unit']=array(null,null,3);
		$data['m_services_price_general']=array(null,null,18);
		$data['on_m_services_price_general']=array(null,null,3);
		$data['m_services_price_general_w']=array(null,null,18);
		$data['on_m_services_price_general_w']=array(null,null,3);
		$data['m_services_categories_id[]']=array();
		$data['on_m_services_categories_id']=array(null,null,3);
		$data['m_services_links[]']=array();
		$data['on_m_services_links']=array(null,null,3);
		$data['m_services_contragents_id']=array(null,null,null,10,1);
		$data['on_m_services_contragents_id']=array(null,null,3);
		$data['m_services_show_site']=array(null,null,3);
		$data['on_m_services_show_site']=array(null,null,3);
		$data['m_services_show_price']=array(null,null,3);
		$data['on_m_services_show_price']=array(null,null,3);
		$data['m_services_products_id[]']=array(null,null,null,10);
		$data['m_services_products_count[]']=array(null,null,20);
		$data['on_m_services_products_id']=array(null,null,3);
		$data['m_services_round5_ceil']=array(null,null,3);
		$data['m_services_round5_floor']=array(null,null,3);

		array_walk($data,'check');
		
		if(!$e){
			$data['m_services_show_site']=$data['m_services_show_site']?1:0;
			$data['m_services_show_price']=$data['m_services_show_price']?1:0;
			$data['m_services_round5_ceil']=$data['m_services_round5_ceil']?1:0;
			$data['m_services_round5_floor']=$data['m_services_round5_floor']?1:0;
			$data['m_services_update']=dt();
			//преобразуем массив используемых товаров в вид ID:количество
			$t=array();
			if($data['on_m_services_products_id']){
				foreach($data['m_services_products_id[]'] as $k=>$v)
					$t[$v]=isset($t[$v])?$t[$v]+(float)str_replace(array(' ',','),array('','.'),$data['m_services_products_count[]'][$k]):(float)str_replace(array(' ',','),array('','.'),$data['m_services_products_count[]'][$k]);
				$data['m_services_products']=json_encode($t);
			}
			
			$q='UPDATE `formetoo_main`.`m_services` SET
				'.($data['on_m_services_contragents_id']&&$data['m_services_contragents_id']?'`m_services_contragents_id`='.$data['m_services_contragents_id'].',':'').'
				'.($data['on_m_services_unit']&&$data['m_services_unit']?'`m_services_unit`='.$data['m_services_unit'].',':'').'
				'.($data['on_m_services_categories_id']&&$data['m_services_categories_id[]']?'`m_services_categories_id`=\''.implode('|',$data['m_services_categories_id[]']).'\',':'').'
				'.($data['on_m_services_price_general']&&$data['m_services_price_general']?('`m_services_price_general`='.($data['m_services_round5_ceil']?('CEIL((`m_services_price_general`'.transform::sum_change($data['m_services_price_general']).')/5)*5,'):($data['m_services_round5_floor']?('FLOOR((`m_services_price_general`'.transform::sum_change($data['m_services_price_general']).')/5)*5,'):('`m_services_price_general`'.transform::sum_change($data['m_services_price_general']).',')))):'').'
				'.($data['on_m_services_price_general_w']&&$data['m_services_price_general_w']?('`m_services_price_general_w`='.($data['m_services_round5_ceil']?('CEIL((`m_services_price_general_w`'.transform::sum_change($data['m_services_price_general_w']).')/5)*5,'):($data['m_services_round5_floor']?('FLOOR((`m_services_price_general_w`'.transform::sum_change($data['m_services_price_general_w']).')/5)*5,'):('`m_services_price_general_w`'.transform::sum_change($data['m_services_price_general_w']).',')))):'').'
				'.($data['on_m_services_show_site']?'`m_services_show_site`='.$data['m_services_show_site'].',':'').'
				'.($data['on_m_services_show_price']?'`m_services_show_price`='.$data['m_services_show_price'].',':'').'
				'.($data['on_m_services_links']?'`m_services_links`=\''.($data['m_services_links[]']?implode('|',$data['m_services_links[]']):'').'\',':'').'
				'.($data['on_m_services_products_id']?'`m_services_products`=\''.$data['m_services_products'].'\',':'').'
				`m_services_update`=\''.$data['m_services_update'].'\' 
				WHERE `m_services_id` IN (0,'.implode(',',$data['group_m_services_id[]']).');';
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
?>