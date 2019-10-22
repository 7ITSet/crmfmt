<script type="text/javascript" src="jquery.min.js"></script>
<script>
$(document).ready(function(){
	function sendNotification(title, options) {
		// Проверим, поддерживает ли браузер HTML5 Notifications
		if ("Notification" in window) {
			// Проверим, есть ли права на отправку уведомлений
			if (Notification.permission === "granted") {
				// Если права есть, отправим уведомление
				var notification = new Notification(title, options);
				function clickFunc(){ alert('Пользователь кликнул на уведомление'); }
				notification.onclick = clickFunc;
			}
			// Если прав нет, пытаемся их получить
			else{
				if (Notification.permission !== 'denied') {
					Notification.requestPermission(function (permission) {
						// Если права успешно получены, отправляем уведомление
						if (permission === "granted"){
							var notification = new Notification(title, options);
						}
						else {
							alert('Вы запретили показывать уведомления'); // Юзер отклонил наш запрос на показ уведомлений
						}
					});
				}
			}
		}
	}


	if($('div:contains("Error")').length||$('div:contains("Warning")').length||$('div:contains("Notice")').length){
		sendNotification('Заголовок', {
			body: 'Тестирование HTML5 Notifications',
			icon: 'http://habrastorage.org/storage2/cf9/50b/e87/cf950be87f8c34e63c07217a009f1d17.jpg',
			dir: 'auto'
		});
		
		var sound = new buzz.sound("Transformery_-_Uvedomlenie", {
			formats: ["mp3"]
		});

		sound.play()
			 .fadeIn()
			 .loop()
			 .bind("timeupdate", function() {
				var timer = buzz.toTimer(this.getTime());
				document.getElementById("timer").innerHTML = timer;
			 });
	}
	else{
		setTimeout(function(){
			window.location=$('a').attr('href');
		},1000);
	}
})
</script>
<?
/*
*	Добавлние id тоавров с сайта Изолюкса, которых нет в прайс-листе
*	сканированием категорий из текстового файла
*	дальнейший парсинг товаров происходит стандартной функцией
*/

define ('_DSITE',1);
ini_set('display_errors',1);
ini_set('memory_limit', '1024M');
require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/classes/contragents.php');
require_once(__DIR__.'/../../functions/classes/documents.php');
require_once(__DIR__.'/../../functions/classes/foto.php');
$sql_islx=new sql(2);

global $main_dir;

$q='SELECT `id`,`price`,`url` FROM `p-islx`.`ci_goods`;';
$res_islx=$sql_islx->query($q,'id');

$ids=array();

$urls=file('categories.txt');
for($k=0;$k<sizeof($urls);$k++){
	$_url=str_replace(array("\r\n","\r","\n"),'',trim($urls[$k]));
	//находим последнюю страницу
	$ch = curl_init($_url.'?limit=100&p=1000');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36');
	$html = curl_exec($ch);
	if ($html === false){echo $_url;
		echo('error: ' . curl_error($ch));
		continue;
	}
	curl_close($ch);
	if($html){
		libxml_use_internal_errors(TRUE);
		$node=new DOMDocument('1.0', 'utf-8');
		if($node->loadHTML($html)){
			$xp=new DomXPath($node);
			$ul=$xp->query('//ul[@class="pagination"]/li');
			$maxPage=($ul->item($ul->length-1)->nodeValue?$ul->item($ul->length-1)->nodeValue:1);
			
			for($p=1;$p<=$maxPage;$p++){
				$ch1 = curl_init($_url.'?limit=100&p='.$p);
				curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch1, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36');
				$html1 = curl_exec($ch1);
				if ($html1 === false) {
					die('error: ' . curl_error($ch1));
				}
				curl_close($ch1);
				if($html1){
					libxml_use_internal_errors(TRUE);
					$node=new DOMDocument('1.0', 'utf-8');
					if($node->loadHTML($html1)){
						$xp=new DomXPath($node);
						foreach($xp->query('//div[@class="product-card__sku"]') as $date_node)
							$ids[]=$date_node->nodeValue;
					}
				}
				else{
					break;
				}
			}
		}
	}
	$a_t=$urls[$k];
	unset($urls[$k]);
	$output=implode("",$urls);
	//file_put_contents('categories.txt',$output);
	break;
}
$add=array();
print_r($ids);
foreach($ids as $_id){
	$_id=explode(' ',$_id);
	$_id=array_pop($_id);
	if(!isset($res_islx['СН'.$_id]))
		$add[]='("СН'.$_id.'","'.dt().'")';
}

$q='INSERT INTO `p-islx`.`ci_goods` (`id`,`date`) VALUES '.implode(',',$add).';';
if($sql_islx->query($q))
	print_r($add);
echo '
	<p>Ссылка: '.$a_t.'<p>
<a href="http://127cron.formetoo.ru/migrate/add_price_out_goods.php">Продолжить</a>';
?>
<?
/* $urls=file('categories.txt');
foreach($urls as $_url){
$a=file_get_contents(trim($_url).'?limit=100&p=100');
echo $a;
break;
} */


?>