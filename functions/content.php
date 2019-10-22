<?
defined ('_DSITE') or die ('Access denied');

class content{
	var $js;
	
	public function setJS($code){
		$this->js.=$code;
	}

	public static function search($search){
		global $sql,$current,$menu;
		
		function tag_b($text,$b){
			$text=is_array($b)?preg_replace('/(\w+)?('.implode('|',$b).')(\w+)?/ui','<b>$1$2$3</b>',$text):preg_replace('/(\w+)?('.$b.')(\w+)?/ui','<b>$1$2$3</b>',$text);
			return $text;
		}

		function t_sort($a,$b){
			$a_s=sizeof(explode('<b>',$a));
			$b_s=sizeof(explode('<b>',$b));
			if($a_s==$b_s)
				return 0;
			return ($a_s>$b_s)?-1:1;
		}
		
		function snippets($content,$words,$s_count=4,$envir=5,$envir_type='words'){
			$positions=array();
			$snippets=array();
			$content_size=mb_strlen($content);
			foreach($words as $word){
				$word_size=mb_strlen($word);
				//текущая позиция найденной строки
				$t_pos=0;
				$t=false;
				for($i=0;$i<20;$i++){
					//находим позицию вхождения строки в контенте
					$t=mb_stripos($content,$word,$t_pos,'utf-8');
					//если вхождение есть
					if($t!==false){
						//запоминаем позицию конца слова (чтобы дальше искать с нее) и позицию начала слова
						$t_pos=$t+$word_size;
						$positions[]=$t;
					}
					//если вхождений нет - выходим из цикла
					else
						break;
				}
				//для каждой найденной позиции
				foreach($positions as $pos)
					if($envir_type=='symbols'){
						//стартовый символ
						$start=($pos-$envir)<0?0:$pos-$envir;
						//количество символов
						$count=($start+$word_size+$envir*2)>$content_size?($content_size-1):($word_size+$envir*2);
						//получаем сниппет и разбиваем его в массив по словам
						$snippet=mb_substr($content,$start,$count,'utf-8');
						//разбиваем его на предложения
						$clauses=explode('.',$snippet);
						//разбиваем на слова
						$snippet=explode(' ',$snippet);
						//если запроса нет в первом предложении, удаляем первое предложение
						if($start&&mb_stripos($clauses[0],$word,0,'utf-8')===false&&sizeof($clauses)>1){
							array_shift($clauses);
							$snippet=explode(' ',implode('.',$clauses));
						}
						//если стартовый символ не нулевой - убираем первое слово (чтобы не было обрезков слов)
						elseif($start)
							array_shift($snippet);
						//заново разбиваем получившийся сниппет на предложения
						$clauses=explode('.',implode(' ',$snippet));
						//если запроса нет в последнем предложении, удаляем последнее предложение
						if($count==$word_size+$envir*2&&mb_stripos($clauses[sizeof($clauses)-1],$word,0,'utf-8')===false&&sizeof($clauses)>1){
							array_pop($clauses);
							$snippet=explode(' ',implode('.',$clauses));
						}
						//если количество символов в сниппете берется из параметра кол-ва символов, а не до конца контента, - убираем последнее слово (в другом случае сниппет идет до конца текста)
						elseif($count==$word_size+$envir*2)
							array_pop($snippet);
						//удаляем пустые элементы
						$snippets[]=array_filter($snippet);
					}
					elseif($envir_type=='words'){
						//стартовый символ
						$start=($pos-$envir*15)<0?0:$pos-$envir*15;
						//количество символов
						$count=($start+$word_size+$envir*2*15)>$content_size?($content_size-1):($word_size+$envir*2*15);
						//получаем сниппет
						$snippet=mb_substr($content,$start,$count,'utf-8');
						//разбиваем на слова
						$snippet=explode(' ',$snippet);
						//если стартовый символ не нулевой - убираем первое слово (чтобы не было обрезков слов)
						if($start)
							array_shift($snippet);
						//если количество символов в сниппете берется из параметра кол-ва символов, а не до конца контента, - убираем последнее слово (в другом случае сниппет идет до конца текста)
						if($count==$word_size+$envir*2*15)
							array_pop($snippet);
						//находим номер элемента с ключевым словом в массиве сниппета
						$i=0;
						foreach($snippet as $t_word){
							if(mb_stripos($t_word,$word,0,'utf-8')!==false)
								break;
							$i++;
						}
						//выбираем нужное кол-во слов до и после ключевого слова
						$snippet=array_slice($snippet,(($i-$envir)<0?0:$i-$envir),$envir*2+1);
						//удаляем пустые элементы
						$snippets[]=array_filter($snippet);
					}
			}
			//удаление похожих (>50% совпадений) сниппетов
			$snippets_size=sizeof($snippets);
			for($i=0;$i<$snippets_size;$i++)
				if(isset($snippets[$i]))
					for($j=0;$j<$snippets_size;$j++)
						if(isset($snippets[$i])&&$snippets[$i]&&isset($snippets[$j])&&$snippets[$j]&&$i!=$j)
							//удаляем совпадения 30% и больше
							if((sizeof(array_intersect($snippets[$i],$snippets[$j]))/min(sizeof($snippets[$i]),sizeof($snippets[$j])))>=0.4)
								if($snippets[$i]>=$snippets[$j])
									unset($snippets[$j]);
								else
									unset($snippets[$i]);
			//собираем слова в предложения, выделяя слова запроса
			foreach($snippets as &$snippet)
				$snippet=tag_b(implode(' ',$snippet),$words);
			//сортировка от количества выделенных слов
			usort($snippets,'t_sort');
			return array_slice(array_filter($snippets),0,$s_count);
		}
		
		if(mb_strlen($search,'utf-8')<3||mb_strlen($search,'utf-8')>50){
			$current['m_content_content']='Поисковый запрос должен составлять от 3-х до 50-ти символов.';
			return false;
		}
		$search=preg_replace('/[^a-zA-ZА-Яа-я0-9\s]/ui','',$search);
		//запоминаем фразу для поиска по всем словам сразу
		$search_aw=$search;
		//удаляем из запроса слова меньше 3 символов
		do
			$search=preg_replace('/(^|\s)[а-яА-Я]{1,2}($|\s)/ui',' ',$search,-1,$r_count);
		while($r_count);
		//если после удаления запрос меньше 3 символов
		if(mb_strlen($search,'utf-8')<3)
			//добавляем в массив поиска всю поисковыю фразу, декодированную в соответствии с контентом и фразу в том виде, в котором она была задана
			$words=array(transform::typography(trim($search_aw),0),trim($search_aw));
		else
			//разбиваем запрос по словам
			$words=explode(' ',trim($search));
		$q='SELECT * FROM `content` WHERE (`h1` LIKE \'<>\'';
		foreach($words as $word)
			$q.=' OR `h1` LIKE \'%'.$word.'%\'';
		$q.=') OR (`title` LIKE \'<>\'';
		foreach($words as $word)
			$q.=' OR `title` LIKE \'%'.$word.'%\'';
		$q.=') OR (`content` LIKE \'<><>\'';	
		foreach($words as $word)
			$q.=' OR `content` LIKE \'%'.$word.'%\'';
		$q.=') LIMIT 20;';
		if($res=$sql->query($q)){
			$results=array();
			$current['m_content_content']='<ol>';
			foreach($res as $record){
				//удаляем теги в контенте
				$record['m_content_content']=str_replace("&nbsp;",' ',$record['content']);
				$record['m_content_content']=iconv('cp1251','utf-8',str_replace(array("\r\n","\t","\n"),' ',strip_tags(html_entity_decode(htmlspecialchars_decode(iconv('utf-8','cp1251',$record['m_content_content']))))));
				$result['m_content_title']=tag_b($record['m_content_title'],$words);
				$result['href']='/?result_id='.$record['m_content_id'];
				//получаем сниппеты
				$result['snippets']=implode('<span class="three-dots"> … </span>',snippets($record['m_content_content'],$words));
				//получаем ссылку
				$result['link']='<span class="search-results-link"><a href="/" target="_blank">'.$_SERVER['HTTP_HOST'].'<span class="underline"></span></a>';
				$parents=array();
				$menu->parents($record['m_content_id'],$parents);
				$url='';
				$parents=array_reverse($parents);
				foreach($parents as $parent){
					$url.=$parent['url'].'/';
					$result['link'].=' > <a href="/'.$url.'" target="_blank">'.$parent['m_menu_name'].'<span class="underline"></span></a>';
				}
				$result['link'].=' > <a href="/'.$url.$menu->nodes_id[$record['m_content_id']]['m_menu_url'].'/" target="_blank">'.$menu->nodes_id[$record['m_content_id']]['m_menu_name'].'<span class="underline"></span></a></span>';
				$results[]='<li><p><a target="_blank" href="'.$url.$menu->nodes_id[$record['m_content_id']]['m_menu_url'].'/">'.$result['m_content_title'].'<span class="underline"></span></a>'.($result['snippets']?'<br/>'.$result['snippets']:'').'<br/>'.$result['link'].'</p></li>';			
			}
			//сортируем результаты по количеству тегов <b>
			usort($results,'t_sort');
			$current['m_content_content'].=implode('',$results).'</ol>';
		}
		else
			$current['m_content_content']='Поиск не дал результатов.';
	}
}
?>