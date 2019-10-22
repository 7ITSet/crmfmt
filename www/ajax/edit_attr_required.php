<?php

if(!empty($_POST['value'])){
    if($_POST['value'] === 'fmt'){
        echo true;
    } else {
        header('HTTP 400 Bad Request',true,400);
        echo "неверный пароль".$q;
    }
}