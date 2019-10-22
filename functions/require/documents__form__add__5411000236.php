<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$services,$info,$products,$orders;

$content->setJS('
	
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
	<div class="row">
		<section class="col col-3">
			<label class="label">С</label>
			<label class="input">
				<i class="icon-append fa fa-calendar"></i>
				<input type="text" name="m_documents_date_from_" class="datepicker" data-mask="99.99.9999" placeholder="<?=dtu('','d.m.Y')?>" value="<?=dtu($document['params']->date_from,'d.m.Y')?>">
				<input type="hidden" name="m_documents_date_from">
			</label>
		</section>
		<section class="col col-3">
			<label class="label">По</label>
			<label class="input">
				<i class="icon-append fa fa-calendar"></i>
				<input type="text" name="m_documents_date_to_" class="datepicker" data-mask="99.99.9999" placeholder="<?=dtu('','d.m.Y')?>" value="<?=dtu($document['params']->date_to,'d.m.Y')?>">
				<input type="hidden" name="m_documents_date_to">
			</label>
		</section>
		<section class="col col-3">
			<label class="label">Сальдо начальное</label>
			<label class="input">
				<i class="icon-append fa fa-money"></i>
				<input name="m_documents_saldo_start" style="text-align:right" placeholder="0.00" autocomplete="off" type="text" value="<?=$document['params']->saldo?>">
			</label>
		</section>
	</div>
</fieldset>	
<footer>
	<button type="submit" class="btn btn-primary">
		<i class="fa fa-save"></i>
		Сохранить данные
	</button>
</footer>
<input type="hidden" name="action" value="m_documents_change"/>
<input type="hidden" name="m_documents_templates_id" value="5411000236"/>
<input type="hidden" name="m_documents_id" value="<?=$document['m_documents_id']?>"/>
<?}
														/* НОВЫЙ ДОКУМЕНТ */
else{?>
<header>
	Период выборки
</header>
<fieldset>
	<div class="row">
		<section class="col col-3">
			<label class="label">С</label>
			<label class="input">
				<i class="icon-append fa fa-calendar"></i>
				<input type="text" name="m_documents_date_from_" class="datepicker" data-mask="99.99.9999" placeholder="<?=dtu('','d.m.Y')?>">
				<input type="hidden" name="m_documents_date_from">
			</label>
		</section>
		<section class="col col-3">
			<label class="label">По</label>
			<label class="input">
				<i class="icon-append fa fa-calendar"></i>
				<input type="text" name="m_documents_date_to_" class="datepicker" data-mask="99.99.9999" placeholder="<?=dtu('','d.m.Y')?>">
				<input type="hidden" name="m_documents_date_to">
			</label>
		</section>
		<section class="col col-3">
			<label class="label">Сальдо начальное</label>
			<label class="input">
				<i class="icon-append fa fa-money"></i>
				<input name="m_documents_saldo_start" style="text-align:right" placeholder="0.00" autocomplete="off" type="text">
			</label>
		</section>
	</div>
</fieldset>	
<footer>
	<button type="submit" class="btn btn-primary">
		<i class="fa fa-save"></i>
		Сохранить данные
	</button>
</footer>
<input type="hidden" name="action" value="m_documents_add"/>
<input type="hidden" name="m_documents_templates_id" value="5411000236"/>
<?}?>