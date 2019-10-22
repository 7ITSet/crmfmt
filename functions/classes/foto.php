<?
defined ('_DSITE') or die ('Access denied');

class foto{
	
	static function loadProductFoto(
		$file,
		$wm=true,
		$user='0000000000',
		$upload=false,
		$_main_dir=''
	){
		global $main_dir;
		$uploadDir=($_main_dir?$_main_dir:$main_dir).'/../www/temp/uploads/';
		if ($upload&&file_exists($file['tmp_name'])){
			$ext=strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));
			$name=$_FILES['file']['name'];
		}
		elseif(file_exists($file)){
			$ext=strtolower(pathinfo($file,PATHINFO_EXTENSION));
			$name=basename($file);
		}
		else return null;
		//создаем временную папку пользователя
		$uploadDir.=$user;
		if(!file_exists($uploadDir))
			mkdir($uploadDir,0777);
		//уникальное имя для файла
		$un=get_id('',0,'',true);
		//оригинальное фото
		$fileoriginal=$uploadDir.'/'.$un.'.'.$ext;
		//миниатюра для показа при загрузке
		$filemin=$uploadDir.'/'.$un.'_min.'.$ext;
		//фото для показа на странице
		$filemed=$uploadDir.'/'.$un.'_med.'.$ext;
		//фото для увеличенного просмотра
		$filemax=$uploadDir.'/'.$un.'_max.'.$ext;
		
		if($upload){
			if (file_exists($file['tmp_name'])){
				clearstatcache();
				move_uploaded_file($file['tmp_name'], $fileoriginal);
				$file=$fileoriginal;
			}
		}
		
		//обработка файла
		if (file_exists($file)){
			list($w,$h)=list($wcur,$hcur)=getimagesize($file);
			if($w&&$h){
				$quality=($w*$h<=490000)?75:85;
				//фото Wx200px
				if($w>=$h){
					foto::resize($file,$filemin,90,200);
					foto::resize($file,$filemed,90,400);
					foto::resize($file,$filemax,$quality,1500);
				}
				else{
					foto::resize($file,$filemin,90,0,200);
					foto::resize($file,$filemed,90,0,400);
					foto::resize($file,$filemax,$quality,0,1500);
				}			
				
				if($wm){
					$img=imagecreatefromjpeg($filemax);
					list($w,$h)=getimagesize($filemax);
					$w=$w<2000?$w:2000;
					$h=$h<2000?$h:2000;
					//готовим ватермарк
					$watermark=imagecreatefrompng(($_main_dir?$_main_dir:$main_dir).'/../www/img/logo-wm.png');
					//накладываем ватермарк
					imagecopy($img,$watermark,0,0,0,0,$w,$h);
					//imagecopy($img,$watermark,$w/2-1000,$h/2-1000,0,0,2000,2000);
					imagejpeg($img,$filemax,$quality);
				}
				
				$file_json['file']['name']=$name;
				$file_json['file']['id']=$un;
				$file_json['file']['resolution']['w']=$wcur;
				$file_json['file']['resolution']['h']=$hcur;
				$file_json['file']['path']='/temp/uploads/'.$user.'/'.$un.'_min.jpg';
				return json_encode($file_json,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
			}
			else return null;
		}
	}
		
	static function resize($file_input,$file_output,$quality=85,$w_o=0,$h_o=0,$percent=false,$transparent=false){
		list($w_i,$h_i,$type)=getimagesize($file_input);
		if(!$w_i||!$h_i){
			echo 'Невозможно получить длину и ширину изображения';
			return;}
		$types=array('','gif','jpeg','png');
		$ext=$types[$type];
		if($ext){
			$func='imagecreatefrom'.$ext;
			$img_i=$func($file_input);
		}
		else{
			echo 'Некорректный формат файла';
			return;
		}
		if ($percent){
			$w_o=$percent*$w_i/100;
			$h_o=$percent*$h_i/100;
		}
		if(!$h_o)
			$h_o=$w_o/($w_i/$h_i);
		if(!$w_o)
			$w_o=$h_o/($h_i/$w_i);
		$img_o=imagecreatetruecolor($w_o,$h_o);
		//если оставлять прозрачность
		if($transparent){
			imagealphablending($img_o,false);
			imagesavealpha($img_o,true);
		}
		else
			imagefill($img_o,0,0,16777215);
		imagecopyresampled($img_o,$img_i,0,0,0,0,$w_o,$h_o,$w_i,$h_i);
		
		$exts=array('jpg','png','gif');
		$ext=pathinfo(strtolower($file_output),PATHINFO_EXTENSION);
		if (!in_array($ext,$exts)){
			echo 'Неверное расширение файла с результатом.';
			return;
		}
		if($ext=='jpg')
			return imagejpeg($img_o,$file_output,$quality);
		else{
			$func='image'.$ext;
			return $func($img_o,$file_output);
		}
		imagedestroy($img_o);
		imagedestroy($img_i);
	}
	
	//обрезка фоток
	static function crop($file_input,$file_output,$quality,$crop='square',$percent=false,$transparent=false){
		list($w_i,$h_i,$type)=getimagesize($file_input);
		if (!$w_i||!$h_i){
			echo 'Невозможно получить длину и ширину изображения';
			return;
		}
		$types=array('','gif','jpeg','png');
		$ext=$types[$type];
		if ($ext){
			$func='imagecreatefrom'.$ext;
			$img_i=$func($file_input);} 
		else {
			echo 'Некорректный формат файла';
			return;
		}
		if ($crop=='square'){
			$min=$w_i;
			if ($w_i>$h_i)$min=$h_i;
			$w_o=$h_o=$min;}
		else {
			list($x_o,$y_o,$w_o,$h_o)=$crop;
			if ($percent){
				$w_o*=$w_i/100;
				$h_o*=$h_i/100;
				$x_o*=$w_i/100;
				$y_o*=$h_i/100;}
			if ($w_o<0){
				$w_o+=$w_i;
				$w_o-=$x_o;}
			if ($h_o<0){
				$h_o+=$h_i;
				$h_o-=$y_o;}}
		$img_o=imagecreatetruecolor($w_o,$h_o);
		//если оставлять прозрачность
		if($transparent){
			imagealphablending($img_o,false);
			imagesavealpha($img_o,true);
		}
		else
			imagefill($img_o,0,0,16777215);
		imagecopy($img_o,$img_i,0,0,$x_o,$y_o,$w_o,$h_o);
		
		$exts=array('jpg','png','gif');
		$ext=explode('.',strtolower($file_output));
		$ext=end($ext); 
		if (!in_array($ext,$exts)){
			echo 'Неверное расширение файла с результатом.';
			return;
		}
		if($ext=='jpg')
			return imagejpeg($img_o,$file_output,$quality);
		else{
			$func='image'.$ext;
			return $func($img_o,$file_output);}
		imagedestroy($img_o);
		imagedestroy($img_i);
	}
	
	//прозрачность
	static function alfa($filename){
		$img=imagecreatefromstring(file_get_contents($filename));
		imagealphablending($img,false);
		imagesavealpha($img,true);
		$width=imagesx($img);
		$height=imagesy($img);
		//$result=imagepng($img);
		//imagedestroy($img);
		return $img;
	}
	
	//поворот фоток
	static function rotate($file,$degree,$qality=100,$transparent=false,$display=false){
		list($w_i,$h_i,$type)=getimagesize($file);
		if (!$w_i||!$h_i){
			echo 'Невозможно получить длину и ширину изображения';
			return;
		}
		$types=array('','gif','jpeg','png');
		$ext=$types[$type];
		if($ext){
			$func='imagecreatefrom'.$ext;
			$img=$func($file);} 
		else{
			echo 'Некорректный формат файла';
			return;
		}
		$img_o=imagecreatetruecolor($w_i,$h_i);
		//если оставлять прозрачность
		if($transparent){
			imagealphablending($img_o,false);
			imagesavealpha($img_o,true);
		}
		else
			imagefill($img_o,0,0,16777215);
		$img_i=imagecreatefromjpeg($file);
		imagecopy($img_o,$img_i,0,0,0,0,$w_i,$h_i);
		$img_o=imagerotate($img_o,$degree,0);
		if($type==2){
			imagejpeg($img_o,$file,$qality);
			if($display)
				imagejpeg($img_o,null,$qality);
		}
		else{
			$func='image'.$ext;
			$func($img_o,$file);
			if($display)
				$func($img_o);
		}
		imagedestroy($img_o);
		imagedestroy($img_i);
	}
		
	//скругленные углы с прозрачностью
	static function round_crop($filename,$radius,$rate){
		$img=imagecreatefromstring(file_get_contents($filename));
		imagealphablending($img,false);
		imagesavealpha($img,true);
		$width=imagesx($img);
		$height=imagesy($img);
		$rs_radius=$radius*$rate;
		$rs_size=$rs_radius*2;
		$corner=imagecreatetruecolor($rs_size,$rs_size);
		imagealphablending($corner,false);
		$trans=imagecolorallocatealpha($corner,255,255,255,127);
		imagefill($corner,0,0,$trans);
		$positions=array(
			array(0,0,0,0),
			array($rs_radius,0,$width-$radius,0),
			array($rs_radius,$rs_radius,$width-$radius,$height-$radius),
			array(0,$rs_radius,0,$height-$radius),
		);
		foreach($positions as $pos)
			imagecopyresampled($corner,$img,$pos[0],$pos[1],$pos[2],$pos[3],$rs_radius,$rs_radius,$radius,$radius);
		$lx=$ly=0;
		$i=-$rs_radius;
		$y2=-$i;
		$r_2=$rs_radius * $rs_radius;
		for (;$i<=$y2;$i++){
			$y=$i;
			$x=sqrt($r_2-$y*$y);
			$y+=$rs_radius;
			$x+=$rs_radius;
			imageline($corner,$x,$y,$rs_size,$y,$trans);
			imageline($corner,0,$y,$rs_size-$x,$y,$trans);
			$lx=$x;
			$ly=$y;
		}
		foreach($positions as $i=>$pos)
			imagecopyresampled($img,$corner,$pos[2],$pos[3],$pos[0],$pos[1],$radius,$radius,$rs_radius,$rs_radius);
		$result=imagepng($img);
		imagedestroy($img);
		return $result;
	}
	
	//удаление фона с фотографий с печатями и подписями
	static function stamp($file_input,$file_output,$border=2,$autodetect=true){
		$border_array=array(1=>1000000,2=>4000000,3=>6000000);
		$border=$border_array[$border];
		$img=imagecreatefromjpeg($file_input);
		//переводим в градации серого
		imagefilter($img,IMG_FILTER_GRAYSCALE);
		//выделяем границы
		imagefilter($img,IMG_FILTER_EDGEDETECT);
		//уменьшаем контраст
		imagefilter($img,IMG_FILTER_CONTRAST,-40);
		//получаем ширину и высоту
		$w=imagesx($img);
		$h=imagesy($img);
		//создаем массив с точками и их цветами
		$colors=array();
		for($x=0;$x<$w;$x++) {
			$colors[$x]=array();
			for($y=0;$y<$h;$y++)
				$colors[$x][$y]=imagecolorat($img,$x,$y);
		}
		//разделяем цвета на синий и белый, используя border
		$blue=511;
		for($x=0;$x<$w;$x++)
			for($y=0;$y<$h;$y++)
				$colors[$x][$y]=($colors[$x][$y]>$border)?16777215:$blue;
		//с автораспознаванием - убрать фон и обрезать до границ оттиска
		if($autodetect){
			//ищем точные координаты оттиска
			$X=$Y=array(); 
			for($x=0;$x<$w;$x++)
				for($y=0;$y<$h;$y++)
					if($colors[$x][$y]==$blue){
						$X[1]=$x;
						$Y[1]=$y;
						break(2);
						break(1);
					}
			for($y=0;$y<$h;$y++)
				for($x=0;$x<$w;$x++)
					if($colors[$x][$y]==$blue){
						$X[2]=$x;
						$Y[2]=$y;
						break(2);
						break(1);
					}
			for($x=$w-1;$x>=0;$x--)
				for($y=$h-1;$y>=0;$y--)
					if($colors[$x][$y]==$blue){
						$X[3]=$x;
						$Y[3]=$y;
						break(2);
						break(1);
					}
			for($y=$h-1;$y>=0;$y--)
				for($x=$w-1;$x>=0;$x--)
					if($colors[$x][$y]==$blue){
						$X[4]=$x;
						$Y[4]=$y;
						break(2);
						break(1);
					}
			//создаем изображение			
			$res=imagecreatetruecolor($X[3]-$X[1]+1,$Y[4]-$Y[2]+1);
			//включаем прозрачность
			imagealphablending($res,false);
			imagesavealpha($res,true);
			$trans=imagecolorallocatealpha($res,255,255,255,127);
			//копируем полученное изображение в новое, заменяя белый цвет прозрачным
			for($x=$X[1],$i=0;$x<=$X[3],$i<=$X[3]-$X[1];$x++,$i++)
				for($y=$Y[2],$j=0;$y<=$Y[4],$j<=$Y[4]-$Y[2];$y++,$j++)
					($colors[$x][$y]==$blue)?imagesetpixel($res,$i,$j,$colors[$x][$y]):imagesetpixel($res,$i,$j,$trans);
		}
		//без автораспознавания - только убрать фон, размеры оставить
		else{
			//создаем новое изображение
			$res=imagecreatetruecolor($w,$h);
			//включаем прозрачность
			imagealphablending($res,false);
			imagesavealpha($res,true);
			$trans=imagecolorallocatealpha($res,255,255,255,127);
			//копируем полученное изображение в новое, заменяя белый цвет прозрачным
			for($x=0;$x<$w;$x++)
				for($y=0;$y<$h;$y++)
					($colors[$x][$y]==$blue)?imagesetpixel($res,$x,$y,$colors[$x][$y]):imagesetpixel($res,$x,$y,$trans);
		}
		//выводим результат
		imagepng($res,$file_output);
		imagedestroy($res);
	}

}
?>