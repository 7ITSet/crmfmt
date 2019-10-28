<?
define('_DSITE', 1);
//ini_set('display_errors',1);
//ini_set('memory_limit','1024M');
require_once('../functions/main.php');
//ob_start();

$dir    = 'uploads';
$file = scandir($dir);
$fileName = $file[2];

downloadExcelSEOFile($fileName);
