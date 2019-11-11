<?
defined('_DSITE') or die('Access denied');
global $user, $sql, $content, $products, $contragents;

if (get('action') == 'change' && $id = get('m_products_id')) {
	//сам товар
	$q = 'SELECT `m_products`.*, GROUP_CONCAT(`m_products_category`.`category_id` SEPARATOR \'|\') AS categories_id FROM `formetoo_main`.`m_products` 
				LEFT JOIN `formetoo_main`.`m_products_category` 
					ON `m_products_category`.`product_id`=`m_products`.`m_products_id` 
				WHERE `m_products_id`=' . $id . '  
				GROUP BY `m_products_category`.`product_id`;';
	$product = $sql->query($q)[0];

	$productAttributesGroupsId = $product['products_attributes_groups_id'];
	//атрибуты
	$q = 'SELECT * FROM `formetoo_main`.`m_products_attributes`
			LEFT JOIN `formetoo_main`.`m_products_attributes_list` ON
				`m_products_attributes`.`m_products_attributes_list_id`=`m_products_attributes_list`.`m_products_attributes_list_id`
			WHERE
				`m_products_attributes_product_id`=' . $id . ';';
	$attr = ($res = $sql->query($q)) ? $res : array();
	if ($attr) {
		$attr_values = array();
		foreach ($attr as $_attr)
			if (is_numeric($_attr['m_products_attributes_value']) && strlen($_attr['m_products_attributes_value']) == 10)
				$attr_values[] = $_attr['m_products_attributes_value'];
		$q = 'SELECT * FROM `formetoo_main`.`m_products_attributes_values` WHERE `m_products_attributes_values_id` IN(' . implode(',', $attr_values) . ');';
		$attr_values = $sql->query($q, 'm_products_attributes_values_id');
	}

	//прайсы
	$q = 'SELECT * FROM `formetoo_main`.`m_products_prices` WHERE `m_products_prices_product_id`=' . $id . ';';
	$price = ($res = $sql->query($q)) ? $res : array();

	//тексты
	$q = 'SELECT * FROM `formetoo_main`.`m_products_desc` WHERE `m_products_desc_id`=' . $id . ' LIMIT 1;';
	$desc = ($res = $sql->query($q)) ? $res[0]['m_products_desc_text'] : '';

	//прочие характеристики
	$product['categories_id'] = explode('|', $product['categories_id']);
	$product['m_products_links'] = explode('|', $product['m_products_links']);
	$foto = $product['m_products_foto'] ? json_decode($product['m_products_foto']) : array();

	//массив c id атрибутов обязательный к заполнению
	$attr_required_array = [];
	foreach ($products->attr_groups as $group) {
		if ($group[0]['m_products_attributes_groups_required']) {
			$m_products_attributes_groups_list_id_array = explode('|', $group[0]['m_products_attributes_groups_list_id']);
			foreach ($m_products_attributes_groups_list_id_array as $at) {
				$attr_required_array[] = $at;
			}
		}
	}

	//атрибуты и их значения обязательные для заполнения
	$attr_required_array_value = [];
	foreach ($attr as $attrItem) {

		if (in_array($attrItem['m_products_attributes_list_id'], $attr_required_array)) {
			$attr_required_array_value[] = [
				'm_products_attributes_list_name' => $attrItem['m_products_attributes_list_name'],
				'm_products_attributes_list_unit' => $attrItem['m_products_attributes_list_unit'],
				'm_products_attributes_value' => $attrItem['m_products_attributes_value'],
				'm_products_attributes_list_id' => $attrItem['m_products_attributes_list_id']
			];
		}
	}

	$content->setJS('

/* обязательные параметры start */

    function attr_required(){
			var attr_required_input =  $("#attr_required input");
			var data = [];

			if (attr_required_input.length > 0){
				attr_required_input.each(function(){
					var data_id_input_required = $(this).attr("data-id");
					var value_input_required = $(this).prop("disabled", true).val();

					if(data_id_input_required !== "" && value_input_required !== ""){
							data.push({id: data_id_input_required, value: value_input_required});
					}
				});

				$("#attr_required_hidden").attr("value", JSON.stringify(data));
			}
    }

    $("form#products-add").submit(function(){
      attr_required();
    });


    var m_products_attributes_groups_id_required = $("[name=\'m_products_attributes_groups_id_required\']");

	    if(m_products_attributes_groups_id_required !== undefined){
	       m_products_attributes_groups_id_required.on("change",function(){

	        $("#attr_required").empty();
	        var value_m_products_attributes_groups_id_required = $(this).val();
	        var product_id =$("[name = \'m_products_id\']").val();

                $.ajax({
                  method: "post",
                  url: "/ajax/attr_required.php",
                  data: {
                    group_id: value_m_products_attributes_groups_id_required,
                    product_id: product_id
                  },
                  success: function( result ) {
                        if(result !== 0){
                    result = JSON.parse(result);
                    for(var i=0; i<result.length; i++){
                        $("#attr_required").append(\'<div style="margin-top: 10px"><label style="width: 20%">\'+ result[i]["m_products_attributes_list_name"] +\'</label><input style="width: 70%" name="m_products_attributes_required_value[]" data-id="\'+ result[i]["m_products_attributes_list_id"] +\'" type="text" value="\'+ result[i]["m_products_attributes_value"] +\'"></div>\');
                    }
                  }
                  },
                  error: function(q,w,e){
                    console.log("ошибка");
                  }
                });

	       });
	    }
	 /* обязательные параметры end */

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
				"selected_categories_id[]" : {
					required : true
				}
			},
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});

	/* СКИДКИ */
	$("#price").df({
		max:10
	});

	/* АТРИБУТЫ */
	$("#attr").df({
		max:100,
		f_a:function(string){
			string.find("select[name=\'m_products_attributes_list_id[]\']").select2();
			string.find("[name=\'m_products_attributes_value[]\']").sug(string.find("[name=\'m_products_attributes_value[]\']").attr("suggest"));
		}
		/* f_a:function(string){
			$.get(
				"/ajax/attr.php",
				{
					"m_products_attributes_groups_id":
				},
				function(data){
					alert(data);
				}
			);
		} */
	});

	$("[name=\'m_products_attributes_groups_id\']").on("change",function(){
		/* УДАЛЯЕМ ПУСТЫЕ ПАРАМЕТРЫ */
		$("#attr .multirow").each(function(index,el){
			if(!$(el).find("[name=\'m_products_attributes_value[]\']").val())
				$(el).find("[name=\'m_products_attributes_value[]\']").parents(".multirow:first").find(".delete").trigger("click");
		});
		var attrs=$("[name=\'m_products_attributes_groups_id\'] option:selected").attr("data").split("|");
		attrs.forEach(function(item,i,arr){
			$("#attr .multirow:last").find("[name=\'m_products_attributes_list_id[]\']").select2("val",item);
			if(i<arr.length-1)
				$("#attr .multirow:last").find(".add:first").trigger("click");
		});
		$("select[name=\'m_products_attributes_list_id[]\']").trigger("change");
	})


	$("#fileupload").uploadFile({
		url:"/ajax/fileuploader/upload.php",
		acceptFiles:"image/jpeg",
		maxFileCount:500,
		maxFileSize:30*1024*1024,
		onSuccess:function(files,data,xhr,pd){
			data=JSON.parse(data);
			pd.preview.attr("src",data.file.path);
			pd.preview.show();
			pd.preview.parent().attr("href",data.file.path.substr(0,data.file.path.indexOf("_"))+"_max.jpg");
			pd.preview.parents(".ajax-file-upload-statusbar:first").find("[name=\'idfoto[]\']").val(data.file.id);
			pd.progressDiv.hide();
			pd.progressDiv.next().show();
		}
	});

	$(document).on("click",".ajax-file-upload-remove",function(){
		$(this).parents(".ajax-file-upload-statusbar:first").fadeOut(200,function(){$(this).remove()});
	});

	$(".fancybox-button").fancybox({
		nextEffect : "none",
		prevEffect : "none",
		closeBtn: true,
		helpers		: {
			title	: { type : "inside" },
			buttons	: {}
		}
	});

	$(document).on("change","[name=\'m_products_foto_main[]\']",function(){
		if($(this).prop("checked"))
			$("[name=\'m_products_foto_main[]\']").prop("checked",false);
		$(this).prop("checked",true);
	});

	/* $("[name=m_products_price_usd]").on("change",function(){
		if($(this).prop("checked"))
			$("[name=m_products_price_general],[name=\'m_products_prices_price[]\']").prev().text("$");
		else{
			$("[name=m_products_price_general],[name=\'m_products_prices_price[]\']").prev().text("р.");
		}
	}); */

	/* ТОЛЬКО ЦИФРЫ, ТОЧКА ВМЕСТО ЗАПЯТОЙ */
	$(document).on("keyup","[name=\'m_products_price_general\'],[name=\'m_products_miltiplicity\'],[name=\'m_products_prices_limit_count[]\'],[name=\'m_products_prices_limit_price[]\'],[name=\'m_products_prices_price[]\']",function(){
		$(this).val($(this).val().replace(",","."));
		$(this).val($(this).val().replace(/[^.0-9]/gim,""));
	});
	/* ОКРУГЛЕНИЕ ДО 3-Х ЗНАКОВ ПОСЛЕ ТОЧКИ В ПОЛЯХ С ЧИСЛОВЫМИ ЗНАЧЕНИЯМИ */
	$(document).on("change","[name=\'m_products_price_general\'],[name=\'m_products_miltiplicity\'],[name=\'m_products_prices_limit_count[]\'],[name=\'m_products_prices_limit_price[]\'],[name=\'m_products_prices_price[]\']",function(){
		$(this).val(($(this).val()*1).toFixed(3));
	});

	$("[name=\'m_products_attributes_value[]\']").sug($(this).attr("suggest"));

	$(document).on("change","[name=\'m_products_attributes_list_id[]\']",function(){
		$(this).parents(".multirow:first").find("[name=\'m_products_attributes_value[]\']").attr("suggest",$(this).val())
		$(this).parents(".multirow:first").find("[name=\'m_products_attributes_value[]\']").data("type",$(this).data("type")).attr("data-type",$(this).data("type"));
	});

	$("[name=\'m_products_attributes_list_id[]\']:first").trigger("change");

	tinymce.init({
		selector: "#m_products_desc",
		theme: "modern",
		plugins: [
			"advlist autolink lists link image charmap print preview hr anchor pagebreak",
			"searchreplace wordcount visualblocks visualchars code fullscreen",
			"insertdatetime media nonbreaking save table contextmenu directionality",
			"emoticons template paste textcolor colorpicker textpattern"
		],
		toolbar1: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | hr removeformat nonbreaking charmap image | link unlink anchor | forecolor backcolor | fullpage print preview code | fontselect fontsizeselect",
		image_advtab: true,
		language: "ru",
		height: "300",
		relative_urls : true,
		/* remove_script_host : false,
		convert_urls : false, */
		verify_html : false,
		fontsize_formats: "6pt 7pt 8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 17pt 18pt 19pt 20pt 21pt 22pt 23pt 24pt 25pt 26pt 27pt 28pt 29pt 30pt 31pt 32pt 33pt 34pt 35pt 36pt",
	});
');
	?>

	<?
		if (isset($_GET['success']))
			echo '<div class="row"><article class="col-lg-12">
			<div class="alert alert-success alert-block">
				<a class="close" data-dismiss="alert" href="#">x</a>
				<h4 class="alert-heading">Выполнено!</h4>
				Информация успешно добавлена!
			</div>
		</article></div>';
		if (isset($_GET['error']))
			echo '<div class="row"><article class="col-lg-12">
			<div class="alert alert-danger alert-block">
				<a class="close" data-dismiss="alert" href="#">x</a>
				<h4 class="alert-heading">Произошла ошибка!</h4>
				Произошла ошибка при сохранении данных.
			</div>
		</article></div>';
		?>

			<section id="widget-grid" class="">
				<div class="row">
					<article class="col-lg-6 sortable-grid ui-sortable">
						<div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">
							<header>
								<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
								<h2>Редактировать товарную позицию</h2>
								<span class="obligatory">* помечены поля, обязательные для заполнения.</span>
							</header>
							<div>
								<div class="widget-body">
									<form id="products-add" class="smart-form" method="post">
									
										<div class="tabs">
											<ul>
												<li><a href="#general-settings">Основные настройки</a></li>
												<li><a href="#categories-settings">Категории</a></li>
											</ul>

											<div id="general-settings">
										<header>Основные данные</header>
										<fieldset>
											<div class="row">
												<section class="col col-8">
													<label class="label">Наименование (название товара) <span class="obligatory_elem">*</span></label>
													<label class="input">
														<input type="text" name="m_products_name" value="<?= $product['m_products_name'] ?>">
													</label>
												</section>
												<section class="col col-4">
													<label class="label">Алиас <span class="obligatory_elem">*</span></label>
													<label class="input">
														<input type="text" name="slug" value="<?= $product['slug'] ?>">
													</label>
												</section>
											</div>
										</fieldset>
										<fieldset>
											<div class="row">
												<section class="col col-3">
													<label class="label">Цена<span class="obligatory_elem">*</span></label>
													<label class="input">
														<i class="icon-append">р.</i>
														<input type="text" name="m_products_price_general" style="text-align:right;" placeholder="цена розницы" value="<?= $product['m_products_price_general']; ?>">
													</label>
												</section>
												<section class="col col-3">
													<label class="label">Кратность (количество единиц в товаре, цена указывается за одну единицу)</label>
													<label class="input">
														<i class="icon-append fa fa-cubes"></i>
														<input type="text" name="m_products_miltiplicity" style="text-align:right;" placeholder="кол-во в единице" value="<?= $product['m_products_multiplicity']; ?>">
													</label>
												</section>
												<section class="col col-3">
													<label class="label">Единица измерения</label>
													<select name="m_products_unit" id="d123" class="autoselect">
														<?
															$q = 'SELECT * FROM `formetoo_cdb`.`m_info_units`;';
															$t = $sql->query($q);
															foreach ($t as $t_)
																echo '<option value="' . $t_['m_info_units_id'] . '" id="' . $t_['m_info_units_id'] . '" data-desc="(' . $t_['m_info_units_name_full'] . ')" ' . ($t_['m_info_units_id'] == $product['m_products_unit'] ? ' selected' : '') . '>',
																	$t_['m_info_units_name'] . ' (' . $t_['m_info_units_name_full'] . ')',
																	'</option>';
															?>
													</select>
												</section>

												<section class="col col-3">
													<label class="label">Организация</label>
													<select name="m_products_contragents_id" class="autoselect" placeholder="выберите из списка...">
														<?
															foreach ($contragents->getInfo() as $contragents_) {
																$ct = explode('|', $contragents_[0]['m_contragents_type']);
																if (in_array(1, $ct))
																	echo '<option value="' . $contragents_[0]['m_contragents_id'] . '">',
																		$contragents_[0]['m_contragents_c_name_short'] ? $contragents_[0]['m_contragents_c_name_short'] : $contragents_[0]['m_contragents_c_name_full'],
																		'</option>';
															}
															?>
													</select>
												</section>
											</div>
											<div class="row">
												<section class="col col-3">
													<select name="m_products_price_currency" class="autoselect" placeholder="выберите из списка...">
														<option value="1" <?= ($product['m_products_price_currency'] == 1 ? ' selected ' : '') ?>>Рубль</option>
														<option value="2" <?= ($product['m_products_price_currency'] == 2 ? ' selected ' : '') ?>>Доллар</option>
														<option value="3" <?= ($product['m_products_price_currency'] == 3 ? ' selected ' : '') ?>>Евро</option>
													</select>
												</section>
												<section class="col col-3">
													<label class="label">&nbsp;</label>
													<label class="checkbox">
														<input type="checkbox" name="m_products_exist" <?= ($product['m_products_exist'] ? ' checked' : '') ?> value="1" />
														<i></i>
														Всегда в наличии
													</label>
												</section>

											</div>
										</fieldset>

										<header>SEO-параметры</header>
										<fieldset>
											<div class="row">
												<section class="col col-6">
													<label class="label">Title</label>
													<label class="input">
														<input type="text" name="seo_parameters[]" placeholder="Title" value="<?= $product['m_products_seo_title'] ?>">
													</label>
												</section>
												<section class="col col-6">
													<label class="label">Keywords</label>
													<label class="input">
														<input type="text" name="seo_parameters[]" placeholder="Keywords" value="<?= $product['m_products_seo_keywords'] ?>">
													</label>
												</section>
											</div>
											<div class="row">
												<section class="col" style="width: 100%;">
													<label class="label">Description</label>
													<label class="textarea textarea-resizable">
														<textarea name="seo_parameters[]" rows="5" placeholder="Description"><?= $product['m_products_seo_description'] ?></textarea>
													</label>
												</section>
											</div>
										</fieldset>

										<header>Скидки</header>
										<fieldset>
											<div id="price">
												<?
													if ($price)
														foreach ($price as $_price) {
															?>
													<div class="multirow">
														<div class="row">
															<section class="col col-3">
																<label class="label">При покупке ОТ КОЛ-ВА</label>
																<label class="input">
																	<i class="icon-append fa fa-cubes"></i>
																	<input type="text" name="m_products_prices_limit_count[]" placeholder="мин. кол-во" style="text-align:right;" value="<?= $_price['m_products_prices_limit_count'] ?>">
																</label>
															</section>
															<section class="col col-3">
																<label class="label">При покупке ОТ ЦЕНЫ</label>
																<label class="input">
																	<i class="icon-append fa fa-money"></i>
																	<input type="text" name="m_products_prices_limit_price[]" placeholder="мин. цена" style="text-align:right;" value="<?= $_price['m_products_prices_limit_price'] ?>">
																</label>
															</section>
															<section class="col col-3">
																<label class="label">Стоимость СОСТАВИТ</label>
																<label class="input">
																	<i class="icon-append">р.</i>
																	<input type="text" name="m_products_prices_price[]" placeholder="стоимость" style="text-align:right;" value="<?= $_price['m_products_prices_price'] ?>">
																</label>
															</section>
															<section class="col col-3">
																<label class="label">&nbsp;</label>
																<div class="btn-group btn-labeled multirow-btn">
																	<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
																	<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
																		<span class="caret"></span>
																	</a>
																	<ul class="dropdown-menu">
																		<li>
																			<a href="javascript:void(0);" class="add">Добавить цену</a>
																		</li>
																		<li>
																			<a href="javascript:void(0);" class="delete">Удалить цену</a>
																		</li>
																	</ul>
																</div>
															</section>
														</div>
													</div>
												<?
													} else {
													?>
													<div class="multirow">
														<div class="row">
															<section class="col col-3">
																<label class="label">При покупке ОТ КОЛ-ВА</label>
																<label class="input">
																	<i class="icon-append fa fa-cubes"></i>
																	<input type="text" name="m_products_prices_limit_count[]" placeholder="мин. кол-во" style="text-align:right;">
																</label>
															</section>
															<section class="col col-3">
																<label class="label">При покупке ОТ ЦЕНЫ</label>
																<label class="input">
																	<i class="icon-append fa fa-money"></i>
																	<input type="text" name="m_products_prices_limit_price[]" placeholder="мин. цена" style="text-align:right;">
																</label>
															</section>
															<section class="col col-3">
																<label class="label">Стоимость СОСТАВИТ</label>
																<label class="input">
																	<i class="icon-append">р.</i>
																	<input type="text" name="m_products_prices_price[]" placeholder="стоимость" style="text-align:right;">
																</label>
															</section>
															<section class="col col-3">
																<label class="label">&nbsp;</label>
																<div class="btn-group btn-labeled multirow-btn">
																	<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
																	<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
																		<span class="caret"></span>
																	</a>
																	<ul class="dropdown-menu">
																		<li>
																			<a href="javascript:void(0);" class="add">Добавить цену</a>
																		</li>
																		<li>
																			<a href="javascript:void(0);" class="delete">Удалить цену</a>
																		</li>
																	</ul>
																</div>
															</section>
														</div>
													</div>
												<?
													}
													?>
											</div>
										</fieldset>
										<header>Параметры</header>
										<fieldset>
											<section>
												<label class="label">Описание товара</label>
												<label class="textarea textarea-resizable">
													<textarea name="m_products_desc" id="m_products_desc" rows="8" class="custom-scroll"><?= $desc; ?></textarea>
												</label>
											</section>
											<section>

												<? foreach ($products->attr_groups as $_group) {
														if ($_group[0]['m_products_attributes_groups_required']) {
															?>
														<section>
															<label class="label">Обязательная группа атрибутов <span class="obligatory_elem">*</span></label>
															<select name="m_products_attributes_groups_id_required" class="autoselect" placeholder="выберите из списка...">
																<option value="0">выберите из списка...</option>
														<?
																	break;
																}
															} ?>
														<? foreach ($products->attr_groups as $_group) {
																if ($_group[0]['m_products_attributes_groups_required']) {
																	echo '<option selected data="' . $_group[0]['m_products_attributes_groups_list_id'] . '" value="' . $_group[0]['m_products_attributes_groups_id'] . '" >',
																		$_group[0]['m_products_attributes_groups_name'],
																		'</option>';
																}
															}
															?>
														<? foreach ($products->attr_groups as $_group) {
																if ($_group[0]['m_products_attributes_groups_required']) {
																	?>
															</select>
															<input id="attr_required_hidden" name="attr_required_val" type="hidden" value="">
															<div id="attr_required">
																<?
																			foreach ($attr_required_array_value as $attr_required_array_value_item) {
																				echo '<div style="margin-top: 10px"><label style="width: 20%">' . $attr_required_array_value_item['m_products_attributes_list_name'] . ' (' . $attr_required_array_value_item['m_products_attributes_list_unit'] . ')</label><input style="width: 70%" name="m_products_attributes_required_value[]" data-id="' . $attr_required_array_value_item['m_products_attributes_list_id'] . '" type="text" value="' . $attr_required_array_value_item['m_products_attributes_value'] . '"></div>';
																			}
																			?>
															</div>
														</section>
												<?
															break;
														}
													} ?>

												<label class="label">Группа атрибутов</label>
												<div class="row">
													<div class="col col-xs-12 col-sm-6">
														<select name="m_products_attributes_groups_id" id="attr-group" class="autoselect" <?= $productAttributesGroupsId ? 'disabled' : ''; ?> placeholder="выберите из списка...">
															<option value="0" checked>выберите из списка...</option>
															<?
																foreach ($products->attr_groups as $_group) {
																	if (!$_group[0]['m_products_attributes_groups_required']) {
																		echo '<option ' . ($productAttributesGroupsId == $_group[0]['m_products_attributes_groups_id'] ? 'selected' : '') . ' data="' . $_group[0]['m_products_attributes_groups_list_id'] . '" value="' . $_group[0]['m_products_attributes_groups_id'] . '">',
																			$_group[0]['m_products_attributes_groups_name'],
																			'</option>';
																	}
																}
																?>
														</select>
													</div>
													<? if ($productAttributesGroupsId) { ?>
														<div class="col-xs-12 col-sm-6">
															<a id="d123-edit" class="btn btn-primary">
																<i class="fa fa-edit"></i>
																Редактировать
															</a>
														</div>
													<? } ?>
												</div>
											</section>

											<div id="attr">
												<!--Здесь появятся аттрибуты-->
											</div>
										</fieldset>
										<header>Дополнительные опции</header>
										<fieldset>
											<div class="row">
												<section class="col col-4">
													<label class="checkbox">
														<input type="checkbox" name="m_products_show_site" <?= ($product['m_products_show_site'] ? ' checked' : '') ?> value="1" />
														<i></i>
														Показывать на сайте
													</label>
													<label class="checkbox">
														<input type="checkbox" name="m_products_show_price" <?= ($product['m_products_show_price'] ? ' checked' : '') ?> value="1" />
														<i></i>
														Выгружать в прайс
													</label>
												</section>
												<section class="col col-8">
													<label class="label">Связанные товары</label>
													<select name="m_products_links[]" id="m_products_links" style="width:100%" multiple class="autoselect" placeholder="выберите из списка...">
														<?
															foreach ($products->products_display_li() as $k => $v)
																if (strlen($k) == 10 && isset($v['items'])) {
																	echo '<optgroup label="' . $v['m_products_categories_name'] . '">';
																	foreach ($v['items'] as $products_)
																		if ($products_['m_products_id'] != $product['m_products_id'])
																			echo '<option value="' . $products_['m_products_id'] . '" ' . (in_array($products_['m_products_id'], $product['m_products_links']) !== false ? ' selected ' : '') . '>',
																				'[' . $v['m_products_categories_name'] . '] ' . $products_['m_products_name'],
																				'</option>';
																	echo '</optgroup>';
																}
															?>
													</select>
												</section>
											</div>
										</fieldset>
										<header>Фото товара</header>
										<fieldset>
											<section>
												<div id="fileupload"></div>
												<?
													echo '<div class="ajax-file-upload-container">';

													foreach ($foto as $_foto)

														echo '
														<div class="ajax-file-upload-statusbar">
															<div class="ajax-file-upload-preview-container">',
															($product['m_products_id_isolux']
																? '<a class="fancybox-button" rel="group" href="//crm.formetoo.ru/images/products/' . $product['m_products_id'] . '/' . $_foto->file . '_max.jpg">
																		<img class="ajax-file-upload-preview" src="//crm.formetoo.ru/images/products/' . $product['m_products_id'] . '/' . $_foto->file . '_min.jpg" style="width: auto; height: auto;">
																	</a>'
																//																	? '<a class="fancybox-button" rel="group" href="//st.formetoo.ru/'.substr($product['m_products_id_isolux'],0,2).'/SN'.$product['m_products_id_isolux'].'/'.$_foto->file.'_max.jpg">
																//																		<img class="ajax-file-upload-preview" src="//st.formetoo.ru/'.substr($product['m_products_id_isolux'],0,2).'/SN'.$product['m_products_id_isolux'].'/'.$_foto->file.'_min.jpg" style="width: auto; height: auto;">
																//																	</a>'

																//																	: '<a class="fancybox-button" rel="group" href="//st.formetoo.ru/v/'.$product['m_products_id'].'/'.$_foto.'_max.jpg">
																//                                                                    : '<a class="fancybox-button" rel="group" href="//crm.formetoo.ru/images/products/'.$product['m_products_id'].'/'.$_foto->file.'_m.jpg">
																//																		<img class="ajax-file-upload-preview" src="//st.formetoo.ru/'.substr($product['m_products_id_isolux'],0,2).'/SN'.$product['m_products_id_isolux'].'/'.$_foto->file.'_min.jpg" style="width: auto; height: auto;">
																//																	</a>'
																: '<a class="fancybox-button" rel="group" href="//crm.formetoo.ru/images/products/' . $product['m_products_id'] . '/' . $_foto->file . '_max.jpg">
																		<img class="ajax-file-upload-preview" src="//crm.formetoo.ru/images/products/' . $product['m_products_id'] . '/' . $_foto->file . '_min.jpg" style="width: auto; height: auto;">
																	</a>'),
															'</div>
															<label class="checkbox ajax-file-upload-info" style="margin-top:8px;">
																<input type="checkbox" name="m_products_foto_main[]" ' . ($_foto->main ? 'checked' : '') . ' value="' . $_foto->file . '"/><i></i>Основное фото
															</label>
															<a class="ajax-file-upload-remove btn btn-default btn-xs txt-color-red" title="Удалить фото">
																<i class="fa fa-trash-o"></i>
															</a>
															<input type="hidden" name="idfoto[]" value="' . $_foto->file . '">
														</div>
												';
													echo '</div>';
													?>
											</section>
										</fieldset>
										</div>
										<div id="categories-settings">
											<div class="widget-body">
											<div class="row">
												<div class="col-xs-12">
													<div class="dd" id="product-categories-list">
														<?$products->product_categories_display(0, $product['categories_id'])?>
													</div>
												</div>
											</div>
											</div>
										</div>
										<footer>
											<button type="submit" class="btn btn-primary">
												<i class="fa fa-save"></i>
												Сохранить данные
											</button>
										</footer>
										<input type="hidden" name="m_products_id" value="<?= $product['m_products_id'] ?>" />
										<input type="hidden" name="action" value="products_change" />
									</form>
								</div>
							</div>
						</div>
					</article>
				</div>
			</section>
	</div>

<?
} else {
	//список связанных работ
	$products_select2 = '';
	foreach ($products->products_display_li() as $k => $v)
		if (strlen($k) == 10 && isset($v['items'])) {
			$products_select2 .= '<optgroup label="' . $v['m_products_categories_name'] . '">';
			foreach ($v['items'] as $products_)
				$products_select2 .= '<option value="' . $products_['m_products_id'] . '">' . '[' . $v['m_products_categories_name'] . '] ' . $products_['m_products_name'] . '</option>';
			$products_select2 .= '</optgroup>';
		}

	//список своих организаций
	$products_contragents = '[';
	foreach ($contragents->getMy() as $contragents_)
		$products_contragents .= '{value:' . $contragents_['m_contragents_id'] . ',text:"' . str_replace('&quot;', '', $contragents_['m_contragents_c_name_short']) . '"},';
	$products_contragents .= ']';

	//список единиц измерения
	$products_units = '[';
	foreach ($products->units_id as $units_)
		$products_units .= '{value:' . $units_[0]['m_info_units_id'] . ',text:"' . $units_[0]['m_info_units_name'] . '"},';
	$products_units .= ']';


	$content->setJS('

/* обязательные параметры start */

    function attr_required(){

        var attr_required_input =  $("#attr_required input");
        var data = [];

        if(attr_required_input.length > 0){
             attr_required_input.each(function(){

            var data_id_input_required = $(this).attr("data-id");
            var value_input_required = $(this).prop("disabled", true).val();

            if(data_id_input_required !== "" && value_input_required !== ""){
                data.push({id: data_id_input_required, value: value_input_required});
            }


        });

        $("#attr_required_hidden").attr("value", JSON.stringify(data));
        }

    }

    $("form#products-add").submit(function(){
      attr_required();
    });


    var m_products_attributes_groups_id_required = $("[name=\'m_products_attributes_groups_id_required\']");

	    if(m_products_attributes_groups_id_required !== undefined){
	       m_products_attributes_groups_id_required.on("change",function(){

	        $("#attr_required").empty();
	        var value_m_products_attributes_groups_id_required = $(this).val();

                $.ajax({
                  method: "post",
                  url: "/ajax/attr_required.php",
                  data: {
                    group_id: value_m_products_attributes_groups_id_required,
                    product_id: 0
                  },
                  success: function( result ) {
                        if(result !== 0){
                    result = JSON.parse(result);
                    for(var i=0; i<result.length; i++){
                        $("#attr_required").append(\'<div style="margin-top: 10px"><label style="width: 20%">\'+ result[i]["m_products_attributes_list_name"] +\'</label><input style="width: 70%" name="m_products_attributes_required_value[]" data-id="\'+ result[i]["m_products_attributes_list_id"] +\'" type="text" placeholder=""></div>\');
                    }
                  }
                  },
                  error: function(q,w,e){
                    console.log("ошибка");
                  }
                });

	       });
	    }
	 /* обязательные параметры end */

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
			"selected_categories_id[]" : {
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

	$("td a.delete").editable({
		url: "/ajax/services_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete"))
				$(this).parents("tr:first").remove();
		}
	});/* .m_products_order */

	/* $("input.show").on("change",function(){
		$.post(
			"/ajax/services_change.php",
			{
				name:$(this).attr("data-name"),
				pk:$(this).attr("data-pk"),
				value:$(this).prop("checked")
			}
		);
	}); */

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

	/* СКИДКИ */
	$("#price").df({
		max:10
	});

	/* АТРИБУТЫ */
	$("#attr").df({
		max:100,
		f_a:function(string){
			string.find("select[name=\'m_products_attributes_list_id[]\']").select2();
			string.find("[name=\'m_products_attributes_value[]\']").sug(string.find("[name=\'m_products_attributes_value[]\']").attr("suggest"));
		}
		/* f_a:function(string){
			$.get(
				"/ajax/attr.php",
				{
					"m_products_attributes_groups_id":
				},
				function(data){
					alert(data);
				}
			);
		} */
	});

	$("[name=\'m_products_attributes_groups_id\']").on("change",function(){
		/* УДАЛЯЕМ ПУСТЫЕ ПАРАМЕТРЫ */
		$("#attr .multirow").each(function(index,el){
			if(!$(el).find("[name=\'m_products_attributes_value[]\']").val())
				$(el).find("[name=\'m_products_attributes_value[]\']").parents(".multirow:first").find(".delete").trigger("click");
		});
		var attrs=$("[name=\'m_products_attributes_groups_id\'] option:selected").attr("data").split("|");
		attrs.forEach(function(item,i,arr){
			$("#attr .multirow:last").find("[name=\'m_products_attributes_list_id[]\']").select2("val",item);
			if(i<arr.length-1)
				$("#attr .multirow:last").find(".add:first").trigger("click");
		});
		$("select[name=\'m_products_attributes_list_id[]\']").trigger("change");
	});


	$("#fileupload").uploadFile({
		url:"/ajax/fileuploader/upload.php",
		acceptFiles:"image/jpeg",
		maxFileCount:500,
		maxFileSize:30*1024*1024,
		onSuccess:function(files,data,xhr,pd){
			data=JSON.parse(data);
			pd.preview.attr("src",data.file.path);
			pd.preview.show();
			pd.preview.parent().attr("href",data.file.path.substr(0,data.file.path.indexOf("_"))+"_b.jpg");
			pd.preview.parents(".ajax-file-upload-statusbar:first").find("[name=\'idfoto[]\']").val(data.file.id);
			pd.progressDiv.hide();
			pd.progressDiv.next().show();
		}
	});

	$(document).on("click",".ajax-file-upload-remove",function(){
		$(this).parents(".ajax-file-upload-statusbar:first").fadeOut(200,function(){$(this).remove()});
	});

	$(".fancybox-button").fancybox({
		nextEffect : "none",
		prevEffect : "none",
		closeBtn: true,
		helpers		: {
			title	: { type : "inside" },
			buttons	: {}
		}
	});

	$(document).on("change","[name=\'m_products_foto_main[]\']",function(){
		if($(this).prop("checked"))
			$("[name=\'m_products_foto_main[]\']").prop("checked",false);
		$(this).prop("checked",true);
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

	$("[name=\'m_products_attributes_value[]\']").sug($(this).attr("suggest"));

	$(document).on("change","[name=\'m_products_attributes_list_id[]\']",function(){console.log($(this).attr("data-type"));
		$(this).parents(".multirow:first").find("[name=\'m_products_attributes_value[]\']").attr("suggest",$(this).val());
		$(this).parents(".multirow:first").find("[name=\'m_products_attributes_value[]\']").data("type",$(this).attr("data-type")).attr("data-type",$(this).attr("data-type"));
	});

	$("[name=\'m_products_attributes_list_id[]\']:first").trigger("change");

	tinymce.init({
		selector: "#m_products_desc",
		theme: "modern",
		plugins: [
			"advlist autolink lists link image charmap print preview hr anchor pagebreak",
			"searchreplace wordcount visualblocks visualchars code fullscreen",
			"insertdatetime media nonbreaking save table contextmenu directionality",
			"emoticons template paste textcolor colorpicker textpattern"
		],
		toolbar1: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | hr removeformat nonbreaking charmap image | link unlink anchor | forecolor backcolor | fullpage print preview code | fontselect fontsizeselect",
		image_advtab: true,
		language: "ru",
		height: "100",
		relative_urls : true,
		/* remove_script_host : false,
		convert_urls : false, */
		verify_html : false,
		fontsize_formats: "6pt 7pt 8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 17pt 18pt 19pt 20pt 21pt 22pt 23pt 24pt 25pt 26pt 27pt 28pt 29pt 30pt 31pt 32pt 33pt 34pt 35pt 36pt",
	});

');
	?>

	<section id="widget-grid" class="">

		<?
			if (isset($_GET['success']))
				echo '<div class="row"><article class="col-lg-12">
			<div class="alert alert-success alert-block">
				<a class="close" data-dismiss="alert" href="#">x</a>
				<h4 class="alert-heading">Выполнено!</h4>
				Информация успешно добавлена!
			</div>
		</article></div>';
			if (isset($_GET['error']))
				echo '<div class="row"><article class="col-lg-12">
			<div class="alert alert-danger alert-block">
				<a class="close" data-dismiss="alert" href="#">x</a>
				<h4 class="alert-heading">Произошла ошибка!</h4>
				Произошла ошибка при сохранении данных.
			</div>
		</article></div>';





			//$group = [];
			//foreach ($products->attr_groups as $_group){
			//    $group[] = $_group[0]['m_products_attributes_groups_list_id'];
			//}
			//
			//echo "<pre>";
			//var_dump($products->attr_groups);
			//echo "</pre>";
			//die;



			?>

		<div class="row">
			<article class="col-lg-6 sortable-grid ui-sortable">
				<div class="row control_header_row">
					<div class="control_header">
						<button type="submit" class="btn btn-primary control_btn">
							<i class="fa fa-save"></i>
							Сохранить
						</button>
						<button type="submit" class="btn btn-primary control_btn">
							<i class="fa fa-eye"></i>
							Посмотреть
						</button>
					</div>
				</div>
				<div class="jarviswidget" id="wid-id-30" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">
					<header>
						<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
						<h2>Добавить товарную позицию</h2>
						<span class="obligatory">* помечены поля, обязательные для заполнения.</span>
					</header>
					<div>
						<div class="widget-body">
							<form id="products-add" class="smart-form" method="post">
								<div class="tabs">
									<ul>
										<li><a href="#general-settings">Основные настройки</a></li>
										<li><a href="#categories-settings">Категории</a></li>
									</ul>

									<div id="general-settings">
										<header>Основные данные</header>
										<fieldset>
											<div class="row">
												<section class="col col-8">
													<label class="label">Наименование (название товара) <span class="obligatory_elem">*</span></label>
													<label class="input">
														<input type="text" name="m_products_name" value="">
													</label>
												</section>
												<section class="col col-4">
													<label class="label">Алиас <span class="obligatory_elem">*</span></label>
													<label class="input">
														<input type="text" name="slug" value="">
													</label>
												</section>
											</div>
										</fieldset>
										<fieldset>
											<div class="row">
												<section class="col col-3">
													<label class="label">Цена <span class="obligatory_elem">*</span></label>
													<label class="input">
														<i class="icon-append">р.</i>
														<input type="text" name="m_products_price_general" style="text-align:right;" placeholder="цена розницы">
													</label>
												</section>
												<section class="col col-3">
													<label class="label">Кратность (количество единиц в товаре, цена указывается за одну единицу)</label>
													<label class="input">
														<i class="icon-append fa fa-cubes"></i>
														<input type="text" name="m_products_miltiplicity" style="text-align:right;" placeholder="кол-во в единице">
													</label>
												</section>
												<section class="col col-3">
													<label class="label">Единица измерения</label>
													<select name="m_products_unit" id="d123" class="autoselect">
														<?
															$q = 'SELECT * FROM `formetoo_cdb`.`m_info_units`;';
															$t = $sql->query($q);
															foreach ($t as $t_)
																echo '<option value="' . $t_['m_info_units_id'] . '" id="' . $t_['m_info_units_id'] . '" data-desc="(' . $t_['m_info_units_name_full'] . ')">',
																	$t_['m_info_units_name'] . ' (' . $t_['m_info_units_name_full'] . ')',
																	'</option>';
															?>
													</select>
												</section>
												<section class="col col-3">
													<label class="label">Организация</label>
													<select name="m_products_contragents_id" class="autoselect" placeholder="выберите из списка...">
														<?
															foreach ($contragents->getInfo() as $contragents_) {
																$ct = explode('|', $contragents_[0]['m_contragents_type']);
																if (in_array(1, $ct))
																	echo '<option value="' . $contragents_[0]['m_contragents_id'] . '">',
																		$contragents_[0]['m_contragents_c_name_short'] ? $contragents_[0]['m_contragents_c_name_short'] : $contragents_[0]['m_contragents_c_name_full'],
																		'</option>';
															}
															?>
													</select>
												</section>
											</div>
											<div class="row">
												<section class="col col-3">
													<select name="m_products_price_currency" class="autoselect" placeholder="выберите из списка...">
														<option value="1" selected>Рубль</option>
														<option value="2">Доллар</option>
														<option value="3">Евро</option>
													</select>
												</section>
												<section class="col col-3">
													<label class="checkbox">
														<input type="checkbox" name="m_products_exist" checked value="1" />
														<i></i>
														Всегда в наличии
													</label>
												</section>
											</div>
										</fieldset>

										<header>SEO-параметры</header>
										<fieldset>
											<div class="row">
												<section class="col col-6">
													<label class="label">Title</label>
													<label class="input">
														<input type="text" name="seo_parameters[]" placeholder="Title">
													</label>
												</section>
												<section class="col col-6">
													<label class="label">Keywords</label>
													<label class="input">
														<input type="text" name="seo_parameters[]" placeholder="Keywords">
													</label>
												</section>
											</div>
											<div class="row">
												<section class="col" style="width: 100%;">
													<label class="label">Description</label>
													<label class="textarea textarea-resizable">
														<textarea name="seo_parameters[]" rows="5" placeholder="Description"></textarea>
													</label>
												</section>
											</div>
										</fieldset>

										<header>Скидки</header>
										<fieldset>
											<div id="price">
												<div class="multirow">
													<div class="row">
														<section class="col col-3">
															<label class="label">При покупке ОТ КОЛ-ВА</label>
															<label class="input">
																<i class="icon-append fa fa-cubes"></i>
																<input type="text" name="m_products_prices_limit_count[]" placeholder="мин. кол-во" style="text-align:right;">
															</label>
														</section>
														<section class="col col-3">
															<label class="label">При покупке ОТ ЦЕНЫ</label>
															<label class="input">
																<i class="icon-append fa fa-money"></i>
																<input type="text" name="m_products_prices_limit_price[]" placeholder="мин. цена" style="text-align:right;">
															</label>
														</section>
														<section class="col col-3">
															<label class="label">Стоимость СОСТАВИТ</label>
															<label class="input">
																<i class="icon-append">р.</i>
																<input type="text" name="m_products_prices_price[]" placeholder="стоимость" style="text-align:right;">
															</label>
														</section>
														<section class="col col-3">
															<label class="label">&nbsp;</label>
															<div class="btn-group btn-labeled multirow-btn">
																<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
																<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
																	<span class="caret"></span>
																</a>
																<ul class="dropdown-menu">
																	<li>
																		<a href="javascript:void(0);" class="add">Добавить цену</a>
																	</li>
																	<li>
																		<a href="javascript:void(0);" class="delete">Удалить цену</a>
																	</li>
																</ul>
															</div>
														</section>
													</div>
												</div>
											</div>
										</fieldset>
										<header>Параметры</header>
										<fieldset>
											<section>
												<label class="label">Описание товара</label>
												<label class="textarea textarea-resizable">
													<textarea name="m_products_desc" id="m_products_desc" rows="5" class="custom-scroll"></textarea>
												</label>
											</section>

											<? foreach ($products->attr_groups as $_group) {
													if ($_group[0]['m_products_attributes_groups_required']) {
														?>
													<section>
														<label class="label">Обязательная группа атрибутов <span class="obligatory_elem">*</span></label>
														<select name="m_products_attributes_groups_id_required" id="d123" class="autoselect" placeholder="выберите из списка...">
															<option value="0" checked>выберите из списка...</option>
													<?

																break;
															}
														} ?>

													<? foreach ($products->attr_groups as $_group) {
															if ($_group[0]['m_products_attributes_groups_required']) {
																echo '<option data="' . $_group[0]['m_products_attributes_groups_list_id'] . '" value="' . $_group[0]['m_products_attributes_groups_id'] . '" >',
																	$_group[0]['m_products_attributes_groups_name'],
																	'</option>';
															}
														}
														?>
													<? foreach ($products->attr_groups as $_group) {
															if ($_group[0]['m_products_attributes_groups_required']) {
																?>
														</select>
														<input id="attr_required_hidden" name="attr_required_val" type="hidden" value="">
														<div id="attr_required">
														</div>
													</section>
											<?

														break;
													}
												} ?>
											<section>
												<label class="label">Группа атрибутов</label>
												<select name="m_products_attributes_groups_id" id="attr-group" class="autoselect" placeholder="выберите из списка...">
													<option value="0" checked>выберите из списка...</option>
													<?
														foreach ($products->attr_groups as $_group) {
															if (!$_group[0]['m_products_attributes_groups_required']) {
																echo '<option data="' . $_group[0]['m_products_attributes_groups_list_id'] . '" value="' . $_group[0]['m_products_attributes_groups_id'] . '" >',
																	$_group[0]['m_products_attributes_groups_name'],
																	'</option>';
															}
														}
														?>
												</select>
											</section>

											<div id="attr">
												<!--Здесь появятся аттрибуты-->
											</div>
										</fieldset>
										<header>Дополнительные опции</header>
										<fieldset>
											<div class="row">
												<section class="col col-4">
													<label class="checkbox">
														<input type="checkbox" name="m_products_show_site" checked="checked" value="1" />
														<i></i>
														Показывать на сайте
													</label>
													<label class="checkbox">
														<input type="checkbox" name="m_products_show_price" checked="checked" value="1" />
														<i></i>
														Выгружать в прайс
													</label>
												</section>
												<section class="col col-8">
													<label class="label">Связанные товары</label>
													<select name="m_products_links[]" id="m_products_links" style="width:100%" multiple class="autoselect" placeholder="выберите из списка...">
														<?
															foreach ($products->products_display_li() as $k => $v)
																if (strlen($k) == 10 && isset($v['items'])) {
																	echo '<optgroup label="' . $v['m_products_categories_name'] . '">';
																	foreach ($v['items'] as $products_)
																		echo '<option value="' . $products_['m_products_id'] . '">',
																			'[' . $v['m_products_categories_name'] . '] ' . $products_['m_products_name'],
																			'</option>';
																	echo '</optgroup>';
																}
															?>
													</select>
												</section>
											</div>
										</fieldset>
										<header>Фото товара</header>
										<fieldset>
											<section>
												<div id="fileupload"></div>
												<?
													$foto = array();
													echo '<div class="ajax-file-upload-container">';
													foreach ($foto as $_foto)
														echo '
															<div class="ajax-file-upload-statusbar">
																<div class="ajax-file-upload-preview-container">
																	<a class="fancybox-button" rel="group" href="/foto/portfolio/' . $order['m_orders_id'] . '/' . ($_foto->file) . '_b.jpg">
																		<img class="ajax-file-upload-preview" src="/foto/portfolio/' . $order['m_orders_id'] . '/' . ($_foto->file) . '_m.jpg" style="width: auto; height: auto;">
																	</a>
																</div>
																<label class="input ajax-file-upload-info" style="margin-top: 8px;">
																	<input type="text" name="m_portfolio_foto_item_name[]" class="form-control" placeholder="название" value="' . ($_foto->name) . '">
																</label>
																<label class="textarea textarea-resizable ajax-file-upload-info">
																	<textarea name="m_portfolio_foto_item_description[]" rows="3" class="custom-scroll" placeholder="описание">' . ($_foto->description) . '</textarea>
																</label>
																<div class="ajax-file-upload-filename">
																	<a href="#" class="copy-filename" title="Нажмите, чтобы скопировать ссылку на фото, а затем на кнопку «Вставить фото» в редакторе" data-id="' . $_foto->file . '">Скопировать</a>
																</div>
																<a class="ajax-file-upload-remove btn btn-default btn-xs txt-color-red" title="Удалить фото">
																	<i class="fa fa-trash-o"></i>
																</a>
																<input type="hidden" name="idfoto[]" value="' . $_foto->file . '">
															</div>
													';
													echo '</div>';
													?>
											</section>
										</fieldset>
									</div>
									<div id="categories-settings">
										<div class="widget-body">
										<div class="row">
											<div class="col-xs-12">
												<div class="dd" id="product-categories-list">
													<?$products->product_categories_display(0)?>
												</div>
											</div>
										</div>
										</div>
									</div>
								<footer>
									<button type="submit" class="btn btn-primary">
										<i class="fa fa-save"></i>
										Сохранить данные
									</button>
								</footer>
								<input type="hidden" name="action" value="products_add" />
								<input type="hidden" name="group_m_products_id[]" />
							</form>
						</div>
					</div>
				</div>
			</article>
		</div>
	</section>
<? } ?>
<script src="/js/jquery.df.js"></script>
<script src="/js/jquery.suggest_attr.js"></script>
<link href="/js/plugin/fileuploader/uploadfile.css" rel="stylesheet" />
<script src="/js/plugin/fileuploader/jquery.uploadfile.js"></script>
<link rel="stylesheet" type="text/css" href="/js/plugin/fancybox/jquery.fancybox.css" />
<link rel="stylesheet" type="text/css" href="/js/plugin/fancybox/helpers/jquery.fancybox-buttons.css" />
<link rel="stylesheet" type="text/css" href="/js/plugin/fancybox/helpers/jquery.fancybox-thumbs.css" />
<script type="text/javascript" src="/js/plugin/fancybox/jquery.fancybox.pack.js"></script>
<script type="text/javascript" src="/js/plugin/fancybox/helpers/jquery.mousewheel-3.0.6.pack.js"></script>
<script type="text/javascript" src="/js/plugin/fancybox/helpers/jquery.fancybox-media.js"></script>
<script type="text/javascript" src="/js/plugin/fancybox/helpers/jquery.fancybox-thumbs.js"></script>
<script type="text/javascript" src="/js/plugin/fancybox/helpers/jquery.fancybox-buttons.js"></script>
<script src="/js/plugin/tinymce/tinymce.min.js"></script>

<script src="/js/libs/speakingurl.js"></script>
<script src="/js/plugin/jquery-nestable/jquery.nestable.min.js"></script>
<script>
	let source = $('input[name="m_products_name"]');
	let target = $('input[name="slug"]');
	slugify(source, target);

	function slugify(source, target) {
		var option = {
			separator: '_',
			//mark: true
		}

		if (target.val() !== '' && target.val() !== undefined) {
			target.data('locked', true);
		}

		target.on('keyup change', function() {
			if (target.val() === '' || target.val() === undefined) {
				target.data('locked', false);
			}
		});

		source.on('keyup change', function() {
			if (true === target.data('locked')) {
				return;
			}
			if (target.is('input') || target.is('textarea')) {
				target.val(getSlug(source.val(), option));
			} else {
				target.text(getSlug(source.val(), option));
			}
		});
	}

	$('#d123-edit').on("click", function() {
		$("#attr-group").prop("disabled", false);
	});

	function renderAttributesGroup(attirbutes_group_id, products_id) {
		$.post(
			"/ajax/product_detail_attributes.php", {
				attirbutes_group_id,
				products_id
			},
			function(data) {
				if (data != "ERROR") {
					$("#attr").html(data);
				}
			}
		);
	}

	$('#attr-group').on('change', function(e) {
		e.val != 0 && renderAttributesGroup(e.val, <?= get('m_products_id') ? get('m_products_id') : '' ?>);
	});
	<?
	if (!empty($productAttributesGroupsId)) {
		echo 'renderAttributesGroup("' . $productAttributesGroupsId . '", "' . get('m_products_id') . '");';
	}
	?>
</script>