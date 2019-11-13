<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$info,$site,$products;
if(get('action')=='details'){
$item_menu=$site->menu_id[get('menu_id')][0];
$items_content=$site->content_id[get('menu_id')];
$city=get('city')?get('city'):'www';
foreach($items_content as $_item){
	$item_content=$_item;
	if($_item['city']==$city){
		$item_content=$_item;
		break;
	}
}
$content->setJS('

	runAllForms();

		/* СМЕНА ГОРОДА */
		$("[name=\'city\']").on("change",function(){
			window.location="http://'.$_SERVER['HTTP_HOST'].'/site/?action=details&menu_id='.get('menu_id').'&city="+$("[name=\'city\'] option:selected").val();
		});

		$("#site-page-add").validate({
		rules : {
			menu_name : {
				maxlength : 80,
				required : true
			},
			menu_url : {
				maxlength : 60,
				remote: {
					url: "/ajax/site_check_url.php",
					type: "get",
					data: {
						url: function() {
							return $( "input[name=menu_url]").val();
						},
						parent: function() {
							return $( "select[name=menu_parent]").val();
						},
						id:'.$item_menu['id'].'
					}
				}
			},
			menu_parent : {
				required : true,
				remote: {
					url: "/ajax/site_check_url.php",
					type: "get",
					data: {
						url: function() {
							return $( "input[name=menu_url]").val();
						},
						parent: function() {
							return $( "select[name=menu_parent]").val();
						},
						id:'.$item_menu['id'].'
					}
				}
			},
			menu_order : {
				maxlength : 2,
				digits: true
			},
			menu_type : {
				maxlength : 80,
				required : true
			},
			page_title : {
				maxlength : 130,
			},
			page_h1 : {
				maxlength : 130,
			},
			page_description : {
				maxlength : 180
			},
			page_keywords : {
				maxlength : 280
			},
			page_content : {
			},
		},
		messages:{
			menu_url:{
				remote: "этот URL уже есть в категории"
			},
			menu_parent:{
				remote: "URL уже есть в этой категории"
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});


	tinymce.init({
		selector: "#page_content",
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
		height: "500",
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
				<h2>Изменение страницы</h2>
                <span class="obligatory">* помечены поля, обязательные для заполнения.</span>
			</header>
			<div>
				<div class="widget-body">
					<form id="site-page-add" class="smart-form" method="post">
						<header>
							Пункт меню
						</header>
						<fieldset>
							<div class="row">
								<section class="col col-4">
									<label class="label">Название страницы (отображается в навигационной цепочке и боковом меню) <span class="obligatory_elem">*</span></label>
									<label class="input">
										<input type="text" name="menu_name" value="<?=$item_menu['name']?>">
									</label>
								</section>
								<section class="col col-4">
									<label class="label">URL (указатель страницы, допускаются латинские буквы, цифры, символы, без пробелов) <span class="obligatory_elem">*</span></label>
									<label class="input">
										<input type="text" pattern="^[A-Za-z_-]+$" name="menu_url" value="<?=$item_menu['url']?>">
									</label>
								</section>
								<section class="col col-4">
									<label class="label">Родительская ссылка (страница, которая будет родительской для добавляемой страницы)</label>
									<label class="input">
										<select name="menu_parent" style="width:100%" class="autoselect">
											<option value="0">—</option>
											<?
												$categories=array();
												$site->categories_childs(0,$categories,1);
												foreach($categories as $categories_){
													echo '<option value="'.$categories_['id'].'"'.($categories_['id']==$item_menu['parent']?' selected':'').'>
															'.$categories_['name'].'
														</option>';
												}
											?>
										</select>
									</label>
								</section>
							</div>
							<div class="row">
								<section class="col col-4">
									<label class="label">Город</label>
									<label class="input">
										<select name="city" style="width:100%" class="autoselect">
											<?
												global $area_list;
												foreach($area_list as $_domain=>$_area){
													echo '<option value="'.$_domain.'" '.($city==$_domain?' selected ':'').'>
															'.$_area['ГОРОД'].'
														</option>';
												}
											?>
										</select>
									</label>
								</section>
								<section class="col col-2">
									<label class="label">Порядок (очередность вывода пункта меню)</label>
									<label class="input">
										<input type="text" name="menu_order" value="<?=$item_menu['order']?>">
									</label>
								</section>
								<section class="col col-2">
									<label class="label">Тип (где будет показываться пункт меню, значение стоит по умолчанию) <span class="obligatory_elem">*</span></label>
									<label class="input">
										<input type="text" name="menu_type"  value="<?=$item_menu['type']?>">
									</label>
								</section>
								<section class="col col-3">
									<label class="label">&nbsp;</label>
									<label class="checkbox">
									<input type="checkbox" name="menu_active" value="1" <?=$item_menu['active']?' checked':''?> >
									<i></i>
										Пункт меню активен
									</label>
								</section>
							</div>
							<section>
								<label class="label">Категория товаров (все товары из выбранной категории будут отображены на странице, а также ссылкой на страницу будет являтся выбранная категория товаров)</label>
								<label class="input">
									<select name="menu_category" style="width:100%" class="autoselect">
										<option value="0">—</option>
										<?
											$categories=array();
											$products->categories_childs(0,$categories,1);
											foreach($categories as $categories_){
												echo '<option value="'.$categories_['m_products_categories_id'].'" '.($item_menu['category']==$categories_['m_products_categories_id']?'selected':'').'>
														'.$categories_['m_products_categories_name'].'
													</option>';
											}
										?>
									</select>
								</label>
							</section>
							<section>
								<label class="label">Строка фильтра</label>
								<label class="textarea textarea-resizable">
									<textarea name="page_keywords" rows="8" class="custom-scroll" placeholder="параметр=значение, разделение построчно"><?=$item_menu['filters']?></textarea>
								</label>
							</section>
						</fieldset>
						<header>
							Страница
						</header>
						<fieldset>
							<section>
								<label class="label">Заголовок <b>&lt;title&gt;</b> (отображается в качетстве названия вкладки) <span class="obligatory_elem">*</span></label>
								<label class="input">
									<input type="text" name="page_title" value="<?=$item_content['title']?>">
								</label>
							</section>
							<section>
								<label class="label">Заголовок <b>&lt;h1&gt;</b>  (заголовок страницы, отображается в начале) <span class="obligatory_elem">*</span></label>
								<label class="input">
									<input type="text" name="page_h1" value="<?=$item_content['h1']?>">
								</label>
							</section>
							<section>
								<label class="label">Описание <b>&lt;meta name=description&gt;</b> (для поисковых систем)</label>
								<label class="textarea textarea-resizable">
									<textarea name="page_description" rows="3" class="custom-scroll"><?=$item_content['description']?></textarea>
								</label>
							</section>
							<section>
								<label class="label">Ключевые слова <b>&lt;meta name=keywords&gt;</b> (для посиковых систем)</label>
								<label class="textarea textarea-resizable">
									<textarea name="page_keywords" rows="8" class="custom-scroll"><?=implode("\r\n",explode(', ',$item_content['keywords']))?></textarea>
								</label>
							</section>
							<section>
								<label class="label">Контент (содержимое страницы)</label>
								<label class="textarea textarea-resizable">
									<textarea class="custom-scroll" rows="50" name="page_content" id="page_content"><?=$item_content['content']?></textarea>
								</label>
							</section>
						</fieldset>
						<footer>
							<button type="submit" class="btn btn-primary">
								<i class="fa fa-save"></i>
								Сохранить изменения
							</button>
						</footer>
						<input type="hidden" name="action" value="site_page_change"/>
						<input type="hidden" name="menu_id" value="<?=$item_menu['id']?>"/>
						<input type="hidden" name="content_id" value="<?=$item_content['id']?>"/>
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

	$("#site-page-add").validate({
		rules : {
			menu_name : {
				maxlength : 80,
				required : true
			},
			menu_url : {
				maxlength : 60,
				required : true,
				remote: {
					url: "/ajax/site_check_url.php",
					type: "get",
					data: {
						url: function() {
							return $( "input[name=menu_url]").val();
						},
						parent: function() {
							return $( "select[name=menu_parent]").val();
						}
					}
				}
			},
			menu_parent : {
				required : true,
				remote: {
					url: "/ajax/site_check_url.php",
					type: "get",
					data: {
						url: function() {
							return $( "input[name=menu_url]").val();
						},
						parent: function() {
							return $( "select[name=menu_parent]").val();
						}
					}
				}
			},
			menu_order : {
				maxlength : 2,
				digits: true
			},
			menu_type : {
				maxlength : 80,
				required : true
			},
			page_title : {
				maxlength : 130,
			},
			page_h1 : {
				maxlength : 130,
			},
			page_description : {
				maxlength : 180
			},
			page_keywords : {
				maxlength : 280
			},
			page_content : {
			},
		},
		messages:{
			menu_url:{
				remote: "этот URL уже есть в категории"
			},
			menu_parent:{
				remote: "URL уже есть в этой категории"
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});


	tinymce.init({
		selector: "#page_content",
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
		height: "500",
		relative_urls : true,
		/* remove_script_host : false,
		convert_urls : false, */
		verify_html : false,
		fontsize_formats: "6pt 7pt 8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 17pt 18pt 19pt 20pt 21pt 22pt 23pt 24pt 25pt 26pt 27pt 28pt 29pt 30pt 31pt 32pt 33pt 34pt 35pt 36pt",
	});


	//editables
	$("a.editable.delete").editable({
		url: "/ajax/site_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete"))
				$(this).parents("li:first").remove();
		}
	});

	$("input.show").on("change",function(){
		$.post(
			"/ajax/site_change.php",
			{
				name:$(this).attr("data-name"),
				pk:$(this).attr("data-pk"),
				value:$(this).prop("checked")
			}
		);
	});

	$("#menu-list").nestable().on("change",function(){
		$.post(
			"/ajax/site_change.php",
			{
				name:"list",
				pk:"0",
				value:JSON.stringify($(this).nestable("serialize"))
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
					<h2>Новая страница</h2>
                    <span class="obligatory">* помечены поля, обязательные для заполнения.</span>
				</header>

			<!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body">
						<form id="site-page-add" class="smart-form" method="post">
							<header>
								Пункт меню
							</header>
							<fieldset>
								<div class="row">
									<section class="col col-4">
										<label class="label">Название страницы (отображается в навигационной цепочке и боковом меню) <span class="obligatory_elem">*</span></label>
										<label class="input">
											<input type="text" name="menu_name">
										</label>
									</section>
									<section class="col col-4">
										<label class="label">URL (указатель страницы) <span class="obligatory_elem">*</span></label>
										<label class="input">
											<input type="text" name="menu_url" pattern="^[0-9A-Za-z_-]+$" placeholder="только латиница, цифры, минус и подчеркивание">
										</label>
									</section>
									<section class="col col-4">
										<label class="label">Родительская ссылка (страница, которая будет родительской для добавляемой страницы)</label>
										<label class="input">
											<select name="menu_parent" style="width:100%" class="autoselect">
												<option value="0">—</option>
												<?
													$categories=array();
													$site->categories_childs(0,$categories,1);
													foreach($categories as $categories_){
														echo '<option value="'.$categories_['id'].'">
																'.$categories_['name'].'
															</option>';
													}
												?>
											</select>
										</label>
									</section>
								</div>
								<div class="row">
									<section class="col col-2">
										<label class="label">Порядок (очередность вывода пункта меню)</label>
										<label class="input">
											<input type="text" name="menu_order">
										</label>
									</section>
									<section class="col col-2">
										<label class="label">Тип (где будет показываться пункт меню, значение стоит по умолчанию) <span class="obligatory_elem">*</span></label>
										<label class="input">
											<input type="text" name="menu_type" value="top|top-catalog">
										</label>
									</section>
									<section class="col col-3">
										<label class="label">&nbsp;</label>
										<label class="checkbox">
										<input type="checkbox" name="menu_active" checked="checked">
										<i></i>
											Пункт меню активен
										</label>
									</section>
								</div>
								<section>
									<label class="label">Категория товаров (все товары из выбранной категории будут отображены на странице, а также ссылкой на страницу будет являтся выбранная категория товаров)</label>
									<label class="input">
										<select name="menu_category" style="width:100%" class="autoselect">
											<option value="0">—</option>
											<?
												$categories=array();
												$products->categories_childs(0,$categories,1);
												foreach($categories as $categories_){
													echo '<option value="'.$categories_['m_products_categories_id'].'">
															'.$categories_['m_products_categories_name'].'
														</option>';
												}
											?>
										</select>
									</label>
								</section>
								<section>
									<label class="label">Строка фильтра</label>
									<label class="textarea textarea-resizable">
										<textarea name="page_keywords" rows="8" class="custom-scroll" placeholder="параметр=значение, разделение построчно"></textarea>
									</label>
								</section>
							</fieldset>
							<header>
								Страница
							</header>
							<fieldset>
								<section>
									<label class="label">Заголовок <b>&lt;title&gt;</b> (отображается в качетстве названия вкладки) <span class="obligatory_elem">*</span></label>
									<label class="input">
										<input type="text" name="page_title">
									</label>
								</section>
								<section>
									<label class="label">Заголовок <b>&lt;h1&gt;</b>  (заголовок страницы, отображается в начале) <span class="obligatory_elem">*</span></label>
									<label class="input">
										<input type="text" name="page_h1">
									</label>
								</section>
								<section>
									<label class="label">Описание <b>&lt;meta name=description&gt;</b> (для поисковых систем)</label>
									<label class="textarea textarea-resizable">
										<textarea name="page_description" rows="3" class="custom-scroll"></textarea>
									</label>
								</section>
								<section>
									<label class="label">Ключевые слова <b>&lt;meta name=keywords&gt;</b> (для поисковых систем)</label>
									<label class="textarea textarea-resizable">
										<textarea name="page_keywords" rows="8" class="custom-scroll"></textarea>
									</label>
								</section>
								<section>
									<label class="label">Контент (содержимое страницы)</label>
									<label class="textarea textarea-resizable">
										<textarea class="custom-scroll" rows="50" name="page_content" id="page_content"></textarea>
									</label>
								</section>
							</fieldset>
							<footer>
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-save"></i>
									Добавить страницу
								</button>
							</footer>
							<input type="hidden" name="action" value="site_page_add"/>
						</form>
					</div>
				</div>
			</div>
		</article>
<?
if($site->content_id){
?>
		<article class="col-lg-6">
			<div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Существующие страницы</h2>
				</header>
				<div class="widget-body">
					<form class="smart-form">
						<div class="col-lg-12">
							<div class="dd" id="menu-list">
								<?$site->categories_display_li()?>
							</div>
						</div>
						<footer>
							<button type="submit" class="btn btn-primary">
								<i class="fa fa-save"></i>
								Сохранить изменения
							</button>
						</footer>
					</div>
				</form>
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
<script src="/js/plugin/tinymce/tinymce.min.js"></script>
<script src="/js/plugin/jquery-nestable/jquery.nestable.min.js"></script>
