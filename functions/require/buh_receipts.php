<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$info,$orders,$buh,$documents;
	
if(get('action')=='details'){

$_buh=$buh->getInfo(get('m_buh_id'));

$templates=$documents->getDocsBuh();

//тэги
$pays=$buh->getInfo();
$buh_tags=array();
foreach($pays as $_pay){
	$_pay=$_pay[0];
	$buh_tags=array_merge($buh_tags,explode('|',$_pay['m_buh_tags']));
}
$buh_tags=array_unique(array_diff($buh_tags,array('')));
array_walk($buh_tags,function(&$el){$el='"'.$el.'"';});

$_buh['m_buh_documents_templates']=explode('|',$_buh['m_buh_documents_templates']);
$_buh['m_buh_invoice_numb']=explode('|',$_buh['m_buh_invoice_numb']);

$content->setJS('
	
	runAllForms();
	
	$("#buh-add").validate({
		rules : {
			m_buh_sum : {
				number: true,
				required: true
			},
			m_buh_sum_nds : {
				number: true
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});

	
	$(".datatable .delete").editable({
		url: "/ajax/services_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete"))
				$(this).parents("tr:first").remove();
		}
	});
	
	/* ТОЛЬКО ЦИФРЫ, ТОЧКА ВМЕСТО ЗАПЯТОЙ */
	$(document).on("keyup","[name=\'m_buh_sum\'],[name=\'m_buh_sum_nds\']",function(){
		$(this).val($(this).val().replace(",","."));
		$(this).val($(this).val().replace(/[^.0-9]/gim,""));
	});
	/* ОКРУГЛЕНИЕ ДО 2-Х ЗНАКОВ ПОСЛЕ ТОЧКИ В ПОЛЯХ С ЧИСЛОВЫМИ ЗНАЧЕНИЯМИ */
	$(document).on("change","[name=\'m_buh_sum\'],[name=\'m_buh_sum_nds\']",function(){
		$(this).val(($(this).val()*1).toFixed(2));
	});
	
	
	$("#m_buh_tags").editable({
		inputclass: "input-large",
		select2: {
			tags: ['.implode(',',$buh_tags).'],
			placeholder: "Укажите теги",
			tokenSeparators: [","]
		}
	});	
	$("#m_buh_tags").on("save",function(e,params){
		$("[name=\'m_buh_tags\']").val(params.newValue.join("|"));
	});
	
	/* АВТОВЫБОР СТОРОН ПРИ ВЫБОРЕ ЗАКАЗА */
	$("[name=\'m_buh_order\']").on("change",function(){
		$.post(
			"/ajax/order_select_contragents.php",
			{
				order:$("[name=\'m_buh_order\'] option:selected").val()
			},
			function(data){
				if(data!="ERROR"){
					data=data.split("|");
					$("[name=\'m_buh_performer\']").select2("val",data[0]);
					$("[name=\'m_buh_customer\']").select2("val",data[1]);
				}
				
			}
		)
	});
	
	/* АВТОВЫБОР ЗАКАЗА ПРИ ВЫБОРЕ СЧЕТА */
	$("[name=\'smeta[]\']").on("change",function(){
		$("[name=\'m_buh_order\']").select2("val",$("[name=\'smeta[]\'] option:selected").parent().attr("id")).triggerHandler("change");
	});
	
	$("#nds18").on("click",function(){
		$(this).next().val(($("[name=m_buh_sum]").val()*.18/1.18).toFixed(2));
	});
	
');
?>
<section id="widget-grid" class="">
<?
if(isset($_GET['success']))
	echo '<div class="row"><article class="col-lg-12">
			<div class="alert alert-success alert-block">
				<a class="close" data-dismiss="alert" href="#">x</a>
				<h4 class="alert-heading">Выполнено!</h4>
				Информация о клиенте успешно обновлена!
			</div>
		</article></div>';
if(isset($_GET['error']))
	echo '<div class="row"><article class="col-lg-12">
			<div class="alert alert-danger alert-block">
				<a class="close" data-dismiss="alert" href="#">x</a>
				<h4 class="alert-heading">Произошла ошибка!</h4>
				Произошла ошибка при сохранении данных.
			</div>
		</article></div>';
?>
<div class="row">
<article class="col-lg-6 sortable-grid ui-sortable">
		
		<div class="jarviswidget" id="wid-id-1" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">	
					
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Детали платежа</h2>
				</header>

			<!-- widget div-->
			<div>
				<!-- widget content -->
				<div class="widget-body">
					<form id="buh-add" class="smart-form" method="post">
						<header>
							Контрагенты
						</header>
						<fieldset>
							<div class="row">
								<section class="col col-6">
									<label class="label">Счет</label>
									<select name="smeta[]" class="autoselect" placeholder="выберите из списка..." multiple >
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
																$nds18=isset($p['doc_nds18'])&&$p['doc_nds18']?', в т.ч. НДС 18%: '.transform::price_o($p['doc_nds18']-$p['doc_nds18']*$_order[0]['m_orders_discount']/100).' р.':' без НДС';
																$sum=isset($p['doc_sum'])&&$p['doc_sum']?transform::price_o($p['doc_sum']):0;
																echo '<option value="'.$_document[0]['m_documents_id'].'"'.(in_array($_document[0]['m_documents_id'],$_buh['m_buh_invoice_numb'])?' selected ':'').'>',
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
								<section class="col col-6">
									<label class="label">Заказ</label>
									<select name="m_buh_order" class="autoselect" placeholder="выберите из списка...">
										<option value="0">выберите из списка...</option>
										<?
											foreach($orders->orders_id as $_order){
												$_order=$_order[0];
												echo '<option value="'.$_order['m_orders_id'].'" performer="'.$_order['m_orders_performer'].'" customer="'.$_order['m_orders_customer'].'" '.($_buh['m_buh_orders_id']==$_order['m_orders_id']?'selected':'').' >',
													$_order['m_orders_name'],
												'</option>';
											}
										?>
									</select>
								</section>
							</div>
							<div class="row">
								<section class="col col-6">
									<label class="label">Исполнитель</label>
									<select name="m_buh_performer" class="autoselect" placeholder="выберите из списка..." >
										<option value="0">выберите из списка...</option>
										<?
											foreach($contragents->getInfo() as $contragents_){
												echo '<option value="'.$contragents_[0]['m_contragents_id'].'" '.($_buh['m_buh_performer']==$contragents_[0]['m_contragents_id']?'selected':'').' >',
													$contragents->getName($contragents_[0]['m_contragents_id']),
												'</option>';
											}
										?>
									</select>
								</section>
								<section class="col col-6">
									<label class="label">Заказчик</label>
									<select name="m_buh_customer" class="autoselect" placeholder="выберите из списка..." >
										<option value="0">выберите из списка...</option>
										<?
											foreach($contragents->getInfo() as $contragents_){
												echo '<option value="'.$contragents_[0]['m_contragents_id'].'" '.($_buh['m_buh_customer']==$contragents_[0]['m_contragents_id']?'selected':'').' >',
													$contragents->getName($contragents_[0]['m_contragents_id']),
												'</option>';
											}
										?>
									</select>
								</section>
							</div>
						</fieldset>
						<header>
							Операция
						</header>
						<fieldset>
							<div class="row">
								<section class="col col-2">
									<label class="label">Тип</label>
									<select name="m_buh_type" class="autoselect">
										<option value="-1"<?=($_buh['m_buh_type']==-1?' selected ':'');?>>Расход</option>
										<option value="1"<?=($_buh['m_buh_type']==1?' selected ':'');?>>Доход</option>
									</select>
								</section>
								<section class="col col-2">
									<label class="label">Нал / безнал</label>
									<select name="m_buh_cash" class="autoselect">
										<option value="0"<?=($_buh['m_buh_cash']==0?' selected ':'');?>>Безнал</option>
										<option value="1"<?=($_buh['m_buh_cash']==1?' selected ':'');?>>Наличные</option>
									</select>
								</section>
								<section class="col col-2">
									<label class="label">Сумма</label>
									<label class="input">
										<i class="icon-append fa fa-money"></i>
										<input type="text" name="m_buh_sum" style="text-align:right" value="<?=$_buh['m_buh_sum']?>" placeholder="0.00">
									</label>
								</section>
								<section class="col col-2">
									<label class="label">Сумма НДС</label>
									<label class="input">
										<i class="icon-append fa fa-hand-o-up" id="nds18"></i>
										<input type="text" name="m_buh_sum_nds" style="text-align:right" value="<?=$_buh['m_buh_sum_nds']?>" placeholder="без НДС">
									</label>
								</section>
								<section class="col col-2">
									<label class="label">Дата операции</label>
									<label class="input">
										<i class="icon-append fa fa-calendar"></i>
										<input type="text" name="m_buh_date_" class="datepicker" data-mask="99.99.9999" value="<?=dtu($_buh['m_buh_date'],'d.m.Y')?>" placeholder="<?=dtu('','d.m.Y')?>">
										<input type="hidden" name="m_buh_date">
									</label>
								</section>
								<section class="col col-2">
									<label class="label">&nbsp;</label>
									<label class="checkbox">
										<input type="checkbox" name="m_buh_status_pay" value="1"<?=($_buh['m_buh_status_pay']==1?' checked ':'');?>/>
										<i></i>
										Исполнена
									</label>
								</section>
							</div>
							<section>
								<label class="label">Отслеживать документы</label>
								<div class="row">
									<?
										$start=0;
										$count=ceil(sizeof($templates)/3);
										for($i=0;$i<3;$i++){		
											echo '<div class="col col-4">';
											for($j=$i*$count;$j<($i+1)*$count;$j++){
												if(isset($templates[$j]))
													echo '<label class="checkbox">
															<input type="checkbox" name="m_buh_documents_templates[]" value="'.$templates[$j]['m_documents_templates_id'].'" '.(in_array($templates[$j]['m_documents_templates_id'],$_buh['m_buh_documents_templates'])?' checked ':'').'>
															<i></i>',
															$templates[$j]['m_documents_templates_name'],
														'</label>';
											}
											echo '</div>';
										}
									?>
								</div>
							</section>
							<section>
								<label class="label">Комментарий</label>
								<label class="textarea textarea-resizable"> 										
									<textarea name="m_buh_comment" rows="3" class="custom-scroll"><?=$_buh['m_buh_comment'];?></textarea> 
								</label>
							</section>
							<section>
									<label class="label">Теги</label>
									<a href="#" id="m_buh_tags" data-type="select2" data-pk="1" data-original-title="Укажите теги"><?=str_replace('|',', ',$_buh['m_buh_tags']);?></a>
									<input type="hidden" name="m_buh_tags" value="<?=$_buh['m_buh_tags'];?>"/>
							</section>
						</fieldset>
						<header>
							Отчетность
						</header>
						<fieldset>
							<div class="row">
									<section class="col col-2">
										<label class="label">Год</label>
										<label class="input">
											<i class="icon-append fa fa-calendar"></i>
											<input type="text" name="m_buh_year" data-mask="9999" value="<?=dtu($_buh['m_buh_year'],'Y')?>" placeholder="<?=dtu('','Y')?>">
										</label>
									</section>
									<section class="col col-2">
										<label class="label">Квартал</label>
										<select name="m_buh_quarter" class="autoselect">
											<?
												for($i=1;$i<5;$i++){
													echo '<option value="'.$i.'" '.($_buh['m_buh_quarter']==$i?' selected ':'').'>',
														$i,
													'</option>';
												}
											?>
										</select>
									</section>
									<section class="col col-3">
										<label class="label">Назначение</label>
										<select name="m_buh_target" class="autoselect">
											<option value="1"<?=($_buh['m_buh_target']==1?' selected ':'');?>>Товары/услуги</option>
											<option value="2"<?=($_buh['m_buh_target']==2?' selected ':'');?>>Прочее</option>
											<option value="3"<?=($_buh['m_buh_target']==3?' selected ':'');?>>Займ</option>
											<option value="4"<?=($_buh['m_buh_target']==4?' selected ':'');?>>Зарплаты</option>
											<option value="5"<?=($_buh['m_buh_target']==5?' selected ':'');?>>Налоги</option>
										</select>
									</section>
									<section class="col col-2">
										<label class="label">№ платёжки</label>
										<label class="input">
											<i class="icon-append fa fa-sort-numeric-asc"></i>
											<input type="text" name="m_buh_payment_numb" value="<?=$_buh['m_buh_payment_numb']?>">
										</label>
									</section>
									<section class="col col-3">
										<label class="label">&nbsp;</label>
										<label class="checkbox">
											<input type="checkbox" name="m_buh_no_calc" value="1"<?=($_buh['m_buh_no_calc']==1?' checked ':'');?>/>
											<i></i>
											Не учитывать в итоге
										</label>
										<label class="checkbox">
											<input type="checkbox" name="m_buh_avans" value="1"<?=($_buh['m_buh_avans']==1?' checked ':'');?>/>
											<i></i>
											Для авансового СФ
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
						<input type="hidden" name="action" value="m_buh_pay_change"/>
						<input type="hidden" name="m_buh_id" value="<?=$_buh['m_buh_id'];?>"/>
					</form>

				</div>
				<!-- end widget content -->

			</div>
			<!-- end widget div -->

		</div>	

		</article>
</div>
</section>
<?
}
else{

if(get('m_buh_order')){
	$order=$orders->orders_id[get('m_buh_order')][0];
	$org=$contragents->getInfo($order['m_orders_performer']);
	$client=$contragents->getInfo($order['m_orders_customer']);
}

//тэги
$pays=$buh->getInfo();
$buh_tags=array();
foreach($pays as $_pay){
	$_pay=$_pay[0];
	$buh_tags=array_merge($buh_tags,explode('|',$_pay['m_buh_tags']));
}
$buh_tags=array_unique(array_diff($buh_tags,array('')));
array_walk($buh_tags,function(&$el){$el='"'.$el.'"';});

//ищем документы с привязанными платежами
$pays_docs=array();
foreach($documents->documents_id as $_document){
	$_document=$_document[0];
	if($_document['m_documents_pays']){
		$_pays=explode('|',$_document['m_documents_pays']);
		foreach($_pays as $_pay)
			if(isset($pays[$_pay]))
				$pays[$_pay][0]['docs'][]=$_document['m_documents_id'];
	}
}

$templates=$documents->getDocsBuh();

$content->setJS('
	
	runAllForms();
	
	$("#buh-add").validate({
		rules : {
			m_buh_sum : {
				number: true,
				required: true
			},
			m_buh_sum_nds : {
				number: true
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});

	
	$(".datatable .delete").editable({
		url: "/ajax/services_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete"))
				$(this).parents("tr:first").remove();
		}
	});
	
	/* ТОЛЬКО ЦИФРЫ, ТОЧКА ВМЕСТО ЗАПЯТОЙ */
	$(document).on("keyup","[name=\'m_buh_sum\'],[name=\'m_buh_sum_nds\']",function(){
		$(this).val($(this).val().replace(",","."));
		$(this).val($(this).val().replace(/[^.0-9]/gim,""));
	});
	/* ОКРУГЛЕНИЕ ДО 2-Х ЗНАКОВ ПОСЛЕ ТОЧКИ В ПОЛЯХ С ЧИСЛОВЫМИ ЗНАЧЕНИЯМИ */
	$(document).on("change","[name=\'m_buh_sum\'],[name=\'m_buh_sum_nds\']",function(){
		$(this).val(($(this).val()*1).toFixed(2));
	});
	
	
	$("#m_buh_tags").editable({
		inputclass: "input-large",
		select2: {
			tags: ['.implode(',',$buh_tags).'],
			placeholder: "Укажите теги",
			tokenSeparators: [","]
		}
	});	
	$("#m_buh_tags").on("save",function(e,params){
		$("[name=\'m_buh_tags\']").val(params.newValue.join("|"));
	});
	
	/* АВТОВЫБОР СТОРОН ПРИ ВЫБОРЕ ЗАКАЗА */
	$("[name=\'m_buh_order\']").on("change",function(){
		$.post(
			"/ajax/invoice_select_contragents.php",
			{
				invoice:$("[name=\'smeta[]\'] option:selected").val()
			},
			function(data){
				if(data!="ERROR"){
					data=data.split("|");
					$("[name=\'m_buh_performer\']").select2("val",data[0]);
					$("[name=\'m_buh_customer\']").select2("val",data[1]);
				}
				
			}
		)
	});
	
	/* АВТОВЫБОР ЗАКАЗА ПРИ ВЫБОРЕ СЧЕТА */
	$("[name=\'smeta[]\']").on("change",function(){
		$("[name=\'m_buh_order\']").select2("val",$("[name=\'smeta[]\'] option:selected").parent().attr("id")).triggerHandler("change");
	});
	
	$("#nds18").on("click",function(){
		$(this).next().val(($("[name=m_buh_sum]").val()*.18/1.18).toFixed(2));
	});
	
	$("#select_order").on("change",function(){
		localStorage.setItem("select_order",$(this).find("option:selected").val());
	});

	/* $("#datatable_pays").find("td.sum").on("click",function(){
		if($(this).hasClass("selected"))
			$(this).removeClass("selected");
		else{
			$(this).addClass("selected");
			
		}
		var p=[];
		$("#datatable_pays").find("td.sum").each(function(index,el){
			if($(el).hasClass("selected"))
				p.push($(el).parents("tr:first").index());
			localStorage.setItem("select_pay",p.join("|"));
		});
	}); */
	
	/* var p=[];
	if(localStorage.getItem("select_pay")!==null&&localStorage.getItem("select_pay").split("|") instanceof Array)
		p=localStorage.getItem("select_pay").split("|");
	p.forEach(function(item, i, arr){
		$("#datatable_pays").find("tr").eq(item*1+2).find("td.sum").addClass("selected");
	}); */
	
	if($(".np").find("b").text().charAt(0)=="-")
		$(".np b").css("text-decoration","line-through").after(" 0.00");
	
');
?>

<section id="widget-grid" class="">

<?
if(isset($_GET['success']))
	echo '<div class="row"><article class="col-lg-12">
			<div class="alert alert-success alert-block">
				<a class="close" data-dismiss="alert" href="#">x</a>
				<h4 class="alert-heading">Выполнено!</h4>
				Информация о клиенте успешно добавлена!
			</div>
		</article></div>';
if(isset($_GET['error']))
	echo '<div class="row"><article class="col-lg-12">
			<div class="alert alert-danger alert-block">
				<a class="close" data-dismiss="alert" href="#">x</a>
				<h4 class="alert-heading">Произошла ошибка!</h4>
				Произошла ошибка при сохранении данных.
			</div>
		</article></div>';



?>
	
	<div class="row">
	
		<article class="col-lg-6 sortable-grid ui-sortable">
		
		<div class="jarviswidget" id="wid-id-1" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">	
					
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Добавить операцию</h2>
				</header>

			<!-- widget div-->
			<div>
				<!-- widget content -->
				<div class="widget-body">
					<form id="buh-add" class="smart-form" method="post">
						<header>
							Контрагенты
						</header>
						<fieldset>
							<div class="row">
								<section class="col col-6">
									<label class="label">Счет</label>
									<select name="smeta[]" class="autoselect" placeholder="выберите из списка..." multiple >
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
																$nds18=isset($p['doc_nds18'])&&$p['doc_nds18']?', в т.ч. НДС 18%: '.transform::price_o($p['doc_nds18']-$p['doc_nds18']*$_order[0]['m_orders_discount']/100).' р.':' без НДС';
																$sum=isset($p['doc_sum'])&&$p['doc_sum']?transform::price_o($p['doc_sum']):0;
																echo '<option value="'.$_document[0]['m_documents_id'].'">',
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
								<section class="col col-6">
									<label class="label">Заказ</label>
									<select name="m_buh_order" class="autoselect" placeholder="выберите из списка...">
										<option value="0">выберите из списка...</option>
										<?
											foreach($orders->orders_id as $_order){
												$_order=$_order[0];
												echo '<option value="'.$_order['m_orders_id'].'" performer="'.$_order['m_orders_performer'].'" customer="'.$_order['m_orders_customer'].'">',
													$_order['m_orders_name'],
												'</option>';
											}
										?>
									</select>
								</section>				
							</div>
							<div class="row">
								<section class="col col-6">
									<label class="label">Исполнитель</label>
									<select name="m_buh_performer" class="autoselect" placeholder="выберите из списка..." >
										<option value="0">выберите из списка...</option>
										<?
											foreach($contragents->getInfo() as $contragents_){
												echo '<option value="'.$contragents_[0]['m_contragents_id'].'"'.($contragents_[0]['m_contragents_id']==3363726835?' selected ':'').'>',
													$contragents->getName($contragents_[0]['m_contragents_id']),
												'</option>';
											}
										?>
									</select>
								</section>
								<section class="col col-6">
									<label class="label">Заказчик</label>
									<select name="m_buh_customer" class="autoselect" placeholder="выберите из списка..." >
										<option value="0">выберите из списка...</option>
										<?
											foreach($contragents->getInfo() as $contragents_){
												echo '<option value="'.$contragents_[0]['m_contragents_id'].'">',
													$contragents->getName($contragents_[0]['m_contragents_id']),
												'</option>';
											}
										?>
									</select>
								</section>
							</div>
						</fieldset>
						<header>
							Операция
						</header>
						<fieldset>
							<div class="row">
								<section class="col col-2">
									<label class="label">Тип</label>
									<select name="m_buh_type" class="autoselect">
										<option value="-1" selected >Расход</option>
										<option value="1">Доход</option>
									</select>
								</section>
								<section class="col col-2">
									<label class="label">Нал / безнал</label>
									<select name="m_buh_cash" class="autoselect">
										<option value="0" selected >Безнал</option>
										<option value="1">Наличные</option>
									</select>
								</section>
								<section class="col col-2">
									<label class="label">Сумма</label>
									<label class="input">
										<i class="icon-append fa fa-money"></i>
										<input type="text" name="m_buh_sum" style="text-align:right" placeholder="0.00">
									</label>
								</section>
								<section class="col col-2">
									<label class="label">Сумма НДС</label>
									<label class="input">
										<i class="icon-append fa fa-hand-o-up" id="nds18"></i>
										<input type="text" name="m_buh_sum_nds" style="text-align:right" placeholder="без НДС">
									</label>
								</section>
								<section class="col col-2">
									<label class="label">Дата операции</label>
									<label class="input">
										<i class="icon-append fa fa-calendar"></i>
										<input type="text" name="m_buh_date_" class="datepicker" data-mask="99.99.9999" placeholder="<?=dtu('','d.m.Y')?>">
										<input type="hidden" name="m_buh_date">
									</label>
								</section>
								<section class="col col-2">
									<label class="label">&nbsp;</label>
									<label class="checkbox">
										<input type="checkbox" name="m_buh_status_pay" value="1" checked />
										<i></i>
										Исполнена
									</label>
								</section>
							</div>
							<section>
								<label class="label">Отслеживать документы</label>
								<div class="row">
									<?
										$start=0;
										$count=ceil(sizeof($templates)/3);
										for($i=0;$i<3;$i++){		
											echo '<div class="col col-4">';
											for($j=$i*$count;$j<($i+1)*$count;$j++){
												if(isset($templates[$j]))
													echo '<label class="checkbox">
															<input type="checkbox" name="m_buh_documents_templates[]" value="'.$templates[$j]['m_documents_templates_id'].'">
															<i></i>',
															$templates[$j]['m_documents_templates_name'],
														'</label>';
											}
											echo '</div>';
										}
									?>
								</div>
							</section>
							<section>
								<label class="label">Комментарий</label>
								<label class="textarea textarea-resizable"> 										
									<textarea name="m_buh_comment" rows="3" class="custom-scroll"></textarea> 
								</label>
							</section>
							<section>
								<label class="label">Теги</label>
								<a href="#" id="m_buh_tags" data-type="select2" data-pk="1" data-original-title="Укажите теги"></a>
								<input type="hidden" name="m_buh_tags"/>
							</section>
						</fieldset>
						<header>
							Отчетность
						</header>
						<fieldset>
							<div class="row">
									<section class="col col-2">
										<label class="label">Год</label>
										<label class="input">
											<i class="icon-append fa fa-calendar"></i>
											<input type="text" name="m_buh_year" data-mask="9999" placeholder="<?=dtu('','Y')?>">
										</label>
									</section>
									<section class="col col-2">
										<label class="label">Квартал</label>
										<select name="m_buh_quarter" class="autoselect">
											<?
												for($i=1;$i<5;$i++){
													echo '<option value="'.$i.'"'.($i==ceil(dtu('','m')/3)?' selected ':'').'>',
														$i,
													'</option>';
												}
											?>
										</select>
									</section>
									<section class="col col-3">
										<label class="label">Назначение</label>
										<select name="m_buh_target" class="autoselect">
											<option value="1" selected >Товары</option>
											<option value="2">Услуги</option>
											<option value="3">Займ</option>
											<option value="4">Зарплаты</option>
											<option value="5">Налоги</option>
										</select>
									</section>
									<section class="col col-2">
										<label class="label">№ платёжки</label>
										<label class="input">
											<i class="icon-append fa fa-sort-numeric-asc"></i>
											<input type="text" name="m_buh_payment_numb">
										</label>
									</section>
									<section class="col col-3">
										<label class="label">&nbsp;</label>
										<label class="checkbox">
											<input type="checkbox" name="m_buh_no_calc" value="1"/>
											<i></i>
											Не учитывать в итоге
										</label>
										<label class="checkbox">
											<input type="checkbox" name="m_buh_avans" value="1"/>
											<i></i>
											Для авансового СФ
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
						<input type="hidden" name="action" value="m_buh_pay_add"/>
					</form>

				</div>
				<!-- end widget content -->

			</div>
			<!-- end widget div -->

		</div>	

	</div>
	
	<div class="row">
<?

if($pays){
?>
		<article class="col-lg-12">
	
			<div class="jarviswidget jarviswidget-color-blue" id="wid-id-20" 
				data-widget-editbutton="false"
				data-widget-colorbutton="false"
				data-widget-deletebutton="false"
				data-widget-togglebutton="false">
	
				<header>
					<span class="widget-icon"> <i class="fa fa-table"></i> </span>
					<h2>Все операции</h2>

				</header>

				<div>

					<div class="widget-body no-padding">

						<table id="datatable_pays" class="datatable table table-striped table-bordered table-hover" width="100%">
	
							<thead>
								<tr>
									<th></th>
									<th class="hasinput">
										<select class="autoselect th-filter" placeholder="выберите из списка..." id="select_order">
											<option value="0">Все заказы</option>
											<?
												foreach($orders->orders_id as $order_){
													echo '<option value="'.$order_[0]['m_orders_id'].'">
															'.$order_[0]['m_orders_name'].'
														</option>';
												}
											?>
										</select>
									</th>
									<th></th>
									<th></th>
									<th class="hasinput">
										<select class="autoselect th-filter" placeholder="выберите из списка..." id="select_order">
											<option value="0">Все операции</option>									
											<option value="+">+</option>									
											<option value="–">–</option>									
										</select>
									</th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
								</tr>
								<tr>
									<th class="order" style="width:6%">Дата</th>
									<th style="width:17%">Заказ</th>
									<th style="width:12%">Исполнитель</th>
									<th style="width:15%">Заказчик</th>
									<th style="width:8%">Сумма</th>
									<th style="width:10%">Документы</th>
									<th style="width:4%">Тип</th>
									<th style="width:4%">Учет</th>
									<th style="width:6%">Назначение</th>
									<th style="width:5%">№ п/п</th>
									<th style="width:10%">Детали</th>
								</tr>
							</thead>
							<tbody>
								<?
$targets=array('','Товар/услуга','Прочее','Займ','Зарплата','Налог');
foreach($pays as $_pays){
	$_pays=$_pays[0];
	$performer=$contragents->getInfo($_pays['m_buh_performer'])['m_contragents_c_name_short']?$contragents->getInfo($_pays['m_buh_performer'])['m_contragents_c_name_short']:$contragents->getInfo($_pays['m_buh_performer'])['m_contragents_p_fio'];
	$customer=$contragents->getInfo($_pays['m_buh_customer'])['m_contragents_c_name_short']?$contragents->getInfo($_pays['m_buh_customer'])['m_contragents_c_name_short']:$contragents->getInfo($_pays['m_buh_customer'])['m_contragents_p_fio'];
	$order_=isset($orders->orders_id[$_pays['m_buh_orders_id']][0])?$orders->orders_id[$_pays['m_buh_orders_id']][0]:array('m_orders_id'=>0);
	$pay_templates=explode('|',$_pays['m_buh_documents_templates']);
	echo '
	<tr '.($_pays['m_buh_status_pay']==0?'class="not-paid"':'class="paid"').'>
		<td>
			'.dtu($_pays['m_buh_date'],'Y-m-d').'<br/>'.dtu($_pays['m_buh_date'],'H:i:s').'
		</td>
		<td class="orderName unionrows">
			'.($order_['m_orders_id']?'<a href="/orders/new/?action=details&m_orders_id='.$order_['m_orders_id'].'">'.$order_['m_orders_name'].'</a>':'—').'
		</td>
		<td>
			<a href="/contragents/new/?action=details&m_contragents_id='.$contragents->getInfo($_pays['m_buh_performer'])['m_contragents_id'].'">'.$performer.'</a>
		</td>		
		<td class="unionrows">
			<a href="/contragents/new/?action=details&m_contragents_id='.$contragents->getInfo($_pays['m_buh_customer'])['m_contragents_id'].'">'.$customer.'</a>';
echo '
		</td>
		<td data-order="'.$_pays['m_buh_sum'].'" class="sum" >
			<b><nobr><span style="color:#'.($_pays['m_buh_type']==1?'4ab54a">+ ':'fb6262">– ').transform::price_o($_pays['m_buh_sum']).'</span></nobr></b><br/>
			<span style="color:#999;font-size:80%"><nobr>'.($_pays['m_buh_sum_nds']?'в т.ч. НДС 18%: <b>'.transform::price_o($_pays['m_buh_sum_nds']).'</b>':'без НДС').'</nobr></span>
		</td>
		<td >';
//выводим метки документов
$inv=0;
if($pay_templates[0]){
	//находим все документы по платежу
	$pay_docs=$documents->getPayDocs($_pays['m_buh_id']);
	//находим другие платежи, относящиеся к этим документам
	$other_pays=array();
	foreach($pay_docs as $_doc)
		$other_pays=array_merge($other_pays,$_doc['m_documents_pays']);
	$other_pays=array_diff(array_unique($other_pays),array(null));
	//счтитаем сумму других платежей
	$sum_all_other_pays=0;
	foreach($other_pays as $_other_pay) 
		$sum_all_other_pays+=$buh->getInfo((string)$_other_pay)['m_buh_sum'];
	//для каждой метки находим готовые документы
	foreach($pay_templates as $_pay_templates){
		$css_class="label-danger";
		$popover_content=0;
		if($pay_docs){
			$popover='
						<table class=\'doc_details\'>
							<thead>
								<tr>
									<th style=\'width:25%\'>№</th>
									<th style=\'width:25%\'>Дата</th>
									<th style=\'width:20%\'>Сумма</th>
									<th style=\'width:15%\'>НДС</th>
									<th style=\'width:15%\'>Скачать</th>
								</tr>
							</thead>
							<tbody>';
			$all_sum=0;
			$all_nds=0;
			//находим все документы по платежу
			foreach($pay_docs as $_doc)
				//выбираем документы по данному типу
				if($_doc['m_documents_templates_id']==$_pay_templates){
					if($_pay_templates=='2363374033')
						$inv=$_doc['m_documents_id'];
					$template=$documents->documents_templates[$_doc['m_documents_templates_id']][0];
					$url='/files/'.$template['m_documents_templates_folder'].'/'.$_doc['m_documents_folder'].'/'.$template['m_documents_templates_filename'].'.pdf?'.mt_rand(100000,999999);
					$p=json_decode($_doc['m_documents_params'],true);
					$nds18=isset($p['doc_nds18'])&&$p['doc_nds18']?transform::price_o($p['doc_nds18']):'без НДС';
					$sum=isset($p['doc_sum'])&&$p['doc_sum']?$p['doc_sum']:0;
					$all_sum+=$sum;
					$all_nds+=isset($p['doc_nds18'])&&$p['doc_nds18']?$p['doc_nds18']:0;
					$popover.='	<tr>
									<td><a title=\'Подробнее\' href=\'/documents/new/?m_documents_id='.$_doc['m_documents_id'].'&m_documents_templates_id='.$_doc['m_documents_templates_id'].'&m_documents_order='.$_doc['m_documents_order'].'&action=details\' target=\'_blank\'>'.$_doc['m_documents_numb'].'</a></td>
									<td>'.dtu($_doc['m_documents_date'],'d.m.Y').'</td>
									<td><nobr>'.transform::price_o($sum).'</nobr></td>
									<td><nobr>'.$nds18.'</nobr></td>
									<td><nobr><a class=\'document_pdf\' title=\'Скачать\' href=\''.$url.'\' target=\'_blank\'><i class=\'fa fa-lg fa-file-pdf-o\'></i> .pdf,&nbsp;'.$_doc['m_documents_filesize'].'</a></nobr></td>
								</tr>';
					$popover_content=1;
				}
			$popover.='
							</tbody>
							<tfoot>
								<tr>
									<th colspan=\'2\'>Итого:</th>
									<th><nobr>'.transform::price_o($all_sum).'</nobr></th>
									<th><nobr>'.transform::price_o($all_nds).'</nobr></th>
									<th></th>
								</tr>
							</tfoot>
					</table>';
			//если сумма по документам равна сумме платежа или сумме связанных платежей
			if($all_sum==$_pays['m_buh_sum']||$all_sum==$sum_all_other_pays)
				$css_class="label-success";
			elseif($all_sum>0){
				$css_class="label-warning";
			}
		}
		$popover=$popover_content?$popover:($_pay_templates=='3552326767'&&$inv?'Документы отсутствуют<br/><a style=\'padding-top:5px;\' href=\'/documents/new/?m_documents_templates_id=3552326767&smeta='.$inv.'\'><b>Создать УПД</b></a>':'Документы отсутствуют');
		echo '<span class="label '.$css_class.'" rel="popover" data-html="true" data-placement="top" data-original-title="'.$documents->documents_templates[$_pay_templates][0]['m_documents_templates_name'].'" data-content="'.$popover.'">', 
			$documents->documents_templates[$_pay_templates][0]['m_documents_templates_name_short'],
			'</span>';
		if($css_class=='label-warning'||$css_class=='label-danger')
			echo '<span style="display:none">d0</span>';
	}
}
echo '
		</td>
		<td >
			'.($_pays['m_buh_cash']?'нал':'б/н').'
		</td>
		<td>
			'.$_pays['m_buh_year'].'.'.$_pays['m_buh_quarter'].'
		</td>
		<td>
			'.$targets[$_pays['m_buh_target']].'
		</td>
		<td>
			'.$_pays['m_buh_payment_numb'].'
		</td>	
		
		<td align="center">
			<a href="javascript:void(0);" '.($_pays['m_buh_comment']?' rel="popover" data-placement="left" data-original-title="Комментарий" data-content="'.$_pays['m_buh_comment'].'"':'').' class="btn btn-primary btn-xs" rel="popover" data-placement="left"><i class="fa fa-comment"></i></a>
			<a title="Редактировать" class="btn btn-primary btn-xs" href="'.url().'?m_buh_id='.$_pays['m_buh_id'].'&action=details"><i class="fa fa-pencil"></i></a>
			<a title="Удалить" class="btn btn-danger btn-xs delete" href="#" data-pk="'.$_pays['m_buh_id'].'" data-name="m_buh_id" data-title="Введите пароль для удаления" data-placement="left"><i class="fa fa-trash-o"></i></a>
		</td>
	</tr>';
}
								?>
							</tbody>
						</table>

					</div>

				</div>

			</div>

		</article>

<?
}
?>

	</div>
</section>
<?
}
?>