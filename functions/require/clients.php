<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$info;
?>

<section id="widget-grid" class="">
	<div class="row">
		<article class="col-lg-12">
	
			<div class="jarviswidget jarviswidget-color-blue" id="wid-id-10" 
				data-widget-editbutton="false"
				data-widget-colorbutton="false"
				data-widget-deletebutton="false"
				data-widget-togglebutton="false">
	
				<header>
					<span class="widget-icon"> <i class="fa fa-table"></i> </span>
					<h2>Зарегистрированные контрагенты</h2>

				</header>

				<div>

					<div class="widget-body no-padding">

						<table id="datatable_clients" class="table table-striped table-bordered table-hover datatable" width="100%">
	
							<thead>
								<tr>
									<th style="width:26%">Контрагент</th>
									<th style="width:35%">Контактная информация</th>
									<th style="width:8%">Сумма заказов</th>
									<th class="order" order="desc" style="width:10%">Дата регистрации</th>
									<th style="width:4%">Детали</th>
								</tr>
							</thead>
							<tbody>
								<?
if($contragents->getInfo()){
	$clients=$contragents->getInfo();
	$type_select='';
	foreach($contragents->contragents_type as $id_=>$type_){
		$type_select.='<option value="'.$id_.'">'.$type_[0]['m_info_contragents_type_name'].'</option>';
	}
	foreach($clients as $clients_){
		$clients_=$clients_[0];
		$tel=$info->getTel($clients_['m_contragents_id']);
		$type=explode('|',$clients_['m_contragents_type']);
		$_address=$info->getAddress($clients_['m_contragents_id']);
		$_address=change_key($_address,'m_address_type',true);
		echo '
		<tr>
			<td>
				<a href="/contragents/new/?m_contragents_id='.$clients_['m_contragents_id'].'&action=details">
					'.($clients_['m_contragents_c_name_full']?$clients_['m_contragents_c_name_full']:$clients_['m_contragents_p_fio']).'
				</a>'.($clients_['m_contragents_c_name_short']?('<br/><span style="color:#999">'.$clients_['m_contragents_c_name_short'].'</span>'):'').'
				<br/>
				<span style="color:#999">ИНН '.$clients_['m_contragents_c_inn'].'</span>
			</td>
			<td>
				<table class="table-mini">';
	if(isset($_address['2'])&&$_address['2'])
		echo '<tr><td><i class="icon-append fa fa-map-marker" style="color:#76B8ED"></i></td><td>'.$_address['2']['m_address_full'].'</td></tr>';
	if(isset($_address['3'])&&$_address['3'])
		echo '<tr><td><i class="icon-append fa fa-paper-plane" style="color:#76B8ED"></i></td><td>'.$_address['3']['m_address_full'].'</td></tr>';
	if(isset($_address['1'])&&$_address['1'])
		echo '<tr><td><i class="icon-append fa fa-mortar-board" style="color:#76B8ED"></i></td><td>'.$_address['1']['m_address_full'].'</td></tr>';
	echo '
				</table>';
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
		echo '<table class="table-mini">
				'.($clients_['m_contragents_email']?'<tr><td><i class="fa fa-envelope-o" style="color:#76B8ED"></i></td><td><a href="mailto:'.$clients_['m_contragents_email'].'">'.$clients_['m_contragents_email'].'</a></td></tr>':'').'
				'.($clients_['m_contragents_www']?'<tr><td><i class="fa fa-external-link" style="color:#76B8ED"></i></td><td><a href="http://'.str_replace('http://','',$clients_['m_contragents_www']).'" target="_blank">'.$clients_['m_contragents_www'].'</a></td></tr>':'').'
				</table>
			</td>
			<td></td>
			<td>'.$clients_['m_contragents_date'].'</td>
			<td align="center">
				<a class="btn btn-primary btn-xs" href="/contragents/new/?m_contragents_id='.$clients_['m_contragents_id'].'&action=details"><i class="fa fa-pencil"></i></a>
				<a class="btn btn-danger btn-xs delete" href="#" data-pk="'.$clients_['m_contragents_id'].'" data-name="m_contragents_id" data-title="Введите пароль для удаления записи" data-placement="left"><i class="fa fa-trash-o"></i></a>
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