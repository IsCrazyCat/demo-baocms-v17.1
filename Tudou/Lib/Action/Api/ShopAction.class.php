<?php

class ShopAction extends CommonAction{
	//商家分类
	public function ShopType(){
		$arr = D('Shopcate')->where(array('parent_id'=>0))->limit(0,10)->select();
		$kk = 0;
		foreach($arr as $k => $val){
			$kk ++ ;
			$arr[$k]['id'] = $val['cate_id'];
			$arr[$k]['img'] = __HOST__.'/static/default/wap/image/life/life_cate_'.$kk.'.png';
		}
        $json_str = json_encode($arr);
        exit($json_str); 
	}
	
	//商家广告
	public function Ad(){
		$list = D('Ad')->where(array('site_id'=>'57','closed'=>'0'))->select();
		foreach ($list as $k => $val){
			$list[$k]['type'] = 2;
			$list[$k]['id'] = $val['ad_id'];
			$list[$k]['img'] = config_weixin_img($val['photo']);
		}
        $json_str = json_encode($list);
        exit($json_str); 
	}
	
	//最新入驻商家
	public function news(){
		$res = D('Shop')->where(array('audit'=>'1','closed'=>'0'))->order(array('create_time' => 'desc'))->limit(0,5)->select();
        $json_str = json_encode($res);
        exit($json_str); 
	}
	
	//商家列表
	public function Lists(){
		$obj = D('Shop');
		import('ORG.Util.Page');
        $map = array('audit' => 1, 'closed' => 0);
		if($keyword = I('keywords','','htmlspecialchars')){
            $map['shop_name|addr'] = array('LIKE', '%' . $keyword . '%');
        }
		$count = $obj->where($map)->count();
        $Page = new Page($count,5);
        $show = $Page->show();
        $p = I('p');
        if($Page->totalPages < $p){
            die('');
        }
		$list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
			$list[$k]['id'] = $val['shop_id'];
			$list[$k]['views'] = $val['view'];
			$list[$k]['coordinates'] = $this->getBaiduChangeMap($val['lat'],$val['lng']);
			$list[$k]['details'] = D('Shopdetails')->find($val['shop_id']);
			$list[$k]['logo'] = config_weixin_img($val['photo']);
		}
        $json_str = json_encode($list);
        exit($json_str); 
	}
	
	
	//商家分类列表
	public function TypeStoreList(){
		$obj = D('Shop');
		import('ORG.Util.Page');
        $map = array('audit' => 1, 'closed' => 0);
        if($cat = (int) I('storetype_id')){
            $catids = D('Shopcate')->getChildren($cat);
            $map['cate_id'] = array('IN', $catids);
        }
		$count = $obj->where($map)->count();
        $Page = new Page($count,5);
        $show = $Page->show();
        $p = I('p');
        if($Page->totalPages < $p){
            die('');
        }
		$list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
			$list[$k]['id'] = $val['shop_id'];
			$list[$k]['views'] = $val['view'];
			$list[$k]['coordinates'] = $this->getBaiduChangeMap($val['lat'],$val['lng']);
			$list[$k]['details'] = D('Shopdetails')->find($val['shop_id']);
			$list[$k]['logo'] = config_weixin_img($val['photo']);
		}
        $json_str = json_encode($list);
        exit($json_str); 
	}
	

	
	//商家详情
	public function detail(){
		$shop_id = I('id','','trim');
		$detail = D('Shop')->find($shop_id);
		
		$detail['id'] = $detail['shop_id'];
		$detail['views'] = $detail['view'];
		$detail['coordinates'] = $this->getBaiduChangeMap($detail['lat'],$detail['lng']);
		$detail['details'] = D('Shopdetails')->where(array('shop_id'=>$shop_id))->find();
		$detail['detail'] =  cleanhtml($detail['details']['details']);
		$detail['logo'] = config_weixin_img($detail['photo']);
		$detail['ad'] = $this->getShopListPics($detail['shop_id']);//商家图片获取
		$detail['img'] = config_weixin_img($detail['photo']);//小程序编码
		$data['store'][]=$detail;
		
		$list = M('ShopDianping')->where(array('shop_id'=>$detail['shop_id'],'closed'=>0))->limit(0,30)->select();
		foreach($list as $k => $val){
			$list[$k]['id'] = $val['dianping_id'];
			$Users = D('Users')->find($val['user_id']);
			$list[$k]['user_img'] = config_weixin_img($Users['face']);
			$list[$k]['name'] = config_user_name($Users['nickname']);
			$list[$k]['details'] = cleanhtml($val['contents']);
		}
		
        $data['pl']= $list;
	    echo json_encode($data);
	}
	
	//获取列表图片开始
	public function getShopListPics($shop_id){
		$list = M('ShopPic')->where(array('shop_id'=>$shop_id,'audit'=>1))->limit(0,30)->select();
		foreach($list as $k => $val){
			$photos[$k] = $val['photo'];
		}
		$Shop = D('Shop')->find($shop_id);
		if($Shop['photo']){
			$photo = config_weixin_img($Shop['photo']);
			array_unshift($photos,$photo);
		}
		$res = implode(",",$photos);
		return "".$res ."";
	}
	
	//获取商城列表图片开始
	public function getGoodsListPics($goods_id){
		$list = M('GoodsPhotos')->where(array('goods_id'=>$shop_id))->limit(0,5)->select();
		foreach($list as $k => $val){
			$photos[$k] = $val['photo'];
		}
		$Goods = D('Goods')->find($shop_id);
		if($Goods['photo']){
			$photo = config_weixin_img($Goods['photo']);
			array_unshift($photos,$photo);
		}
		$res = implode(",",$photos);
		return "".$res ."";
	}
	
	//商品列表
	public function goods(){
		$shop_id = I('store_id','','trim');
		
		$list = M('Goods')->where(array('audit'=>1,'closed'=>0))->limit(0,6)->select();
		//$list = M('Goods')->where(array('shop_id'=>$shop_id,'audit'=>1,'closed'=>0))->limit(0,6)->select();
		foreach($list as $k => $val){
			$list[$k]['id'] = $val['goods_id'];
			$list[$k]['is_show'] = 1;
			$list[$k]['price'] = round($val['mall_price']/100,2);
			$list[$k]['img'] = config_weixin_img($val['photo']);
			$list[$k]['imgs'] = $this->getGoodsListPics($val['goods_id']);//商品图片获取
		}
		//p($list);die;
        $json_str = json_encode($list);
        exit($json_str); 
	}
	
	//收藏
    public function Collection(){
         $shop_id = I('store_id','','trim');
		 $user_id = I('user_id','','trim');
		 $information_id = I('information_id','','trim');
		 
		 $ShopFavorites = M('ShopFavorites')->where(array('shop_id'=>$shop_id,'user_id'=>$user_id))->find();
         if($ShopFavorites){
             M('ShopFavorites')->delete($ShopFavorites['favorites_id']);
         }else{
			 $data = array('shop_id'=>$shop_id,'user_id'=>$user_id,'create_time'=>NOW_TIME,'create_ip'=>get_client_ip());
			 $res = M('ShopFavorites')->add($data);
             if($res){
                echo '1';
             }else{
                echo '2';
             }
          } 
    }
	
	
	//商家点评列表
	public function dianping(){
		$shop_id = I('store_id','','trim');
		$obj = D('ShopDianping');
		import('ORG.Util.Page');
        $map = array('audit' => 1, 'closed' => 0);
		$count = $obj->where($map)->count();
        $Page = new Page($count,5);
        $show = $Page->show();
        $p = I('p');
        if($Page->totalPages < $p){
            die('');
        }
		$list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
			$list[$k]['id'] = $val['dianping_id'];
			$Users = D('Users')->find($val['user_id']);
			$list[$k]['user_img'] = config_weixin_img($Users['face']);
			$list[$k]['name'] = config_user_name($Users['nickname']);
			$list[$k]['details'] = cleanhtml($val['contents']);
		}
        $json_str = json_encode($list);
        exit($json_str); 
	}
	
	
	//百度地图转换为谷歌地图
	public function getBaiduChangeMap($lat,$lng){
		$x_pi = 3.14159265358979324 * 3000.0 / 180.0;
        $x = $lng - 0.0065;
        $y = $lat - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
        $lng = $z * cos($theta);
        $lat = $z * sin($theta);
		return $lat.','.$lng;
	}
	
	//腾讯地图转换为百度地图
	public function getMapChangeBaidu($lat,$lng){
		$x_pi = 3.14159265358979324 * 3000.0 / 180.0;
        $x = $lng;
        $y = $lat;
        $z =sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
        $lng = $z * cos($theta) + 0.0065;
        $lat = $z * sin($theta) + 0.006;
		return $lat.','.$lng;
	}
	
	
	
		
	
	  
	 //商家d点评
      public function StoreComments(){
		$data['shop_id'] = I('store_id','','trim'); 
		$data['user_id'] = I('user_id','','trim');
		$data['details'] = I('details','','trim,htmlspecialchars');
		$data['score'] = I('score','','trim,htmlspecialchars');
		$data['time']=time();
		if($dianping_id = D('ShopDianping')->add($add)){
			 D('Shop')->updateCount($shop_id, 'score_num');
             D('Users')->updateCount($this->uid, 'ping_num');
             D('Shopdianping')->updateScore($shop_id);
		     D('Users')->prestige($this->uid, 'dianping_shop');
			 echo $dianping_id;
	    }else{
         echo '2';
       }
     }
	
	 
	  
	  
	 
	  
	  //商家二维码
	  public function StoreCode(){
		  $config = D('Setting')->fetchAll();
		  $shop_id = I('store_id','','trim');
		  $base64= $this->getCoade($shop_id);
		  $base64_image_content="data:image/jpeg;base64,".$base64;
		  $ename='tudou_shop_'.$shop_id;
		  if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
			$type = $result[2];
			$new_file = BASE_PATH ."/attachs/weixin/wxapp/";
			if(!file_exists($new_file)){
				mkdir($new_file, 0777);//检查是否有该文件夹，如果没有就创建，并给予最高权限
			}
			$wname=$ename.".{$type}";
			$new_file = $new_file.$wname;
			if(file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
				
			}else{
				echo '新文件保存失败';
			}
		}
		
		echo $config['site']['host']."/attachs/weixin/wxapp/{$ename}.jpeg";
	
	}

	
	//获取getaccess_token
	public function getaccess_token(){
		  $config = D('Setting')->fetchAll();
		  $appid=$config['wxapp']['appid'];
		  $secret=$config['wxapp']['appsecret'];
		  $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret."";
		  $ch = curl_init();
		  curl_setopt($ch, CURLOPT_URL,$url);
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
		  $data = curl_exec($ch);
		  curl_close($ch);
		  $data = json_decode($data,true);
		  return $data['access_token'];
	  }
	  
	 //发送东西
	 public function set_msg($shop_id){
			$access_token = $this->getaccess_token();
			$data2=array(
				"scene"=>$shop_id,
				"page"=>"zh_tcwq/pages/sellerinfo/sellerinfo",//小程序目录
				"width"=>400
			);
			$data2 = json_encode($data2);
			$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token."";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data2);
			$data = curl_exec($ch);
			curl_close($ch);
			//p($data);die;
			return $data;
	  }
	  
	  //获取getCoade
	  public function  getCoade($shop_id){
			$img = $this->set_msg($shop_id);
			$img = base64_encode($img);
			return $img;
	  }
	  
	  
	
	//帖子评论成功模板消息后期整合这里还有问题
	public function StorehfMessage(){
		$access_token = $this->getaccess_token();
	    $res2=pdo_get('zhtc_sms',array('uniacid'=>$_W['uniacid']));
	    $sql="select a.details,a.store_id,a.time,b.name as user_name from " . tablename("zhtc_comments") . " a"  . " left join " . tablename("zhtc_user") . " b on b.id=a.user_id  WHERE a.id=:id ";
	    $res=pdo_fetch($sql,array(':id'=>$_GPC['pl_id']));
	    $time=date("Y-m-d H:i:s",$res['time']);
	    $formwork ='{
			 "touser": "'.$_GET["openid"].'",
			 "template_id": "'.$res2["tid3"].'",
			 "page":"zh_tcwq/pages/sellerinfo/sellerinfo?id='.$res['store_id'].'",
			 "form_id":"'.$_GET['form_id'].'",
			 "data": {
			   "keyword1": {
				 "value": "'.$res['details'].'",
				 "color": "#173177"
			   },
			   "keyword2": {
				 "value":"'.$res['user_name'].'",
				 "color": "#173177"
			   },
			   "keyword3": {
				 "value": "'.$time.'",
				 "color": "#173177"
			   }
			  
			 }   
		   }';
	   $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$access_token."";
	   $ch = curl_init();
	   curl_setopt($ch, CURLOPT_URL,$url);
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
	   curl_setopt($ch, CURLOPT_POST,1);
	   curl_setopt($ch, CURLOPT_POSTFIELDS,$formwork);
	   $data = curl_exec($ch);
	   curl_close($ch);
	   return $data;
	}

	 
	 //商家回复点评
     public function Reply(){
		 $id = I('id','','trim');
		 $reply = I('reply','','trim,htmlspecialchars');
		 $res = D('Shopdianping')->where(array('dianping_id'=>$id))->save(array('reply'=>$reply));
		 if($res){
			 echo '1';
		 }else{
			 echo '2';
		 }
     }
	 
	 //商家入驻时候选择分类
     public function storetype(){
        $arr = D('Shopcate')->where(array('parent_id'=>array('neq',0)))->limit(0,60)->select();
		$kk = 0;
		foreach($arr as $k => $val){
			$kk ++ ;
			$arr[$k]['id'] = $val['cate_id'];
			$arr[$k]['img'] = __HOST__.'/static/default/wap/image/life/life_cate_'.$kk.'.png';
		}
        $json_str = json_encode($arr);
        exit($json_str); 
      }
	
	//商家入驻费用
	public function InMoney(){
		$config = D('Setting')->fetchAll();
		if($config['shop']['shop_apply_prrice']){
			$res[] = array(
				'id' => 3,
				'type' => 3,
				'money' => round($config['shop']['shop_apply_prrice']/100,2),
			);
			echo json_encode($res);
		}else{
			$res[] = array(
				'id' => 1,
				'type' => 2,
				'money' => 0,
			);
			echo json_encode($res);
		}
	}


	//解密小程序专用
	public function Jiemi(){
		  include APP_PATH . 'Lib/Action/Api/wxBizDataCrypt.php';
		  $config = D('Setting')->fetchAll();
		  $appid = $config['wxapp']['appid'];
		  $sessionKey = I('sessionKey','','trim,htmlspecialchars');
		  $encryptedData = I('data','','trim,htmlspecialchars');
		  $iv = I('iv','','trim,htmlspecialchars');;
		  $pc = new WXBizDataCrypt($appid, $sessionKey);
		  $errCode = $pc->decryptData($encryptedData, $iv, $data );
		  if($errCode == 0){
			  print($data . "\n");
		  }else{
			  print($errCode . "\n");
		  }
	 }
	 
	 //商家入驻页面
     public function Store(){
		 $data['city_id'] = I('city_id','','trim');//城市id
         $data['user_id'] = I('user_id','','trim');//用户id
         $data['shop_name']= I('store_name','','trim,htmlspecialchars');//商家名称
		 $storetype_id = I('storetype_id','','trim');//行业分类id
         $data['cate_id'] = I('storetype_id','','trim');//之行业分类id
         $data['start_time']=I('start_time','','trim,htmlspecialchars');//营业时间
         $data['end_time']=I('end_time','','trim,htmlspecialchars');//营业时间
		 
		 
         $data['contact']=I('keywordcontact','','trim,htmlspecialchars');//关键字
		 
         $data['tel']=I('tel','','trim,htmlspecialchars');//电话
		 $data['addr']= I('address','','trim,htmlspecialchars');//地址
		 
		 
         $data['photo']=I('logo','','trim');//商家photo
		 $data['logo']=I('logo','','trim');//商家logo
         $data['panorama_url']=I('vr_link','','trim');//vr
		 
         $data['service_weixin_qrcode']=I('weixin_logo','','trim');//老板微信
		 
         $data['start_time']=I('start_time','','trim');
         $data['end_time']=I('end_time','','trim');
         $data['create_time'] = NOW_TIME;
         $data['create_ip'] = get_client_ip();
         $data['money']=I('money','','trim,htmlspecialchars');//付款价格
         $data['details']=I('details','','trim,htmlspecialchars');//商家简介
		 
         $coordinates = I('coordinates','','trim,htmlspecialchars');//坐标
		 $coordinates2 = explode(',',$coordinates);

		 $getMapChangeBaidu = $this->getMapChangeBaidu($coordinates2['0'],$coordinates2['1']);
		 $getMapChangeBaidus = explode(',',$getMapChangeBaidu);
		 
		 $data['lat'] = htmlspecialchars($getMapChangeBaidus['0']);
		 $data['lng'] = htmlspecialchars($getMapChangeBaidus['1']);
        
       
         if($shop_id = D('Shop')->add($data)){
			D('Shop')->buildShopQrcode($shop_id,15);//生成商家二维码
			
			$ads = explode(',',I('ad','','trim'));
			foreach($ads as $val){
				D('Shoppic')->where(array('shop_id'=>$shop_id))->add(array('photo'=>$val));
			}

			$imgs = explode(',',I('img','','trim'));

			foreach($imgs as $val){
				if($val != ''){
					$arrs .= '<img src='. config_img($val) .'>';
				}
			}
			
			$data['details'] = $data['details'] .'<br>'. $arrs;		
			$arr = array(
				'details' => $data['details'], 
				'price' => $data['money'], 
				'business_time' => $data['start_time'].''.$data['end_time'],
			);
            D('Shopdetails')->upDetails($shop_id,$arr);
             echo $shop_id;
         }else{
             echo '2';
         }
    }
	 
	 
	//商家入驻模板消息后期开发
	public function rzmessage(){
		$access_token = $this->getaccess_token();
		echo '1';
	}
	
	
}