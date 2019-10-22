<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$services,$info,$products,$documents;

if(get('m_documents_id')){
	$params=json_decode($document['m_documents_params']);
	$categoiries=array();
	foreach($params->items as $k=>$v)
		$categories[]=$k;
	$document=$documents->getInfo(get('m_documents_id'));
	$params=json_decode($document['m_documents_params']);
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
			/* ДОБАВЛЕНИЕ ПРОЁМОВ */
			var id="id"+Math.floor(Math.random()*10000000)+1;
			string.find(".openings:last").attr("id",id).df({
				max:1000,
				f_a:function(string){
					string.find("select.autoselect").select2();				
				},
				/* ОБНОВЛЕНИЕ РАСЧЁТОВ ПРИ УДАЛЕНИИ ПРОЁМА */
				f_d:function(){
					$("#"+id).parents(".multirow:first").find("[name=\'m_orders_smeta_services_name[]\']").trigger("change");
				}
			});
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
				}
			});
		},
		f_d:function(){
			$(".multirow:first").find("[name=\'m_orders_smeta_services_count[]\']").trigger("change");
		}
	});
	$(".openings").each(function(index,el){
		var id="id"+Math.floor(Math.random()*10000000)+1;
		$(el).attr("id",id).df({
			max:1000,
			f_a:function(string){
				string.find("select.autoselect").select2("destroy").select2();
			},
			f_d:function(){
				$("#"+id).parents(".multirow:first").find("[name=\'m_orders_smeta_services_name[]\']").trigger("change");
			}
		});
	});
	$(".services").each(function(index,el){
		var id="id"+Math.floor(Math.random()*10000000)+1;
		$(el).attr("id",id).df({
			max:1000,
			f_a:function(string){
				string.find("input[name=\'m_orders_smeta_services_name[]\']").sug();
			},
			f_d:function(){
				$("#"+id).parents(".multirow:first").find("[name=\'m_orders_smeta_services_count[]\']").trigger("change");
			}
		});
	});
	
	/* НАЗВАНИЕ И ПЛОЩАДЬ КОМНАТЫ В ЗАГОЛОВКЕ */
	$(document).on("keyup","[name=\'m_orders_smeta_room_name[]\'],[name=\'m_orders_smeta_room_square[]\']",function(){
		var parent=$(this).parents(".multirow:first");
		parent.find("h2").html(parent.find("[name=\'m_orders_smeta_room_name[]\']").val()+(parent.find("[name=\'m_orders_smeta_room_square[]\']").val()?" — "+parent.find("[name=\'m_orders_smeta_room_square[]\']").val()+" м<sup>2</sup>":""));
	}); 
	
	/* ТОЛЬКО ЦИФРЫ, ТОЧКА ВМЕСТО ЗАПЯТОЙ */
	$(document).on("keyup","[name=\'m_orders_smeta_services_count[]\'],[name=\'m_orders_smeta_services_price[]\'],[name=\'m_orders_smeta_services_sum[]\'],[name=\'m_orders_smeta_room_length[]\'],[name=\'m_orders_smeta_room_weight[]\'],[name=\'m_orders_smeta_room_height[]\'],[name=\'m_orders_smeta_room_square[]\'],[name=\'m_orders_smeta_room_openings_weight[]\'],[name=\'m_orders_smeta_room_openings_height[]\'],[name=\'m_orders_smeta_room_openings_depth[]\'],[name=\'m_orders_smeta_room_openings_length[]\'],[name=\'m_orders_smeta_room_openings_square[]\']",function(){
		$(this).val($(this).val().replace(",","."));
		$(this).val($(this).val().replace(/[^.0-9]/gim,""));
	});
	/* ОКРУГЛЕНИЕ ДО 2-Х ЗНАКОВ ПОСЛЕ ТОЧКИ В ПОЛЯХ С ЧИСЛОВЫМИ ЗНАЧЕНИЯМИ */
	$(document).on("change","[name=\'m_orders_smeta_services_count[]\'],[name=\'m_orders_smeta_services_price[]\'],[name=\'m_orders_smeta_services_sum[]\'],[name=\'m_orders_smeta_room_length[]\'],[name=\'m_orders_smeta_room_weight[]\'],[name=\'m_orders_smeta_room_height[]\'],[name=\'m_orders_smeta_room_square[]\'],[name=\'m_orders_smeta_room_openings_weight[]\'],[name=\'m_orders_smeta_room_openings_height[]\'],[name=\'m_orders_smeta_room_openings_depth[]\'],[name=\'m_orders_smeta_room_openings_length[]\'],[name=\'m_orders_smeta_room_openings_square[]\']",function(){
		$(this).val(($(this).val()*1).toFixed(2));
	});
	
	/* ОБНОВЛЕНИЕ ПЛОЩАДИ КОМНАТЫ И РАСЧЕТОВ, СВЯЗАННЫХ С НЕЙ ПРИ ИЗМЕНЕНИИ РАЗМЕРОВ КОМНАТЫ */
	$(document).on("change","[name=\'m_orders_smeta_room_length[]\'],[name=\'m_orders_smeta_room_weight[]\'],[name=\'m_orders_smeta_room_height[]\']",function(){
		var parent=$(this).parents(".row:first");
		parent.find("[name=\'m_orders_smeta_room_square[]\']").val((parent.find("[name=\'m_orders_smeta_room_length[]\']").val()*parent.find("[name=\'m_orders_smeta_room_weight[]\']").val()).toFixed(2));
		parent.find("[name=\'m_orders_smeta_room_square[]\']").trigger("keyup").trigger("change");
		parent.parents(".multirow:first").find("[name=\'m_orders_smeta_services_name[]\']").trigger("change");
	});
	/* ПРИ ИЗМЕНЕНИИ САМОЙ ПЛОЩАДИ ОБНОВЛЕНИЕ РАСЧЕТОВ, СВЯЗАННЫХ С НЕЙ*/
	$(document).on("change","[name=\'m_orders_smeta_room_square[]\']",function(){
		var parent=$(this).parents(".row:first");
		parent.parents(".multirow:first").find("[name=\'m_orders_smeta_services_name[]\']").trigger("change");
	});
	
	/* ОБНОВЛЕНИЕ ПЛОЩАДИ ПРОЁМОВ И РАСЧЕТОВ, СВЯЗАННЫХ С НЕЙ ПРИ ИЗМЕНЕНИИ РАЗМЕРОВ ПРОЁМА */
	$(document).on("change","[name=\'m_orders_smeta_room_openings_weight[]\'],[name=\'m_orders_smeta_room_openings_height[]\'],[name=\'m_orders_smeta_room_openings_depth[]\']",function(){
		var parent=$(this).parents(".row:first");
		parent.find("[name=\'m_orders_smeta_room_openings_square[]\']").val((parent.find("[name=\'m_orders_smeta_room_openings_weight[]\']").val()*parent.find("[name=\'m_orders_smeta_room_openings_height[]\']").val()).toFixed(2));
		parent.find("[name=\'m_orders_smeta_room_openings_square[]\']").trigger("keyup").trigger("change");;
		
		parent.find("[name=\'m_orders_smeta_room_openings_length[]\']").val(((parent.find("[name=\'m_orders_smeta_room_openings_weight[]\']").val()*1+parent.find("[name=\'m_orders_smeta_room_openings_height[]\']").val()*2)).toFixed(2));
		parent.find("[name=\'m_orders_smeta_room_openings_length[]\']").trigger("keyup").trigger("change");
		
		parent.parents(".multirow:eq(1)").find("[name=\'m_orders_smeta_services_name[]\']").trigger("change");
	});
	/* ПРИ ИЗМЕНЕНИИ САМОЙ ПЛОЩАДИ ПРОЁМА ОБНОВЛЕНИЕ РАСЧЕТОВ, СВЯЗАННЫХ С НЕЙ*/
	$(document).on("change","[name=\'m_orders_smeta_room_openings_square[]\'],[name=\'m_orders_smeta_room_openings_type[]\']",function(){
		var parent=$(this).parents(".row:first");
		parent.parents(".multirow:eq(1)").find("[name=\'m_orders_smeta_services_name[]\']").trigger("change");
	});
	
	/* АВТОЗАПОЛНЕНИЕ ПО НАИМЕНОВАНИЮ УСЛУГИ */
	$("input[name=\'m_orders_smeta_services_name[]\']").sug();
	
	/* ИЗМЕНЕНИЕ СУММЫ ПО УСЛУГЕ ПРИ ИЗМЕНЕНИИ САМОЙ УСЛУГИ, КОЛ-ВА ИЛИ ЦЕНЫ УСЛУГИ */
	$(document).on("change","[name=\'m_orders_smeta_services_name[]\'],[name=\'m_orders_smeta_services_count[]\'],[name=\'m_orders_smeta_services_price[]\'],[name=\'m_orders_smeta_room_openings_type[]\']",function(){
		var parent=$(this).parents(".multirow:first"),
			sum=0;
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
	
	/* ПРИ ВЫБОРЕ УСЛУГИ ПО НАИМЕНОВАНИЮ РАСЧЕТ КОЛ-ВА ЕД. НА ОСНОВАНИИ ПАРАМЕТРОВ КОМНАТЫ И ПРОЕМОВ */
	$(document).on("change","[name=\'m_orders_smeta_services_name[]\']",function(){
		
		var parent=$(this).parents(".multirow:first"),
			room_length=parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_length[]\']").val()*1,
			room_weight=parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_weight[]\']").val()*1,
			room_height=parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_height[]\']").val()*1,
			room_square=parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_square[]\']").val()*1,	
			room_walls_square_with_openings=((room_length+room_weight)*2*room_height).toFixed(2),
			/* ИСКЛЮЧЕНИЯ - РАБОТЫ, КОЛ-ВО КОТОРЫХ НЕ НАДО СЧИТАТЬ АВТОМАТИЧЕСКИ */
			exceptions=[1362761010,5100000046,5100000047,5100000048,5100000049,5100000050,5100000051,5100000052,5100000053,5100000056,5100000057,5100000058,5100000065,5100000066,5100000067,5100000068,5100000069,5100000070,5100000071,5100000072,5100000075,5100000076,5100000077,5100000086,5100000087,5100000090,5100000091,5100000097,5100000098,5100000099,5100000100,5100000101,5100000102,5100000103,5100000104,5100000105,5100000106,5100000107,5100000108,5100000114,1003477391,5100000036];
			
		/* ПОЛЫ, ПОТОЛКИ */
		if(!in_array(parent.find("[name=\'m_orders_smeta_services_id[]\']").val(),exceptions)&&(parent.find("[name=\'m_orders_smeta_services_category[]\']").val().indexOf("1234019089")!=-1||parent.find("[name=\'m_orders_smeta_services_category[]\']").val().indexOf("1332709645")!=-1))
			parent.find("[name=\'m_orders_smeta_services_count[]\']:not(.manual-changed)").val(room_square).trigger("change");
		
		/* ПЛИНТУСА НАПОЛЬНЫЕ */
		var element=[1004777659,5100000009,5100000010,5100000039],
			/* ДЛИНА ПРОЁМОВ */
			room_openings_width=0;
		/* СЧИТАЕМ ТОЛЬКО ДЛИНУ ПРОЁМОВ И ДВЕРЕЙ (БЕЗ ОКОН И НИШ) */
		parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_weight[]\']").each(function(index,el){
			if($(el).parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_type[]\']").val()==2||$(el).parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_type[]\']").val()==3)
				room_openings_width+=$(el).val()*1;
		});
		for(var i=0; i<element.length;i++){
			if(parent.find("[name=\'m_orders_smeta_services_id[]\']").val().indexOf(element[i])!=-1)
				parent.find("[name=\'m_orders_smeta_services_count[]\']:not(.manual-changed)").val((room_length+room_weight)*2-room_openings_width).trigger("change");
		}
		
		/* ПЛИНТУСА ПОТОЛОЧНЫЕ */
		var element=[5100000115,5100000143];
			for(var i=0; i<element.length;i++){
				if(parent.find("[name=\'m_orders_smeta_services_id[]\']").val().indexOf(element[i])!=-1)
					parent.find("[name=\'m_orders_smeta_services_count[]\']:not(.manual-changed)").val((room_length+room_weight)*2).trigger("change");
		}
		
		/* СТЕНЫ */
		if(!in_array(parent.find("[name=\'m_orders_smeta_services_id[]\']").val(),exceptions)&&parent.find("[name=\'m_orders_smeta_services_category[]\']").val().indexOf("1345900046")!=-1){
			
			/* РАСЧЕТ ПЛОЩАДЕЙ СТЕН С УЧЁТОМ РАЗМЕРОВ ВСЕХ ПРОЁМОВ */
			var room_walls_square=0;
			parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_square[]\']").each(function(index,el){
				room_walls_square+=$(el).val()*1;
			});
			room_walls_square=room_walls_square_with_openings-room_walls_square;
			if(room_walls_square>=0)
				parent.find("[name=\'m_orders_smeta_services_count[]\']:not(.manual-changed)").val(room_walls_square).trigger("change");
			else{
				alert("Ошибка: площадь проёмов больше площади стен!");
			}
		}
		
		/* ОТКОСЫ ОКОННЫЕ */
		var element=[5100000059,5100000060,5100000061,5100000062,5100000063,5100000064,5100000089],
			/* ДЛИНА ПРОЁМОВ */
			room_openings_length=0;
		/* СЧИТАЕМ ДЛИНУ ВСЕХ ОТКОСОВ */
		parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_length[]\']").each(function(index,el){
			if($(el).parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_type[]\']").val()==1)
				room_openings_length+=$(el).val()*1;
		});
		for(var i=0; i<element.length;i++){
			if(parent.find("[name=\'m_orders_smeta_services_id[]\']").val().indexOf(element[i])!=-1)
				parent.find("[name=\'m_orders_smeta_services_count[]\']:not(.manual-changed)").val(room_openings_length).trigger("change");
		}
		
		/* ПОДОКОННИКИ, ОТЛИВЫ */
		var element=[5100000224,5100000225,5100000233,5100000234,5100000235,5100000236],
			/* ДЛИНА ПРОЁМОВ */
			room_openings_width=0;
		/* СЧИТАЕМ ШИРИНУ ВСЕХ ПОДОКОННИКОВ */
		parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_weight[]\']").each(function(index,el){
			if($(el).parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_type[]\']").val()==1)
				room_openings_width+=$(el).val()*1;
		});
		for(var i=0; i<element.length;i++){
			if(parent.find("[name=\'m_orders_smeta_services_id[]\']").val().indexOf(element[i])!=-1)
				parent.find("[name=\'m_orders_smeta_services_count[]\']:not(.manual-changed)").val(room_openings_width).trigger("change");
		}
		
		/* ПОРОГИ ДВЕРЕЙ */
		var element=[5100000250],
			/* ДЛИНА ПРОЁМОВ */
			room_doors_width=0;
		/* СЧИТАЕМ ШИРИНУ ВСЕХ ПОРОГОВ */
		parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_weight[]\']").each(function(index,el){
			if($(el).parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_type[]\']").val()==2)
				room_doors_width+=$(el).val()*1;
		});
		for(var i=0; i<element.length;i++){
			if(parent.find("[name=\'m_orders_smeta_services_id[]\']").val().indexOf(element[i])!=-1)
				parent.find("[name=\'m_orders_smeta_services_count[]\']:not(.manual-changed)").val(room_doors_width).trigger("change");
		}
		
		
		parent.find("[name=\'m_orders_smeta_services_sum[]\']").trigger("change");
		
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
	
	$("[name=\'m_orders_smeta_services_count[]\']").trigger("change");
	
');	

?>
<header>
	Параметры сметы
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
						echo '<option value="'.$_order['m_orders_id'].'"'.($document['m_documents_order']==$_order['m_orders_id']?' selected ':'').'>',
							$_order['m_orders_name'],
						'</option>';
					}
				?>
			</select>
		</section>
		<section class="col col-6">
			<label class="label">&nbsp;</label>
			<label class="checkbox">
				<input type="checkbox" name="m_orders_smeta_additional" <?=(isset($params->additional)&&$params->additional?' checked':'')?> value="1"/>
				<i></i>
				Дополнительное соглашение
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
				<section class="col col-9">
					<h2>
						Помещение
					</h2>
				</section>
				<section class="col col-3" style="text-align:right">
					<div class="btn-group btn-labeled multirow-btn">
						<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
						<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
							<span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							<li>
								<a href="javascript:void(0);" class="add">Добавить помещение</a>
							</li>
							<li>
								<a href="javascript:void(0);" class="copy">Скопировать помещение</a>
							</li>
							<li>
								<a href="javascript:void(0);" class="delete">Удалить помещение</a>
							</li>
						</ul>
					</div>
				</section>
			</div>
			<div class="row">		
				<section class="col col-4">
					<label class="label">Основные параметры</label>
					<label class="input">
						<input type="text" name="m_orders_smeta_room_name[]"  placeholder="название" title="Название помещения">
					</label>
				</section>
				<section class="col col-2">
					<label class="label">&nbsp;</label>
					<label class="input">
						<i class="icon-append">м</i>
						<input type="text" name="m_orders_smeta_room_length[]" style="text-align:right" placeholder="длина" title="Длина помещения">
					</label>
				</section>
				<section class="col col-2">
					<label class="label">&nbsp;</label>
					<label class="input">
						<i class="icon-append">м</i>
						<input type="text" name="m_orders_smeta_room_weight[]" style="text-align:right" placeholder="ширина" title="Ширина помещения">
					</label>
				</section>
				<section class="col col-2">
					<label class="label">&nbsp;</label>
					<label class="input">
						<i class="icon-append">м</i>
						<input type="text" name="m_orders_smeta_room_height[]" style="text-align:right" placeholder="высота" title="Высота помещения">
					</label>
				</section>
				<section class="col col-2">
					<label class="label">&nbsp;</label>
					<label class="input">
						<i class="icon-append">м<sup>2</sup></i>
						<input type="text" name="m_orders_smeta_room_square[]" style="text-align:right" placeholder="площ." title="Площадь помещения">
					</label>
				</section>
			</div>
			<div class="row red">
				<section>
					<h3>
						Проёмы для вычета
					</h3>
				</section>
				<div class="openings">
					<div class="multirow">
						<div class="row red">
							<section class="col col-2-25">
								<select name="m_orders_smeta_room_openings_type[]" class="autoselect">
									<?
										foreach($info->getRoomOpenings() as $t_)
											echo '<option value="'.$t_[0]['m_info_orders_room_openings_id'].'">',
												$t_[0]['m_info_orders_room_openings_name'],
												'</option>';
									?>
								</select>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">м</i>
									<input type="text" name="m_orders_smeta_room_openings_weight[]" style="text-align:right" placeholder="ширина" title="Ширина проёма">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">м</i>
									<input type="text" name="m_orders_smeta_room_openings_height[]" style="text-align:right" placeholder="высота" title="Высота проёма">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">м</i>
									<input type="text" name="m_orders_smeta_room_openings_depth[]" style="text-align:right" placeholder="глубина" title="Глубина проёма">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">мп</i>
									<input type="text" name="m_orders_smeta_room_openings_length[]" style="text-align:right" placeholder="длина" title="Общая длин проёма">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">м<sup>2</sup></i>
									<input type="text" name="m_orders_smeta_room_openings_square[]" style="text-align:right" placeholder="площ." title="Площадь проёма">
								</label>
							</section>
							<section class="col col-2-25" style="text-align:right">
								<div class="btn-group btn-labeled multirow-btn">
									<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
									<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href="javascript:void(0);" class="add">Добавить проём</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="delete">Удалить проём</a>
										</li>
									</ul>
								</div>
							</section>
						</div>
					</div>
				</div>
			</div>
			<div class="row yellow">
				<section>
					<h3>
						Работы
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
								</label>
							</section>
							<section class="col col-1">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_unit[]" placeholder="ед." >
								</label>
							</section>
							<section class="col col-1-25">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_count[]" style="text-align:right" placeholder="кол-во" title="Необходимое количество">
								</label>
							</section>
							<section class="col col-1-5">
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
							<section class="col col-2-25" style="text-align:right">
								<div class="btn-group btn-labeled multirow-btn">
									<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
									<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href="javascript:void(0);" class="add">Добавить работы</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="delete">Удалить работы</a>
										</li>
									</ul>
								</div>
							</section>
						</div>
					</div>
				</div>
				<section>
					<div class="rowsum">
						Итого по помещению: <span>0 р.</span>
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
				<input type="hidden" name="idroom[]" value="<?=$_id?>">
				<section class="col col-9">
					<h2>
						<?=($_item->room->name?$_item->room->name:'Помещение').($_item->room->square?' — '.$_item->room->square.' м<sup>2</sup>':'')?>
					</h2>
				</section>
				<section class="col col-3" style="text-align:right">
					<div class="btn-group btn-labeled multirow-btn">
						<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
						<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
							<span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							<li>
								<a href="javascript:void(0);" class="add">Добавить помещение</a>
							</li>
							<li>
								<a href="javascript:void(0);" class="copy">Скопировать помещение</a>
							</li>
							<li>
								<a href="javascript:void(0);" class="delete">Удалить помещение</a>
							</li>
						</ul>
					</div>
				</section>
			</div>
			<div class="row">		
				<section class="col col-4">
					<label class="label">Основные параметры</label>
					<label class="input">
						<input type="text" name="m_orders_smeta_room_name[]"  placeholder="название" value="<?=$_item->room->name?>" title="Название помещения">
					</label>
				</section>
				<section class="col col-2">
					<label class="label">&nbsp;</label>
					<label class="input">
						<i class="icon-append">м</i>
						<input type="text" name="m_orders_smeta_room_length[]" style="text-align:right" placeholder="длина" value="<?=$_item->room->length?>" title="Длина помещения">
					</label>
				</section>
				<section class="col col-2">
					<label class="label">&nbsp;</label>
					<label class="input">
						<i class="icon-append">м</i>
						<input type="text" name="m_orders_smeta_room_weight[]" style="text-align:right" placeholder="ширина" value="<?=$_item->room->weight?>" title="Ширина помещения">
					</label>
				</section>
				<section class="col col-2">
					<label class="label">&nbsp;</label>
					<label class="input">
						<i class="icon-append">м</i>
						<input type="text" name="m_orders_smeta_room_height[]" style="text-align:right" placeholder="высота" value="<?=$_item->room->height?>" title="Высота помещения">
					</label>
				</section>
				<section class="col col-2">
					<label class="label">&nbsp;</label>
					<label class="input">
						<i class="icon-append">м<sup>2</sup></i>
						<input type="text" name="m_orders_smeta_room_square[]" style="text-align:right" placeholder="площ." value="<?=$_item->room->square?>" title="Площадь помещения">
					</label>
				</section>
			</div>
			<div class="row red">
				<section>
					<h3>
						Проёмы для вычета
					</h3>
				</section>
				<div class="openings">
<?
if(!isset($_item->openings)||!$_item->openings){
?>
					<div class="multirow">
						<div class="row red">
							<section class="col col-2-25">
								<select name="m_orders_smeta_room_openings_type[]" class="autoselect">
									<?
										foreach($info->getRoomOpenings() as $t_)
											echo '<option value="'.$t_[0]['m_info_orders_room_openings_id'].'">',
												$t_[0]['m_info_orders_room_openings_name'],
												'</option>';
									?>
								</select>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">м</i>
									<input type="text" name="m_orders_smeta_room_openings_weight[]" style="text-align:right" placeholder="ширина" title="Ширина проёма">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">м</i>
									<input type="text" name="m_orders_smeta_room_openings_height[]" style="text-align:right" placeholder="высота" title="Высота проёма">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">м</i>
									<input type="text" name="m_orders_smeta_room_openings_depth[]" style="text-align:right" placeholder="глубина" title="Глубина проёма">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">мп</i>
									<input type="text" name="m_orders_smeta_room_openings_length[]" style="text-align:right" placeholder="длина" title="Общая длин проёма">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">м<sup>2</sup></i>
									<input type="text" name="m_orders_smeta_room_openings_square[]" style="text-align:right" placeholder="площ." title="Площадь проёма">
								</label>
							</section>
							<section class="col col-2-25" style="text-align:right">
								<div class="btn-group btn-labeled multirow-btn">
									<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
									<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href="javascript:void(0);" class="add">Добавить проём</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="delete">Удалить проём</a>
										</li>
									</ul>
								</div>
							</section>
						</div>
					</div>
<?	
}
else
	foreach($_item->openings as $_opening){
?>				
					<div class="multirow">
						<div class="row red">
							<section class="col col-2-25">
								<select name="m_orders_smeta_room_openings_type[]" class="autoselect">
									<?
										foreach($info->getRoomOpenings() as $t_)
											echo '<option value="'.$t_[0]['m_info_orders_room_openings_id'].'"'.($t_[0]['m_info_orders_room_openings_id']==$_opening->type?' selected ':'').'>',
												$t_[0]['m_info_orders_room_openings_name'],
												'</option>';
									?>
								</select>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">м</i>
									<input type="text" name="m_orders_smeta_room_openings_weight[]" style="text-align:right" placeholder="ширина" value="<?=$_opening->weight?>" title="Ширина проёма">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">м</i>
									<input type="text" name="m_orders_smeta_room_openings_height[]" style="text-align:right" placeholder="высота" value="<?=$_opening->height?>" title="Высота проёма">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">м</i>
									<input type="text" name="m_orders_smeta_room_openings_depth[]" style="text-align:right" placeholder="глубина" value="<?=$_opening->depth?>" title="Глубина проёма">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">мп</i>
									<input type="text" name="m_orders_smeta_room_openings_length[]" style="text-align:right" placeholder="длина" value="<?=$_opening->length?>" title="Общая длин проёма">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">м<sup>2</sup></i>
									<input type="text" name="m_orders_smeta_room_openings_square[]" style="text-align:right" placeholder="площ." value="<?=$_opening->square?>" title="Площадь проёма">
								</label>
							</section>
							<section class="col col-2-25" style="text-align:right">
								<div class="btn-group btn-labeled multirow-btn">
									<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
									<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href="javascript:void(0);" class="add">Добавить проём</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="delete">Удалить проём</a>
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
			</div>
			<div class="row yellow">
				<section>
					<h3>
						Работы
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
									<input type="text" name="m_orders_smeta_services_name[]"  placeholder="вид работ" title="Наименование работ">
									<input type="hidden" name="m_orders_smeta_services_id[]">
									<input type="hidden" name="m_orders_smeta_services_category[]">
								</label>
							</section>
							<section class="col col-1">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_unit[]" placeholder="ед." >
								</label>
							</section>
							<section class="col col-1-25">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_count[]" style="text-align:right" placeholder="кол-во" title="Необходимое количество">
									<input type="hidden" name="m_orders_smeta_services_manual_changed[]" value="0">
								</label>
							</section>
							<section class="col col-1-5">
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
							<section class="col col-2-25" style="text-align:right">
								<div class="btn-group btn-labeled multirow-btn">
									<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
									<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href="javascript:void(0);" class="add">Добавить работы</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="delete">Удалить работы</a>
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
							<section class="col col-4-25">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_name[]"  placeholder="вид работ" value="<?=$services->services_id[$_service->id][0]['m_services_name']?>" title="Наименование работ">
									<input type="hidden" name="m_orders_smeta_services_id[]" value="<?=$_service->id?>">
									<input type="hidden" name="m_orders_smeta_services_category[]" value="<?=$services->services_id[$_service->id][0]['m_services_categories_id']?>">
								</label>
							</section>
							<section class="col col-1">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_unit[]" placeholder="ед." value="<?=$info->getUnitsNoHTML($services->services_id[$_service->id][0]['m_services_unit'])?>">
								</label>
							</section>
							<section class="col col-1-25">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_count[]" style="text-align:right" placeholder="кол-во" value="<?=$_service->count?>" <?=$_service->manual_changed?'class="manual-changed"':''?>  title="Необходимое количество">
									<input type="hidden" name="m_orders_smeta_services_manual_changed[]" value="<?=$_service->manual_changed?>">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">р.</i>
									<input type="text" name="m_orders_smeta_services_price[]" style="text-align:right" placeholder="цена" value="<?=$_service->price?>" title="Цена за ед. измерения">
								</label>
							</section>
							<section class="col col-1-75">
								<label class="input">
									<i class="icon-append">р.</i>
									<input type="text" name="m_orders_smeta_services_sum[]" style="text-align:right" placeholder="сумма" value="<?=$_service->sum?>" title="Сумма">
								</label>
							</section>
							<section class="col col-2-25" style="text-align:right">
								<div class="btn-group btn-labeled multirow-btn">
									<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
									<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href="javascript:void(0);" class="add">Добавить работы</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="delete">Удалить работы</a>
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
				<section>
					<div class="rowsum">
						Итого по помещению: <span>0 р.</span>
					</div>
				</section>
			</div>
		</div>
<?
	}
?>
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
	<button type="submit" class="btn btn-primary" id="m_orders_smeta_add">
		<i class="fa fa-save"></i>
		Сохранить данные
	</button>
</footer>
<input type="hidden" name="action" value="m_documents_change"/>
<input type="hidden" name="m_documents_templates_id" value="1200369852"/>
<input type="hidden" name="m_documents_id" value="<?=$document['m_documents_id']?>"/>
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
			/* ДОБАВЛЕНИЕ ПРОЁМОВ */
			var id="id"+Math.floor(Math.random()*10000000)+1;
			string.find(".openings:last").attr("id",id).df({
				max:1000,
				f_a:function(string){
					string.find("select.autoselect").select2();				
				},
				/* ОБНОВЛЕНИЕ РАСЧЁТОВ ПРИ УДАЛЕНИИ ПРОЁМА */
				f_d:function(){
					$("#"+id).parents(".multirow:first").find("[name=\'m_orders_smeta_services_name[]\']").trigger("change");
				}
			});
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
				}
			});
		},
		f_d:function(){
			$(".multirow:first").find("[name=\'m_orders_smeta_services_count[]\']").trigger("change");
		}
	});
	$(".openings").each(function(index,el){
		var id="id"+Math.floor(Math.random()*10000000)+1;
		$(el).attr("id",id).df({
			max:1000,
			f_a:function(string){
				string.find("select.autoselect").select2("destroy").select2();
			},
			f_d:function(){
				$("#"+id).parents(".multirow:first").find("[name=\'m_orders_smeta_services_name[]\']").trigger("change");
			}
		});
	});
	$(".services").each(function(index,el){
		var id="id"+Math.floor(Math.random()*10000000)+1;
		$(el).attr("id",id).df({
			max:1000,
			f_a:function(string){
				string.find("input[name=\'m_orders_smeta_services_name[]\']").sug();
			},
			f_d:function(){
				$("#"+id).parents(".multirow:first").find("[name=\'m_orders_smeta_services_count[]\']").trigger("change");
			}
		});
	});
	
	/* НАЗВАНИЕ И ПЛОЩАДЬ КОМНАТЫ В ЗАГОЛОВКЕ */
	$(document).on("keyup","[name=\'m_orders_smeta_room_name[]\'],[name=\'m_orders_smeta_room_square[]\']",function(){
		var parent=$(this).parents(".multirow:first");
		parent.find("h2").html(parent.find("[name=\'m_orders_smeta_room_name[]\']").val()+(parent.find("[name=\'m_orders_smeta_room_square[]\']").val()?" — "+parent.find("[name=\'m_orders_smeta_room_square[]\']").val()+" м<sup>2</sup>":""));
	}); 
	
	/* ТОЛЬКО ЦИФРЫ, ТОЧКА ВМЕСТО ЗАПЯТОЙ */
	$(document).on("keyup","[name=\'m_orders_smeta_services_count[]\'],[name=\'m_orders_smeta_services_price[]\'],[name=\'m_orders_smeta_services_sum[]\'],[name=\'m_orders_smeta_room_length[]\'],[name=\'m_orders_smeta_room_weight[]\'],[name=\'m_orders_smeta_room_height[]\'],[name=\'m_orders_smeta_room_square[]\'],[name=\'m_orders_smeta_room_openings_weight[]\'],[name=\'m_orders_smeta_room_openings_height[]\'],[name=\'m_orders_smeta_room_openings_depth[]\'],[name=\'m_orders_smeta_room_openings_length[]\'],[name=\'m_orders_smeta_room_openings_square[]\']",function(){
		$(this).val($(this).val().replace(",","."));
		$(this).val($(this).val().replace(/[^.0-9]/gim,""));
	});
	/* ОКРУГЛЕНИЕ ДО 2-Х ЗНАКОВ ПОСЛЕ ТОЧКИ В ПОЛЯХ С ЧИСЛОВЫМИ ЗНАЧЕНИЯМИ */
	$(document).on("change","[name=\'m_orders_smeta_services_count[]\'],[name=\'m_orders_smeta_services_price[]\'],[name=\'m_orders_smeta_services_sum[]\'],[name=\'m_orders_smeta_room_length[]\'],[name=\'m_orders_smeta_room_weight[]\'],[name=\'m_orders_smeta_room_height[]\'],[name=\'m_orders_smeta_room_square[]\'],[name=\'m_orders_smeta_room_openings_weight[]\'],[name=\'m_orders_smeta_room_openings_height[]\'],[name=\'m_orders_smeta_room_openings_depth[]\'],[name=\'m_orders_smeta_room_openings_length[]\'],[name=\'m_orders_smeta_room_openings_square[]\']",function(){
		$(this).val(($(this).val()*1).toFixed(2));
	});
	
	/* ОБНОВЛЕНИЕ ПЛОЩАДИ КОМНАТЫ И РАСЧЕТОВ, СВЯЗАННЫХ С НЕЙ ПРИ ИЗМЕНЕНИИ РАЗМЕРОВ КОМНАТЫ */
	$(document).on("change","[name=\'m_orders_smeta_room_length[]\'],[name=\'m_orders_smeta_room_weight[]\'],[name=\'m_orders_smeta_room_height[]\']",function(){
		var parent=$(this).parents(".row:first");
		parent.find("[name=\'m_orders_smeta_room_square[]\']").val((parent.find("[name=\'m_orders_smeta_room_length[]\']").val()*parent.find("[name=\'m_orders_smeta_room_weight[]\']").val()).toFixed(2));
		parent.find("[name=\'m_orders_smeta_room_square[]\']").trigger("keyup").trigger("change");
		parent.parents(".multirow:first").find("[name=\'m_orders_smeta_services_name[]\']").trigger("change");
	});
	/* ПРИ ИЗМЕНЕНИИ САМОЙ ПЛОЩАДИ ОБНОВЛЕНИЕ РАСЧЕТОВ, СВЯЗАННЫХ С НЕЙ*/
	$(document).on("change","[name=\'m_orders_smeta_room_square[]\']",function(){
		var parent=$(this).parents(".row:first");
		parent.parents(".multirow:first").find("[name=\'m_orders_smeta_services_name[]\']").trigger("change");
	});
	
	/* ОБНОВЛЕНИЕ ПЛОЩАДИ ПРОЁМОВ И РАСЧЕТОВ, СВЯЗАННЫХ С НЕЙ ПРИ ИЗМЕНЕНИИ РАЗМЕРОВ ПРОЁМА */
	$(document).on("change","[name=\'m_orders_smeta_room_openings_weight[]\'],[name=\'m_orders_smeta_room_openings_height[]\'],[name=\'m_orders_smeta_room_openings_depth[]\']",function(){
		var parent=$(this).parents(".row:first");
		parent.find("[name=\'m_orders_smeta_room_openings_square[]\']").val((parent.find("[name=\'m_orders_smeta_room_openings_weight[]\']").val()*parent.find("[name=\'m_orders_smeta_room_openings_height[]\']").val()).toFixed(2));
		parent.find("[name=\'m_orders_smeta_room_openings_square[]\']").trigger("keyup").trigger("change");;
		
		parent.find("[name=\'m_orders_smeta_room_openings_length[]\']").val(((parent.find("[name=\'m_orders_smeta_room_openings_weight[]\']").val()*1+parent.find("[name=\'m_orders_smeta_room_openings_height[]\']").val()*2)).toFixed(2));
		parent.find("[name=\'m_orders_smeta_room_openings_length[]\']").trigger("keyup").trigger("change");
		
		parent.parents(".multirow:eq(1)").find("[name=\'m_orders_smeta_services_name[]\']").trigger("change");
	});
	/* ПРИ ИЗМЕНЕНИИ САМОЙ ПЛОЩАДИ ПРОЁМА ОБНОВЛЕНИЕ РАСЧЕТОВ, СВЯЗАННЫХ С НЕЙ*/
	$(document).on("change","[name=\'m_orders_smeta_room_openings_square[]\'],[name=\'m_orders_smeta_room_openings_type[]\']",function(){
		var parent=$(this).parents(".row:first");
		parent.parents(".multirow:eq(1)").find("[name=\'m_orders_smeta_services_name[]\']").trigger("change");
	});
	
	/* АВТОЗАПОЛНЕНИЕ ПО НАИМЕНОВАНИЮ УСЛУГИ */
	$("input[name=\'m_orders_smeta_services_name[]\']").sug();
	
	/* ИЗМЕНЕНИЕ СУММЫ ПО УСЛУГЕ ПРИ ИЗМЕНЕНИИ САМОЙ УСЛУГИ, КОЛ-ВА ИЛИ ЦЕНЫ УСЛУГИ */
	$(document).on("change","[name=\'m_orders_smeta_services_name[]\'],[name=\'m_orders_smeta_services_count[]\'],[name=\'m_orders_smeta_services_price[]\'],[name=\'m_orders_smeta_room_openings_type[]\']",function(){
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
	
	/* ПРИ ВЫБОРЕ УСЛУГИ ПО НАИМЕНОВАНИЮ РАСЧЕТ КОЛ-ВА ЕД. НА ОСНОВАНИИ ПАРАМЕТРОВ КОМНАТЫ И ПРОЕМОВ */
	$(document).on("change","[name=\'m_orders_smeta_services_name[]\']",function(){
		
		var parent=$(this).parents(".multirow:first"),
			room_length=parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_length[]\']").val()*1,
			room_weight=parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_weight[]\']").val()*1,
			room_height=parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_height[]\']").val()*1,
			room_square=parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_square[]\']").val()*1,	
			room_walls_square_with_openings=((room_length+room_weight)*2*room_height).toFixed(2),
			/* ИСКЛЮЧЕНИЯ - РАБОТЫ, КОЛ-ВО КОТОРЫХ НЕ НАДО СЧИТАТЬ АВТОМАТИЧЕСКИ */
			exceptions=[1362761010,5100000046,5100000047,5100000048,5100000049,5100000050,5100000051,5100000052,5100000053,5100000056,5100000057,5100000058,5100000065,5100000066,5100000067,5100000068,5100000069,5100000070,5100000071,5100000072,5100000075,5100000076,5100000077,5100000086,5100000087,5100000090,5100000091,5100000097,5100000098,5100000099,5100000100,5100000101,5100000102,5100000103,5100000104,5100000105,5100000106,5100000107,5100000108,5100000114,1003477391,5100000036];
			
		/* ПОЛЫ, ПОТОЛКИ */
		if(!in_array(parent.find("[name=\'m_orders_smeta_services_id[]\']").val(),exceptions)&&parent.find("[name=\'m_orders_smeta_services_category[]\']").val().indexOf("1234019089")!=-1||parent.find("[name=\'m_orders_smeta_services_category[]\']").val().indexOf("1332709645")!=-1)
			parent.find("[name=\'m_orders_smeta_services_count[]\']:not(.manual-changed)").val(room_square).trigger("change");
		
		/* ПЛИНТУСА НАПОЛЬНЫЕ */
		var element=[1004777659,5100000009,5100000010,5100000039],
			/* ДЛИНА ПРОЁМОВ */
			room_openings_width=0;
			/* СЧИТАЕМ ТОЛЬКО ДЛИНУ ПРОЁМОВ И ДВЕРЕЙ (БЕЗ ОКОН И НИШ) */
			parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_weight[]\']").each(function(index,el){
				if($(el).parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_type[]\']").val()==2||$(el).parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_type[]\']").val()==3)
					room_openings_width+=$(el).val()*1;
			});
			for(var i=0; i<element.length;i++){
				if(parent.find("[name=\'m_orders_smeta_services_id[]\']").val().indexOf(element[i])!=-1)
					parent.find("[name=\'m_orders_smeta_services_count[]\']:not(.manual-changed)").val((room_length+room_weight)*2-room_openings_width).trigger("change");
		}
		
		/* ПЛИНТУСА ПОТОЛОЧНЫЕ */
		var element=[5100000115,5100000143];
			for(var i=0; i<element.length;i++){
				if(parent.find("[name=\'m_orders_smeta_services_id[]\']").val().indexOf(element[i])!=-1)
					parent.find("[name=\'m_orders_smeta_services_count[]\']:not(.manual-changed)").val((room_length+room_weight)*2).trigger("change");
		}
		
		/* СТЕНЫ */
		if(!in_array(parent.find("[name=\'m_orders_smeta_services_id[]\']").val(),exceptions)&&parent.find("[name=\'m_orders_smeta_services_category[]\']").val().indexOf("1345900046")!=-1){
			
			/* РАСЧЕТ ПЛОЩАДЕЙ СТЕН С УЧЁТОМ РАЗМЕРОВ ВСЕХ ПРОЁМОВ */
			var room_walls_square=0;
			parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_square[]\']").each(function(index,el){
				room_walls_square+=$(el).val()*1;
			});
			room_walls_square=room_walls_square_with_openings-room_walls_square;
			if(room_walls_square>=0)
				parent.find("[name=\'m_orders_smeta_services_count[]\']:not(.manual-changed)").val(room_walls_square).trigger("change");
			else{
				alert("Ошибка: площадь проёмов больше площади стен!");
			}
		}
		
		/* ОТКОСЫ ОКОННЫЕ */
		var element=[5100000059,5100000060,5100000061,5100000062,5100000063,5100000064,5100000089],
			/* ДЛИНА ПРОЁМОВ */
			room_openings_length=0;
		/* СЧИТАЕМ ДЛИНУ ВСЕХ ОТКОСОВ */
		parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_length[]\']").each(function(index,el){
			if($(el).parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_type[]\']").val()==1)
				room_openings_length+=$(el).val()*1;
		});
		for(var i=0; i<element.length;i++){
			if(parent.find("[name=\'m_orders_smeta_services_id[]\']").val().indexOf(element[i])!=-1)
				parent.find("[name=\'m_orders_smeta_services_count[]\']:not(.manual-changed)").val(room_openings_length).trigger("change");
		}
		
		/* ПОДОКОННИКИ, ОТЛИВЫ */
		var element=[5100000224,5100000225,5100000233,5100000234,5100000235,5100000236],
			/* ДЛИНА ПРОЁМОВ */
			room_openings_width=0;
		/* СЧИТАЕМ ШИРИНУ ВСЕХ ПОДОКОННИКОВ */
		parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_weight[]\']").each(function(index,el){
			if($(el).parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_type[]\']").val()==1)
				room_openings_width+=$(el).val()*1;
		});
		for(var i=0; i<element.length;i++){
			if(parent.find("[name=\'m_orders_smeta_services_id[]\']").val().indexOf(element[i])!=-1)
				parent.find("[name=\'m_orders_smeta_services_count[]\']:not(.manual-changed)").val(room_openings_width).trigger("change");
		}
		
		/* ПОРОГИ ДВЕРЕЙ */
		var element=[5100000250],
			/* ДЛИНА ПРОЁМОВ */
			room_doors_width=0;
		/* СЧИТАЕМ ШИРИНУ ВСЕХ ПОРОГОВ */
		parent.parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_weight[]\']").each(function(index,el){
			if($(el).parents(".multirow:first").find("[name=\'m_orders_smeta_room_openings_type[]\']").val()==2)
				room_doors_width+=$(el).val()*1;
		});
		for(var i=0; i<element.length;i++){
			if(parent.find("[name=\'m_orders_smeta_services_id[]\']").val().indexOf(element[i])!=-1)
				parent.find("[name=\'m_orders_smeta_services_count[]\']:not(.manual-changed)").val(room_doors_width).trigger("change");
		}
		
		parent.find("[name=\'m_orders_smeta_services_sum[]\']").trigger("change");
		
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
	
');	

?>
<header>
	Параметры сметы
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
						echo '<option value="'.$_order['m_orders_id'].'">',
							$_order['m_orders_name'],
						'</option>';
					}
				?>
			</select>
		</section>
		<section class="col col-6">
			<label class="label">&nbsp;</label>
			<label class="checkbox">
				<input type="checkbox" name="m_orders_smeta_additional" value="1"/>
				<i></i>
				Дополнительное соглашение
			</label>
		</section>
	</div>

	<div id="rooms">
		<div class="multirow">
			<div class="row">
				<input type="hidden" name="idroom[]" value="<?=mt_rand(1,99999999)?>">
				<section class="col col-9">
					<h2>
						Помещение
					</h2>
				</section>
				<section class="col col-3" style="text-align:right">
					<div class="btn-group btn-labeled multirow-btn">
						<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
						<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
							<span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							<li>
								<a href="javascript:void(0);" class="add">Добавить помещение</a>
							</li>
							<li>
								<a href="javascript:void(0);" class="delete">Удалить помещение</a>
							</li>
						</ul>
					</div>
				</section>
			</div>
			<div class="row">		
				<section class="col col-4">
					<label class="label">Основные параметры</label>
					<label class="input">
						<input type="text" name="m_orders_smeta_room_name[]"  placeholder="название" title="Название помещения">
					</label>
				</section>
				<section class="col col-2">
					<label class="label">&nbsp;</label>
					<label class="input">
						<i class="icon-append">м</i>
						<input type="text" name="m_orders_smeta_room_length[]" style="text-align:right" placeholder="длина" title="Длина помещения">
					</label>
				</section>
				<section class="col col-2">
					<label class="label">&nbsp;</label>
					<label class="input">
						<i class="icon-append">м</i>
						<input type="text" name="m_orders_smeta_room_weight[]" style="text-align:right" placeholder="ширина" title="Ширина помещения">
					</label>
				</section>
				<section class="col col-2">
					<label class="label">&nbsp;</label>
					<label class="input">
						<i class="icon-append">м</i>
						<input type="text" name="m_orders_smeta_room_height[]" style="text-align:right" placeholder="высота" title="Высота помещения">
					</label>
				</section>
				<section class="col col-2">
					<label class="label">&nbsp;</label>
					<label class="input">
						<i class="icon-append">м<sup>2</sup></i>
						<input type="text" name="m_orders_smeta_room_square[]" style="text-align:right" placeholder="площ." title="Площадь помещения">
					</label>
				</section>
			</div>
			<div class="row red">
				<section>
					<h3>
						Проёмы для вычета
					</h3>
				</section>
				<div class="openings">
					<div class="multirow">
						<div class="row red">
							<section class="col col-2-25">
								<select name="m_orders_smeta_room_openings_type[]" class="autoselect">
									<?
										foreach($info->getRoomOpenings() as $t_)
											echo '<option value="'.$t_[0]['m_info_orders_room_openings_id'].'">',
												$t_[0]['m_info_orders_room_openings_name'],
												'</option>';
									?>
								</select>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">м</i>
									<input type="text" name="m_orders_smeta_room_openings_weight[]" style="text-align:right" placeholder="ширина" title="Ширина проёма">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">м</i>
									<input type="text" name="m_orders_smeta_room_openings_height[]" style="text-align:right" placeholder="высота" title="Высота проёма">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">м</i>
									<input type="text" name="m_orders_smeta_room_openings_depth[]" style="text-align:right" placeholder="глубина" title="Глубина проёма">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">мп</i>
									<input type="text" name="m_orders_smeta_room_openings_length[]" style="text-align:right" placeholder="длина" title="Общая длин проёма">
								</label>
							</section>
							<section class="col col-1-5">
								<label class="input">
									<i class="icon-append">м<sup>2</sup></i>
									<input type="text" name="m_orders_smeta_room_openings_square[]" style="text-align:right" placeholder="площ." title="Площадь проёма">
								</label>
							</section>
							<section class="col col-2-25" style="text-align:right">
								<div class="btn-group btn-labeled multirow-btn">
									<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
									<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href="javascript:void(0);" class="add">Добавить проём</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="delete">Удалить проём</a>
										</li>
									</ul>
								</div>
							</section>
						</div>
					</div>
				</div>
			</div>
			<div class="row yellow">
				<section>
					<h3>
						Работы
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
								</label>
							</section>
							<section class="col col-1">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_unit[]" placeholder="ед." >
								</label>
							</section>
							<section class="col col-1-25">
								<label class="input">
									<input type="text" name="m_orders_smeta_services_count[]" style="text-align:right" placeholder="кол-во" title="Необходимое количество">
									<input type="hidden" name="m_orders_smeta_services_manual_changed[]" value="0">
								</label>
							</section>
							<section class="col col-1-5">
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
							<section class="col col-2-25" style="text-align:right">
								<div class="btn-group btn-labeled multirow-btn">
									<a class="btn btn-info add" href="javascript:void(0);"><span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Добавить</a>
									<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href="javascript:void(0);" class="add">Добавить работы</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="delete">Удалить работы</a>
										</li>
									</ul>
								</div>
							</section>
						</div>
					</div>
				</div>
				<section>
					<div class="rowsum">
						Итого по помещению: <span>0 р.</span>
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
	<button type="submit" class="btn btn-primary" id="m_orders_smeta_add">
		<i class="fa fa-save"></i>
		Сохранить данные
	</button>
</footer>
<input type="hidden" name="action" value="m_documents_add"/>
<input type="hidden" name="m_documents_templates_id" value="1200369852"/>
<?}?>
<script src="/js/jquery.df.js"></script>
<script src="/js/jquery.suggest_services.js"></script>