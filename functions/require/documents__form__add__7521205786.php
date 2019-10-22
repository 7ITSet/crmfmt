<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$services,$info,$products;

if(get('m_documents_id')){
	$params=json_decode($document['m_documents_params']);
	$categoiries=array();
	foreach($params->items as $k=>$v)
		$categories[]=$k;
													/* РЕДАКТИРОВАНИЕ ПРАЙС-ЛИСТА */
?>
<header>
	Параметры прайс-листа
</header>
<fieldset>
	<section>
		<label class="label">Выгрузить категории</label>
		<select name="m_services_categories_id[]" style="width:100%" multiple class="autoselect" placeholder="выберите из списка...">
			<? 
				$c=array();
				$services->categories_childs(0,$c,2);
				foreach($c as $categories_){
					echo '<option value="'.$categories_['m_services_categories_id'].'" '.(in_array($categories_['m_services_categories_id'],$categories)?' selected':'').'>
							'.$categories_['m_services_categories_name'].'
						</option>';																
				}
			?>
		</select>
	</section>
	<div class="row">
		<section class="col col-6">
		  <label class="checkbox">
			<input type="checkbox" name="m_documents_itemslist" <?=$params->doc_itemslist==1?'checked="checked"':'';?> value="1"/>
			<i></i>
			Добавить содержание (оглавление)
		  </label>
		</section>
	</div>
	<section>
		<label class="label">Комментарий в начале прайса</label>
		<label class="textarea textarea-resizable"> 										
			<textarea name="m_documents_doc_message_info" rows="3" class="custom-scroll"><?=$params->doc_message_info;?> </textarea> 
		</label>
	</section>
</fieldset>	
<footer>
	<button type="submit" class="btn btn-primary">
		<i class="fa fa-save"></i>
		Сохранить данные
	</button>
</footer>
<input type="hidden" name="action" value="m_documents_change"/>
<input type="hidden" name="m_documents_id" value="<?=$document['m_documents_id'];?>"/>
<input type="hidden" name="m_documents_templates_id" value="7521205786"/>
<?}
														/* НОВЫЙ ПРАЙС-ЛИСТ */
else{?>
<header>
	Параметры прайс-листа
</header>
<fieldset>
	<section>
		<label class="label">Выгрузить категории</label>
		<select name="m_services_categories_id[]" style="width:100%" multiple class="autoselect" placeholder="выберите из списка...">
			<? 
				$categories=array();
				$services->categories_childs(0,$categories,2);
				foreach($categories as $categories_){
					echo '<option value="'.$categories_['m_services_categories_id'].'">
							'.$categories_['m_services_categories_name'].'
						</option>';																
				}
			?>
		</select>
	</section>
	<div class="row">
		<section class="col col-6">
		  <label class="checkbox">
			<input type="checkbox" name="m_documents_itemslist" checked="checked" value="1"/>
			<i></i>
			Добавить содержание (оглавление)
		  </label>
		</section>
	</div>
	<section>
		<label class="label">Комментарий в начале прайса</label>
		<label class="textarea textarea-resizable"> 										
			<textarea name="m_documents_doc_message_info" rows="3" class="custom-scroll"></textarea> 
		</label>
	</section>
</fieldset>	
<footer>
	<button type="submit" class="btn btn-primary">
		<i class="fa fa-save"></i>
		Сохранить данные
	</button>
</footer>
<input type="hidden" name="action" value="m_documents_add"/>
<input type="hidden" name="m_documents_templates_id" value="7521205786"/>
<?}?>