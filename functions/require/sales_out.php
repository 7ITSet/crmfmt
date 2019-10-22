<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$terminal;
?>
<script>
$(document).ready(function(){
    var otable = $('#datatable_sales_out').DataTable({
		 "order": [[5,"asc"]]
    });
    $("#datatable_sales_out thead th input[type=text]").on( 'keyup change', function () {
        otable
            .column( $(this).parent().index()+':visible' )
            .search( this.value )
            .draw();       
    });
});
</script>
<!-- widget grid -->
				<section id="widget-grid" class="">
				
					<!-- row -->
					<div class="row">
				
						<!-- NEW WIDGET START -->
						<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						
							<!-- Widget ID (each widget will need unique ID)-->
							<div class="jarviswidget jarviswidget-color-blue" id="wid-id-1" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false">
												

				
								<header>
									<span class="widget-icon"> <i class="fa fa-table"></i> </span>
									<h2>Транзакции (дебет)</h2>
				
								</header>
				
								<!-- widget div-->
								<div>				
									<!-- widget content -->
									<div class="widget-body no-padding">
				
										<table id="datatable_sales_out" class="table table-striped table-bordered table-hover" width="100%">
					
									        <thead>
												<tr>
													<th class="hasinput" style="width:7%">
														<input type="text" class="form-control" placeholder="Фильтр по ID" />
													</th>
													<th class="hasinput" style="width:35%">
														<input type="text" class="form-control" placeholder="Фильтр по магазину" />
													</th>
													<th class="hasinput" style="width:10%">
														<input type="text" class="form-control" placeholder="Фильтр по типу" />
													</th>
													<th class="hasinput" style="width:10%">
														<input type="text" class="form-control" placeholder="Фильтр по сумме продажи" />
													</th>
													<th class="hasinput" style="width:10%">
														<input type="text" class="form-control" placeholder="Фильтр по сумме бонусов" />	
													</th>
													<th class="hasinput icon-addon" >
														<input id="dateselect_filter" type="text" placeholder="Фильтр по дате" class="form-control datepicker" data-dateformat="yy/mm/dd">
														<label for="dateselect_filter" class="glyphicon glyphicon-calendar no-margin padding-top-15" rel="tooltip" title=""></label>
													</th>
													<th class="hasinput" style="width:16%">
														<input type="text" class="form-control" placeholder="Фильтр по статусу" />
													</th>
												</tr>
									            <tr>
									                <th>ID</th>
													<th>Магазин</th>
									                <th>Тип продажи</th>
									                <th>Сумма продажи</th>
													<th>Сумма бонусов</th>
									                <th>Дата и время продажи</th>
									                <th>Статус</th>
									            </tr>
									        </thead>
				
											<tbody>
												<?	
$shops=$user->getShops();
for($i=0;$i<sizeof($shops);$i++){
	$shops[$shops[$i]['m_clients_shops_id']]=$shops[$i];
	unset($shops[$i]);
}
$confirm=array(0=>'отменён',1=>'в обработке',2=>'исполнен',3=>'возврат');										
if($t_history=$terminal->history('cash',100000))
	foreach($t_history as $t_h)
		echo '<tr>
				<td>'.$t_h['m_transactions_id'].'</td>
				<td><b>'.$shops[$t_h['m_transactions_m_clients_shops_id']]['m_clients_shops_name'].'</b><br/>'.$shops[$t_h['m_transactions_m_clients_shops_id']]['m_clients_shops_addr_full'].'</td>
				<td>покупка за наличные</td>
				<td style="text-align:right">'.$t_h['m_transactions_sum_buy'].'</td>
				<td style="text-align:right">'.$t_h['m_transactions_sum_bonus'].'</td>
				<td>'.date('Y/m/d G:i:s',$t_h['m_transactions_date']).'</td>
				<td>'.$confirm[$t_h['m_transactions_confirm']].'</td>
			</tr>';
												?>
											</tbody>
										</table>
				
									</div>
									<!-- end widget content -->
				
								</div>
								<!-- end widget div -->
				
							</div>
							<!-- end widget -->
	
						</article>
						<!-- WIDGET END -->

					</div>
				
					<!-- end row -->
				
					<!-- end row -->
				
				</section>
				<!-- end widget grid -->