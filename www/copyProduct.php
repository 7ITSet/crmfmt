<?php
define ('_DSITE',1);
require_once('../functions/main.php');

if (!empty($_GET['id'])) {
    $id = htmlspecialchars($_GET['id']);
    $result = products::products_copy($id);
    if($result){
        header('Location: https://crm.formetoo.ru/companies/products/items/');
    } else {
        echo 'ошибка';
    }
}