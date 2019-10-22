<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$services,$info,$products,$orders;

$content->setJS('
	
	/* автовыбор сторон при выборе заказа */
	$("[name=\'m_documents_invoice\']").on("change",function(){
		$.post("/ajax/order_select_contragents.php",
			{
				order:$("[name=\'m_documents_invoice\'] option:selected").attr("data-order")
			},
			function(data){alert(data);
				if(data!="ERROR"){
					data=data.split("|");
					$("[name=\'m_documents_performer\']").select2("val",data[0]);
					$("[name=\'m_documents_customer\']").select2("val",data[1]);
				}
				
			}
		)
	});
	
	$("[name=\'m_documents_signature\']").prop("checked",true);
	
');

if(get('m_documents_id')){

	$document=$documents->getInfo(get('m_documents_id'));
	$document['params']=json_decode($document['m_documents_params']);
													/* РЕДАКТИРОВАНИЕ ДОКУМЕНТА */
?>
<header>
	Параметры договора
</header>
<fieldset>
<div class="row">
		<section class="col col-6">
			<label class="label">Счет</label>
			<select name="m_documents_invoice" class="autoselect" placeholder="выберите из списка..." >
				<option value="0">выберите из списка...</option>
				<?
				foreach($orders->orders_id as $_order){
					echo '<optgroup label="Заказ: '.$_order[0]['m_orders_name'].'">';
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
									echo '<option data-order="'.$_order[0]['m_orders_id'].'" value="'.$_document[0]['m_documents_id'].'"'.($document['params']->invoice==$_document[0]['m_documents_id']?' selected ':'').'>',
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
	</div>
	<div class="row">
		<section class="col col-3">
			<label class="label">Срок поставки</label>
			<label class="input">
				<i class="icon-append">дн.</i>
				<input type="text" name="m_documents_doc_delivery_time" placeholder="в течение 7-и дней" style="text-align:right" value="<?=$document['params']->m_documents_doc_delivery_time?>">
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
<input type="hidden" name="m_documents_templates_id" value="4234525327"/>
<input type="hidden" name="m_documents_id" value="<?=$document['m_documents_id']?>"/>
<?}
														/* НОВЫЙ ДОКУМЕНТ */
else{?>
<header>
	Параметры договора
</header>
<fieldset>
	<div class="row">
		<section class="col col-6">
			<label class="label">Счет</label>
			<select name="m_documents_invoice" class="autoselect" placeholder="выберите из списка..." >
				<option value="0">выберите из списка...</option>
				<?
				foreach($orders->orders_id as $_order){
					echo '<optgroup label="Заказ: '.$_order[0]['m_orders_name'].'">';
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
									echo '<option data-order="'.$_order[0]['m_orders_id'].'" value="'.$_document[0]['m_documents_id'].'"'.($smeta['m_documents_id']==$_document[0]['m_documents_id']?' selected ':'').'>',
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
	</div>
	<div class="row">
		<section class="col col-3">
			<label class="label">Срок поставки</label>
			<label class="input">
				<i class="icon-append">дн.</i>
				<input type="text" name="m_documents_doc_delivery_time" placeholder="в течение 7-и дней" style="text-align:right">
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
<input type="hidden" name="m_documents_templates_id" value="4234525327"/>
<?}?>