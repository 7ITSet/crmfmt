<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$workers,$info,$services;
if(get('action')=='details'){
$content->setJS('
	
	runAllForms();
	
	$("#employees-add").validate({
		rules : {
			m_employees_fio : {
				maxlength : 180,
				required : true
			},
			"m_employees_services_categories[]":{
				required : true
			},
			m_employees_email : {
				email: true,
				maxlength : 64,
				remote: {
					url: "/ajax/clients_check_email.php",
					data: {
						m_users_login: function() {
							return $( "input[name=m_employees_email]" ).val();
						},
						m_users_id: '.get('m_employees_id').'
					}
				}
			}
		},
		messages:{
			m_employees_email:{
				remote: "E-mail уже есть в системе"
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});
		
	$(".datatable .delete").editable({
		url: "/ajax/services_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete"))
				$(this).parents("tr:first").remove();
		}
	});
		
	$("input[name=m_employees_fio]").on("change",function(){
		$.get(
			"/ajax/name_rp.php",
			{
				name:$("input[name=m_employees_fio]").val()
			},
			function(data){
				if(data!="ERROR"){
					$("input[name=m_employees_fio_rp]").val(data);
				}
			}
		);
	});		
		
	$("#telephones").df({
		max:5,
		f_a:function(){
			$("#telephones .multirow:last").find("input[name=\'m_contragents_tel_numb[]\']").mask("+7 (999) 999-99-99");
			$("#telephones .multirow:last").find("select[name=\'m_contragents_tel_type[]\']").select2();
		}
	});
	
	$(document).on("change","[name=\"m_contragents_tel_numb[]\"]",function(){
		$.get(
			"/ajax/workers_check_tel.php",
			{
				tel:$(this).val()
			},
			function(data){
				if(data.indexOf("true")==-1)
					alert("Этот телефонный номер принадлежит следующим работникам:\n\n"+data);
				else{
					return false;
				}
			}
		);
	})
	
	$(document).find("input[name=\'m_contragents_tel_numb[]\']").mask("+7 (999) 999-99-99");
	$(document).on("keyup","input[name=\'m_contragents_tel_numb[]\']",function(){
		var target=$(this);
		$.get(
			"/ajax/tel_city_code.php",
			{
				tel:target.val()
			},
			function(data){
				if(data!="SHORT_CODE"&&data)
					target.mask(data);
			}
		);
		return true;
	});

');
$categories=array();
$services->categories_childs(0,$categories,2,0,true);
$t=array();
foreach($categories as $k=>$v)
	$t[$v['m_services_categories_id']]=$v;
$categories=$t;
$employee=$workers->getInfo(get('m_employees_id'));
$tel=$info->getTel($employee['m_employees_id']);
$employee['m_employees_services_categories']=explode('|',$employee['m_employees_services_categories']);
?>
<section id="widget-grid" class="">
<?
if(isset($_GET['success']))
	echo '<div class="row"><article class="col-lg-12">
			<div class="alert alert-success alert-block">
				<a class="close" data-dismiss="alert" href="#">x</a>
				<h4 class="alert-heading">Выполнено!</h4>
				Информация успешно обновлена!
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
		<div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">	
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Редактирование данных работника</h2>
				</header>
			<div>
				<div class="widget-body">
					<div id="myTabContent1" class="tab-content padding-10">
						<div class="tab-pane fade in active" id="s1">
							<form id="employees-add" class="smart-form" method="post">
								<header>
									Основные данные
								</header>
								<fieldset>
									<div class="row">
										<section class="col col-6">
											<label class="label">ФИО</label>
											<label class="input">
												<i class="icon-append fa fa-user"></i>
												<input type="text" name="m_employees_fio" value="<?=$employee['m_employees_fio']?>">
											</label>
										</section>
										<section class="col col-6">
											<label class="label">ФИО в родительном падеже</label>
											<label class="input">
												<i class="icon-append fa fa-user"></i>
												<input type="text" name="m_employees_fio_rp" value="<?=$employee['m_employees_fio_rp']?>">
											</label>
										</section>
									</div>
									<div class="row">
										<section class="col col-4">
											<label class="label">Пол</label>
											<div class="inline-group" >
												<label class="radio">
													<input type="radio" name="m_employees_sex" value="женский" <?=$employee['m_employees_sex']=='женский'?'checked':''?>>
													<i></i>
													Женский
												</label>
												<label class="radio">
													<input type="radio" name="m_employees_sex" value="мужской" <?=$employee['m_employees_sex']=='мужской'?'checked':''?>>
													<i></i>
													Мужской
												</label>
											</div>
										</section>
										<section class="col col-4">
											<label class="label">Дата рождения</label>
											<label class="input">
												<i class="icon-append fa fa-calendar"></i>
												<input type="text" name="m_employees_birthday_" class="datepicker" data-mask="99.99.9999" value="<?=dtu($employee['m_employees_birthday'],'d-m-Y')?>">
												<input type="hidden" name="m_employees_birthday" value="<?=$employee['m_employees_birthday']?>">
											</label>
										</section>
									</div>
								</fieldset>
								<header>
									Паспортные данные
								</header>
								<fieldset>
									<div class="row">
										<section class="col col-4">
											<label class="label">Серия и номер паспорта</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_employees_passport_sn" data-mask="9999 999999" value="<?=$employee['m_employees_passport_sn']?>">
											</label>
										</section>
										<section class="col col-8">
											<label class="label">Кем выдан паспорт</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_employees_passport_v" value="<?=$employee['m_employees_passport_v']?>">
											</label>
										</section>
									</div>
									<div class="row">
										<section class="col col-4">
											<label class="label">Дата выдачи паспорта</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_employees_passport_date_" class="datepicker" data-mask="99.99.9999" value="<?=dtu($employee['m_employees_passport_date'],'d-m-Y')?>">
												<input type="hidden" name="m_employees_passport_date" value="<?=$employee['m_employees_passport_date']?>">
											</label>
										</section>
										<section class="col col-8">
											<label class="label">Адрес регистрации по паспорту</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_employees_passport_address_r" value="<?=$employee['m_employees_passport_address_r']?>">
											</label>
										</section>
									</div>
									<div class="row">
										<section class="col col-10">
											<label class="label">
												Скан паспорта
											</label>
											<div class="input input-file">
												<span class="button">
													<input type="file" id="file" name="m_employees_passport_scan" onchange="this.parentNode.nextSibling.value=this.value">
													Обзор
												</span>
												<input type="text" placeholder="добавьте скан паспорта" readonly="">
											</div>
										</section>
									</div>
								</fieldset>
								<header>
									Контактные данные
								</header>
								<fieldset>
									<label class="label">Телефоны</label>
									<div  id="telephones">
										<?
											if($tel)
												foreach($tel as $_tel){
										?>
											<div class="multirow">
												<div class="row">
													<section class="col col-3">
														<label class="input">
															<i class="icon-append fa fa-phone"></i>
															<input type="text" name="m_contragents_tel_numb[]"  placeholder="номер" value="<?=$_tel['m_contragents_tel_numb']?>">
														</label>
													</section>
													<section class="col col-3">
														<label class="input">
															<select name="m_contragents_tel_type[]" style="width:100%" class="autoselect">
																<? 
																	$telType=$info->getTelType();
																	foreach($telType as $_telType)
																		echo '<option value="'.$_telType[0]['m_info_tel_type_id'].'"'.($_tel['m_contragents_tel_type']==$_telType[0]['m_info_tel_type_id']?' selected ':'').'>'.$_telType[0]['m_info_tel_type_name'].'</option>';
																?>
															</select>
														</label>
													</section>
													<section class="col col-3">
														<label class="input">
															<i class="icon-append fa fa-info"></i>
															<input type="text" name="m_contragents_tel_comment[]" placeholder="комментарий" value="<?=$_tel['m_contragents_tel_comment']?>">
														</label>
													</section>
													<section class="col col-3" style="text-align:right">
														<div class="btn-group btn-labeled multirow-btn">
															<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
															<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
																<span class="caret"></span>
															</a>
															<ul class="dropdown-menu">
																<li>
																	<a href="javascript:void(0);" class="add">Добавить номер</a>
																</li>
																<li>
																	<a href="javascript:void(0);" class="delete">Удалить номер</a>
																</li>
															</ul>
														</div>
													</section>
												</div>
											</div>
										<?
											}
											else{
										?>
											<div class="multirow">
												<div class="row">
													<section class="col col-3">
														<label class="input">
															<i class="icon-append fa fa-phone"></i>
															<input type="text" name="m_contragents_tel_numb[]"  placeholder="номер">
														</label>
													</section>
													<section class="col col-3">
														<label class="input">
															<select name="m_contragents_tel_type[]" style="width:100%" class="autoselect">
																<? 
																	$telType=$info->getTelType();
																	foreach($telType as $_telType)
																		echo '<option value="'.$_telType[0]['m_info_tel_type_id'].'">'.$_telType[0]['m_info_tel_type_name'].'</option>';
																?>
															</select>
														</label>
													</section>
													<section class="col col-3">
														<label class="input">
															<i class="icon-append fa fa-info"></i>
															<input type="text" name="m_contragents_tel_comment[]" placeholder="комментарий">
														</label>
													</section>
													<section class="col col-3" style="text-align:right">
														<div class="btn-group btn-labeled multirow-btn">
															<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
															<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
																<span class="caret"></span>
															</a>
															<ul class="dropdown-menu">
																<li>
																	<a href="javascript:void(0);" class="add">Добавить номер</a>
																</li>
																<li>
																	<a href="javascript:void(0);" class="delete">Удалить номер</a>
																</li>
															</ul>
														</div>
													</section>
												</div>
											</div>
										<?}?>
									</div>
									<div class="row">
										<section class="col col-8">
											<label class="label">Адрес для корреспонденции (фактический)</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_employees_address_p" value="<?=$employee['m_employees_address_p']?>">
											</label>
										</section>
									</div>
									<div class="row">
										<section class="col col-4">
											<label class="label">E-mail</label>
											<label class="input">
												<i class="icon-append fa fa-envelope"></i>
												<input type="text" name="m_employees_email" placeholder="@" value="<?=$employee['m_employees_email']?>">
											</label>
										</section>
									</div>
									
								</fieldset>
								<header>
									Рабочая информация
								</header>
								<fieldset>
									<div class="row">
										<section class="col col-4">
											<label class="label">Должность</label>
											<select name="m_employees_post" class="autoselect">
												<?
													foreach($info->getEmployeesPost() as $t_){
														$ct=explode('|',$t_[0]['m_info_post_name']);
														echo '<option value="'.$t_[0]['m_info_post_id'].'" '.($employee['m_employees_post']==$t_[0]['m_info_post_id']?' selected':'').'>',
															$t_[0]['m_info_post_name'],
														'</option>';
													}
												?>
											</select>
										</section>
										<section class="col col-4">
											<label class="label">Дата начала работы</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_employees_date_start_" class="datepicker" data-mask="99.99.9999" value="<?=dtu($employee['m_employees_date_start'],'d-m-Y')?>">
												<input type="hidden" name="m_employees_date_start" value="<?=$employee['m_employees_date_start']?>">
											</label>
										</section>
										<section class="col col-4">
											<label class="label">Дата окончания работы</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_employees_date_end_" class="datepicker" data-mask="99.99.9999" value="<?=dtu($employee['m_employees_date_end'],'d-m-Y')?>">
												<input type="hidden" name="m_employees_date_end" value="<?=$employee['m_employees_date_end']?>">
											</label>
										</section>
									</div>
									<section>
										<label class="label">Виды работ</label>
										<select name="m_employees_services_categories[]" multiple class="autoselect" placeholder="выберите из списка...">
											<? 
												$categories=array();
												$services->categories_childs(0,$categories,2);
												foreach($categories as $categories_){
													echo '<option value="'.$categories_['m_services_categories_id'].'" '.(in_array($categories_['m_services_categories_id'],$employee['m_employees_services_categories'])?' selected':'').'>
															'.$categories_['m_services_categories_name'].'
														</option>';																
												}
											?>
										</select>
									</section>
									<div class="row">
										<section class="col col-6">
											<label class="label">Количество работников</label>
											<label class="input">
												<i class="icon-append fa fa-child"></i>
												<input type="text" name="m_employees_brigade" value="<?=$employee['m_employees_brigade']?>">
											</label>
										</section>
									</div>
								</fieldset>
								<header>
									Заработная плата
								</header>
								<fieldset>
									<div class="row">
										<section class="col col-3">
											<label class="label">Фиксированная часть</label>
											<label class="input">
												<i class="icon-append fa fa-rub"></i>
												<input type="text" name="m_employees_salary_f" style="text-align:right;" value="<?=$employee['m_employees_salary_f']?>">
											</label>
										</section>
										<section class="col col-3">
											<label class="label">Переменная часть</label>
											<label class="input">
												<i class="icon-append fa">%</i>
												<input type="text" name="m_employees_salary_v" style="text-align:right;" value="<?=$employee['m_employees_salary_v']?>">
											</label>
										</section>
									</div>
									<section>
										<label class="label">Комментарий</label>
										<label class="textarea textarea-resizable"> 										
											<textarea name="m_employees_comment" rows="3" class="custom-scroll"><?=$employee['m_employees_comment']?></textarea> 
										</label>
									</section>
								</fieldset>
								<footer>
									<button type="submit" class="btn btn-primary">
										<i class="fa fa-save"></i>
										Сохранить данные
									</button>
								</footer>
								<input type="hidden" name="action" value="m_employees_change">
								<input type="hidden" name="m_employees_id" value="<?=$employee['m_employees_id']?>"/>
							</form>
						</div>
					</div>
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
	
	$("#employees-add").validate({
		rules : {
			m_employees_fio : {
				maxlength : 180,
				required : true
			},
			"m_employees_services_categories[]":{
				required : true
			},
			m_employees_email : {
				email: true,
				maxlength : 64,
				remote: {
					url: "/ajax/clients_check_email.php",
					data: {
						m_users_login: function() {
							return $( "input[name=m_employees_email]" ).val();
						}
					}
				}
			}
		},
		messages:{
			m_employees_email:{
				remote: "E-mail уже есть в системе"
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});
		
	$(".datatable .delete").editable({
		url: "/ajax/services_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete"))
				$(this).parents("tr:first").remove();
		}
	});
		
	$("input[name=m_employees_fio]").on("change",function(){
		$.get(
			"/ajax/name_rp.php",
			{
				name:$("input[name=m_employees_fio]").val()
			},
			function(data){
				if(data!="ERROR"){
					$("input[name=m_employees_fio_rp]").val(data);
				}
			}
		);
	});	
		
	$("#telephones").df({
		max:5,
		f_a:function(){
			$("#telephones .multirow:last").find("input[name=\'m_contragents_tel_numb[]\']").mask("+7 (999) 999-99-99");
			$("#telephones .multirow:last").find("select[name=\'m_contragents_tel_type[]\']").select2();
		}
	});
	
	$(document).on("change","[name=\"m_contragents_tel_numb[]\"]",function(){
		$.get(
			"/ajax/workers_check_tel.php",
			{
				tel:$(this).val()
			},
			function(data){
				if(data.indexOf("true")==-1)
					alert("Этот телефонный номер принадлежит следующим работникам:\n\n"+data);
				else{
					return false;
				}
			}
		);
	})
	
	$(document).find("input[name=\'m_contragents_tel_numb[]\']").mask("+7 (999) 999-99-99");
	$(document).on("keyup","input[name=\'m_contragents_tel_numb[]\']",function(){
		var target=$(this);
		$.get(
			"/ajax/tel_city_code.php",
			{
				tel:target.val()
			},
			function(data){
				if(data!="SHORT_CODE"&&data)
					target.mask(data);
			}
		);
		return true;
	});
');
$categories=array();
$services->categories_childs(0,$categories,2,0,true);
$t=array();
foreach($categories as $k=>$v)
	$t[$v['m_services_categories_id']]=$v;
$categories=$t;
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
if($employees=$workers->getInfo()){
?>
		<article class="col-lg-12">
	
			<div class="jarviswidget jarviswidget-color-blue" id="wid-id-1" 
				data-widget-editbutton="false"
				data-widget-colorbutton="false"
				data-widget-deletebutton="false"
				data-widget-togglebutton="false">
	
				<header>
					<span class="widget-icon"> <i class="fa fa-table"></i> </span>
					<h2>Сотрудники</h2>

				</header>

				<div>				

					<div class="widget-body no-padding">

						<table id="datatable_clients" class="table table-striped table-bordered table-hover datatable" width="100%">
	
							<thead>
								<tr>
									<th>Должность</th>
									<th>Имя</th>
									<th style="width:20%">Виды работ</th>
									<th>Контакты</th>
									<th>Бригада</th>
									<th>Комментарий</th>
									<th style="width:4%">Детали</th>
								</tr>
							</thead>

							<tbody>
								<?
foreach($employees as $employees_){
	$employees_=$employees_[0];
	$m_services_categories_id=array();
	$employees_['m_employees_services_categories']=explode('|',$employees_['m_employees_services_categories']);
	if($employees_['m_employees_services_categories'][0])
		foreach($employees_['m_employees_services_categories'] as $t_)
			$m_services_categories_id[]=$categories[$t_]['m_services_categories_name'];
	$tel=$info->getTel($employees_['m_employees_id']);
	echo '<tr>
		<td>
			'.$info->getEmployeesPost($employees_['m_employees_post']).'
		</td>
		<td>
			'.$employees_['m_employees_fio'].'
		</td>
		<td>
			'.implode('<br/>',$m_services_categories_id).'
		</td>
		<td>',
			$employees_['m_employees_address_p']?'<table class="table-mini"><tr><td><i class="icon-append fa fa-map-marker" style="color:#76B8ED"></i></td><td>'.$employees_['m_employees_address_p'].'</td></tr></table>':'';
if($tel){
	echo '<table class="table-mini">';
	$tel_icon='<td rowspan="'.sizeof($tel).'"><i class="icon-append fa fa-phone" style="color:#76B8ED"></i></td>';
	foreach($tel as $tel_){
		echo '<tr>',
			$tel_icon,
			'<td><nobr>',
			$tel_['m_contragents_tel_numb'],
			'</nobr></td><td><span>',
			$info->getTelType($tel_['m_contragents_tel_type']),
			'</span></td><td>',
			$tel_['m_contragents_tel_comment'],
			'</td></tr>';
			$tel_icon='';
		}
	echo '</table>';
}
if($employees_['m_employees_email'])
	echo '<table class="table-mini"><tr><td><i class="fa fa-envelope-o" style="color:#76B8ED"></td><td>'.'<a href="mailto:'.$employees_['m_employees_email'].'">'.$employees_['m_employees_email'].'</a>'.'</td></tr></table>';
echo '</td>
		<td>
			'.$employees_['m_employees_brigade'].'
		</td>
		<td>
			'.$employees_['m_employees_comment'].'
		</td>
		<td align="center">
			<a class="btn btn-primary btn-xs" href="'.url().'?action=details&m_employees_id='.$employees_['m_employees_id'].'"><i class="fa fa-pencil"></i></a>
			<a class="btn btn-danger btn-xs delete" href="#" data-pk="'.$employees_['m_employees_id'].'" data-name="m_employees_id" data-title="Введите пароль для удаления записи" data-placement="left"><i class="fa fa-trash-o"></i></a>
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
					<h2>Новый работник</h2>
				</header>

			<!-- widget div-->
			<div>

				<!-- widget edit box -->
				<div class="jarviswidget-editbox">
					<!-- This area used as dropdown edit box -->

				</div>
				<!-- end widget edit box -->

				<!-- widget content -->
				<div class="widget-body">

					<div id="myTabContent1" class="tab-content padding-10">
						<div class="tab-pane fade in active" id="s1">
							<form id="employees-add" class="smart-form" method="post">
								<header>
									Основные данные
								</header>
								<fieldset>
									<div class="row">
										<section class="col col-6">
											<label class="label">ФИО</label>
											<label class="input">
												<i class="icon-append fa fa-user"></i>
												<input type="text" name="m_employees_fio" placeholder="Фамилия Имя Отчество">
											</label>
										</section>
										<section class="col col-6">
											<label class="label">ФИО в родительном падеже</label>
											<label class="input">
												<i class="icon-append fa fa-user"></i>
												<input type="text" name="m_employees_fio_rp" placeholder="заполняется автоматически">
											</label>
										</section>
									</div>
									<div class="row">
										<section class="col col-4">
											<label class="label">Пол</label>
											<div class="inline-group" >
												<label class="radio">
													<input type="radio" name="m_employees_sex" value="женский">
													<i></i>
													Женский
												</label>
												<label class="radio">
													<input type="radio" name="m_employees_sex" value="мужской">
													<i></i>
													Мужской
												</label>
											</div>
										</section>
										<section class="col col-4">
											<label class="label">Дата рождения</label>
											<label class="input">
												<i class="icon-append fa fa-calendar"></i>
												<input type="text" name="m_employees_birthday_" class="datepicker" data-mask="99.99.9999">
												<input type="hidden" name="m_employees_birthday">
											</label>
										</section>
									</div>
								</fieldset>
								<header>
									Паспортные данные
								</header>
								<fieldset>
									<div class="row">
										<section class="col col-4">
											<label class="label">Серия и номер паспорта</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_employees_passport_sn" data-mask="9999 999999">
											</label>
										</section>
										<section class="col col-8">
											<label class="label">Кем выдан паспорт</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_employees_passport_v">
											</label>
										</section>
									</div>
									<div class="row">
										<section class="col col-4">
											<label class="label">Дата выдачи паспорта</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_employees_passport_date_" class="datepicker" data-mask="99.99.9999">
												<input type="hidden" name="m_employees_passport_date">
											</label>
										</section>
										<section class="col col-8">
											<label class="label">Адрес регистрации по паспорту</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_employees_passport_address_r">
											</label>
										</section>
									</div>
									<div class="row">
										<section class="col col-10">
											<label class="label">
												Скан паспорта
											</label>
											<div class="input input-file">
												<span class="button">
													<input type="file" id="file" name="m_employees_passport_scan" onchange="this.parentNode.nextSibling.value=this.value">
													Обзор
												</span>
												<input type="text" placeholder="добавьте скан паспорта" readonly="">
											</div>
										</section>
									</div>
								</fieldset>
								<header>
									Контактные данные
								</header>
								<fieldset>
									<label class="label">Телефоны</label>
									<div  id="telephones">
										<div class="multirow">
											<div class="row">
												<section class="col col-3">
													<label class="input">
														<i class="icon-append fa fa-phone"></i>
														<input type="text" name="m_contragents_tel_numb[]"  placeholder="номер">
													</label>
												</section>
												<section class="col col-3">
													<label class="input">
														<select name="m_contragents_tel_type[]" style="width:100%" class="autoselect">
															<? 
																$telType=$info->getTelType();
																foreach($telType as $_telType)
																	echo '<option value="'.$_telType[0]['m_info_tel_type_id'].'">'.$_telType[0]['m_info_tel_type_name'].'</option>';
															?>
														</select>
													</label>
												</section>
												<section class="col col-3">
													<label class="input">
														<i class="icon-append fa fa-info"></i>
														<input type="text" name="m_contragents_tel_comment[]" placeholder="комментарий">
													</label>
												</section>
												<section class="col col-3" style="text-align:right">
													<div class="btn-group btn-labeled multirow-btn">
														<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
														<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
															<span class="caret"></span>
														</a>
														<ul class="dropdown-menu">
															<li>
																<a href="javascript:void(0);" class="add">Добавить номер</a>
															</li>
															<li>
																<a href="javascript:void(0);" class="delete">Удалить номер</a>
															</li>
														</ul>
													</div>
												</section>
											</div>
										</div>
									</div>
									<div class="row">
										<section class="col col-8">
											<label class="label">Адрес для корреспонденции (фактический)</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_employees_address_p">
											</label>
										</section>
									</div>
									<div class="row">
										<section class="col col-4">
											<label class="label">E-mail</label>
											<label class="input">
												<i class="icon-append fa fa-envelope"></i>
												<input type="text" name="m_employees_email" placeholder="@">
											</label>
										</section>
									</div>
									
								</fieldset>
								<header>
									Рабочая информация
								</header>
								<fieldset>
									<div class="row">
										<section class="col col-4">
											<label class="label">Должность</label>
											<select name="m_employees_post" class="autoselect">
												<?
													foreach($info->getEmployeesPost() as $t_){
														$ct=explode('|',$t_[0]['m_info_post_name']);
														echo '<option value="'.$t_[0]['m_info_post_id'].'" '.($t_[0]['m_info_post_id']==3?'selected ':'').'>',
															$t_[0]['m_info_post_name'],
														'</option>';
													}
												?>
											</select>
										</section>
										<section class="col col-4">
											<label class="label">Дата начала работы</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_employees_date_start_" class="datepicker" data-mask="99.99.9999">
												<input type="hidden" name="m_employees_date_start">
											</label>
										</section>
										<section class="col col-4">
											<label class="label">Дата окончания работы</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_employees_date_end_" class="datepicker" data-mask="99.99.9999">
												<input type="hidden" name="m_employees_date_end">
											</label>
										</section>
									</div>
									<section>
										<label class="label">Виды работ</label>
										<select name="m_employees_services_categories[]" multiple class="autoselect" placeholder="выберите из списка...">
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
									<div class="row">
										<section class="col col-6">
											<label class="label">Количество работников</label>
											<label class="input">
												<i class="icon-append fa fa-child"></i>
												<input type="text" name="m_employees_brigade">
											</label>
										</section>
									</div>
								</fieldset>
								<header>
									Заработная плата
								</header>
								<fieldset>
									<div class="row">
										<section class="col col-3">
											<label class="label">Фиксированная часть</label>
											<label class="input">
												<i class="icon-append fa fa-rub"></i>
												<input type="text" name="m_employees_salary_f" style="text-align:right;">
											</label>
										</section>
										<section class="col col-3">
											<label class="label">Переменная часть</label>
											<label class="input">
												<i class="icon-append fa">%</i>
												<input type="text" name="m_employees_salary_v" style="text-align:right;">
											</label>
										</section>
									</div>
									<section>
										<label class="label">Комментарий</label>
										<label class="textarea textarea-resizable"> 										
											<textarea name="m_employees_comment" rows="3" class="custom-scroll"></textarea> 
										</label>
									</section>
								</fieldset>
								<footer>
									<button type="submit" class="btn btn-primary">
										<i class="fa fa-save"></i>
										Добавить работника
									</button>
								</footer>
								<input type="hidden" name="action" value="m_employees_add"/>
							</form>
						</div>
					</div>

				</div>
				<!-- end widget content -->

			</div>
			<!-- end widget div -->

		</div>	

		</article>

	</div>

</section>
<?
}
?>
<script src="/js/jquery.df.js"></script>