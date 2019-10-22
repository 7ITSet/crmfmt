<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$services,$info,$products,$orders;

$content->setJS('
	
	$("[name=\'m_documents_performer\']").trigger("change");
	
	/* АВТОВЫБОР СТОРОН ПРИ ВЫБОРЕ ЗАКАЗА */
	$("[name=\'m_documents_order\']").on("change",function(){
		$.post(
			"/ajax/invoice_select_contragents.php",
			{
				invoice:$("[name=\'m_documents_invoice\'] option:selected").val()
			},
			function(data){
				if(data!="ERROR"){
					data=data.split("|");
					$("[name=\'m_documents_performer\']").select2("val",data[0]);
					$("[name=\'m_documents_customer\']").select2("val",data[1]);
				}
				
			}
		)
	});
	
	/* АВТОВЫБОР ЗАКАЗА ПРИ ВЫБОРЕ СЧЕТА */
	$("[name=\'m_documents_invoice\']").on("change",function(){
		$("[name=\'m_documents_order\']").val($("[name=\'m_documents_invoice\'] option:selected").parent().attr("id")).triggerHandler("change");
	});
	
	$("[name=\'m_documents_signature\']").prop("checked",true);
	
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
	Параметры документа
</header>
<fieldset>
	<section>
		<label class="label">Счет</label>
		<select name="m_documents_invoice" class="autoselect" placeholder="выберите из списка...">
			<option value="0">выберите из списка...</option>
			<?
				foreach($orders->orders_id as $_order){
					echo '<optgroup label="Заказ: '.$_order[0]['m_orders_name'].'" id="'.$_order[0]['m_orders_id'].'">';
						foreach($documents->documents_id as $_document)
							if(
								(
									$_document[0]['m_documents_templates_id']==1200369852||
									$_document[0]['m_documents_templates_id']==1200369853||
									$_document[0]['m_documents_templates_id']==2363374033||
									$_document[0]['m_documents_templates_id']==8522102145
								)
									&&$_document[0]['m_documents_order']==$_order[0]['m_orders_id']
								){
									$p=json_decode($_document[0]['m_documents_params'],true);
									$nds18=isset($p['doc_nds18'])&&$p['doc_nds18']?', в т.ч. НДС 18%: '.transform::price_o($p['doc_nds18']-$p['doc_nds18']*$order['m_orders_discount']/100).' р.':' без НДС';
									$sum=isset($p['doc_sum'])&&$p['doc_sum']?transform::price_o($p['doc_sum']):0;
									echo '<option value="'.$_document[0]['m_documents_id'].'"'.($smeta['m_documents_id']==$_document[0]['m_documents_id']?' selected ':'').'>',
											$documents->documents_templates[$_document[0]['m_documents_templates_id']][0]['m_documents_templates_name'].
											' № '.$_document[0]['m_documents_numb'].' от '.transform::date_f(dtu($_document[0]['m_documents_date'])).($_document[0]['m_documents_comment']?' <span style="color:#999">('.$_document[0]['m_documents_comment'].')</span>':''),
											', сумма '.$sum.' р.'.$nds18,
										'</option>';
								}
					echo '</optgroup>';		
				}
			?>
		</select>
	</section>
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
			<label class="label">Доверенное лицо</label>
			<select name="m_documents_agent" class="autoselect" placeholder="выберите из списка...">
				<?
					foreach($contragents->getInfo() as $contragents_){
						if($contragents_[0]['m_contragents_type']==3)
						echo '<option value="'.$contragents_[0]['m_contragents_id'].'"'.($contragents_[0]['m_contragents_id']==$document['params']->agent?' selected ':'').'>',
							$contragents->getName($contragents_[0]['m_contragents_id']),
						'</option>';
					}
				?>
			</select>
		</section>
		<section class="col col-3">
			<br/>
			<label class="checkbox">
				<input type="checkbox" name="print_items" value="1"/>
				<i></i>
				Выводить все товары
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
<input type="hidden" name="m_documents_order" />
<input type="hidden" name="m_documents_templates_id" value="4562002365"/>
<?}?>