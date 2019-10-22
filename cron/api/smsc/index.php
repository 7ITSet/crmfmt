<?
define ('_DSITE',1);
ini_set('display_errors',1);
require_once(__DIR__.'/../../../functions/system.php');
require_once(__DIR__.'/../../../functions/ccdb.php');
$sql=new sql;

$data['md5']=array(1,null,null,32);
$data['id']=array(1,1,null,null,1);
$data['phone']=array(1,null,null,11,1);
$data['status']=array(1,1,10,null,1);
$data['err']=array(1,1,10,null,1);
$data['time']=array(1,1,20);
$data['cnt']=array(1,1,2,null,1);
$data['flag']=array(1,1,20);
$data['sender']=array(1,1,50);

array_walk($data,'check');

if(!$e){
    file_put_contents('1.txt',json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'|',FILE_APPEND);
    
    if($data['md5']==md5($data['id'].':'.$data['phone'].':'.$data['status'].':03fjrE49e3ci')){
        $q='UPDATE `formetoo_main`.`m_users_messages` SET 
            '.($data['status']==1&&$data['err']==0?'`m_users_messages_status`=2,':'').'
            `m_users_messages_response`=\''.json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'\' 
            WHERE `m_users_messages_id`='.$data['id'].' LIMIT 1;';
        $sql->query($q);
    }
}
else elogs();
?>