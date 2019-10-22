<?
class menu{
	public
		$current,
		$current_parents=array(),
		$nodes,
		$nodes_id,
		$nodes_parent;

	function __construct(){
		global $current,$sql,$user;
		$this->current=$current['m_content_id'];
		$q='SELECT * FROM `formetoo_main`.`m_menu` WHERE `m_menu_active`=1 ORDER BY `m_menu_parent`,`m_menu_order`;';
		$res=$sql->query($q);
		$this->nodes=$res;
		foreach($res as $item)
			$nodes_id[$item['m_menu_id']]=$item;
		$this->nodes_id=$nodes_id;
		foreach($res as $item)
			$nodes_parent[$item['m_menu_parent']][]=$item;
		$this->nodes_parent=$nodes_parent;
		$this->parents($this->current,$this->current_parents);
	}

	public function parent($el,&$nodes){
		foreach($this->nodes_id as $t)
			//если очередная категория является родительской для заданной
			if($t['m_menu_id']==$this->nodes_id[$el]['m_menu_parent']){
				//добавляем ее в массив
				$nodes[]=$t;
				break;
			}
	}

	public function parents($el,&$nodes){
		foreach($this->nodes_id as $t)
			//если текущая категория является родительской для заданной
			if(isset($this->nodes_id[$el])&&$t['m_menu_id']==$this->nodes_id[$el]['m_menu_parent']){
				//добавляем ее в массив
				$nodes[$t['m_menu_id']]=$t;
				//ищем родительскую категорию для найденной, если нужно найти все родительские
					$this->parents($t['m_menu_id'],$nodes);
				break;
			}
	}

	public function child($el,&$nodes){
		foreach($this->nodes_id as $t)
			//если текущая категория является дочерней для заданной
			if($t['m_menu_parent']==$el){
				//добавляем ее в массив
				$nodes[]=$t;
			}
	}

	public function childs($el,&$nodes,$tab=0,$level=0){
		foreach($this->nodes_id as $t)
			//если текущая категория является дочерней для заданной
			if ($t['m_menu_parent']==$el){
				//добавляем ее в массив
				if($tab===1)
					for($i=0;$i<$level;$i++)
						$t['m_menu_name']='&nbsp;&nbsp;&nbsp;&nbsp;'.$t['m_menu_name'];
				elseif($tab===2){
					$parents=array();
					$this->parents($t['m_menu_id'],$parents);
					$parents=array_reverse($parents);
					$name='';
					foreach($parents as $parents_)
						$name.=$parents_['m_menu_name'].'&nbsp;→&nbsp;';
					$t['m_menu_name']=$name.$t['m_menu_name'];
				}
				$nodes[]=$t;
				//ищем дочернюю категорию для найденной, если нужно найти все дочерние
					$this->childs($t['m_menu_id'],$nodes,$tab,$level+1);
			}
		--$level;
	}

	public function display($type='',$parent=0){
		global $menu;
		echo '<ul>';
		//перебираем дочерние пункты текущего пункта
		foreach($this->nodes_parent[$parent] as $nodes_parent__){
			//если пункт меню первый - переходим к следующему (первая ссылка прописывается отдельно)
			if($nodes_parent__['m_menu_id']==1) continue;
			//находим всех родителей текущего дочернего пункта и строим путь ссылки
			$parents=array();
			$this->parents($nodes_parent__['m_menu_id'],$parents);
			$parents=array_reverse($parents);
			$url='';
			foreach($parents as $parents_)
				$url.=$parents_['m_menu_url'].'/';
			//выводим пункт меню
			echo '<li'.($nodes_parent__['m_menu_id']==$this->current?' class="active"':'').'>',
					'<a href="'.($nodes_parent__['m_menu_no_link']?'#':('/'.$url.$nodes_parent__['m_menu_url'].($nodes_parent__['m_menu_url']?'/':''))).'" title="'.$nodes_parent__['m_menu_name'].'">',
						$nodes_parent__['m_menu_icon']?('<i class="fa fa-lg fa-fw '.$nodes_parent__['m_menu_icon'].'"></i>'):'',
						'<span class="menu-item-parent">'.(mb_strlen($nodes_parent__['m_menu_name'],'utf-8')<=16?$nodes_parent__['m_menu_name']:transform::some($nodes_parent__['m_menu_name'],16,1)).'</span>',
					'</a>',
					//если у текущего дочернего пункта есть подпункты рекурсивно выводим их
					isset($this->nodes_parent[$nodes_parent__['m_menu_id']])?$this->display($type,$nodes_parent__['m_menu_id']):'',
				'</li>';
		}
		echo '</ul>';
	}

	public function breadcrumbs(){
		if (!$this->current) return;

		$this->parents($this->current,$parents);
		if(!$parents)
			echo '<li><a href="/">CRM</a></li><li>'.$this->nodes_id[$this->current]['m_menu_name'].'</li>';
		else{
			$parents_reverse=array_reverse($parents);
			echo '<li><a href="/">CRM</a></li>';
			foreach($parents_reverse as $p){
				echo '<li><a href="'.$p['m_menu_url'].'">'.$p['m_menu_name'].'</a></li>';
			}
			echo '<li>'.$this->nodes_id[$this->current]['m_menu_name'].'</li>';
		}
	}

}
?>
