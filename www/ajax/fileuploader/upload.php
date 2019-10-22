<?
define ('_DSITE',1);

require_once('../../../functions/system.php');
require_once('../../../functions/ccdb.php');
$sql=new sql();
require_once('../../../functions/classes/user.php');
$user=new user;
require_once('../../../functions/classes/foto.php');
$foto=new foto;

global $main_dir;

//папка для хранения файлов
$allowedExt=array('jpg','jpeg');
$maxFileSize=30*1024*1024;
$maxFileCount=500;

//если получен файл
if (isset($_FILES['file'])){	

	$custom_error=array();
    //проверяем размер и тип файла 
    $ext=strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)){
        $custom_error['jquery-upload-file-error']='EXTENSION_ERROR';
		echo json_encode($custom_error);
		exit;
	}
    if ($_FILES['file']['size']>$maxFileSize){
		$custom_error['jquery-upload-file-error']='FILE_SIZE_ERROR';
		echo json_encode($custom_error);
		exit;
	}
	list($w,$h)=getimagesize($_FILES['file']['tmp_name']);
	if ($w<150&&$h<100||$w>6000||$h>6000){
        $custom_error['jquery-upload-file-error']='IMAGE_SIZE_ERROR_1200';
		echo json_encode($custom_error);
		exit;
	}
	//обработка файла
	if ($_FILES['file']['error']==0&&is_uploaded_file($_FILES['file']['tmp_name'])){
		echo $foto::loadProductFoto($_FILES['file'],true,$user->getInfo(),true);
	}
	else{
		$custom_error['jquery-upload-file-error']='FILES_COUNT_LIMIT';
		echo json_encode($custom_error);
	}
}
 ?>