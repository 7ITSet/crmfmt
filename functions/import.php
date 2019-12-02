<?php
// Определяем константу для включения режима отладки (режим отладки выключен)
define("EXCEL_MYSQL_DEBUG", false);
// Подключаем библиотеку импорта/экспорта
require_once __DIR__ . "/classes/import/PHPExcel.php";
// Подключаем модуль
require_once __DIR__ . "/classes/excel_mysql.php";
require_once('../functions/ccdb.php');

function file_force_download($file)
{
  if (file_exists($file)) {
    // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
    // если этого не сделать файл будет читаться в память полностью!
    if (ob_get_level()) {
      ob_end_clean();
    }
    // заставляем браузер показать окно сохранения файла
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    // читаем файл и отправляем его пользователю
    readfile($file);
    exit;
  }
}

function exceldate($exceldate)
{

  $tdate = explode('#', str_replace('.', '#', str_replace(':', '#', str_replace(' ', '#', $exceldate))));

  if (($tdate[0] / 100) > 1) {
    $tdate[3] = ($exceldate - $tdate[0]) * 24;
    $tdate[4] = ($tdate[3] - floor($tdate[3])) * 60;
    $tdate[3] = floor($tdate[3]);
    $tdate[5] = ($tdate[4] - floor($tdate[4])) * 60;
    $tdate[4] = floor($tdate[4]);
    $tdate[5] = round($tdate[5]);

    $tdate = date('Y-m-d H:i:s', mktime($tdate[3], $tdate[4], $tdate[5], 1, $tdate[0] - 1, 1900));
  } else
    $tdate = date('Y-m-d H:i:s', mktime($tdate[3], $tdate[4], $tdate[5], $tdate[1], $tdate[0], $tdate[2]));

  return $tdate;
}

function uploadExcelFile($fileName)
{
  $sql = new sql();
  $connection = new mysqli($sql->db[0]['serv'],$sql->db[0]['user'],$sql->db[0]['pass'], $sql->db[0]['name']);

  if (mysqli_connect_errno()) {
    printf("Подключение к серверу MySQL невозможно. Код ошибки: %s\n", mysqli_connect_error());
    exit;
  }

  // Выбираем кодировку UTF-8
  $connection->set_charset("utf8");

  // Создаем экземпляр класса excel_mysql
  $excel_mysql_import_export = new Excel_mysql($connection,  $_SERVER['DOCUMENT_ROOT'] . "/" . $fileName);

  echo $excel_mysql_import_export->excel_to_mysql_by_index(
    "m_products",
    0,
    array(
      'm_products_id',
      'm_products_id_isolux',
      'm_products_main_product',
      "m_products_contragents_id",

      "m_products_name",
      "m_products_name_full",
      "m_products_unit",
      "m_products_price_general",
      "m_products_price_currency",

      "m_products_price_bonus",
      "m_products_price_discount",
      "m_products_multiplicity",
      "m_products_min_order",
      'm_products_check_it',

      "m_products_show_site",
      "m_products_show_price",
      "m_products_links",
      "m_products_update",
      "m_products_date",

      "m_products_order",
      "m_products_exist",
      'm_products_dir',
      "m_products_foto",
      'm_products_foto_category',

      'm_products_rate',
      'm_products_feedbacks',
      'm_products_weight',

      'm_products_seo_title',
      'm_products_seo_keywords',
      'm_products_seo_description',
    ),
    2,
    false,
    array(
      "m_products_update" =>
      function ($exceldate) {
        return exceldate($exceldate);
      },
      "m_products_date" =>
      function ($exceldate) {
        return exceldate($exceldate);
      }
    ),
    1,
    array(
      "bigint(20) UNSIGNED NOT NULL", //m_products_id
      "char(10) NOT NULL DEFAULT '0'", //m_products_id_isolux
      "bigint(20) UNSIGNED NOT NULL DEFAULT '0'",  //m_products_main_product   
      "bigint(20) NOT NULL DEFAULT '1000000000'", //m_products_contragents_id

      "varchar(200) NOT NULL COMMENT 'Наименование'", //m_products_name
      "varchar(300) NOT NULL", //m_products_name_full
      "int(11) NOT NULL COMMENT 'Еденица измерения'", //m_products_unit
      "decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Цена'", //m_products_price_general
      "tinyint(3) UNSIGNED NOT NULL DEFAULT '1'", //m_products_price_currency

      "decimal(5,2) NOT NULL DEFAULT '0.00'", //m_products_price_bonus
      "decimal(2,2) UNSIGNED NOT NULL DEFAULT '0.00'", //m_products_price_discount
      "decimal(15,4) NOT NULL DEFAULT '1.0000'", //m_products_multiplicity
      "decimal(15,4) NOT NULL DEFAULT '1.0000'", //m_products_min_order
      "tinyint(4) NOT NULL DEFAULT '0'", //m_products_check_it

      "tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Показывать на сайте'", //m_products_show_site
      "tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Показывать в прайсе'", //m_products_show_price
      "varchar(500) NOT NULL COMMENT 'Дополнительные (связанные продукты)'", //m_products_links
      "datetime NOT NULL", //m_products_update
      "datetime NOT NULL", //m_products_date

      "int(11) NOT NULL DEFAULT '0'", //m_products_order
      "tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Наличие, 1- в наличии, 0 - под заказ, -1 - нет в продаже'", //m_products_exist
      "tinyint(3) UNSIGNED NOT NULL DEFAULT '0'", //m_products_dir
      "text NOT NULL", //m_products_foto
      "tinyint(4) NOT NULL DEFAULT '0'", //m_products_foto_category

      "float UNSIGNED NOT NULL DEFAULT '0'", //m_products_rate
      "int(10) UNSIGNED NOT NULL DEFAULT '0'", //m_products_feedbacks
      "float UNSIGNED NOT NULL DEFAULT '0'", //m_products_weight

      "varchar(255) NOT NULL", //m_products_seo_title
      "text NOT NULL", //m_products_seo_keywords
      "text NOT NULL", //m_products_seo_description
    ),
    array(),
    "utf8_unicode_ci",
    "MyISAM"
  ) ? "OK\n" : "FAIL\n";

  /* Закрываем соединение */
  $connection->close();
}
function excel_to_mysql_by_index($table_name, $index = 0, $columns_names = 0, $start_row_index = false, $condition_functions = false, $transform_functions = false, $unique_column_for_update = false, $table_types = false, $table_keys = false, $table_encoding = "utf8_general_ci", $table_engine = "InnoDB") {
}
function uploadExcelSEOFile($fileName)
{
  $sql = new sql();
  $connection = new mysqli($sql->db[0]['serv'],$sql->db[0]['user'],$sql->db[0]['pass'], $sql->db[0]['name']);

  if (mysqli_connect_errno()) {
    printf("Подключение к серверу MySQL невозможно. Код ошибки: %s\n", mysqli_connect_error());
    exit;
  }

  // Выбираем кодировку UTF-8
  $connection->set_charset("utf8");

  // Создаем экземпляр класса excel_mysql
  $excel_mysql_import_export = new Excel_mysql($connection,  $_SERVER['DOCUMENT_ROOT'] . "/" . $fileName);

  echo $excel_mysql_import_export->excel_to_mysql_by_index(
    "m_products",
    0,
    array(
      'm_products_id',
      "m_products_name",
      "m_products_name_full",

      'slug',
      'm_products_seo_title',
      'm_products_seo_keywords',
      'm_products_seo_description',

      "m_products_update"
    ),
    2,
    false,
    array(
      "m_products_update" =>
      function () {
        return date_create('now')->format('Y-m-d H:i:s');
      }
    ),
    1,
    array(
      "bigint(20) UNSIGNED NOT NULL", //m_products_id
      "varchar(200) NOT NULL COMMENT 'Наименование'", //m_products_name
      "varchar(300) NOT NULL", //m_products_name_full

      "varchar(255) NOT NULL", //slug
      "varchar(255) NOT NULL", //m_products_seo_title
      "text NOT NULL", //m_products_seo_keywords
      "text NOT NULL", //m_products_seo_description

      "datetime NOT NULL", //m_products_update
    ),
    array(),
    "utf8_unicode_ci",
    "MyISAM"
  ) ? "" : "FAIL";
  
  /* Закрываем соединение */
  $connection->close();
}

function downloadExcelFile($fileName)
{
  $sql = new sql();
  $connection = new mysqli($sql->db[0]['serv'],$sql->db[0]['user'],$sql->db[0]['pass'], $sql->db[0]['name']);
  //$connection = new mysqli("localhost", "formetoo_main", "1u8hb8-7H498ffoI2", "formetoo_main");

  if (mysqli_connect_errno()) {
    printf("Подключение к серверу MySQL невозможно. Код ошибки: %s\n", mysqli_connect_error());
    exit;
  }

  // Выбираем кодировку UTF-8
  $connection->set_charset("utf8");

  // Создаем экземпляр класса excel_mysql
  $excel_mysql_import_export = new Excel_mysql($connection,  $_SERVER['DOCUMENT_ROOT'] . "/" . $fileName);

  $exportFileName = 'export.xlsx';

  $excel_mysql_import_export->setFileName($exportFileName);

  // Экспортируем таблицу MySQL в Excel с указанием какие столбцы выгружать и заголовками столбцов
  echo $excel_mysql_import_export->mysql_to_excel(
    "m_products",
    "Экспорт",
    array(
      'm_products_id',
      'm_products_id_isolux',
      'm_products_main_product',
      "m_products_contragents_id",

      "m_products_name",
      "m_products_name_full",
      "m_products_unit",
      "m_products_price_general",
      "m_products_price_currency",

      "m_products_price_bonus",
      "m_products_price_discount",
      "m_products_multiplicity",
      "m_products_min_order",
      'm_products_check_it',

      "m_products_show_site",
      "m_products_show_price",
      "m_products_links",
      "m_products_update",
      "m_products_date",

      "m_products_order",
      "m_products_exist",
      'm_products_dir',
      "m_products_foto",
      'm_products_foto_category',

      'm_products_rate',
      'm_products_feedbacks',
      'm_products_weight',

      'm_products_seo_title',
      'm_products_seo_keywords',
      'm_products_seo_description',
    ),
    array(
      "Артикул",
      "m_products_id_isolux",
      "m_products_main_product",

      "Организация (id)",
      "Категория (id)",

      "Наименование",
      "Полное наименование",
      "Единица измерения",
      "Цена",
      "Валюта",

      "Бонус",
      "Скидка",
      "Кратность",
      "Минимальный заказ(шт.)",
      'm_products_check_it',

      "Показывать на сайте",
      "Выгружать в прайс",
      "Связанные продукты",
      "m_products_update",
      "m_products_date",

      "Порядок",
      "Наличие(1- в наличии, 0 - под заказ, -1 - нет в продаже)",
      'm_products_dir',
      "Фото",
      'm_products_foto_category',

      'm_products_rate',
      'm_products_feedbacks',
      'm_products_weight',

      'SEO title',
      'SEO keywords',
      'SEO description',

    )
  ) ? "" : "";

  if (file_exists($exportFileName)) {

    $downloadsPath = 'downloads/' . $exportFileName;

    copy($exportFileName, $downloadsPath);
    unlink($exportFileName);

    //скачиваем файл
    file_force_download($downloadsPath);
  } else {
    echo "Файл $exportFileName не существует";
  }

  /* Закрываем соединение */
  $connection->close();
}


function downloadExcelSEOFile($fileName)
{
  $sql = new sql();
  $connection = new mysqli($sql->db[0]['serv'],$sql->db[0]['user'],$sql->db[0]['pass'], $sql->db[0]['name']);
  //$connection = new mysqli("localhost", "formetoo_main", "1u8hb8-7H498ffoI2", "formetoo_main");

  if (mysqli_connect_errno()) {
    printf("Подключение к серверу MySQL невозможно. Код ошибки: %s\n", mysqli_connect_error());
    exit;
  }

  // Выбираем кодировку UTF-8
  $connection->set_charset("utf8");

  // Создаем экземпляр класса excel_mysql
  $excel_mysql_import_export = new Excel_mysql($connection,  $_SERVER['DOCUMENT_ROOT'] . "/" . $fileName);

  $exportFileName = 'export.xlsx';

  $excel_mysql_import_export->setFileName($exportFileName);

  // Экспортируем таблицу MySQL в Excel с указанием какие столбцы выгружать и заголовками столбцов
  echo $excel_mysql_import_export->mysql_to_excel(
    "m_products",
    "Экспорт SEO",
    array(
      'm_products_id',
      "m_products_name",
      "m_products_name_full",

      'slug',
      'm_products_seo_title',
      'm_products_seo_keywords',
      'm_products_seo_description'
    ),
    array(
      "id",
      "Наименование",
      "Полное наименование",

      'алиас',
      'SEO title',
      'SEO keywords',
      'SEO description',

    )
  ) ? "" : "";

  if (file_exists($exportFileName)) {

    $downloadsPath = 'downloads/' . $exportFileName;

    copy($exportFileName, $downloadsPath);
    unlink($exportFileName);

    //скачиваем файл
    file_force_download($downloadsPath);
  } else {
    echo "Файл $exportFileName не существует";
  }

  /* Закрываем соединение */
  $connection->close();
}
