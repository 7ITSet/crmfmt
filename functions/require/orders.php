<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$workers,$info,$services,$contragents,$orders,$documents;

$content->setJS('
	$("[name=m_orders_active]").on("change",function(){
		$(this).prop("checked")===true?$("tr.inactive").hide():$("tr.inactive").show();
	});
');
?>
<section>
	<div class="row">
		<article class="col-lg-12">
	
			<div class="jarviswidget jarviswidget-color-blue" id="wid-id-1" 
				data-widget-editbutton="false"
				data-widget-colorbutton="false"
				data-widget-deletebutton="false"
				data-widget-togglebutton="false">
	
				<header>
					<span class="widget-icon"> <i class="fa fa-table"></i> </span>
					<h2>Список заказов</h2>

				</header>

				<div>				

					<div class="widget-body no-padding">
<div style="padding:5px 0 0 8px;background:#fafafa;">
<div class="checkbox">
	<label>
	  <input type="checkbox" class="checkbox style-0" name="m_orders_active" checked="checked">
	  <span>Только активные заказы</span>
	</label>
</div>
</div>
						<table id="datatable_clients" class="datatable table table-striped table-bordered table-hover" width="100%">
	
							<thead>
								<tr>
									<th style="width:15%">Наименование</th>
									<th class="order" order="desc" style="width:6%">Дата</th>
									<th style="width:9%">Статус</th>
									<th style="width:8%">Исполнитель</th>
									<th style="width:18%">Заказчик</th>
									<th style="width:4%">Сумма</th>
									<th style="width:8%">Комментарий</th>
									<th style="width:4%">Детали</th>
								</tr>
							</thead>
							<tbody>
								<?
if($orders->orders_id){
	$i=0;
	foreach($orders->orders_id as $_order){
		$_order=$_order[0];
		
		$status='<ul class="table-ul">';
		foreach($info->getOrdersStatus() as $_status)
			$status.='<li'.($_order['m_orders_status']>$_status[0]['m_info_orders_status_id']?' class="done"':($_order['m_orders_status']==$_status[0]['m_info_orders_status_id']?' class="actual"':'')).'>'.$_status[0]['m_info_orders_status_id'].'. '.$_status[0]['m_info_orders_status_name'].'</li>';
		$status.='</ul>';
		

		$performer=$contragents->getInfo($_order['m_orders_performer'])['m_contragents_c_name_short']?$contragents->getInfo($_order['m_orders_performer'])['m_contragents_c_name_short']:$contragents->getInfo($_order['m_orders_performer'])['m_contragents_p_fio'];
		$customer=$contragents->getInfo($_order['m_orders_customer'])['m_contragents_c_name_short']?$contragents->getInfo($_order['m_orders_customer'])['m_contragents_c_name_short']:$contragents->getInfo($_order['m_orders_customer'])['m_contragents_p_fio'];
		
		$docs=$documents->getOrderDocs($_order['m_orders_id']);
		
		$tel=$info->getTel($_order['m_orders_customer']);
		
		//сумма по всем документам
		$sum=0;
		foreach($docs as $_docs)
			//если шаблон документа нужно калькулировать
			if($documents->documents_templates[$_docs['m_documents_templates_id']][0]['m_documents_templates_calc']){
				$details=json_decode($_docs['m_documents_params'],true);
				$sum+=isset($sum['doc_sum'])?$sum['doc_sum']:0;
			}	
		
		echo '
		<tr '.(isset($_order['m_orders_status'])&&$_order['m_orders_status']==8?'class="inactive"':'').'>
			<td>
				<a href="/orders/new/?m_orders_id='.$_order['m_orders_id'].'&action=details"><span style="font-weight:600;font-size:110%;">'.$_order['m_orders_name'].'</span></a><br/>
				<span style="color:#999;font-size:80%">id: '.$_order['m_orders_id'].'</span>
			</td>
			<td>
				'.dtu($_order['m_orders_date'],'Y-m-d').'<br/>
				<span style="color:#999;font-size:80%"><i class="fa fa-refresh"></i>&nbsp;&nbsp;'.dtu($_order['m_orders_update'],'Y-m-d').'</span>
			</td>
			<td class="status">
				'.$status.'
			</td>
			<td>
				'.$performer.'
			</td>
			<td>
				<a href="/contragents/new/?action=details&m_contragents_id='.$contragents->getInfo($_order['m_orders_customer'])['m_contragents_id'].'">'.$customer.'</a>
				<table class="table-mini" style="margin-top:5px;">
					<tr><td><i class="icon-append fa fa-map-marker" style="color:#76B8ED"></i></td><td>'.(!empty($_order['m_orders_address_full'])?$_order['m_orders_address_full']:'').'</td></tr>
				</table>';
	if($tel){
		echo '<table class="table-mini" style="margin-top:5px;">';
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
	echo '
			</td>
			<td>
				'.$sum.'
			</td>
			<td>
				'.$_order['m_orders_comment'].'
			</td>
			<td align="center">
				<a class="btn btn-primary btn-xs" href="/orders/new/?m_orders_id='.$_order['m_orders_id'].'&action=details"><i class="fa fa-pencil"></i></a>
				<a class="btn btn-danger btn-xs delete" href="#" data-pk="'.$_order['m_orders_id'].'" data-name="m_orders_id" data-title="Введите пароль для удаления заказа" data-placement="left"><i class="fa fa-trash-o"></i></a>
			</td>
		</tr>';
	}
}
								?>
							</tbody>
						</table>

					</div>

				</div>

			</div>

		</article>
	</div>
</section>