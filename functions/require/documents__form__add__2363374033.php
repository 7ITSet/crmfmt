<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$services,$info,$products,$documents;

if(get('m_documents_id')){
	$params=json_decode($document['m_documents_params']);
	$categoiries=array();
	foreach($params->items as $k=>$v)
		$categories[]=$k;

	//скидка по заказу
	$discount=$orders->orders_id[$document['m_documents_order']][0]['m_orders_discount'];
														/* РЕДАКТИРОВАНИЕ */
$content->setJS('
	/* ДЕЛАЕМ ПОШИРЕ БЛОК С ФОРМОЙ */
	$(".main-documents").addClass("col-lg-12").removeClass("col-lg-6");
	
	/* АВТОВЫБОР СТОРОН ПРИ ВЫБОРЕ ЗАКАЗА */
	$("[name=\'m_documents_order\']").on("change",function(){
		$.post(
			"/ajax/order_select_contragents.php",
			{
				order:$("[name=\'m_documents_order\'] option:selected").val()
			},
			function(data){
				if(data!="ERROR"){
					data=data.split("|");
					$("[name=\'m_documents_performer\']").select2("val",data[0]);
					$("[name=\'m_documents_customer\']").select2("val",data[1]);
					$("[name=\'m_orders_smeta_services_nds[]\']").select2("val",$("[name=\'m_documents_order\'] option:selected").data("nds"));
				}
				
			}
		)
	});
	
	/* ДОБАВЛЕНИЕ ПОМЕЩЕНИЙ */
	$(document).on("click",".multirow a.copy",function(){
		$(this).parents(".multirow:first").find("select.autoselect").select2("destroy");
	});
	$("#rooms").df({
		max:30,
		f_a:function(string){

			string.find("select.autoselect").select2();
			
			var prev_autoselect=string.prev().find("select.autoselect");
			if(prev_autoselect.data("select2")==undefined)
				prev_autoselect.select2();
			
			/* АВТОЗАПОЛНЕНИЕ ПО НАИМЕНОВАНИЮ УСЛУГИ */
			string.find("input[name=\'m_orders_smeta_services_name[]\']").sug();
			/* ИДЕНТИФИКАТОР ДЛЯ КАЖДОГО ПОМЕЩЕНИЯ */
			string.find("[name=\'idroom[]\']:last").val(Math.floor(Math.random()*10000000)+1);
			/* ДОБАВЛЕНИЕ РАБОТ */
			var id1="id"+Math.floor(Math.random()*10000000)+1;
			string.find(".services:last").attr("id",id1).df({
				max:1000,
				f_a:function(string){
					string.find("input[name=\'m_orders_smeta_services_name[]\']").sug();
					string.find("input[name=\'m_orders_smeta_services_unit[]\']").sug_units();
				}
			});
			
			/* АВТОЗАПОЛНЕНИЕ ЕДИНИЦ ИЗМЕРЕНИЯ НОВЫХ ПОЗИЦИЙ */
			string.find("input[name=\'m_orders_smeta_services_unit[]\']").sug_units();
			
		},
		f_d:function(){
			$(".multirow:first").find("[name=\'m_orders_smeta_services_count[]\']").trigger("change");
		}
	});
	$(".services").each(function(index,el){
		var id="id"+Math.floor(Math.random()*10000000)+1;
		$(el).attr("id",id).df({
			max:1000,
			f_a:function(string){
				string.find("input[name=\'m_orders_smeta_services_name[]\']").sug();
				string.find("input[name=\'m_orders_smeta_services_unit[]\']").sug_units();
				string.find("select.autoselect").select2();
			},
			f_d:function(){
				$("#"+id).parents(".multirow:first").find("[name=\'m_orders_smeta_services_count[]\']").trigger("change");
			}
		});
	});
	
	/* ДОБАВЛЕНИЕ НОВОЙ ПОЗИЦИИ В БАЗУ */
	$(document).on("click",".add-service",function(){
		var string=$(this).parents(".multirow:first");
		if(string.find("[name=\'m_orders_smeta_services_name[]\']").val()&&string.find("[name=\'m_orders_smeta_services_unit_id[]\']").val()&&string.find("[name=\'m_orders_smeta_services_price[]\']").val())
			$.get(
				"/ajax/add_services.php",
				{
					"m_services_name":string.find("[name=\'m_orders_smeta_services_name[]\']").val(),
					"m_services_unit":string.find("[name=\'m_orders_smeta_services_unit_id[]\']").val(),
					"m_services_price_general":string.find("[name=\'m_orders_smeta_services_price[]\']").val(),
					"m_services_contragents_id":$("[name=\'m_documents_performer\']").val(),
				},
				function(data){
					string.find("[name=\'m_orders_smeta_services_id[]\']").val(data);
					string.find("[name=\'m_orders_smeta_table[]\']").val("services")
				}
			);
		else alert("Заполнены не все необходимые поля");
	});
	$(document).on("click",".add-product",function(){
		var string=$(this).parents(".multirow:first");
		if(string.find("[name*=\'m_orders_smeta_services_name[]\']").val()&&string.find("[name*=\'m_orders_smeta_services_unit_id[]\']").val()&&string.find("[name*=\'m_orders_smeta_services_price[]\']").val())
			$.get(
				"/ajax/add_products.php",
				{
					"m_products_name":string.find("[name*=\'m_orders_smeta_services_name[]\']").val(),
					"m_products_unit":string.find("[name*=\'m_orders_smeta_services_unit_id[]\']").val(),
					"m_products_price_general":string.find("[name*=\'m_orders_smeta_services_price[]\']").val(),
					"m_products_contragents_id":$("[name=\'m_documents_performer\']").val(),
				},
				function(data){
					string.find("[name*=\'m_orders_smeta_services_id[]\']").val(data);
					string.find("[name*=\'m_orders_smeta_table[]\']").val("products");
				}
			);
		else alert("Заполнены не все необходимые поля");
	});
	
	/* АВТОЗАПОЛНЕНИЕ ЕДИНИЦ ИЗМЕРЕНИЯ НОВЫХ ПОЗИЦИЙ */
	$("input[name=\'m_orders_smeta_services_unit[]\']").sug_units();
	
	/* НАЗВАНИЕ И ПЛОЩАДЬ КОМНАТЫ В ЗАГОЛОВКЕ */
	$(document).on("keyup","[name=\'m_orders_smeta_room_name[]\']",function(){
		var parent=$(this).parents(".multirow:first");
		parent.find("h2").html(parent.find("[name=\'m_orders_smeta_room_name[]\']").val());
	}); 
	
	/* ТОЛЬКО ЦИФРЫ, ТОЧКА ВМЕСТО ЗАПЯТОЙ */
	$(document).on("keyup","[name=\'m_orders_smeta_services_price[]\'],[name=\'m_orders_smeta_services_sum[]\'],[name=\'m_orders_smeta_room_length[]\'],[name=\'m_orders_smeta_room_weight[]\'],[name=\'m_orders_smeta_room_height[]\'],[name=\'m_orders_smeta_room_square[]\'],[name=\'m_orders_smeta_room_openings_weight[]\'],[name=\'m_orders_smeta_room_openings_height[]\'],[name=\'m_orders_smeta_room_openings_depth[]\'],[name=\'m_orders_smeta_room_openings_length[]\'],[name=\'m_orders_smeta_room_openings_square[]\']",function(){
		$(this).val($(this).val().replace(",","."));
		$(this).val($(this).val().replace(/[^.0-9]/gim,""));
	});
	/* ОКРУГЛЕНИЕ ДО 3-Х ЗНАКОВ ПОСЛЕ ТОЧКИ В ПОЛЯХ С ЧИСЛОВЫМИ ЗНАЧЕНИЯМИ */
	$(document).on("change","[name=\'m_orders_smeta_services_count[]\'],[name=\'m_orders_smeta_services_price[]\'],[name=\'m_orders_smeta_services_sum[]\'],[name=\'m_orders_smeta_room_length[]\'],[name=\'m_orders_smeta_room_weight[]\'],[name=\'m_orders_smeta_room_height[]\'],[name=\'m_orders_smeta_room_square[]\'],[name=\'m_orders_smeta_room_openings_weight[]\'],[name=\'m_orders_smeta_room_openings_height[]\'],[name=\'m_orders_smeta_room_openings_depth[]\'],[name=\'m_orders_smeta_room_openings_length[]\'],[name=\'m_orders_smeta_room_openings_square[]\']",function(){
		$(this).val(($(this).val()*1).toFixed(3));
	});

	/* АВТОЗАПОЛНЕНИЕ ПО НАИМЕНОВАНИЮ УСЛУГИ */
	$("input[name=\'m_orders_smeta_services_name[]\']").sug();
	
	/* ИЗМЕНЕНИЕ СУММЫ ПО УСЛУГЕ ПРИ ИЗМЕНЕНИИ САМОЙ УСЛУГИ, КОЛ-ВА ИЛИ ЦЕНЫ УСЛУГИ */
	$(document).on("change","[name=\'m_orders_smeta_services_name[]\'],[name=\'m_orders_smeta_services_count[]\'],[name=\'m_orders_smeta_services_price[]\'],[name=\'m_orders_smeta_room_openings_type[]\'],[name=m_documents_nds_itog]",function(){
		var parent=$(this).parents(".multirow:first"),sum=0;
		parent.find("[name=\'m_orders_smeta_services_sum[]\']").val((parent.find("[name=\'m_orders_smeta_services_count[]\']").val()*parent.find("[name=\'m_orders_smeta_services_price[]\']").val()).toFixed(2));
		/* обновление промежуточной суммы */
		parent.parent().find("[name=\'m_orders_smeta_services_sum[]\']").each(function(index,el){
			sum+=$(el).val()*1;
		});
		parent.parent().next().find(".rowsum span").text(sum.toFixed(2)+" р.");
		updateAllSum();
	});
	
	/* ОБНОВЛЕНИЕ ОБЩЕЙ СУММЫ */
	function updateAllSum(){
		var sum=0,discount='.(isset($discount)?$discount:0).';
		$(".rowsum span").each(function(index,el){
			sum+=$(el).text().substr(0,($(el).text().length*1-2))*1;
		});
		/* НДС ОБЩИМ ИТОГОМ */
			if($("[name=m_documents_nds_itog]").prop("checked"))
				sum*=1.18;
		$("#allsum span,#allsum_a span").text(sum.toFixed(2)+" р.");
		if(discount){
			$("#allsum_d span").text((sum*discount/100).toFixed(2)+" р.");
			$("#allsum_a span").text((sum-sum*discount/100).toFixed(2)+" р.");
		}
	}
	
	/* БЛОКИРОВАНИЕ ОБНОВЛЕНИЯ ПЛОЩАДИ ПРИ ОБНОВЛЕНИИ РАЗМЕРОВ КОМНАТЫ И ПРОЁМОВ, ЕСЛИ КОЛ-ВО БЫЛО ИЗМЕНЕНО */
	$(document).on("keydown","[name=\'m_orders_smeta_services_count[]\']",function(){
		$(this).addClass("manual-changed").next().val(1);
	});
	
	/* ОЧИСТКА ПОЛЯ С КОЛИЧЕСТВОМ ПРИ ФОКУСЕ И ВОЗВРАТ ЗНАЧЕНИЯ ЕСЛИ ПОЛЕ ОСТАЛОСЬ ПУСТЫМ */
	var active_input_count;
	$(document)
		.on("focus","[name=\'m_orders_smeta_services_count[]\']",function(){
			active_input_count=$(this).val();
			$(this).val("");
		})
		.on("blur","[name=\'m_orders_smeta_services_count[]\']",function(){
			if(!$(this).val())
				$(this).val(active_input_count);
		});
	var active_input_price;
	$(document)
		.on("focus","[name=\'m_orders_smeta_services_price[]\']",function(){
			active_input_price=$(this).val();
			$(this).val("");
		})
		.on("blur","[name=\'m_orders_smeta_services_price[]\']",function(){
			if(!$(this).val())
				$(this).val(active_input_price);
		});
	
	$("#documents-add").on("submit",function(){
		$("#rooms > .multirow").each(function(index,el){
			var idroom=$(el).find("[name=\'idroom[]\']").val();
			$(el).find("input:not([name=\'idroom[]\']),select").each(function(index,el){
				$(el).attr("name",idroom+$(el).attr("name"));
			});
		});
		
		return true;
	});
	
	$("[name=\'m_documents_signature\']").prop("checked",true);
	
	$("[name=\'m_orders_smeta_services_count[]\']").trigger("change");
	
	$("input[type=number]").each(function(index,el){
		$(el).val(Intl.NumberFormat("en",{useGrouping:false,minimumFractionDigits:3}).format($(el).val()));
	});
	$("input[type=number]").on("change",function(){
		$(this).val(Intl.NumberFormat("en",{useGrouping:false,minimumFractionDigits:3}).format($(this).val()));
	});
	
	/* ДОБАВЛЕНИЕ ПОЗИЦИЙ В БАЗУ ТОВАРОВ ПРИ ОТПРАВКЕ ФОРМЫ */
	$("#m_orders_pos_add").on("click",function(){
		/* по всем позициям */
		$("[name*=\'m_orders_smeta_services_name[]\']").each(function(index,el){
			/* если наименование, ед. изм, цена заполнены, но нет ID позиции (новая) */ 
			if($(el).val()&&$(el).parents(".row:first").find("[name*=\'m_orders_smeta_services_unit_id[]\']").val()&&$(el).parents(".row:first").find("[name*=\'m_orders_smeta_services_price[]\']").val()&&!$(el).parents(".row:first").find("[name*=\'m_orders_smeta_services_id[]\']").val()){
				$(el).parents(".row:first").find("a.add-product").trigger("click");
			}		
		});
		$(this).prop("disabled",true);
		$("#m_orders_invoice_add").prop("disabled",false);
	});
	
	updateAllSum();
	
	$(document).on("click",".distribute",function(){
		var string=$(this).parents(".multirow:first"),
			section=$(this).parents(".multirow:eq(1)"),
			sum_distr=string.find("[name*=\'m_orders_smeta_services_sum[]\']").val()*1,
			sum_all=section.find(".rowsum span").text().split(" ")[0]*1-sum_distr,
			sender_index=string.index("form");
		section.find("[name*=\'m_orders_smeta_services_name[]\']").each(function(index,el){
			if(sender_index!=index){
				string.remove();
				var sum_string=$(el).parents(".row:first").find("[name*=\'m_orders_smeta_services_sum[]\']").val()*1,
					sum_string_new=sum_string+(sum_string/sum_all)*sum_distr;
				$(el).parents(".row:first").find("[name*=\'m_orders_smeta_services_price[]\']").val((sum_string_new/$(el).parents(".row:first").find("[name*=\'m_orders_smeta_services_count[]\']").val()).toFixed(2)).trigger("change");
			}
		});
		updateAllSum();
	});
	
');	


?>
<header>
	Параметры счёта
</header>
<fieldset>
	<div class="row">
		<section class="col col-6">
			<label class="label">Заказ</label>
			<select name="m_documents_order" class="autoselect" placeholder="выберите из списка...">
				<option value="0">выберите из списка...</option>
				<?
					foreach($orders->orders_id as $_order){
						$_order=$_order[0];
						echo '<option value="'.$_order['m_orders_id'].'"'.($document['m_documents_order']==$_order['m_orders_id']?' selected ':'').' data-nds="'.$_order['m_orders_nds'].'">',
							$_order['m_orders_name'],
						'</option>';
					}
				?>
			</select>
		</section>
		<section class="col col-15">
			<label class="label">&nbsp;</label>
			<label class="checkbox">
				<input type="checkbox" name="m_orders_smeta_products" value="1" checked />
				<i></i>
				База товаров
			</label>
		</section>
		<section class="col col-15">
			<label class="label">&nbsp;</label>
			<label class="checkbox">
				<input type="checkbox" name="m_orders_smeta_services" value="1" checked />
				<i></i>
				База услуг
			</label>
		</section>
	</div>
	<div class="row">
		<section class="col col-2">
			<label class="label">Срок оплаты счёта</label>
			<label class="input">
				<i class="icon-append fa fa-calendar"></i>
				<input type="text" name="m_invoice_date_expire_" class="datepicker" data-mask="99.99.9999" placeholder="<?=dtu(dtc('','+ 3 weekdays'),'d.m.Y')?>" value="<?=(isset($params->doc_date_expire))?dtu($params->doc_date_expire,'d.m.Y'):''?>">
				<input type="hidden" name="m_invoice_date_expire" value="<?=$params->doc_date_expire?>">
			</label>
		</section>
		<section class="col col-5">
			<label class="label">Важное сообщение</label>
			<label class="input">
				<i class="icon-append fa fa-flash"></i>
				<input type="text" name="m_invoice_attention" value="<?=$params->doc_attention?>">
			</label>
		</section>
		<section class="col col-5">
			<label class="label">Условия поставки</label>
			<label class="textarea textarea-resizable"> 										
				<textarea name="m_invoice_terms" rows="3" class="custom-scroll"><?=$params->doc_terms?></textarea> 
			</label>
		</section>
	</div>
	<div id="rooms">
<?
if(!isset($params->items)||!$params->items){
?>
		<div class="multirow">
			<div class="row">
				<input type="hidden" name="idroom[]" value="<?=mt_rand(1,99999999)?>">
				<section class="col col-1">
					<h2>
						Раздел
					</h2>
				</section>
				<section class="col col-8">
					<label class="input">
						<input type="text" name="m_orders_smeta_room_name[]"  placeholder="название или описание" title="азвание или описание раздела счёта" value="<?=($_item->room->name?$_item->room->name:'Раздел')?>">
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
								<a href="javascript:void(0);" class="add">Добавить раздел</a>
							</li>
							<li>
								<a href="javascript:void(0);" class="delete">Удалить раздел</a>
							</li>
						</ul>
					</div>
				</section>
			</div>

			<div class="row yellow">
				<section>
					<h3>
						Позиции счёта
					</h3>
				</section>
				<div class="services">
					<div class="multirow">
						<div class="row yellow">
							<section class="col col-4-25">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_name[]"  placeholder="вид работ" title="Наименование работ">
									<input type="hidden" name="m_orders_smeta_services_id[]">
									<input type="hidden" name="m_orders_smeta_services_category[]">
									<input type="hidden" name="m_orders_smeta_table[]">
								</label>
							</section>
							<section class="col col-1">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_unit[]" placeholder="ед." >
									<input type="hidden" name="m_orders_smeta_services_unit_id[]" >
								</label>
							</section>
							<section class="col col-1">
								<select name="m_orders_smeta_services_nds[]" class="autoselect" placeholder="выберите из списка...">
									<option value="-1" <?=($orders->orders_id[$document['m_documents_order']][0]['m_orders_nds']==-1?'selected':'')?> >без НДС</option>
									<option value="0" <?=($orders->orders_id[$document['m_documents_order']][0]['m_orders_nds']==0?'selected':'')?> >0%</option>
									<option value="10" <?=($orders->orders_id[$document['m_documents_order']][0]['m_orders_nds']==10?'selected':'')?> >10%</option>
									<option value="18" <?=($orders->orders_id[$document['m_documents_order']][0]['m_orders_nds']==18?'selected':'')?> >18%</option>
									<option value="20" <?=($orders->orders_id[$document['m_documents_order']][0]['m_orders_nds']==20?'selected':'')?>>20%</option>
								</select>
							</section>
							<section class="col col-1-25">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_count[]" style="text-align:right" placeholder="кол-во" title="Необходимое количество">
									<input type="hidden" name="m_orders_smeta_services_manual_changed[]" value="0">
								</label>
							</section>
							<section class="col col-1-25">
								<label class="input">
									<i class="icon-append">р.</i>
									<input type="text" name="m_orders_smeta_services_price[]" style="text-align:right" placeholder="цена" title="Цена за ед. измерения">
								</label>
							</section>
							<section class="col col-1-75">
								<label class="input">
									<i class="icon-append">р.</i>
									<input type="text" name="m_orders_smeta_services_sum[]" style="text-align:right" placeholder="сумма" title="Сумма">
								</label>
							</section>
							<section class="col col-1-5" style="text-align:right">
								<div class="btn-group btn-labeled multirow-btn">
									<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
									<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href="javascript:void(0);" class="add">Добавить позицию</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="add-product">Добавить в базу товаров</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="add-service">Добавить в базу услуг</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="delete">Удалить позицию</a>
										</li>
									</ul>
								</div>
							</section>
						</div>
					</div>
				</div>
				<section>
					<div class="rowsum">
						Итого по разделу: <span>0 р.</span>
					</div>
				</section>
			</div>
		</div>
<?
}
else
	foreach($params->items as $_id=>$_item){
?>
		<div class="multirow">
			<div class="row">
				<input type="hidden" name="idroom[]" value="<?=mt_rand(1,99999999)?>">
				<section class="col col-1">
					<h2>
						<?=($_item->room->name?$_item->room->name:'Раздел')?>
					</h2>
				</section>
				<section class="col col-8">
					<label class="input">
						<input type="text" name="m_orders_smeta_room_name[]"  placeholder="название или описание" title="азвание или описание раздела счёта" value="<?=($_item->room->name?$_item->room->name:'Раздел')?>">
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
								<a href="javascript:void(0);" class="add">Добавить раздел</a>
							</li>
							<li>
								<a href="javascript:void(0);" class="delete">Удалить раздел</a>
							</li>
						</ul>
					</div>
				</section>
			</div>

			<div class="row yellow">
				<section>
					<h3>
						Позиции счёта
					</h3>
				</section>
				<div class="services">
<?
if(!isset($_item->services)||!$_item->services){
?>				
					<div class="multirow">
						<div class="row yellow">
							<section class="col col-4-25">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_name[]" autocomplete="off" placeholder="вид работ" title="Наименование работ">
									<input type="hidden" name="m_orders_smeta_services_id[]">
									<input type="hidden" name="m_orders_smeta_services_category[]">
									<input type="hidden" name="m_orders_smeta_table[]" >
								</label>
							</section>
							<section class="col col-1">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_unit[]" autocomplete="off" placeholder="ед." >
									<input type="hidden" name="m_orders_smeta_services_unit_id[]" >
								</label>
							</section>
							<section class="col col-1">
								<select name="m_orders_smeta_services_nds[]" class="autoselect" placeholder="выберите из списка...">
									<option value="-1" <?=($orders->orders_id[$document['m_documents_order']][0]['m_orders_nds']==-1?'selected':'')?> >без НДС</option>
									<option value="0" <?=($orders->orders_id[$document['m_documents_order']][0]['m_orders_nds']==0?'selected':'')?> >0%</option>
									<option value="10" <?=($orders->orders_id[$document['m_documents_order']][0]['m_orders_nds']==10?'selected':'')?> >10%</option>
									<option value="18" <?=($orders->orders_id[$document['m_documents_order']][0]['m_orders_nds']==18?'selected':'')?> >18%</option>
									<option value="20" <?=($orders->orders_id[$document['m_documents_order']][0]['m_orders_nds']==20?'selected':'')?>>20%</option>
								</select>
							</section>
							<section class="col col-1-25">
								<label class="input">
									<input type="number" step="any" name="m_orders_smeta_services_count[]" style="text-align:right" placeholder="кол-во" title="Необходимое количество">
									<input type="hidden" name="m_orders_smeta_services_manual_changed[]" value="0">
								</label>
							</section>
							<section class="col col-1-25">
								<label class="input">
									<i class="icon-append">р.</i>
									<input type="text" name="m_orders_smeta_services_price[]" style="text-align:right" placeholder="цена" title="Цена за ед. измерения">
								</label>
							</section>
							<section class="col col-1-75">
								<label class="input">
									<i class="icon-append">р.</i>
									<input type="text" name="m_orders_smeta_services_sum[]" style="text-align:right" placeholder="сумма" title="Сумма">
								</label>
							</section>
							<section class="col col-1-5" style="text-align:right">
								<div class="btn-group btn-labeled multirow-btn">
									<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
									<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href="javascript:void(0);" class="add">Добавить позицию</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="add-product">Добавить в базу товаров</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="add-service">Добавить в базу услуг</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="delete">Удалить позицию</a>
										</li>
									</ul>
								</div>
							</section>
						</div>
					</div>
<?
}
else
	foreach($_item->services as $_service){
?>
					<div class="multirow">
						<div class="row yellow">
<?
if($_service->table=='products'){
?>
							<section class="col col-4-25">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_name[]"  placeholder="вид работ" value="<?=$products->products_id[$_service->id][0]['m_products_name']?>" title="Наименование работ">
									<input type="hidden" name="m_orders_smeta_services_id[]" value="<?=$_service->id?>">
									<input type="hidden" name="m_orders_smeta_table[]" value="<?=$_service->table?>">
									<input type="hidden" name="m_orders_smeta_services_category[]" value="<?=$products->products_id[$_service->id][0]['m_products_categories_id']?>">
								</label>
							</section>
							<section class="col col-1">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_unit[]" placeholder="ед." value="<?=$info->getUnitsNoHTML($products->products_id[$_service->id][0]['m_products_unit'])?>">
									<input type="hidden" name="m_orders_smeta_services_unit_id[]" >
								</label>
							</section>
<?
}else{
?>
							<section class="col col-4-25">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_name[]" autocomplete="off" placeholder="вид работ" value="<?=$services->services_id[$_service->id][0]['m_services_name']?>" title="Наименование работ">
									<input type="hidden" name="m_orders_smeta_services_id[]" value="<?=$_service->id?>">
									<input type="hidden" name="m_orders_smeta_table[]" value="<?=$_service->table?>">
									<input type="hidden" name="m_orders_smeta_services_category[]" value="<?=$services->services_id[$_service->id][0]['m_services_categories_id']?>">
								</label>
							</section>
							<section class="col col-1">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_unit[]" autocomplete="off" placeholder="ед." value="<?=$info->getUnitsNoHTML($services->services_id[$_service->id][0]['m_services_unit'])?>">
									<input type="hidden" name="m_orders_smeta_services_unit_id[]" >
								</label>
							</section>
<?}?>
							<section class="col col-1">
								<select name="m_orders_smeta_services_nds[]" class="autoselect" placeholder="выберите из списка...">
									<option value="-1" <?=((!isset($_service->nds)&&$orders->orders_id[$document['m_documents_order']][0]['m_orders_nds']==-1)||(isset($_service->nds)&&$_service->nds==-1)?'selected':'')?> >без НДС</option>
									<option value="0" <?=((!isset($_service->nds)&&$orders->orders_id[$document['m_documents_order']][0]['m_orders_nds']==0)||(isset($_service->nds)&&$_service->nds==0)?'selected':'')?> >0%</option>
									<option value="10" <?=((!isset($_service->nds)&&$orders->orders_id[$document['m_documents_order']][0]['m_orders_nds']==10)||(isset($_service->nds)&&$_service->nds==10)?'selected':'')?> >10%</option>
									<option value="18" <?=((!isset($_service->nds)&&$orders->orders_id[$document['m_documents_order']][0]['m_orders_nds']==18)||(isset($_service->nds)&&$_service->nds==18)?'selected':'')?> >18%</option>
									<option value="20" <?=((!isset($_service->nds)&&$orders->orders_id[$document['m_documents_order']][0]['m_orders_nds']==20)||(isset($_service->nds)&&$_service->nds==20)?'selected':'')?>>20%</option>
								</select>
							</section>
							<section class="col col-1-25">
								<label class="input">
									<input type="number" step="any" name="m_orders_smeta_services_count[]" style="text-align:right" placeholder="кол-во" value="<?=$_service->count?>" title="Необходимое количество">
									<input type="hidden" name="m_orders_smeta_services_manual_changed[]" value="0">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">р.</i>
									<input type="text" name="m_orders_smeta_services_price[]" style="text-align:right" placeholder="цена" value="<?=$_service->price?>" title="Цена за ед. измерения">
								</label>
							</section>
							<section class="col col-1-25">
								<label class="input">
									<i class="icon-append">р.</i>
									<input type="text" name="m_orders_smeta_services_sum[]" style="text-align:right" placeholder="сумма" value="<?=$_service->sum?>" title="Сумма">
								</label>
							</section>
							<section class="col col-1-5" style="text-align:right">
								<div class="btn-group btn-labeled multirow-btn">
									<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
									<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href="javascript:void(0);" class="add">Добавить позицию</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="add-product">Добавить в базу товаров</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="add-service">Добавить в базу услуг</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="distribute">Размазать</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="correction">Скорректировать (для размаза)</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="delete">Удалить позицию</a>
										</li>
									</ul>
								</div>
							</section>
						</div>
					</div>
<?}?>
				</div>
				<section>
					<div class="rowsum">
						Итого по разделу: <span>0 р.</span>
					</div>
				</section>
			</div>
		</div>
<?}?>	
	</div>
	<section>
		<div id="allsum" class="allsum">
			Общий итог: <span>0 р.</span>
		</div>
		<div id="allsum_d" class="allsum">
			Скидка <?=$discount?>%: <span>0 р.</span>
		</div>
		<div id="allsum_a" class="allsum">
			Итого со скидкой: <span>0 р.</span>
		</div>
	</section>
</fieldset>	
<footer>
	<button type="button" class="btn btn-primary" id="m_orders_pos_add" >
		<i class="fa fa-save"></i>
		Сохранить все новые позиции
	</button>
	<button type="submit" class="btn btn-primary" id="m_orders_invoice_add" disabled >
		<i class="fa fa-save"></i>
		Сохранить данные
	</button>
</footer>
<input type="hidden" name="action" value="m_documents_change"/>
<input type="hidden" name="m_documents_templates_id" value="2363374033"/>
<input type="hidden" name="m_documents_id" value="<?=$document['m_documents_id']?>"/>
<input type="hidden" name="m_documents_rs" value="2547852026"/>
<?}
														/* НОВЫЙ */
else{
//скидка по заказу
if(isset($document))
	$discount=$orders->orders_id[$document['m_documents_order']][0]['m_orders_discount'];
$content->setJS('	
	/* ДЕЛАЕМ ПОШИРЕ БЛОК С ФОРМОЙ */
	$(".main-documents").addClass("col-lg-12").removeClass("col-lg-6");
	
	/* АВТОВЫБОР СТОРОН ПРИ ВЫБОРЕ ЗАКАЗА */
	$("[name=\'m_documents_order\']").on("change",function(){
		$.post(
			"/ajax/order_select_contragents.php",
			{
				order:$("[name=\'m_documents_order\'] option:selected").val()
			},
			function(data){
				if(data!="ERROR"){
					data=data.split("|");
					$("[name=\'m_documents_performer\']").select2("val",data[0]);
					$("[name=\'m_documents_customer\']").select2("val",data[1]);
					$("[name=\'m_orders_smeta_services_nds[]\']").select2("val",$("[name=\'m_documents_order\'] option:selected").data("nds"));
				}
			}
		)
	});
	
	/* ДОБАВЛЕНИЕ ПОМЕЩЕНИЙ */
	$(document).on("click",".multirow a.copy",function(){
		$(this).parents(".multirow:first").find("select.autoselect").select2("destroy");
	});
	$("#rooms").df({
		max:30,
		f_a:function(string){

			string.find("select.autoselect").select2();
			
			var prev_autoselect=string.prev().find("select.autoselect");
			if(prev_autoselect.data("select2")==undefined)
				prev_autoselect.select2();
			
			/* АВТОЗАПОЛНЕНИЕ ПО НАИМЕНОВАНИЮ УСЛУГИ */
			string.find("input[name=\'m_orders_smeta_services_name[]\']").sug();
			/* ИДЕНТИФИКАТОР ДЛЯ КАЖДОГО ПОМЕЩЕНИЯ */
			string.find("[name=\'idroom[]\']:last").val(Math.floor(Math.random()*10000000)+1);
			/* ДОБАВЛЕНИЕ РАБОТ */
			var id1="id"+Math.floor(Math.random()*10000000)+1;
			string.find(".services:last").attr("id",id1).df({
				max:1000,
				f_a:function(string){
					string.find("input[name=\'m_orders_smeta_services_name[]\']").sug();
					string.find("input[name=\'m_orders_smeta_services_unit[]\']").sug_units();
				}
			});
			
			/* АВТОЗАПОЛНЕНИЕ ЕДИНИЦ ИЗМЕРЕНИЯ НОВЫХ ПОЗИЦИЙ */
			string.find("input[name=\'m_orders_smeta_services_unit[]\']").sug_units();
		},
		f_d:function(){
			$(".multirow:first").find("[name=\'m_orders_smeta_services_count[]\']").trigger("change");
		}
	});
	$(".services").each(function(index,el){
		var id="id"+Math.floor(Math.random()*10000000)+1;
		$(el).attr("id",id).df({
			max:1000,
			f_a:function(string){
				string.find("input[name=\'m_orders_smeta_services_name[]\']").sug();
				string.find("input[name=\'m_orders_smeta_services_unit[]\']").sug_units();
				string.find("select.autoselect").select2();
			},
			f_d:function(){
				$("#"+id).parents(".multirow:first").find("[name=\'m_orders_smeta_services_count[]\']").trigger("change");
			}
		});
	});
	
	/* ДОБАВЛЕНИЕ НОВОЙ ПОЗИЦИИ В БАЗУ */
	$(document).on("click",".add-service",function(){
		var string=$(this).parents(".multirow:first");
		if(string.find("[name=\'m_orders_smeta_services_name[]\']").val()&&string.find("[name=\'m_orders_smeta_services_unit_id[]\']").val()&&string.find("[name=\'m_orders_smeta_services_price[]\']").val())
			$.get(
				"/ajax/add_services.php",
				{
					"m_services_name":string.find("[name=\'m_orders_smeta_services_name[]\']").val(),
					"m_services_unit":string.find("[name=\'m_orders_smeta_services_unit_id[]\']").val(),
					"m_services_price_general":string.find("[name=\'m_orders_smeta_services_price[]\']").val(),
					"m_services_contragents_id":$("[name=\'m_documents_performer\']").val(),
				},
				function(data){
					string.find("[name=\'m_orders_smeta_services_id[]\']").val(data);
					string.find("[name=\'m_orders_smeta_table[]\']").val("services");
				}
			);
		else alert("Заполнены не все необходимые поля");
	});
	$(document).on("click",".add-product",function(){
		var string=$(this).parents(".multirow:first");
		if(string.find("[name*=\'m_orders_smeta_services_name[]\']").val()&&string.find("[name*=\'m_orders_smeta_services_unit_id[]\']").val()&&string.find("[name*=\'m_orders_smeta_services_price[]\']").val())
			$.get(
				"/ajax/add_products.php",
				{
					"m_products_name":string.find("[name*=\'m_orders_smeta_services_name[]\']").val(),
					"m_products_unit":string.find("[name*=\'m_orders_smeta_services_unit_id[]\']").val(),
					"m_products_price_general":string.find("[name*=\'m_orders_smeta_services_price[]\']").val(),
					"m_products_contragents_id":$("[name=\'m_documents_performer\']").val(),
				},
				function(data){
					string.find("[name*=\'m_orders_smeta_services_id[]\']").val(data);
					string.find("[name*=\'m_orders_smeta_table[]\']").val("products");
				}
			);
		else alert("Заполнены не все необходимые поля");
	});
	
	/* АВТОЗАПОЛНЕНИЕ ЕДИНИЦ ИЗМЕРЕНИЯ НОВЫХ ПОЗИЦИЙ */
	$("input[name=\'m_orders_smeta_services_unit[]\']").sug_units();
	
	/* НАЗВАНИЕ И ПЛОЩАДЬ КОМНАТЫ В ЗАГОЛОВКЕ */
	$(document).on("keyup","[name=\'m_orders_smeta_room_name[]\']",function(){
		var parent=$(this).parents(".multirow:first");
		parent.find("h2").html(parent.find("[name=\'m_orders_smeta_room_name[]\']").val());
	}); 
	
	/* ТОЛЬКО ЦИФРЫ, ТОЧКА ВМЕСТО ЗАПЯТОЙ */
	$(document).on("keyup","[name=\'m_orders_smeta_services_price[]\'],[name=\'m_orders_smeta_services_sum[]\'],[name=\'m_orders_smeta_room_length[]\'],[name=\'m_orders_smeta_room_weight[]\'],[name=\'m_orders_smeta_room_height[]\'],[name=\'m_orders_smeta_room_square[]\'],[name=\'m_orders_smeta_room_openings_weight[]\'],[name=\'m_orders_smeta_room_openings_height[]\'],[name=\'m_orders_smeta_room_openings_depth[]\'],[name=\'m_orders_smeta_room_openings_length[]\'],[name=\'m_orders_smeta_room_openings_square[]\']",function(){
		$(this).val($(this).val().replace(",","."));
		$(this).val($(this).val().replace(/[^.0-9]/gim,""));
	});
	/* ОКРУГЛЕНИЕ ДО 3-Х ЗНАКОВ ПОСЛЕ ТОЧКИ В ПОЛЯХ С ЧИСЛОВЫМИ ЗНАЧЕНИЯМИ */
	$(document).on("change","[name=\'m_orders_smeta_services_count[]\'],[name=\'m_orders_smeta_services_price[]\'],[name=\'m_orders_smeta_services_sum[]\'],[name=\'m_orders_smeta_room_length[]\'],[name=\'m_orders_smeta_room_weight[]\'],[name=\'m_orders_smeta_room_height[]\'],[name=\'m_orders_smeta_room_square[]\'],[name=\'m_orders_smeta_room_openings_weight[]\'],[name=\'m_orders_smeta_room_openings_height[]\'],[name=\'m_orders_smeta_room_openings_depth[]\'],[name=\'m_orders_smeta_room_openings_length[]\'],[name=\'m_orders_smeta_room_openings_square[]\']",function(){
		$(this).val(($(this).val()*1).toFixed(3));
	});

	/* АВТОЗАПОЛНЕНИЕ ПО НАИМЕНОВАНИЮ УСЛУГИ */
	$("input[name=\'m_orders_smeta_services_name[]\']").sug();
	
	/* ИЗМЕНЕНИЕ СУММЫ ПО УСЛУГЕ ПРИ ИЗМЕНЕНИИ САМОЙ УСЛУГИ, КОЛ-ВА ИЛИ ЦЕНЫ УСЛУГИ */
	$(document).on("change","[name=\'m_orders_smeta_services_name[]\'],[name=\'m_orders_smeta_services_count[]\'],[name=\'m_orders_smeta_services_price[]\'],[name=\'m_orders_smeta_room_openings_type[]\'],[name=m_documents_nds_itog]",function(){
		var parent=$(this).parents(".multirow:first"),sum=0;
		parent.find("[name=\'m_orders_smeta_services_sum[]\']").val((parent.find("[name=\'m_orders_smeta_services_count[]\']").val()*parent.find("[name=\'m_orders_smeta_services_price[]\']").val()).toFixed(2));
		/* обновление промежуточной суммы */
		parent.parent().find("[name=\'m_orders_smeta_services_sum[]\']").each(function(index,el){
			sum+=$(el).val()*1;
		});
		parent.parent().next().find(".rowsum span").text(sum.toFixed(2)+" р.");
		updateAllSum();
	});
	
	/* ОБНОВЛЕНИЕ ОБЩЕЙ СУММЫ */
	function updateAllSum(){
		var sum=0,discount='.(isset($discount)?$discount:0).';
		$(".rowsum span").each(function(index,el){
			sum+=$(el).text().substr(0,($(el).text().length*1-2))*1;
		});
		/* НДС ОБЩИМ ИТОГОМ */
		if($("[name=m_documents_nds_itog]").prop("checked"))
			sum*=1.18;
		$("#allsum span,#allsum_a span").text(sum.toFixed(2)+" р.");
		if(discount){
			$("#allsum_d span").text((sum*discount/100).toFixed(2)+" р.");
			$("#allsum_a span").text((sum-sum*discount/100).toFixed(2)+" р.");
		}
	}
	
	/* БЛОКИРОВАНИЕ ОБНОВЛЕНИЯ ПЛОЩАДИ ПРИ ОБНОВЛЕНИИ РАЗМЕРОВ КОМНАТЫ И ПРОЁМОВ, ЕСЛИ КОЛ-ВО БЫЛО ИЗМЕНЕНО */
	$(document).on("keydown","[name=\'m_orders_smeta_services_count[]\']",function(){
		$(this).addClass("manual-changed").next().val(1);
	});
	
	/* ОЧИСТКА ПОЛЯ С КОЛИЧЕСТВОМ ПРИ ФОКУСЕ И ВОЗВРАТ ЗНАЧЕНИЯ ЕСЛИ ПОЛЕ ОСТАЛОСЬ ПУСТЫМ */
	var active_input_count;
	$(document)
		.on("focus","[name=\'m_orders_smeta_services_count[]\']",function(){
			active_input_count=$(this).val();
			$(this).val("");
		})
		.on("blur","[name=\'m_orders_smeta_services_count[]\']",function(){
			if(!$(this).val())
				$(this).val(active_input_count);
		});
	var active_input_price;
	$(document)
		.on("focus","[name=\'m_orders_smeta_services_price[]\']",function(){
			active_input_price=$(this).val();
			$(this).val("");
		})
		.on("blur","[name=\'m_orders_smeta_services_price[]\']",function(){
			if(!$(this).val())
				$(this).val(active_input_price);
		});
	
	$("#documents-add").on("submit",function(){
		$("#rooms > .multirow").each(function(index,el){
			var idroom=$(el).find("[name=\'idroom[]\']").val();
			$(el).find("input:not([name=\'idroom[]\']),select").each(function(index,el){
				$(el).attr("name",idroom+$(el).attr("name"));
			});
		});
		
		return true;
	});
	
	$("[name=\'m_documents_signature\']").prop("checked",true);
	
	$("input[type=number]").each(function(index,el){
		$(el).val(Intl.NumberFormat("en",{useGrouping:false,minimumFractionDigits:3}).format($(el).val()));
	});
	$("input[type=number]").on("change",function(){
		$(this).val(Intl.NumberFormat("en",{useGrouping:false,minimumFractionDigits:3}).format($(this).val()));
	});
	
	/* ДОБАВЛЕНИЕ ПОЗИЦИЙ В БАЗУ ТОВАРОВ ПРИ ОТПРАВКЕ ФОРМЫ */
	$("#m_orders_pos_add").on("click",function(){
		/* по всем позициям */
		$("[name*=\'m_orders_smeta_services_name[]\']").each(function(index,el){
			/* если наименование, ед. изм, цена заполнены, но нет ID позиции (новая) */ 
			if($(el).val()&&$(el).parents(".row:first").find("[name*=\'m_orders_smeta_services_unit_id[]\']").val()&&$(el).parents(".row:first").find("[name*=\'m_orders_smeta_services_price[]\']").val()&&!$(el).parents(".row:first").find("[name*=\'m_orders_smeta_services_id[]\']").val()){
				$(el).parents(".row:first").find("a.add-product").trigger("click");
			}		
		});
		var sender=$(this);
		setTimeout(function(){
			sender.prop("disabled",true);
			$("#m_orders_invoice_add").prop("disabled",false);
		},3000);
	});
	
	$(document).on("click",".distribute",function(){
		var string=$(this).parents(".multirow:first"),
			section=$(this).parents(".multirow:eq(1)"),
			sum_distr=string.find("[name*=\'m_orders_smeta_services_sum[]\']").val()*1,
			sum_all=section.find(".rowsum span").text().split(" ")[0]*1-sum_distr,
			sender_index=string.index("form");
		section.find("[name*=\'m_orders_smeta_services_name[]\']").each(function(index,el){
			if(sender_index!=index){
				string.remove();
				var sum_string=$(el).parents(".row:first").find("[name*=\'m_orders_smeta_services_sum[]\']").val()*1,
					sum_string_new=sum_string+(sum_string/sum_all)*sum_distr;
				$(el).parents(".row:first").find("[name*=\'m_orders_smeta_services_price[]\']").val((sum_string_new/$(el).parents(".row:first").find("[name*=\'m_orders_smeta_services_count[]\']").val()).toFixed(2)).trigger("change");
			}
		});
		updateAllSum();
	});
	
');	

?>
<header>
	Параметры счёта
</header>
<fieldset>
	<div class="row">
		<section class="col col-6">
			<label class="label">Заказ</label>
			<select name="m_documents_order" class="autoselect" placeholder="выберите из списка...">
				<option value="0">выберите из списка...</option>
				<?
					foreach($orders->orders_id as $_order){
						$_order=$_order[0];
						echo '<option value="'.$_order['m_orders_id'].'" data-nds="'.$_order['m_orders_nds'].'">',
							$_order['m_orders_name'],
						'</option>';
					}
				?>
			</select>
		</section>
		<section class="col col-15">
			<label class="label">&nbsp;</label>
			<label class="checkbox">
				<input type="checkbox" name="m_orders_smeta_products" value="1" checked />
				<i></i>
				База товаров
			</label>
		</section>
		<section class="col col-15">
			<label class="label">&nbsp;</label>
			<label class="checkbox">
				<input type="checkbox" name="m_orders_smeta_services" value="1" checked />
				<i></i>
				База услуг
			</label>
		</section>
	</div>
	<div class="row">
		<section class="col col-2">
			<label class="label">Срок оплаты счёта</label>
			<label class="input">
				<i class="icon-append fa fa-calendar"></i>
				<input type="text" name="m_invoice_date_expire_" class="datepicker" data-mask="99.99.9999" placeholder="<?=dtu(dtc('','+ 3 weekdays'),'d.m.Y')?>">
				<input type="hidden" name="m_invoice_date_expire">
			</label>
		</section>
		<section class="col col-5">
			<label class="label">Важное сообщение</label>
			<label class="input">
				<i class="icon-append fa fa-flash"></i>
				<input type="text" name="m_invoice_attention" value="ВНИМАНИЕ! СМЕНИЛИСЬ БАНКОВСКИЕ РЕКВИЗИТЫ!">
			</label>
		</section>
		<section class="col col-5">
			<label class="label">Условия поставки</label>
			<label class="textarea textarea-resizable"> 										
				<textarea name="m_invoice_terms" rows="3" class="custom-scroll"></textarea> 
			</label>
		</section>
	</div>
	<div id="rooms">
		<div class="multirow">
			<div class="row">
				<input type="hidden" name="idroom[]" value="<?=mt_rand(1,99999999)?>">
				<section class="col col-1">
					<h2>
						Раздел
					</h2>
				</section>
				<section class="col col-8">
					<label class="input">
						<input type="text" name="m_orders_smeta_room_name[]"  placeholder="название или описание" title="азвание или описание раздела счёта">
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
								<a href="javascript:void(0);" class="add">Добавить раздел</a>
							</li>
							<li>
								<a href="javascript:void(0);" class="delete">Удалить раздел</a>
							</li>
						</ul>
					</div>
				</section>
			</div>

			<div class="row yellow">
				<section>
					<h3>
						Позиции счёта
					</h3>
				</section>
				<div class="services">
					<div class="multirow">
						<div class="row yellow">
							<section class="col col-4-25">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_name[]"  autocomplete="off" placeholder="вид работ" title="Наименование работ">
									<input type="hidden" name="m_orders_smeta_services_id[]">
									<input type="hidden" name="m_orders_smeta_table[]">
									<input type="hidden" name="m_orders_smeta_services_category[]">
								</label>
							</section>
							<section class="col col-1">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_unit[]" placeholder="ед." autocomplete="off">
									<input type="hidden" name="m_orders_smeta_services_unit_id[]" >
								</label>
							</section>
							<section class="col col-1">
								<select name="m_orders_smeta_services_nds[]" class="autoselect" placeholder="выберите из списка...">
									<option value="-1">без НДС</option>
									<option value="0">0%</option>
									<option value="10">10%</option>
									<option value="18" selected>18%</option>
									<option value="20">20%</option>
								</select>
							</section>
							<section class="col col-1-25">
								<label class="input">
									<input type="number" step="any" name="m_orders_smeta_services_count[]" style="text-align:right" placeholder="кол-во" title="Необходимое количество">
									<input type="hidden" name="m_orders_smeta_services_manual_changed[]" value="0">
								</label>
							</section>
							<section class="col col-1-25">
								<label class="input">
									<i class="icon-append">р.</i>
									<input type="text" name="m_orders_smeta_services_price[]" style="text-align:right" placeholder="цена" title="Цена за ед. измерения">
								</label>
							</section>
							<section class="col col-1-75">
								<label class="input">
									<i class="icon-append">р.</i>
									<input type="text" name="m_orders_smeta_services_sum[]" style="text-align:right" placeholder="сумма" title="Сумма">
								</label>
							</section>
							<section class="col col-1-5" style="text-align:right">
								<div class="btn-group btn-labeled multirow-btn">
									<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
									<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href="javascript:void(0);" class="add">Добавить позицию</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="add-product">Добавить в базу товаров</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="add-service">Добавить в базу услуг</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="distribute">Размазать</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="correction">Скорректировать (для размаза)</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="delete">Удалить позицию</a>
										</li>
									</ul>
								</div>
							</section>
						</div>
					</div>
				</div>
				<section>
					<div class="rowsum">
						Итого по разделу: <span>0 р.</span>
					</div>
				</section>
			</div>
		</div>
	</div>
	<section>
		<div id="allsum" class="allsum">
			Общий итог: <span>0 р.</span>
		</div>
	</section>
</fieldset>	
<footer>
	<button type="button" class="btn btn-primary" id="m_orders_pos_add">
		<i class="fa fa-save"></i>
		Сохранить все новые позиции
	</button>
	<button type="submit" class="btn btn-primary" id="m_orders_invoice_add" disabled >
		<i class="fa fa-save"></i>
		Сохранить данные
	</button>
</footer>
<input type="hidden" name="action" value="m_documents_add"/>
<input type="hidden" name="m_documents_templates_id" value="2363374033"/>
<input type="hidden" name="m_documents_rs" value="2547852026"/>
<?}?>
<script src="/js/jquery.df.js"></script>
<script src="/js/jquery.suggest_services_products.js"></script>
<script src="/js/jquery.suggest_units.js"></script>