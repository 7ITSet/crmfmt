<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
require_once('../../functions/classes/info.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/../functions/classes/product_attributes.php'); 

$sql=new sql();
$info=new info();
global $e;

$productAttributes = new ProductAttributes();

$data['attirbutes_group_id'] = array();
$data['products_id'] = array();

array_walk($data,'check');

if ($e) {
  echo "ERROR";
  die();
} 


$q='SELECT `m_products_attributes_groups_list_id` FROM `formetoo_main`.`m_products_attributes_groups` WHERE `m_products_attributes_groups_id` = '.$data['attirbutes_group_id'].' LIMIT 1;';

$res = $sql->query($q);

$group = array();

if(!empty($res)){
  foreach ($res as $_res){
    $group = explode('|', $_res['m_products_attributes_groups_list_id']);
  }
}

if (!isset($group)) {
  echo "ERROR";
  die();
}

if ($data['products_id']) {
  // $q = 'SELECT * FROM `formetoo_main`.`m_products_attributes`
  //   LEFT JOIN `formetoo_main`.`m_products_attributes_list` ON
  //     `m_products_attributes`.`m_products_attributes_list_id`=`m_products_attributes_list`.`m_products_attributes_list_id`
  //   WHERE
  //     `m_products_attributes_product_id`=' . $data['products_id'] . ';';
  
  // $q = 'SELECT * FROM `formetoo_main`.`m_products_attributes_list` WHERE `m_products_attributes_list_id` IN(' . implode(',', $group) . ');';
  // $attr = $sql->query($q);

  // $q = 'SELECT `m_products_attributes`.*, `m_products_attributes_list`.* FROM `formetoo_main`.`m_products_attributes` 
  //   RIGHT JOIN `formetoo_main`.`m_products_attributes_list` ON `m_products_attributes_list`.
  //    `m_products_attributes_list_id` IN(' . implode(',', $group) . ') AND `m_products_attributes_list`.`m_products_attributes_list_id` = `m_products_attributes`.`m_products_attributes_list_id`
  //   LEFT JOIN `formetoo_main`.`m_products_attributes` ON `m_products_attributes`.`m_products_attributes_product_id`=' . $data['products_id'] . ';';

  $q = 'SELECT `m_products_attributes`.*, `m_products_attributes_list`.* FROM `formetoo_main`.`m_products_attributes_list` 
    LEFT JOIN `formetoo_main`.`m_products_attributes` ON `m_products_attributes`.`m_products_attributes_product_id`=' . $data['products_id'] .'  AND `m_products_attributes_list`.`m_products_attributes_list_id` = `m_products_attributes`.`m_products_attributes_list_id` 
    WHERE `m_products_attributes_list`.
     `m_products_attributes_list_id` IN(' . implode(',', $group) . ');';
//, GROUP_CONCAT  (`m_products_attributes`.`m_products_attributes_list_id` SEPARATOR \'|\') AS attributes_list_ids 

  $attr = ($res = $sql->query($q)) ? $res : array();

  $tempAttrSelected = array();
  foreach ($attr as $keyAttr => $_attr) {
    if ($_attr['m_products_attributes_list_type'] == 'L') {
      $_attr['attributesEnum'] = $productAttributes->getAttributesListById($_attr['m_products_attributes_list_id']);
    }

    $index = array_search($_attr['m_products_attributes_list_id'], $group);
    if (false !== $index) {
      if (empty($tempAttrSelected[$index])) $tempAttrSelected[$index] = $_attr;
      $tempAttrSelected[$index]['valuesEnum'][] = $_attr['m_products_attributes_value'];
    }
  }
                        
  ksort($tempAttrSelected);

  foreach ($tempAttrSelected as $keyAttr => $_attr) { 
  ?>
    <div class="multirow">
      <div class="row">
        <section class="col col-xs-5 attr_name">
          <label class="label">
            <?
            echo $_attr['m_products_attributes_list_name'] . ($_attr['m_products_attributes_list_unit'] ? ', ' . $_attr['m_products_attributes_list_unit'] : '') . ($_attr['m_products_attributes_list_comment'] ? ' (' . $_attr['m_products_attributes_list_comment'] . ')' : '');
            echo '<input type="hidden" name="m_products_attributes_list_id['.$keyAttr.'][]" value="'.$_attr['m_products_attributes_list_id'].'">';
            ?>
          </label>
        </section>
        <section class="col col-xs-7 attr_value">
          <label class="label"></label>
          <?
          switch ($_attr['m_products_attributes_list_type']) {
            case 'L':
              echo '<select '. ($_attr['is_multiply'] ? 'multiple' : '') .' name="m_products_attributes_value['.$keyAttr.'][]" class="autoselect" placeholder="выберите из списка..."> 
								<option value="0">выберите из списка...</option>';
								foreach ($_attr['attributesEnum'] as $attributEnum) {
									echo '<option ' . (array_search($attributEnum['id'], $_attr['valuesEnum']) !== false ? 'selected' : '') .' data="' . $attributEnum['id'] . '" value="' . $attributEnum['id'] . '" >'.$attributEnum['value'].'</option>';
								}
							echo '</select>';
              break;

            case 'F': 
              break;

            case 'H': 
              foreach ($_attr['valuesEnum'] as $valuesEnum) {
                echo '<label class="textarea textarea-resizable">	<textarea name="m_products_attributes_value['.$keyAttr.'][]" rows="6" class="custom-editor custom-scroll">'.$valuesEnum.'</textarea></label>'; 
              }
              if ($_attr['is_multiply']) {
                echo '<button class="btn btn-primary js-add-textarea onclick="return false;">+</button>';
              }
              break;

            case 'N': 
              foreach ($_attr['valuesEnum'] as $valuesEnum) {
                echo '<label class="input">';
                  echo '<input type="number" name="m_products_attributes_value['.$keyAttr.'][]" data-type="'. $_attr['m_products_attributes_list_id'] .'" data-list-type="'. $_attr['m_products_attributes_list_type'] .'" suggest="'. $_attr['m_products_attributes_list_id'] .'" placeholder="значение" value="'. $valuesEnum .'">';
                echo '</label>'; 
              }
              if ($_attr['is_multiply']) {
                echo '<button class="btn btn-primary js-add-row onclick="return false;">+</button>';
              }
              break;

            case 'I': echo '<label class="input"><input type="number" name="m_products_attributes_value['.$keyAttr.'][]" data-type="'. $_attr['m_products_attributes_list_id'] .'" data-list-type="'. $_attr['m_products_attributes_list_type'] .'" suggest="'. $_attr['m_products_attributes_list_id'] .'" placeholder="число" value="'. $_attr['m_products_attributes_value'] .'"> <span> - </span> <input type="number" name="m_products_attributes_value['.$keyAttr.'][]" data-type="'. $_attr['m_products_attributes_list_id'] .'" data-list-type="'. $_attr['m_products_attributes_list_type'] .'" suggest="'. $_attr['m_products_attributes_list_id'] .'" placeholder="число" value="'. $_attr['m_products_attributes_value'] .'"></label>';
              break;

            default:
              foreach ($_attr['valuesEnum'] as $valuesEnum) {
                echo '<label class="input">';
                  echo '<input type="text" name="m_products_attributes_value['.$keyAttr.'][]" data-type="'. $_attr['m_products_attributes_list_id'] .'" data-list-type="'. $_attr['m_products_attributes_list_type'] .'" suggest="'. $_attr['m_products_attributes_list_id'] .'" placeholder="значение" value="'. $valuesEnum .'">';
                echo '</label>'; 
              }
              if ($_attr['is_multiply']) {
                echo '<button class="btn btn-primary js-add-row onclick="return false;">+</button>';
              }
              break;
          }
          ?>
        </section>
      </div>
    </div>
  <?
  }
} else {
  $q = 'SELECT * FROM `formetoo_main`.`m_products_attributes_list` WHERE `m_products_attributes_list_id` IN(' . implode(',', $group) . ');';
  $attr = $sql->query($q);

  foreach ($attr as $keyAttr => $valueAttr) {
  ?>
    <div class="multirow">
    <div class="row">
      <section class="col col-6 attr_name">
        <label class="label">
          <?
          echo $valueAttr['m_products_attributes_list_name'] . ($valueAttr['m_products_attributes_list_unit'] ? ', ' . $valueAttr['m_products_attributes_list_unit'] : '') . ($valueAttr['m_products_attributes_list_comment'] ? ' (' . $valueAttr['m_products_attributes_list_comment'] . ')' : '');
          echo '<input type="hidden" name="m_products_attributes_list_id['.$keyAttr.'][]" value="'.$valueAttr['m_products_attributes_list_id'].'">';
          ?>
        </label>
      </section>
      <section class="col col-6 attr_value">
        <label class="label"></label>
        <label class="input">
          <i></i>
          <input type="text" name="m_products_attributes_value[]" suggest="" placeholder="значение (два пробела для подсказки)">
        </label>
      </section>
    </div>
  </div>
  <?
  }
}

unset($sql);
?>

<script src="/js/plugin/tinymce/tinymce.min.js"></script>
<script>
  $(document).ready(function(){
    const TINYMCE_SETTINGS = {
      selector: ".custom-editor",
      theme: "modern",
      plugins: [
        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor colorpicker textpattern"
      ],
      toolbar1: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | hr removeformat nonbreaking charmap image | link unlink anchor | forecolor backcolor | fullpage print preview code | fontselect fontsizeselect",
      image_advtab: true,
      language: "ru",
      height: "250",
      relative_urls : true,
      /* remove_script_host : false,
      convert_urls : false, */
      verify_html : false,
      fontsize_formats: "6pt 7pt 8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 17pt 18pt 19pt 20pt 21pt 22pt 23pt 24pt 25pt 26pt 27pt 28pt 29pt 30pt 31pt 32pt 33pt 34pt 35pt 36pt",
    }

    function setSelect2() {
      let $input = $("select.autoselect");
      $input.select2();
    }

    setSelect2();
    tinymce.init(TINYMCE_SETTINGS);

    $('.js-add-row').click(function() {
      let prev = $(this).prev('label.input').clone();
      $(prev).find('input').val('');
      $(this).before($(prev));
      setSelect2();
      return false;
    });

    $('.js-add-textarea').click(function() {
      tinymce.remove();
      let prev = $(this).prev('.textarea').clone();
      let textarea = $(prev).find('textarea');
      $(textarea).val('');
      $(textarea).removeAttr('id');
      $(this).before($(prev));
      tinymce.init(TINYMCE_SETTINGS);
      return false;
    });
  })
</script>