<?
defined ('_DSITE') or die ('Access denied');

class products{
	public
		$categories_nodes,
		$categories_nodes_id,
		$categories_nodes_parent,
		$products_id,
		$products_attr,
		$products_price,
		$units_id,
		$attr_id,
		$attr_groups;

	function __construct(){
		global $sql;
		$q='SELECT * FROM `formetoo_main`.`m_products_categories` ORDER BY `m_products_categories_parent`,`m_products_categories_order`;';
		if($res=$sql->query($q)){
			$this->categories_nodes=$res;
			foreach($res as $item)
				$nodes_id[$item['id']]=$item;
			$this->categories_nodes_id=$nodes_id;
			foreach($res as $item)
				$nodes_parent[$item['m_products_categories_parent']][]=$item;
			$this->categories_nodes_parent=$nodes_parent;
		}

		/* $q='SELECT * FROM `formetoo_main`.`m_products` ORDER BY `m_products_name`,`m_products_date` DESC;';
		$this->products_id=$sql->query($q,'id'); */
		
		$q = 'SELECT `m_products`.`id`, `m_products`.`m_products_unit`,`m_products`.`m_products_name`,`m_products`.`m_products_date`, GROUP_CONCAT(`m_products_category`.`category_id` SEPARATOR \'|\') AS categories_id FROM `formetoo_main`.`m_products` 
			LEFT JOIN `formetoo_main`.`m_products_category` 
				ON `m_products_category`.`product_id`=`m_products`.`id` 
			GROUP BY `m_products_category`.`product_id` 
			ORDER BY `m_products_name`,`m_products_date` DESC;';
		$this->products_id=$sql->query($q,'id');

		$q='SELECT * FROM `formetoo_cdb`.`m_info_units`;';
		$this->units_id=$sql->query($q,'m_info_units_id');

		$q='SELECT * FROM `formetoo_main`.`m_products_attributes_list`;';
		$this->attr_id=$sql->query($q,'m_products_attributes_list_id');

		$q='SELECT * FROM `formetoo_main`.`m_products_attributes_groups`;';
		$this->attr_groups=$sql->query($q,'products_attributes_groups_id');

		/* $q='SELECT * FROM `formetoo_main`.`m_products_attributes`;';
		$this->products_attr=$sql->query($q,'m_products_attributes_product_id'); */

		$q='SELECT * FROM `formetoo_main`.`m_products_prices`;';
		$this->products_price=$sql->query($q,'m_products_prices_product_id');

	}

	public function products_display_li(){
		$categories=array();
		$this->categories_childs(0,$categories);
		foreach($categories as &$categories_)
			$categories[$categories_['id']]=$categories_;
		//привязываем услуги к категориям
		if($this->products_id) {
			foreach($this->products_id as $t) {
				if($ct=explode('|',$t[0]['categories_id'])) {
					//пробегаемся по каждой категории услуги
					foreach($ct as $ct_) {
						//если эта категория существует
						if(isset($this->categories_nodes_id[$ct_])) {
							//добавляем услугу в категорию
							$categories[$ct_]['items'][]=$t[0];
						}
					}
				}
			}
		}
		return $categories;
	}

	public function categories_parent($el,&$nodes){
		foreach($this->categories_nodes_id as $t)
			//если очередная категория является родительской для заданной
			if($t['id']==$this->categories_nodes_id[$el]['m_products_categories_parent']){
				//добавляем ее в массив
				$nodes[]=$t;
				break;
			}
	}

	public function categories_parents($el,&$nodes){
		foreach($this->categories_nodes_id as $t)
			//если текущая категория является родительской для заданной
			if(isset($this->categories_nodes_id[$el])&&$t['id']==$this->categories_nodes_id[$el]['m_products_categories_parent']){
				//добавляем ее в массив
				$nodes[$t['id']]=$t;
				//ищем родительскую категорию для найденной, если нужно найти все родительские
					$this->categories_parents($t['id'],$nodes);
				break;
			}
	}

	public function categories_child($el,&$nodes){
		foreach($this->categories_nodes_id as $t)
			//если текущая категория является дочерней для заданной
			if($t['m_products_categories_parent']==$el){
				//добавляем ее в массив
				$nodes[]=$t;
			}
	}

	public function categories_childs($el,&$nodes,$tab=0,$level=0){
		foreach($this->categories_nodes_id as $t)
			//если текущая категория является дочерней для заданной
			if ($t['m_products_categories_parent']==$el){
				//добавляем ее в массив
				if($tab===1)
					for($i=0;$i<$level;$i++)
						$t['m_products_categories_name']='&nbsp;&nbsp;&nbsp;&nbsp;'.$t['m_products_categories_name'];
				elseif($tab===2){
					$parents=array();
					$this->categories_parents($t['id'],$parents);
					$parents=array_reverse($parents);
					$name='';
					foreach($parents as $parents_)
						$name.=$parents_['m_products_categories_name'].'&nbsp;→&nbsp;';
					$t['m_products_categories_name']=$name.$t['m_products_categories_name'];
				}
				$nodes[]=$t;
				//ищем дочернюю категорию для найденной
				$this->categories_childs($t['id'],$nodes,$tab,$level+1);
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
					<div class="dd3-content">
						<a href="#" class="editable" id="m_products_categories_name_'.$nodes_parent__['id'].'" data-type="text" data-pk="'.$nodes_parent__['id'].'" data-name="m_products_categories_name" data-title="Название категории">',
						$nodes_parent__['m_products_categories_name'],
						'</a>
						<span class="pull-right">
							<a href="javascript:void(0);" class="editable btn btn-xs btn-default delete" style="margin-left:20px!important;" data-type="text" data-pk="'.$nodes_parent__['id'].'" data-name="id" data-title="Введите пароль для удаления записи" data-placement="right">
								<i class="fa fa-lg fa-times"></i>
							</a>
						</span>
						<span class="pull-right">
							<div class="checkbox no-margin">
								<label>
								  <input type="checkbox" class="checkbox style-0 show" data-name="m_products_categories_show_categories" '.($nodes_parent__['m_products_categories_show_categories']?'checked':'').' data-pk="'.$nodes_parent__['id'].'">
								  <span class="font-xs">Категории картинками</span>
								</label>
							</div>
						</span>
						<span class="pull-right">
							<div class="checkbox no-margin" style="margin-left:20px!important;">
								<label>
								  <input type="checkbox" class="checkbox style-0 show" data-name="m_products_categories_show_goods" '.($nodes_parent__['m_products_categories_show_goods']?'checked':'').' data-pk="'.$nodes_parent__['id'].'">
								  <span class="font-xs" title="Если снять галку — в категории будут показаны дочерние подкатегории (для больших категорий)&#010;Если поставить галку — в категории будут показаны все товары категории и покатегорий с фильтрами атрибутов">Показывать товары</span>
								</label>
							</div>
						</span>
						<span class="pull-right">
							<div class="checkbox no-margin">
								<label>
								  <input type="checkbox" class="checkbox style-0 show" data-name="m_products_categories_show_attributes" '.($nodes_parent__['m_products_categories_show_attributes']?'checked':'').' '.(!$nodes_parent__['m_products_categories_show_goods']||$nodes_parent__['m_products_categories_show_categories']?'disabled':'').' data-pk="'.$nodes_parent__['id'].'">
								  <span class="font-xs">Показывать фильтры</span>
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

	public function product_categories_display($parent=0, $selected_categories_id = []){
		echo '<ol class="dd-list">';
		//перебираем дочерние пункты текущего пункта
		foreach($this->categories_nodes_parent[$parent] as $nodes_parent__){
			//выводим пункт меню
			echo '<li class="dd-item dd3-item" data-id="'.$nodes_parent__['id'].'">
					<div class="dd-handle dd3-handle">&nbsp;</div>
					<div class="dd3-content">
						<span class="pull-left">
							<div class="checkbox no-margin">
								<label>
								  <input type="checkbox" class="checkbox style-0 show" name="selected_categories_id[]" '.(in_array($nodes_parent__['id'], $selected_categories_id)?'checked':'').' value="'.$nodes_parent__['id'].'">
								  <span class="font-xs"></span>
								</label>
							</div>
						</span>
						<a href="#" class="editable" id="m_products_categories_name_'.$nodes_parent__['id'].'">'.
						$nodes_parent__['m_products_categories_name'].
						'</a>
					</div>',
					//если у текущего дочернего пункта есть подпункты рекурсивно выводим их
					isset($this->categories_nodes_parent[$nodes_parent__['id']])?$this->product_categories_display($nodes_parent__['id'], $selected_categories_id):'',
				'</li>';
		}
		echo '</ol>';
	}

	public static function categories_add(){
		global $sql,$e;
		$data['m_products_categories_name']=array(1,null,180);
		$data['m_products_categories_parent']=array(null,null,null,10,1);
		$data['m_products_categories_show_attributes']=array(null,null,3);
		$data['m_products_categories_show_goods']=array(null,null,3);

		array_walk($data,'check');

		if(!$e){
			$data['id']=get_id('m_products_categories');
			$data['m_products_categories_parent']=$data['m_products_categories_parent']?$data['m_products_categories_parent']:0;
			$data['m_products_categories_show_attributes']=$data['m_products_categories_show_attributes']?1:0;
			$data['m_products_categories_show_goods']=$data['m_products_categories_show_goods']?1:0;

			$q='INSERT `formetoo_main`.`m_products_categories` SET
				`id`='.$data['id'].',
				`m_products_categories_name`=\''.$data['m_products_categories_name'].'\',
				`m_products_categories_name_seo`=\''.transform::translit($data['m_products_categories_name']).'\',
				`m_products_categories_parent`='.$data['m_products_categories_parent'].',
				`m_products_categories_show_attributes`='.$data['m_products_categories_show_attributes'].',
				`m_products_categories_show_goods`='.$data['m_products_categories_show_goods'].';';

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

	public static function products_add(){

		global $sql,$e,$user,$info;
		$data['m_products_name']=array(1,null,180);
		$data['m_products_unit']=array(1,null,3,null,1);
		$data['m_products_unit_volume']=array(null,null,18);
		$data['m_products_price_general']=array(1,null,18);
		$data['m_products_price_currency']=array(1,null,null,1,1);
		$data['m_products_miltiplicity']=array(null,null,18);
		$data['selected_categories_id[]']=array(1);
		$data['m_products_links[]']=array();

		$data['idfoto[]']=array();
		$data['m_products_foto_main[]']=array(null,null,8);

		$data['m_products_prices_limit_count[]']=array(null,null,18,null,1);
		$data['m_products_prices_limit_price[]']=array(null,null,18,null,1);
		$data['m_products_prices_price[]']=array(null,null,18,null,1);

		$data['m_products_contragents_id']=array(1,null,null,10,1);
		$data['m_products_show_site']=array(null,null,3);
		$data['m_products_show_price']=array(null,null,3);
		$data['m_products_exist']=array(null,null,3);
		$data['m_products_desc']=array(null,null,65000);

		$data['slug']=array(1,null,255);

		array_walk($data,'check');

		$data['products_attributes_groups_id'] =  $_REQUEST['products_attributes_groups_id'];
		$data['m_products_attributes_list_id[]'] = $_REQUEST['m_products_attributes_list_id'];
		$data['m_products_attributes_value[]'] = $_REQUEST['m_products_attributes_value'];
		//аттрибут интервал
		$data['attribute_value_interval_min[]'] =  $_REQUEST['attribute_value_interval_min'];
		$data['attribute_value_interval_max[]'] =  $_REQUEST['attribute_value_interval_max'];

		$data['attribute_value_file[]'] =  $_REQUEST['attribute_value_file'];
		$data['unit_weight'] = $_REQUEST['unit_weight'];
		$data['unit_height'] = $_REQUEST['unit_height'];
		$data['unit_length'] = $_REQUEST['unit_length'];
		$data['unit_width'] = $_REQUEST['unit_width'];

		if(!$e){
			$data['id']=get_id('m_products');
			$data['m_products_show_site']=$data['m_products_show_site']?1:0;
			$data['m_products_show_price']=$data['m_products_show_price']?1:0;
			$data['m_products_exist']=$data['m_products_exist']?1:0;
			$data['m_products_miltiplicity']=$data['m_products_miltiplicity']?$data['m_products_miltiplicity']:1;
			$data['m_products_date']=$data['m_products_update']=dt();

			$id =$data['id'];
			$m_products_desc = $data['m_products_desc'];

			//добавляем привязанные категории к продукту
			$q='INSERT INTO `formetoo_main`.`m_products_category` (`product_id`,`category_id`) VALUES ';
			for($i=0;$i<count($data['selected_categories_id[]']);$i++){
				$q.='(\''.$data['id'].'\', \''.$data['selected_categories_id[]'][$i].'\'), ';
			}
			$q = mb_substr($q, 0, -2);
				
			if(!($sql->query($q))){
				elogs();
			}

			//проверка есть ли обязательные атрибуты и заполнены ли они
			//узнаем сколько обязательный атрибутов должно быть
			$attr_required_count = 0;
			$q="SELECT `m_products_attributes_groups_list_id` FROM `formetoo_main`.`m_products_attributes_groups` WHERE `m_products_attributes_groups_required` = 1;";
			$groups = $sql->query($q);
			if(!empty($groups)){
				foreach ($groups as $group){
					$arr = explode('|', $group['m_products_attributes_groups_list_id']);
					$attr_required_count += count($arr);
				}
			}



			if($attr_required_count > 0){
				if(!empty($_POST['attr_required_val'])){
					$data['attr_required_val'] = json_decode($_POST['attr_required_val']);
					if($attr_required_count == count($data['attr_required_val'])){
						for($i=0;$i<count($data['attr_required_val']);$i++){
							$q='INSERT INTO `formetoo_main`.`m_products_attributes` (`m_products_attributes_product_id`,`m_products_attributes_list_id`,`m_products_attributes_value`) VALUES ';
							$q.='(
							\''.$data['id'].'\',
							\''.$data['attr_required_val'][$i]->id.'\',
							\''.$data['attr_required_val'][$i]->value.'\'
						);';
							if(!($sql->query($q)))
								elogs();
						}
					} else {
						header('Location: '.url().'?error');
						exit;
					}
				} else {
					header('Location: '.url().'?error');
					exit;
				}
			}
			//добавляем атрибуты
			if(isset($data['m_products_attributes_value[]'])){
				foreach($data['m_products_attributes_value[]'] as $key => $value) {
					foreach($value as $keyAttr => $valueAttr) {
					if ($valueAttr) {
						$valuesArray[] = '(
							\''.$data['id'].'\', 
							\''.$data['m_products_attributes_list_id[]'][$key][0].'\', 
							\''.$valueAttr.'\'
						)';
						}
					}
				}

				foreach($data['attribute_value_interval_min[]'] as $key => $value) {
					foreach($value as $keyAttr => $valueAttr) {
						$valuesArray[] = '(
							\''.$data['id'].'\', 
							\''.$data['m_products_attributes_list_id[]'][$key][0].'\', 
							\''.$valueAttr.'|'.$data['attribute_value_interval_max[]'][$key][0].'\'
						)';
					}
				}

				//добавляем файлы аттрибутов
				if (!empty($data['attribute_value_file[]'])) {
					foreach($data['attribute_value_file[]'] as $keyFileAttr => $valueFileAttr) {
						if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/uploads/files/products/'.$data['id']))
								mkdir($_SERVER['DOCUMENT_ROOT'].'/uploads/files/products/'.$data['id'], 0777, true);
						$files=array();
						if($valueFileAttr) {
							foreach($valueFileAttr as $k=>$v){
								$files[$k]['file'] = $v;
								$files[$k]['name'] = $v;
								//копируем только добавленные файлы
								if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/uploads/files/products/'.$data['id'].'/'.$files[$k]['file'])){
									copy($_SERVER['DOCUMENT_ROOT'].'/temp/uploads/'.$user->getInfo().'/'.$files[$k]['file'], $_SERVER['DOCUMENT_ROOT'].'/uploads/files/products/'.$data['id'].'/'.$files[$k]['file']);
								}
							}
						}

						$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
						$valuesArray[] = '(
							\''.$data['id'].'\', 
							\''.$data['m_products_attributes_list_id[]'][$keyFileAttr][0].'\', 
							\''.$files.'\'
						)';
					}
				}
				
				$q='INSERT INTO `formetoo_main`.`m_products_attributes` (`m_products_attributes_product_id`,`m_products_attributes_list_id`,`m_products_attributes_value`) VALUES '. implode(',', $valuesArray);
				
				if(!($sql->query($q)))
					elogs();
			}


			//добавляем скидки
			if($data['m_products_prices_price[]'][0]!=''&&$count=sizeof($data['m_products_prices_price[]'])){
				$q='INSERT INTO `formetoo_main`.`m_products_prices` (`m_products_prices_product_id`,`m_products_prices_limit_count`,`m_products_prices_limit_price`,`m_products_prices_price`) VALUES ';
				for($i=0;$i<$count;$i++)
					if($data['m_products_prices_price[]'][$i])
						$q.='(
							'.$data['id'].',
							\''.(float)str_replace(array(' ',','),array('','.'),$data['m_products_prices_limit_count[]'][$i]).'\',
							\''.(float)str_replace(array(' ',','),array('','.'),$data['m_products_prices_limit_price[]'][$i]).'\',
							\''.(float)str_replace(array(' ',','),array('','.'),$data['m_products_prices_price[]'][$i]).'\'
						),';

				if(!($sql->query(substr($q,0,-1).';')))
					elogs();
			}

			//добавляем описание товара
			if($data['m_products_desc']){
				$q="INSERT INTO `formetoo_main`.`m_products_desc` (`m_products_desc_id`,`m_products_desc_text`) VALUES ('$id','$m_products_desc');";
				if(!($sql->query($q)))
					elogs();
				}

			//добавляем фото
			if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/images/products/'.$data['id']))
				mkdir($_SERVER['DOCUMENT_ROOT'].'/images/products/'.$data['id']);
			$foto=array();
			if($data['idfoto[]'])
				foreach($data['idfoto[]'] as $k=>$v){
					$array = explode('.', $v);
					$nameFile = $array[0];
					$ext = end($array);
					$foto[$v]['file'] = $nameFile;
					$foto[$v]['ext'] = $ext;
					$foto[$v]['main']=isset($data['m_products_foto_main[]'][0])&&$data['m_products_foto_main[]'][0]==$v?1:0;
					//копируем только добавленные фотки
					if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/images/products/'.$data['id'].'/'.$nameFile.'_min.'.$ext)){
						copy($_SERVER['DOCUMENT_ROOT'].'/temp/uploads/'.$user->getInfo().'/'.$nameFile.'_max.'.$ext,$_SERVER['DOCUMENT_ROOT'].'/images/products/'.$data['id'].'/'.$nameFile.'_max.'.$ext);
						copy($_SERVER['DOCUMENT_ROOT'].'/temp/uploads/'.$user->getInfo().'/'.$nameFile.'_min.'.$ext,$_SERVER['DOCUMENT_ROOT'].'/images/products/'.$data['id'].'/'.$nameFile.'_min.'.$ext);
						copy($_SERVER['DOCUMENT_ROOT'].'/temp/uploads/'.$user->getInfo().'/'.$nameFile.'_med.'.$ext,$_SERVER['DOCUMENT_ROOT'].'/images/products/'.$data['id'].'/'.$nameFile.'_med.'.$ext);
					}
				}
			$foto=json_encode($foto,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

			$m_products_contragents_id = $data['m_products_contragents_id'];
			$m_products_name = $data['m_products_name'];
			$m_products_unit = $data['m_products_unit'];
			$m_products_price_general = (float)str_replace(array(' ',','),array('','.'),$data['m_products_price_general']);
			$m_products_price_currency = $data['m_products_price_currency'];
			$m_products_multiplicity = $data['m_products_miltiplicity'];
			$m_products_show_site = $data['m_products_show_site'];
			$m_products_show_price = $data['m_products_show_price'];
			$m_products_links = $data['m_products_links[]']?implode('|',$data['m_products_links[]']):'';
			$m_products_exist = $data['m_products_exist'];
			$m_products_foto = $foto;
			$slug = $data['slug'];
			$products_attributes_groups_id = $data['products_attributes_groups_id'];
			$unit_weight = str_replace(array(' ',','), array('','.'), $data['unit_weight']);
			$unit_height = str_replace(array(' ',','), array('','.'), $data['unit_height']);
			$unit_length = str_replace(array(' ',','), array('','.'), $data['unit_length']);
			$unit_width = str_replace(array(' ',','), array('','.'), $data['unit_width']);

			//seo
			if(!empty($_POST['seo_parameters'][0])){
				$m_products_seo_title = $_POST['seo_parameters'][0];
			} else {
				$m_products_seo_title = $m_products_name;
			}

			if(!empty($_POST['seo_parameters'][1])){
				$m_products_seo_keywords = $_POST['seo_parameters'][1];
			} else {
				$m_products_seo_keywords = $m_products_name;
			}

			if(!empty($_POST['seo_parameters'][2])){
				$m_products_seo_description = $_POST['seo_parameters'][2];
			} else {
				$m_products_seo_description = $m_products_name;
			}

			$q = "INSERT INTO `formetoo_main`.`m_products`(
			`id`,
			`m_products_contragents_id`,
			`m_products_name`,
			`m_products_name_full`,
			`m_products_unit`,
			`m_products_price_general`,
			`m_products_price_currency`,
			`m_products_multiplicity`,
			`m_products_show_site`,
			`m_products_show_price`,
			`m_products_links`,
			`m_products_exist`,
			`m_products_foto`,
			`m_products_seo_title`,
			`m_products_seo_keywords`,
			`m_products_seo_description`,
			`slug`,
			`products_attributes_groups_id`
			) 
			VALUES
			 (
			 '$id',
			 '$m_products_contragents_id',
			  '$m_products_name',
			  '$m_products_name',
			  '$m_products_unit',
			  '$m_products_price_general',
			  '$m_products_price_currency',
			  '$m_products_multiplicity',
			  '$m_products_show_site',
			  '$m_products_show_price',
			  '$m_products_links',
			  '$m_products_exist',
			  '$m_products_foto',
			  '$m_products_seo_title',
			  '$m_products_seo_keywords',
				'$m_products_seo_description',
				'$slug',
				`unit_weight`='$unit_weight',
				`unit_height`='$unit_height',
				`unit_length`='$unit_length',
				`unit_width`='$unit_width',
				'$products_attributes_groups_id'
			 );"; 

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

	//не изменено копирование категорий должна вылетать ошибка
	public static function products_change(){
		global $sql,$e,$user,$info;

		$data['id']=array(1,null,null,10,1);
		$data['m_products_name']=array(1,null,180);
		$data['m_products_unit']=array(1,null,3,null,1);
		$data['m_products_unit_volume']=array(null,null,18);
		$data['m_products_price_general']=array(1,null,18);
		$data['m_products_price_currency']=array(1,null,null,1,1);
		$data['m_products_miltiplicity']=array(null,null,18);
		$data['selected_categories_id[]']=array(1);
		$data['m_products_links[]']=array();

		$data['idfoto[]']=array();
		$data['m_products_foto_main[]']=array();

		$data['m_products_prices_limit_count[]']=array(null,null,18,null,1);
		$data['m_products_prices_limit_price[]']=array(null,null,18,null,1);
		$data['m_products_prices_price[]']=array(null,null,18,null,1);

		$data['m_products_contragents_id']=array(1,null,null,10,1);
		$data['m_products_show_site']=array(null,null,3);
		$data['m_products_show_price']=array(null,null,3);
		$data['m_products_exist']=array(null,null,3);
		$data['m_products_desc']=array(null,null,65000);

		$data['slug']=array(1,null,255);
		
		array_walk($data,'check');

		$data['products_attributes_groups_id'] =  $_REQUEST['products_attributes_groups_id'];
		$data['m_products_attributes_list_id[]'] = $_REQUEST['m_products_attributes_list_id'];
		$data['m_products_attributes_value[]'] = $_REQUEST['m_products_attributes_value'];
		//аттрибут интервал
		$data['attribute_value_interval_min[]'] =  $_REQUEST['attribute_value_interval_min'];
		$data['attribute_value_interval_max[]'] =  $_REQUEST['attribute_value_interval_max'];

		$data['attribute_value_file[]'] =  $_REQUEST['attribute_value_file'];
		$data['unit_weight'] = $_REQUEST['unit_weight'];
		$data['unit_height'] = $_REQUEST['unit_height'];
		$data['unit_length'] = $_REQUEST['unit_length'];
		$data['unit_width'] = $_REQUEST['unit_width'];

		if(!$e){
			$data['m_products_show_site']=$data['m_products_show_site']?1:0;
			$data['m_products_show_price']=$data['m_products_show_price']?1:0;
			$data['m_products_exist']=$data['m_products_exist']?1:0;
			$data['m_products_miltiplicity']=$data['m_products_miltiplicity']?$data['m_products_miltiplicity']:1;
			$data['m_products_date']=$data['m_products_update']=dt();

			$id =$data['id'];
			$m_products_desc = transform::typography($data['m_products_desc']);

			//удаляем привязанные категории к продукту
			$q='DELETE FROM `formetoo_main`.`m_products_category` WHERE `product_id`=\''.$data['id'].'\';';
			if(!($sql->query($q))){
				elogs();
			}
			
			//добавляем привязанные категории к продукту
			$q='INSERT INTO `formetoo_main`.`m_products_category` (`product_id`,`category_id`) VALUES ';
			for($i=0;$i<count($data['selected_categories_id[]']);$i++){
				$q.='(\''.$data['id'].'\', \''.$data['selected_categories_id[]'][$i].'\'), ';
			}
			$q = mb_substr($q, 0, -2);
				
			if(!($sql->query($q))){
				elogs();
			}

			//добавляем атрибуты
			$sql->query('DELETE FROM `formetoo_main`.`m_products_attributes` WHERE `m_products_attributes_product_id`='.$data['id'].';');
			if(isset($data['m_products_attributes_value[]'])){
				foreach($data['m_products_attributes_value[]'] as $key => $value) {
					foreach($value as $keyAttr => $valueAttr) {
					if ($valueAttr) {
						$valuesArray[] = '(
							\''.$data['id'].'\', 
							\''.$data['m_products_attributes_list_id[]'][$key][0].'\', 
							\''.$valueAttr.'\'
						)';
						}
					}
				}

				foreach($data['attribute_value_interval_min[]'] as $key => $value) {
					foreach($value as $keyAttr => $valueAttr) {
						$valuesArray[] = '(
							\''.$data['id'].'\', 
							\''.$data['m_products_attributes_list_id[]'][$key][0].'\', 
							\''.$valueAttr.'|'.$data['attribute_value_interval_max[]'][$key][0].'\'
						)';
					}
				}
				//добавляем файлы аттрибутов
				if (!empty($data['attribute_value_file[]'])) {
					foreach($data['attribute_value_file[]'] as $keyFileAttr => $valueFileAttr) {

						$files=array();
						foreach($valueFileAttr['file'] as $valueFileAttrKey => $valueFileAttrValue) {
							if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/uploads/files/products/'.$data['id']))
									mkdir($_SERVER['DOCUMENT_ROOT'].'/uploads/files/products/'.$data['id'], 0777, true);

							//копируем только добавленные файлы
							if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/uploads/files/products/'.$data['id'].'/'.$files[$valueFileAttrKey]['file'])){
								copy($_SERVER['DOCUMENT_ROOT'].'/temp/uploads/'.$user->getInfo().'/'.$files[$valueFileAttrKey]['file'], $_SERVER['DOCUMENT_ROOT'].'/uploads/files/products/'.$data['id'].'/'.$files[$valueFileAttrKey]['file']);
							}

							$files[$valueFileAttrKey]['file'] = $valueFileAttrValue;
							$files[$valueFileAttrKey]['name'] = $valueFileAttr['name'][$valueFileAttrKey];
							$files[$valueFileAttrKey]['size'] = self::get_filesize($_SERVER['DOCUMENT_ROOT'].'/uploads/files/products/'.$data['id'].'/'.$files[$valueFileAttrKey]['file']);
						}

						$files=json_encode($files,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
						$valuesArray[] = '(
							\''.$data['id'].'\', 
							\''.$data['m_products_attributes_list_id[]'][$keyFileAttr][0].'\', 
							\''.$files.'\'
						)';
					}
				}
				
				$q='INSERT INTO `formetoo_main`.`m_products_attributes` (`m_products_attributes_product_id`,`m_products_attributes_list_id`,`m_products_attributes_value`) VALUES '. implode(',', $valuesArray);
				
				if(!($sql->query($q)))
					elogs();
			}

			//добавляем скидки
			$sql->query('DELETE FROM `formetoo_main`.`m_products_prices` WHERE `m_products_prices_product_id`='.$data['id'].';');
			if($data['m_products_prices_price[]'][0]!=''&&$count=sizeof($data['m_products_prices_price[]'])){
				$q='INSERT INTO `formetoo_main`.`m_products_prices` (`m_products_prices_product_id`,`m_products_prices_limit_count`,`m_products_prices_limit_price`,`m_products_prices_price`) VALUES ';
				for($i=0;$i<$count;$i++)
					if($data['m_products_prices_price[]'][$i])
						$q.='(
							'.$data['id'].',
							\''.(float)str_replace(array(' ',','),array('','.'),$data['m_products_prices_limit_count[]'][$i]).'\',
							\''.(float)str_replace(array(' ',','),array('','.'),$data['m_products_prices_limit_price[]'][$i]).'\',
							\''.(float)str_replace(array(' ',','),array('','.'),$data['m_products_prices_price[]'][$i]).'\'
						),';
				if(!($sql->query(substr($q,0,-1).';')))
					elogs();
			}

			//добавляем описание товара
			if($data['m_products_desc']){
				$sql->query("DELETE FROM `formetoo_main`.`m_products_desc` WHERE `m_products_desc_id`='$id';");
				$q="INSERT INTO `formetoo_main`.`m_products_desc` (`m_products_desc_id`,`m_products_desc_text`) VALUES ('$id','$m_products_desc');";
				if(!($sql->query($q)))
					elogs();
			}

			//добавляем фото
			if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/images/products/'.$data['id']))
				mkdir($_SERVER['DOCUMENT_ROOT'].'/images/products/'.$data['id']);
			$foto=array();
			if($data['idfoto[]'])
				foreach($data['idfoto[]'] as $k=>$v){
					$array = explode('.', $v);
					$nameFile = $array[0];
					$ext = end($array);
					$foto[$v]['file'] = $nameFile;
					$foto[$v]['ext'] = $ext;
					$foto[$v]['main']=isset($data['m_products_foto_main[]'][0])&&$data['m_products_foto_main[]'][0]==$v?1:0;
					//копируем только добавленные фотки
					if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/images/products/'.$data['id'].'/'.$nameFile.'_min.'.$ext)){
						copy($_SERVER['DOCUMENT_ROOT'].'/temp/uploads/'.$user->getInfo().'/'.$nameFile.'_max.'.$ext,$_SERVER['DOCUMENT_ROOT'].'/images/products/'.$data['id'].'/'.$nameFile.'_max.'.$ext);
						copy($_SERVER['DOCUMENT_ROOT'].'/temp/uploads/'.$user->getInfo().'/'.$nameFile.'_min.'.$ext,$_SERVER['DOCUMENT_ROOT'].'/images/products/'.$data['id'].'/'.$nameFile.'_min.'.$ext);
						copy($_SERVER['DOCUMENT_ROOT'].'/temp/uploads/'.$user->getInfo().'/'.$nameFile.'_med.'.$ext,$_SERVER['DOCUMENT_ROOT'].'/images/products/'.$data['id'].'/'.$nameFile.'_med.'.$ext);
					}
				}

			$foto=json_encode($foto,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

			$m_products_contragents_id = $data['m_products_contragents_id'];
			$m_products_name = $data['m_products_name'];
			$m_products_unit = $data['m_products_unit'];
			$m_products_price_general = (float)str_replace(array(' ',','),array('','.'),$data['m_products_price_general']);
			$m_products_price_currency = $data['m_products_price_currency'];
			$m_products_links = $data['m_products_links[]']?implode('|',$data['m_products_links[]']):'';
			$m_products_show_site = $data['m_products_show_site'];
			$m_products_show_price = $data['m_products_show_price'];
			$m_products_multiplicity = $data['m_products_miltiplicity'];
			$m_products_exist = $data['m_products_exist'];
			$m_products_foto = $foto;
			$m_products_date = 	$data['m_products_date'];
			$m_products_update = $data['m_products_update'];
			$slug = $data['slug'];
			$products_attributes_groups_id = $data['products_attributes_groups_id'];
			$unit_weight = str_replace(array(' ',','), array('','.'), $data['unit_weight']);
			$unit_height = str_replace(array(' ',','), array('','.'), $data['unit_height']);
			$unit_length = str_replace(array(' ',','), array('','.'), $data['unit_length']);
			$unit_width = str_replace(array(' ',','), array('','.'), $data['unit_width']);

			if(!empty($_POST['seo_parameters'][0])){
				$m_products_seo_title = $_POST['seo_parameters'][0];
			} else {
				$m_products_seo_title = $m_products_name;
			}

			if(!empty($_POST['seo_parameters'][1])){
				$m_products_seo_keywords = $_POST['seo_parameters'][1];
			} else {
				$m_products_seo_keywords = $m_products_name;
			}

			if(!empty($_POST['seo_parameters'][2])){
				$m_products_seo_description = $_POST['seo_parameters'][2];
			} else {
				$m_products_seo_description = $m_products_name;
			}

			$q="UPDATE `formetoo_main`.`m_products` SET 
				`m_products_contragents_id`= '$m_products_contragents_id',
				`m_products_name` = '$m_products_name',
				`m_products_unit` ='$m_products_unit',
				`m_products_price_general`='$m_products_price_general',
				`m_products_price_currency`='$m_products_price_currency',
				`m_products_links`='$m_products_links',
				`m_products_show_site`='$m_products_show_site',
				`m_products_show_price`='$m_products_show_price',
				`m_products_multiplicity`='$m_products_multiplicity',
				`m_products_exist`='$m_products_exist',
				`m_products_foto`='$m_products_foto',
				`m_products_date`= '$m_products_date',
				`m_products_update`='$m_products_update',
				`m_products_seo_title`='$m_products_seo_title',
				`m_products_seo_keywords`='$m_products_seo_keywords',
				`m_products_seo_description`='$m_products_seo_description',
				`slug`='$slug',
				`unit_weight`='$unit_weight',
				`unit_height`='$unit_height',
				`unit_length`='$unit_length',
				`unit_width`='$unit_width',
				`products_attributes_groups_id`='$products_attributes_groups_id'
				WHERE `id`='$id';";				

			if($sql->query($q))
				header('Location: '.url().'?success&action=change&id='.$data['id']);
			else{
				elogs();
				header('Location: '.url().'?error&action=change&id='.$data['id']);
			}
		}
		else{
			elogs();
			header('Location: '.url().'?error');
		}
		exit;
	}
	private static function get_filesize($file){
		$f_size=filesize($file);
		$f_size=($f_size)?$f_size:0;
		if ($f_size>1048576)
			$f_size=round($f_size/1048576,1).' МБ';
		else
			$f_size=round($f_size/1024,1).' КБ';
		return $f_size;
	}

	public static function products_group_change() {
		global $sql,$e;
		$data['group_id[]']=array();
		$data['m_products_unit']=array(null,null,3,null,1);
		$data['on_m_products_unit']=array(null,null,3);
		$data['m_products_unit_weight']=array(null,null,18);
		$data['on_m_products_unit_weight']=array(null,null,3);
		$data['m_products_unit_volume']=array(null,null,18);
		$data['on_m_products_unit_volume']=array(null,null,3);
		$data['m_products_price_general']=array(null,null,18);
		$data['on_m_products_price_general']=array(null,null,3);
		$data['id[]']=array(null);
		$data['on_id']=array(null,null,3);
		$data['m_products_links[]']=array();
		$data['on_m_products_links']=array(null,null,3);
		$data['m_products_contragents_id']=array(null,null,null,10,1);
		$data['on_m_products_contragents_id']=array(null,null,3);
		$data['m_products_show_site']=array(null,null,3);
		$data['on_m_products_show_site']=array(null,null,3);
		$data['m_products_show_price']=array(null,null,3);
		$data['on_m_products_show_price']=array(null,null,3);

		array_walk($data,'check');

		if(!$e){
			$data['m_products_show_site']=$data['m_products_show_site']?1:0;
			$data['m_products_show_price']=$data['m_products_show_price']?1:0;
			$data['m_products_update']=dt();

			$q='UPDATE `formetoo_main`.`m_products` SET
				'.($data['on_m_products_contragents_id']&&$data['m_products_contragents_id']?'`m_products_contragents_id`='.$data['m_products_contragents_id'].',':'').'
				'.($data['on_m_products_unit']&&$data['m_products_unit']?'`m_products_unit`='.$data['m_products_unit'].',':'').'
				'.($data['on_m_products_unit_weight']&&$data['m_products_unit_weight']?'`m_products_unit_weight`='.(float)str_replace(array(' ',','),array('','.'),$data['m_products_unit_weight']).',':'').'
				'.($data['on_m_products_unit_volume']&&$data['m_products_unit_volume']?'`m_products_unit_volume`='.(float)str_replace(array(' ',','),array('','.'),$data['m_products_unit_volume']).',':'').'
				'.($data['on_m_products_price_general']&&$data['m_products_price_general']?'`m_products_price_general`=`m_products_price_general`'.transform::sum_change($data['m_products_price_general']).',':'').'
				'.($data['on_id']&&$data['id[]']?'`id`=\''.implode('|',$data['id[]']).'\',':'').'
				'.($data['on_m_products_links']?'`m_products_links`=\''.($data['m_products_links[]']?implode('|',$data['m_products_links[]']):'').'\',':'').'
				'.($data['on_m_products_show_site']?'`m_products_show_site`='.$data['m_products_show_site'].',':'').'
				'.($data['on_m_products_show_price']?'`m_products_show_price`='.$data['m_products_show_price'].',':'').'
				`m_products_update`=\''.$data['m_products_update'].'\'
				WHERE `id` IN (0,'.implode(',',$data['group_id[]']).');';
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

	public static function products_copy($id){
		global $sql;

		//достаем продукт
		$q = "SELECT * FROM `formetoo_main`.`m_products` WHERE `id` = '$id';";

		if(!($product = $sql->query($q))){
			elogs();
		}

		//достаем скидки
		$q = "SELECT * FROM `formetoo_main`.`m_products_prices` WHERE `m_products_prices_product_id` = '$id';";

		if(!($discount = $sql->query($q))){
			elogs();
		}

		//достаем атрибуты
		$q = "SELECT * FROM `formetoo_main`.`m_products_attributes` WHERE `m_products_attributes_product_id` = '$id';";

		if(!($attr = $sql->query($q))){
			elogs();
		}

		//достаем описание
		$q = "SELECT * FROM `formetoo_main`.`m_products_desc` WHERE `m_products_desc_id` = '$id';";

		if(!($description = $sql->query($q))){
			elogs();
		}

		//id
		$id = get_id('m_products');

		//копируем скидки
		if(!empty($discount)){

			$m_products_prices_limit_count = $discount[0]['m_products_prices_limit_count'];
			$m_products_prices_limit_price = $discount[0]['m_products_prices_limit_price'];
			$m_products_prices_price = $discount[0]['m_products_prices_price'];

			$q='INSERT INTO `formetoo_main`.`m_products_prices` (`m_products_prices_product_id`,`m_products_prices_limit_count`,`m_products_prices_limit_price`,`m_products_prices_price`) VALUES ';
			$q.="(
						'$id',
						'$m_products_prices_limit_count',
						'$m_products_prices_limit_price',							
						'$m_products_prices_price'	
						);";
			if(!($sql->query($q))){
				elogs();
			}
		}

		//копируем атрибуты
		if(!empty($attr)){
			for($i=0;$i<count($attr);$i++){
				$m_products_attributes_list_id = $attr[$i]['m_products_attributes_list_id'];
				$m_products_attributes_value = $attr[$i]['m_products_attributes_value'];

				$q='INSERT INTO `formetoo_main`.`m_products_attributes` (`m_products_attributes_product_id`,`m_products_attributes_list_id`,`m_products_attributes_value`) VALUES ';
				$q.="(
							'$id',
							'$m_products_attributes_list_id',
							'$m_products_attributes_value'
						);";
				if(!($sql->query($q)))
					elogs();
			}
		}

		//копируем описание товара
		if(!empty($description)){

			$m_products_desc = $description[0]['m_products_desc_text'];

			$q="INSERT INTO `formetoo_main`.`m_products_desc` (`m_products_desc_id`,`m_products_desc_text`) VALUES ('$id','$m_products_desc');";
			if(!($sql->query($q)))
				elogs();
		}


		//копируем фото
		if(!empty($product[0]['m_products_foto'])){
			if(file_exists($_SERVER['DOCUMENT_ROOT'].'/images/products/'.$id)){

				mkdir($_SERVER['DOCUMENT_ROOT'].'/images/products/'.$id);

				$param = json_decode($product[0]['m_products_foto']);

				foreach($param as $item){

					copy($_SERVER['DOCUMENT_ROOT'].'/images/products/'.$id.'/'.$item->file.'_max.jpg',$_SERVER['DOCUMENT_ROOT'].'/images/products/'.$id.'/'.$item->file.'_max.jpg');
					copy($_SERVER['DOCUMENT_ROOT'].'/images/products/'.$id.'/'.$item->file.'_min.jpg',$_SERVER['DOCUMENT_ROOT'].'/images/products/'.$id.'/'.$item->file.'_min.jpg');
					copy($_SERVER['DOCUMENT_ROOT'].'/images/products/'.$id.'/'.$item->file.'_med.jpg',$_SERVER['DOCUMENT_ROOT'].'/images/products/'.$id.'/'.$item->file.'_med.jpg');
				}

			}
		}



		//копируем продукт
		$m_products_contragents_id = $product[0]['m_products_contragents_id'];
		$m_products_name = $product[0]['m_products_name'] . '_COPY';
		$m_products_unit = $product[0]['m_products_unit'];
		$m_products_price_general = $product[0]['m_products_price_general'];
		$m_products_price_currency = $product[0]['m_products_price_currency'];
		$m_products_multiplicity = $product[0]['m_products_multiplicity'];
		$m_products_show_site = $product[0]['m_products_show_site'];
		$m_products_show_price = $product[0]['m_products_show_price'];
		$m_products_links = $product[0]['m_products_links'];
		$m_products_exist = $product[0]['m_products_exist'];
		$m_products_foto = $product[0]['m_products_foto'];
		$m_products_seo_title = $product[0]['m_products_seo_title'];
		$m_products_seo_keywords = $product[0]['m_products_seo_keywords'];
		$m_products_seo_description = $product[0]['m_products_seo_description'];

		$q = "INSERT INTO `formetoo_main`.`m_products`(
			`id`,
			`m_products_contragents_id`,
			`m_products_name`,
			`m_products_name_full`,
			`m_products_unit`,
			`m_products_price_general`,
			`m_products_price_currency`,
			`m_products_multiplicity`,
			`m_products_show_site`,
			`m_products_show_price`,
			`m_products_links`,
			`m_products_exist`,
			`m_products_foto`,
			`m_products_seo_title`,
			`m_products_seo_keywords`,
			`m_products_seo_description`
			) 
			VALUES (
			  '$id',
			 ' $m_products_contragents_id',
			  '$m_products_name',
			  '$m_products_name',
			  '$m_products_unit',
			  '$m_products_price_general',
			  '$m_products_price_currency',
			  '$m_products_multiplicity',
			  '$m_products_show_site',
			  '$m_products_show_price',
			  '$m_products_links',
			  '$m_products_exist',
			  '$m_products_foto',
			  '$m_products_seo_title',
			  '$m_products_seo_keywords',
			  '$m_products_seo_description'		  
			 );";

		if(!($sql->query($q))) {
			elogs();
		}
		
		//находим привязанные категории к продукту
		$q='SELECT `category_id` FROM `formetoo_main`.`m_products_category` WHERE `product_id`=\''.$id.'\';';
		if($resCategories = $sql->query($q)){
			//добавляем привязанные категории к продукту
			$q='INSERT INTO `formetoo_main`.`m_products_category` (`product_id`,`category_id`) VALUES ';
			foreach ($resCategories as $resCategory) {
				$q.='(\''.$id.'\', \''.$resCategory["category_id"].'\'), ';
			}
			$q = mb_substr($q, 0, -2);
				
			if(!($sql->query($q))){
				elogs();
			}
		} else {
			elogs();
		}

		return true;
	}




//	public static function products_copy(){
//	global $user,$services,$contragents,$info,$documents,$sql,$e,$products;
//
//		$data['id']=array(1,null,null,10,1);
//
//		array_walk($data,'check',true);
//
//		if(!$e){
//			$product=$products->products_id[$data['id']][0];
//			$prices=$products->products_price[$product['id']];
//			$attrs=$products->products_attr[$product['id']];
//
//			$data['id']=get_id('m_products');
//
//			//добавляем скидки
//			if($prices){
//				$q='INSERT INTO `formetoo_main`.`m_products_prices` (`m_products_prices_product_id`,`m_products_prices_limit_count`,`m_products_prices_limit_price`,`m_products_prices_price`) VALUES ';
//				foreach($prices as $_price)
//					$q.='(
//						'.$data['id'].',
//						\''.$_price['m_products_prices_limit_count'].'\',
//						\''.$_price['m_products_prices_limit_price'].'\',
//						\''.$_price['m_products_prices_price'].'\'
//					),';
//				if(!($sql->query(substr($q,0,-1).';')))
//					elogs();
//			}
//
//			//добавляем атрибуты
//			if($attrs){
//				$q='INSERT INTO `formetoo_main`.`m_products_attributes` (`m_products_attributes_product_id`,`m_products_attributes_list_id`,`m_products_attributes_value`) VALUES ';
//				foreach($attrs as $_attr)
//					$q.='(
//						\''.$data['id'].'\',
//						\''.$_attr['m_products_attributes_list_id'].'\',
//						\''.$_attr['m_products_attributes_value'].'\'
//					),';
//				if(!($sql->query(substr($q,0,-1).';')))
//					elogs();
//			}
//
//			//добавляем фото
//			mkdir($_SERVER['DOCUMENT_ROOT'].'/images/products/'.$data['id']);
//			$foto=json_decode($product['m_products_foto'],true);
//			if($foto)
//				foreach($foto as $_foto){
//					//копируем фотки
//					if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/images/products/'.$data['id'].'/'.$v.'_m.jpg')){
//						//1200
//						copy($_SERVER['DOCUMENT_ROOT'].'/images/products/'.$product['id'].'/'.$_foto['file'].'_b.jpg',$_SERVER['DOCUMENT_ROOT'].'/images/products/'.$data['id'].'/'.$_foto['file'].'_b.jpg');
//						//200
//						copy($_SERVER['DOCUMENT_ROOT'].'/images/products/'.$product['id'].'/'.$_foto['file'].'_m.jpg',$_SERVER['DOCUMENT_ROOT'].'/images/products/'.$data['id'].'/'.$_foto['file'].'_m.jpg');
//					}
//				}
//
//			$q='INSERT `formetoo_main`.`m_products` SET
//				`id`='.$data['id'].',
//				`m_products_contragents_id`='.$product['m_products_contragents_id'].',
//				`m_products_name`=\''.$product['m_products_name'].'\',
//				`m_products_unit`='.$product['m_products_unit'].',
//				`m_products_price_general`='.$product['m_products_price_general'].',
//				`m_products_price_currency`='.$product['m_products_price_currency'].',
//				`id`=\''.$product['id'].'\',
//				`m_products_links`=\''.$product['m_products_links'].'\',
//				`m_products_show_site`='.$product['m_products_show_site'].',
//				`m_products_show_price`='.$product['m_products_show_price'].',
//				`m_products_miltiplicity`='.$product['m_products_miltiplicity'].',
//				`m_products_exist`='.$product['m_products_exist'].',
//				`m_products_desc`=\''.$product['m_products_desc'].'\',
//				`m_products_foto`=\''.$product['m_products_foto'].'\',
//				`m_products_date`=\''.dt().'\',
//				`m_products_update`=\''.$product['m_products_update'].'\';';
//
//			if($sql->query($q)){
//				header('Location: /companies/products/new/?action=change&id='.$data['id'].'&copy_success');
//			}
//			else{
//				header('Location: '.url().'error');
//			}
//
//		}
//		else{
//			elogs();
//			header('Location: '.url().'?error');
//		}
//	}

	public static function products_attributes_list_add(){
		global $sql,$e;
		$data['m_products_attributes_list_name']=array(1,null,200);
		$data['m_products_attributes_list_name_url']=array(null,null,100);
		$data['m_products_attributes_list_type']=array();
		$data['m_products_attributes_list_unit']=array(null,null,80);
		$data['m_products_attributes_list_hint']=array(null,null,100000);
		$data['m_products_attributes_list_comment']=array(null,null,500);
		$data['m_products_attributes_list_required']=array(null,null,3);
		$data['is_multiply']=array(null,null,3);
		$data['m_products_attributes_list_site_search']=array(null,null,3);
		$data['m_products_attributes_list_site_filter']=array(null,null,3);
		$data['m_products_attributes_list_site_open']=array(null,null,3);
		$data['is_active']=array(null,null,3);

		array_walk($data,'check');

		if(!$e){
			$data['m_products_attributes_list_id']=get_id('m_products_attributes_list');
			$data['m_products_attributes_list_required']=$data['m_products_attributes_list_required']?1:0;
			$data['is_multiply']=$data['is_multiply']?1:0;
			$data['m_products_attributes_list_site_search']=$data['m_products_attributes_list_site_search']?1:0;
			$data['m_products_attributes_list_site_filter']=$data['m_products_attributes_list_site_filter']?1:0;
			$data['m_products_attributes_list_site_open']=$data['m_products_attributes_list_site_open']?1:0;
			$data['is_active']=$data['is_active']?1:0;
			$data['m_products_attributes_list_name_url']=$data['m_products_attributes_list_name_url']?$data['m_products_attributes_list_name_url']:transform::translit($data['m_products_attributes_list_name']);


			$q='INSERT `formetoo_main`.`m_products_attributes_list` SET 
				`m_products_attributes_list_id`='.$data['m_products_attributes_list_id'].',
				`m_products_attributes_list_name`=\''.$data['m_products_attributes_list_name'].'\',
				`m_products_attributes_list_name_url`=\''.$data['m_products_attributes_list_name_url'].'\',
				`m_products_attributes_list_type`=\''.$data['m_products_attributes_list_type'].'\',
				`m_products_attributes_list_unit`=\''.$data['m_products_attributes_list_unit'].'\',
				`m_products_attributes_list_required`='.$data['m_products_attributes_list_required'].',
				`is_multiply`='.$data['is_multiply'].',
				`m_products_attributes_list_site_search`='.$data['m_products_attributes_list_site_search'].',
				`m_products_attributes_list_site_filter`='.$data['m_products_attributes_list_site_filter'].',
				`m_products_attributes_list_site_open`='.$data['m_products_attributes_list_site_open'].',
				`is_active`='.$data['is_active'].',
				`m_products_attributes_list_hint`=\''.$data['m_products_attributes_list_hint'].'\',
				`m_products_attributes_list_comment`=\''.$data['m_products_attributes_list_comment'].'\';';

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

	public static function products_attributes_list_change(){
		global $sql,$e;
		$data['m_products_attributes_list_id']=array(1,null,null,10,1);
		$data['m_products_attributes_list_name']=array(1,null,200);
		$data['m_products_attributes_list_name_url']=array(null,null,100);
		$data['m_products_attributes_list_type']=array();
		$data['m_products_attributes_list_unit']=array(null,null,80);
		$data['m_products_attributes_list_hint']=array(null,null,100000);
		$data['m_products_attributes_list_comment']=array(null,null,500);
		$data['m_products_attributes_list_required']=array(null,null,3);
		$data['is_multiply']=array(null,null,3);
		$data['m_products_attributes_list_site_search']=array(null,null,3);
		$data['m_products_attributes_list_site_filter']=array(null,null,3);
		$data['m_products_attributes_list_site_open']=array(null,null,3);
		$data['is_active']=array(null,null,3);
		$data['property_values_default']=array();

		array_walk($data,'check');
		$data['property_values']=$_REQUEST['property_values'];

		if(!$e){
			$data['m_products_attributes_list_required']=$data['m_products_attributes_list_required']?1:0;
			$data['is_multiply']=$data['is_multiply']?1:0;
			$data['m_products_attributes_list_site_search']=$data['m_products_attributes_list_site_search']?1:0;
			$data['m_products_attributes_list_site_filter']=$data['m_products_attributes_list_site_filter']?1:0;
			$data['m_products_attributes_list_site_open']=$data['m_products_attributes_list_site_open']?1:0;
			$data['is_active']=$data['is_active']?1:0;
			$data['m_products_attributes_list_name_url']=$data['m_products_attributes_list_name_url']?$data['m_products_attributes_list_name_url']:transform::translit($data['m_products_attributes_list_name']);

			if ($data['property_values']) {
				foreach($data['property_values'] as $key => $value) {
					if($value && $value['value']) {
						if (is_numeric($key)) {
							$ids[] = $key;
							
							$q='UPDATE `formetoo_main`.`m_attributes_enum` 
								SET `value`=\''.$value['value'].'\', `sort`=\''.($value['sort'] ? $value['sort'] : 500).'\', `default`='.($data['property_values_default'] == $key ? 1 : 0).' 
							WHERE `id`='.$key.';';
							$sql->query($q);
						} else {
							$add[] = '( 
								\''.$data['m_products_attributes_list_id'].'\', 
								\''.$value['value'].'\', 
								\''.($data['property_values_default'] == $key ? 1 : 0).'\', 
								\''.($value['sort'] ? $value['sort'] : 500).'\'
							)';
						}
					}
				}
				if (!empty($ids)) {
					$q = 'DELETE FROM `formetoo_main`.`m_attributes_enum` WHERE `id` NOT IN ('.implode(', ', $ids).') AND `attribute_id`=\''.$data['m_products_attributes_list_id'].'\';';
					$sql->query($q);
				}
			
				if (!empty($add)) {
					$q = 'INSERT INTO `formetoo_main`.`m_attributes_enum` (`attribute_id`,`value`,`default`, `sort`) VALUES '.implode(',', $add).';';
					$sql->query($q);
				}
			}
			
			$q='UPDATE `formetoo_main`.`m_products_attributes_list` SET
				`m_products_attributes_list_id`='.$data['m_products_attributes_list_id'].',
				`m_products_attributes_list_name`=\''.$data['m_products_attributes_list_name'].'\',
				`m_products_attributes_list_name_url`=\''.$data['m_products_attributes_list_name_url'].'\',
				`m_products_attributes_list_type`=\''.$data['m_products_attributes_list_type'].'\',
				`m_products_attributes_list_unit`=\''.$data['m_products_attributes_list_unit'].'\',
				`m_products_attributes_list_required`='.$data['m_products_attributes_list_required'].',
				`is_multiply`='.$data['is_multiply'].',
				`m_products_attributes_list_site_search`='.$data['m_products_attributes_list_site_search'].',
				`m_products_attributes_list_site_filter`='.$data['m_products_attributes_list_site_filter'].',
				`m_products_attributes_list_site_open`='.$data['m_products_attributes_list_site_open'].',
				`is_active`='.$data['is_active'].',
				`m_products_attributes_list_hint`=\''.$data['m_products_attributes_list_hint'].'\',
				`m_products_attributes_list_comment`=\''.$data['m_products_attributes_list_comment'].'\'
				WHERE `m_products_attributes_list_id`='.$data['m_products_attributes_list_id'].' LIMIT 1;';
			//var_dump($q);
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

	public static function m_products_attributes_groups_add(){
		global $sql,$e;
		$data['m_products_attributes_groups_name']=array(1,null,200);
		$data['m_products_attributes_groups_list_id[]']=array(1);
		$data['m_products_attributes_groups_required']=array(null,null,3);

		array_walk($data,'check');

		if(!$e){
			$data['products_attributes_groups_id']=get_id('products_attributes_groups_id');
			$data['m_products_attributes_groups_list_id[]']=implode('|',$data['m_products_attributes_groups_list_id[]']);
			$data['m_products_attributes_groups_required'] = $data['m_products_attributes_groups_required']?1:0;

			$q='INSERT `formetoo_main`.`m_products_attributes_groups` SET
				`products_attributes_groups_id`='.$data['products_attributes_groups_id'].',
				`m_products_attributes_groups_name`=\''.$data['m_products_attributes_groups_name'].'\',
				`m_products_attributes_groups_list_id`=\''.$data['m_products_attributes_groups_list_id[]'].'\',
				`m_products_attributes_groups_required`=\''.$data['m_products_attributes_groups_required'].'\';';

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

	public static function m_products_attributes_groups_change(){
		global $sql,$e;
		$data['products_attributes_groups_id']=array(1,null,null,10,1);
		$data['m_products_attributes_groups_name']=array(1,null,200);
		$data['m_products_attributes_groups_list_id[]']=array(1);
		$data['m_products_attributes_groups_required']=array(null,null,3);

		array_walk($data,'check');

		if(!$e){
			$data['m_products_attributes_groups_list_id[]']=implode('|',$data['m_products_attributes_groups_list_id[]']);
			$data['m_products_attributes_groups_required'] = $data['m_products_attributes_groups_required']?1:0;

			$q='UPDATE `formetoo_main`.`m_products_attributes_groups` SET
				`m_products_attributes_groups_name`=\''.$data['m_products_attributes_groups_name'].'\',
				`m_products_attributes_groups_list_id`=\''.$data['m_products_attributes_groups_list_id[]'].'\',
				`m_products_attributes_groups_required`=\''.$data['m_products_attributes_groups_required'].'\'
				WHERE `products_attributes_groups_id`='.$data['products_attributes_groups_id'].';';

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
