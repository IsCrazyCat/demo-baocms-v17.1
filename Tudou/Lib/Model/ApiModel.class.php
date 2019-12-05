<?php
class ApiModel extends CommonModel{




    //生成带参数二维码 
    public  function getQrcode($fuid){
		$config = D('Setting')->fetchAll();
		$connect = D('Connect')->where(array('type'=>'weixin','uid'=>$fuid))->find();
		if($config['weixin']['user_auto'] && $connect['open_id']){
			$qrcode = $this->getWeixinCodePng($fuid);//获取微信二维码
			return $qrcode;
		}else{
			$token = 'fuid_' . $fuid;
			$url = U('Wap/passport/register', array('fuid' => $fuid));
			$file = tuQrCode($token,$url,8,'user');
			return $file;
		}
		return true;
    }
	
	
	//获取二维码
	public function getWeixinCodePng($fuid){
			$config = D('Setting')->fetchAll();
			require_cache(APP_PATH.'Lib/phpqrcode/phpqrcode.php'); 
			$size = 8;
			$token = 'fuid_' . $fuid;
			import("@/Net.Jssdk");
			$jssdk = new JSSDK($config['weixin']['appid'], $config['weixin']['appsecret']);
			$wxqimg = $jssdk->getTemporaryQrcode($fuid);
			
			$name = date('Y/m/d/', NOW_TIME);
			$md5 = md5($token);
			$patch = BASE_PATH.'/attachs/'. 'weixinuid/'.$name;
			if(!file_exists($patch)){
				mkdir($patch,0755,true);
			}
			$file = '/attachs/weixinuid/'.$name.$md5.'.png';
			$fileName  = BASE_PATH.'/attachs/'.$file;
			if(!file_exists($fileName)){
				$level = 'L';
				$data = $wxqimg;
				QRcode::png($data, $fileName, $level, $size,2,true);
			}
		 return $file;
	 }
	 
	 
	//获取用户头像
	public function getWeixinUserFacePng($fuid){
		$config = D('Setting')->fetchAll();
		 $token = 'face_' . $fuid;
		 
		 $md6 = md5($token);
		 $dir2 = substr($md6,0,3).'/'.substr($md6,3,3).'/'.substr($md6,6,3).'/';
		 $patch = BASE_PATH.'/attachs/'. 'weixinuid/'.$dir2;
		 $catalog = '/attachs/'. 'weixinuid/'.$dir2;
		 
		 $Connect = D('Connect')->where(array('uid'=>$fuid,'type'=>'weixin'))->find();
		 $Users = D('Users')->where(array('user_id'=>$fuid))->find();
		
		 if($Connect['headimgurl']){
			$arr = $this->getImage($Connect['headimgurl'],$patch,$md6,$catalog,1);
			$res = $this->imagepress($arr['save_path'], 140, 140);
			return $arr;
		 }elseif($Users['face']){
			$arr = $this->getImage(config_img($Users['face']),$patch,$md6,$catalog,1);
			$res = $this->imagepress($arr['save_path'], 140, 140);
			return $arr;
		 }else{
			$arr = $this->getImage(config_weixin_img($config['site']['logo']),$patch,$md6,$catalog,1);
			$res = $this->imagepress($arr['save_path'], 140, 140);
			return $arr;
		}
		return false;
	 }
	 
	 
	 //生成带参数二维码 
    public  function getPoster($fuid){
		$config = D('Setting')->fetchAll();
		$Users = D('Users')->where(array('user_id'=>$fuid))->find();
		
		if($Users['poster']){
			return $Users['poster'];
		}else{
				$qrcode = $this->getWeixinCodePng($fuid);//获取微信二维码
				$face = $this->getWeixinUserFacePng($fuid);//获取用户头像
				$poster_path = $config['site']['host'].'/Public/images/poster.png';
				
				//如果没有获取到用户头像
				if(empty($face['catalog'])){
					$catalog = $config['site']['host'].'/attachs/avatar.jpg';
				}else{
					$catalog = $config['site']['host'].''.$face['catalog'];
				}
				
				$imgs = array(
					'poster' => $poster_path,
					'qrcode' => $config['site']['host'].'/attachs/'.$qrcode,
					'logo' => config_weixin_img($config['site']['logo']),
					'face' =>$catalog,
				);
				//p($imgs);die;
				$poster = $this->mergerImg($imgs,$face['save_path'],$face['catalog']);
				if($poster){
					D('Users')->where(array('user_id'=>$fuid))->save(array('poster'=>$poster));
				}
				return $poster;
			
		}
		return false;
		
    }
	


	
	//$filepath图片路径,$new_width新的宽度,$new_height新的高度  
	public function imagepress($filepath, $new_width, $new_height) {  
		$source_info   = getimagesize($filepath);  
		$source_width  = $source_info[0];  
		$source_height = $source_info[1];  
		$source_mime   = $source_info['mime'];  
		$source_ratio  = $source_height / $source_width;  
		$target_ratio  = $new_height / $new_width;  
		//源图过高  
		if($source_ratio > $target_ratio){  
			$cropped_width  = $source_width;  
			$cropped_height = $source_width * $target_ratio;  
			$source_x = 0;  
			$source_y = ($source_height - $cropped_height) / 2;  
		}elseif($source_ratio < $target_ratio) {  // 源图过宽  
			$cropped_width  = $source_height / $target_ratio;  
			$cropped_height = $source_height;  
			$source_x = ($source_width - $cropped_width) / 2;  
			$source_y = 0;  
		}else{  // 源图适中 
			$cropped_width  = $source_width;  
			$cropped_height = $source_height;  
			$source_x = 0;  
			$source_y = 0;  
		}  
		switch ($source_mime)  {  
			case 'image/gif':  
				$source_image = imagecreatefromgif($filepath);  
				break;  
			case 'image/jpeg':  
				$source_image = imagecreatefromjpeg($filepath);  
				break;  
			case 'image/png':  
				$source_image = imagecreatefrompng($filepath);  
			break;  
				default:  
				return false;  
			break;  
		}  
		$target_image  = imagecreatetruecolor($new_width, $new_height);  
		$cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);  
		imagecopy($cropped_image, $source_image, 0, 0, $source_x, $source_y, $cropped_width, $cropped_height);  
		imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $new_width, $new_height, $cropped_width, $cropped_height);  
		imagejpeg($target_image,$filepath);
		imagedestroy($source_image);  
		imagedestroy($target_image);  
		imagedestroy($cropped_image);  
		return true; 
	}  
	
	
	
  
	
	//合并图片
	public function mergerImg($imgs,$save_path,$catalog) {
        list($max_poster_width, $max_poster_height) = getimagesize($imgs['poster']);
        $posters = imagecreatetruecolor($max_poster_width, $max_poster_height);
        $poster_im = imagecreatefrompng($imgs['poster']);
        imagecopy($posters,$poster_im,0,0,0,0,$max_poster_width,$max_poster_height);
        imagedestroy($poster_im);
		
        $qrcode_im = imagecreatefrompng($imgs['qrcode']);
        $qrcode_info = getimagesize($imgs['qrcode']);
        imagecopy($posters,$qrcode_im,($max_poster_width-$qrcode_info[0])/2,($max_poster_height/2)-100,0,0,$qrcode_info[0],$qrcode_info[1]);
        imagedestroy($qrcode_im);
		
		
		//$logo_im = imagecreatefrompng($imgs['logo']);
        //$logo_info = getimagesize($imgs['logo']);
        //imagecopy($posters,$logo_im,$max_poster_width-200,$max_poster_height-60,0,0,$logo_info[0],$logo_info[1]);
        //imagedestroy($qrcode_im);

	
		$face_im = imagecreatefromjpeg($imgs['face']);
        $face_info = getimagesize($imgs['face']);
	
		
        imagecopy($posters,$face_im,($max_poster_width-$face_info[0])/5,$max_poster_height -1010,0,0, $face_info[0],$face_info[1]);
        imagedestroy($face_im);
		
		
       
        imagejpeg($posters,$save_path);
		return $catalog; 
		//imagejpeg($target_image,$filepath);
	}


	
	
	//远程下载微信头像
	public function getImage($url,$save_dir='',$filename='',$catalog,$type=0){  
		$ext=".png";//以jpg的格式结尾  
		clearstatcache();//清除文件缓存  
		if(trim($url)==''){  
			return array('file_name'=>'','save_path'=>'','error'=>1);  
		}  
		if(trim($save_dir)==''){  
			$save_dir='./';  
		}  
		if(trim($filename)==''){//保存文件名  
			$filename=time().$ext;  
		}else{  
			$filename = $filename.$ext;  
		}  
		if(0!==strrpos($save_dir,'/')){  
			$save_dir.='/';  
		}  
		//创建保存目录  
		if(!is_dir($save_dir)){//文件夹不存在，则新建  
			//print_r($save_dir."文件不存在");  
			mkdir(iconv("UTF-8", "GBK", $save_dir),0777,true);  
			//mkdir($save_dir,0777,true);  
		}  
		//获取远程文件所采用的方法   
		if($type){  
			$ch=curl_init();  
			$timeout=3;  
			curl_setopt($ch,CURLOPT_URL,$url);  
			curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);  
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);  
			$img=curl_exec($ch);  
			curl_close($ch);  
		}else{  
			ob_start();   
			readfile($url);  
			$img=ob_get_contents();   
			ob_end_clean();   
		}  
		$size=strlen($img);  
		//文件大小   
		//var_dump("文件大小:".$size);  
		$fp2=@fopen($save_dir.$filename,'w');  
		fwrite($fp2,$img);  
		fclose($fp2);  
		unset($img,$url);  
		return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'catalog'=>$catalog.''.$filename,'error'=>0);  
	}  
	
	
	//生成条码，返回条码路径
	public function getBarcodeGen($barcode){
		require(APP_PATH.'Lib/barcodegen/class/BCGFont.php');
		require(APP_PATH.'Lib/barcodegen/class/BCGColor.php');
		require(APP_PATH.'Lib/barcodegen/class/BCGDrawing.php'); 
		include(APP_PATH.'Lib/barcodegen/class/BCGean13.barcode.php'); 
		$font = new BCGFont(APP_PATH.'Lib/barcodegen/class/font/Arial.ttf', 16);
		$color_black = new BCGColor(0, 0, 0);
		$color_white = new BCGColor(255, 255, 255);//颜色
		
		$code = new BCGean13();
		$code->setScale(2); // Resolution
		$code->setThickness(30); // Thickness
		$code->setForegroundColor($color_black); // Color of bars
		$code->setBackgroundColor($color_white); // Color of spaces
		$code->setFont($font);
		
		$code->parse($barcode); // Text
		
		$patch = BASE_PATH.'/attachs/barcode/';//路径
		
		$drawing = new BCGDrawing($patch.''.$goods_id.".png", $color_white);
		$drawing->setBarcode($code);
		$drawing->draw();
		header('Content-Type: image/png');
		$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);		
		$file = '/attachs/barcode/'.$goods_id.".png";  
		return $file;  
	} 
	
	
		
}