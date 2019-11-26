<?php
define('_DSITE', 1);
include_once($_SERVER['DOCUMENT_ROOT'].'/../functions/classes/product_attributes.php'); 
$productAttributes = new ProductAttributes();
//типы аттрибута
$attributeTypes = $productAttributes->getMap();
$attributesEnum = isset($_REQUEST['id']) ? $productAttributes->getAttributesListById($_REQUEST['id']) : array();
$cntProperty = count($attributesEnum) + 3;
?>

<hr>
<div class="row">
  <div class="col col-xs-12">
    <label class="label">Значения списка</label>
    <table class="table table-striped table-bordered table-hover no-footer" id="table_property">
      <thead>
        <tr>
          <td>Значение</td>
          <td>Сортировка</td>
          <td>По умолчанию</td>
        </tr>
      </thead>
      <tbody>
      <?for($i = 0; $i < $cntProperty; $i++) {
        $num = !empty($attributesEnum[$i]) ? $attributesEnum[$i]['id'] : 'n'.$i;
      ?>
        <tr>
          <td>
            <label class="input">
              <input type="text" name="property_values[<?=$num?>][value]" value="<?= !empty($attributesEnum[$i]) ? $attributesEnum[$i]['value'] : '' ?>">
            </label>
          </td>
          <td>
            <label class="input">
              <input type="number" name="property_values[<?=$num?>][sort]" value="<?= !empty($attributesEnum[$i]) ? $attributesEnum[$i]['sort'] : '' ?>">
            </label>
          </td>
          <td><input type="radio" name="property_values_default" <?= !empty($attributesEnum[$i]) && $attributesEnum[$i]['default'] == 1 ? 'checked' : '' ?> value="<?=$num?>"></td>
        </tr>
      <?}?>
      </tbody>
    </table>
    <button class="btn btn-primary" id="property_cnt_btn">Добавить</button>
    <input type="hidden" name="property_cnt" id="property_cnt" value="<?=$cntProperty?>">
  </div>
</div>
<hr>

<script>
	$('#property_cnt_btn').click(function(event) {
		event.preventDefault();

		let cnt = +$('#property_cnt').val();
		$('#table_property tbody').append(`
			<tr>
				<td>
					<label class="input">
						<input type="text" name="property_values[${cnt}][value]" value="">
					</label>
				</td>
				<td>
					<label class="input">
						<input type="number" name="property_sort[${cnt}][sort]" value="">
					</label>
				</td>
				<td><input type="radio" name="property_values_default" value="${cnt}"></td>
			</tr>`
		);
		
		$('#property_cnt').val(++cnt);

		return false;
	})
</script>