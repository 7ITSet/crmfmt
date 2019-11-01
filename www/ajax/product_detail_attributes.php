<?
define ('_DSITE',1);

require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
require_once('../../functions/classes/info.php');
$sql=new sql();
$info=new info();
global $e;

$type = array(1 => 'Текстовый', 2 => 'Числовой', 3 => 'Логический');

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
  $q = 'SELECT * FROM `formetoo_main`.`m_products_attributes`
    LEFT JOIN `formetoo_main`.`m_products_attributes_list` ON
      `m_products_attributes`.`m_products_attributes_list_id`=`m_products_attributes_list`.`m_products_attributes_list_id`
    WHERE
      `m_products_attributes_product_id`=' . $data['products_id'] . ';';

  $attr = ($res = $sql->query($q)) ? $res : array();

  $tempAttrSelected = array();
  foreach ($attr as $keyAttr => $_attr) {
    $index = array_search($_attr['m_products_attributes_list_id'], $group);
    if (false !== $index) {
      $tempAttrSelected[$index] =  $_attr;
    }
  }
                        
  ksort($tempAttrSelected);

  foreach ($tempAttrSelected as $keyAttr => $_attr) { 
  ?>
    <div class="multirow">
      <div class="row">
        <section class="col col-6 attr_name">
          <label class="label">
            <?
            echo $_attr['m_products_attributes_list_name'] . ' [' . $type[$_attr['m_products_attributes_list_type']] . ($_attr['m_products_attributes_list_unit'] ? ', ' . $_attr['m_products_attributes_list_unit'] : '') . ($_attr['m_products_attributes_list_comment'] ? ', (' . $_attr['m_products_attributes_list_comment'] . ')' : '') . ']';
            echo '<input type="hidden" name="m_products_attributes_list_id['.$keyAttr.'][]" value="'.$_attr['m_products_attributes_list_id'].'">';
            ?>
          </label>
        </section>
        <section class="col col-6 attr_value">
          <label class="label"></label>
          <label class="input">
            <i></i>
            <input type="text" name="m_products_attributes_value[<?=$keyAttr?>][]" data-type="<?= $_attr['m_products_attributes_list_id']; ?>" data-list-type="<?= $_attr['m_products_attributes_list_type']; ?>" suggest="<?= $_attr['m_products_attributes_list_id'] ?>" placeholder="значение (два пробела для подсказки)" value="<?= $_attr['m_products_attributes_value'] ?>">
          </label>
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
          echo $valueAttr['m_products_attributes_list_name'] . ' [' . $type[$valueAttr['m_products_attributes_list_type']] . ($valueAttr['m_products_attributes_list_unit'] ? ', ' . $valueAttr['m_products_attributes_list_unit'] : '') . ($valueAttr['m_products_attributes_list_comment'] ? ', (' . $valueAttr['m_products_attributes_list_comment'] . ')' : '') . ']';
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