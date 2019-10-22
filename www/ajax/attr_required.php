<?php
define ('_DSITE',1);
require_once('../../functions/system.php');
require_once('../../functions/ccdb.php');
$sql = new sql();
$error = false;


if(!empty($_POST['group_id'])){

    $group_id = htmlspecialchars($_POST['group_id']);

    if(!empty($_POST['product_id'])){
        $product_id = htmlspecialchars($_POST['product_id']);
    } else {
        $product_id = 0;
    }

    $q = "SELECT `m_products_attributes_groups_list_id` FROM `formetoo_main`.`m_products_attributes_groups` WHERE `m_products_attributes_groups_id` = '$group_id';";

    $attrs_id = $sql->query($q);

    if (!empty($attrs_id)){

        $array = explode('|', $attrs_id[0]['m_products_attributes_groups_list_id']);

        $q = "SELECT `m_products_attributes_list_id`, `m_products_attributes_list_name` FROM `formetoo_main`.`m_products_attributes_list` WHERE `m_products_attributes_list_id` IN ('".implode("','",$array)."');";
//        $q = "SELECT * FROM `formetoo_main`.`m_products_attributes_list` WHERE `m_products_attributes_list_id` IN ('".implode("','",$array)."');";

        $attrs_name = $sql->query($q);

//        echo json_encode($attrs_name, JSON_UNESCAPED_UNICODE);
//        die;

        $response = [];
        if ($product_id){

            $q = "SELECT `m_products_attributes_list_id`,`m_products_attributes_value` FROM `formetoo_main`.`m_products_attributes` WHERE `m_products_attributes_product_id` = '$product_id';";

            $attrs_value = $sql->query($q);

            if(!empty($attrs_value)){

                foreach ($attrs_value as $value){
                    if(in_array($value['m_products_attributes_list_id'], $array)){

                        foreach ($attrs_name as $name){
                            if($name['m_products_attributes_list_id'] == $value['m_products_attributes_list_id']){
                                $m_products_attributes_list_name = $name['m_products_attributes_list_name'];
                            }
                        }

                        $response[] = [
                            'm_products_attributes_list_id' => $value['m_products_attributes_list_id'],
                            'm_products_attributes_list_name' => $m_products_attributes_list_name,
                            'm_products_attributes_value' => $value['m_products_attributes_value']
                            ];
                    }
                }

                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {

                foreach ($attrs_name as $name){
                    $response[] = [
                        'm_products_attributes_list_id' => $name['m_products_attributes_list_id'],
                        'm_products_attributes_list_name' => $name['m_products_attributes_list_name'],
                        'm_products_attributes_value' => ''
                    ];
                }

                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            }

//            echo json_encode($attrs_value, JSON_UNESCAPED_UNICODE);
//            die;

//            $error = true;
        } else {

            echo json_encode($attrs_name, JSON_UNESCAPED_UNICODE);
        }






//    } else {
//        $error = true;
    }
//} else {
//    $error = true;
}
//
//unset($sql);
//
//if(!$error){
//    echo json_encode($result, JSON_UNESCAPED_UNICODE);
//} else {
//    echo 0;
//}
//
//









































//    if(!empty($_POST['data'])){
//
//        $id = htmlspecialchars($_POST['data']);
//
//        $q = "SELECT `m_products_attributes_groups_list_id` FROM `formetoo_main`.`m_products_attributes_groups` WHERE `m_products_attributes_groups_id` = '$id';";
//
//        $attrs_id = $sql->query($q);
//
//
//        if (!empty($attrs_id)){
//
//            $array = explode('|', $attrs_id[0]['m_products_attributes_groups_list_id']);
//
//            $q = "SELECT `m_products_attributes_list_id`, `m_products_attributes_list_name` FROM `formetoo_main`.`m_products_attributes_list` WHERE `m_products_attributes_list_id` IN ('".implode("','",$array)."');";
//
//            $result = $sql->query($q);
//
//
//
//
//
//
//            if (empty($result)){
//                $error = true;
//            }
//        } else {
//            $error = true;
//        }
//    } else {
//        $error = true;
//    }
//
//unset($sql);
//
//    if(!$error){
//        echo json_encode($result, JSON_UNESCAPED_UNICODE);
//    } else {
//        echo 0;
//    }