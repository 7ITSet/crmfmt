 <?
defined ('_DSITE') or die ('Access denied');

global $user,$sql,$content,$orders;

$content->setJS('

	$(".datatable .delete").editable({
			url: "/ajax/services_change.php",
			success: function(response,newValue){
				if($(this).hasClass("delete")){
					$(this).parents("tr:first").remove();
				}
			}
	});
	
	$(document).on("click",".send_sms_to_client,.send_email_to_client",function(){
		var self=$(this);
		$.post(
			"/ajax/documents.send_to_client.php",
			{
				id:$(this).data("pk"),
				type:$(this).data("type")
			},
			function(data){
				if(data=="SUCCESS")
					self.prop("disabled",true);
			}
		);
	});
	$(document).on("click",".change_pay_methods",function(){
		var self=$(this);
		$.post(
			"/ajax/documents.change_paymethod.php",
			{
				order:$(this).data("order"),
				method:$(this).data("method")
			},
			function(data){
				if(data=="SUCCESS")
					self.parents("ul:first").prev().html(self.text()+" <span class=\"caret\"></span>");
			}
		);
	});
	
	
	$(document).on("change","td.check input",function(){
		if($(this).prop("checked"))
			$(this).parents("tr:first").addClass("tr-selected");
		else
			$(this).parents("tr:first").removeClass("tr-selected");
	});
	
	$("#m_documents_id_all").on("change",function(){
		if($("#m_documents_id_all:checked").length)
			$(".m_documents_id").prop("checked",true);
		else
			$(".m_documents_id").prop("checked",false);
	});
	$("#documents_filter").on("keyup",function(eventObject){
		if(eventObject.which!=13) return false;
		$.get(
			"/ajax/table_documents.php",
			{
				search:$("#documents_filter").val(),
				limit:$("#documents_count option:selected").val(),
				order:$("#orders option:selected").val(),
				page:$(".pagination li.active").text()
			},
			function(data){
				/* ПОКАЗЫВАЕМ РЕЗУЛЬТАТ В ТАБЛИЦЕ */
				$("#documents tbody").html(data);
					/* КОЛ-ВО ЗАПИСЕЙ */
					var count=$("#documents tbody").find("tr:first").attr("count"),
					/* КОЛ-ВО СТРАНИЦ */
					pages=Math.ceil(count/$("#documents_count option:selected").val()),
					/* ТЕКУЩАЯ СТРАНИЦА */
					page=$("#documents tbody").find("tr:first").attr("page"),
					/* СПИСОК СТРАНИЦ */
					page_list="";
				
				$(".table-info span").text("Всего отобрано записей: "+count);
				
				for(var i=1;i<=pages;i++){
					page_list+="<li><a num=\""+i+"\" "+(Math.abs(i-page)>2?"style=\"display:none\"":"")+" class=\"pagenum\" href=\"javascript:void(0);\">"+i+"</a></li>";
				}
				$(".pagination").html("<li><a href=\"javascript:void(0);\" class=\"prev\"><i class=\"fa fa-arrow-left\"></i></a></li>"+page_list+"<li><a href=\"javascript:void(0);\"  class=\"next\"><i class=\"fa fa-arrow-right\"></i></a></li>");

				$(".pagination").find("a[num="+page+"]").parent().addClass("active");
				
				$("td a.delete").editable({
					url: "/ajax/services_change.php",
					success: function(response,newValue){
						if($(this).hasClass("delete"))
							$(this).parents("tr:first").remove();
					}
				});
			},
			"html"
		);
	});
	
	$("#documents_count,#orders").on("change",function(){
		$(".pagination li").removeClass("active");
		var e = $.Event("keyup");
		e.which = 13;
		$("#documents_filter").triggerHandler(e);
	});
	$(document).on("click","a.pagenum",function(){
		$(this).parents("ul:first").find("li.active").removeClass("active");
		$(this).parent().addClass("active");
		var e = $.Event("keyup");
		e.which = 13;
		$("#documents_filter").triggerHandler(e);
	});
	$(document).on("click",".pagination a.next",function(){
		$(this).parents("ul:first").find("li.active").next().find("a.pagenum").trigger("click");
	});
	$(document).on("click",".pagination a.prev",function(){
		$(this).parents("ul:first").find("li.active").prev().find("a.pagenum").trigger("click");
	});
	
	var e = $.Event("keyup",{which:13});
	$("#documents_filter").trigger(e);

');

?>

<section>
	<div class="row">
		<article class="col-lg-12">
	
			<div class="jarviswidget jarviswidget-color-blue" id="wid-id-10" 
				data-widget-editbutton="false"
				data-widget-colorbutton="false"
				data-widget-deletebutton="false"
				data-widget-togglebutton="false">
	
				<header>
					<span class="widget-icon"> <i class="fa fa-table"></i> </span>
					<h2>Документы</h2>

				</header>

				<div>	

					<form class="smart-form" method="post">	
						<div class="row">
							<section class="col col-3">
								<label class="label">Произвольный поиск</label>
								<label class="input">
									<input type="text" id="documents_filter">
								</label>
							</section>
							<section class="col col-3">
								<label class="label">Заказы</label>
								<select class="autoselect th-filter" id="orders" placeholder="выберите из списка...">
										<option value="0">Все заказы</option>
										<?
											foreach($orders->orders_id as $order_){
												echo '<option value="'.$order_[0]['m_orders_id'].'">
														'.$order_[0]['m_orders_name'].'
													</option>';
											}
										?>
								</select>
							</section>
							<section class="col col-1">
								<label class="label">Выводить по</label>
								<select class="autoselect th-filter" id="documents_count" placeholder="выберите из списка...">
										<option value="10" >10 поз.</option>
										<option value="20">20 поз.</option>
										<option value="30" selected >30 поз.</option>
										<option value="50">50 поз.</option>
										<option value="10000000">Все поз.</option>
								</select>
							</section>
						</div>
						<div class="row">
							<section class="col col-3">
								<ul class="pagination">
									<li>
										<a href="javascript:void(0);" class="prev"><i class="fa fa-arrow-left"></i></a>
									</li>
									<li>
										<a href="javascript:void(0);" class="next"><i class="fa fa-arrow-right"></i></a>
									</li>
								</ul>
							</section>
						</div>
					</form>
					
					<div class="alert alert-info no-margin fade in table-info">
						<button class="close" data-dismiss="alert">
							×
						</button>
						<i class="fa-fw fa fa-info"></i>
						<span></span>
					</div>

					<div class="widget-body">

						<div class="table-responsive">

							<table id="documents" class="table table-bordered table-striped table-condensed table-hover smart-form has-tickbox" width="100%">
								<thead>
									<tr>
										<th style="width:2%" class="no-order">
											<label class="checkbox">
												<input type="checkbox" class="checkbox tr" id="m_documents_id_all">
												<i></i>
											</label>
										</th>
										<th style="width:6%">Номер <sup>id</sup></th>
										<th style="width:10%" class="order">Дата</th>
										<th style="width:9%">Заказ</th>
										<th style="width:21%">Документ</th>
										<th style="width:6%">Сумма</th>
										<th style="width:14%">Заказчик</th>
										<th style="width:12%">Исполнитель</th>
										<th style="width:12%">Скачать</th>
										<th style="width:10%"></th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>
						
						</div>

					</div>

				</div>

			</div>

		</article>
	</div>
</section>