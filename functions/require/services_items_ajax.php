<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$services;

//список связанных работ
$services_select2='';
foreach($services->services_display_li() as $k=>$v)
	if(strlen($k)==10&&isset($v['items'])){
		$services_select2.='<optgroup label="'.$v['m_services_categories_name'].'">';
			foreach($v['items'] as $services_)
				$services_select2.='<option value="'.$services_['m_services_id'].'">'.'['.$v['m_services_categories_name'].'] '.$services_['m_services_name'].'</option>';
		$services_select2.='</optgroup>';			
	}

$services_select='';
$categories=array();
$services->categories_childs(0,$categories,2);
foreach($categories as $categories_){
	$services_select.='<option value="'.$categories_['m_services_categories_id'].'">'.$categories_['m_services_categories_name'].'</option>';
}

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
				maxlength : 180,
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
			m_services_companies_id : {
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
	
	$(".m_service_contragents_id").editable({
		source: '.$services_contragents.',
		select2: {
			width: 200
		},
		url: "/ajax/services_change.php"
	});
	
	$(".m_service_name,.m_service_price_general,.m_service_price_general_w,.m_service_price_wholesale,.m_service_price_wholesale_w,.delete").editable({
		url: "/ajax/services_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete"))
				$(this).parents("tr:first").remove();
		}
	});
	
	$(".m_service_unit").editable({
		source: '.$services_units.',
		select2: {
			width: 150
		},
		url: "/ajax/services_change.php"
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
	
	$(".m_services_links").each(function(index,el){
		if($(el).attr("data-value")){
			var selected=$(el).attr("data-value").split("|");
			for(var i=0;i<selected.length;i++){
				var sel=selected[i]*1;
				if(sel)
					$(el).find("option[value="+sel+"]").prop("selected", "selected");
			}
		}
	});
	$(".m_services_links").on("change",function(){
		var selected=new Array();
		$(this).find("option:selected").each(function(){
			selected.push($(this).val());
		});
		$.post(
			"/ajax/services_change.php",
			{
				name:$(this).attr("data-name"),
				pk:$(this).attr("data-pk"),
				value:selected.join("|")
			}
		);
	});
	
	$(".m_services_categories_id").each(function(index,el){
		if($(el).attr("data-value")){
			var selected=$(el).attr("data-value").split("|");
			for(var i=0;i<selected.length;i++){
				var sel=selected[i]*1;
				if(sel)
					$(el).find("option[value="+sel+"]").prop("selected", "selected");
			}
		}
	});
	$(".m_services_categories_id").on("change",function(){
		var selected=new Array();
		$(this).find("option:selected").each(function(){
			selected.push($(this).val());
		});
		$.post(
			"/ajax/services_change.php",
			{
				name:$(this).attr("data-name"),
				pk:$(this).attr("data-pk"),
				value:selected.join("|")
			}
		);
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
	
			<div class="jarviswidget jarviswidget-color-blue" id="wid-id-1" 
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
									<th style="width:8%">Фирма</th>
									<th style="width:20%">Наименование</th>
									<th style="width:4%">Ед.&nbsp;изм.</th>
									<th style="width:9%">Цена розница</th>
									<th style="width:9%">Цена опт</th>
									<th style="width:17%">Категории</th>
									<th style="width:25%">Связанные работы</th>
									<th style="width:7%">Показывать</th>
									<th style="width:1%"></th>
								</tr>
							</thead>

							<tbody>
								<?
$i=0;
foreach($services->services_id as $services_){
	if($i++==10) break;
	$services_=$services_[0];
	echo '<tr>
		<td>
			<a href="#" class="m_service_contragents_id" data-type="select2" data-pk="'.$services_['m_services_id'].'" data-name="m_services_contragents_id" data-select-search="true" data-value="'.$services_['m_services_contragents_id'].'" data-original-title="Организация"></a>
		</td>
		<td>
			<a href="#" class="m_service_name" data-type="text" data-pk="'.$services_['m_services_id'].'" data-name="m_services_name" data-title="Наименование услуги">',
				$services_['m_services_name'],
			'</a>
		</td>
		<td>
			<a href="#" class="m_service_unit" data-type="select2" data-pk="'.$services_['m_services_id'].'" data-name="m_services_unit" data-select-search="true" data-value="'.$services_['m_services_unit'].'" data-original-title="Единица измерения"></a>
		</td>
		<td>
			<table class="minitable price"><tr><td>Клиент</td><td><a href="#" class="m_service_price_general" data-type="text" data-pk="'.$services_['m_services_id'].'" data-name="m_services_price_general" data-title="Основная для клиента">',
				$services_['m_services_price_general'],
			'</a></td><tr>
			<tr><td>Работник</td><td><a href="#" class="m_service_price_general_w" data-type="text" data-pk="'.$services_['m_services_id'].'" data-name="m_services_price_general_w" data-title="Основная для работника">',
				$services_['m_services_price_general_w'],
			'</a></td><tr></table>
		</td>
		<td>
			<table class="minitable price"><tr><td>Клиент</td><td><a href="#" class="m_service_price_wholesale" data-type="text" data-pk="'.$services_['m_services_id'].'" data-name="m_services_price_wholesale" data-title="Опт для клиента">',
				$services_['m_services_price_wholesale'],
			'</a></td><tr>
			<tr><td>Работник</td><td><a href="#" class="m_service_price_wholesale_w" data-type="text" data-pk="'.$services_['m_services_id'].'" data-name="m_services_price_wholesale_w" data-title="Опт для работника">',
				$services_['m_services_price_wholesale_w'],
			'</a></td><tr></table>
		</td>
		<td>
			<select data-name="m_services_categories_id" style="width:100%" multiple class="m_services_categories_id autoselect" placeholder="выберите из списка..." data-pk="'.$services_['m_services_id'].'" data-value="'.$services_['m_services_categories_id'].'">',
				$services_select,
			'</select>
		</td>
		<td>
			<select data-name="m_services_links" style="width:100%" multiple class="m_services_links autoselect" placeholder="выберите из списка..." data-pk="'.$services_['m_services_id'].'" data-value="'.$services_['m_services_links'].'">',
				$services_select2,
			'</select>
		</td>
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
			<a href="'.url().'?action=change&m_services_id='.$services_['m_services_id'].'" title="Редактировать" class="btn btn-xs btn-default change" data-type="text"  >
				<i class="fa fa-lg fa-pencil"></i>
			</a>
			<a href="javascript:void(0);" title="Удалить" class="btn btn-xs btn-default delete" data-type="text" data-pk="'.$services_['m_services_id'].'" data-name="m_services_id" data-title="Введите пароль для удаления записи" data-placement="left">
				<i class="fa fa-lg fa-times"></i>
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
			<div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">		
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
												$q='SELECT * FROM `formetoo_cdb`.`m_info_units`;';
												$t=$sql->query($q);
												foreach($t as $t_)
													echo '<option value="'.$t_['m_info_units_id'].'">',
														$t_['m_info_units_name'],
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
										<select name="m_services_contragents_id" class="autoselect">
											<?
												foreach($contragents->getInfo() as $contragents_){
													$ct=explode('|',$contragents_[0]['m_contragents_type']);
													if(in_array(0,$ct))
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
										<label class="label">Основная — клиент</label>
										<label class="input">
											<i class="icon-append fa fa-rub"></i>
											<input type="text" name="m_services_price_general" style="text-align:right;">
										</label>
									</section>
									<section class="col col-3">
										<label class="label">Основная — работник</label>
										<label class="input">
											<i class="icon-append fa fa-rub"></i>
											<input type="text" name="m_services_price_general_w" style="text-align:right;">
										</label>
									</section>
									<section class="col col-3">
										<label class="label">Опт — клиент</label>
										<label class="input">
											<i class="icon-append fa fa-rub"></i>
											<input type="text" name="m_services_price_wholesale" style="text-align:right;">
										</label>
									</section>
									<section class="col col-3">
										<label class="label">Опт — работник</label>
										<label class="input">
											<i class="icon-append fa fa-rub"></i>
											<input type="text" name="m_services_price_wholesale_w" style="text-align:right;">
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