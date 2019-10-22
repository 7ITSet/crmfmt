<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$services,$info,$products,$documents;

if(get('m_documents_id')){

	$document=$documents->getInfo(get('m_documents_id'));
	$document['params']=json_decode($document['m_documents_params']);
	//скидка по заказу
	$discount=$orders->orders_id[$document['m_documents_order']][0]['m_orders_discount'];
														/* РЕДАКТИРОВАНИЕ */
	if(get('m_documents_order')){
		$order=$orders->orders_id[get('m_documents_order')][0];
		$org=$contragents->getInfo($order['m_orders_performer']);
		$client=$contragents->getInfo($order['m_orders_customer']);

		$smeta=$documents->getInfo($document['params']->smeta);

		$order['params'][]=json_decode($smeta['m_documents_params'],true);
		$order['params'][sizeof($order['params'])-1]['doc_id']=$smeta['m_documents_id'];
		$json=json_encode($order['params'][sizeof($order['params'])-1],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
		$order['params'][sizeof($order['params'])-1]=json_decode($json);

	}
	$template=get('m_documents_templates_id');
	$id_stage_1=mt_rand(1,99999999);
	
	//выбираем все акты по данному заказу и по данной смете
	$acts=array();
	foreach($documents->getInfo() as $_document)
		if($_document[0]['m_documents_order']==$order['m_orders_id']&&$_document[0]['m_documents_templates_id']==8522102145)
			$acts[]=$_document[0];
	//УЖЕ ВЫПОЛНЕННЫЕ РАБОТЫ ПО ВСЕМ АКТАМ, КРОМЕ ТЕКУЩЕГО
	$done_services=array();
	$length_acts=sizeof($acts);
	for($i=0;$i<$length_acts;$i++){
		if($acts[$i]['m_documents_id']==$document['m_documents_id']){
			unset($acts[$i]);
			continue;
		}
		$p=json_decode($acts[$i]['m_documents_params']);
		if(isset($p->smeta)&&$p->smeta==$smeta['m_documents_id']){
			//список работ в акте
			foreach($p->items as $_item)
				$done_services[]=$_item->room_id.$_item->id.$_item->count.' ';
			continue;
		}
		else{
			unset($acts[$i]);
		}
	}
	//находим уже сделанные работы по данной смете в других актах//


$template=get('m_documents_templates_id');
$id_stage_1=mt_rand(1,99999999);
	
$content->setJS('
	/* ДЕЛАЕМ ПОШИРЕ БЛОК С ФОРМОЙ */
	$(".main-documents").addClass("col-lg-8").removeClass("col-lg-6");
	
	/* АВТОВЫБОР СТОРОН ПРИ ВЫБОРЕ ЗАКАЗА */
	$("[name=\'smeta\']").on("change",function(){
		window.location="http://'.$_SERVER['HTTP_HOST'].'/documents/?m_documents_templates_id='.$template.'&smeta="+$("[name=\'smeta\'] option:selected").val();
	});
	
	$("[name=\'m_documents_performer\']").val("'.(isset($org['m_contragents_id'])?$org['m_contragents_id']:'0').'");
	$("[name=\'m_documents_customer\']").val("'.(isset($client['m_contragents_id'])?$client['m_contragents_id']:'0').'");

	
	/* ОБНОВЛЕНИЕ ОБЩЕЙ СУММЫ */
	function updateAllSum(){
		var sum=0,discount='.(isset($discount)?$discount:0).';
		$(".rowsum span").each(function(index,el){
			sum+=$(el).text().substr(0,($(el).text().length*1-2))*1;
		});
		$("#allsum span,#allsum_a span").text(sum.toFixed(2)+" р.");
		$("[name=\'m_orders_act_sum\']").val(sum.toFixed(2));
		if(discount){
			$("#allsum_d span").text((sum*discount/100).toFixed(2)+" р.");
			$("#allsum_a span").text((sum-sum*discount/100).toFixed(2)+" р.");
		}
	}
	
	$(document).on("change","td.check input",function(){
		/* ПОМЕЧАЕМ ВЫДЕЛЕНЫЕ ПОЛЯ РАБОТ И БЛОКИРУЕМ ЭТИ РАБОТЫ В ДРУГИХ ЭТАПАХ */
		if($(this).prop("checked")){
			$("input[data-id=\'"+$(this).attr("data-id")+"\']").prop("disabled",true);
			$(this).prop("disabled",false).parents("tr:first").addClass("tr-selected").removeClass("tr-block");
		}
		else{
			$("input[data-id=\'"+$(this).attr("data-id")+"\']").prop("disabled",false);
			$(this).parents("tr:first").removeClass("tr-selected");}
		var sum=0;
		$(this).parents(".row:first").find("tr.tr-selected").each(function(index,el){
			sum+=$(el).find("td.sum").text()*1;
		});
		$(this).parents(".row:first").find(".rowsum span").text(sum.toFixed(2)+" р.");
		
		var sum_stage=0;
		$(this).parents(".multirow:first").find(".rowsum span").each(function(index,el){
			sum_stage+=$(el).text().substr(0,$(el).text().indexOf(" "))*1;
			$(el).parents(".multirow:first").find("[name=\'m_orders_kalendar_stage_sum[]\']").val(sum_stage);
		});
		
		
		/* ПОМЕТКА ЗЕЛЕНОЙ ГАЛОЧКОЙ ПОМЕЩЕНИЯ, ЕСЛИ ВЫДЕЛЕНЫ ВСЕ РАБОТЫ ПОМЕЩЕНИЯ //(КРОМЕ ТЕХ, КОТОРЫЕ БЫЛИ ВЫДЕЛЕНЫ В ДРУГИХ ЭТАПАХ) */
		//if($(this).parents("tbody:first").find("tr:not(.tr-block)").length==$(this).parents("tbody:first").find("tr.tr-selected:not(.tr-block)").length)
		if($(this).parents("tbody:first").find("tr").length==$(this).parents("tbody:first").find("tr.tr-selected").length)
			$(this).parents(".panel").find("i.check-status").removeClass("txt-color-noone txt-color-part").addClass("txt-color-full");
		else{
			/* ЕСЛИ ВЫДЕЛЕНЫ НЕ ВСЕ, А ХОТЯ БЫ НЕСКОЛЬКО РАБОТ - СТАВИМ СЕРУЮ ГАЛОЧКУ */
			if($(this).parents("tbody:first").find("tr.tr-selected").length)
				$(this).parents(".panel").find("i.check-status").removeClass("txt-color-noone txt-color-full").addClass("txt-color-part");
			/* ЕСЛИ РАБОТЫ НЕ ВЫДЕЛЕНЫ - СТАВИМ СВЕТЛО-СЕРУЮ ГАЛОЧКУ*/
			else{
				$(this).parents(".panel").find("i.check-status").removeClass("txt-color-part txt-color-full").addClass("txt-color-noone");
			}
		}
		updateAllSum();
	});	
	
	$(document).on("change",".select_all",function(){
		if($(this).prop("checked")==true){
			$(this).parents("table:first").find("td.check").parent(":not(.tr-block)").addClass("tr-selected").find("input").prop("checked",true).trigger("change");}
		else{
			$(this).parents("table:first").find("td.check").parent(":not(.tr-block)").removeClass("tr-selected").find("input").prop("checked",false).trigger("change");}
	});
	
	$("#documents-add").on("submit",function(){
		$("tr:not(.tr-selected)").remove();		
		return true;
	});
	
	$("td.check input").trigger("change");
	$(".tr-selected input").trigger("change");
	
	$("td.check input").each(function(index,el){
		if($(el).attr("disable")==1){
			$(el).prop("disabled",true).parents("tr:first").addClass("tr-block");
		}
	});
	
');	

?>
<header>
	Акт выполненных работ
</header>
<fieldset>
	<div class="row">
		<section class="col col-6">
			<label class="label">Смета</label>
			<select name="smeta" class="autoselect" placeholder="выберите из списка...">
				<option value="0">выберите из списка...</option>
				<?
					foreach($orders->orders_id as $_order){
						echo '<optgroup label="Заказ: '.$_order[0]['m_orders_name'].'">';
							foreach($documents->documents_id as $_document)
								if($_document[0]['m_documents_templates_id']==1200369852&&$_document[0]['m_documents_order']==$_order[0]['m_orders_id'])
									echo '<option value="'.$_document[0]['m_documents_id'].'"'.($smeta['m_documents_id']==$_document[0]['m_documents_id']?' selected ':'').'>',
											'Смета № '.$_document[0]['m_documents_numb'].' от '.transform::date_f(dtu($_document[0]['m_documents_date'])).($_document[0]['m_documents_comment']?' <span style="color:#999">('.$_document[0]['m_documents_comment'].')</span>':''),
										'</option>';
						echo '</optgroup>';		
					}
				?>
			</select>
		</section>
	</div>
<?
//если есть сметы по заказу
if(isset($order['params'])){
	$par=$document['params'];
	//СПИСОК РАБОТ В ТЕКУЩЕМ АКТЕ
	$isset_services=array();
	if(isset($par->smeta)&&$par->smeta==$smeta['m_documents_id']){
		foreach($par->items as $_item)
			$isset_services[]=$_item->room_id.$_item->id.$_item->count.' ';
	}
	//print_r($isset_services);
 foreach($order['params'] as $_smeta){
?>
			
				<section>
					<h3 style="font-size:15px;">Выполненные работы по смете</h3>
				</section>
				<div class="panel-group smart-accordion-default" id="accordion<?=$_smeta->doc_numb?>">
<?
		//если есть комнаты в смете
		if(isset($_smeta->items)&&$_smeta->items)
			foreach($_smeta->items as $_room_id=>$_item){
?>
					<div class="panel panel-default">
						<div class="panel-heading">
						  <h4 class="panel-title">
							<i class="fa fa-fw fa-check txt-color-noone check-status"></i>
							<a data-toggle="collapse" data-parent="#accordion<?=$_smeta->doc_numb?>" href="#collapse<?=$_room_id?>" class="">
								<i class="fa fa-lg fa-angle-down pull-right"></i>
								<i class="fa fa-lg fa-angle-up pull-right"></i>
								<?=($_item->room->name?$_item->room->name:'Помещение').($_item->room->square?' — '.$_item->room->square.' м<sup>2</sup>':'')?>
								
							</a>
						  </h4>
						</div>
						<div id="collapse<?=$_room_id?>" class="panel-collapse collapse" style="height: auto;">
						  <div class="panel-body no-padding">
							<table class="table table-bordered table-hover table-striped table-condensed">
								<thead>
									<tr>
										<th style="width:5%" class="no-order">
											<label>
												<div class="checkbox">
												  <label>
													<input type="checkbox" class="checkbox tr select_all"/>
													<span></span>
												  </label>
												</div>
											</label>
										</th>
										<th style="width:40%">Наименование</th>
										<th style="width:10%">Ед.&nbsp;изм.</th>
										<th style="width:15%">Кол-во</th>
										<th style="width:15%">Цена</th>
										<th style="width:15%">Сумма</th>
									</tr>
								</thead>
								<tbody>
<?
				//если есть работы по комнате
				if(isset($_item->services)&&$_item->services)
					foreach($_item->services as $k=>$_service){
?>	
									<tr>
										<td class="check">
											<label>
												<div class="checkbox">
												  <label>
													<input type="checkbox" name="m_orders_act_services[]" class="checkbox tr" value="1" data-clean="no" data-id="<?=$_smeta->doc_numb.'_'.$_room_id.'_'.$k?>" <?=in_array($_room_id.$_service->id.$_service->count.' ',$done_services)?'  disable="1" ':''?> <?=in_array($_room_id.$_service->id.$_service->count.' ',$isset_services)?' checked ':''?>>
													<span></span>
												  </label>
												</div>
											</label>
										</td>
										<td>
											<?=$services->services_id[$_service->id][0]['m_services_name']?>
											<input type="hidden" name="m_orders_act_services_id[]" value="<?=$_service->id?>" data-clean="no"/>
											<input type="hidden" name="m_orders_act_doc_id[]" value="<?=$_smeta->doc_id?>" data-clean="no"/>
											<input type="hidden" name="m_orders_act_services_room_id[]" value="<?=$_room_id?>" data-clean="no"/>
										</td>
										<td>
											<?=$info->getUnitsNoHTML($services->services_id[$_service->id][0]['m_services_unit'])?>
											<input type="hidden" name="m_orders_act_services_count[]" value="<?=$_service->count?>" data-clean="no"/>
										</td>
										<td>
											<?=$_service->count?>
										</td>
										<td>
											<?=$_service->price?>
											<input type="hidden" name="m_orders_act_services_price[]" value="<?=$_service->price?>" data-clean="no"/>
										</td>
										<td class="sum">
											<?=$_service->sum?>
											<input type="hidden" name="m_orders_act_services_sum[]" value="<?=$_service->sum?>" data-clean="no"/>
										</td>
									</tr>
<?
						}
?>
								</tbody>
							</table>
						  </div>
						</div>
					</div>
<?
			}
?>
				</div>
				<section>
					<div id="allsum" class="rowsum allsum">
						Сумма выполненных работ: <span>0 р.</span>
					</div>
					<div id="allsum_d" class="allsum">
						Скидка <?=$discount?>%: <span>0 р.</span>
					</div>
					<div id="allsum_a" class="allsum">
						Итого со скидкой: <span>0 р.</span>
					</div>
				</section>
				
<?
		}
	}
?>
</fieldset>	
<footer>
	<button type="submit" class="btn btn-primary" id="m_orders_act_add">
		<i class="fa fa-save"></i>
		Сохранить данные
	</button>
</footer>

<input type="hidden" name="m_orders_act_sum" />
<input type="hidden" name="m_documents_id" value="<?=$document['m_documents_id']?>" />
<input type="hidden" name="action" value="m_documents_change"/>
<input type="hidden" name="m_documents_templates_id" value="8522102145"/>
<?
}
														/* НОВЫЙ */
else{

if(get('smeta')){
	
	$smeta=$documents->getInfo(get('smeta'));

	$order=$orders->orders_id[$smeta['m_documents_order']][0];
	$org=$contragents->getInfo($order['m_orders_performer']);
	$client=$contragents->getInfo($order['m_orders_customer']);
	
	//скидка по заказу
	$discount=$order['m_orders_discount'];
	
	//выбираем все акты по данному заказу и по данной смете
	$acts=array();
	foreach($documents->getInfo() as $_document)
		if($_document[0]['m_documents_order']==$order['m_orders_id']&&$_document[0]['m_documents_templates_id']==8522102145)
			$acts[]=$_document[0];
	//выполненные работы в других актах
	$done_services=array();
	//пробегаемся по каждому акту
	foreach($acts as &$_act){
		$p=json_decode($_act['m_documents_params']);
		if(isset($p->smeta)&&$p->smeta==$smeta['m_documents_id']){
			//список работ в акте
			foreach($p->items as $_item)
				$done_services[]=$_item->room_id.$_item->id.$_item->count.' ';
			continue;
		}
		else{
			unset($_act);
		}
	}
	//находим уже сделанные работы по данной смете в других актах
	
	//foreach($documents->documents_id as $_document){
		//if($_document[0]['m_documents_templates_id']==1200369852&&$_document[0]['m_documents_order']==$order['m_orders_id']&&){
	$order['params'][]=json_decode($smeta['m_documents_params'],true);
	$order['params'][sizeof($order['params'])-1]['doc_id']=$smeta['m_documents_id'];
	$json=json_encode($order['params'][sizeof($order['params'])-1],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
	$order['params'][sizeof($order['params'])-1]=json_decode($json);
		//}
	//}
	
}
$template=get('m_documents_templates_id');
$id_stage_1=mt_rand(1,99999999);
	
$content->setJS('
	/* ДЕЛАЕМ ПОШИРЕ БЛОК С ФОРМОЙ */
	$(".main-documents").addClass("col-lg-8").removeClass("col-lg-6");
	
	/* АВТОВЫБОР СТОРОН ПРИ ВЫБОРЕ ЗАКАЗА */
	$("[name=\'smeta\']").on("change",function(){
		window.location="http://'.$_SERVER['HTTP_HOST'].'/documents/?m_documents_templates_id='.$template.'&smeta="+$("[name=\'smeta\'] option:selected").val();
	});
	
	$("[name=\'m_documents_performer\']").val("'.(isset($org['m_contragents_id'])?$org['m_contragents_id']:'0').'");
	$("[name=\'m_documents_customer\']").val("'.(isset($client['m_contragents_id'])?$client['m_contragents_id']:'0').'");

	
	/* ОБНОВЛЕНИЕ ОБЩЕЙ СУММЫ */
	function updateAllSum(){
		var sum=0,discount='.(isset($discount)?$discount:0).';
		$(".rowsum span").each(function(index,el){
			sum+=$(el).text().substr(0,($(el).text().length*1-2))*1;
		});
		$("#allsum span,#allsum_a span").text(sum.toFixed(2)+" р.");
		$("[name=\'m_orders_act_sum\']").val(sum.toFixed(2));
		if(discount){
			$("#allsum_d span").text((sum*discount/100).toFixed(2)+" р.");
			$("#allsum_a span").text((sum-sum*discount/100).toFixed(2)+" р.");
		}
	}
	
	$(document).on("change","td.check input",function(){
		/* ПОМЕЧАЕМ ВЫДЕЛЕНЫЕ ПОЛЯ РАБОТ И БЛОКИРУЕМ ЭТИ РАБОТЫ В ДРУГИХ ЭТАПАХ */
		if($(this).prop("checked")){
			$("input[data-id=\'"+$(this).attr("data-id")+"\']").prop("disabled",true);
			$(this).prop("disabled",false).parents("tr:first").addClass("tr-selected").removeClass("tr-block");
		}
		else{
			$("input[data-id=\'"+$(this).attr("data-id")+"\']").prop("disabled",false);
			$(this).parents("tr:first").removeClass("tr-selected");}
		var sum=0;
		$(this).parents(".row:first").find("tr.tr-selected").each(function(index,el){
			sum+=$(el).find("td.sum").text()*1;
		});
		$(this).parents(".row:first").find(".rowsum span").text(sum.toFixed(2)+" р.");
		
		var sum_stage=0;
		$(this).parents(".multirow:first").find(".rowsum span").each(function(index,el){
			sum_stage+=$(el).text().substr(0,$(el).text().indexOf(" "))*1;
			$(el).parents(".multirow:first").find("[name=\'m_orders_kalendar_stage_sum[]\']").val(sum_stage);
		});
		
		
		/* ПОМЕТКА ЗЕЛЕНОЙ ГАЛОЧКОЙ ПОМЕЩЕНИЯ, ЕСЛИ ВЫДЕЛЕНЫ ВСЕ РАБОТЫ ПОМЕЩЕНИЯ //(КРОМЕ ТЕХ, КОТОРЫЕ БЫЛИ ВЫДЕЛЕНЫ В ДРУГИХ ЭТАПАХ) */
		//if($(this).parents("tbody:first").find("tr:not(.tr-block)").length==$(this).parents("tbody:first").find("tr.tr-selected:not(.tr-block)").length)
		if($(this).parents("tbody:first").find("tr").length==$(this).parents("tbody:first").find("tr.tr-selected").length)
			$(this).parents(".panel").find("i.check-status").removeClass("txt-color-noone txt-color-part").addClass("txt-color-full");
		else{
			/* ЕСЛИ ВЫДЕЛЕНЫ НЕ ВСЕ, А ХОТЯ БЫ НЕСКОЛЬКО РАБОТ - СТАВИМ СЕРУЮ ГАЛОЧКУ */
			if($(this).parents("tbody:first").find("tr.tr-selected").length)
				$(this).parents(".panel").find("i.check-status").removeClass("txt-color-noone txt-color-full").addClass("txt-color-part");
			/* ЕСЛИ РАБОТЫ НЕ ВЫДЕЛЕНЫ - СТАВИМ СВЕТЛО-СЕРУЮ ГАЛОЧКУ*/
			else{
				$(this).parents(".panel").find("i.check-status").removeClass("txt-color-part txt-color-full").addClass("txt-color-noone");
			}
		}
		updateAllSum();
	});	
	
	$(document).on("change",".select_all",function(){
		if($(this).prop("checked")==true){
			$(this).parents("table:first").find("td.check").parent(":not(.tr-block)").addClass("tr-selected").find("input").prop("checked",true).trigger("change");}
		else{
			$(this).parents("table:first").find("td.check").parent(":not(.tr-block)").removeClass("tr-selected").find("input").prop("checked",false).trigger("change");}
	});
	
	$("#documents-add").on("submit",function(){
		$("tr:not(.tr-selected)").remove();		
		return true;
	});
	
');	

?>
<header>
	Акт выполненных работ
</header>
<fieldset>
	<div class="row">
		<section class="col col-6">
			<label class="label">Смета</label>
			<select name="smeta" class="autoselect" placeholder="выберите из списка...">
				<option value="0">выберите из списка...</option>
				<?
					foreach($orders->orders_id as $_order){
						echo '<optgroup label="Заказ: '.$_order[0]['m_orders_name'].'">';
							foreach($documents->documents_id as $_document)
								if($_document[0]['m_documents_templates_id']==1200369852&&$_document[0]['m_documents_order']==$_order[0]['m_orders_id'])
									echo '<option value="'.$_document[0]['m_documents_id'].'"'.($smeta['m_documents_id']==$_document[0]['m_documents_id']?' selected ':'').'>',
											'Смета № '.$_document[0]['m_documents_numb'].' от '.transform::date_f(dtu($_document[0]['m_documents_date'])).($_document[0]['m_documents_comment']?' <span style="color:#999">('.$_document[0]['m_documents_comment'].')</span>':''),
										'</option>';
						echo '</optgroup>';		
					}
				?>
			</select>
		</section>
	</div>
<?
//если есть сметы по заказу
if(isset($order['params'])){
	foreach($order['params'] as $_smeta){
?>
			
				<section>
					<h3 style="font-size:15px;">Выполненные работы по смете</h3>
				</section>
				<div class="panel-group smart-accordion-default" id="accordion<?=$_smeta->doc_numb?>">
<?
		//если есть комнаты в смете
		if(isset($_smeta->items)&&$_smeta->items)
			foreach($_smeta->items as $_room_id=>$_item){
?>
					<div class="panel panel-default">
						<div class="panel-heading">
						  <h4 class="panel-title">
							<i class="fa fa-fw fa-check txt-color-noone check-status"></i>
							<a data-toggle="collapse" data-parent="#accordion<?=$_smeta->doc_numb?>" href="#collapse<?=$_room_id?>" class="">
								<i class="fa fa-lg fa-angle-down pull-right"></i>
								<i class="fa fa-lg fa-angle-up pull-right"></i>
								<?=($_item->room->name?$_item->room->name:'Помещение').($_item->room->square?' — '.$_item->room->square.' м<sup>2</sup>':'')?>
								
							</a>
						  </h4>
						</div>
						<div id="collapse<?=$_room_id?>" class="panel-collapse collapse" style="height: auto;">
						  <div class="panel-body no-padding">
							<table class="table table-bordered table-hover table-striped table-condensed">
								<thead>
									<tr>
										<th style="width:5%" class="no-order">
											<label>
												<div class="checkbox">
												  <label>
													<input type="checkbox" class="checkbox tr select_all"/>
													<span></span>
												  </label>
												</div>
											</label>
										</th>
										<th style="width:40%">Наименование</th>
										<th style="width:10%">Ед.&nbsp;изм.</th>
										<th style="width:15%">Кол-во</th>
										<th style="width:15%">Цена</th>
										<th style="width:15%">Сумма</th>
									</tr>
								</thead>
								<tbody>
<?
				//если есть работы по комнате
				if(isset($_item->services)&&$_item->services)
					foreach($_item->services as $k=>$_service)
						//не показываем работы по смете, которые были занесены в другие акты
						if(!in_array($_room_id.$_service->id.$_service->count.' ',$done_services)){
?>								
									<tr>
										<td class="check">
											<label>
												<div class="checkbox">
												  <label>
													<input type="checkbox" name="m_orders_act_services[]" class="checkbox tr" value="1" data-clean="no" data-id="<?=$_smeta->doc_numb.'_'.$_room_id.'_'.$k?>">
													<span></span>
												  </label>
												</div>
											</label>
										</td>
										<td>
											<?=$services->services_id[$_service->id][0]['m_services_name']?>
											<input type="hidden" name="m_orders_act_services_id[]" value="<?=$_service->id?>" data-clean="no"/>
											<input type="hidden" name="m_orders_act_doc_id[]" value="<?=$_smeta->doc_id?>" data-clean="no"/>
											<input type="hidden" name="m_orders_act_services_room_id[]" value="<?=$_room_id?>" data-clean="no"/>
										</td>
										<td>
											<?=$info->getUnitsNoHTML($services->services_id[$_service->id][0]['m_services_unit'])?>
											<input type="hidden" name="m_orders_act_services_count[]" value="<?=$_service->count?>" data-clean="no"/>
										</td>
										<td>
											<?=$_service->count?>
										</td>
										<td>
											<?=$_service->price?>
											<input type="hidden" name="m_orders_act_services_price[]" value="<?=$_service->price?>" data-clean="no"/>
										</td>
										<td class="sum">
											<?=$_service->sum?>
											<input type="hidden" name="m_orders_act_services_sum[]" value="<?=$_service->sum?>" data-clean="no"/>
										</td>
									</tr>
<?
						}
?>
								</tbody>
							</table>
						  </div>
						</div>
					</div>
<?
			}
?>
				</div>
				<section>
					<div id="allsum" class="rowsum allsum">
						Сумма выполненных работ: <span>0 р.</span>
					</div>
					<div id="allsum_d" class="allsum">
						Скидка <?=$discount?>%: <span>0 р.</span>
					</div>
					<div id="allsum_a" class="allsum">
						Итого со скидкой: <span>0 р.</span>
					</div>
				</section>
<?
	}
?>
</fieldset>	
<footer>
	<button type="submit" class="btn btn-primary" id="m_orders_act_add">
		<i class="fa fa-save"></i>
		Сохранить данные
	</button>
</footer>
<?
}
?>
<input type="hidden" name="m_orders_act_sum" />
<input type="hidden" name="action" value="m_documents_add"/>
<input type="hidden" name="m_documents_templates_id" value="8522102145"/>
<?}?>
<script src="/js/jquery.df.js"></script>
<script src="/js/jquery.suggest_services.js"></script>