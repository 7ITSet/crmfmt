<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$info;
if(get('action')=='details'){
$content->setJS('
	
	runAllForms();
	
	$(document).on("click",".multirow a.copy",function(){
		$(this).parents(".multirow:first").find("select.autoselect").select2("destroy");
	});
	
	$("input[name=m_contragents_p_birthday_]").datepicker("option","altField","input[name=\'m_contragents_p_birthday\']");
	$("input[name=m_contragents_p_birthday_]").datepicker("option","yearRange","1900:"+($.datepicker.formatDate("yy",new Date())*1-18));
	
	$("input[name=m_contragents_p_passport_date_]").datepicker("option","altField","input[name=\'m_contragents_p_passport_date\']");
	$("input[name=m_contragents_p_passport_date_]").datepicker("option","yearRange","1900:"+($.datepicker.formatDate("yy",new Date())*1));
	
	
	$("#clients-personal-add").validate({
		rules : {
			m_contragents_p_fio : {
				maxlength : 130,
				required : true
			},
			m_contragents_p_fio_rp : {
				maxlength : 130
			},
			m_contragents_p_passport_v : {
				maxlength : 80
			},
			m_contragents_address_j : {
				maxlength : 180
			},
			m_contragents_address_p : {
				maxlength : 180
			},
			m_contragents_email : {
				email: true,
				maxlength : 64,
				remote: {
					url: "/ajax/clients_check_email.php",
					data: {
						m_contragents_email: function() {
							return $( "input[name=m_contragents_email]").val();
						}
					}
				}
			},
		},
		messages:{
			m_contragents_email:{
				remote: "E-mail уже есть в системе"
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});
	
	$("#clients-company-add").validate({
		rules : {
			m_contragents_c_name_full : {
				maxlength : 180
			},
			m_contragents_c_name_short : {
				required : true,
				maxlength : 80
			},
			"m_address_area[]" : {
				maxlength : 80
			},
			"m_address_district[]" : {
				maxlength : 80
			},
			"m_address_city[]" : {
				maxlength : 80
			},
			"m_address_street[]" : {
				maxlength : 80
			},
			"m_address_house[]" : {
				maxlength : 8
			},
			"m_address_corp[]" : {
				maxlength : 8
			},
			"m_address_build[]" : {
				maxlength : 8
			},
			"m_address_mast[]" : {
				maxlength : 8
			},
			"m_address_detail[]" : {
				maxlength : 180
			},
			m_contragents_email : {
				maxlength : 64
			},
			m_contragents_www : {
				maxlength : 64
			},
			m_contragents_c_inn : {
				required : true,
				rangelength:[10,12],
				number: true
			},
			m_contragents_c_kpp : {
				rangelength:[9,9],
				number: true
			},
			m_contragents_c_ogrn : {
				rangelength:[13,15],
				number: true
			},
			m_contragents_c_okpo : {
				maxlength : 40,
				number: true
			},
			m_contragents_c_okved : {
				maxlength : 40,
			},
			m_contragents_c_okato : {
				maxlength : 40,
				number: true
			},
			m_contragents_c_oktmo : {
				maxlength : 40,
				number: true
			},
			m_contragents_c_bank_name : {
				maxlength : 150
			},
			m_contragents_c_bank_bik : {
				rangelength:[9,9],
				number: true
			},
			m_contragents_c_bank_rs : {
				rangelength:[20,20],
				number: true
			},
			m_contragents_c_bank_ks : {
				rangelength:[20,20],
				number: true
			},
			m_contragents_c_director_post : {
				maxlength : 280
			},
			m_contragents_c_director_name : {
				maxlength : 80
			},
			m_contragents_c_director_name_rp : {
				maxlength : 80
			},
			m_contragents_c_director_base : {
				maxlength : 180
			},
			m_contragents_c_bookkeeper_name : {
				maxlength : 80
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});
	
	$("#rs").df({
		max:5,
		f_a:function(){
			$("#rs .multirow:last").find("input[name=\'m_contragents_rs_bik[]\']").on("change",function(){
				var sender=$(this);
				$.get(
					"/ajax/bik.php",
					{
						bik:sender.val()
					},
					function(data){
						if(data!="ERROR"){
							var firm=JSON.parse(data);
							sender.parents(".multirow:first").find("input[name=\'m_contragents_rs_bank[]\']").val(firm.suggestions[0].data.name.payment)
							sender.parents(".multirow:first").find("input[name=\'m_contragents_rs_ks[]\']").val(firm.suggestions[0].data.correspondent_account);
						}
					}
				);	
			});
			$("#rs .multirow:last").find("input[name=\'m_contragents_rs_main_[]\']").on("change",function(){
				if($(this).prop("checked"))
					$(this).next().val(1);
				else{
					$(this).next().val(0);
				}
			});
		}
	});
	
	$("#rs .multirow:last").find("input[name=\'m_contragents_rs_bik[]\']").on("change",function(){
		var sender=$(this);
		$.get(
			"/ajax/bik.php",
			{
				bik:sender.val()
			},
			function(data){
				if(data!="ERROR"){
					var firm=JSON.parse(data);
					sender.parents(".multirow:first").find("input[name=\'m_contragents_rs_bank[]\']").val(firm.suggestions[0].data.name.payment)
					sender.parents(".multirow:first").find("input[name=\'m_contragents_rs_ks[]\']").val(firm.suggestions[0].data.correspondent_account);
				
				}
			}
		);	
	});
	$("input[name=\'m_contragents_rs_main_[]\']").on("change",function(){
		if($(this).prop("checked"))
			$(this).next().val(1);
		else{
			$(this).next().val(0);
		}
	});

	$("input[name=m_contragents_c_inn]").on("change",function(){
		$.get(
			"/ajax/inn.php",
			{
				inn:$("input[name=m_contragents_c_inn]").val()
			},
			function(data){
				if(data!="ERROR"){
					var firm=JSON.parse(data);
					$("input[name=\'m_address_full[]\']:first").val(firm.suggestions[0].data.address.value);
					$("input[name=m_contragents_c_kpp]").val(firm.suggestions[0].data.kpp);
					$("input[name=m_contragents_c_ogrn]").val(firm.suggestions[0].data.ogrn);
					$("input[name=m_contragents_c_name_full]").val(firm.suggestions[0].data.name.full_with_opf);
					$("input[name=m_contragents_c_name_short]").val(firm.suggestions[0].value);
					$("input[name=m_contragents_c_director_post]").val(firm.suggestions[0].data.management.post);
					$("input[name=m_contragents_c_director_name]").val(firm.suggestions[0].data.management.name).triggerHandler("change");
					$("input[name=m_contragents_c_okpo]").val(firm.suggestions[0].data.okpo);
					$("input[name=m_contragents_c_okved]").val(firm.suggestions[0].data.okved);
					$("input[name=m_contragents_c_okato]").val(firm.suggestions[0].data.address.data.okato);
					$("input[name=m_contragents_c_oktmo]").val(firm.suggestions[0].data.address.data.oktmo);
					
					/* РАСПРЕДЕЛЯЕМ АДРЕС ПО ПОЛЯМ */
					$.get(
						"/ajax/address.php",
						{
							address:firm.suggestions[0].data.address.value
						},
						function(data){
							var address=JSON.parse(data);
							address=address.suggestions[0].data;
							$("#address").find("input:not([readonly])").val("");
							$("[name=\'m_address_index[]\']:first").val(address.postal_code);
							$("[name=\'m_address_area[]\']:first").val(address.region_with_type);
							$("[name=\'m_address_district[]\']:first").val(address.city_district_with_type);
							$("[name=\'m_address_city[]\']:first").val(address.city_type+". "+address.city);
							$("[name=\'m_address_street[]\']:first").val(address.street_type+". "+address.street);
							$("[name=\'m_address_house[]\']:first").val(address.house);
							$("[name=\'m_address_corp[]\']:first").val(address.block!==undefined?address.block:"");
							$("[name=\'m_address_m_address_detail[]\']:first").val(address.flat_type+". "+address.flat);
							$("[name=\'m_address_full[]\']:first").val(address.postal_code+", "+$("[name=\'m_address_full[]\']:first").val());
						}
					);
				}
			}
		);
	});
	
	$("input[name=m_contragents_c_director_name]").on("change",function(){
		$.get(
			"/ajax/name_rp.php",
			{
				name:$("input[name=m_contragents_c_director_name]").val()
			},
			function(data){
				if(data!="ERROR"){
					$("input[name=m_contragents_c_director_name_rp]").val(data);
				}
			}
		);
		
	});
	
	$("input[name=m_contragents_p_fio]").on("change",function(){
		$.get(
			"/ajax/name_rp.php",
			{
				name:$("input[name=m_contragents_p_fio]").val()
			},
			function(data){
				if(data!="ERROR"){
					$("input[name=m_contragents_p_fio_rp]").val(data);
				}
			}
		);
		
	});
	
	$("#telephones").df({
		max:20,
		f_a:function(){
			$("#telephones .multirow:last").find("input[name=\'m_contragents_tel_numb[]\']").mask("+7 999 999-99-99");
			$("#telephones .multirow:last").find("select[name=\'m_contragents_tel_type[]\']").select2();
		}
	});
	
	$(document).find("input[name=\'m_contragents_tel_numb[]\']").mask("+7 999 999-99-99");
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
	
	$("input[name=\'m_contragents_tel_numb[]\']").trigger("keyup");
	
	/* АВТОЗАПОЛНЕНИЕ АДРЕСОВ */
	$("#address").df({
		max:20,
		f_a:function(string){
			string.find("[name=\'m_address_area[]\'],[name=\'m_address_district[]\'],[name=\'m_address_city[]\'],[name=\'m_address_street[]\']").sug($(this).attr("suggest"));
			string.find("[name=\'m_address_type[]\']").select2();
			string.find("input").on("change",function(){
				myAddress=(string.find("input[name=\'m_address_index[]\']").val()?string.find("input[name=\'m_address_index[]\']").val()+", ":"")+
					(string.find("input[name=\'m_address_area[]\']").val()?string.find("input[name=\'m_address_area[]\']").val():"")+
					(string.find("input[name=\'m_address_district[]\']").val()?(", "+string.find("input[name=\'m_address_district[]\']").val()):"")+
					(string.find("input[name=\'m_address_city[]\']").val()?(", "+string.find("input[name=\'m_address_city[]\']").val()):"")+
					(string.find("input[name=\'m_address_street[]\']").val()?(", "+string.find("input[name=\'m_address_street[]\']").val()):"")+
					(string.find("input[name=\'m_address_house[]\']").val()?(", д. "+string.find("input[name=\'m_address_house[]\']").val()):"")+
					(string.find("input[name=\'m_address_corp[]\']").val()?(", корп. "+string.find("input[name=\'m_address_corp[]\']").val()):"")+
					(string.find("input[name=\'m_address_build[]\']").val()?(", стр. "+string.find("input[name=\'m_address_build[]\']").val()):"")+
					(string.find("input[name=\'m_address_mast[]\']").val()?(", вл. "+string.find("input[name=\'m_address_mast[]\']").val()):"");
				
				string.find("[name=\'m_address_full[]\']").val(myAddress+(string.find("input[name=\'m_address_detail[]\']").val()?(", "+string.find("input[name=\'m_address_detail[]\']").val()):""));
			});
			
			var prev_autoselect=string.prev().find("select.autoselect");
			if(prev_autoselect.data("select2")==undefined)
				prev_autoselect.select2();

		}
	});	
	
	$("[name=\'m_address_area[]\'],[name=\'m_address_district[]\'],[name=\'m_address_city[]\'],[name=\'m_address_street[]\']").sug($(this).attr("suggest"));
	
	$(document).on("change","#address input",function(){
		string=$(this).parents(".multirow:first");
		myAddress=(string.find("input[name=\'m_address_index[]\']").val()?string.find("input[name=\'m_address_index[]\']").val()+", ":"")+
			(string.find("input[name=\'m_address_area[]\']").val()?string.find("input[name=\'m_address_area[]\']").val():"")+
			(string.find("input[name=\'m_address_district[]\']").val()?(", "+string.find("input[name=\'m_address_district[]\']").val()):"")+
			(string.find("input[name=\'m_address_city[]\']").val()?(", "+string.find("input[name=\'m_address_city[]\']").val()):"")+
			(string.find("input[name=\'m_address_street[]\']").val()?(", "+string.find("input[name=\'m_address_street[]\']").val()):"")+
			(string.find("input[name=\'m_address_house[]\']").val()?(", д. "+string.find("input[name=\'m_address_house[]\']").val()):"")+
			(string.find("input[name=\'m_address_corp[]\']").val()?(", корп. "+string.find("input[name=\'m_address_corp[]\']").val()):"")+
			(string.find("input[name=\'m_address_build[]\']").val()?(", стр. "+string.find("input[name=\'m_address_build[]\']").val()):"")+
			(string.find("input[name=\'m_address_mast[]\']").val()?(", вл. "+string.find("input[name=\'m_address_mast[]\']").val()):"");
		
		string.find("[name=\'m_address_full[]\']").val(myAddress+(string.find("input[name=\'m_address_detail[]\']").val()?(", "+string.find("input[name=\'m_address_detail[]\']").val()):""));
		
	});
	
	$("[name=m_contragents_c_director_name]").triggerHandler("change");
	
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
$client=$contragents->getInfo(get('m_contragents_id'));
$type=explode('|',$client['m_contragents_type']);
$tel=$info->getTel($client['m_contragents_id']);
$address=$info->getAddress($client['m_contragents_id']);
$rs=$info->getRS($client['m_contragents_id']);
$foto=array();
//удаляем временную директорию фоток пользователя
echo file::deldir($_SERVER['DOCUMENT_ROOT'].'/temp/uploads/'.$user->getInfo());
?>
<div class="row">
<article class="col-lg-6 sortable-grid ui-sortable">
	<div class="jarviswidget" id="wid-id-1" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">			
		<header>
			<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
			<h2>Редактирование данных контрагента</h2>
		</header>
		<div>
			<div class="widget-body">
			<?
				if($client['m_contragents_p_fio']){
			?>
				<form id="clients-personal-add" class="smart-form" method="post">
					<header>
						Основные данные
					</header>
					<fieldset>
						<div class="row">
							<section class="col col-4">
								<label class="label">ФИО</label>
								<label class="input">
									<i class="icon-append fa fa-user"></i>
									<input type="text" name="m_contragents_p_fio" value="<?=$client['m_contragents_p_fio']?>" placeholder="Фамилия Имя Отчество">
								</label>
							</section>
							<section class="col col-8">
								<label class="label">ФИО в родительном падеже</label>
								<label class="input">
									<i class="icon-append fa fa-user"></i>
									<input type="text" name="m_contragents_p_fio_rp" value="<?=$client['m_contragents_p_fio_rp']?>"  placeholder="заполнится автоматически">
								</label>
							</section>
						</div>
						<div class="row">
							<section class="col col-4">
								<label class="label">Пол</label>
								<div class="inline-group" >
									<label class="radio">
										<input type="radio" name="m_contragents_p_sex" value="женский" <?=$client['m_contragents_p_sex']=='женский'?'checked':''?>>
										<i></i>
										Женский
									</label>
									<label class="radio">
										<input type="radio" name="m_contragents_p_sex" value="мужской" <?=$client['m_contragents_p_sex']=='мужской'?'checked':''?>>
										<i></i>
										Мужской
									</label>
								</div>
							</section>
							<section class="col col-4">
								<label class="label">Дата рождения</label>
								<label class="input">
									<i class="icon-append fa fa-calendar"></i>
									<input type="text" name="m_contragents_p_birthday_" class="datepicker" data-mask="99.99.9999" value="<?=$client['m_contragents_p_birthday']?dtu($client['m_contragents_p_birthday'],'d-m-Y'):''?>">
									<input type="hidden" name="m_contragents_p_birthday" value="<?=$client['m_contragents_p_birthday']?>">
								</label>
							</section>
						</div>
						<section>
							<label class="label">Комментарий</label>
							<label class="textarea textarea-resizable"> 										
								<textarea name="m_contragents_comment" rows="3" class="custom-scroll"><?=$client['m_contragents_comment']?></textarea> 
							</label>
						</section>
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
									<input type="text" name="m_contragents_p_passport_sn" data-mask="9999 999999" value="<?=$client['m_contragents_p_passport_sn']?>">
								</label>
							</section>
							<section class="col col-8">
								<label class="label">Кем выдан паспорт</label>
								<label class="input">
									<i class="icon-append fa fa-columns"></i>
									<input type="text" name="m_contragents_p_passport_v" value="<?=$client['m_contragents_p_passport_v']?>">
								</label>
							</section>
						</div>
						<div class="row">
							<section class="col col-4">
								<label class="label">Дата выдачи паспорта</label>
								<label class="input">
									<i class="icon-append fa fa-columns"></i>
									<input type="text" name="m_contragents_p_passport_date_" class="datepicker" data-mask="99.99.9999" value="<?=$client['m_contragents_p_passport_date']?dtu($client['m_contragents_p_passport_date'],'d-m-Y'):''?>">
									<input type="hidden" name="m_contragents_p_passport_date" value="<?=$client['m_contragents_p_passport_date']?>">
								</label>
							</section>
							<section class="col col-8">
								<label class="label">Адрес регистрации по паспорту</label>
								<label class="input">
									<i class="icon-append fa fa-columns"></i>
									<input type="text" name="m_contragents_address_j" value="<?=$client['m_contragents_address_j']?>">
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
										<input type="file" id="file" name="m_clients_personal_passport_scan" onchange="this.parentNode.nextSibling.value=this.value">
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
										<section class="col col-3">
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
										<section class="col col-3">
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
						<section>
								<label class="label">Адрес для корреспонденции</label>
								<label class="input">
									<i class="icon-append fa fa-columns"></i>
									<input type="text" name="m_contragents_address_p" value="<?=$client['m_contragents_address_p']?>">
								</label>
						</section>
						<div class="row">
							<section class="col col-4">
								<label class="label">E-mail</label>
								<label class="input">
									<i class="icon-append fa fa-envelope"></i>
									<input type="text" name="m_contragents_email" placeholder="@" value="<?=$client['m_contragents_email']?>">
								</label>
							</section>
						</div>
					</fieldset>
					<footer>
						<button type="submit" class="btn btn-primary">
							<i class="fa fa-save"></i>
							Сохранить изменения
						</button>
					</footer>
					<input type="hidden" name="action" value="m_clients_personal_change"/>
					<input type="hidden" name="m_contragents_type" value="2"/>
					<input type="hidden" name="m_contragents_id" value="<?=$client['m_contragents_id']?>"/>
				</form>
			<?
				}
				elseif($client['m_contragents_c_name_full']){
			?>
				<form id="clients-company-add" class="smart-form" method="post">
					<header>
						Реквизиты
					</header>
					<fieldset>
						<div class="row">
							<section class="col col-6">
								<label class="label">Полное наименование</label>
								<label class="input">
									<input type="text" name="m_contragents_c_name_full" placeholder="Общество с сограниченной ответственностью ..." value="<?=$client['m_contragents_c_name_full']?>">
								</label>
							</section>
							<section class="col col-6">
								<label class="label">Краткое наименование</label>
								<label class="input">
									<input type="text" name="m_contragents_c_name_short" placeholder="ООО ..." value="<?=$client['m_contragents_c_name_short']?>">
								</label>
							</section>
						</div>
						<div class="row">
								<section class="col col-3">
									<label class="label">ИНН</label>
									<label class="input">
										<i class="icon-append fa fa-file"></i>
										<input type="text" name="m_contragents_c_inn" placeholder="10 цифр для ООО и 12 для ИП" value="<?=$client['m_contragents_c_inn']?>">
									</label>
								</section>
								<section class="col col-3">
									<label class="label">КПП</label>
									<label class="input">
										<i class="icon-append fa fa-file"></i>
										<input type="text" name="m_contragents_c_kpp" placeholder="для — ИП не заполнять" value="<?=$client['m_contragents_c_kpp']?>">
									</label>
								</section>
								<section class="col col-3">
									<label class="label">ОГРН/ОГРНИП</label>
									<label class="input">
										<i class="icon-append fa fa-file"></i>
										<input type="text" name="m_contragents_c_ogrn" placeholder="" value="<?=$client['m_contragents_c_ogrn']?>">
									</label>
								</section>
								<section class="col col-3">
									<label class="label">&nbsp;</label>
									<label class="checkbox">
										<input type="checkbox" name="m_contragents_c_nds" value="1" <?=$client['m_contragents_c_nds']?'checked':''?> />
										Плательщик НДС
										<i></i>
									</label>
								</section>
						</div>
						<div class="row">
							<section class="col col-3">
								<label class="label">ОКПО</label>
								<label class="input">
									<i class="icon-append fa fa-file"></i>
									<input type="text" name="m_contragents_c_okpo" value="<?=$client['m_contragents_c_okpo']?>">
								</label>
							</section>
							<section class="col col-3">
								<label class="label">ОКВЭД</label>
								<label class="input">
									<i class="icon-append fa fa-file"></i>
									<input type="text" name="m_contragents_c_okved" value="<?=$client['m_contragents_c_okved']?>">
								</label>
							</section>
							<section class="col col-3">
								<label class="label">ОКАТО</label>
								<label class="input">
									<i class="icon-append fa fa-file"></i>
									<input type="text" name="m_contragents_c_okato" value="<?=$client['m_contragents_c_okato']?>">
								</label>
							</section>
							<section class="col col-3">
								<label class="label">ОКТМО</label>
								<label class="input">
									<i class="icon-append fa fa-file"></i>
									<input type="text" name="m_contragents_c_oktmo" value="<?=$client['m_contragents_c_oktmo']?>">
								</label>
							</section>
						</div>
					</fieldset>
					<header>
					Банковские реквизиты
					</header>
					<fieldset>
						<div id="rs">
						<?
							if($rs)
								foreach($rs as $_rs){
						?>
							
								<div class="multirow">
									<div class="row">
										<section class="col col-4">
											<label class="label">БИК</label>
											<label class="input">
												<i class="icon-append fa fa-money"></i>
												<input type="text" name="m_contragents_rs_bik[]" placeholder="042..." value="<?=$_rs['m_contragents_rs_bik']?>">
											</label>
										</section>
										<section class="col col-4">
											<label class="label">Расчётный счёт</label>
											<label class="input">
												<i class="icon-append fa fa-money"></i>
												<input type="text" name="m_contragents_rs_rs[]" placeholder="40702810..." value="<?=$_rs['m_contragents_rs_rs']?>">
											</label>
										</section>
										<section class="col col-4">
											<label class="label">&nbsp;</label>
											<label class="checkbox">
												<input type="checkbox" name="m_contragents_rs_main_[]" value="1" <?=$_rs['m_contragents_rs_main']?'checked':''?> />
												<input type="hidden" name="m_contragents_rs_main[]" value="<?=$_rs['m_contragents_rs_main']?>" />
												<i></i>
												По умолчанию для документов
											</label>
										</section>
										<section class="col col-5">
											<label class="label">Банк</label>
											<label class="input">
												<i class="icon-append fa fa-money"></i>
												<input type="text" name="m_contragents_rs_bank[]" placeholder="автозаполнение" value="<?=$_rs['m_contragents_rs_bank']?>">
											</label>
										</section>
										<section class="col col-4">
											<label class="label">Корреспондентский счёт</label>
											<label class="input">
												<i class="icon-append fa fa-money"></i>
												<input type="text" name="m_contragents_rs_ks[]" placeholder="автозаполнение" value="<?=$_rs['m_contragents_rs_ks']?>">
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
														<a href="javascript:void(0);" class="add">Добавить счёт</a>
													</li>
													<li>
														<a href="javascript:void(0);" class="delete">Удалить счёт</a>
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
										<section class="col col-4">
											<label class="label">БИК</label>
											<label class="input">
												<i class="icon-append fa fa-money"></i>
												<input type="text" name="m_contragents_rs_bik[]" placeholder="042..." >
											</label>
										</section>
										<section class="col col-4">
											<label class="label">Расчётный счёт</label>
											<label class="input">
												<i class="icon-append fa fa-money"></i>
												<input type="text" name="m_contragents_rs_rs[]" placeholder="40702810..." >
											</label>
										</section>
										<section class="col col-4">
											<label class="label">&nbsp;</label>
											<label class="checkbox">
												<input type="checkbox" name="m_contragents_rs_main[]" value="1" />
												<input type="hidden" name="m_contragents_rs_main[]" value="0" />
												<i></i>
												По умолчанию для документов
											</label>
										</section>
										<section class="col col-5">
											<label class="label">Банк</label>
											<label class="input">
												<i class="icon-append fa fa-money"></i>
												<input type="text" name="m_contragents_rs_bank[]" placeholder="автозаполнение">
											</label>
										</section>
										<section class="col col-4">
											<label class="label">Корреспондентский счёт</label>
											<label class="input">
												<i class="icon-append fa fa-money"></i>
												<input type="text" name="m_contragents_rs_ks[]" placeholder="автозаполнение" >
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
														<a href="javascript:void(0);" class="add">Добавить счёт</a>
													</li>
													<li>
														<a href="javascript:void(0);" class="delete">Удалить счёт</a>
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
					<header>
						Адреса организации
					</header>
					<fieldset>
						<div id="address">
						<?
							if($address)
								foreach($address as $_address){
						?>
							<div class="multirow" style="border-bottom:1px dotted #ccc;margin-bottom:15px;">
								<section>
									<label class="input">
										<input type="text" name="m_address_full[]" readonly placeholder="Полный адрес" style="border:none;padding:0;font-weight:700;" value="<?=$_address['m_address_full']?>">
									</label>
								</section>
								<div class="row">
									<section class="col col-2">
										<label class="input">
											<input type="text" name="m_address_index[]" placeholder="индекс" value="<?=$_address['m_address_index']?>">
										</label>
									</section>
									<section class="col col-4">
										<label class="input">
											<input type="text" name="m_address_area[]" suggest="area" placeholder="регион" value="<?=$_address['m_address_area']?>">
										</label>
									</section>
									<section class="col col-3">
										<label class="input">
											<input type="text" name="m_address_district[]" suggest="subarea" placeholder="район" value="<?=$_address['m_address_district']?>">
										</label>
									</section>
									<section class="col col-3">
										<label class="input">
											<input type="text" name="m_address_city[]" suggest="city"placeholder="город" value="<?=$_address['m_address_city']?>">
										</label>
									</section>
								</div>
								<div class="row">
									<section class="col col-4">
										<label class="input">
											<input type="text" name="m_address_street[]" suggest="street" placeholder="улица" value="<?=$_address['m_address_street']?>">
										</label>
									</section>
									<section class="col col-sm-2">
										<label class="input">
											<input type="text" name="m_address_house[]" placeholder="дом" value="<?=$_address['m_address_house']?>">
										</label>
									</section>
									<section class="col col-sm-2">
										<label class="input">
											<input type="text" name="m_address_corp[]" placeholder="корпус" value="<?=$_address['m_address_corp']?>">
										</label>
									</section>
									<section class="col col-sm-2">
										<label class="input">
											<input type="text" name="m_address_build[]" placeholder="строение" value="<?=$_address['m_address_build']?>">
										</label>
									</section>
									<section class="col col-sm-2">
										<label class="input">
											<input type="text" name="m_address_mast[]" placeholder="владение" value="<?=$_address['m_address_mast']?>">
										</label>
									</section>
								</div>
								<div class="row">
									<section class="col col-3">
										<label class="input">
											<input type="text" name="m_address_detail[]" placeholder="кв. / оф. или дополн. данные" value="<?=$_address['m_address_detail']?>">
										</label>
									</section>
									<section class="col col-3">
										<label class="input">
											<input type="text" name="m_address_recipient[]" placeholder="получатель" value="<?=$_address['m_address_recipient']?>">
										</label>
									</section>
									
									<section class="col col-3">
										<label class="input">
											<select name="m_address_type[]" style="width:100%" class="autoselect">
												<? 
													$addrType=$info->getAddressType();
													foreach($addrType as $_addrType)
														echo '<option value="'.$_addrType[0]['m_info_address_type_id'].'"'.($_addrType[0]['m_info_address_type_id']==$_address['m_address_type']?' selected ':'').'>'.$_addrType[0]['m_info_address_type_name'].'</option>';
												?>
											</select>
										</label>
									</section>
									<section class="col col-3">
										<div class="btn-group btn-labeled multirow-btn">
											<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
											<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
												<span class="caret"></span>
											</a>
											<ul class="dropdown-menu">
												<li>
													<a href="javascript:void(0);" class="add">Добавить адрес</a>
												</li>
												<li>
													<a href="javascript:void(0);" class="copy">Скопировать адрес</a>
												</li>
												<li>
													<a href="javascript:void(0);" class="delete">Удалить адрес</a>
												</li>
											</ul>
										</div>
									</section>
								</div>
							</div>
						<?
							} else {
						?>
							<div class="multirow" style="border-bottom:1px dotted #ccc;margin-bottom:15px;">
								<section>
									<label class="input">
										<input type="text" name="m_address_full[]" disabled="disabled" placeholder="Полный адрес" style="border:none;padding:0;font-weight:700;">
									</label>
								</section>
								<div class="row">
									<section class="col col-2">
										<label class="input">
											<input type="text" name="m_address_index[]" placeholder="индекс">
										</label>
									</section>
									<section class="col col-4">
										<label class="input">
											<input type="text" name="m_address_area[]" suggest="area" placeholder="регион">
										</label>
									</section>
									<section class="col col-3">
										<label class="input">
											<input type="text" name="m_address_district[]" suggest="subarea" placeholder="район">
										</label>
									</section>
									<section class="col col-3">
										<label class="input">
											<input type="text" name="m_address_city[]" suggest="city"placeholder="город">
										</label>
									</section>
								</div>
								<div class="row">
									<section class="col col-4">
										<label class="input">
											<input type="text" name="m_address_street[]" suggest="street" placeholder="улица">
										</label>
									</section>
									<section class="col col-sm-2">
										<label class="input">
											<input type="text" name="m_address_house[]" placeholder="дом">
										</label>
									</section>
									<section class="col col-sm-2">
										<label class="input">
											<input type="text" name="m_address_corp[]" placeholder="корпус">
										</label>
									</section>
									<section class="col col-sm-2">
										<label class="input">
											<input type="text" name="m_address_build[]" placeholder="строение">
										</label>
									</section>
									<section class="col col-sm-2">
										<label class="input">
											<input type="text" name="m_address_mast[]" placeholder="владение">
										</label>
									</section>
								</div>
								<div class="row">
									<section class="col col-4">
										<label class="input">
											<input type="text" name="m_address_detail[]" placeholder="кв. / оф. или дополн. данные">
										</label>
									</section>
									<section class="col col-3">
										<label class="input">
											<input type="text" name="m_address_recipient[]" placeholder="получатель" value="<?=$_address['m_address_recipient']?>">
										</label>
									</section>
									<section class="col col-3">
										<label class="input">
											<select name="m_address_type[]" style="width:100%" class="autoselect">
												<? 
													$addrType=$info->getAddressType();
													foreach($addrType as $_addrType)
														echo '<option value="'.$_addrType[0]['m_info_address_type_id'].'">'.$_addrType[0]['m_info_address_type_name'].'</option>';
												?>
											</select>
										</label>
									</section>
									<section class="col col-3">
										<div class="btn-group btn-labeled multirow-btn">
											<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
											<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
												<span class="caret"></span>
											</a>
											<ul class="dropdown-menu">
												<li>
													<a href="javascript:void(0);" class="add">Добавить адрес</a>
												</li>
												<li>
													<a href="javascript:void(0);" class="copy">Скопировать адрес</a>
												</li>
												<li>
													<a href="javascript:void(0);" class="delete">Удалить адрес</a>
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
					<header>
						Контактная информация
					</header>
					<fieldset>
						<div class="row">
							<section class="col col-6">
								<label class="label">Электронная почта</label>
								<label class="input">
									<i class="icon-append fa fa-envelope"></i>
									<input type="text" name="m_contragents_email" placeholder="@" value="<?=$client['m_contragents_email']?>">
								</label>
							</section>
							<section class="col col-6">
								<label class="label">Сайт</label>
								<label class="input">
									<i class="icon-append fa fa-external-link"></i>
									<input type="text" name="m_contragents_www" placeholder="http://" value="<?=$client['m_contragents_www']?>">
								</label>
							</section>
						</div>			
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
										<section class="col col-3">
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
										<section class="col col-3">
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
						<section>
							<label class="label">Комментарий</label>
							<label class="textarea textarea-resizable"> 										
								<textarea name="m_contragents_comment" rows="3" class="custom-scroll"><?=$client['m_contragents_comment']?></textarea> 
							</label>
						</section>
					</fieldset>
					<header>
						Исполнительный орган (руководитель) и главный бухгалтер
					</header>
					<fieldset>
						<div class="row">
								<section class="col col-4">
									<label class="label">Должность руководителя</label>
									<label class="input">
										<i class="icon-append fa fa-hand-o-up"></i>
										<input type="text" name="m_contragents_c_director_post" placeholder="генеральный директор" value="<?=$client['m_contragents_c_director_post']?>">
									</label>
								</section>
								<section class="col col-4">
									<label class="label">ФИО руководителя</label>
									<label class="input">
										<i class="icon-append fa fa-user"></i>
										<input type="text" name="m_contragents_c_director_name" placeholder="Петров Иван Алексеевич" value="<?=$client['m_contragents_c_director_name']?>">
									</label>
								</section>
								<section class="col col-4">
									<label class="label">ФИО руководителя в род. падеже</label>
									<label class="input">
										<i class="icon-append fa fa-user"></i>
										<input type="text" name="m_contragents_c_director_name_rp" placeholder="Петрова Ивана Алексеевича" value="<?=$client['m_contragents_c_director_name_rp']?>">
									</label>
								</section>
								<section class="col col-4">
									<label class="label">Действует на основании</label>
									<label class="input">
										<i class="icon-append fa fa-pencil-square-o"></i>
										<input type="text" name="m_contragents_c_director_base" placeholder="Устава, доверенности, св-ва" value="<?=$client['m_contragents_c_director_base']?>">
									</label>
								</section>
								<section class="col col-4">
									<label class="label">ФИО гл. бухгалтера</label>
									<label class="input">
										<i class="icon-append fa fa-user"></i>
										<input type="text" name="m_contragents_c_bookkeeper_name" placeholder="Петрова Василиса Ивановна" value="<?=$client['m_contragents_c_bookkeeper_name']?>">
									</label>
								</section>
								<section class="col col-4">
									<label class="label">ФИО ответственного</label>
									<label class="input">
										<i class="icon-append fa fa-user"></i>
										<input type="text" name="m_contragents_c_responsible_name" placeholder="Петрова Василиса Ивановна" value="<?=$client['m_contragents_c_responsible_name']?>">
									</label>
								</section>
						</div>
					</fieldset>
					<header>
						Грузополучатель
					</header>
					<fieldset>
						<section>
							<select name="m_contragents_consignee" class="autoselect" placeholder="выберите из списка...">
								<option value="0" <?=$client['m_contragents_consignee']==0?' selected ':''?>>он же</option>
								<?
									foreach($contragents->getInfo() as $contragents_){
										echo '<option value="'.$contragents_[0]['m_contragents_id'].'"'.($contragents_[0]['m_contragents_id']==$client['m_contragents_consignee']?' selected ':'').'>',
											$contragents->getName($contragents_[0]['m_contragents_id']),
										'</option>';
									}
								?>
							</select>
						</section>
					</fieldset>
					<footer>
						<button type="submit" class="btn btn-primary">
							<i class="fa fa-save"></i>
							Сохранить данные
						</button>
					</footer>
					<input type="hidden" name="action" value="m_clients_company_change"/>
					<input type="hidden" name="m_contragents_id" value="<?=$client['m_contragents_id']?>"/>
				</form>
			<?
				}
			?>
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
	
	$(document).on("click",".multirow a.copy",function(){
		$(this).parents(".multirow:first").find("select.autoselect").select2("destroy");
	});
	
	$("input[name=m_contragents_p_birthday_]").datepicker("option","altField","input[name=\'m_contragents_p_birthday\']");
	$("input[name=m_contragents_p_birthday_]").datepicker("option","yearRange","1900:"+($.datepicker.formatDate("yy",new Date())*1-18));
	
	$("input[name=m_contragents_p_passport_date_]").datepicker("option","altField","input[name=\'m_contragents_p_passport_date\']");
	$("input[name=m_contragents_p_passport_date_]").datepicker("option","yearRange","1900:"+($.datepicker.formatDate("yy",new Date())*1));
	
	
	$("#clients-personal-add").validate({
		rules : {
			m_contragents_p_fio : {
				maxlength : 130,
				required : true
			},
			m_contragents_p_fio_rp : {
				maxlength : 130
			},
			m_contragents_p_passport_v : {
				maxlength : 80
			},
			m_contragents_address_j : {
				maxlength : 180
			},
			m_contragents_address_p : {
				maxlength : 180
			},
			m_contragents_email : {
				email: true,
				maxlength : 64,
				remote: {
					url: "/ajax/clients_check_email.php",
					data: {
						m_contragents_email: function() {
							return $( "input[name=m_contragents_email]").val();
						}
					}
				}
			},
		},
		messages:{
			m_contragents_email:{
				remote: "E-mail уже есть в системе"
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});
	
	$("#clients-company-add").validate({
		rules : {
			m_contragents_c_name_full : {
				maxlength : 180
			},
			m_contragents_c_name_short : {
				required : true,
				maxlength : 80
			},
			"m_address_area[]" : {
				maxlength : 80
			},
			"m_address_district[]" : {
				maxlength : 80
			},
			"m_address_city[]" : {
				maxlength : 80
			},
			"m_address_street[]" : {
				maxlength : 80
			},
			"m_address_house[]" : {
				maxlength : 8
			},
			"m_address_corp[]" : {
				maxlength : 8
			},
			"m_address_build[]" : {
				maxlength : 8
			},
			"m_address_mast[]" : {
				maxlength : 8
			},
			"m_address_detail[]" : {
				maxlength : 180
			},
			m_contragents_email : {
				maxlength : 64
			},
			m_contragents_www : {
				maxlength : 64
			},
			m_contragents_c_inn : {
				required : true,
				rangelength:[10,12],
				number: true
			},
			m_contragents_c_kpp : {
				rangelength:[9,9],
				number: true
			},
			m_contragents_c_ogrn : {
				rangelength:[13,15],
				number: true
			},
			m_contragents_c_okpo : {
				maxlength : 40,
				number: true
			},
			m_contragents_c_okved : {
				maxlength : 40
			},
			m_contragents_c_okato : {
				maxlength : 40,
				number: true
			},
			m_contragents_c_oktmo : {
				maxlength : 40,
				number: true
			},
			m_contragents_c_bank_name : {
				maxlength : 150
			},
			m_contragents_c_bank_bik : {
				rangelength:[9,9],
				number: true
			},
			m_contragents_c_bank_rs : {
				rangelength:[20,20],
				number: true
			},
			m_contragents_c_bank_ks : {
				rangelength:[20,20],
				number: true
			},
			m_contragents_c_director_post : {
				maxlength : 280
			},
			m_contragents_c_director_name : {
				maxlength : 80
			},
			m_contragents_c_director_name_rp : {
				maxlength : 80
			},
			m_contragents_c_director_base : {
				maxlength : 80
			},
			m_contragents_c_bookkeeper_name : {
				maxlength : 80
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});
	
	$("#rs").df({
		max:5,
		f_a:function(){
			$("#rs .multirow:last").find("input[name=\'m_contragents_rs_bik[]\']").on("change",function(){
				var sender=$(this);
				$.get(
					"/ajax/bik.php",
					{
						bik:sender.val()
					},
					function(data){
						if(data!="ERROR"){
							var firm=JSON.parse(data);
							sender.parents(".multirow:first").find("input[name=\'m_contragents_rs_bank[]\']").val(firm.suggestions[0].data.name.payment)
							sender.parents(".multirow:first").find("input[name=\'m_contragents_rs_ks[]\']").val(firm.suggestions[0].data.correspondent_account);
						}
					}
				);	
			});
			$("#rs .multirow:last").find("input[name=\'m_contragents_rs_main_[]\']").on("change",function(){
				if($(this).prop("checked"))
					$(this).next().val(1);
				else{
					$(this).next().val(0);
				}
			});
		}
	});
	
	$("#rs .multirow:last").find("input[name=\'m_contragents_rs_bik[]\']").on("change",function(){
		var sender=$(this);
		$.get(
			"/ajax/bik.php",
			{
				bik:sender.val()
			},
			function(data){
				if(data!="ERROR"){
					var firm=JSON.parse(data);
					sender.parents(".multirow:first").find("input[name=\'m_contragents_rs_bank[]\']").val(firm.suggestions[0].data.name.payment)
					sender.parents(".multirow:first").find("input[name=\'m_contragents_rs_ks[]\']").val(firm.suggestions[0].data.correspondent_account);
				
				}
			}
		);	
	});
	$("input[name=\'m_contragents_rs_main_[]\']").on("change",function(){
		if($(this).prop("checked"))
			$(this).next().val(1);
		else{
			$(this).next().val(0);
		}
	});

	$("input[name=m_contragents_c_inn]").on("change",function(){
		$.get(
			"/ajax/inn.php",
			{
				inn:$("input[name=m_contragents_c_inn]").val()
			},
			function(data){
				if(data!="ERROR"){
					var firm=JSON.parse(data);
					$("input[name=\'m_address_full[]\']:first").val(firm.suggestions[0].data.address.value);
					$("input[name=m_contragents_c_kpp]").val(firm.suggestions[0].data.kpp);
					$("input[name=m_contragents_c_ogrn]").val(firm.suggestions[0].data.ogrn);
					$("input[name=m_contragents_c_name_full]").val(firm.suggestions[0].data.name.full_with_opf);
					$("input[name=m_contragents_c_name_short]").val(firm.suggestions[0].value);
					$("input[name=m_contragents_c_director_post]").val(firm.suggestions[0].data.management.post);
					$("input[name=m_contragents_c_director_name]").val(firm.suggestions[0].data.management.name).triggerHandler("change");
					$("input[name=m_contragents_c_okpo]").val(firm.suggestions[0].data.okpo);
					$("input[name=m_contragents_c_okved]").val(firm.suggestions[0].data.okved);
					$("input[name=m_contragents_c_okato]").val(firm.suggestions[0].data.address.data.okato);
					$("input[name=m_contragents_c_oktmo]").val(firm.suggestions[0].data.address.data.oktmo);
					/* РАСПРЕДЕЛЯЕМ АДРЕС ПО ПОЛЯМ */
					$.get(
						"/ajax/address.php",
						{
							address:firm.suggestions[0].data.address.value
						},
						function(data){
							var address=JSON.parse(data);
							address=address.suggestions[0].data;
							$("#address").find("input:not([readonly])").val("");
							$("[name=\'m_address_index[]\']:first").val(address.postal_code);
							$("[name=\'m_address_area[]\']:first").val(address.region_with_type);
							$("[name=\'m_address_district[]\']:first").val(address.city_district_with_type);
							$("[name=\'m_address_city[]\']:first").val(address.city_type+". "+address.city);
							$("[name=\'m_address_street[]\']:first").val(address.street_type+". "+address.street);
							$("[name=\'m_address_house[]\']:first").val(address.house);
							$("[name=\'m_address_corp[]\']:first").val(address.block!==undefined?address.block:"");
							$("[name=\'m_address_detail[]\']:first").val(address.flat_type+". "+address.flat);
							$("[name=\'m_address_full[]\']:first").val(address.postal_code+", "+$("[name=\'m_address_full[]\']:first").val());
							
						}
					);
				}
			}
		);
	});

	
	$("input[name=m_contragents_c_director_name]").on("change",function(){
		$.get(
			"/ajax/name_rp.php",
			{
				name:$("input[name=m_contragents_c_director_name]").val()
			},
			function(data){
				if(data!="ERROR"){
					$("input[name=m_contragents_c_director_name_rp]").val(data);
				}
			}
		);
		
	});
	
	$("#telephones-p").df({
		max:20,
		f_a:function(string){
			string.find("input[name=\'m_contragents_tel_numb[]\']").mask("+7 999 999-99-99");
			string.find("select[name=\'m_contragents_tel_type[]\']").select2();
		}
	});
	$("#telephones-c").df({
		max:20,
		f_a:function(string){
			string.find("input[name=\'m_contragents_tel_numb[]\']").mask("+7 999 999-99-99");
			string.find("select[name=\'m_contragents_tel_type[]\']").select2();
		}
	});
	
	$(".datatable .delete").editable({
		url: "/ajax/services_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete"))
				$(this).parents("tr:first").remove();
		}
	});
	
	$(".m_contragents_type").each(function(index,el){
		if($(el).attr("data-value")){
			var selected=$(el).attr("data-value").split("|");
			for(var i=0;i<selected.length;i++){
				var sel=selected[i]*1;
				if(sel)
					$(el).find("option[value="+sel+"]").prop("selected", "selected");
			}
		}
	});
	$(".m_contragents_type").on("change",function(){
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
	
	$(document).find("input[name=\'m_contragents_tel_numb[]\']").mask("+7 (999) 999-99-99");
	$("#telephones-p .multirow:last").find("select[name=\'m_contragents_tel_type[]\']").select2();
	$("#telephones-c .multirow:last").find("select[name=\'m_contragents_tel_type[]\']").select2();
	$(document).on("keyup","input[name=\'m_contragents_tel_numb[]\']",function(){
		var target=$(this);
		$.get(
			"/ajax/tel_city_code.php",
			{
				tel:target.val()
			},
			function(data){
				if(data!="SHORT_CODE"&&data){
					target.mask(data);}
			}
		);
		return true;
	});
	
	/* АВТОЗАПОЛНЕНИЕ АДРЕСОВ */
	$("#address").df({
		max:20,
		f_a:function(string){
			string.find("[name=\'m_address_area[]\'],[name=\'m_address_district[]\'],[name=\'m_address_city[]\'],[name=\'m_address_street[]\']").sug($(this).attr("suggest"));
			string.find("[name=\'m_address_type[]\']").select2();
			string.find("input").on("change",function(){
			var	myAddress=(string.find("input[name=\'m_address_index[]\']").val()?string.find("input[name=\'m_address_index[]\']").val()+", ":"")+
					(string.find("input[name=\'m_address_area[]\']").val()?string.find("input[name=\'m_address_area[]\']").val():"")+
					(string.find("input[name=\'m_address_district[]\']").val()?(", "+string.find("input[name=\'m_address_district[]\']").val()):"")+
					(string.find("input[name=\'m_address_city[]\']").val()?(", "+string.find("input[name=\'m_address_city[]\']").val()):"")+
					(string.find("input[name=\'m_address_street[]\']").val()?(", "+string.find("input[name=\'m_address_street[]\']").val()):"")+
					(string.find("input[name=\'m_address_house[]\']").val()?(", д. "+string.find("input[name=\'m_address_house[]\']").val()):"")+
					(string.find("input[name=\'m_address_corp[]\']").val()?(", корп. "+string.find("input[name=\'m_address_corp[]\']").val()):"")+
					(string.find("input[name=\'m_address_build[]\']").val()?(", стр. "+string.find("input[name=\'m_address_build[]\']").val()):"")+
					(string.find("input[name=\'m_address_mast[]\']").val()?(", вл. "+string.find("input[name=\'m_address_mast[]\']").val()):"");
				
				string.find("[name=\'m_address_full[]\']").val(myAddress+(string.find("input[name=\'m_address_detail[]\']").val()?(", "+string.find("input[name=\'m_address_detail[]\']").val()):""));
			});
			var prev_autoselect=string.prev().find("select.autoselect");
			if(prev_autoselect.data("select2")==undefined)
				prev_autoselect.select2();
		}
	});	
	
	$(document).on("change","#address input",function(){
		string=$(this).parents(".multirow:first");
		var myAddress=(string.find("input[name=\'m_address_index[]\']").val()?string.find("input[name=\'m_address_index[]\']").val()+", ":"")+
			(string.find("input[name=\'m_address_area[]\']").val()?string.find("input[name=\'m_address_area[]\']").val():"")+
			(string.find("input[name=\'m_address_district[]\']").val()?(", "+string.find("input[name=\'m_address_district[]\']").val()):"")+
			(string.find("input[name=\'m_address_city[]\']").val()?(", "+string.find("input[name=\'m_address_city[]\']").val()):"")+
			(string.find("input[name=\'m_address_street[]\']").val()?(", "+string.find("input[name=\'m_address_street[]\']").val()):"")+
			(string.find("input[name=\'m_address_house[]\']").val()?(", д. "+string.find("input[name=\'m_address_house[]\']").val()):"")+
			(string.find("input[name=\'m_address_corp[]\']").val()?(", корп. "+string.find("input[name=\'m_address_corp[]\']").val()):"")+
			(string.find("input[name=\'m_address_build[]\']").val()?(", стр. "+string.find("input[name=\'m_address_build[]\']").val()):"")+
			(string.find("input[name=\'m_address_mast[]\']").val()?(", вл. "+string.find("input[name=\'m_address_mast[]\']").val()):"");
		
		string.find("[name=\'m_address_full[]\']").val(myAddress+(string.find("input[name=\'m_address_detail[]\']").val()?(", "+string.find("input[name=\'m_address_detail[]\']").val()):""));
	});
	
	$("#active_s2").click();
	
	/* АКТИВАЦИЯ АВТОЗАПОЛОНЕНИЯ ПРИ АКТИВАЦИИ ТАБА */
	$("a[data-toggle=\'tab\']").on("shown.bs.tab",function(e){
		$("[name=\'m_address_area[]\'],[name=\'m_address_district[]\'],[name=\'m_address_city[]\'],[name=\'m_address_street[]\']").sug($(this).attr("suggest"));
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
		
		<div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">	
					
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Новый контрагент</h2>
				</header>

			<!-- widget div-->
			<div>
				<!-- widget content -->
				<div class="widget-body">
					<ul id="myTab1" class="nav nav-tabs bordered">
						<li class="active">
							<a href="#s1" data-toggle="tab"><i class="fa fa-fw fa-lg fa-user">&nbsp;</i>Физическое лицо</a>
						</li>
						<li>
							<a href="#s2" id="active_s2" data-toggle="tab"><i class="fa fa-fw fa-lg fa-briefcase">&nbsp;</i>Юридическое лицо</a>
						</li>
					</ul>

					<div id="myTabContent1" class="tab-content padding-10">
						<div class="tab-pane fade in active" id="s1">
							<form id="clients-personal-add" class="smart-form" method="post">
								<header>
									Основные данные
								</header>
								<fieldset>
                                    <div class="row">
                                        <section class="col col-4">
                                            <label class="label">Дата создания карточки</label>
                                            <label class="input">
                                                <i class="icon-append fa fa-calendar"></i>
                                                <input type="text" name="m_contragents_create_date" class="datepicker" data-mask="99.99.9999">
                                                <input type="hidden" name="m_contragents_create_date">
                                            </label>
                                        </section>
                                        <section class="col col-4">
                                            <label class="label">Дата изменения</label>
                                            <label class="input">
                                                <i class="icon-append fa fa-calendar"></i>
                                                <input type="text" name="m_contragents_change_date" class="datepicker" data-mask="99.99.9999">
                                                <input type="hidden" name="m_contragents_change_date">
                                            </label>
                                        </section>
                                        <section class="col col-4">
                                            <label class="label">Баланс</label>
                                            <label class="input">
                                                <input type="text" name="contragents_balance">
                                            </label>
                                        </section>
                                        <section class="col col-4">
                                            <label class="label">Срок доверия</label>
                                            <label class="input">
                                                <input type="text" name="contragents_trust_period">
                                            </label>
                                        </section>
                                        <section class="col col-4">
                                            <label class="label">Лимит доверия</label>
                                            <label class="input">
                                                <input type="text" name="contragents_trust_limit">
                                            </label>
                                        </section>
                                    </div>
									<div class="row">
										<section class="col col-6">
											<label class="label">ФИО</label>
											<label class="input">
												<i class="icon-append fa fa-user"></i>
												<input type="text" name="m_contragents_p_fio" placeholder="Фамилия Имя Отчество">
											</label>
										</section>
										<section class="col col-6">
											<label class="label">ФИО в родительном падеже</label>
											<label class="input">
												<i class="icon-append fa fa-user"></i>
												<input type="text" name="m_contragents_p_fio_rp" placeholder="заполнится автоматически">
											</label>
										</section>
									</div>
                                    <label class="label">Телефоны</label>
                                    <div  id="telephones-p">
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
                                                <section class="col col-3">
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
										<section class="col col-4">
											<label class="label">Пол</label>
											<div class="inline-group" >
												<label class="radio">
													<input type="radio" name="m_contragents_p_sex" value="женский">
													<i></i>
													Женский
												</label>
												<label class="radio">
													<input type="radio" name="m_contragents_p_sex" value="мужской">
													<i></i>
													Мужской
												</label>
											</div>
										</section>
										<section class="col col-4">
											<label class="label">Дата рождения</label>
											<label class="input">
												<i class="icon-append fa fa-calendar"></i>
												<input type="text" name="m_contragents_p_birthday_" class="datepicker" data-mask="99.99.9999">
												<input type="hidden" name="m_contragents_p_birthday">
											</label>
										</section>
									</div>
									<section>
										<label class="label">Комментарий</label>
										<label class="textarea textarea-resizable"> 										
											<textarea name="m_contragents_comment" rows="3" class="custom-scroll"></textarea> 
										</label>
									</section>
                                    <section>
                                        <label class="label">Адрес для доставки 1</label>
                                        <label class="input">
                                            <input type="text" name="first_adress">
                                        </label>
                                    </section>
                                    <section>
                                        <label class="label">Адрес для доставки 2</label>
                                        <label class="input">
                                            <input type="text" name="second_adress">
                                        </label>
                                    </section>
                                    <div class="row">
                                        <section class="col col-5">
                                            <label class="label">ИНН</label>
                                            <label class="input">
                                                <input type="text" name="inn">
                                            </label>
                                        </section>
                                        <section class="col col-5">
                                            <label class="label">СНИЛС</label>
                                            <label class="input">
                                                <input type="text" name="snils">
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
												<input type="text" name="m_contragents_p_passport_sn" data-mask="9999 999999">
											</label>
										</section>
										<section class="col col-8">
											<label class="label">Кем выдан паспорт</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_contragents_p_passport_v">
											</label>
										</section>
									</div>
									<div class="row">
										<section class="col col-4">
											<label class="label">Дата выдачи паспорта</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_contragents_p_passport_date_" class="datepicker" data-mask="99.99.9999">
												<input type="hidden" name="m_contragents_p_passport_date">
											</label>
										</section>
										<section class="col col-8">
											<label class="label">Адрес регистрации по паспорту</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_contragents_address_j">
											</label>
										</section>
									</div>
									<div class="row">
										<section class="col col-4">
											<label class="label">
												Скан паспорта
											</label>
											<div class="input input-file">
												<span class="button">
													<input type="file" id="file" name="m_clients_personal_passport_scan" onchange="this.parentNode.nextSibling.value=this.value">
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
									<section>
											<label class="label">Адрес для корреспонденции</label>
											<label class="input">
												<i class="icon-append fa fa-columns"></i>
												<input type="text" name="m_contragents_address_p" >
											</label>
									</section>
									<div class="row">
										<section class="col col-4">
											<label class="label">E-mail</label>
											<label class="input">
												<i class="icon-append fa fa-envelope"></i>
												<input type="text" name="m_contragents_email" placeholder="@">
											</label>
										</section>
									</div>
                                    <div class="row">
                                        <section class="col col-4">
                                            <label class="label">Индекс</label>
                                            <label class="input">
                                                <input type="text" name="index">
                                            </label>
                                        </section>
                                        <section class="col col-4">
                                            <label class="label">Область/Край</label>
                                            <label class="input">
                                                <input type="text" name="oblast">
                                            </label>
                                        </section>
                                        <section class="col col-4">
                                            <label class="label">Район</label>
                                            <label class="input">
                                                <input type="text" name="district">
                                            </label>
                                        </section>
                                    </div>
                                    <div class="row">
                                        <section class="col col-4">
                                            <label class="label">Населенный пункт</label>
                                            <label class="input">
                                                <input type="text" name="locality">
                                            </label>
                                        </section>
                                        <section class="col col-4">
                                            <label class="label">Улица</label>
                                            <label class="input">
                                                <input type="text" name="street">
                                            </label>
                                        </section>
                                        <section class="col col-4">
                                            <label class="label">Дом</label>
                                            <label class="input">
                                                <input type="text" name="house">
                                            </label>
                                        </section>
                                    </div>
                                    <div class="row">
                                        <section class="col col-4">
                                            <label class="label">Корпус</label>
                                            <label class="input">
                                                <input type="text" name="housing">
                                            </label>
                                        </section>
                                        <section class="col col-4">
                                            <label class="label">Строение</label>
                                            <label class="input">
                                                <input type="text" name="building">
                                            </label>
                                        </section>
                                        <section class="col col-4">
                                            <label class="label">Квартира/помещение/оффис</label>
                                            <label class="input">
                                                <input type="text" name="apt">
                                            </label>
                                        </section>
                                    </div>
								</fieldset>
								<footer>
									<button type="submit" class="btn btn-primary">
										<i class="fa fa-save"></i>
										Добавить клиента
									</button>
								</footer>
								<input type="hidden" name="action" value="m_clients_personal_add"/>
								<input type="hidden" name="m_contragents_type" value="2"/>
							</form>
						</div>
						<div class="tab-pane fade" id="s2">
							<form id="clients-company-add" class="smart-form" method="post">
								<header>
									Реквизиты
								</header>
								<fieldset>
									<div class="row">
										<section class="col col-6">
											<label class="label">Полное наименование</label>
											<label class="input">
												<input type="text" name="m_contragents_c_name_full" placeholder="Общество с сограниченной ответственностью ...">
											</label>
										</section>
										<section class="col col-6">
											<label class="label">Краткое наименование</label>
											<label class="input">
												<input type="text" name="m_contragents_c_name_short" placeholder="ООО ...">
											</label>
										</section>
									</div>
									<div class="row">
											<section class="col col-3">
												<label class="label">ИНН</label>
												<label class="input">
													<i class="icon-append fa fa-file"></i>
													<input type="text" name="m_contragents_c_inn" placeholder="10 цифр для ООО и 12 для ИП" >
												</label>
											</section>
											<section class="col col-3">
												<label class="label">КПП</label>
												<label class="input">
													<i class="icon-append fa fa-file"></i>
													<input type="text" name="m_contragents_c_kpp" placeholder="для — ИП не заполнять" >
												</label>
											</section>
											<section class="col col-3">
												<label class="label">ОГРН/ОГРНИП</label>
												<label class="input">
													<i class="icon-append fa fa-file"></i>
													<input type="text" name="m_contragents_c_ogrn" placeholder="" >
												</label>
											</section>
											<section class="col col-3">
												<label class="label">&nbsp;</label>
												<label class="checkbox">
													<input type="checkbox" name="m_contragents_c_nds" value="1" checked />
													Плательщик НДС
													<i></i>
												</label>
											</section>
									</div>
									<div class="row">
										<section class="col col-3">
											<label class="label">ОКПО</label>
											<label class="input">
												<i class="icon-append fa fa-file"></i>
												<input type="text" name="m_contragents_c_okpo">
											</label>
										</section>
										<section class="col col-3">
											<label class="label">ОКВЭД</label>
											<label class="input">
												<i class="icon-append fa fa-file"></i>
												<input type="text" name="m_contragents_c_okved">
											</label>
										</section>
										<section class="col col-3">
											<label class="label">ОКАТО</label>
											<label class="input">
												<i class="icon-append fa fa-file"></i>
												<input type="text" name="m_contragents_c_okato">
											</label>
										</section>
										<section class="col col-3">
											<label class="label">ОКТМО</label>
											<label class="input">
												<i class="icon-append fa fa-file"></i>
												<input type="text" name="m_contragents_c_oktmo">
											</label>
										</section>
									</div>
								</fieldset>
								<header>
								Банковские реквизиты
								</header>
								<fieldset>
									<div id="rs">
										<div class="multirow">
											<div class="row">
												<section class="col col-4">
													<label class="label">БИК</label>
													<label class="input">
														<i class="icon-append fa fa-money"></i>
														<input type="text" name="m_contragents_rs_bik[]" placeholder="042..." >
													</label>
												</section>
												<section class="col col-4">
													<label class="label">Расчётный счёт</label>
													<label class="input">
														<i class="icon-append fa fa-money"></i>
														<input type="text" name="m_contragents_rs_rs[]" placeholder="40702810..." >
													</label>
												</section>
												<section class="col col-4">
													<label class="label">&nbsp;</label>
													<label class="checkbox">
														<input type="checkbox" name="m_contragents_rs_main[]" value="1" checked />
														<input type="hidden" name="m_contragents_rs_main[]" value="1" />
														<i></i>
														По умолчанию для документов
													</label>
												</section>
												<section class="col col-5">
													<label class="label">Банк</label>
													<label class="input">
														<i class="icon-append fa fa-money"></i>
														<input type="text" name="m_contragents_rs_bank[]" placeholder="автозаполнение">
													</label>
												</section>
												<section class="col col-4">
													<label class="label">Корреспондентский счёт</label>
													<label class="input">
														<i class="icon-append fa fa-money"></i>
														<input type="text" name="m_contragents_rs_ks[]" placeholder="автозаполнение" >
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
																<a href="javascript:void(0);" class="add">Добавить счёт</a>
															</li>
															<li>
																<a href="javascript:void(0);" class="delete">Удалить счёт</a>
															</li>
														</ul>
													</div>
												</section>
											</div>
										</div>
									</div>
								</fieldset>
								<header>
									Адреса организации
								</header>
								<fieldset>
									<div id="address">
										<div class="multirow" style="border-bottom:1px dotted #ccc;margin-bottom:15px;">
											<section>
												<label class="input">
													<input type="text" name="m_address_full[]" readonly placeholder="Полный адрес" style="border:none;padding:0;font-weight:700;">
												</label>
											</section>
											<div class="row">
												<section class="col col-2">
													<label class="input">
														<input type="text" name="m_address_index[]" placeholder="индекс">
													</label>
												</section>
												<section class="col col-4">
													<label class="input">
														<input type="text" name="m_address_area[]" suggest="area" placeholder="регион">
													</label>
												</section>
												<section class="col col-3">
													<label class="input">
														<input type="text" name="m_address_district[]" suggest="subarea" placeholder="район">
													</label>
												</section>
												<section class="col col-3">
													<label class="input">
														<input type="text" name="m_address_city[]" suggest="city"placeholder="город">
													</label>
												</section>
											</div>
											<div class="row">
												<section class="col col-4">
													<label class="input">
														<input type="text" name="m_address_street[]" suggest="street" placeholder="улица">
													</label>
												</section>
												<section class="col col-sm-2">
													<label class="input">
														<input type="text" name="m_address_house[]" placeholder="дом">
													</label>
												</section>
												<section class="col col-sm-2">
													<label class="input">
														<input type="text" name="m_address_corp[]" placeholder="корпус">
													</label>
												</section>
												<section class="col col-sm-2">
													<label class="input">
														<input type="text" name="m_address_build[]" placeholder="строение">
													</label>
												</section>
												<section class="col col-sm-2">
													<label class="input">
														<input type="text" name="m_address_mast[]" placeholder="владение">
													</label>
												</section>
											</div>
											<div class="row">
												<section class="col col-3">
													<label class="input">
														<input type="text" name="m_address_detail[]" placeholder="кв. / оф. или дополн. данные">
													</label>
												</section>
												<section class="col col-3">
													<label class="input">
														<input type="text" name="m_address_recipient[]" placeholder="получатель">
													</label>
												</section>
												<section class="col col-3">
													<label class="input">
														<select name="m_address_type[]" style="width:100%" class="autoselect">
															<? 
																$addrType=$info->getAddressType();
																foreach($addrType as $_addrType)
																	echo '<option value="'.$_addrType[0]['m_info_address_type_id'].'">'.$_addrType[0]['m_info_address_type_name'].'</option>';
															?>
														</select>
													</label>
												</section>
												<section class="col col-3">
													<div class="btn-group btn-labeled multirow-btn">
														<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
														<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
															<span class="caret"></span>
														</a>
														<ul class="dropdown-menu">
															<li>
																<a href="javascript:void(0);" class="add">Добавить адрес</a>
															</li>
															<li>
																<a href="javascript:void(0);" class="copy">Скопировать адрес</a>
															</li>
															<li>
																<a href="javascript:void(0);" class="delete">Удалить адрес</a>
															</li>
														</ul>
													</div>
												</section>
											</div>
										</div>
									</div>
								</fieldset>
								<header>
									Контактная информация
								</header>
								<fieldset>
									<div class="row">
										<section class="col col-6">
											<label class="label">Электронная почта</label>
											<label class="input">
												<i class="icon-append fa fa-envelope"></i>
												<input type="text" name="m_contragents_email" placeholder="@">
											</label>
										</section>
										<section class="col col-6">
											<label class="label">Сайт</label>
											<label class="input">
												<i class="icon-append fa fa-external-link"></i>
												<input type="text" name="m_contragents_www" placeholder="http://">
											</label>
										</section>
									</div>
									<label class="label">Телефоны</label>
									<div id="telephones-c">
										<div class="multirow">
											<div class="row">
												<section class="col col-3">
													<label class="input">
														<i class="icon-append fa fa-phone"></i>
														<input type="text" name="m_contragents_tel_numb[]" placeholder="номер">
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
												<section class="col col-3">
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
									<section>
										<label class="label">Комментарий</label>
										<label class="textarea textarea-resizable"> 										
											<textarea name="m_contragents_comment" rows="3" class="custom-scroll"></textarea> 
										</label>
									</section>
								</fieldset>
								<header>
									Исполнительный орган (руководитель) и главный бухгалтер
								</header>
								<fieldset>
									<div class="row">
											<section class="col col-4">
												<label class="label">Должность руководителя</label>
												<label class="input">
													<i class="icon-append fa fa-hand-o-up"></i>
													<input type="text" name="m_contragents_c_director_post" placeholder="генеральный директор" >
												</label>
											</section>
											<section class="col col-4">
												<label class="label">ФИО руководителя</label>
												<label class="input">
													<i class="icon-append fa fa-user"></i>
													<input type="text" name="m_contragents_c_director_name" placeholder="Петров Иван Алексеевич" >
												</label>
											</section>
											<section class="col col-4">
												<label class="label">ФИО руководителя в род. падеже</label>
												<label class="input">
													<i class="icon-append fa fa-user"></i>
													<input type="text" name="m_contragents_c_director_name_rp" placeholder="Петрова Ивана Алексеевича" >
												</label>
											</section>
											<section class="col col-4">
												<label class="label">Действует на основании</label>
												<label class="input">
													<i class="icon-append fa fa-pencil-square-o"></i>
													<input type="text" name="m_contragents_c_director_base" placeholder="Устава, доверенности, св-ва" >
												</label>
											</section>
											<section class="col col-4">
												<label class="label">ФИО гл. бухгалтера</label>
												<label class="input">
													<i class="icon-append fa fa-user"></i>
													<input type="text" name="m_contragents_c_bookkeeper_name" placeholder="Петрова Василиса Ивановна" >
												</label>
											</section>
											<section class="col col-4">
												<label class="label">ФИО ответственного</label>
												<label class="input">
													<i class="icon-append fa fa-user"></i>
													<input type="text" name="m_contragents_c_responsible_name" placeholder="Петрова Василиса Ивановна">
												</label>
											</section>
									</div>
								</fieldset>
								<header>
									Грузополучатель
								</header>
								<fieldset>
									<section>
										<select name="m_contragents_consignee" class="autoselect" placeholder="выберите из списка...">
											<option value="0" selected>он же</option>
											<?
												foreach($contragents->getInfo() as $contragents_){
													echo '<option value="'.$contragents_[0]['m_contragents_id'].'">',
														$contragents->getName($contragents_[0]['m_contragents_id']),
													'</option>';
												}
											?>
										</select>
									</section>
								</fieldset>
								<footer>
									<button type="submit" class="btn btn-primary">
										<i class="fa fa-save"></i>
										Сохранить данные
									</button>
								</footer>
								<input type="hidden" name="action" value="m_clients_company_add"/>
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
<script src="http://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
<script src="/js/jquery.suggest_address.js"></script>
<script src="/js/jquery.df.js"></script>