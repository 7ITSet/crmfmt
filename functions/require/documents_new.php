 <?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$info,$documents,$orders,$buh;
if(get('action')=='details'){
												/* РЕДАКТИРОВАНИЕ ДОКУМЕНТА */
$content->setJS('
	
	runAllForms();	
	
	$("#clients-personal-add").validate({
		rules : {
			m_contragents_p_fio : {
				maxlength : 130,
				required : true
			},
			m_contragents_p_sex : {
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
			m_contragents_address_j : {
				maxlength : 180
			},
			m_contragents_address_p : {
				maxlength : 180
			},
			m_contragents_address_f : {
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
				maxlength : 80
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
	
	$("input[name=m_contragents_c_bank_bik]").on("change",function(){
		$.get(
			"/ajax/bik.php",
			{
				bik:$("input[name=m_contragents_c_bank_bik]").val()
			},
			function(data){
				if(data!="ERROR"){
					var firm=JSON.parse(data);
					$("input[name=m_contragents_c_bank_name]").val(firm.suggestions[0].data.name.payment);
					$("input[name=m_contragents_c_bank_ks]").val(firm.suggestions[0].data.correspondent_account);					
				}
			}
		);		
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
					$("input[name=m_contragents_c_kpp]").val(firm.suggestions[0].data.kpp);
					$("input[name=m_contragents_c_ogrn]").val(firm.suggestions[0].data.ogrn);
					$("input[name=m_contragents_c_name_full]").val(firm.suggestions[0].data.name.full_with_opf);
					$("input[name=m_contragents_c_name_short]").val(firm.suggestions[0].value);
					$("input[name=m_contragents_address_j]").val(firm.suggestions[0].data.address.value);
					$("input[name=m_contragents_c_director_post]").val(firm.suggestions[0].data.management.post);
					$("input[name=m_contragents_c_director_name]").val(firm.suggestions[0].data.management.name).triggerHandler("change");
					$("input[name=m_contragents_c_okpo]").val(firm.suggestions[0].data.okpo);
					$("input[name=m_contragents_c_okved]").val(firm.suggestions[0].data.okved);
					$("input[name=m_contragents_c_okato]").val(firm.suggestions[0].data.address.data.okato);
					$("input[name=m_contragents_c_oktmo]").val(firm.suggestions[0].data.address.data.oktmo);
					
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
	
	/* $("input[name=m_contragents_c_bank_bik]").sug({
		ksValue: "m_contragents_c_bank_ks",
		nameValue: "m_contragents_c_bank_name"
	}); */
	
	$(document).find("input[name=\'m_contragents_tel_numb[]\']").mask("+7 (999) 999-99-99");
	
	if($("#select_documents_template").parent().find("a.active").length)
		$("#select_documents_template").html($("#select_documents_template").parent().find("a.active").text()+"&nbsp;&nbsp;<i class=\"fa fa-caret-down\"></i>");
		
	$(document).on("change","td.check input",function(){
		if($(this).prop("checked"))
			$(this).parents("tr:first").addClass("tr-selected");
		else
			$(this).parents("tr:first").removeClass("tr-selected");
	});
		
	$("#m_documents_templates_buh_done").on("click",function(){
		var buh=[];
		$(".m_buh_id").each(function(index,el){
			if($(el).prop("checked"))
				buh.push($(el).val());
		});
		$("[name=m_documents_pays]").val(buh.join("|"));
		$("#paysPopup").modal("hide");
		if($("[name=m_documents_pays]").val())
			$("#showPays").addClass("btn-success").removeClass("btn-primary").html("<i class=\'fa fa-money\'></i>&nbsp;&nbsp;Привязанные платежи");
		else{
			$("#showPays").addClass("btn-primary").removeClass("btn-success").html("<i class=\'fa fa-money\'></i>&nbsp;&nbsp;Привязать платежи к документу");
		}
	});
	
	$("#fileupload").uploadFile({
		url:"/ajax/fileuploader_docs/upload.php",
		acceptFiles:"image/jpeg,image/png,application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.oasis.opendocument.text",
		maxFileCount:500,
		maxFileSize:30*1024*1024,
		onSuccess:function(files,data,xhr,pd){
			data=JSON.parse(data);
			pd.filetype.text("."+data.file.name.split(".").pop());
			pd.filetype.attr("href",data.file.path);
			pd.idVal.val(data.file.id+"."+data.file.name.split(".").pop());
			pd.progressDiv.hide();
			pd.progressDiv.next().show();
		}
	});
	
	$(document).on("click",".ajax-file-upload-remove",function(){
		$(this).parents(".ajax-file-upload-statusbar:first").fadeOut(200,function(){$(this).remove()});
	});
	
	$("[name=m_documents_performer]").on("change",function(){
		if($(this).val()!=3363726835)
			$("[name=m_documents_pdf_none]").prop("checked",true);
		else{
			$("[name=m_documents_pdf_none]").prop("checked",false);}
	});
	
');

$document=$documents->getInfo(get('m_documents_id'));
$files=$document['m_documents_scan']?json_decode($document['m_documents_scan']):array();
$template=$documents->documents_templates[$document['m_documents_templates_id']][0];

$document_pays=explode('|',$document['m_documents_pays']);
?>

<div class="modal fade" id="paysPopup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	  <div class="modal-dialog modal-lg">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h4 class="modal-title" id="myModalLabel">Выбрать платежи</h4>
		  </div>
		  <div class="modal-body">
				<div>	
					<div class="widget-body no-padding">
						<div class="custom-scroll table-responsive" style="height:290px; overflow-y: scroll;">
							<table class="table table-bordered datatable" data-paging="false">
								<thead>
									<tr>
										<th class="order" order="desc"></th>
										<th>Дата</th>
										<th>Исполнитель</th>
										<th>Заказчик</th>
										<th>Сумма</th>
										<th>№ п/п</th>
									</tr>
								</thead>
								<tbody>
									<?
										foreach($buh->getInfo() as $_pays){
											$_pays=$_pays[0];
											$performer=$contragents->getInfo($_pays['m_buh_performer'])['m_contragents_c_name_short']?$contragents->getInfo($_pays['m_buh_performer'])['m_contragents_c_name_short']:$contragents->getInfo($_pays['m_buh_performer'])['m_contragents_p_fio'];
											$customer=$contragents->getInfo($_pays['m_buh_customer'])['m_contragents_c_name_short']?$contragents->getInfo($_pays['m_buh_customer'])['m_contragents_c_name_short']:$contragents->getInfo($_pays['m_buh_customer'])['m_contragents_p_fio'];
											echo '<tr'.(in_array($_pays['m_buh_id'],$document_pays)?' class="tr-selected"':'').'>
													<td class="check" data-sort="'.(in_array($_pays['m_buh_id'],$document_pays)?'1':'0').'">
														<label>
															<div class="checkbox">
															  <label>
																<input type="checkbox" class="checkbox tr m_buh_id" value="'.$_pays['m_buh_id'].'"'.(in_array($_pays['m_buh_id'],$document_pays)?'checked':'').'>
																<span></span>
															  </label>
															</div>
														</label>
													</td>
													<td>'.dtu($_pays['m_buh_date'],'d.m.Y').'</td>
													<td>'.$performer.'</td>
													<td>'.$customer.'</td>
													<td data-order="'.$_pays['m_buh_sum'].'" class="sum">
														<b><nobr><span style="color:#'.($_pays['m_buh_type']==1?'4ab54a">+ ':'fb6262">– ').transform::price_o($_pays['m_buh_sum']).'</span></nobr></b><br/>
														<span style="color:#999;font-size:80%"><nobr>'.($_pays['m_buh_sum_nds']?'в т.ч. НДС 18%: <b>'.transform::price_o($_pays['m_buh_sum_nds']).'</b>':'без НДС').'</nobr></span>
													</td>
													<td>'.$_pays['m_buh_payment_numb'].'</td>
												</tr>';
										}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			<button type="button" id="m_documents_templates_buh_done" class="btn btn-primary">Сохранить изменения</button>
		  </div>
		</div>
	  </div>
</div>

<section id="widget-grid" class="">
<?
if(isset($_GET['success']))
	echo '<div class="row"><article class="col-lg-12">
			<div class="alert alert-success alert-block">
				<a class="close" data-dismiss="alert" href="#">x</a>
				<h4 class="alert-heading">Выполнено!</h4>
				<p style="font-size:16px"><strong><a href="http://'.$_SERVER['HTTP_HOST'].'/documents/new/?m_documents_id='.get('id').'&action=details">Редактировать документ</a></p>
				Информация успешно добавлена!'.(isset($_GET['filepath'])&&$_GET['filepath']?'<p style="font-size:16px"><strong><a href="'.$_GET['filepath'].'" target="_blank">Скачать PDF</a></strong></p>':'').'
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
	<article class="col-lg-6 sortable-grid ui-sortable main-documents">
		<div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">		
				<header>
					<span class="widget-icon"> <i class="fa fa-arrow-down"></i> </span>
					<h2>Редактировать документ</h2>
					<div class="widget-toolbar">
						<div class="btn-group">
							<button class="btn dropdown-toggle btn-xs btn-warning" id="select_documents_template" data-toggle="dropdown">
								Выбор документа&nbsp;&nbsp;<i class="fa fa-caret-down"></i>
							</button>
							<ul class="dropdown-menu pull-right">
								<li><a href="javascript:void(0);" class="documents_types active" data-name="select_documents_template" data-pk="<?=$template['m_documents_templates_id']?>"><?=$template['m_documents_templates_name'].' <span style="color:#bbb">v.'.$template['m_documents_templates_version']?></span></a></li>
							</ul>
						</div>
					</div>
				</header>
			<div>
				<div class="widget-body">
					<form id="documents-add" class="smart-form" method="post" autocomplete="off">
						<header>
							Основные данные
						</header>
						<fieldset>
							<div class="row">
								<section class="col col-3">
									<label class="label">Исполнитель</label>
									<select name="m_documents_performer" class="autoselect" placeholder="выберите из списка...">
										<?
											foreach($contragents->getInfo() as $contragents_){
												echo '<option value="'.$contragents_[0]['m_contragents_id'].'"'.($contragents_[0]['m_contragents_id']==$document['m_documents_performer']?' selected ':'').'>',
													$contragents->getName($contragents_[0]['m_contragents_id']),
												'</option>';
											}
										?>
									</select>
								</section>
								<section class="col col-3">
									<label class="label">Заказчик</label>
									<select name="m_documents_customer" class="autoselect" placeholder="выберите из списка...">
										<?
											foreach($contragents->getInfo() as $contragents_){
												echo '<option value="'.$contragents_[0]['m_contragents_id'].'"'.($contragents_[0]['m_contragents_id']==$document['m_documents_customer']?' selected ':'').'>',
													$contragents->getName($contragents_[0]['m_contragents_id']),
												'</option>';
											}
										?>
									</select>
								</section>
								<section class="col col-3">
									<label class="label">Номер документа</label>
									<label class="input">
										<i class="icon-append fa fa-columns"></i>
										<input type="text" name="m_documents_numb" placeholder="автоматически" value="<?=$document['m_documents_numb'];?>">
									</label>
								</section>
								<section class="col col-3">
									<label class="label">Дата документа</label>
									<label class="input">
										<i class="icon-append fa fa-calendar"></i>
										<input type="text" name="m_documents_date_" class="datepicker" data-mask="99.99.9999" placeholder="<?=dtu('','d.m.Y')?>" value="<?=dtu($document['m_documents_date'],'d.m.Y');?>">
										<input type="hidden" name="m_documents_date">
									</label>
								</section>
							</div>
							<div class="row">
								<section class="col col-3">
									<br/>
									<label class="checkbox">
										<input type="checkbox" name="m_documents_signature" <?=($document['m_documents_signature']?' checked':'')?> value="1"/>
										<i></i>
										Вставить подписи и печати
									</label>
									<label class="checkbox">
										<input type="checkbox" name="m_documents_bar" <?=($document['m_documents_bar']?' checked':'')?> value="1"/>
										<i></i>
										Вставить штрих-код
									</label>
									<label class="checkbox">
										<input type="checkbox" name="m_documents_nds_itog" value="1"<?=($document['m_documents_nds_itog']?' checked':'')?>/>
										<i></i>
										НДС плюсом к сумме
									</label>
									<label class="checkbox">
										<input type="checkbox" name="m_documents_pdf_none" value="1"<?=($document['m_documents_pdf_none']?' checked':'')?>/>
										<i></i>
										Не генерировать PDF
									</label>
								</section>
								<section class="col col-9">
									<label class="label">Комментарий</label>
									<label class="textarea textarea-resizable"> 										
										<textarea name="m_documents_comment" rows="3" class="custom-scroll"><?=$document['m_documents_comment'];?></textarea> 
									</label>
								</section>
							</div>
							<?
								//если выбран тип документа и этот тип нужен для бухгалтерии, показываем поле для привязывания платежей
								if($template&&$template['m_documents_templates_buh']){
							?>
									<section>
										<a href="#" id="showPays" class="btn btn-<?=$document['m_documents_pays']?'success':'primary';?>" data-toggle="modal" data-target="#paysPopup"><i class="fa fa-money"></i>&nbsp;&nbsp;<?=$document['m_documents_pays']?'Привязанные платежи':'Привязать платежи к документу';?></a>
										<input type="hidden" name="m_documents_pays" value="<?=$document['m_documents_pays'];?>"/>
									</section>
							<?		
								}
							?>
						</fieldset>
						<header>
							Сканы документов
						</header>
						<fieldset>
							<section>
								<div id="fileupload"></div>
								<?
									echo '<div class="ajax-file-upload-container docs-upload">';
									foreach($files as $_files)
										echo '
												<div class="ajax-file-upload-statusbar">
													<a class="filetype" href="/files/scan_docs/'.$document['m_documents_id'].'/'.$_files->file.'" target="_blank">.'.explode(".",$_files->file)[1].'</a>
													<div class="fileinfo">
														<div class="ajax-file-upload-filename">'.$_files->size.'</div>
														<a class="ajax-file-upload-remove btn btn-default btn-lg txt-color-red" title="Удалить документ"><i class="fa fa-trash-o"></i></a>
														<input type="hidden" name="m_documents_scan[]" value="'.$_files->file.'">
													</div>
												</div>
										';
									echo '</div>';
								?>
							</section>
						</fieldset>
<?
	require_once('../functions/require/documents__form__add__'.$template['m_documents_templates_id'].'.php');
?>						
					</form>
				</div>
			</div>
		</div>	
	</article>
</div>
</section>
<?
}

													/* НОВЫЙ ДОКУМЕНТ */
else{
$content->setJS('
	
	runAllForms();
	
	
	$("#clients-personal-add").validate({
		rules : {
			m_contragents_p_fio : {
				maxlength : 130,
				required : true
			},
			m_contragents_p_sex : {
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
			m_contragents_address_j : {
				maxlength : 180
			},
			m_contragents_address_p : {
				maxlength : 180
			},
			m_contragents_address_f : {
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
				maxlength : 80
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

	$(".switch-stage button").on("mousedown",function(){
		var active=($(this).hasClass("active")?0:1),
			context=$(this);
		$.post(
			"/ajax/services_change.php",
			{
				name:$(this).attr("data-name"),
				pk:$(this).attr("data-pk"),
				value:active
			},
			function(data){
				//if(data=="true")
					if(context.hasClass("active"))
						context.removeClass("active");
					else
						context.addClass("active");
			}
		);
	});

	
	if($("#select_documents_template").parent().find("a.active").length)
		$("#select_documents_template").html($("#select_documents_template").parent().find("a.active").text()+"&nbsp;&nbsp;<i class=\"fa fa-caret-down\"></i>");
	
	$("#select_documents_template").parent().find("a").on("click",function(){
		window.location="http://'.$_SERVER['SERVER_NAME'].'/documents/new/?m_documents_templates_id="+$(this).attr("data-pk");
	});

	$("#select_order").on("change",function(){
		localStorage.setItem("select_order",$(this).find("option:selected").val());
	});
	
	$("[name=m_orders_active]").on("change",function(){
		$(this).prop("checked")===true?$("tr.inactive").hide():$("tr.inactive").show();
	});
	
	$("td.check input").on("change",function(){
		if($(this).prop("checked"))
			$(this).parents("tr:first").addClass("tr-selected");
		else
			$(this).parents("tr:first").removeClass("tr-selected");
	});
	
	$("#m_documents_templates_buh_done").on("click",function(){
		var buh=[];
		$(".m_buh_id").each(function(index,el){
			if($(el).prop("checked"))
				buh.push($(el).val());
		});
		$("[name=m_documents_pays]").val(buh.join("|"));
		$("#paysPopup").modal("hide");
		if($("[name=m_documents_pays]").val())
			$("#showPays").addClass("btn-success").removeClass("btn-primary").html("<i class=\'fa fa-money\'></i>&nbsp;&nbsp;Привязанные платежи");
		else{
			$("#showPays").addClass("btn-primary").removeClass("btn-success").html("<i class=\'fa fa-money\'></i>&nbsp;&nbsp;Привязать платежи к документу");
		}
	});
	
	$("#fileupload").uploadFile({
		url:"/ajax/fileuploader_docs/upload.php",
		acceptFiles:"image/jpeg,image/png,application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.oasis.opendocument.text",
		maxFileCount:500,
		maxFileSize:30*1024*1024,
		onSuccess:function(files,data,xhr,pd){
			data=JSON.parse(data);
			pd.filetype.text("."+data.file.name.split(".").pop());
			pd.filetype.attr("href",data.file.path);
			pd.idVal.val(data.file.id+"."+data.file.name.split(".").pop());
			pd.progressDiv.hide();
			pd.progressDiv.next().show();
		}
	});
	
	$(document).on("click",".ajax-file-upload-remove",function(){
		$(this).parents(".ajax-file-upload-statusbar:first").fadeOut(200,function(){$(this).remove()});
	});
	
	/* НЕ ГЕНЕРИРОВАТЬ PDF У СТОРОННИХ КОМПАНИЙ */
	$("[name=m_documents_performer]").on("change",function(){
		if($(this).val()!=3363726835)
			$("[name=m_documents_pdf_none]").prop("checked",true);
		else{
			$("[name=m_documents_pdf_none]").prop("checked",false);}
	});

');
?>
<div class="modal fade" id="paysPopup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	  <div class="modal-dialog modal-lg">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h4 class="modal-title" id="myModalLabel">Выбрать платежи</h4>
		  </div>
		  <div class="modal-body">
				<div>	
					<div class="widget-body no-padding">
						<div class="custom-scroll table-responsive" style="height:290px; overflow-y: scroll;">
							<table class="table table-bordered datatable" data-paging="false">
								<thead>
									<tr>
										<th class="order" order="desc"></th>
										<th>Дата</th>
										<th>Исполнитель</th>
										<th>Заказчик</th>
										<th>Сумма</th>
										<th>№ п/п</th>
									</tr>
								</thead>
								<tbody>
									<?
										foreach($buh->getInfo() as $_pays){
											$_pays=$_pays[0];
											$performer=$contragents->getInfo($_pays['m_buh_performer'])['m_contragents_c_name_short']?$contragents->getInfo($_pays['m_buh_performer'])['m_contragents_c_name_short']:$contragents->getInfo($_pays['m_buh_performer'])['m_contragents_p_fio'];
											$customer=$contragents->getInfo($_pays['m_buh_customer'])['m_contragents_c_name_short']?$contragents->getInfo($_pays['m_buh_customer'])['m_contragents_c_name_short']:$contragents->getInfo($_pays['m_buh_customer'])['m_contragents_p_fio'];
											echo '<tr>
													<td class="check" data-sort="0">
														<label>
															<div class="checkbox">
															  <label>
																<input type="checkbox" class="checkbox tr m_buh_id" value="'.$_pays['m_buh_id'].'">
																<span></span>
															  </label>
															</div>
														</label>
													</td>
													<td>'.dtu($_pays['m_buh_date'],'d.m.Y').'</td>
													<td>'.$performer.'</td>
													<td>'.$customer.'</td>
													<td data-order="'.$_pays['m_buh_sum'].'" class="sum">
														<b><nobr><span style="color:#'.($_pays['m_buh_type']==1?'4ab54a">+ ':'fb6262">– ').transform::price_o($_pays['m_buh_sum']).'</span></nobr></b><br/>
														<span style="color:#999;font-size:80%"><nobr>'.($_pays['m_buh_sum_nds']?'в т.ч. НДС 18%: <b>'.transform::price_o($_pays['m_buh_sum_nds']).'</b>':'без НДС').'</nobr></span>
													</td>
													<td>'.$_pays['m_buh_payment_numb'].'</td>
												</tr>';
										}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			<button type="button" id="m_documents_templates_buh_done" class="btn btn-primary">Сохранить изменения</button>
		  </div>
		</div>
	  </div>
</div>
	
<section id="widget-grid" class="">

<?
if(isset($_GET['success']))
	echo '<div class="row"><article class="col-lg-12">
			<div class="alert alert-success alert-block">
				<a class="close" data-dismiss="alert" href="#">x</a>
				<h4 class="alert-heading">Выполнено!</h4>
				<p style="font-size:16px"><strong><a href="http://'.$_SERVER['HTTP_HOST'].'/documents/new/?m_documents_id='.get('id').'&m_documents_templates_id='.get('m_documents_templates_id').'&m_documents_order='.get('m_documents_order').'&action=details">Редактировать документ</a></p>
				'.(isset($_GET['filepath'])&&$_GET['filepath']?'<p style="font-size:16px"><strong><a href="'.$_GET['filepath'].'" target="_blank">Скачать PDF</a></strong></p>':'').'
			</div>
		</article></div>';
if(isset($_GET['copy_success']))
	echo '<div class="row"><article class="col-lg-12">
			<div class="alert alert-success alert-block">
				<a class="close" data-dismiss="alert" href="#">x</a>
				<h4 class="alert-heading">Выполнено!</h4>
				Документ успешно скопирован!
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
	
		<article class="col-lg-6 sortable-grid ui-sortable main-documents">
		
		<div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">	
					
				<header>
					<span class="widget-icon"> <i class="fa fa-arrow-down"></i> </span>
					<h2>Новый документ</h2>
					<div class="widget-toolbar">
						<div class="btn-group">
							<button class="btn dropdown-toggle btn-xs btn-warning" id="select_documents_template" data-toggle="dropdown">
								Выбор документа&nbsp;&nbsp;<i class="fa fa-caret-down"></i>
							</button>
							<ul class="dropdown-menu pull-right">
								<? 
									$t=get('m_documents_templates_id');
									foreach($documents->documents_templates as $_documents_templates){
										echo '<li><a href="javascript:void(0);" class="documents_types'.($t&&isset($documents->documents_templates[$t])&&$t==$_documents_templates[0]['m_documents_templates_id']?' active':'').'" data-name="select_documents_template" data-pk="'.$_documents_templates[0]['m_documents_templates_id'].'">'.$_documents_templates[0]['m_documents_templates_name'].' <span style="color:#bbb">v.'.$_documents_templates[0]['m_documents_templates_version'].'<span></a></li>';
									}
								?>
							</ul>
						</div>
					</div>
					
				</header>

			<!-- widget div-->
			<div>
				<!-- widget content -->
				<div class="widget-body">
					<form id="documents-add" class="smart-form" method="post" autocomplete="off">
						<header>
							Основные данные
						</header>
						<fieldset>
							<div class="row">
								<section class="col col-3">
									<label class="label">Исполнитель</label>
									<select name="m_documents_performer" class="autoselect" placeholder="выберите из списка..." >
										<option value="0">выберите из списка...</option>
										<?
											foreach($contragents->getInfo() as $contragents_){
												echo '<option value="'.$contragents_[0]['m_contragents_id'].'"'.($contragents_[0]['m_contragents_id']==3363726835?' selected ':'').'>',
													$contragents->getName($contragents_[0]['m_contragents_id']),
												'</option>';
											}
										?>
									</select>
								</section>
								<section class="col col-3">
									<label class="label">Заказчик</label>
									<select name="m_documents_customer" class="autoselect" placeholder="выберите из списка..." >
										<option value="0">выберите из списка...</option>
										<?
											foreach($contragents->getInfo() as $contragents_){
												echo '<option value="'.$contragents_[0]['m_contragents_id'].'">',
													$contragents->getName($contragents_[0]['m_contragents_id']),
												'</option>';
											}
										?>
									</select>
								</section>
								<section class="col col-3">
									<label class="label">Номер документа</label>
									<label class="input">
										<i class="icon-append fa fa-columns"></i>
										<input type="text" name="m_documents_numb" placeholder="автоматически">
									</label>
								</section>
								<section class="col col-3">
									<label class="label">Дата документа</label>
									<label class="input">
										<i class="icon-append fa fa-calendar"></i>
										<input type="text" name="m_documents_date_" class="datepicker" data-mask="99.99.9999" placeholder="<?=dtu('','d.m.Y')?>">
										<input type="hidden" name="m_documents_date">
									</label>
								</section>
							</div>
							<div class="row">
								<section class="col col-3">
									<br/>
									<label class="checkbox">
										<input type="checkbox" name="m_documents_signature" value="1"/>
										<i></i>
										Вставить подписи и печати
									</label>
									<label class="checkbox">
										<input type="checkbox" name="m_documents_bar" value="1" checked />
										<i></i>
										Вставить штрих-код
									</label>
									<label class="checkbox">
										<input type="checkbox" name="m_documents_nds_itog" value="1"/>
										<i></i>
										НДС плюсом к сумме
									</label>
									<label class="checkbox">
										<input type="checkbox" name="m_documents_pdf_none" value="1"/>
										<i></i>
										Не генерировать PDF
									</label>
								</section>
								<section class="col col-9">
									<label class="label">Комментарий</label>
									<label class="textarea textarea-resizable"> 										
										<textarea name="m_documents_comment" rows="3" class="custom-scroll"></textarea> 
									</label>
								</section>
							</div>
							<?
								//если выбран тип документа и этот тип нужен для бухгалтерии, показываем поле для привязывания платежей
								if($t&&isset($documents->documents_templates[$t])&&$documents->documents_templates[$t][0]['m_documents_templates_buh']){
							?>
									<section>
										<a href="#" id="showPays" class="btn btn-primary" data-toggle="modal" data-target="#paysPopup"><i class="fa fa-money"></i>&nbsp;&nbsp;Привязать платежи к документу </a>
										<input type="hidden" name="m_documents_pays" />
									</section>
							<?		
								}
							?>
						</fieldset>
						<header>
							Сканы документов
						</header>
						<fieldset>
							<section>
								<div id="fileupload"></div>
								<?
									$foto=array();
									echo '<div class="ajax-file-upload-container docs-upload">';
									foreach($foto as $_foto)
										echo '
												<div class="ajax-file-upload-statusbar">
													<div class="ajax-file-upload-preview-container">
														<a class="fancybox-button" rel="group" href="/foto/portfolio/'.$order['m_orders_id'].'/'.($_foto->file).'_b.jpg">
															<img class="ajax-file-upload-preview" src="/foto/portfolio/'.$order['m_orders_id'].'/'.($_foto->file).'_m.jpg" style="width: auto; height: auto;">
														</a>
													</div>
													<label class="input ajax-file-upload-info" style="margin-top: 8px;">
														<input type="text" name="m_portfolio_foto_item_name[]" class="form-control" placeholder="название" value="'.($_foto->name).'">
													</label>
													<label class="textarea textarea-resizable ajax-file-upload-info">
														<textarea name="m_portfolio_foto_item_description[]" rows="3" class="custom-scroll" placeholder="описание">'.($_foto->description).'</textarea>
													</label>
													<a class="ajax-file-upload-remove btn btn-default btn-xs txt-color-red" title="Удалить документ">
														<i class="fa fa-trash-o"></i>
													</a>
													<input type="hidden" name="m_documents_scan[]" value="'.$_foto->file.'">
												</div>
										';
									echo '</div>';
								?>
							</section>
						</fieldset>
<?
if($t&&isset($documents->documents_templates[$t]))
	require_once('../functions/require/documents__form__add__'.$t.'.php');
?>						

					</form>

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
<link href="/js/plugin/fileuploader_docs/uploadfile.css" rel="stylesheet" />
<script src="/js/plugin/fileuploader_docs/jquery.uploadfile.js"></script>
<link rel="stylesheet" type="text/css" href="/js/plugin/fancybox/jquery.fancybox.css"/>
<link rel="stylesheet" type="text/css" href="/js/plugin/fancybox/helpers/jquery.fancybox-buttons.css"/>
<link rel="stylesheet" type="text/css" href="/js/plugin/fancybox/helpers/jquery.fancybox-thumbs.css"/>
<script type="text/javascript" src="/js/plugin/fancybox/jquery.fancybox.pack.js"></script>
<script type="text/javascript" src="/js/plugin/fancybox/helpers/jquery.mousewheel-3.0.6.pack.js"></script>
<script type="text/javascript" src="/js/plugin/fancybox/helpers/jquery.fancybox-media.js"></script>
<script type="text/javascript" src="/js/plugin/fancybox/helpers/jquery.fancybox-thumbs.js"></script>
<script type="text/javascript" src="/js/plugin/fancybox/helpers/jquery.fancybox-buttons.js"></script>