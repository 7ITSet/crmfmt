<?
defined ('_DSITE') or die ('Access denied');

//системные функции
require_once('system.php');
//подключаемся к основной БД
require_once('ccdb.php');
$sql=new sql;

//инфотаблицы
require_once('classes/info.php');
$info=new info;
//идентификация пользователя
require_once('classes/user.php');
$user=new user;
//сотрудники
require_once('classes/workers.php');
$workers=new workers;
//компании
require_once('classes/contragents.php');
$contragents=new contragents;
//документы
require_once('classes/documents.php');
$documents=new documents;
//бухгалтерия
require_once('classes/buh.php');
$buh=new buh;
//услуги
require_once('classes/services.php');
$services=new services;
//продукция
require_once('classes/products.php');
$products=new products;
//заказы
require_once('classes/orders.php');
$orders=new orders;
//сайт
require_once('classes/site.php');
$site=new site;
//страница настроек
require_once('classes/settings.php');
$settings=new settings;

//навигация по страницам
require_once('navigator.php');

//сохранение изменений
require_once('actions.php');

require_once('menu.php');
$menu=new menu;

//файл обработчик импорта/экспорта excel-документа
require_once ('import.php');

//функции для генерирования тела страницы
require_once('content.php');
$content=new content;
?>