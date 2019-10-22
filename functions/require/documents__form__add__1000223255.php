<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$services,$info,$products,$orders;

$content->setJS('
	
	/* автофильтрация адресов по контрагенту */
	$("[name=\'m_documents_performer\']").on("change",function(){
		$("[name=\'m_documents_address_from\'] option").removeClass("hide");
		$("[name=\'m_documents_address_from\'] option").each(function(index,el){
			/* СКРЫВАЕМ АДРЕСА ДРУГИХ КОНТРАГЕНТОВ */
			if($(el).attr("data-contragent")!=$("[name=\'m_documents_performer\'] option:selected").val())
				$(el).addClass("hide");
			/* ВЫДЕЛЯЕМ ПОСЛЕДНИЙ АДРЕС НУЖНОГО КОНТРАГЕНТА */
			else{
				$("[name=\'m_documents_address_from\']").select2("val",$(el).val());
			}
		});
	});
	$("[name=\'m_documents_customer\']").on("change",function(){
		$("[name=\'m_documents_address_to\'] option").removeClass("hide");
		$("[name=\'m_documents_address_to\'] option").each(function(index,el){
			if($(el).attr("data-contragent")!=$("[name=\'m_documents_customer\'] option:selected").val())
				$(el).addClass("hide");
			else{
				$("[name=\'m_documents_address_to\']").select2("val",$(el).val());
			}
		});
	});
	
	$("[name=\'m_documents_bar\']").prop("checked",false);
	$("[name=\'m_documents_performer\']").trigger("change");
	
	
');

if(get('m_documents_id')){

	$document=$documents->getInfo(get('m_documents_id'));
	$document['params']=json_decode($document['m_documents_params']);
													/* РЕДАКТИРОВАНИЕ ДОКУМЕНТА */
?>
<header>
	Информация
</header>
<fieldset>
	<section>
		<label class="label">Адрес отправителя</label>
		<select name="m_documents_address_from" class="autoselect" placeholder="выберите из списка...">
			<option value="0">выберите Исполнителя...</option>
			<?
				foreach($info->getAddress() as $_a)
					foreach($_a as $__a)
						if($__a['m_address_type']==3)
							echo '<option value="'.$__a['m_address_id'].'" data-contragent="'.$__a['m_address_contragents_id'].'"'.($__a['m_address_id']==$document['params']->address_from?' selected ':'').'>',
								$__a['m_address_full'].', '.$__a['m_address_recipient'],
							'</option>';
			?>
		</select>
	</section>
	<section>
		<label class="label">Адрес получателя</label>
		<select name="m_documents_address_to" class="autoselect" placeholder="выберите из списка...">
			<option value="0">выберите Заказчика...</option>
			<?
				foreach($info->getAddress() as $_a)
					foreach($_a as $__a)
						if($__a['m_address_type']==3)
							echo '<option value="'.$__a['m_address_id'].'" data-contragent="'.$__a['m_address_contragents_id'].'"'.($__a['m_address_id']==$document['params']->address_to?' selected ':'').'>',
								$__a['m_address_full'].', '.$__a['m_address_recipient'],
							'</option>';
			?>
		</select>
	</section>
</fieldset>	
<footer>
	<button type="submit" class="btn btn-primary">
		<i class="fa fa-save"></i>
		Сохранить данные
	</button>
</footer>
<input type="hidden" name="action" value="m_documents_change"/>
<input type="hidden" name="m_documents_templates_id" value="1000223255"/>
<input type="hidden" name="m_documents_id" value="<?=$document['m_documents_id']?>"/>
<?}
														/* НОВЫЙ ДОКУМЕНТ */
else{?>
<header>
	Информация
</header>
<fieldset>
	<section>
		<label class="label">Адрес отправителя</label>
		<select name="m_documents_address_from" class="autoselect" placeholder="выберите из списка...">
			<option value="0">выберите Исполнителя...</option>
			<?
				foreach($info->getAddress() as $_a)
					foreach($_a as $__a)
						if($__a['m_address_type']==3)
							echo '<option value="'.$__a['m_address_id'].'" data-contragent="'.$__a['m_address_contragents_id'].'"'.($__a['m_address_contragents_id']==3363726835?' selected ':'').'>',
								$__a['m_address_full'].', '.$__a['m_address_recipient'],
							'</option>';
			?>
		</select>
	</section>
	<section>
		<label class="label">Адрес получателя</label>
		<select name="m_documents_address_to" class="autoselect" placeholder="выберите из списка...">
			<option value="0">выберите Заказчика...</option>
			<?
				foreach($info->getAddress() as $_a)
					foreach($_a as $__a)
						if($__a['m_address_type']==3)
							echo '<option value="'.$__a['m_address_id'].'" data-contragent="'.$__a['m_address_contragents_id'].'">',
								$__a['m_address_full'].', '.$__a['m_address_recipient'],
							'</option>';
			?>
		</select>
	</section>
</fieldset>	
<footer>
	<button type="submit" class="btn btn-primary">
		<i class="fa fa-save"></i>
		Сохранить данные
	</button>
</footer>
<input type="hidden" name="action" value="m_documents_add"/>
<input type="hidden" name="m_documents_templates_id" value="1000223255"/>
<?}?>