<?
define ('_DSITE',1);

global $e;

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/classes/import/NCL/NCLNameCaseRu.php');
$sql=new sql;

$data['name']=array(1,null,150);
array_walk($data,'check',true);

if(!$e){
	$case = new NCLNameCaseRu();
	if($result = $case->q($data['name'],NCL::$RODITLN))
		print_r($result);
		//echo implode(' ',$result);
	else
		echo 'ERROR';
}
else 
	echo 'ERROR';

unset($sql);
?>