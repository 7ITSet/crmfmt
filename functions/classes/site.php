<?
defined ('_DSITE') or die ('Access denied');

class site{
	public 
		$menu_id,
		$content_id,
		$categories_nodes_parent;
	
	function __construct(){
		global $sql;
		
		$q='SELECT * FROM `formetoo_main`.`menu` ORDER BY `parent`,`order`;';
		$this->menu_id=$sql->query($q,'id');
		
		$q='SELECT * FROM `formetoo_main`.`content` ORDER BY `update` DESC;';
		$this->content_id=$sql->query($q,'menu');
		
		foreach($this->menu_id as $item)
			$nodes_parent[$item[0]['parent']][]=$item[0];
		$this->categories_nodes_parent=$nodes_parent;
	}
	
		public function products_display_li(){
		$categories=array();
		$this->categories_childs(0,$categories);
		foreach($categories as &$categories_)
			$categories[$categories_['m_products_categories_id']]=$categories_;
		//привязываем услуги к категориям
		foreach($this->products_id as $t)
			if($ct=explode('|',$t[0]['m_products_categories_id']))
				//пробегаемся по каждой категории услуги
				foreach($ct as $ct_)
					//если эта категория существует
					if(isset($this->categories_nodes_id[$ct_]))
						//добавляем услугу в категорию
						$categories[$ct_]['items'][]=$t[0];
		return $categories;
	}
	
	public function menu_parents($el,&$nodes){
		foreach($this->menu_id as $t)
			//если текущая категория является родительской для заданной
			if(isset($this->menu_id[$el])&&$t['id']==$this->menu_id[$el]['parent']){
				//добавляем ее в массив
				$nodes[$t['id']]=$t;
				//ищем родительскую категорию для найденной, если нужно найти все родительские
					$this->menu_parents($t['id'],$nodes);
				break;
			}
	}
	
	public function categories_child($el,&$nodes){
		foreach($this->menu_id as $t)
			//если текущая категория является дочерней для заданной
			if($t['parent']==$el){
				//добавляем ее в массив
				$nodes[]=$t;
			}
	}
	
	public function categories_childs($el,&$nodes,$tab=0,$level=0){
		foreach($this->menu_id as $t){
			$t=isset($t['id'])?$t:$t[0];
			//если текущая категория является дочерней для заданной
			if ($t['parent']==$el){
				//добавляем ее в массив
				if($tab===1)
					for($i=0;$i<$level;$i++)
						$t['name']='&nbsp;&nbsp;&nbsp;&nbsp;'.$t['name'];
				elseif($tab===2){
					$parents=array();
					$this->menu_parents($t['id'],$parents);
					$parents=array_reverse($parents);
					$name='';
					foreach($parents as $parents_)
						$name.=$parents_['name'].'&nbsp;→&nbsp;';
					$t['name']=$name.$t['name'];
				}
				$nodes[]=$t;
				//ищем дочернюю категорию для найденной
				$this->categories_childs($t['id'],$nodes,$tab,$level+1);
			}
		}
		--$level;
	}
	
	public function categories_display_li($parent=0){
		echo '<ol class="dd-list">';
		//перебираем дочерние пункты текущего пункта
		foreach($this->categories_nodes_parent[$parent] as $nodes_parent__){
			//выводим пункт меню
			echo '<li class="dd-item dd3-item" data-id="'.$nodes_parent__['id'].'">
					<div class="dd-handle dd3-handle">&nbsp;</div>
					<div class="dd3-content">',
						$nodes_parent__['name'],
						'<span class="pull-right">
							<a href="javascript:void(0);" class="editable btn btn-xs btn-default delete" style="margin-left:5px!important;" data-type="text" data-pk="'.$nodes_parent__['id'].'" data-name="id" data-title="Введите пароль для удаления записи" data-placement="left">
								<i class="fa fa-lg fa-times"></i>
							</a>
						</span>
						<span class="pull-right">
							<a href="'.url().'?action=details&city=www&menu_id='.$nodes_parent__['id'].'" class="editable btn btn-xs btn-default" style="margin-left:20px!important;" data-name="site_mwnu_active" data-type="text" data-placement="right">
								<i class="fa fa-lg fa-pencil"></i>
							</a>
						</span>
						<span class="pull-right">
							<div class="checkbox no-margin">
								<label>
								  <input type="checkbox" class="checkbox style-0 show" data-name="site_menu_active" '.($nodes_parent__['active']?'checked':'').' data-pk="'.$nodes_parent__['id'].'">
								  <span class="font-xs">Показывать на сайте</span>
								</label>
							</div>
						</span>
					</div>',
					//если у текущего дочернего пункта есть подпункты рекурсивно выводим их
					isset($this->categories_nodes_parent[$nodes_parent__['id']])?$this->categories_display_li($nodes_parent__['id']):'',
				'</li>';
		}
		echo '</ol>';
	}
	
	public static function page_add(){
		global $sql,$e;
		$data['menu_name']=array(1,null,80);
		$data['menu_url']=array(null,null,60);
		$data['menu_parent']=array(1,null,10,null,1);
		$data['menu_order']=array(null,null,2,null,1);
		$data['menu_type']=array(1,null,80);
		$data['menu_active']=array(null,null,10);
		$data['menu_category']=array(null,null,10);
		$data['menu_filters']=array(null,null,65000);
		$data['page_title']=array(1,null,130);
		$data['page_h1']=array(1,null,130);
		$data['page_description']=array(null,null,180);
		$data['page_keywords']=array(null,null,380);

		array_walk($data,'check');

		if(!$e){
			$data['menu_id']=get_id('menu');
			$data['content_id']=get_id('content');
			$data['page_update']=dt();
			$data['menu_active']=$data['menu_active']?1:0;
			$data['menu_order']=$data['menu_order']?$data['menu_order']:1;
			$data['page_content']=post('page_content',array('<span class="underline"></span>','</a>','"'),array('','<span class="underline"></span></a>','&quot;'),1);
			$data['page_keywords']=explode('\r\n',$data['page_keywords']);$data['page_keywords']=mb_strtolower(implode(', ',$data['page_keywords']),'utf-8');
			
			//добавляем пункт меню
			$q='INSERT `formetoo_main`.`menu` SET
				`id`='.$data['menu_id'].',
				`parent`='.$data['menu_parent'].',
				`name`=\''.$data['menu_name'].'\',
				`url`=\''.$data['menu_url'].'\',
				`active`='.$data['menu_active'].',
				`order`='.$data['menu_order'].',
				`category`='.$data['menu_category'].',
				`filters`=\''.$data['menu_filters'].'\',
				`type`=\''.$data['menu_type'].'\';';
			if($sql->query($q)){
				//добавляем контент
				$q='INSERT `formetoo_main`.`content` SET
					`id`='.$data['content_id'].',
					`menu`='.$data['menu_id'].',
					`title`=\''.transform::typography($data['page_title']).'\',
					`description`=\''.transform::typography($data['page_description']).'\',
					`keywords`=\''.$data['page_keywords'].'\',
					`h1`=\''.transform::typography($data['page_h1']).'\',
					`content`=\''.transform::typography($data['page_content']).'\',
					`update`=\''.$data['page_update'].'\';';
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
		}
		else{
			elogs();
			header('Location: '.url().'?error');
		}
		exit;
	}
	
	public static function page_change(){
		global $sql,$e;
		$data['menu_id']=array(1,null,10,null,1);
		$data['content_id']=array(1,null,10,null,1);
		$data['menu_name']=array(1,null,80);
		$data['city']=array(1);
		$data['menu_url']=array(null,null,60);
		$data['menu_parent']=array(1,null,10,null,1);
		$data['menu_order']=array(null,null,2,null,1);
		$data['menu_type']=array(1,null,80);
		$data['menu_active']=array(null,null,10);
		$data['menu_category']=array(null,null,10);
		$data['menu_filters']=array(null,null,65000);
		$data['page_title']=array(null,null,130);
		$data['page_h1']=array(null,null,130);
		$data['page_description']=array(null,null,180);
		$data['page_keywords']=array(null,null,380);

		array_walk($data,'check');

		if(!$e){
			$data['page_update']=dt();
			$page_id=get_id('content');
			$data['menu_active']=$data['menu_active']?1:0;
			$data['menu_order']=$data['menu_order']?$data['menu_order']:1;
			$data['page_content']=post('page_content',array('<span class="underline"></span>','</a>','"'),array('','<span class="underline"></span></a>','&quot;'),1);
			$data['page_keywords']=explode('\r\n',$data['page_keywords']);$data['page_keywords']=mb_strtolower(implode(', ',$data['page_keywords']),'utf-8');
			
			//обновляем пункт меню
			$q='UPDATE `formetoo_main`.`menu` SET
				`parent`='.$data['menu_parent'].',
				`name`=\''.$data['menu_name'].'\',
				`url`=\''.$data['menu_url'].'\',
				`active`='.$data['menu_active'].',
				`order`='.$data['menu_order'].',
				`category`='.$data['menu_category'].',
				`filters`=\''.$data['menu_filters'].'\',
				`type`=\''.$data['menu_type'].'\' 
				WHERE `id`='.$data['menu_id'].';';
			if($sql->query($q)){
				//выбираем контент с подходящим городом, если есть - обноаввляем, если нет - добаляем новый контент
				$q='SELECT `id` FROM `formetoo_main`.`content` WHERE `id`='.$data['content_id'].' AND `city`=\''.$data['city'].'\';';
				if($sql->query($q))
					//обновляем контент
					$q='UPDATE `formetoo_main`.`content` SET
						`title`=\''.transform::typography($data['page_title']).'\',
						`description`=\''.transform::typography($data['page_description']).'\',
						`keywords`=\''.$data['page_keywords'].'\',
						`h1`=\''.transform::typography($data['page_h1']).'\',
						`content`=\''.transform::typography($data['page_content']).'\',
						`update`=\''.$data['page_update'].'\' 
						WHERE `id`='.$data['content_id'].';';
				else{
					//добавляем контент
					$q='INSERT INTO `formetoo_main`.`content` SET
						`id`='.get_id('content').',
						`city`=\''.$data['city'].'\',
						`menu`='.$data['menu_id'].',
						`title`=\''.transform::typography($data['page_title']).'\',
						`description`=\''.transform::typography($data['page_description']).'\',
						`keywords`=\''.$data['page_keywords'].'\',
						`h1`=\''.transform::typography($data['page_h1']).'\',
						`content`=\''.transform::typography($data['page_content']).'\',
						`update`=\''.$data['page_update'].'\';';
				}
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
		}
		else{
			elogs();
			header('Location: '.url().'?error');
		}
		exit;
	}
	
}
?>