<?
defined ('_DSITE') or die ('Access denied');
set_time_limit(100000);

require_once(__DIR__.'/import/phpbarcode/php-barcode.php');

class codes{
	
	public function getQR($file,$text,$ecc='L',$size=3){
		$ecc_v=array(
			'L'=>QR_ECLEVEL_L,
			'M'=>QR_ECLEVEL_M,
			'Q'=>QR_ECLEVEL_Q,
			'H'=>QR_ECLEVEL_H,
			'1'=>QR_ECLEVEL_L,
			'2'=>QR_ECLEVEL_M,
			'3'=>QR_ECLEVEL_Q,
			'4'=>QR_ECLEVEL_H
		);
		return QRcode::png($text,$file,$ecc_v[$ecc],$size);
	}
	
	public static function getBAR($file,$text,$digits=true,$height=100,$type='EAN',$size=2){
		$bars=barcode_encode($text,$type);
		barcode_outimage($digits?$bars['text']:null,$bars['bars'],$size,'png',$file);
		img::crop($file,$file,10,array(0,0,100,$height),true,true);
	}
	
}
?>