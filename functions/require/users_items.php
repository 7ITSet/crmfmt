<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$services,$workers;

$workers=new workers;

//список пользователей
$users=$user->getUsers();

//список групп пользователей
$groups=$user->getGroups();

//список организаций
$groups_editable='[';
foreach($groups as $groups_)
	$groups_editable.='{value:'.$groups_[0]['m_users_groups_id'].',text:"'.str_replace('&quot;','',$groups_[0]['m_users_groups_name']).'"},';
$groups_editable.=']';

$content->setJS('
	
	runAllForms();
		
	$("#users-add").validate({
		rules : {
			m_users_login : {
				email: true,
				maxlength : 64,
				remote: {
					url: "/ajax/clients_check_email.php",
					data: {
						m_users_login: function() {
							return $( "input[name=m_users_login]" ).val();
						}
					}
				}
			},
			m_users_password : {
				maxlength: 20,
				minlength: 10,
				required: true
			},
			m_users_group : {
				required : true
			}
		},
		messages:{
			m_users_login:{
				remote: "Уже занятый e-mail"
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});	
	

	$(".m_users_group").editable({
		source: '.$groups_editable.',
		select2: {
			width: 200
		},
		url: "/ajax/services_change.php"
	});
	
	$(".m_services_password,.m_users_contragents_id,.delete").editable({
		url: "/ajax/services_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete"))
				$(this).parents("tr:first").remove();
		}
	});
	
	$(".pass_new").on("click",function(){
		var code=Math.floor(Math.random()*(900000))+100000,
			tcode=prompt("Введите "+code+" для подтверждения смены пароля\r\nНовый пароль будет отправлен пользователю на e-mail"),
			arr="QWXZASERGDFCVTYHBNUOPILMKJplmkoiujytgnhbvcrdfeqwsazx7896523014",pass="";;
		if (tcode==code){
			for(var i=0;i<10;i++){
				pass+=arr.charAt(rand(0,61));
			}
			$.post(
				"/ajax/services_change.php",
				{
					name:$(this).attr("data-name"),
					pk:$(this).attr("data-pk"),
					value:pass
				}
			);
		}
		else 
			return false;
	});
	
	$("input.active").on("change",function(){
		$.post(
			"/ajax/services_change.php",
			{
				name:$(this).attr("data-name"),
				pk:$(this).attr("data-pk"),
				value:$(this).prop("checked")
			}
		);
	});
	
	function rand(min,max){
		return Math.floor(Math.random()*(max-min+1))+min;
	}
	$("#pass_gen").on("click",function(){
		var arr="QWXZASERGDFCVTYHBNUOPILMKJplmkoiujytgnhbvcrdfeqwsazx7896523014",pass="";
		for(var i=0;i<10;i++){
			pass+=arr.charAt(rand(0,61));
		}
		$("input[name=\'m_users_password\']").val(pass);
		
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
if($users){
?>
		<article class="col-lg-6">
			<div class="jarviswidget jarviswidget-color-blue" id="wid-id-1" 
				data-widget-editbutton="false"
				data-widget-colorbutton="false"
				data-widget-deletebutton="false"
				data-widget-togglebutton="false">
				<header>
					<span class="widget-icon"> <i class="fa fa-table"></i> </span>
					<h2>Пользователи</h2>
				</header>
				<div>
					<div class="widget-body no-padding">
						<table class="datatable table table-striped table-bordered table-hover" width="100%">
							<thead>
								<tr>
									<th style="width:25%">Группа</th>
									<th style="width:40%">Логин</th>
									<th style="width:15%">Пароль</th>
									<th style="width:15%">Активность</th>
									<th style="width:5%"></th>
								</tr>
							</thead>
							<tbody>
								<?
foreach($users as $users_){
	$users_=$users_[0];
	$name=$contragents->getName($users_['m_users_id'])?$contragents->getName($users_['m_users_id']):(!empty($workers->getInfo($users_['m_users_id'])['m_employees_fio'])?$workers->getInfo($users_['m_users_id'])['m_employees_fio']:'');
	echo '<tr>
		<td>
			<a href="#" class="m_users_group" data-type="select2" data-pk="'.$users_['m_users_id'].'" data-name="m_users_group" data-select-search="true" data-value="'.$users_['m_users_group'].'" data-original-title="Группа"></a>
		</td>
		<td>',		
				$users_['m_users_login'],
				'<br/><span style="color:#aaa">',
				$name,
				'</span>',				
		'</td>
		<td>
			<a href="javascript:void(0);" class="btn btn-labeled btn-info pass_new" data-name="m_users_password" data-pk="'.$users_['m_users_id'].'" >
				<span class="btn-label">
					<i class="glyphicon glyphicon-refresh"></i>
				</span>
				Сменить пароль
			</a>
		</td>
		<td>
			<label class="checkbox">
			  <input type="checkbox" class="checkbox style-0 active" data-name="m_users_active" '.($users_['m_users_active']?'checked':'').' data-pk="'.$users_['m_users_id'].'">
			  <span>Активен</span>
			</label>
		</td>
		<td>
			<a href="javascript:void(0);" class="btn btn-xs btn-danger delete" data-type="text" data-pk="'.$users_['m_users_id'].'" data-name="m_users_id" data-title="Введите пароль для удаления записи" data-placement="left">
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
			<div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">		
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Добавить пользователя</h2>
				</header>
				<div>
					<div class="widget-body">
						<form id="users-add" class="smart-form" method="post">
							<header>
								Основные данные
							</header>
							<fieldset>
								<div class="row">
									<section class="col col-6">
										<label class="label">Логин (e-mail)</label>
										<label class="input">
											<input type="text" name="m_users_login" placeholder="">
										</label>
									</section>
									<section class="col col-3">
										<label class="label">Пароль</label>
										<label class="input">
											<input type="text" name="m_users_password" placeholder="">
										</label>
									</section>
									<section class="col col-3">
										<label class="label">&nbsp;</label>
										<a href="javascript:void(0);" class="btn btn-labeled btn-info" id="pass_gen">
											<span class="btn-label">
												<i class="glyphicon glyphicon-refresh"></i>
											</span>
											Сгенерировать 
										</a>
									</section>
								</div>
								<div class="row">
									<section class="col col-6">
										<label class="label">Группа</label>
										<select name="m_users_group" class="autoselect">
											<?
												foreach($groups as $groups_)
													echo '<option value="'.$groups_[0]['m_users_groups_id'].'">',
														$groups_[0]['m_users_groups_name'],
													'</option>';
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
							<input type="hidden" name="action" value="m_users_add"/>
						</form>
					</div>
				</div>
			</div>	
		</article>
	</div>
</section>