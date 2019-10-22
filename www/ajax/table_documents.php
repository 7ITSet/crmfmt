<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
require_once('../../functions/classes/info.php');
require_once('../../functions/classes/orders.php');
require_once('../../functions/classes/contragents.php');

$sql=new sql;
$info=new info;
$orders=new orders;
$contragents=new contragents;

//список категорий
$q='SELECT * FROM `formetoo_cdb`.`m_documents_templates`;';
$templates=$sql->query($q,'m_documents_templates_id');

$w=trim(get('search',array('\\','%','_','\''),array('\\\\','\%','\_','\\\'')));
$w=explode(' ',$w);

$limit=get('limit')?get('limit'):10;
$page=get('page')?get('page'):1;
$start=$limit*$page-$limit;
$order=get('order');

//ИЩЕМ ПО ПОЛНОМУ СООТВЕТСВИЮ
$ql='SELECT COUNT(`m_documents_id`) FROM `formetoo_cdb`.`m_documents`
		INNER JOIN `formetoo_cdb`.`m_documents_templates`
		ON `m_documents`.`m_documents_templates_id`=`m_documents_templates`.`m_documents_templates_id`
		INNER JOIN `formetoo_cdb`.`m_orders`
		ON `m_documents`.`m_documents_order`=`m_orders`.`m_orders_id`
		INNER JOIN `formetoo_cdb`.`m_contragents`
		ON `m_documents`.`m_documents_customer`=`m_contragents`.`m_contragents_id`
		WHERE 
			(`m_documents_numb` LIKE \'%'.implode(' ',$w).'%\' OR 
			`m_orders`.`m_orders_name` LIKE \'%'.implode(' ',$w).'%\' OR 
			`m_documents_templates`.`m_documents_templates_name` LIKE \'%'.implode(' ',$w).'%\' OR 
			`m_contragents`.`m_contragents_c_name_full` LIKE \'%'.implode(' ',$w).'%\' OR `m_contragents`.`m_contragents_c_name_short` LIKE \'%'.implode(' ',$w).'%\' OR `m_contragents`.`m_contragents_p_fio` LIKE \'%'.implode(' ',$w).'%\')
			'.($order?' AND `m_documents_order`='.$order:'').';';

$q='SELECT * FROM `formetoo_cdb`.`m_documents`
		INNER JOIN `formetoo_cdb`.`m_documents_templates`
		ON `m_documents`.`m_documents_templates_id`=`m_documents_templates`.`m_documents_templates_id`
		INNER JOIN `formetoo_cdb`.`m_orders`
		ON `m_documents`.`m_documents_order`=`m_orders`.`m_orders_id`
		INNER JOIN `formetoo_cdb`.`m_contragents`
		ON `m_documents`.`m_documents_customer`=`m_contragents`.`m_contragents_id`
		WHERE 
			(`m_documents_numb` LIKE \'%'.implode(' ',$w).'%\' OR 
			`m_orders`.`m_orders_name` LIKE \'%'.implode(' ',$w).'%\' OR 
			`m_documents_templates`.`m_documents_templates_name` LIKE \'%'.implode(' ',$w).'%\' OR 
			`m_contragents`.`m_contragents_c_name_full` LIKE \'%'.implode(' ',$w).'%\' OR `m_contragents`.`m_contragents_c_name_short` LIKE \'%'.implode(' ',$w).'%\' OR `m_contragents`.`m_contragents_p_fio` LIKE \'%'.implode(' ',$w).'%\')
			'.($order?' AND `m_documents_order`='.$order:'').' 	
		GROUP BY `m_documents_id`
		ORDER BY `m_documents_date` DESC
		LIMIT '.$start.','.$limit.';';
	
if($res=$sql->query($q))
	$p=$res;
else{
	//ИЩЕМ ПО ПОЛНОМУ СООТВЕТСВИЮ
	$ql='SELECT COUNT(`m_documents_id`) FROM `formetoo_cdb`.`m_documents`
			INNER JOIN `formetoo_cdb`.`m_documents_templates`
			ON `m_documents`.`m_documents_templates_id`=`m_documents_templates`.`m_documents_templates_id`
			INNER JOIN `formetoo_cdb`.`m_orders`
			ON `m_documents`.`m_documents_order`=`m_orders`.`m_orders_id`
			INNER JOIN `formetoo_cdb`.`m_contragents`
			ON `m_documents`.`m_documents_performer`=`m_contragents`.`m_contragents_id`
			WHERE 
				(`m_documents_numb` LIKE \'%'.implode(' ',$w).'%\' OR 
				`m_orders`.`m_orders_name` LIKE \'%'.implode(' ',$w).'%\' OR 
				`m_documents_templates`.`m_documents_templates_name` LIKE \'%'.implode(' ',$w).'%\' OR 
				`m_contragents`.`m_contragents_c_name_full` LIKE \'%'.implode(' ',$w).'%\' OR `m_contragents`.`m_contragents_c_name_short` LIKE \'%'.implode(' ',$w).'%\' OR `m_contragents`.`m_contragents_p_fio` LIKE \'%'.implode(' ',$w).'%\')
				'.($order?' AND `m_documents_order`='.$order:'').';';

	$q='SELECT * FROM `formetoo_cdb`.`m_documents`
			INNER JOIN `formetoo_cdb`.`m_documents_templates`
			ON `m_documents`.`m_documents_templates_id`=`m_documents_templates`.`m_documents_templates_id`
			INNER JOIN `formetoo_cdb`.`m_orders`
			ON `m_documents`.`m_documents_order`=`m_orders`.`m_orders_id`
			INNER JOIN `formetoo_cdb`.`m_contragents`
			ON `m_documents`.`m_documents_performer`=`m_contragents`.`m_contragents_id`
			WHERE 
				(`m_documents_numb` LIKE \'%'.implode(' ',$w).'%\' OR 
				`m_orders`.`m_orders_name` LIKE \'%'.implode(' ',$w).'%\' OR 
				`m_documents_templates`.`m_documents_templates_name` LIKE \'%'.implode(' ',$w).'%\' OR 
				`m_contragents`.`m_contragents_c_name_full` LIKE \'%'.implode(' ',$w).'%\' OR `m_contragents`.`m_contragents_c_name_short` LIKE \'%'.implode(' ',$w).'%\' OR `m_contragents`.`m_contragents_p_fio` LIKE \'%'.implode(' ',$w).'%\')
				'.($order?' AND `m_documents_order`='.$order:'').' 	
			GROUP BY `m_documents_id`
			ORDER BY `m_documents_date` DESC
			LIMIT '.$start.','.$limit.';';
		
	if($res=$sql->query($q))
		$p=$res;
	//ЕСЛИ РЕЗУЛЬТАТОВ НЕТ - ИЩЕМ ПО ЧАСТИЧНОМУ СООТВЕТСВИЮ
	else{
		$like=[];
		foreach($w as $_w){
			if (mb_strlen($_w,'utf-8')<3) continue;
			$like[]='( 
				`m_orders`.`m_orders_name` LIKE \'%'.$_w.'%\' OR 
				`m_documents_templates`.`m_documents_templates_name` LIKE \'%'.$_w.'%\' OR 
				`m_contragents`.`m_contragents_c_name_full` LIKE \'%'.$_w.'%\' OR `m_contragents`.`m_contragents_c_name_short` LIKE \'%'.$_w.'%\' OR `m_contragents`.`m_contragents_p_fio` LIKE \'%'.$_w.'%\'
				)';
		}
		$like=implode(' AND ',$like);
		$q='SELECT * FROM `formetoo_cdb`.`m_documents`
				LEFT JOIN `formetoo_cdb`.`m_documents_templates`
				ON `m_documents`.`m_documents_templates_id`=`m_documents_templates`.`m_documents_templates_id`
				LEFT JOIN `formetoo_cdb`.`m_orders`
				ON `m_documents`.`m_documents_order`=`m_orders`.`m_orders_id`
				LEFT JOIN `formetoo_cdb`.`m_contragents`
				ON `m_documents`.`m_documents_performer`=`m_contragents`.`m_contragents_id` OR `m_documents`.`m_documents_customer`=`m_contragents`.`m_contragents_id`
				WHERE (
					'.($like?$like.' OR':'').'
					`m_documents_numb` LIKE \'%'.$_w.'%\'
				)
				'.($order?' AND `m_documents_order`='.$order:'').' 
				GROUP BY `m_documents_id`
				ORDER BY `m_documents_date` DESC
				LIMIT '.$start.','.$limit.';';
		$ql='SELECT COUNT(`m_documents_id`) FROM `formetoo_cdb`.`m_documents`
				LEFT JOIN `formetoo_cdb`.`m_documents_templates`
				ON `m_documents`.`m_documents_templates_id`=`m_documents_templates`.`m_documents_templates_id`
				LEFT JOIN `formetoo_cdb`.`m_orders`
				ON `m_documents`.`m_documents_order`=`m_orders`.`m_orders_id`
				LEFT JOIN `formetoo_cdb`.`m_contragents`
				ON `m_documents`.`m_documents_performer`=`m_contragents`.`m_contragents_id` OR `m_documents`.`m_documents_customer`=`m_contragents`.`m_contragents_id`
				WHERE (
					'.($like?$like.' OR':'').'
					`m_documents_numb` LIKE \'%'.$_w.'%\'
				)
				'.($order?' AND `m_documents_order`='.$order:'').';';
		if($res=$sql->query($q))
			$p=$res;
	}
}


if(isset($p)&&$p){
	$res=$sql->query($ql);
	echo '<tr style="display:none" count="'.($res[0]['COUNT(`m_documents_id`)']/2).'" page="'.$page.'"></tr>';
	$i=0;
	$k=0;
	foreach($p as $_docs){
		$performer=$contragents->getInfo($_docs['m_documents_performer'])['m_contragents_c_name_short']?$contragents->getInfo($_docs['m_documents_performer'])['m_contragents_c_name_short']:$contragents->getInfo($_docs['m_documents_performer'])['m_contragents_p_fio'];
		$customer=$contragents->getInfo($_docs['m_documents_customer'])['m_contragents_c_name_short']?$contragents->getInfo($_docs['m_documents_customer'])['m_contragents_c_name_short']:$contragents->getInfo($_docs['m_documents_customer'])['m_contragents_p_fio'];

		$order=isset($orders->orders_id[$_docs['m_documents_order']][0])?$orders->orders_id[$_docs['m_documents_order']][0]:array('m_orders_id'=>0);
		$order['m_orders_discount']=(isset($type['m_documents_templates_id'])&&$type['m_documents_templates_id']==3522102145)?0:(isset($order['m_orders_discount'])?$order['m_orders_discount']:'');
		$p=json_decode($_docs['m_documents_params'],true);
		$nds18=isset($p['doc_nds18'])&&$p['doc_nds18']?'в т.ч. НДС 18%: <b>'.transform::price_o($p['doc_nds18']-$p['doc_nds18']*$order['m_orders_discount']/100).'</b>':'без НДС';
		$sum=isset($p['doc_sum'])&&$p['doc_sum']?$p['doc_sum']:0;
		$url='/files/'.$_docs['m_documents_templates_folder'].'/'.$_docs['m_documents_folder'].'/'.$_docs['m_documents_templates_filename'].'.pdf?'.mt_rand(100000,999999);
		$pay_methods=array(1=>'Оплата картой',2=>'Счёт безнал',3=>'Наличные');
		
		$tel=$_docs['m_documents_customer']!='3363726835'?$info->getTel($_docs['m_documents_customer']):false;

		echo '
		<tr '.(isset($order['m_orders_status'])&&$order['m_orders_status']==8?'class="inactive"':'').'>
			<td class="check">
				<label class="checkbox">
					<input type="checkbox" class="checkbox tr m_documents_id" value="'.$_docs['m_documents_id'].'">
					<i></i>
				</label>
			</td>
			<td>
				'.$_docs['m_documents_numb'].'<br/><span style="color:#999;font-size:80%">id: '.$_docs['m_documents_id'].'</span>
			</td>
			<td data-sort="'.dtu($_docs['m_documents_date'],'Y-m-d').'">
				'.dtu($_docs['m_documents_date'],'d.m.Y H:i:s').'<br/>
				<span style="color:#999;font-size:80%"><i class="fa fa-refresh"></i>&nbsp;&nbsp;'.dtu($_docs['m_documents_update'],'d.m.Y H:i:s').'</span>
			</td>
			<td class="orderName unionrows">
				'.($order['m_orders_id']?'<a href="/orders/new/?action=details&m_orders_id='.$order['m_orders_id'].'">'.$order['m_orders_name'].'</a>':'—').'
			</td>
			<td>
				<table>
					<tr>
						<td style="vertical-align:top;padding-right:5px;">
							<span class="doc-type smeta" style="background:'.transform::colorid($order['m_orders_id']).'">'.$_docs['m_documents_templates_name_short'].(isset($p['additional'])&&$p['additional']?'<sup><span style="font-size:12px;">2</span></sup>':'').'</span>
						</td>
						<td style="vertical-align:top">
							<a href="/documents/new/?m_documents_id='.$_docs['m_documents_id'].'&m_documents_templates_id='.$_docs['m_documents_templates_id'].'&m_documents_order='.$_docs['m_documents_order'].'&action=details">'.$_docs['m_documents_templates_name'].'</a>
							<br/>
							<span style="color:#999;display:block;clear:both;">'.$_docs['m_documents_comment'].'</span>
						</td>
					</tr>
				</table>
			</td>
			<td data-order="'.number_format($sum-$sum*$order['m_orders_discount']/100,2,'.','').'">
				<b><nobr>'.transform::price_o(number_format($sum-$sum*$order['m_orders_discount']/100,2,'.','')).'</nobr></b><br/>
				<span style="color:#999;font-size:80%"><nobr>'.$nds18.'</nobr></span>
				<div class="btn-group" style="margin-top:5px;">
					<button class="btn btn-default btn-sm dropdown-toggle change_pay_methods_selected" data-toggle="dropdown">
						'.$pay_methods[$order['m_orders_pay_method']].' <span class="caret"></span>
					</button>
					<ul class="dropdown-menu">';
		foreach($pay_methods as $_n=>$_pt)
			echo '
						<li>
							<a href="javascript:void(0);" class="change_pay_methods" data-method="'.$_n.'" data-order="'.$order['m_orders_id'].'">'.$_pt.'</a>
						</li>';
		echo '
					</ul>
				</div>
			</td>
			<td class="unionrows">
				<a href="/contragents/new/?action=details&m_contragents_id='.$contragents->getInfo($_docs['m_documents_customer'])['m_contragents_id'].'">'.$customer.'</a>';
	echo '<table class="table-mini" style="margin-top:5px;">';
	if($tel){
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
	}
	if($contragents->getInfo($_docs['m_documents_customer'])['m_contragents_email'])
		echo '<tr><td><i class="icon-append fa fa-envelope-o" style="color:#76B8ED"></i></td><td><a href="mailto:'.$contragents->getInfo($_docs['m_documents_customer'])['m_contragents_email'].'">'.$contragents->getInfo($_docs['m_documents_customer'])['m_contragents_email'].'</a></td></tr>';
	echo '
			</table>
			</td>
			<td>
				'.$performer.'
			</td>
			
			<td>
				<a class="document_pdf" title="Скачать" href="'.$url.'" target="_blank">
					<i class="fa fa-lg fa-file-pdf-o"></i> .pdf,&nbsp;'.$_docs['m_documents_filesize'].'
				</a><br/>',
				($_docs['m_documents_filesize']
					?'<button type="button" class="btn btn-xs btn-primary send_sms_to_client" '.($tel?'':'disabled').' style="margin-top:5px;margin-right:5px;float:left;" data-pk="'.$_docs['m_documents_id'].'" data-type="sms" data-name="m_documents_send">
						<i class="fa fa-send"></i>
						SMS
					</button>
					<button type="button" class="btn btn-xs btn-primary send_email_to_client" '.($contragents->getInfo($_docs['m_documents_customer'])['m_contragents_email']?'':'disabled').' style="margin-top:5px;float:left;" data-pk="'.$_docs['m_documents_id'].'" data-type="email" data-name="m_documents_send">
						<i class="fa fa-send"></i>
						E-mail
					</button>'
					:'<span class="small">Счёт не сформирован</span>'
				),
			'</td>		
			<td align="center" style="vertical-align:middle!important;">
				<a title="Сделать копию документа" class="btn btn-success btn-xs" href="/documents/new/?m_documents_id='.$_docs['m_documents_id'].'&action=document_copy"><i class="fa fa-copy"></i></a>
				<a title="Редактировать документ" class="btn btn-primary btn-xs" href="/documents/new/?m_documents_id='.$_docs['m_documents_id'].'&m_documents_templates_id='.$_docs['m_documents_templates_id'].'&m_documents_order='.$_docs['m_documents_order'].'&action=details"><i class="fa fa-pencil"></i></a>
				<a title="Удалить документ" class="btn btn-danger btn-xs delete" href="#" data-pk="'.$_docs['m_documents_id'].'" data-name="m_documents_id" data-title="Введите пароль для удаления документа" data-placement="left"><i class="fa fa-trash-o"></i></a>
			</td>
		</tr>';
	}
}

unset($sql);
?>