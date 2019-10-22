<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$logistic,$info;

if($id=get('m_products_id')){
	$product=$products->products_id[$id][0];
	$product['m_products_categories_id']=explode('|',$product['m_products_categories_id']);
	$product['m_products_links']=explode('|',$product['m_products_links']);
	
	$content->setJS('
	
		runAllForms();
			
		$("#products-add").validate({
			rules : {
				m_products_name : {
					maxlength : 180,
					required : true
				},
				m_products_unit : {
					required : true
				},
				m_products_price_general : {
					maxlength : 15,
					required : true
				},
				m_products_price_general_w : {
					maxlength : 15,
					required : true
				},
				m_products_price_wholesale : {
					maxlength : 15,
					required : true
				},
				m_products_price_wholesale_w : {
					maxlength : 15,
					required : true
				},
				m_products_contragents_id : {
					required : true
				},
				"m_products_categories_id[]" : {
					required : true
				}
			},
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
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
						<h2>Редактировать товарную позицию</h2>
					</header>
					<div>
						<div class="widget-body">
							<form id="products-add" class="smart-form" method="post">
								<header>
									Основные данные
								</header>
								<fieldset>
									<section>
										<label class="label">Наименование</label>
										<label class="input">
											<input type="text" name="m_products_name" value="<?=$product['m_products_name']?>">
										</label>
									</section>
									<div class="row">
										<section class="col col-3">
											<label class="label">Единица измерения</label>
											<select name="m_products_unit" class="autoselect">
												<?
													$q='SELECT * FROM `formetoo_cdb`.`m_info_units`;';
													$t=$sql->query($q);
													foreach($t as $t_)
														echo '<option value="'.$t_['m_info_units_id'].'" '.($t_['m_info_units_id']==$product['m_products_unit']?' selected':'').'>',
															$t_['m_info_units_name'],
															'</option>';
												?>
											</select>
										</section>
										<section class="col col-3">
											<label class="label">Вес, кг</label>
											<label class="input">
												<i class="icon-append">кг</i>
												<input type="text" name="m_products_unit_weight" style="text-align:right;" value="<?=$product['m_products_unit_weight']?>">
											</label>
										</section>
										<section class="col col-3">
											<label class="label">Объём, м<sup>3</sup></label>
											<label class="input">
												<i class="icon-append">м<sup>3</sup></i>
												<input type="text" name="m_products_unit_volume" style="text-align:right;" value="<?=$product['m_products_unit_volume']?>">
											</label>
										</section>
									</div>
									<div class="row">
										<section class="col col-9">
											<label class="label">Нахождение в категориях</label>
											<select name="m_products_categories_id[]" style="width:100%" multiple class="autoselect" placeholder="выберите из списка...">
												<? 
													$categories=array();
													$products->categories_childs(0,$categories,2);
													foreach($categories as $categories_){
														echo '<option value="'.$categories_['m_products_categories_id'].'" '.(in_array($categories_['m_products_categories_id'],$product['m_products_categories_id'])?' selected':'').'>
																'.$categories_['m_products_categories_name'].'
															</option>';																
													}
												?>
											</select>
										</section>
										<section class="col col-3">
											<label class="label">Организация</label>
											<select name="m_products_contragents_id" class="autoselect">
												<?
													foreach($contragents->getInfo() as $contragents_){
														$ct=explode('|',$contragents_[0]['m_contragents_type']);
														if(in_array(1,$ct))
															echo '<option value="'.$contragents_[0]['m_contragents_id'].'" '.($contragents_[0]['m_contragents_id']==$product['m_contragents_id']?' selected':'').'>',
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
											<label class="label">Розница</label>
											<label class="input">
												<i class="icon-append fa fa-rub"></i>
												<input type="text" name="m_products_price_general" style="text-align:right;" value="<?=$product['m_products_price_general']?>">
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
											<input type="checkbox" name="m_products_show_site" <?=($product['m_products_show_site']?' checked':'')?> value="1"/>
											<i></i>
											Показывать на сайте
										  </label>
										  <label class="checkbox">
											<input type="checkbox" name="m_products_show_price" checked="checked" <?=($product['m_products_show_price']?' checked':'')?> value="1"/>
											<i></i>
											Выгружать в прайс
										  </label>
										</section>
										<section class="col col-8">
											<label class="label">Связанные товары</label>
											<select name="m_products_links[]" id="m_products_links" style="width:100%" multiple class="autoselect" placeholder="выберите из списка...">
												<?
													foreach($products->products_display_li() as $k=>$v)
														if(strlen($k)==10&&isset($v['items'])){
															echo '<optgroup label="'.$v['m_products_categories_name'].'">';
																foreach($v['items'] as $products_)
																	if($products_['m_products_id']!=$product['m_products_id'])
																		echo '<option value="'.$products_['m_products_id'].'" '.(in_array($products_['m_products_id'],$product['m_products_links'])!==false?' selected ':'').'>',
																				'['.$v['m_products_categories_name'].'] '.$products_['m_products_name'],
																			'</option>';
															echo '</optgroup>';			
														}
												?>
											</select>
										</section>
									</div>
								</fieldset>			
								<footer>
									<button type="submit" class="btn btn-primary">
										<i class="fa fa-save"></i>
										Сохранить данные
									</button>
								</footer>
								<input type="hidden" name="m_products_id" value="<?=$product['m_products_id']?>"/>
								<input type="hidden" name="action" value="products_change"/>
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







$content->setJS('
	
	runAllForms();
		
	$("#products-add").validate({
		rules : {
			m_products_name : {
				maxlength : 180,
				required : true
			},
			m_products_unit : {
				required : true
			},
			m_products_price_general : {
				maxlength : 15,
				required : true
			},
			m_products_contragents_id : {
				required : true
			},
			"m_products_categories_id[]" : {
				required : true
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
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
	
	$(".changepos").on("click",function(){
		var sender=$(this),
			order=$(this).parents("tr:first").find(".m_products_order"),
			value=sender.attr("data-name")=="m_products_order_up"?(order.text()*1-1):(order.text()*1+1);
		$.post(
			"/ajax/services_change.php",
			{
				name:order.attr("data-name"),
				pk:order.attr("data-pk"),
				value:value
			},
			function (data){
				if(sender.attr("data-name")=="m_products_order_up")
					sender.parents("tr:first").prev().before(sender.parents("tr:first"));
				else
					sender.parents("tr:first").next().after(sender.parents("tr:first"));
				order.text(value);
				
			}
		);	
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
	
	$(".m_product_name,.m_product_price_general,.m_products_order,.delete").editable({
		url: "/ajax/services_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete"))
				$(this).parents("tr:first").remove();
		}
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
	
	$("#products-group-change").on("submit",function(){
		var ids=[];
		$("input.m_products_id:checked").each(function(index,el){
			ids[ids.length]=$(el).val();
		});
		$("input[name=\'group_m_products_id[]\']").val(ids);
	});
	
	$("#m_products_id_all").on("change",function(){
		if($("#m_products_id_all:checked").length)
			$(".m_products_id").prop("checked",true);
		else
			$(".m_products_id").prop("checked",false);
	});
	
	$("[name=m_delivery_price_contragents_id]").on("click",function(){
		window.location="http://'.$_SERVER['SERVER_NAME'].'/companies/logistic/?m_contragents_id="+$(this).val();
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
	
	<article class="col-lg-6 sortable-grid ui-sortable">
			<div class="jarviswidget" id="wid-id-30" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">		
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Добавить расчет логистики</h2>
				</header>
				<div>
					<div class="widget-body">
						<form id="products-add" class="smart-form" method="post">
							<fieldset>
								<div class="row">
									<section class="col col-4">
										<label class="label">Организация</label>
										<select name="m_delivery_price_contragents_id" class="autoselect" placeholder="выберите из списка...">
											<?
												foreach($contragents->getInfo() as $contragents_){
													echo '<option value="'.$contragents_[0]['m_contragents_id'].'"'.($contragents_[0]['m_contragents_id']==get('m_contragents_id')?'selected':'').'>',
														$contragents_[0]['m_contragents_c_name_short']?$contragents_[0]['m_contragents_c_name_short']:$contragents_[0]['m_contragents_c_name_full'],
													'</option>';
												}
											?>
										</select>
									</section>
									<section class="col col-4">
										<label class="label">Адрес отгрузки товаров</label>
										<select name="m_delivery_price_contragents_address_id" class="autoselect" placeholder="выберите из списка...">
											<?
												if(!get('m_contragents_id'))
													echo '<option value="0">выберите контрагента...</option>';
												else{
													foreach($info->getAddress((string)get('m_contragents_id')) as $_a)
														if($_a['m_address_type']==4)
															echo '<option value="'.$_a['m_address_id'].'">',
																$_a['m_address_full'],
															'</option>';
												}
											?>
										</select>
									</section>
																		<section class="col col-4">
										<label class="label">Тип автомобиля</label>
										<select name="m_delivery_price_transport_id" class="autoselect" placeholder="выберите из списка...">
											<?
												foreach($logistic->transport as $_t){
													echo '<option value="'.$_t[0]['m_delivery_transport_id'].'">',
															$_t[0]['m_delivery_transport_name'],
															' ('.$_t[0]['m_delivery_transport_volume'].' м3, '.number_format($_t[0]['m_delivery_transport_weight'],0,'.',' ').' кг)',
														'</option>';
												}
											?>
										</select>
									</section>
								</div>
								<div class="row">
									<section class="col col-3">
										<label class="label">Цена за км</label>
										<label class="input">
											<i class="icon-append fa fa-rub"></i>
											<input type="text" name="m_delivery_price_sum" style="text-align:right;">
										</label>
									</section>
									<section class="col col-3">
										<label class="label">Цена за км при 75% загр.</label>
										<label class="input">
											<i class="icon-append fa fa-rub"></i>
											<input type="text" name="m_delivery_price_sum_fullload" style="text-align:right;">
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
							<input type="hidden" name="action" value="logistic_add"/>
						</form>
					</div>
				</div>
			</div>	
		</article>
	
<?
if(1==2&&$products->products_id){
?>
		<article class="col-lg-12">
	
			<div class="jarviswidget jarviswidget-color-blue" id="wid-id-1" 
				data-widget-editbutton="false"
				data-widget-colorbutton="false"
				data-widget-deletebutton="false"
				data-widget-togglebutton="false">
				
				<header>
					<span class="widget-icon"> <i class="fa fa-table"></i> </span>
					<h2>Список товаров</h2>
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
									<th></th>
									<th></th>
									<th class="hasinput">
										<select class="autoselect th-filter" placeholder="выберите из списка...">
											<option value="0">Все категории</option>
											<?											
												$categories1=array();
												$products->categories_childs(0,$categories1,2);
												foreach($categories1 as $categories_){
													echo '<option value="'.$categories_['m_products_categories_id'].'">
															'.$categories_['m_products_categories_name'].'
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
												<input type="checkbox" class="checkbox tr" id="m_products_id_all"/>
												<span></span>
											  </label>
											</div>
										</label>
									</th>					
									<th style="width:2%">№</th>
									<th style="width:15%">Наименование</th>
									<th style="width:4%">Ед.&nbsp;изм.</th>
									<th style="width:6%">Вес ед.</th>
									<th style="width:6%">Объём ед.</th>
									<th style="width:9%">Цена розница</th>
									<th style="width:15%">Категории</th>
									<th style="width:15%">Связанные работы</th>
									<th style="width:8%">Фирма</th>
									<th style="width:7%">Показывать</th>
									<th style="width:8%">Управление</th>
								</tr>
							</thead>

							<tbody>
								<?
$i=0;
foreach($products->products_id as $products_){
	$products_=$products_[0];
	$m_products_categories_id=array();
	$products_['m_products_categories_id']=explode('|',$products_['m_products_categories_id']);
	if($products_['m_products_categories_id'][0])
		foreach($products_['m_products_categories_id'] as $t_)
			$m_products_categories_id[]=$categories[$t_]['m_products_categories_name'];
	
	$m_products_links=array();
	$products_['m_products_links']=explode('|',$products_['m_products_links']);
	if($products_['m_products_links'][0])
		foreach($products_['m_products_links'] as $t_)
			$m_products_links[]=$products->products_id[$t_][0]['m_products_name'];
	
	echo '<tr>
		<td class="check">
			<label>
				<div class="checkbox">
				  <label>
					<input type="checkbox" class="checkbox tr m_products_id" value="'.$products_['m_products_id'].'">
					<span></span>
				  </label>
				</div>
			</label>
		</td>
		<td>
			<a href="#" class="m_products_order" data-type="text" data-pk="'.$products_['m_products_id'].'" data-name="m_products_order" data-title="Порядковый номер">',
				$products_['m_products_order'],
			'</a>
		</td>
		<td>',
			$products_['m_products_name'],
		'</td>
		<td>',
			$products->units_id[$products_['m_products_unit']][0]['m_info_units_name'],
		'</td>
		<td>',
			$products_['m_products_unit_weight'],
		'</td>
		<td>',
			$products_['m_products_unit_volume'],
		'</td>
		<td>
			<table class="minitable price"><tr><td>Клиент</td><td><a href="#" class="m_product_price_general" data-type="text" data-pk="'.$products_['m_products_id'].'" data-name="m_products_price_general" data-title="Основная для клиента">',
				$products_['m_products_price_general'],
			'</a></td><tr></table>
		</td>
		<td>',
			implode('<br/>',$m_products_categories_id),
		'</td>
		<td>',
			implode('<br/>',$m_products_links),
		'</td>
		<td>',
			$contragents->getInfo($products_['m_products_contragents_id'])['m_contragents_c_name_short'],
		'</td>
		<td>
			<label class="checkbox">
			  <input type="checkbox" class="checkbox style-0 show" data-name="m_products_show_price" '.($products_['m_products_show_price']?'checked':'').' data-pk="'.$products_['m_products_id'].'">
			  <span>В прайсе</span>
			</label>
			<label class="checkbox">
			  <input type="checkbox" class="checkbox style-0 show" data-name="m_products_show_site" '.($products_['m_products_show_site']?'checked':'').' data-pk="'.$products_['m_products_id'].'">
			  <span>На сайте</span>
			</label>
		</td>
		<td>
			<a href="javascript:void(0);" title="Изменить позицию (выше)" class="btn btn-xs btn-default changepos" data-value="'.$products_['m_products_order'].'" data-type="text" data-pk="'.$products_['m_products_id'].'" data-name="m_products_order_up" data-placement="left">
				<i class="fa fa-angle-up"></i>
			</a>
			<a href="javascript:void(0);" title="Изменить позицию (ниже)" class="btn btn-xs btn-default changepos" data-value="'.$products_['m_products_order'].'" data-type="text" data-pk="'.$products_['m_products_id'].'" data-name="m_products_order_down" data-placement="left">
				<i class="fa fa-angle-down"></i>
			</a>&nbsp;&nbsp;
			<a href="'.url().'?action=change&m_products_id='.$products_['m_products_id'].'" title="Редактировать" class="btn btn-primary btn-xs btn-default change" data-type="text">
				<i class="fa fa-pencil"></i>
			</a>
			<a href="javascript:void(0);" title="Удалить" class="btn btn-xs btn-danger delete" data-type="text" data-pk="'.$products_['m_products_id'].'" data-name="m_products_id" data-title="Введите пароль для удаления записи" data-placement="left">
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
	</div>
</section>
<?}?>