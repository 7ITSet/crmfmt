<?
defined('_DSITE') or die('Access denied');
global $user, $sql, $content, $contragents, $logistic, $info, $products;

$units = array();
foreach ($products->attr_id as $_attr) {
	$_attr = $_attr[0];
	$units = array_merge($units, explode('|', $_attr['m_products_attributes_list_unit']));
}
foreach ($products->units_id as $_attr) {
	$_attr = $_attr[0];
	$units = array_merge($units, explode('|', $_attr['m_info_units_name']));
}
$units = array_unique(array_diff($units, array('')));

$attrList = array();
foreach ($products->attr_groups as $_attrKey => $_attrValue) {
	$attrsGroup = explode('|', $_attrValue[0]['m_products_attributes_groups_list_id']);
	foreach ($attrsGroup as $_groupKey => $_groupValue) {
		$attrList[$_groupValue][] = $_attrValue[0]['m_products_attributes_groups_name'];
	}
}

array_walk($units, function (&$el) {
	$el = '"' . $el . '"';
});



//получение списка обязательных атрибутов
$q = "SELECT `m_products_attributes_groups_list_id` FROM `formetoo_main`.`m_products_attributes_groups` WHERE `m_products_attributes_groups_required` = '1';";

$attrs_id_required = $sql->query($q);

$attrs_id_required_array = [];
foreach ($attrs_id_required as $attrs_id_required_item) {
	$attrs_id_required_array_explode = explode('|', $attrs_id_required_item['m_products_attributes_groups_list_id']);

	foreach ($attrs_id_required_array_explode as $attrs_id_required_array_explode_item) {
		$attrs_id_required_array[] = $attrs_id_required_array_explode_item;
	}
}




if ($id = get('m_products_attributes_list_id')) {
	$attr = $products->attr_id[$id][0];

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

		$("#m_products_attributes_list_unit").editable({
			inputclass: "input-large",
			select2: {
				tags: [' . implode(',', $units) . '],
				placeholder: "Укажите теги",
				tokenSeparators: [","],
				maximumSelectionSize: 1
			}
		});
		$("#m_products_attributes_list_unit").on("save",function(e,params){
			$("[name=\'m_products_attributes_list_unit\']").val(params.newValue.join("|"));
		});




	');
	?>

	<section id="widget-grid" class="">

		<div class="row">

			<article class="col-lg-6 sortable-grid ui-sortable">
				<div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">
					<header>
						<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
						<h2>Редактировать атрибут</h2>
						<span class="obligatory">* помечены поля, обязательные для заполнения.</span>
					</header>
					<div>
						<div class="widget-body">
							<form id="products-add" class="smart-form" method="post">
								<fieldset>
									<div class="row">
										<section class="col col-6">
											<label class="label">Наименование (название атрибута) <span class="obligatory_elem">*</span></label>
											<label class="input">
												<input type="text" name="m_products_attributes_list_name" value="<?= $attr['m_products_attributes_list_name']; ?>">
											</label>
										</section>
										<section class="col col-6">
											<label class="label">Наименование для URL (единый указатель для атрибута) <span class="obligatory_elem">*</span></label>
											<label class="input">
												<input type="text" name="m_products_attributes_list_name_url" value="<?= $attr['m_products_attributes_list_name_url']; ?>">
											</label>
										</section>
									</div>
									<div class="row">
										<section class="col col-6">
											<label class="label">Тип данных</label>
											<select name="m_products_attributes_list_type" class="autoselect">
												<option value="1" <?= ($attr['m_products_attributes_list_type'] == 1 ? 'selected' : ''); ?>>Текстовый</option>
												<option value="2" <?= ($attr['m_products_attributes_list_type'] == 2 ? 'selected' : ''); ?>>Числовой</option>
												<option value="3" <?= ($attr['m_products_attributes_list_type'] == 3 ? 'selected' : ''); ?>>Логический</option>
											</select>
										</section>
										<section class="col col-6">
											<label class="label">Ед. измерения</label>
											<a href="#" id="m_products_attributes_list_unit" data-type="select2" data-pk="1" data-original-title="Укажите теги"><?= $attr['m_products_attributes_list_unit']; ?></a>
											<input type="hidden" name="m_products_attributes_list_unit" value="<?= $attr['m_products_attributes_list_unit']; ?>" />
										</section>
									</div>
									<div class="row">
										<section class="col col-4">
											<label class="label">Параметры</label>
											<label class="checkbox">
												<input type="checkbox" name="m_products_attributes_list_main" <?= $attr['m_products_attributes_list_main'] ? 'checked="checked"' : '' ?> value="1" />
												<i></i>
												Основной
											</label>
											<label class="checkbox">
												<input type="checkbox" name="m_products_attributes_list_required" <?= $attr['m_products_attributes_list_required'] ? 'checked="checked"' : '' ?> value="0" />
												<i></i>
												Обязателен для заполнения
											</label>
										</section>
										<section class="col col-4">
											<label class="label">&nbsp;</label>
											<label class="checkbox">
												<input type="checkbox" name="m_products_attributes_list_site_search" <?= $attr['m_products_attributes_list_site_search'] ? 'checked="checked"' : '' ?> value="1" />
												<i></i>
												Участвует в поиске
											</label>
											<label class="checkbox">
												<input type="checkbox" name="m_products_attributes_list_site_filter" <?= $attr['m_products_attributes_list_site_filter'] ? 'checked="checked"' : '' ?> value="1" />
												<i></i>
												Участвует в фильтрах
											</label>
										</section>
										<section class="col col-4">
											<label class="label">&nbsp;</label>
											<label class="checkbox">
												<input type="checkbox" name="m_products_attributes_list_site_open" <?= $attr['m_products_attributes_list_site_open'] ? 'checked="checked"' : '' ?> value="1" />
												<i></i>
												Раскрыт по умолчанию
											</label>
											<label class="checkbox">
												<input type="checkbox" name="m_products_attributes_list_active" <?= $attr['m_products_attributes_list_active'] ? 'checked="checked"' : '' ?> value="1" />
												<i></i>
												Активен
											</label>
										</section>
									</div>
									<section>
										<label class="label">Комментарий</label>
										<label class="textarea textarea-resizable">
											<textarea name="m_products_attributes_list_comment" rows="2" class="custom-scroll" placeholder="Для внутреннего использования"><?= $attr['m_products_attributes_list_comment']; ?></textarea>
										</label>
									</section>
									<section>
										<label class="label">Подсказка для сайта</label>
										<label class="textarea textarea-resizable">
											<textarea name="m_products_attributes_list_hint" rows="5" class="custom-scroll" placeholder="Детальное описание атрибута"><?= $attr['m_products_attributes_list_hint']; ?></textarea>
										</label>
									</section>
								</fieldset>
								<footer>
									<button type="submit" class="btn btn-primary">
										<i class="fa fa-save"></i>
										Сохранить данные
									</button>
								</footer>
								<input type="hidden" name="action" value="products_attributes_list_change" />
								<input type="hidden" name="m_products_attributes_list_id" value="<?= $attr['m_products_attributes_list_id']; ?>" />
							</form>
						</div>
					</div>
				</div>
			</article>
		</div>
	</section>
<?
} else {







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

	$("#m_products_attributes_list_unit").editable({
		inputclass: "input-large",
		select2: {
			tags: [' . implode(',', $units) . '],
			placeholder: "Укажите теги",
			tokenSeparators: [","],
			maximumSelectionSize: 1
		}
	});
	$("#m_products_attributes_list_unit").on("save",function(e,params){
		$("[name=\'m_products_attributes_list_unit\']").val(params.newValue.join("|"));
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


	$(".delete").editable({
		url: "/ajax/services_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete"))
				$(this).parents("tr:first").remove();
		}
	});

	$(".requiredAttr").editable({
    url: "/ajax/edit_attr_required.php",
    type: "text",
    pk: 1,
    name: "m_products_required_attributes_id",
    success: function(response){
        if(response){
            var redirect = $(".requiredAttr").attr("redirect-url");
            document.location.href = redirect;
        }
    },
    error: function(q,w,e){
    }
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
			?>

		<div class="row">

			<article class="col-lg-6 sortable-grid ui-sortable">
				<div class="jarviswidget" id="wid-id-30" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">
					<header>
						<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
						<h2>Добавить атрибут товара</h2>
						<span class="obligatory">* помечены поля, обязательные для заполнения.</span>
					</header>
					<div>
						<div class="widget-body">
							<form id="products-add" class="smart-form" method="post">
								<fieldset>
									<div class="row">
										<section class="col col-6">
											<label class="label">Наименование (название атрибута) <span class="obligatory_elem">*</span></label>
											<label class="input">
												<input type="text" name="m_products_attributes_list_name">
											</label>
										</section>
										<section class="col col-6">
											<label class="label">Наименование для URL (единый указатель для атрибута) <span class="obligatory_elem">*</span></label>
											<label class="input">
												<input type="text" pattern="^[0-9A-Za-z_-]+$" name="m_products_attributes_list_name_url" placeholder="только латиница, цифры, минус и подчеркивание">
											</label>
										</section>
									</div>
									<div class="row">
										<section class="col col-6">
											<label class="label">Тип данных</label>
											<select name="m_products_attributes_list_type" class="autoselect">
												<option value="1" checked>Текстовый</option>
												<option value="2">Числовой</option>
												<option value="3">Логический</option>
											</select>
										</section>
										<section class="col col-6">
											<label class="label">Ед. измерения</label>
												<a href="#" id="m_products_attributes_list_unit" data-type="select2" data-pk="1" data-original-title="Укажите теги"></a>
												<input type="hidden" name="m_products_attributes_list_unit" />
										</section>
									</div>
									<div class="row">
										<section class="col col-4">
											<label class="label">Параметры</label>
											<div class="label_checkbox">
												<input type="checkbox" name="m_products_attributes_list_main" checked="checked" value="1" />
												<i></i>
												Основной
											</div>
											<div class="label_checkbox">
												<input type="checkbox" name="m_products_attributes_list_required" value="0" />
												<i></i>
												Обязателен для заполнения
											</div>
										</section>
										<section class="col col-4">
											<label class="label">&nbsp;</label>
											<div class="label_checkbox">
												<input type="checkbox" name="m_products_attributes_list_site_search" value="1" />
												<i></i>
												Участвует в поиске
											</div>
											<div class="label_checkbox">
												<input type="checkbox" name="m_products_attributes_list_site_filter" value="1" />
												<i></i>
												Участвует в фильтрах
											</div>
										</section>
										<section class="col col-4">
											<label class="label">&nbsp;</label>
											<div class="label_checkbox">
												<input type="checkbox" name="m_products_attributes_list_site_open" value="1" />
												<i></i>
												Раскрыт по умолчанию
											</div>
											<div class="label_checkbox">
												<input type="checkbox" name="m_products_attributes_list_active" value="1" />
												<span class="input_name">Активен</span>
											</div>
										</section>
									</div>
									<section>
										<label class="label">Комментарий</label>
										<label class="textarea textarea-resizable">
											<textarea name="m_products_attributes_list_comment" rows="2" class="custom-scroll" placeholder="Для внутреннего использования"></textarea>
										</label>
									</section>
									<section>
										<label class="label">Подсказка для сайта</label>
										<label class="textarea textarea-resizable">
											<textarea name="m_products_attributes_list_hint" rows="5" class="custom-scroll" placeholder="Детальное описание атрибута"></textarea>
										</label>
									</section>
								</fieldset>
								<footer>
									<button type="submit" class="btn btn-primary">
										<i class="fa fa-save"></i>
										Сохранить данные
									</button>
								</footer>
								<input type="hidden" name="action" value="products_attributes_list_add" />
							</form>
						</div>
					</div>
				</div>
			</article>

			<?
				if ($products->attr_id) {
					?>
				<article class="col-lg-12">

					<div class="jarviswidget jarviswidget-color-blue" id="wid-id-1" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-togglebutton="false">

						<header>
							<span class="widget-icon"> <i class="fa fa-table"></i> </span>
							<h2>Список атрибутов</h2>
						</header>

						<div>

							<div class="widget-body no-padding">

								<table class="datatable table table-striped table-bordered table-hover" width="100%">

									<thead>
										<tr>
											<th style="width:18%">Наименование</th>
											<th style="width:5%">Ед.&nbsp;изм.</th>
											<th style="width:8%">Тип данных</th>
											<th style="width:10%">Подсказка для покупателя</th>
											<th style="width:11%">Подсказка для контент-менеджера</th>
											<th style="width:42%">Параметры</th>
											<th style="width:10%">В группах атрибутов</th>
											<th style="width:7%">Управление</th>
										</tr>
									</thead>

									<tbody>
										<?
												$i = 0;

												foreach ($products->attr_id as $_attr) {
													$_attr = $_attr[0];

													echo '<tr>
		<td>',
														$_attr['m_products_attributes_list_name'] . '<br/><span style="color:#999;font-style:italic;">' . $_attr['m_products_attributes_list_id'] . '</span>',
														'</td>
        <td>',
														$_attr['m_products_attributes_list_unit'],
														'</td>
		<td>';
													switch ($_attr['m_products_attributes_list_type']) {
														case 1:
															echo 'Текстовый';
															break;
														case 2:
															echo 'Числовой';
															break;
														case 3:
															echo 'Логический';
															break;
													}
													echo '</td>

		<td>',
														$_attr['m_products_attributes_list_comment'],
														'</td>
		<td>',
														transform::some($_attr['m_products_attributes_list_hint'], 10),
														'</td>
		<td>
			<label class="checkbox">
			  <input type="checkbox" class="checkbox style-0 show" data-name="m_products_attributes_list_main" ' . ($_attr['m_products_attributes_list_main'] ? 'checked' : '') . ' data-pk="' . $_attr['m_products_attributes_list_id'] . '">
			  <span>Основной</span>
			</label>
			<label class="checkbox">
			  <input type="checkbox" class="checkbox style-0 show" data-name="m_products_attributes_list_required" ' . ($_attr['m_products_attributes_list_required'] ? 'checked' : '') . ' data-pk="' . $_attr['m_products_attributes_list_id'] . '">
			  <span>Обязательный</span>
			</label>
			<label class="checkbox">
			  <input type="checkbox" class="checkbox style-0 show" data-name="m_products_attributes_list_site_search" ' . ($_attr['m_products_attributes_list_site_search'] ? 'checked' : '') . ' data-pk="' . $_attr['m_products_attributes_list_id'] . '">
			  <span>В поиске</span>
			</label>
			<label class="checkbox">
			  <input type="checkbox" class="checkbox style-0 show" data-name="m_products_attributes_list_site_filter" ' . ($_attr['m_products_attributes_list_site_filter'] ? 'checked' : '') . ' data-pk="' . $_attr['m_products_attributes_list_id'] . '">
			  <span>В фильтрах</span>
			</label>
			<label class="checkbox">
			  <input type="checkbox" class="checkbox style-0 show" data-name="m_products_attributes_list_site_open" ' . ($_attr['m_products_attributes_list_site_open'] ? 'checked' : '') . ' data-pk="' . $_attr['m_products_attributes_list_id'] . '">
			  <span>Раскрыт</span>
			</label>
			<label class="checkbox">
			  <input type="checkbox" class="checkbox style-0 show" data-name="m_products_attributes_list_active" ' . ($_attr['m_products_attributes_list_active'] ? 'checked' : '') . ' data-pk="' . $_attr['m_products_attributes_list_id'] . '">
			  <span>Активен</span>
			</label>
		</td>';
		echo '<td>';
			if (isset($attrList[$_attr['m_products_attributes_list_id']])) {
				foreach ($attrList[$_attr['m_products_attributes_list_id']] as $attrsListId) {
					echo '<p>'.$attrsListId.'</p>';
				}
			} else {
				echo '-';
			}
    echo '</td>';
		echo '<td>
		';

													if (in_array($_attr['m_products_attributes_list_id'], $attrs_id_required_array)) {

														echo '<a href="javascript:void(0);" redirect-url="' . url() . '?action=change&m_products_attributes_list_id=' . $_attr['m_products_attributes_list_id'] . '" title="Редактировать" class="btn btn-primary btn-xs btn-default change requiredAttr" data-type="text" data-placement="left">
				<i class="fa fa-pencil"></i>
			</a>';
													} else {

														echo '<a href="' . url() . '?action=change&m_products_attributes_list_id=' . $_attr['m_products_attributes_list_id'] . '" title="Редактировать" class="btn btn-primary btn-xs btn-default change" data-type="text">
				<i class="fa fa-pencil"></i>
			</a>';
													}

													echo '<a href="javascript:void(0);" title="Удалить" class="btn btn-xs btn-danger delete" data-type="text" data-pk="' . $_attr['m_products_attributes_list_id'] . '" data-name="m_products_attributes_list_id" data-title="Введите пароль для удаления записи" data-placement="left">
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
<? } ?>