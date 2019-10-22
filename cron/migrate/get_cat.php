<?
/*
*	Включение в текстовый файл всех товарных категорий сайта isolux
*/

define ('_DSITE',1);
ini_set('display_errors',1);
ini_set('memory_limit', '1024M');
require_once(__DIR__.'/simple_html_dom.php');
require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
	
$main_links=array(
	'https://www.isolux.ru/stroymateriali.html',
	'https://www.isolux.ru/otdelochnie-materiali.html',
	'https://www.isolux.ru/santehnika.html',
	'https://www.isolux.ru/elektrotehnicheskoe-oborudovanie-2.html',
	'https://www.isolux.ru/geomateriali.html',
	'https://www.isolux.ru/instrument-i-oborudovaniye.html',
	'https://www.isolux.ru/tovary-dlya-dachi-i-sada.html'
);
$output=array();
foreach($main_links as $_main_link){
	$ch = curl_init($_main_link);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36');
	$html = curl_exec($ch);
	$html=str_replace(array("\r\n","\r","\n","\t"),'',$html);
	$html=preg_replace('/[\s]{2,}/',' ',$html);
	
	$page = new simple_html_dom();
	$page->load($html);
	
	if($a=$page->find('div.sidebar-nav__link a'))
		foreach($a as $_a)
			$output[] = $_a->href;
}
$output=array_unique($output);
$output=implode("\r\n",$output);
file_put_contents('categories.txt',$output);


?>