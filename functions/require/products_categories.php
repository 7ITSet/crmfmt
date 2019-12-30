<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$products;
$content->setJS('
	runAllForms();
	
	$("#clients-personal-add").validate({
		rules : {
			m_products_categories_name : {
				maxlength : 180,
				required : true
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});
	
	$("#categories-list").nestable().on("change",function(){
		$.post(
			"/ajax/services_change.php",
			{
				name:"products_list",
				pk:"0000000000",
				value:JSON.stringify($(this).nestable("serialize"))
			}
		);
	});
	//editables
	$("#categories-list a.editable").editable({
		url: "/ajax/services_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete"))
				$(this).parents("li:first").remove();
		}
	});
	$("input.show").on("change",function(){
		if($(this).data("name")=="m_products_categories_show_goods"&&!$(this).prop("checked"))
			$(this).parents("li:first").find("input[data-name=m_products_categories_show_attributes]:first").prop("checked",false).prop("disabled",true).triggerHandler("change");
		else{
			if($(this).prop("checked"))
				$(this).parents("li:first").find("input[data-name=m_products_categories_show_attributes]:first").prop("disabled",false);
		}
		$.post(
			"/ajax/services_change.php",
			{
				name:$(this).attr("data-name"),
				pk:$(this).attr("data-pk"),
				value:$(this).prop("checked")
			}
		);
	});
	$("input[name=m_products_categories_show_goods],input[name=m_products_categories_show_categories]").on("change",function(){
		if(!$(this).prop("checked"))
			$("input[name=m_products_categories_show_attributes]").prop("checked",false).prop("disabled",true);
		else{
			$("input[name=m_products_categories_show_attributes]").prop("disabled",false);
		}
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
	
	<div class="row cat_row">
	
<?
if($products->categories_nodes){
?>
		<article class="col-lg-8">
			<div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">		
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Существующие категории</h2>
				</header>
				<div class="widget-body">
					<div class="col-lg-12">
						<div class="dd" id="categories-list">
							<?$products->categories_display_li()?>
						</div>
					</div>
				</div>
                <footer class="save_cat">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i>
                        Сохранить данные
                    </button>
                </footer>
			</div>
		</article>
<?
}
?>



		<article class="col-lg-4 sortable-grid ui-sortable">
			<div class="jarviswidget" id="wid-id-1" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">		
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Добавить категорию</h2>
                    <span class="obligatory" style="float: none; width: 100%;">* помечены поля, обязательные для заполнения.</span>
				</header>
				<div>
					<div class="widget-body">
						<form id="clients-company-add" class="smart-form" method="post">
							<header>
								Основные данные
							</header>
							<fieldset>
								<section>
									<label class="label">Наименование (название категории) <span class="obligatory_elem">*</span></label>
									<label class="input">
										<input type="text" name="m_products_categories_name">
									</label>
								</section>
								<section>
									<label class="label">Родительская категория</label>
									<label class="input">
										<select name="m_products_categories_parent" style="width:100%" class="autoselect">
											<option value="0">—</option>
											<? 
												$categories=array();
												$products->categories_childs(0,$categories,1);
												foreach($categories as $categories_){
													echo '<option value="'.$categories_['id'].'">
															'.$categories_['m_products_categories_name'].'
														</option>';																
												}
											?>
										</select>
									</label>
								</section>
							</fieldset>
							<header>
								Дополнительные опции
							</header>
							<fieldset>
								<section>
								  <label class="checkbox">
									<input type="checkbox" name="m_products_categories_show_attributes" checked="checked" value="1"/>
									<i></i>
									Показывать фильтры
								  </label>
								  <label class="checkbox" title="Если снять галку — в категории будут показаны дочерние подкатегории (для больших категорий)&#010;Если поставить галку — в категории будут показаны все товары категории и покатегорий с фильтрами атрибутов">
									<input type="checkbox" name="m_products_categories_show_goods" checked="checked" value="1"/>
									<i></i>
									Показывать товары
								  </label>
								   <label class="checkbox" title="Категории будут показаны блоками с картинками">
									<input type="checkbox" name="m_products_categories_show_categories" checked="checked" value="1"/>
									<i></i>
									Категории картинками
								  </label>
								</section>
							</fieldset>
							<footer>
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-save"></i>
									Сохранить данные
								</button>
							</footer>
							<input type="hidden" name="action" value="products_categories_add"/>
						</form>
					</div>
				</div>
			</div>	
		</article>
	</div>
</section>
<script src="/js/plugin/jquery-nestable/jquery.nestable.min.js"></script>