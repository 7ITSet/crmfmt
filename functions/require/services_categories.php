<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$services;
$content->setJS('
	runAllForms();
	
	$("#clients-personal-add").validate({
		rules : {
			m_services_categories_name : {
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
				name:"list",
				pk:"0000000000",
				value:JSON.stringify($(this).nestable("serialize"))
			}
		);
	});

	$("#categories-list a.editable").editable({
		url: "/ajax/services_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete")) 1;
				$(this).parents("li:first").remove();
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
if($contragents->getInfo()){
?>
		<article class="col-lg-6">
			<div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">		
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Существующие категории</h2>
				</header>
				<div class="widget-body">
					<div class="col-lg-12">
						<div class="dd" id="categories-list">
							<?$services->categories_display_li()?>
						</div>
					</div>
				</div>
			</div>
		</article>
<?
}
?>

		<article class="col-lg-6 sortable-grid ui-sortable">
			<div class="jarviswidget" id="wid-id-1" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">		
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Добавить категорию</h2>
				</header>
				<div>
					<div class="widget-body">
						<form id="clients-company-add" class="smart-form" method="post">
							<header>
								Основные данные
							</header>
							<fieldset>
								<div class="row">
									<section class="col col-4">
										<label class="label">Наименование</label>
										<label class="input">
											<input type="text" name="m_services_categories_name">
										</label>
									</section>
									<section class="col col-8">
										<label class="label">Родительская категория</label>
										<label class="input">
											<select name="m_services_categories_parent" style="width:100%" class="autoselect">
												<option value="0">—</option>
												<? 
													$categories=array();
													$services->categories_childs(0,$categories,1);
													foreach($categories as $categories_){
														echo '<option value="'.$categories_['m_services_categories_id'].'">
																'.$categories_['m_services_categories_name'].'
															</option>';																
													}
												?>
											</select>
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
										<input type="checkbox" name="m_services_categories_show_site" checked="checked" value="1"/>
										<i></i>
										Показывать на сайте
									  </label>
									  <label class="checkbox">
										<input type="checkbox" name="m_services_categories_show_price" checked="checked" value="1"/>
										<i></i>
										Выгружать в прайс
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
							<input type="hidden" name="action" value="categories_add"/>
						</form>
					</div>
				</div>
			</div>	
		</article>
	</div>
</section>
<script src="/js/plugin/jquery-nestable/jquery.nestable.min.js"></script>