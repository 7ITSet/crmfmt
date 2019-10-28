<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$products;

//список связанных работ
$products_select2='';
foreach($products->products_display_li() as $k=>$v)
	if(strlen($k)==10&&isset($v['items'])){
		$products_select2.='<optgroup label="'.$v['m_products_categories_name'].'">';
			foreach($v['items'] as $products_)
				$products_select2.='<option value="'.$products_['m_products_id'].'">'.'['.$v['m_products_categories_name'].'] '.$products_['m_products_name'].'</option>';
		$products_select2.='</optgroup>';
	}

//список категорий
$products_select='';
$categories=array();
$products->categories_childs(0,$categories,2,0,true);
foreach($categories as $categories_){
	$products_select.='<option value="'.$categories_['m_products_categories_id'].'">'.$categories_['m_products_categories_name'].'</option>';
}
$t=array();
foreach($categories as $k=>$v)
	$t[$v['m_products_categories_id']]=$v;
$categories=$t;

//список своих организаций
$products_contragents='[';
foreach($contragents->getMy() as $contragents_)
	$products_contragents.='{value:'.$contragents_['m_contragents_id'].',text:"'.str_replace('&quot;','',$contragents_['m_contragents_c_name_short']).'"},';
$products_contragents.=']';

//список единиц измерения
$products_units='[';
foreach($products->units_id as $units_)
	$products_units.='{value:'.$units_[0]['m_info_units_id'].',text:"'.$units_[0]['m_info_units_name'].'"},';
$products_units.=']';


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

	$(document).on("click",".changepos",function(){
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

	$(document).on("change","td.check input",function(){
		if($(this).prop("checked"))
			$(this).parents("tr:first").addClass("tr-selected");
		else
			$(this).parents("tr:first").removeClass("tr-selected");
	});

	$(document).on("change","input.show",function(){
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



	$("#products_list tbody tr")
		.on("mouseenter",function(){
			if($(this).find("td.check").attr("data-foto"))
				$("#left-panel span.minifyme").after("<div id=\'minifoto\'><img src=\'/images/products/"+$(this).find("td:eq(1) a").attr("data-pk")+"/"+$(this).find("td.check").attr("data-foto")+"_m.jpg\' /></div>");
		})
		.on("mouseleave",function(){
			if($(this).find("td.check").attr("data-foto"))
				$("#left-panel #minifoto").remove();
		});

	/* ТОЛЬКО ЦИФРЫ, ТОЧКА ВМЕСТО ЗАПЯТОЙ */
	$(document).on("keyup","[name=\'m_products_price_general\'],[name=\'m_products_miltiplicity\'],[name=\'m_products_prices_limit_count[]\'],[name=\'m_products_prices_limit_price[]\'],[name=\'m_products_prices_price[]\']",function(){
		$(this).val($(this).val().replace(",","."));
		$(this).val($(this).val().replace(/[^.0-9]/gim,""));
	});
	/* ОКРУГЛЕНИЕ ДО 3-Х ЗНАКОВ ПОСЛЕ ТОЧКИ В ПОЛЯХ С ЧИСЛОВЫМИ ЗНАЧЕНИЯМИ */
	$(document).on("change","[name=\'m_products_price_general\'],[name=\'m_products_miltiplicity\'],[name=\'m_products_prices_limit_count[]\'],[name=\'m_products_prices_limit_price[]\'],[name=\'m_products_prices_price[]\']",function(){
		$(this).val(($(this).val()*1).toFixed(3));
	});

	$("[name=\'m_products_attributes_list_id[]\']:first").trigger("change");

	$("#products_filter").on("keyup",function(){
		$.get(
			"/ajax/table_products.php",
			{
				search:$("#products_filter").val(),
				limit:$("#products_count option:selected").val(),
				category:$("#products_category option:selected").val(),
				page:$(".pagination li.active").text()
			},
			function(data){
				/* ПОКАЗЫВАЕМ РЕЗУЛЬТАТ В ТАБЛИЦЕ */
				$("#products_list tbody").html(data);
					/* КОЛ-ВО ЗАПИСЕЙ */
				var count=$("#products_list tbody").find("tr:first").attr("count"),
					/* КОЛ-ВО СТРАНИЦ */
					pages=Math.ceil(count/$("#products_count option:selected").val()),
					/* ТЕКУЩАЯ СТРАНИЦА */
					page=$("#products_list tbody").find("tr:first").attr("page"),
					/* СПИСОК СТРАНИЦ */
					page_list="";

				$(".table-info span").text("Всего отобрано записей: "+count);

				for(var i=1;i<=pages;i++){
					page_list+="<li><a num=\""+i+"\" "+(Math.abs(i-page)>2?"style=\"display:none\"":"")+" class=\"pagenum\" href=\"javascript:void(0);\">"+i+"</a></li>";
				}
				$(".pagination").html("<li><a href=\"javascript:void(0);\" class=\"prev\"><i class=\"fa fa-arrow-left\"></i></a></li>"+page_list+"<li><a href=\"javascript:void(0);\"  class=\"next\"><i class=\"fa fa-arrow-right\"></i></a></li>");

				$(".pagination").find("a[num="+page+"]").parent().addClass("active");

				$("td a.delete,td a.m_products_order,td a.refresh").editable({
					url: "/ajax/services_change.php",
					success: function(response,newValue){
						if($(this).hasClass("delete"))
							$(this).parents("tr:first").remove();
					}
				});
				$("td a.refresh").on("click",function(){
					$.post(
						"/ajax/services_change.php",
						{
							name:$(this).data("name"),
							pk:$(this).data("pk"),
							value:1
						},
						function(data){console.log(data);}
					);
				});
			},
			"html"
		);
	});
	$("#products_count,#products_category").on("change",function(){
		$(".pagination li").removeClass("active");
		$("#products_filter").triggerHandler("keyup");
	});
	$(document).on("click","a.pagenum",function(){
		$(this).parents("ul:first").find("li.active").removeClass("active");
		$(this).parent().addClass("active");
		$("#products_filter").triggerHandler("keyup");
	});
	$(document).on("click",".pagination a.next",function(){
		$(this).parents("ul:first").find("li.active").next().find("a.pagenum").trigger("click");
	});
	$(document).on("click",".pagination a.prev",function(){
		$(this).parents("ul:first").find("li.active").prev().find("a.pagenum").trigger("click");
	});
	$("#products_filter").triggerHandler("keyup");

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

		<article class="col-lg-12">
			<div class="jarviswidget jarviswidget-color-blue" id="wid-id-2"
				data-widget-editbutton="false"
				data-widget-colorbutton="false"
				data-widget-deletebutton="false"
				data-widget-togglebutton="false">

				<header>
					<span class="widget-icon"> <i class="fa fa-table"></i> </span>
					<h2>Список товаров</h2>
				</header>

				<div>


					<form class="smart-form" method="post">
						<div class="row">
							<section class="col col-3">
								<label class="label">Произвольный поиск</label>
								<label class="input">
									<input type="text" id="products_filter">
								</label>
							</section>
							<section class="col col-3">
								<label class="label">Категории</label>
								<select class="autoselect th-filter" id="products_category" placeholder="выберите из списка...">
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
							</section>
							<section class="col col-1">
								<label class="label">Выводить по</label>
								<select class="autoselect th-filter" id="products_count" placeholder="выберите из списка...">
										<option value="20">20 поз.</option>
										<option value="50">50 поз.</option>
										<option value="100">100 поз.</option>
										<option value="10000000" selected >Все поз.</option>
								</select>
							</section>
						</div>
						<div class="row">
							<section class="col col-3">
								<ul class="pagination">
									<li>
										<a href="javascript:void(0);" class="prev"><i class="fa fa-arrow-left"></i></a>
									</li>
									<li>
										<a href="javascript:void(0);" class="next"><i class="fa fa-arrow-right"></i></a>
									</li>
								</ul>
							</section>
						</div>
					</form>

					<div class="alert alert-info no-margin fade in table-info">
						<button class="close" data-dismiss="alert">
							×
						</button>
						<i class="fa-fw fa fa-info"></i>
						<span></span>
					</div>

					<div class="widget-body">

						<div class="table-responsive">

							<table class="table table-bordered table-striped table-condensed table-hover smart-form has-tickbox table_sort" id="products_list" width="100%">
								<thead>
									<tr>
										<th style="width:2%" class="no-order">
											<label class="checkbox">
												<input type="checkbox" class="checkbox tr" id="m_products_id_all">
												<i></i>
											</label>
										</th>
										<th style="width:1%">Артикул</th>
										<th style="width:30%">Наименование</th>
										<th style="width:5%">Ед.&nbsp;изм.</th>
										<th style="width:5%">Цена</th>
										<th style="width:18%">Категории</th>
                                        <th style="width:10%">Дата последнего изменения</th>
										<th style="width:7%">Показывать</th>
										<th style="width:2%">Порядок</th>
										<th style="width:20%">Управление</th>
									</tr>
								</thead>

								<tbody></tbody>
							</table>

						</div>

					</div>

				</div>

			</div>

		</article>
		<script type="text/javascript">
				document.addEventListener('DOMContentLoaded', () => {

					const getSort = ({ target }) => {
							const order = (target.dataset.order = -(target.dataset.order || -1));
							const index = [...target.parentNode.cells].indexOf(target);
							const collator = new Intl.Collator(['en', 'ru'], { numeric: true });
							const comparator = (index, order) => (a, b) => order * collator.compare(
									a.children[index].innerHTML,
									b.children[index].innerHTML
							);

							for(const tBody of target.closest('table').tBodies)
									tBody.append(...[...tBody.rows].sort(comparator(index, order)));

							for(const cell of target.parentNode.cells)
									cell.classList.toggle('sorted', cell === target);
					};

					document.querySelectorAll('.table_sort thead').forEach(tableTH => tableTH.addEventListener('click', () => getSort(event)));

					});
		</script>

		<article class="col-lg-6 sortable-grid ui-sortable">
			<div class="jarviswidget jarviswidget-color-blue" id="wid-id-3" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Массовые изменения</h2>
				</header>
				<div>
					<div class="widget-body">
						<form id="products-group-change" class="smart-form" method="post">
							<header>
								Основные данные
							</header>
							<fieldset>
								<div class="row">
									<section class="col col-3">
										<label class="label">
											<div class="checkbox">
											  <label>
												<input type="checkbox" name="on_m_products_unit" class="checkbox style-0">
												<span>
												  Единица измерения
												</span>
											  </label>
											</div>
										</label>
										<select name="m_products_unit" class="autoselect disabled" disabled >
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
									<section class="col col-3">
										<label class="label">
											<div class="checkbox">
											  <label>
												<input type="checkbox" name="on_m_products_unit_weight" class="checkbox style-0">
												<span>
												  Вес, кг
												</span>
											  </label>
											</div>
										</label>
										<label class="input">
											<i class="icon-append">кг</i>
											<input type="text" name="m_products_unit_weight" style="text-align:right;" class="disabled" disabled >
										</label>
									</section>

									<section class="col col-3">
										<label class="label">
											<div class="checkbox">
											  <label>
												<input type="checkbox" name="on_m_products_unit_volume" class="checkbox style-0">
												<span>
												  Объём, м<sup>3</sup>
												</span>
											  </label>
											</div>
										</label>
										<label class="input">
											<i class="icon-append">м<sup>3</sup></i>
											<input type="text" name="m_products_unit_volume" style="text-align:right;" class="disabled" disabled >
										</label>
									</section>

								</div>
								<div class="row">
									<section class="col col-9">
										<label class="label">
											<div class="checkbox">
											  <label>
												<input type="checkbox" name="on_m_products_categories_id" class="checkbox style-0">
												<span>
												  Нахождение в категориях
												</span>
											  </label>
											</div>
										</label>
										<select name="m_products_categories_id[]" style="width:100%" multiple class="autoselect disabled" disabled placeholder="выберите из списка...">
											<?
												$categories=array();
												$products->categories_childs(0,$categories,2);
												foreach($categories as $categories_){
													echo '<option value="'.$categories_['m_products_categories_id'].'">
															'.$categories_['m_products_categories_name'].'
														</option>';
												}
											?>
										</select>
									</section>
									<section class="col col-3">
										<label class="label">
											<div class="checkbox">
											  <label>
												<input type="checkbox" name="on_m_products_contragents_id" class="checkbox style-0">
												<span>
												  Организация
												</span>
											  </label>
											</div>
										</label>
										<select name="m_products_contragents_id" class="autoselect disabled" disabled placeholder="выберите из списка...">
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
												<input type="checkbox" name="on_m_products_price_general" class="checkbox style-0">
												<span>
												  Розница
												</span>
											  </label>
											</div>
										</label>
										<label class="input">
											<i class="icon-append fa fa-rub"></i>
											<input type="text" name="m_products_price_general" style="text-align:right;" class=" disabled" disabled>
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
												<input type="checkbox" name="on_m_products_show_site" class="onoffswitch-checkbox" id="myonoffswitch1" value="1">
												<label class="onoffswitch-label" for="myonoffswitch1">
													<span class="onoffswitch-inner" data-swchon-text="ВКЛ" data-swchoff-text="ВЫКЛ"></span>
													<span class="onoffswitch-switch"></span> </label>
											</span>
										</label>
										<label class="checkbox disabled" style="float:right;">
											<input type="checkbox" name="m_products_show_site" checked="checked" value="1" disabled class="disabled"/>
											<i></i>
											Показывать на сайте
										</label>
									</section>
									<section class="col col-1">
									</section>
									<section class="col col-4">
										<label class="label" style="float:left;">
											<span class="onoffswitch">
												<input type="checkbox" name="on_m_products_show_price" class="onoffswitch-checkbox" id="myonoffswitch2" value="1">
												<label class="onoffswitch-label" for="myonoffswitch2">
													<span class="onoffswitch-inner" data-swchon-text="ВКЛ" data-swchoff-text="ВЫКЛ"></span>
													<span class="onoffswitch-switch"></span> </label>
											</span>
										</label>
										<label class="checkbox disabled" style="float:right;">
											<input type="checkbox" name="m_products_show_price" checked="checked" value="1" disabled />
											<i></i>
											Выгружать в прайс
										</label>
									</section>
								</div>
								<section>
									<label class="label">
										<div class="checkbox">
										  <label>
											<input type="checkbox" name="on_m_products_links" class="checkbox style-0">
											<span>
											  Связанные товары
											</span>
										  </label>
										</div>
									</label>
									<select name="m_products_links[]" id="m_products_links" style="width:100%" multiple class="autoselect disabled" disabled placeholder="выберите из списка...">
										<?
											foreach($products->products_display_li() as $k=>$v)
												if(strlen($k)==10&&isset($v['items'])){
													echo '<optgroup label="'.$v['m_products_categories_name'].'">';
														foreach($v['items'] as $products_)
															echo '<option value="'.$products_['m_products_id'].'">',
																'['.$v['m_products_categories_name'].'] '.$products_['m_products_name'],
																'</option>';
													echo '</optgroup>';
												}
										?>
									</select>
								</section>
							</fieldset>
							<footer>
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-save"></i>
									Обновить
								</button>
							</footer>
							<input type="hidden" name="action" value="m_products_group_change"/>
							<input type="hidden" name="group_m_products_id[]"/>
						</form>
					</div>
				</div>
			</div>
		</article>
	</div>
</section>
