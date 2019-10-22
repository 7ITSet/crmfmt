<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$services,$info,$products,$orders;

$content->setJS('
	
	/* автовыбор сторон при выборе заказа */
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
	
	$("[name=\'m_documents_signature\']").prop("checked",true);
	
');

if(get('m_documents_id')){

	$document=$documents->getInfo(get('m_documents_id'));
	$document['params']=json_decode($document['m_documents_params']);
													/* РЕДАКТИРОВАНИЕ ДОКУМЕНТА */
?>
<header>
	Параметры договора
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
	</div>
</fieldset>	
<footer>
	<button type="submit" class="btn btn-primary">
		<i class="fa fa-save"></i>
		Сохранить данные
	</button>
</footer>
<input type="hidden" name="action" value="m_documents_change"/>
<input type="hidden" name="m_documents_templates_id" value="4234525326"/>
<input type="hidden" name="m_documents_id" value="<?=$document['m_documents_id']?>"/>
<?}
														/* НОВЫЙ ДОКУМЕНТ */
else{?>
<header>
	Параметры договора
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
	</div>
</fieldset>	
<footer>
	<button type="submit" class="btn btn-primary">
		<i class="fa fa-save"></i>
		Сохранить данные
	</button>
</footer>
<input type="hidden" name="action" value="m_documents_add"/>
<input type="hidden" name="m_documents_templates_id" value="4234525326"/>
<?}?>