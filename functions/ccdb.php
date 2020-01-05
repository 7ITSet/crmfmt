<?
defined ('_DSITE') or die ('Access denied');

class sql{
	var $db;
	var $mysqli;
	var $res;
	private $logging='error';
	//подключение к БД
	function __construct($n=0) {
		$this->db[0]=array('name'=>'formetoo_main','user'=>'formetoo_main','pass'=>'f343y4H45r','serv'=>'195.161.41.199:3306');
		//$this->db[0]=array('name'=>'formetoo_main','user'=>'formetoo_main','pass'=>'1u8hb8-7H498ffoI2','serv'=>'localhost');
		$this->db[1]=array('name'=>'u0023354_address','user'=>'u0023354_7itset','pass'=>'Q7d4K3t0','serv'=>'server94.hosting.reg.ru');
		$this->db[2]=array('name'=>'formetoo_parser','user'=>'formetoo_parser','pass'=>'18ffoI2','serv'=>'localhost');
		$this->mysqli=new mysqli($this->db[$n]['serv'],$this->db[$n]['user'],$this->db[$n]['pass']);

		if (mysqli_connect_error() && ($this->logging=='all' || $this->logging=='error')) {
			$this->log(false);
    	die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
		}

		$this->mysqli->query('set character_set_client=\'utf8\'');  
		$this->mysqli->query('set character_set_results=\'utf8\'');  
		$this->mysqli->query('set collation_connection=\'utf8_general_ci\'');
		
		return $this->mysqli;
	}
	//очистка переменных запроса
	function real_escape($var){
		return $this->mysqli->real_escape_string($var);
	}
	//выполнение запроса
 	function query($query,$k=0){
		if($this->logging=='all'||$this->logging=='error')
			$start = microtime(true);//
		if($this->logging=='all')
			$fp=fopen(__DIR__.'/../q.txt','a');//

		$records=array();
		if ($res=$this->mysqli->query($query)){
			if (substr($query,0,6)=='SELECT'){
				if ($k)
					while($row=$res->fetch_assoc())
						$records[$row[$k]][]=$row;
				else
					while($row=$res->fetch_assoc())
						$records[]=$row;
				$res->close();
				if($this->logging=='all'||$this->logging=='error')
					$time = round(microtime(true) - $start,4);//
				if($this->logging=='all'){
					fwrite($fp,$time."\t".$query."\n");//
					fclose($fp);//
				}
				
				return $records;
			}
			else {
				if($this->logging=='all'||$this->logging=='error')
					$time = round(microtime(true) - $start,4);//
				if($this->logging=='all'){
					fwrite($fp,$time."\t".$query."\n");//
					fclose($fp);//
				}
				
				return true;
			}
		}
		else {
			if($this->logging=='all'||$this->logging=='error')
				$this->log(true,$query);
			return false;
		}
	}
		
	//логи
	function log($type,$query=''){
		$fp=fopen(__DIR__.'/../e.txt','a');
		fwrite($fp,date('d.m.Y H:i:s').' IP: '.user_info::ip()."\r\n");
		//ошибки запросов
		if ($type)
			fwrite($fp,$query."\r\n".mysqli_error($this->mysqli)."\r\n");
		//ошибки подключения
		else
			fwrite($fp,$this->error."\r\n");
		fwrite($fp,"\r\n");
		fclose($fp);
	}
	//закрываем соединение
	function __destruct(){
		mysqli_close($this->mysqli);
	}
}
?>