<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$content,$contragents,$logistic,$info,$products;

$units=array();
foreach($products->attr_id as $_attr){
	$_attr=$_attr[0];
	$units=array_merge($units,explode('|',$_attr['m_products_attributes_list_unit']));
}
foreach($products->units_id as $_attr){
	$_attr=$_attr[0];
	$units=array_merge($units,explode('|',$_attr['m_info_units_name']));
}
$units=array_unique(array_diff($units,array('')));
array_walk($units,function(&$el){$el='"'.$el.'"';});

if($id=get('m_products_attributes_groups_id')){
	$group=$products->attr_groups[$id][0];
	$group['m_products_attributes_groups_list_id']=explode('|',$group['m_products_attributes_groups_list_id']);
	
	$content->setJS('
	
		runAllForms();
			
		$("#products-add").validate({
			rules : {
				m_products_name : {
					maxlength : 180,
					required : true
				},
				m_products_unit : {
					required : true
				},
				m_products_price_general : {
					maxlength : 15,
					required : true
				},
				m_products_price_general_w : {
					maxlength : 15,
					required : true
				},
				m_products_price_wholesale : {
					maxlength : 15,
					required : true
				},
				m_products_price_wholesale_w : {
					maxlength : 15,
					required : true
				},
				m_products_contragents_id : {
					required : true
				},
				"m_products_categories_id[]" : {
					required : true
				}
			},
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});	

		$("#m_products_attributes_list_unit").editable({
			inputclass: "input-large",
			select2: {
				tags: ['.implode(',',$units).'],
				placeholder: "Укажите теги",
				tokenSeparators: [","]
			}
		});	
		$("#m_products_attributes_list_unit").on("save",function(e,params){
			$("[name=\'m_products_attributes_list_unit\']").val(params.newValue.join("|"));
		});

		

		
	');
	?>

	<section id="widget-grid" class="">
		
		<div class="row">
					
			<article class="col-lg-6 sortable-grid ui-sortable">
				<div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">		
					<header>
						<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
						<h2>Редактировать группу атрибутов</h2>
					</header>
					<div>
						<div class="widget-body">
							<form id="products-add" class="smart-form" method="post">
								<fieldset>
                                    <div class="row">
                                        <section class="col col-8">
                                            <label class="label">Наименование группы</label>
                                            <label class="input">
                                                <input type="text" name="m_products_attributes_groups_name" value="<?=$group['m_products_attributes_groups_name']?>">
                                            </label>
                                        </section>
                                        <section class="col col-4">
                                            <label class="checkbox">
                                                <input type="checkbox" name="m_products_attributes_groups_required" <?=($group['m_products_attributes_groups_required']?' checked':'')?> value="1">
                                                <i></i>
                                                Обязательная группа
                                            </label>
                                        </section>
                                    </div>
								<section>
										<label class="label">Список атрибутов</label>
										<select name="m_products_attributes_groups_list_id[]" style="width:100%" multiple class="autoselect" placeholder="выберите из списка...">
											<? 
												$type=array(1=>'Текстовый',2=>'Числовой',3=>'Логический');
												foreach($products->attr_id as $_attr){
													$_attr=$_attr[0];
													echo '<option value="'.$_attr['m_products_attributes_list_id'].'" '.(in_array($_attr['m_products_attributes_list_id'],$group['m_products_attributes_groups_list_id'])?'selected ':'').'>
															'.$_attr['m_products_attributes_list_name'].' ['.$type[$_attr['m_products_attributes_list_type']].($_attr['m_products_attributes_list_unit']?', '.$_attr['m_products_attributes_list_unit']:'').($_attr['m_products_attributes_list_comment']?', ('.$_attr['m_products_attributes_list_comment'].')':'').']
														</option>';																
												}
											?>
										</select>
								</section>
							</fieldset>		
								<footer>
									<button type="submit" class="btn btn-primary">
										<i class="fa fa-save"></i>
										Сохранить данные
									</button>
								</footer>
								<input type="hidden" name="action" value="m_products_attributes_groups_change"/>
								<input type="hidden" name="m_products_attributes_groups_id" value="<?=$group['m_products_attributes_groups_id'];?>"/>
							</form>
						</div>
					</div>
				</div>	
			</article>
		</div>
	</section>
<?
}
else{


$content->setJS('
	
	runAllForms();
		
	$("#products-add").validate({
		rules : {
			m_products_name : {
				maxlength : 180,
				required : true
			},
			m_products_unit : {
				required : true
			},
			m_products_price_general : {
				maxlength : 15,
				required : true
			},
			m_products_contragents_id : {
				required : true
			},
			"m_products_categories_id[]" : {
				required : true
			}
		},
		errorPlacement : function(error, element) {
			error.insertAfter(element.parent());
		}
	});		

	$("#m_products_attributes_list_unit").editable({
		inputclass: "input-large",
		select2: {
			tags: ['.implode(',',$units).'],
			placeholder: "Укажите теги",
			tokenSeparators: [","]
		}
	});	
	$("#m_products_attributes_list_unit").on("save",function(e,params){
		$("[name=\'m_products_attributes_list_unit\']").val(params.newValue.join("|"));
	});
	
	$("input.show").on("change",function(){
		$.post(
			"/ajax/services_change.php",
			{
				name:$(this).attr("data-name"),
				pk:$(this).attr("data-pk"),
				value:$(this).prop("checked")
			}
		);
	});
		
	$(".delete").editable({
		url: "/ajax/services_change.php",
		success: function(response,newValue){
			if($(this).hasClass("delete"))
				$(this).parents("tr:first").remove();
		}
	});
	
	$(".changePassword").editable({
	});
	
	$("#requiredAttrGroup").editable({
    url: "/ajax/edit_attr_required.php",
    type: "text",
    pk: 1,
    name: "m_products_required_attributes_groups_id",
    success: function(response){
        if(response){
            var redirect = $("#requiredAttrGroup").attr("redirect-url");
            document.location.href = redirect;
        }
    },
    error: function(q,w,e){
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
			<div class="jarviswidget" id="wid-id-30" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">		
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Добавить группу атрибутов</h2>
				</header>
				<div>
					<div class="widget-body">
						<form id="products-add" class="smart-form" method="post">
							<fieldset>
                                <div class="row">
								    <section class="col col-8">
                                        <label class="label">Наименование группы</label>
                                        <label class="input">
                                            <input type="text" name="m_products_attributes_groups_name">
                                        </label>
                                    </section>
                                    <section class="col col-4">
                                        <label class="checkbox">
                                            <input type="checkbox" name="m_products_attributes_groups_required" value="1">
                                            <i></i>
                                            Обязательная группа
                                        </label>
                                    </section>
                                </div>
                                <section>
                                    <label class="label">Список атрибутов</label>
                                    <select name="m_products_attributes_groups_list_id[]" style="width:100%" multiple class="autoselect" placeholder="выберите из списка...">
                                        <?
                                        $type=array(1=>'Текстовый',2=>'Числовой',3=>'Логический');
                                        foreach($products->attr_id as $_attr){
                                            $_attr=$_attr[0];
                                            echo '<option value="'.$_attr['m_products_attributes_list_id'].'">
                                                               '.$_attr['m_products_attributes_list_name'].' ['.$type[$_attr['m_products_attributes_list_type']].($_attr['m_products_attributes_list_unit']?', '.$_attr['m_products_attributes_list_unit']:'').($_attr['m_products_attributes_list_comment']?', ('.$_attr['m_products_attributes_list_comment'].')':'').']
                                                           </option>';
                                        }
                                        ?>
                                    </select>
                                    </section>

							</fieldset>		
							<footer>
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-save"></i>
									Сохранить данные
								</button>
							</footer>
							<input type="hidden" name="action" value="m_products_attributes_groups_add"/>
						</form>
					</div>
				</div>
			</div>	
		</article>
	
<?
if($products->attr_groups){
?>
		<article class="col-lg-12">
	
			<div class="jarviswidget jarviswidget-color-blue" id="wid-id-1" 
				data-widget-editbutton="false"
				data-widget-colorbutton="false"
				data-widget-deletebutton="false"
				data-widget-togglebutton="false">
				
				<header>
					<span class="widget-icon"> <i class="fa fa-table"></i> </span>
					<h2>Список атрибутов</h2>
				</header>

				<div>				

					<div class="widget-body no-padding">

						<table class="datatable table table-striped table-bordered table-hover" width="100%">
	
							<thead>
								<tr>				
									<th style="width:20%">Наименование группы</th>
									<th style="width:73%">Атрибуты в группе</th>
									<th style="width:7%">Управление</th>
								</tr>
							</thead>

							<tbody>
								<?
$i=0;
foreach($products->attr_groups as $_group){

	$_group=$_group[0];
	
	echo '<tr>
		<td>',
			$_group['m_products_attributes_groups_name'],
		'</td>
		<td>';
		$type=array(1=>'Текстовый',2=>'Числовой',3=>'Логический');
		$_group['m_products_attributes_groups_list_id']=explode('|',$_group['m_products_attributes_groups_list_id']);
		foreach($_group['m_products_attributes_groups_list_id'] as $_attr)
			echo $products->attr_id[$_attr][0]['m_products_attributes_list_name'].' ['.$type[$products->attr_id[$_attr][0]['m_products_attributes_list_type']].($products->attr_id[$_attr][0]['m_products_attributes_list_unit']?', '.$products->attr_id[$_attr][0]['m_products_attributes_list_unit']:'').($products->attr_id[$_attr][0]['m_products_attributes_list_comment']?', ('.$products->attr_id[$_attr][0]['m_products_attributes_list_comment'].')':'').']<br/>';
echo	'</td>
		<td>';

    if($_group['m_products_attributes_groups_required']){
        echo '<a href="javascript:void(0);" id="requiredAttrGroup" redirect-url="'.url().'?action=change&m_products_attributes_groups_id='.$_group['m_products_attributes_groups_id'].'" title="Редактировать" class="btn btn-primary btn-xs btn-default" data-placement="left"><i class="fa fa-pencil"></i></a>';
    } else {
        echo '<a href="'.url().'?action=change&m_products_attributes_groups_id='.$_group['m_products_attributes_groups_id'].'" title="Редактировать" class="btn btn-primary btn-xs btn-default change" data-type="text"><i class="fa fa-pencil"></i></a>';
    }
    echo '<a href="javascript:void(0);" title="Удалить" class="btn btn-xs btn-danger delete" data-type="text" data-pk="'.$_group['m_products_attributes_groups_id'].'" data-name="m_products_attributes_groups_id" data-title="Введите пароль для удаления записи" data-placement="left">
				<i class="fa fa-trash-o"></i>
			</a>
		</td>
	</tr>';
}
								?>
							</tbody>
						</table>
<!--                       <a href="'.url().'?action=change&m_products_attributes_groups_id='.$_group['m_products_attributes_groups_id'].'" title="Редактировать" class="btn btn-primary btn-xs btn-default change" data-type="text">-->
					</div>

				</div>

			</div>

		</article>

<?
}
?>		
	</div>
</section>
<?}?>