<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$services,$info,$products;

if($id=get('m_services_id')){
	$service=$services->services_id[$id][0];
	$service['m_services_categories_id']=explode('|',$service['m_services_categories_id']);
	$service['m_services_links']=explode('|',$service['m_services_links']);
	
	$content->setJS('
	
		runAllForms();
			
		$("#services-add").validate({
			rules : {
				m_services_name : {
					maxlength : 280,
					required : true
				},
				m_services_unit : {
					required : true
				},
				m_services_price_general : {
					maxlength : 15,
					required : true
				},
				m_services_price_general_w : {
					maxlength : 15,
					required : true
				},
				m_services_price_wholesale : {
					maxlength : 15,
					required : true
				},
				m_services_price_wholesale_w : {
					maxlength : 15,
					required : true
				},
				m_services_contragents_id : {
					required : true
				},
				"m_services_categories_id[]" : {
					required : true
				}
			},
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});	

		$("input[name=\'m_services_products_name[]\']").sug({
			idValue: "m_services_products_id[]"
		});
		
		$("#products1").df({
			max:20,
			f_a:function(){
				$("#products1 .multirow:last").find("input[name=\'m_services_products_name[]\']").sug({
					idValue: "m_services_products_id[]"
				});
			}
		});

		

		
	');
	?>

	<section id="widget-grid" class="">
		
		<div class="row">
					
			<article class="col-lg-6 sortable-grid ui-sortable">
				<div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">		
					<header>
						<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
						<h2>Редактировать услугу</h2>
					</header>
					<div>
						<div class="widget-body">
							<form id="services-add" class="smart-form" method="post">
								<header>
									Основные данные
								</header>
								<fieldset>
									<div class="row">
										<section class="col col-9">
											<label class="label">Наименование</label>
											<label class="input">
												<input type="text" name="m_services_name" placeholder="например, Монтаж полотенцесушителя" value="<?=$service['m_services_name']?>">
											</label>
										</section>
										<section class="col col-3">
											<label class="label">Единица измерения</label>
											<select name="m_services_unit" class="autoselect">
												<?
													foreach($info->getUnits() as $t_)
														echo '<option value="'.$t_[0]['m_info_units_id'].'" '.($t_[0]['m_info_units_id']==$service['m_services_unit']?' selected':'').'>',
															$t_[0]['m_info_units_name'],
															'</option>';
												?>
											</select>
										</section>
									</div>
									<div class="row">
										<section class="col col-9">
											<label class="label">Нахождение в категориях</label>
											<select name="m_services_categories_id[]" style="width:100%" multiple class="autoselect" placeholder="выберите из списка...">
												<? 
													$categories=array();
													$services->categories_childs(0,$categories,2);
													foreach($categories as $categories_){
														echo '<option value="'.$categories_['m_services_categories_id'].'" '.(in_array($categories_['m_services_categories_id'],$service['m_services_categories_id'])?' selected':'').'>
																'.$categories_['m_services_categories_name'].'
															</option>';																
													}
												?>
											</select>
										</section>
										<section class="col col-3">
											<label class="label">Организация</label>
											<select name="m_services_contragents_id" class="autoselect">
												<?
													foreach($contragents->getInfo() as $contragents_){
														$ct=explode('|',$contragents_[0]['m_contragents_type']);
														if(in_array(1,$ct))
															echo '<option value="'.$contragents_[0]['m_contragents_id'].'" '.($contragents_[0]['m_contragents_id']==$service['m_services_contragents_id']?' selected':'').'>',
																$contragents_[0]['m_contragents_c_name_short']?$contragents_[0]['m_contragents_c_name_short']:$contragents_[0]['m_contragents_c_name_full'],
															'</option>';
													}
												?>
											</select>
										</section>
									</div>
									<section>
										<label class="label">Заметки</label>
										<label class="textarea textarea-resizable"> 										
											<textarea name="m_services_comment" rows="3" class="custom-scroll"><?=$service['m_services_comment']?></textarea> 
										</label>
									</section>
								</fieldset>
								<header>
									Цены
								</header>
								<fieldset>
									<div class="row">
										<section class="col col-3">
											<label class="label">Для клиента</label>
											<label class="input">
												<i class="icon-append fa fa-rub"></i>
												<input type="text" name="m_services_price_general" style="text-align:right;" value="<?=$service['m_services_price_general']?>">
											</label>
										</section>
										<section class="col col-3">
											<label class="label">Для работника</label>
											<label class="input">
												<i class="icon-append fa fa-rub"></i>
												<input type="text" name="m_services_price_general_w" style="text-align:right;" value="<?=$service['m_services_price_general_w']?>">
											</label>
										</section>
									</div>
								</fieldset>
								<header>
									Дополнительные опции
								</header>
								<fieldset>
									<div class="row">
										<section class="col col-4">
										  <label class="checkbox">
											<input type="checkbox" name="m_services_show_site" <?=($service['m_services_show_site']?' checked':'')?> value="1"/>
											<i></i>
											Показывать на сайте
										  </label>
										  <label class="checkbox">
											<input type="checkbox" name="m_services_show_price" <?=($service['m_services_show_price']?' checked':'')?> value="1"/>
											<i></i>
											Выгружать в прайс
										  </label>
										</section>
										<section class="col col-8">
											<label class="label">Связанные работы</label>
											<select name="m_services_links[]" id="m_services_links" style="width:100%" multiple class="autoselect" placeholder="выберите из списка...">
												<?
													foreach($services->services_display_li() as $k=>$v)
														if(strlen($k)==10&&isset($v['items'])){
															echo '<optgroup label="'.$v['m_services_categories_name'].'">';
																foreach($v['items'] as $services_)
																	if($services_['m_services_id']!=$service['m_services_id'])
																		echo '<option value="'.$services_['m_services_id'].'" '.(in_array($services_['m_services_id'],$service['m_services_links'])!==false?' selected ':'').'>',
																				'['.$v['m_services_categories_name'].'] '.$services_['m_services_name'],
																			'</option>';
															echo '</optgroup>';			
														}
												?>
											</select>
										</section>
									</div>
								</fieldset>
								<header>
									Используемые материалы
								</header>
								<fieldset>
									<div  id="products1">
										<?
											if($service['m_services_products']){
												$service['m_services_products']=json_decode($service['m_services_products'],true);
												foreach($service['m_services_products'] as $k=>$v){
										?>
											<div class="multirow">
												<div class="row">
													<section class="col col-2">
														<label class="input">
															<input type="text" name="m_services_products_id[]"  placeholder="арт." value="<?=$k?>" readonly>
														</label>
													</section>
													<section class="col col-5">
														<label class="input">
															<i class="icon-append fa fa-cube"></i>
															<input type="text" name="m_services_products_name[]"  placeholder="товар" value="<?=!empty($products->products_id[$k])?$products->products_id[$k][0]['m_products_name']:''?>">
														</label>
													</section>
													<section class="col col-2">
														<label class="input">
															<i class="icon-append fa fa-cubes"></i>
															<input type="text" name="m_services_products_count[]"  placeholder="кол-во" value="<?=$v?>">
														</label>
													</section>
													<section class="col col-3" style="text-align:right">
														<div class="btn-group btn-labeled multirow-btn">
															<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
															<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
																<span class="caret"></span>
															</a>
															<ul class="dropdown-menu">
																<li>
																	<a href="javascript:void(0);" class="add">Добавить товар</a>
																</li>
																<li>
																	<a href="javascript:void(0);" class="delete">Удалить товар</a>
																</li>
															</ul>
														</div>
													</section>
												</div>
											</div>
										<?
												}
											}
											else{
										?>
											<div class="multirow">
												<div class="row">
													<section class="col col-2">
														<label class="input">
															<input type="text" name="m_services_products_id[]"  placeholder="арт." readonly>
														</label>
													</section>
													<section class="col col-5">
														<label class="input">
															<i class="icon-append fa fa-cube"></i>
															<input type="text" name="m_services_products_name[]"  placeholder="товар">
														</label>
													</section>
													<section class="col col-2">
														<label class="input">
															<i class="icon-append fa fa-cubes"></i>
															<input type="text" name="m_services_products_count[]"  placeholder="кол-во">
														</label>
													</section>
													<section class="col col-3" style="text-align:right">
														<div class="btn-group btn-labeled multirow-btn">
															<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
															<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
																<span class="caret"></span>
															</a>
															<ul class="dropdown-menu">
																<li>
																	<a href="javascript:void(0);" class="add">Добавить товар</a>
																</li>
																<li>
																	<a href="javascript:void(0);" class="delete">Удалить товар</a>
																</li>
															</ul>
														</div>
													</section>
												</div>
											</div>
										<?}?>
									</div>	
								</fieldset>
								<footer>
									<button type="submit" class="btn btn-primary">
										<i class="fa fa-save"></i>
										Сохранить данные
									</button>
								</footer>
								<input type="hidden" name="m_services_id" value="<?=$service['m_services_id']?>"/>
								<input type="hidden" name="action" value="m_services_change"/>
							</form>
						</div>
					</div>
				</div>	
			</article>
		</div>
	</section>
<?
}
else{
//список связанных работ
$services_select2='';
foreach($services->services_display_li() as $k=>$v)
	if(strlen($k)==10&&isset($v['items'])){
		$services_select2.='<optgroup label="'.$v['m_services_categories_name'].'">';
			foreach($v['items'] as $services_)
				$services_select2.='<option value="'.$services_['m_services_id'].'">'.'['.$v['m_services_categories_name'].'] '.$services_['m_services_name'].'</option>';
		$services_select2.='</optgroup>';			
	}

//список категорий
$services_select='';
$categories=array();
$services->categories_childs(0,$categories,2,0,true);
foreach($categories as $categories_){
	$services_select.='<option value="'.$categories_['m_services_categories_id'].'">'.$categories_['m_services_categories_name'].'</option>';
}
$t=array();
foreach($categories as $k=>$v)
	$t[$v['m_services_categories_id']]=$v;
$categories=$t;

//список своих организаций
$services_contragents='[';
foreach($contragents->getMy() as $contragents_)
	$services_contragents.='{value:'.$contragents_['m_contragents_id'].',text:"'.str_replace('&quot;','',$contragents_['m_contragents_c_name_short']).'"},';
$services_contragents.=']';

//список единиц измерения
$services_units='[';
foreach($services->units_id as $units_)
	$services_units.='{value:'.$units_[0]['m_info_units_id'].',text:"'.$units_[0]['m_info_units_name'].'"},';
$services_units.=']';


$content->setJS('
	
	runAllForms();
		
	$("#services-add").validate({
		rules : {
			m_services_name : {
				maxlength : 280,
				required : true
			},
			m_services_unit : {
				required : true
			},
			m_services_price_general : {
				maxlength : 15,
				required : true
			},
			m_services_price_general_w : {
				maxlength : 15,
				required : true
			},
			m_services_price_wholesale : {
				maxlength : 15,
				required : true
			},
			m_services_price_wholesale_w : {
				maxlength : 15,
				required : true
			},
			m_services_contragents_id : {
				required : true
			},
			"m_services_categories_id[]" : {
				required : true
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});	
	
	$("#services-group-change").validate({
		rules : {
			m_services_name : {
				maxlength : 280
			},
			m_services_price_general : {
				maxlength : 15
			},
			m_services_price_general_w : {
				maxlength : 15
			},
			m_services_price_wholesale : {
				maxlength : 15
			},
			m_services_price_wholesale_w : {
				maxlength : 15
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});	
	
	
	
	$(".m_service_price_general,.m_service_price_general_w,.m_services_order,.datatable .delete").editable({
		url: "/ajax/services_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete"))
				$(this).parents("tr:first").remove();
		}
	});
	
	$(".changepos").on("click",function(){
		var sender=$(this),
			order=$(this).parents("tr:first").find(".m_services_order"),
			value=sender.attr("data-name")=="m_services_order_up"?(order.text()*1-1):(order.text()*1+1);
		$.post(
			"/ajax/services_change.php",
			{
				name:order.attr("data-name"),
				pk:order.attr("data-pk"),
				value:value
			},
			function (data){
				if(sender.attr("data-name")=="m_services_order_up")
					sender.parents("tr:first").prev().before(sender.parents("tr:first"));
				else
					sender.parents("tr:first").next().after(sender.parents("tr:first"));
				order.text(value);
				
			}
		);	
	});
	
	
	$("input.show").on("change",function(){
		$.post(
			"/ajax/services_change.php",
			{
				name:$(this).attr("data-name"),
				pk:$(this).attr("data-pk"),
				value:$(this).prop("checked")
			}
		);
	});
	
	$("input[name=\'m_services_products_name[]\']").sug({
		idValue: "m_services_products_id[]"
	});
	
	$("#products1").df({
		max:20,
		f_a:function(string){
			string.find("input[name=\'m_services_products_name[]\']").sug({
				idValue: "m_services_products_id[]"
			});
		}
	});
	$("#products2").df({
		max:20,
		f_a:function(string){
			string.find("input[name=\'m_services_products_name[]\']").sug({
				idValue: "m_services_products_id[]"
			});
		}
	});
		
	
	$(".label > .checkbox").on("change.name",function(){
		if($(this).parents("section:first").find("select").length){
			if($(this).parents("section:first").find("select").prop("disabled"))
				$(this).parents("section:first").find("select").prop("disabled",false);
			else
				$(this).parents("section:first").find("select").prop("disabled",true);
		}
		else{
			if($(this).parents("section:first").find("input:last").prop("disabled"))
				$(this).parents("section:first").find("input:last").prop("disabled",false).removeClass("disabled");
			else
				$(this).parents("section:first").find("input:last").prop("disabled",true).addClass("disabled");
		}
	});
	
	$(".label > .onoffswitch > input").on("change",function(){
		if($(this).parents("section:first").find("[type=checkbox]:last").prop("disabled"))
			$(this).parents("section:first").find("[type=checkbox]:last").prop("disabled",false).parent().removeClass("disabled");
		else
			$(this).parents("section:first").find("[type=checkbox]:last").prop("disabled",true).parent().addClass("disabled");	
	});
	
	$("td.check input").on("change",function(){
		if($(this).prop("checked"))
			$(this).parents("tr:first").addClass("tr-selected");
		else
			$(this).parents("tr:first").removeClass("tr-selected");
	});
	
	$("#services-group-change").on("submit",function(){
		var ids=[];
		$("input.m_services_id:checked").each(function(index,el){
			ids[ids.length]=$(el).val();
		});
		$("input[name=\'group_m_services_id[]\']").val(ids);
	});
	
	$("#m_services_id_all").on("change",function(){
		if($("#m_services_id_all:checked").length)
			$(".m_services_id").prop("checked",true);
		else
			$(".m_services_id").prop("checked",false);
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
				Информация успешно добавлена!
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
	
<?
if($services->services_id){
?>
		<article class="col-lg-12">
	
			<div class="jarviswidget jarviswidget-color-blue" id="wid-id-11" 
				data-widget-editbutton="false"
				data-widget-colorbutton="false"
				data-widget-deletebutton="false"
				data-widget-togglebutton="false">

				<header>
					<span class="widget-icon"> <i class="fa fa-table"></i> </span>
					<h2>Список услуг</h2>
				</header>

				<div>				

					<div class="widget-body no-padding">

						<table class="datatable table table-striped table-bordered table-hover" width="100%">
	
							<thead>
								<tr>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th class="hasinput">
										<select class="autoselect th-filter" placeholder="выберите из списка...">
											<option value="0">Все категории</option>
											<? 
												$categories1=array();
												$services->categories_childs(0,$categories1,2);
												foreach($categories1 as $categories_){
													echo '<option value="'.$categories_['m_services_categories_id'].'">
															'.$categories_['m_services_categories_name'].'
														</option>';													
												}
											?>
										</select>
									</th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
								</tr>
								<tr>
									<th style="width:2%" class="no-order">
										<label>
											<div class="checkbox">
											  <label>
												<input type="checkbox" class="checkbox tr" id="m_services_id_all"/>
												<span></span>
											  </label>
											</div>
										</label>
									</th>
									<th style="width:2%">№</th>
									<th style="width:20%">Наименование</th>
									<th style="width:4%">Ед.&nbsp;изм.</th>
									<th style="width:9%">Цена розница</th>
									<th style="width:17%">Категории</th>
									<th style="width:19%">Связанные работы</th>
									<th style="width:8%">Фирма</th>
									<th style="width:7%">Показывать</th>
									<th style="width:8%">Управление</th>
								</tr>
							</thead>

							<tbody>
								<?
$i=0;
foreach($services->services_id as $services_){//$i++;if($i==20) break;
	$services_=$services_[0];
	$m_services_categories_id=array();
	$services_['m_services_categories_id']=explode('|',$services_['m_services_categories_id']);
	if($services_['m_services_categories_id'][0])
		foreach($services_['m_services_categories_id'] as $t_)
			$m_services_categories_id[]=$categories[$t_]['m_services_categories_name'];
	
	$m_services_links=array();
	$services_['m_services_links']=explode('|',$services_['m_services_links']);
	if($services_['m_services_links'][0])
		foreach($services_['m_services_links'] as $t_)
			$m_services_links[]=$services->services_id[$t_][0]['m_services_name'];
	
	echo '<tr>
		<td class="check">
			<label>
				<div class="checkbox">
				  <label>
					<input type="checkbox" class="checkbox tr m_services_id" value="'.$services_['m_services_id'].'">
					<span></span>
				  </label>
				</div>
			</label>
		</td>
		<td>
			<a href="#" class="m_services_order" data-type="text" data-pk="'.$services_['m_services_id'].'" data-name="m_services_order" data-title="Порядковый номер">',
				$services_['m_services_order'],
			'</a>
		</td>
		<td>',
			$services_['m_services_name'],
		'</td>
		<td>',
			$services->units_id[$services_['m_services_unit']][0]['m_info_units_name'],
		'</td>
		<td>
			<table class="minitable price r"><tr><td>Клиент</td><td><a href="#" class="m_service_price_general" data-type="text" data-pk="'.$services_['m_services_id'].'" data-name="m_services_price_general" data-title="Основная для клиента">',
				$services_['m_services_price_general'],
			'</a></td><tr>
			<tr><td>Работник</td><td><a href="#" class="m_service_price_general_w" data-type="text" data-pk="'.$services_['m_services_id'].'" data-name="m_services_price_general_w" data-title="Основная для работника">',
				$services_['m_services_price_general_w'],
			'</a></td><tr></table>
		</td>
		<td>',
			implode('<br/>',$m_services_categories_id),
		'</td>
		<td>',
			implode('<br/>',$m_services_links),
		'</td>
		<td>',
			$contragents->getInfo($services_['m_services_contragents_id'])['m_contragents_c_name_short'],
		'</td>
		<td>
			<label class="checkbox">
			  <input type="checkbox" class="checkbox style-0 show" data-name="m_services_show_price" '.($services_['m_services_show_price']?'checked':'').' data-pk="'.$services_['m_services_id'].'">
			  <span>В прайсе</span>
			</label>
			<label class="checkbox">
			  <input type="checkbox" class="checkbox style-0 show" data-name="m_services_show_site" '.($services_['m_services_show_site']?'checked':'').' data-pk="'.$services_['m_services_id'].'">
			  <span>На сайте</span>
			</label>
		</td>
		<td>
			<a href="javascript:void(0);" title="Изменить позицию (выше)" class="btn btn-xs btn-default changepos" data-value="'.$services_['m_services_order'].'" data-type="text" data-pk="'.$services_['m_services_id'].'" data-name="m_services_order_up" data-placement="left">
				<i class="fa fa-angle-up"></i>
			</a>
			<a href="javascript:void(0);" title="Изменить позицию (ниже)" class="btn btn-xs btn-default changepos" data-value="'.$services_['m_services_order'].'" data-type="text" data-pk="'.$services_['m_services_id'].'" data-name="m_services_order_down" data-placement="left">
				<i class="fa fa-angle-down"></i>
			</a>&nbsp;&nbsp;
			<a href="'.url().'?action=change&m_services_id='.$services_['m_services_id'].'" title="Редактировать" class="btn btn-primary btn-xs btn-default change" data-type="text">
				<i class="fa fa-pencil"></i>
			</a>
			<a href="javascript:void(0);" title="Удалить" class="btn btn-xs btn-danger delete" data-type="text" data-pk="'.$services_['m_services_id'].'" data-name="m_services_id" data-title="Введите пароль для удаления записи" data-placement="left">
				<i class="fa fa-trash-o"></i>
			</a>
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

		<article class="col-lg-6 sortable-grid ui-sortable">
			<div class="jarviswidget jarviswidget-color-blue" id="wid-id-12" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">		
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Масовые изменения</h2>
				</header>
				<div>
					<div class="widget-body">
						<form id="services-group-change" class="smart-form" method="post">
							<header>
								Основные данные
							</header>
							<fieldset>
								<div class="row">
									<section class="col col-3">
										<label class="label">
											<div class="checkbox">
											  <label>
												<input type="checkbox" name="on_m_services_unit" class="checkbox style-0">
												<span>
												  Единица измерения
												</span>
											  </label>
											</div>
										</label>
										<select name="m_services_unit" class="autoselect" disabled >
											<?
												foreach($info->getUnits() as $t_)
													echo '<option value="'.$t_[0]['m_info_units_id'].'">',
														$t_[0]['m_info_units_name'],
														'</option>';
											?>
										</select>
									</section>
									<section class="col col-9">
										<label class="label">
											<div class="checkbox">
											  <label>
												<input type="checkbox" name="on_m_services_categories_id" class="checkbox style-0">
												<span>
													Нахождение в категориях
												</span>
											  </label>
											</div>
										</label>
										<select name="m_services_categories_id[]" style="width:100%" multiple class="autoselect" disabled placeholder="выберите из списка...">
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
								</div>
								<div class="row">
									<section class="col col-6">
										<label class="label">
											<div class="checkbox">
											  <label>
												<input type="checkbox" name="on_m_services_contragents_id" class="checkbox style-0">
												<span>
												  Организация
												</span>
											  </label>
											</div>
										</label>
										<select name="m_services_contragents_id" class="autoselect" disabled placeholder="выберите из списка...">
											<?
												foreach($contragents->getInfo() as $contragents_){
													$ct=explode('|',$contragents_[0]['m_contragents_type']);
													if(in_array(1,$ct))
														echo '<option value="'.$contragents_[0]['m_contragents_id'].'">',
															$contragents_[0]['m_contragents_c_name_short']?$contragents_[0]['m_contragents_c_name_short']:$contragents_[0]['m_contragents_c_name_full'],
														'</option>';
												}
											?>
										</select>
									</section>
								</div>
							</fieldset>
							<header>
								Цены
							</header>
							<fieldset>
								<div class="row">
									<section class="col col-3">
										Изменение цен (пример):
									</section>
									<section class="col col-1">
										+|-100
									</section>
									<section class="col col-1">
										*|/1.5
									</section>
									<section class="col col-1">
										+|-10%
									</section>
								</div>
								
								<div class="row">
									<section class="col col-3">
										<label class="label">
											<div class="checkbox">
											  <label>
												<input type="checkbox" name="on_m_services_price_general" class="checkbox style-0">
												<span>
													Для клиента
												</span>
											  </label>
											</div>
										</label>
										<label class="input">
											<i class="icon-append fa fa-rub"></i>
											<input type="text" name="m_services_price_general" class="disabled" style="text-align:right;" disabled >
										</label>
									</section>
									<section class="col col-3">
										<label class="label">
											<div class="checkbox">
											  <label>
												<input type="checkbox" name="on_m_services_price_general_w" class="checkbox style-0">
												<span>
													Для работника
												</span>
											  </label>
											</div>
										</label>
										<label class="input">
											<i class="icon-append fa fa-rub"></i>
											<input type="text" name="m_services_price_general_w" class="disabled" style="text-align:right;" disabled >
										</label>
									</section>
									<section class="col col-5">
										<br/>
										<label class="checkbox">
											<input type="checkbox" name="m_services_round5_ceil" checked="checked" value="1"/>
											<i></i>
											Округлить кратно 5 в большую сторону
										</label>
										<label class="checkbox">
											<input type="checkbox" name="m_services_round5_floor" value="1"/>
											<i></i>
											Округлить кратно 5 в меньшую сторону
										</label>
									</section>
								</div>
							</fieldset>
							<header>
								Дополнительные опции
							</header>
							<fieldset>
								<div class="row">
									<section class="col col-4">
										<label class="label" style="float:left;">
											<span class="onoffswitch">
												<input type="checkbox" name="on_m_services_show_site" class="onoffswitch-checkbox" id="myonoffswitch1" value="1">
												<label class="onoffswitch-label" for="myonoffswitch1"> 
													<span class="onoffswitch-inner" data-swchon-text="ВКЛ" data-swchoff-text="ВЫКЛ"></span> 
													<span class="onoffswitch-switch"></span> </label> 
											</span>
										</label>
										<label class="checkbox disabled" style="float:right;">
											<input type="checkbox" name="m_services_show_site" checked="checked" value="1" disabled class="disabled"/>
											<i></i>
											Показывать на сайте
										</label>
									</section>
									<section class="col col-1">
									</section>
									<section class="col col-4">
										<label class="label" style="float:left;">
											<span class="onoffswitch">
												<input type="checkbox" name="on_m_services_show_price" class="onoffswitch-checkbox" id="myonoffswitch2" value="1">
												<label class="onoffswitch-label" for="myonoffswitch2"> 
													<span class="onoffswitch-inner" data-swchon-text="ВКЛ" data-swchoff-text="ВЫКЛ"></span> 
													<span class="onoffswitch-switch"></span> </label> 
											</span>
										</label>
										<label class="checkbox disabled" style="float:right;">
											<input type="checkbox" name="m_services_show_price" checked="checked" value="1" disabled />
											<i></i>
											Выгружать в прайс
										</label>
									</section>
								</div>
								<section>
									<label class="label">
										<div class="checkbox">
										  <label>
											<input type="checkbox" name="on_m_services_links" class="checkbox style-0">
											<span>
											  Связанные работы
											</span>
										  </label>
										</div>
									</label>
									<select name="m_services_links[]" id="m_services_links" style="width:100%" multiple class="autoselect disabled" disabled placeholder="выберите из списка...">
										<?
											foreach($services->services_display_li() as $k=>$v)
												if(strlen($k)==10&&isset($v['items'])){
													echo '<optgroup label="'.$v['m_services_categories_name'].'">';
														foreach($v['items'] as $services_)
															echo '<option value="'.$services_['m_services_id'].'">',
																'['.$v['m_services_categories_name'].'] '.$services_['m_services_name'],
																'</option>';
													echo '</optgroup>';			
												}
										?>
									</select>
								</section>
							</fieldset>
							<header>
								<section>
								<label class="label" style="float:left;margin-top:-px;">
									<div class="checkbox">
									  <label>
										<input type="checkbox" name="on_m_services_products_id" class="checkbox style-0">
										<span>
										 
										</span>
									  </label>
									</div>
								</label>Используемые материалы
								<input type="hidden"/>
								</section>
							</header>
							<fieldset>
								<div  id="products1">
									<div class="multirow">
										<div class="row">
											<section class="col col-2">
												<label class="input">
													<input type="text" name="m_services_products_id[]"  placeholder="арт." readonly>
												</label>
											</section>
											<section class="col col-5">
												<label class="input">
													<i class="icon-append fa fa-cube"></i>
													<input type="text" name="m_services_products_name[]"  placeholder="товар">
												</label>
											</section>
											<section class="col col-2">
												<label class="input">
													<i class="icon-append fa fa-cubes"></i>
													<input type="text" name="m_services_products_count[]"  placeholder="кол-во">
												</label>
											</section>
											<section class="col col-3" style="text-align:right">
												<div class="btn-group btn-labeled multirow-btn">
													<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
													<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
														<span class="caret"></span>
													</a>
													<ul class="dropdown-menu">
														<li>
															<a href="javascript:void(0);" class="add">Добавить товар</a>
														</li>
														<li>
															<a href="javascript:void(0);" class="delete">Удалить товар</a>
														</li>
													</ul>
												</div>
											</section>
										</div>
									</div>
								</div>
							</fieldset>			
							<footer>
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-save"></i>
									Обновить
								</button>
							</footer>
							<input type="hidden" name="action" value="m_services_group_change"/>
							<input type="hidden" name="group_m_services_id[]"/>
						</form>
					</div>
				</div>
			</div>	
		</article>
		<article class="col-lg-6 sortable-grid ui-sortable">
			<div class="jarviswidget" id="wid-id-13" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">		
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Добавить услугу</h2>
				</header>
				<div>
					<div class="widget-body">
						<form id="services-add" class="smart-form" method="post">
							<header>
								Основные данные
							</header>
							<fieldset>
								<div class="row">
									<section class="col col-9">
										<label class="label">Наименование</label>
										<label class="input">
											<input type="text" name="m_services_name" placeholder="например, Монтаж полотенцесушителя">
										</label>
									</section>
									<section class="col col-3">
										<label class="label">Единица измерения</label>
										<select name="m_services_unit" class="autoselect">
											<?
												foreach($info->getUnits() as $t_)
													echo '<option value="'.$t_[0]['m_info_units_id'].'">',
														$t_[0]['m_info_units_name'],
														'</option>';
											?>
										</select>
									</section>
								</div>
								<div class="row">
									<section class="col col-9">
										<label class="label">Нахождение в категориях</label>
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
									<section class="col col-3">
										<label class="label">Организация</label>
										<select name="m_services_contragents_id" class="autoselect" placeholder="выберите из списка...">
											<?
												foreach($contragents->getInfo() as $contragents_){
													$ct=explode('|',$contragents_[0]['m_contragents_type']);
													if(in_array(1,$ct))
														echo '<option value="'.$contragents_[0]['m_contragents_id'].'" '.($contragents_[0]['m_contragents_id']=='1323142588'?'selected ':'').'>',
															$contragents_[0]['m_contragents_c_name_short']?$contragents_[0]['m_contragents_c_name_short']:$contragents_[0]['m_contragents_c_name_full'],
														'</option>';
												}
											?>
										</select>
									</section>
								</div>
							</fieldset>
							<header>
								Цены
							</header>
							<fieldset>
								<div class="row">
									<section class="col col-3">
										<label class="label">Для клиента</label>
										<label class="input">
											<i class="icon-append fa fa-rub"></i>
											<input type="text" name="m_services_price_general" style="text-align:right;">
										</label>
									</section>
									<section class="col col-3">
										<label class="label">Для работника</label>
										<label class="input">
											<i class="icon-append fa fa-rub"></i>
											<input type="text" name="m_services_price_general_w" style="text-align:right;">
										</label>
									</section>
								</div>
							</fieldset>
							<header>
								Дополнительные опции
							</header>
							<fieldset>
								<div class="row">
									<section class="col col-4">
									  <label class="checkbox">
										<input type="checkbox" name="m_services_show_site" checked="checked" value="1"/>
										<i></i>
										Показывать на сайте
									  </label>
									  <label class="checkbox">
										<input type="checkbox" name="m_services_show_price" checked="checked" value="1"/>
										<i></i>
										Выгружать в прайс
									  </label>
									</section>
									<section class="col col-8">
										<label class="label">Связанные работы</label>
										<select name="m_services_links[]" id="m_services_links" style="width:100%" multiple class="autoselect" placeholder="выберите из списка...">
											<?
												foreach($services->services_display_li() as $k=>$v)
													if(strlen($k)==10&&isset($v['items'])){
														echo '<optgroup label="'.$v['m_services_categories_name'].'">';
															foreach($v['items'] as $services_)
																echo '<option value="'.$services_['m_services_id'].'">',
																	'['.$v['m_services_categories_name'].'] '.$services_['m_services_name'],
																	'</option>';
														echo '</optgroup>';			
													}
											?>
										</select>
									</section>
								</div>
							</fieldset>
							<header>
								Используемые материалы
							</header>
							<fieldset>
								<div id="products2">
									<div class="multirow">
										<div class="row">
											<section class="col col-2">
												<label class="input">
													<input type="text" name="m_services_products_id[]"  placeholder="арт." readonly>
												</label>
											</section>
											<section class="col col-5">
												<label class="input">
													<i class="icon-append fa fa-cube"></i>
													<input type="text" name="m_services_products_name[]"  placeholder="товар">
												</label>
											</section>
											<section class="col col-2">
												<label class="input">
													<i class="icon-append fa fa-cubes"></i>
													<input type="text" name="m_services_products_count[]"  placeholder="кол-во">
												</label>
											</section>
											<section class="col col-3" style="text-align:right">
												<div class="btn-group btn-labeled multirow-btn">
													<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
													<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
														<span class="caret"></span>
													</a>
													<ul class="dropdown-menu">
														<li>
															<a href="javascript:void(0);" class="add">Добавить товар</a>
														</li>
														<li>
															<a href="javascript:void(0);" class="delete">Удалить товар</a>
														</li>
													</ul>
												</div>
											</section>
										</div>
									</div>
								</div>
							</fieldset>			
							<footer>
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-save"></i>
									Сохранить данные
								</button>
							</footer>
							<input type="hidden" name="action" value="m_services_add"/>
						</form>
					</div>
				</div>
			</div>	
		</article>
	</div>
</section>
<?}?>
<script src="/js/jquery.suggest_products.js"></script>
<script src="/js/jquery.df.js"></script>