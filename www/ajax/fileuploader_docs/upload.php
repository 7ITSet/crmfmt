<?
define ('_DSITE',1);

require_once('../../../functions/system.php');
require_once('../../../functions/ccdb.php');
$sql=new sql();
require_once('../../../functions/classes/user.php');
$user=new user;

//����� ��� �������� ������ 
$uploadDir=$_SERVER['DOCUMENT_ROOT'].'/temp/uploads/';
$allowedExt=array('jpg','jpeg','png','pdf','xls','xlsx','doc','docx','odt');
$maxFileSize=30*1024*1024;
$maxFileCount=500;

//���� ������� ����
if (isset($_FILES['file'])){
	$custom_error=array();
    //��������� ������ � ��� �����
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
	//��������� �����
    if ($_FILES['file']['error']==0&&is_uploaded_file($_FILES['file']['tmp_name'])){
		//������� ��� ������� ��� ������ � �������
		clearstatcache();
		//������� ��������� ����� ������������
		$uploadDir.=$user->getInfo();
		if(!file_exists($uploadDir))
			mkdir($uploadDir,0777);
		//���������� ��� ��� �����
		$un=get_id('',0,'',true);
		//������������ ����
		$filemax=$uploadDir.'/'.$un.'.'.$ext;
		//������� ���������� ������
		$tdir=scandir($uploadDir);
		$j=0; 
		foreach ($tdir as $file)
			if(!is_dir($file))
				$j++;
		if ($j<$maxFileCount){
			move_uploaded_file($_FILES['file']['tmp_name'], $filemax);
		
			$file_json['file']['name']=$_FILES['file']['name'];
			$file_json['file']['id']=$un;
			$file_json['file']['path']='/temp/uploads/'.$user->getInfo().'/'.$un.'.'.$ext;
			echo json_encode($file_json);
		}
		else{
			$custom_error['jquery-upload-file-error']='FILES_COUNT_LIMIT';
			echo json_encode($custom_error);
		}
	}
}
 ?>