<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$workers,$info,$services,$contragents,$orders,$documents;

if(get('action')=='details'){
	
$categories=array();
$services->categories_childs(0,$categories,2,0,true);
$t=array();
foreach($categories as $k=>$v)
	$t[$v['m_services_categories_id']]=$v;
$categories=$t;


$order=$orders->orders_id[get('m_orders_id')][0];
//удаляем временную директорию фоток пользователя
echo file::deldir($_SERVER['DOCUMENT_ROOT'].'/temp/uploads/'.$user->getInfo());

$foto=json_decode($order['m_orders_foto']);
$content->setJS('
	
	runAllForms();
	
	$("#order-add").validate({
		rules : {
			m_orders_name : {
				maxlength : 80,
				required : true
			},
			m_orders_m_contragents_id_in : {
				required : true
			},
			m_orders_m_contragents_id_out : {
				required : true
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});
			
	$("input[name=m_orders_address_area], input[name=m_orders_address_district], input[name=m_orders_address_city], input[name=m_orders_address_street]").sug($(this).attr("suggest"));
		
	$("#order-items").df({
		max:500,
		f_a:function(){
			
			$("#order-items .multirow:last").find("select[name=\'m_contragents_tel_type[]\']").select2();
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
		
		<div class="jarviswidget" id="wid-id-11" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">	
			<header>
				<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
				<h2>Редактировать заказ</h2>
			</header>
			<div>
				<div class="widget-body">
					<form id="order-change2" class="smart-form" method="post">							
						<header>
							Основные данные
						</header>
						<fieldset>
							<div class="row">
								<section class="col col-9">
									<label class="label">Наименование</label>
									<label class="input">
										<i class="icon-append fa fa-user"></i>
										<input type="text" name="m_orders_name" value="<?=$order['m_orders_name']?>">
									</label>
								</section>
								<section class="col col-3">
									<label class="label">Дата заказа</label>
									<label class="input">
										<i class="icon-append fa fa-calendar"></i>
										<input type="text" name="m_orders_date_" class="datepicker" data-mask="99.99.9999" placeholder="<?=dtu('','d.m.Y')?>" value="<?=dtu($order['m_orders_date'],'d.m.Y');?>">
										<input type="hidden" name="m_orders_date">
									</label>
								</section>
							</div>
							<div class="row">
								<section class="col col-3">
									<label class="label">Заказчик</label>
									<select name="m_orders_customer" class="autoselect" placeholder="выберите из списка...">
										<?
											foreach($contragents->getInfo() as $contragents_){
												$ct=explode('|',$contragents_[0]['m_contragents_type']);
													echo '<option value="'.$contragents_[0]['m_contragents_id'].'" '.($contragents_[0]['m_contragents_id']==$order['m_orders_customer']?'selected ':'').'>',
														$contragents->getName($contragents_[0]['m_contragents_id']),
													'</option>';
											}
										?>
									</select>
								</section>
								<section class="col col-3">
									<label class="label">Исполнитель</label>
									<select name="m_orders_performer" class="autoselect" placeholder="выберите из списка...">
										<?
											foreach($contragents->getInfo() as $contragents_){
												$ct=explode('|',$contragents_[0]['m_contragents_type']);
													echo '<option value="'.$contragents_[0]['m_contragents_id'].'" '.($contragents_[0]['m_contragents_id']==$order['m_orders_performer']?'selected ':'').'>',
														$contragents_[0]['m_contragents_c_name_short']?$contragents_[0]['m_contragents_c_name_short']:$contragents_[0]['m_contragents_c_name_full'],
													'</option>';
											}
										?>
									</select>
								</section>
								<section class="col col-2">
									<label class="label">Скидка</label>
									<label class="input">
										<i class="icon-append">%</i>
										<input type="text" name="m_orders_discount" placeholder="0" style="text-align:right" value="<?=$order['m_orders_discount']?>">
									</label>
								</section>
								<section class="col col-2">
									<label class="label">&nbsp;</label>
									<label class="checkbox">
										<input type="checkbox" name="m_orders_nds" value="1" <?=($order['m_orders_nds']!=-1?' checked':'')?> />
										<i></i>
										НДС 18%
									</label>
								</section>
							</div>
							<section>
								<label class="label">Комментарий</label>
								<label class="textarea textarea-resizable"> 										
									<textarea name="m_orders_comment" rows="3" class="custom-scroll"><?=$order['m_orders_comment']?></textarea> 
								</label>
							</section>
						</fieldset>
						<header>
							Адрес объекта или доставки
						</header>
						<fieldset id="order-address-fields">
							<div class="row">
								<section class="col col-4">
									<label class="input">
										<input type="text" name="m_orders_address_area" suggest="area" class="form-control" placeholder="регион" value="<?=$order['m_orders_address_area']?>">
									</label>
								</section>
								<section class="col col-4">
									<label class="input">
										<input type="text" name="m_orders_address_district" suggest="subarea" class="form-control" placeholder="район" value="<?=$order['m_orders_address_district']?>">
									</label>
								</section>
								<section class="col col-4">
									<label class="input">
										<input type="text" name="m_orders_address_city" suggest="city" class="form-control" placeholder="город" value="<?=$order['m_orders_address_city']?>">
									</label>
								</section>
							</div>
							<div class="row">
								<section class="col col-4">
									<label class="input">
										<input type="text" name="m_orders_address_street" suggest="street" class="form-control" placeholder="улица" value="<?=$order['m_orders_address_street']?>">
									</label>
								</section>
								<section class="col col-sm-2">
									<label class="input">
										<input type="text" name="m_orders_address_house" class="form-control" placeholder="дом" value="<?=$order['m_orders_address_house']?>">
									</label>
								</section>
								<section class="col col-sm-2">
									<label class="input">
										<input type="text" name="m_orders_address_corp" class="form-control" placeholder="корпус" value="<?=$order['m_orders_address_corp']?>">
									</label>
								</section>
								<section class="col col-sm-2">
									<label class="input">
										<input type="text" name="m_orders_address_build" class="form-control" placeholder="строение" value="<?=$order['m_orders_address_build']?>">
									</label>
								</section>
								<section class="col col-sm-2">
									<label class="input">
										<input type="text" name="m_orders_address_mast" class="form-control" placeholder="владение" value="<?=$order['m_orders_address_mast']?>">
									</label>
								</section>
							</div>
							<section>
								<label class="input">
									<input type="text" name="m_orders_address_office" class="form-control" placeholder="квартира / офис или дополнительные данные (ТЦ, остановка, …)" value="<?=$order['m_orders_address_office']?>">
								</label>
							</section>
						</fieldset>
						<footer>
							<button type="submit" class="btn btn-primary">
								<i class="fa fa-save"></i>
								Сохранить данные заказа
							</button>
						</footer>
						<input type="hidden" name="m_orders_address_full" value="<?=$order['m_orders_address_full']?>"/>
						<input type="hidden" name="m_orders_address_map_lat" value="<?=$order['m_orders_address_map_lat']?>"/>
						<input type="hidden" name="m_orders_address_map_lon" value="<?=$order['m_orders_address_map_lon']?>"/>
						<input type="hidden" name="m_orders_address_map_type" value="<?=$order['m_orders_address_map_type']?>"/>
						<input type="hidden" name="m_orders_address_map_zoom" value="<?=$order['m_orders_address_map_zoom']?>"/>
						<input type="hidden" name="m_orders_id" value="<?=$order['m_orders_id']?>"/>
						<input type="hidden" name="action" value="m_orders_change"/>
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
else{
$content->setJS('
	
	runAllForms();
	
	$("#order-add").validate({
		rules : {
			m_orders_name : {
				maxlength : 80,
				required : true
			},
			m_orders_m_contragents_id_in : {
				required : true
			},
			m_orders_m_contragents_id_out : {
				required : true
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});
			
	$("input[name=m_orders_address_area], input[name=m_orders_address_district], input[name=m_orders_address_city], input[name=m_orders_address_street]").sug($(this).attr("suggest"));
	
	$("#order-address-fields").find("input").on("change",function(){
		myAddress=($("input[name=m_orders_address_area]").val()?$("input[name=m_orders_address_area]").val():"")+
			($("input[name=m_orders_address_district]").val()?(", "+$("input[name=m_orders_address_district]").val()):"")+
			($("input[name=m_orders_address_city]").val()?(", "+$("input[name=m_orders_address_city]").val()):"")+
			($("input[name=m_orders_address_street]").val()?(", "+$("input[name=m_orders_address_street]").val()):"")+
			($("input[name=m_orders_address_house]").val()?(", д. "+$("input[name=m_orders_address_house]").val()):"")+
			($("input[name=m_orders_address_corp]").val()?(", корп. "+$("input[name=m_orders_address_corp]").val()):"")+
			($("input[name=m_orders_address_build]").val()?(", стр. "+$("input[name=m_orders_address_build]").val()):"")+
			($("input[name=m_orders_address_mast]").val()?(", вл. "+$("input[name=m_orders_address_mast]").val()):"");
		
		$("#order-address").text(myAddress+($("input[name=m_orders_address_office]").val()?(", "+$("input[name=m_orders_address_office]").val()):""));
		$("input[name=m_orders_address_full]").val($("#order-address").text());
		
	});
	
	var status_items;
	$(document).on("click",".status li",function(){
		
		if($(this).hasClass("actual")){
			$.post(
				"/ajax/services_change.php",
				{
					name:"m_orders_status",
					pk:$(this).parents("tr:first").find("td:eq(0) span:eq(1)").text().substr(4),
					value:$(this).index()+2
				}
			);
			$(this).addClass("done").removeClass("actual");
			$(this).next("li").addClass("actual");
			if(($(this).index()==$(this).parent().find("li:last").index()-1)||($(this).index()==$(this).parent().find("li:last").index())){
				status_items=$(this).parent();
				$(this).parent().parent().append("<span class=\"done-all\">ВЫПОЛНЕН</span>");
				$(this).parent().remove();
			}
		}
		else{
			$.post(
				"/ajax/services_change.php",
				{
					name:"m_orders_status",
					pk:$(this).parents("tr:first").find("td:eq(0) span:eq(1)").text().substr(4),
					value:$(this).index()+1
				}
			);
			if($(this).hasClass("done")){
				$(this).removeClass("done").addClass("actual");
				$(this).nextAll("li").removeClass("actual done");
			}
			else{
				$(this).addClass("actual");
				$(this).prevAll("li").removeClass("actual").addClass("done");
				if($(this).index()==$(this).parent().find("li:last").index()){
					$(this).removeClass("actual").addClass("done");
					status_items=$(this).parent();
					$(this).parent().parent().append("<span class=\"done-all\">ВЫПОЛНЕН</span>");
					$(this).parent().remove();
				}
			}
		}
	});
	$(document).on("click",".done-all",function(){
		$(this).parent().append(status_items.clone());
		$(this).remove();
	});
	
	$(".status").each(function(index,el){
		if($(el).find("li:last").hasClass("actual")||$(el).find("li:last").hasClass("done"))
			$(el).find("li:last").trigger("click");
	});
	
	$(".datatable .delete").editable({
		url: "/ajax/services_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete")){
				$(this).parents("tr:first").remove();
			}
		}
	});
	
	$("[name=m_orders_active]").on("change",function(){
		$(this).prop("checked")===true?$("tr.inactive").hide():$("tr.inactive").show();
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
	
		<article class="col-lg-6 sortable-grid ui-sortable">
		
		<div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">	
			<header>
				<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
				<h2>Новый заказ</h2>
			</header>
			<div>
				<div class="widget-body">
					<form id="order-add" class="smart-form" method="post">			
						<header>
							Основные данные
						</header>
						<fieldset>
							<div class="row">
								<section class="col col-9">
									<label class="label">Наименование</label>
									<label class="input">
										<i class="icon-append fa fa-user"></i>
										<input type="text" name="m_orders_name">
									</label>
								</section>
								<section class="col col-3">
									<label class="label">Дата заказа</label>
									<label class="input">
										<i class="icon-append fa fa-calendar"></i>
										<input type="text" name="m_orders_date_" class="datepicker" data-mask="99.99.9999" placeholder="<?=dtu('','d.m.Y')?>">
										<input type="hidden" name="m_orders_date">
									</label>
								</section>
							</div>
							<div class="row">
								<section class="col col-3">
									<label class="label">Заказчик</label>
									<select name="m_orders_customer" class="autoselect" placeholder="выберите из списка...">
										<?
											foreach($contragents->getInfo() as $contragents_){
												$ct=explode('|',$contragents_[0]['m_contragents_type']);
													echo '<option value="'.$contragents_[0]['m_contragents_id'].'">',
														$contragents->getName($contragents_[0]['m_contragents_id']),
													'</option>';
											}
										?>
									</select>
								</section>
								<section class="col col-3">
									<label class="label">Исполнитель</label>
									<select name="m_orders_performer" class="autoselect" placeholder="выберите из списка...">
										<?
											foreach($contragents->getInfo() as $contragents_){
												$ct=explode('|',$contragents_[0]['m_contragents_type']);
													echo '<option value="'.$contragents_[0]['m_contragents_id'].'" '.($contragents_[0]['m_contragents_id']=='3363726835'?'selected ':'').'>',
														$contragents_[0]['m_contragents_c_name_short']?$contragents_[0]['m_contragents_c_name_short']:$contragents_[0]['m_contragents_c_name_full'],
													'</option>';
											}
										?>
									</select>
								</section>
								<section class="col col-2">
									<label class="label">Скидка</label>
									<label class="input">
										<i class="icon-append">%</i>
										<input type="text" name="m_orders_discount" placeholder="0" style="text-align:right" value="">
									</label>
								</section>
								<section class="col col-3">
									<label class="label">&nbsp;</label>
									<label class="checkbox">
										<input type="checkbox" name="m_orders_nds" value="1" checked />
										<i></i>
										НДС 18%
									</label>
								</section>
							</div>
							<section>
								<label class="label">Комментарий</label>
								<label class="textarea textarea-resizable"> 										
									<textarea name="m_orders_comment" rows="3" class="custom-scroll"></textarea> 
								</label>
							</section>
						</fieldset>
						<header>
							Адрес объекта или доставки
						</header>
						<fieldset id="order-address-fields">
							<div class="row">
								<section class="col col-4">
									<label class="input">
										<input type="text" name="m_orders_address_area" suggest="area" class="form-control" placeholder="регион">
									</label>
								</section>
								<section class="col col-4">
									<label class="input">
										<input type="text" name="m_orders_address_district" suggest="subarea" class="form-control" placeholder="район">
									</label>
								</section>
								<section class="col col-4">
									<label class="input">
										<input type="text" name="m_orders_address_city" suggest="city" class="form-control" placeholder="город">
									</label>
								</section>
							</div>
							<div class="row">
								<section class="col col-4">
									<label class="input">
										<input type="text" name="m_orders_address_street" suggest="street" class="form-control" placeholder="улица">
									</label>
								</section>
								<section class="col col-sm-2">
									<label class="input">
										<input type="text" name="m_orders_address_house" class="form-control" placeholder="дом">
									</label>
								</section>
								<section class="col col-sm-2">
									<label class="input">
										<input type="text" name="m_orders_address_corp" class="form-control" placeholder="корпус">
									</label>
								</section>
								<section class="col col-sm-2">
									<label class="input">
										<input type="text" name="m_orders_address_build" class="form-control" placeholder="строение">
									</label>
								</section>
								<section class="col col-sm-2">
									<label class="input">
										<input type="text" name="m_orders_address_mast" class="form-control" placeholder="владение">
									</label>
								</section>
							</div>
							<section>
								<label class="input">
									<input type="text" name="m_orders_address_office" class="form-control" placeholder="квартира / офис или дополнительные данные (ТЦ, остановка, …)">
								</label>
							</section>
						</fieldset>
						<footer>
							<button type="submit" class="btn btn-primary">
								<i class="fa fa-save"></i>
								Добавить заказ
							</button>
						</footer>
						<input type="hidden" name="m_orders_address_full" value=""/>
						<input type="hidden" name="m_orders_address_map_lat" value="57.004751"/>
						<input type="hidden" name="m_orders_address_map_lon" value="40.978041"/>
						<input type="hidden" name="m_orders_address_map_type" value="yandex#map"/>
						<input type="hidden" name="m_orders_address_map_zoom" value="10"/>
						<input type="hidden" name="action" value="m_orders_add"/>
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
<script src="/js/jquery.df.js"></script>
<script src="/js/jquery.suggest_address.js"></script>
<link href="/js/plugin/fileuploader/uploadfile.css" rel="stylesheet" />
<script src="/js/plugin/fileuploader/jquery.uploadfile.js"></script>
<link rel="stylesheet" type="text/css" href="/js/plugin/fancybox/jquery.fancybox.css"/>
<link rel="stylesheet" type="text/css" href="/js/plugin/fancybox/helpers/jquery.fancybox-buttons.css"/>
<link rel="stylesheet" type="text/css" href="/js/plugin/fancybox/helpers/jquery.fancybox-thumbs.css"/>
<script type="text/javascript" src="/js/plugin/fancybox/jquery.fancybox.pack.js"></script>
<script type="text/javascript" src="/js/plugin/fancybox/helpers/jquery.mousewheel-3.0.6.pack.js"></script>
<script type="text/javascript" src="/js/plugin/fancybox/helpers/jquery.fancybox-media.js"></script>
<script type="text/javascript" src="/js/plugin/fancybox/helpers/jquery.fancybox-thumbs.js"></script>
<script type="text/javascript" src="/js/plugin/fancybox/helpers/jquery.fancybox-buttons.js"></script>
<script src="/js/plugin/tinymce/tinymce.min.js"></script>