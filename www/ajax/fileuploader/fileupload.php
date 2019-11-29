<?
define ('_DSITE',1);

require_once('../../../functions/system.php');
require_once('../../../functions/ccdb.php');
$sql=new sql();
require_once('../../../functions/classes/user.php');
$user=new user;
require_once('../../../functions/classes/file.php');
$fileUpload=new FileUpload();

global $main_dir;

$maxFileSize=30*1024*1024;
$maxFileCount=500;

//если получен файл
if (isset($_FILES['file'])){	

	$custom_error=array();
  //проверяем размер и тип файла 
  $ext=strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));
  if ($_FILES['file']['size']>$maxFileSize){
		$custom_error['jquery-upload-file-error']='FILE_SIZE_ERROR';
		echo json_encode($custom_error);
		exit;
	}
	//обработка файла
	if ($_FILES['file']['error']==0&&is_uploaded_file($_FILES['file']['tmp_name'])){
		echo $fileUpload::loadFile($_FILES['file'],$user->getInfo(),true);
	}
	else{
		$custom_error['jquery-upload-file-error']='FILES_COUNT_LIMIT';
		echo json_encode($custom_error);
	}
}
 ?>