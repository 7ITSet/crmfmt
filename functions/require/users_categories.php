<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$menu;

$menus_select='';
$menus=array();
$menu->childs(0,$menus,2);
foreach($menus as $menus_)
	if(!$menus_['m_menu_no_link'])
		$menus_select.='<option value="'.$menus_['m_menu_id'].'">'.$menus_['m_menu_name'].'</option>';

$content->setJS('
	
	runAllForms();
		
	$("#groups-add").validate({
		rules : {
			m_users_groups_name : {
				maxlength : 80,
				required : true
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});
	
	$(".m_users_groups_name,.delete").editable({
		url: "/ajax/services_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete"))
				$(this).parents("tr:first").remove();
		}
	});
	

	$(".m_users_groups").each(function(index,el){
		if($(el).attr("data-value")){
			var selected=$(el).attr("data-value").split("|");
			for(var i=0;i<selected.length;i++){
				var sel=selected[i]*1;
				if(sel)
					$(el).find("option[value="+sel+"]").prop("selected", "selected");
			}
		}
	});
	$(".m_users_groups").on("change",function(){
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
if($groups=$user->getGroups()){
?>
		<article class="col-lg-12">
	
			<div class="jarviswidget jarviswidget-color-blue" id="wid-id-1" 
				data-widget-editbutton="false"
				data-widget-colorbutton="false"
				data-widget-deletebutton="false"
				data-widget-togglebutton="false">

				<header>
					<span class="widget-icon"> <i class="fa fa-table"></i> </span>
					<h2>Группы пользователей</h2>
				</header>

				<div>				

					<div class="widget-body no-padding">

						<table class="users_groups datatable table table-striped table-bordered table-hover" width="100%">
	
							<thead>
								<tr>
									<th style="width:10%">Название</th>
									<th style="width:17%">Чтение</th>
									<th style="width:17%">Изменение</th>
									<th style="width:17%">Удаление</th>
									<th style="width:17%">Создание</th>
									<th style="width:17%">Только свои данные</th>
									<th style="width:5%"></th>
								</tr>
							</thead>
							<tbody>
								<?
foreach($groups as $groups_){
	$groups_=$groups_[0];
	echo '<tr>
		<td>
			<a href="#" class="m_users_groups_name" data-pk="'.$groups_['m_users_groups_id'].'" data-name="m_users_groups_name" data-select-search="true" data-value="'.$groups_['m_users_groups_name'].'" data-original-title="Название группы">'.$groups_['m_users_groups_name'].'</a>
		</td>
		<td>
			<select data-name="m_users_groups_rights_read" style="width:100%" multiple class="m_users_groups autoselect" placeholder="выберите из списка..." data-pk="'.$groups_['m_users_groups_id'].'" data-value="'.$groups_['m_users_groups_rights_read'].'">',
				$menus_select,
			'</select>
		</td>
		<td>
			<select data-name="m_users_groups_rights_change" style="width:100%" multiple class="m_users_groups autoselect" placeholder="выберите из списка..." data-pk="'.$groups_['m_users_groups_id'].'" data-value="'.$groups_['m_users_groups_rights_change'].'">',
				$menus_select,
			'</select>
		</td>
		<td>
			<select data-name="m_users_groups_rights_delete" style="width:100%" multiple class="m_users_groups autoselect" placeholder="выберите из списка..." data-pk="'.$groups_['m_users_groups_id'].'" data-value="'.$groups_['m_users_groups_rights_delete'].'">',
				$menus_select,
			'</select>
		</td>
		<td>
			<select data-name="m_users_groups_rights_create" style="width:100%" multiple class="m_users_groups autoselect" placeholder="выберите из списка..." data-pk="'.$groups_['m_users_groups_id'].'" data-value="'.$groups_['m_users_groups_rights_create'].'">',
				$menus_select,
			'</select>
		</td>
		<td>
			<select data-name="m_users_groups_rights_myself" style="width:100%" multiple class="m_users_groups autoselect" placeholder="выберите из списка..." data-pk="'.$groups_['m_users_groups_id'].'" data-value="'.$groups_['m_users_groups_rights_myself'].'">',
				$menus_select,
			'</select>
		</td>
		<td>
			<a href="javascript:void(0);" class="btn btn-xs btn-default delete" data-type="text" data-pk="'.$groups_['m_users_groups_id'].'" data-name="m_users_groups_id" data-title="Введите пароль для удаления записи" data-placement="left">
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
					<h2>Добавить группу пользователей</h2>
				</header>
				<div>
					<div class="widget-body">
						<form id="groups-add" class="smart-form" method="post">
							<header>
								Основные данные
							</header>
							<fieldset>
								<div class="row">
									<section class="col col-6">
										<label class="label">Название</label>
										<label class="input">
											<input type="text" name="m_users_groups_name">
										</label>
									</section>
								</div>
							</fieldset>
							<header>
								Разрешения
							</header>
							<fieldset>
								<section>
									<label class="label">Чтение</label>
									<select name="m_users_groups_rights_read[]" style="width:100%" multiple class="autoselect" placeholder="выберите меню из списка...">
										<?=$menus_select?>
									</select>
								</section>
								<section>
									<label class="label">Изменение</label>
									<select name="m_users_groups_rights_change[]" style="width:100%" multiple class="autoselect" placeholder="выберите меню из списка...">
										<?=$menus_select?>
									</select>
								</section>
								<section>
									<label class="label">Удаление</label>
									<select name="m_users_groups_rights_delete[]" style="width:100%" multiple class="autoselect" placeholder="выберите меню из списка...">
										<?=$menus_select?>
									</select>
								</section>
								<section>
									<label class="label">Создание</label>
									<select name="m_users_groups_rights_create[]" style="width:100%" multiple class="autoselect" placeholder="выберите меню из списка...">
										<?=$menus_select?>
									</select>
								</section>
								<section>
									<label class="label">Только свои данные</label>
									<select name="m_users_groups_rights_myself[]" style="width:100%" multiple class="autoselect" placeholder="выберите меню из списка...">
										<?=$menus_select?>
									</select>
								</section>
							</fieldset>	
							<footer>
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-save"></i>
									Сохранить данные
								</button>
							</footer>
							<input type="hidden" name="action" value="m_users_groups_add"/>
						</form>
					</div>
				</div>
			</div>	
		</article>
	</div>
</section>