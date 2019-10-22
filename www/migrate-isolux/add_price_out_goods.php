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
define ('_DSITE',1);
ini_set('display_errors',1);
ini_set('memory_limit', '1024M');
require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/classes/contragents.php');
require_once(__DIR__.'/../../functions/classes/documents.php');
require_once(__DIR__.'/../../functions/classes/foto.php');
require_once(__DIR__.'/simple_html_dom.php');
$sql_islx=new sql(2);

global $main_dir;

$q='SELECT `id`,`price`,`url` FROM `ci_goods`;';
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
	if ($html === false){
		echo('error: ' . curl_error($ch));
		continue;
	}
	curl_close($ch);
	if($html){
		$html=str_replace(array("\r\n","\r","\n","\t"),'',$html);
		$html=preg_replace('/[\s]{2,}/',' ',$html);
		
		$page = new simple_html_dom();
		$page->load($html);

		if($li=$page->find('ul.pagination li',-1))
			$maxPage=$li->plaintext;
		else $maxPage=1;
		
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
					foreach($xp->query('//div[@class="product-card__qty-wrap"]/div[@class="change-qty"]/input[@class="change-qty__value"]') as $date_node)
						if($data_content=json_decode($date_node->getAttribute('data-content'))){
							$ids_['id']=isset($data_content->sku)?$data_content->sku:0;
							$ids_['count']=isset($data_content->inStockCount)?$data_content->inStockCount:0;
							$ids_['price']=isset($data_content->prices->GENERAL->regular)?$data_content->prices->GENERAL->regular:0;
							$ids_['price_bigopt']=isset($data_content->prices->WH->regular)&&((float)$data_content->prices->WH->regular>0)?$data_content->prices->WH->regular:$ids_['price'];
							$ids_['miltiplicity']=isset($data_content->multiplicity)?$data_content->multiplicity:1;
							$ids[]=$ids_;
						}				
				}
			}
			else{
				break;
			}
		}

	}
	$a_t=$urls[$k];
	unset($urls[$k]);
	$output=implode("",$urls);
	file_put_contents('categories.txt',$output);
	break;
}

$add=array();
foreach($ids as $_id)
	$add[]='("'.$_id['id'].'","'.dt().'",'.$_id['count'].','.$_id['price'].','.$_id['price_bigopt'].','.$_id['miltiplicity'].')';
if($add){
	$q='INSERT INTO `ci_goods` 
				(`id`,`date`,`count`,`price`,`price_bigopt`,`miltiplicity`) 
			VALUES '.implode(',',$add).' 
			ON DUPLICATE KEY UPDATE 
				`date`=values(`date`),
				`count`=values(`count`),
				`price`=values(`price`),
				`price_bigopt`=values(`price_bigopt`),
				`miltiplicity`=values(`miltiplicity`);';
	if($sql_islx->query($q))
		echo '<br/>Обновлено '.sizeof($add).' товаров<br/>';
	else echo 'Во время обновления прооизошла ошибка.';
}
echo '
	<p>Ссылка: '.$a_t.'<p>
<a href="http://crm.formetoo.ru/migrate-isolux/add_price_out_goods.php">Продолжить</a>';
?>