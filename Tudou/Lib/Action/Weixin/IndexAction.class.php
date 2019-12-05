<?php
class IndexAction extends CommonAction{
    public function index(){
        $data = $this->weixin->request();
        switch ($data['MsgType']) {
            case 'event':
                if ($data['Event'] == 'subscribe') {
                    if (isset($data['EventKey']) && !empty($data['EventKey'])) {
                        $this->events();
                    } else {
                        $this->event();
                    }
                }
                if ($data['Event'] == 'SCAN') {
                    $this->scan();
                }
                break;
            case 'location':
                $this->location($data);
                break;
            default:
                $this->keyword($data);
                break;
        }
    }
    private function location($data){
        $lat = addcslashes($data['Location_X']);
        $lng = addcslashes($data['Location_Y']);
        $list = D('Shop')->where(array('audit' => 1, 'closed' => 0))->order(" (ABS(lng - '{$lng}') +  ABS(lat - '" . $lat . '\') )  asc ')->limit(0, 10)->select();
        if (!empty($list)) {
            $content = array();
            foreach ($list as $item) {
                $content[] = array($item['shop_name'], $item['addr'], $this->getImage($item['photo']), __HOST__ . '/wap/shop/detail/shop_id/' . $item['shop_id'] . '.html');
            }
            $this->weixin->response($content, 'news');
        } else {
            $this->weixin->response('很抱歉没有合适的商家推荐给您', 'text');
        }
    }
	
	 private function keyword($data){
        if (empty($data['Content'])) {
            return;
        }
        if ($this->shop_id == 0) {
            $key = explode(' ', $data['Content']);
            $keyword = D('Weixinkeyword')->checkKeyword($key[0]);
            if ($keyword) {
			 switch ($keyword['type']) {
                    case 'text':
                        $this->weixin->response($keyword['contents'], 'text');
                        break;
                    case 'news':
                        $content = array();
                        $content[] = array(
                            $keyword['title'],
                            $keyword['contents'],
                            $this->getImage($keyword['photo']),
                            $keyword['url'],
                        );
						//file_put_contents('keyword.txt', var_export($content, true));
                        $this->weixin->response($content, 'news');
                        break;
                }
            } else {
                $this->event();
            }
        }else{
           $keyword = D('Shopweixinkeyword')->checkKeyword($this->shop_id, $data['Content']);
            if ($keyword) {
                switch ($keyword['type']) {
                    case 'text':
                        $this->weixin->response($keyword['contents'], 'text');
                        break;
                    case 'news':
                        $content = array();
                        $content[] = array(
                            $keyword['title'],
                            $keyword['contents'],
                            $this->getImage($keyword['photo']),
                            $keyword['url'],
                        );
                        $this->weixin->response($content, 'news');
                        break;
                }
            } else {
                $this->event();
            }
        }
    }
	
	
	

	
	//自动发送账户
	public function PostMeg($condata){
		 $token = $this->getAccessToken($this->_CONFIG['weixin']["appid"], $this->_CONFIG['weixin']["appsecret"]);
		 $Contentbody = '恭喜【'.$condata['nickname'].'】成为'.$condata['uid']."位会员！\r\n会员昵称：".$condata['nickname']."\r\n会员ID：".$condata['uid'].'\r\n关注时间：'.date('Y-m-d H:i:s',time());
		  $post_msg = '{
		   "touser":"'.$condata[openid].'",
		   "msgtype":"text",
		   "text":
			   {
				   "content":"'.$Contentbody.'"
			   }
		   }';	 
				 
		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$token;
		$ret_json = $this->curl_grab_page($url, $post_msg);
	}
	//curl提交
	public function curl_grab_page($url,$data,$proxy='',$proxystatus='',$ref_url='') {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($proxystatus == 'true') {
			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
			curl_setopt($ch, CURLOPT_PROXY, $proxy);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_URL, $url);
		if(!empty($ref_url)){
			curl_setopt($ch, CURLOPT_HEADER, TRUE);
			curl_setopt($ch, CURLOPT_REFERER, $ref_url);
		}
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		ob_start();
		return curl_exec ($ch);
		ob_end_clean();
		curl_close ($ch);
		unset($ch);
	}
	
	
	//获取全局授权
	public function getAccessToken($appId,$appSecret) {
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$appSecret";
		$res = json_decode($this->httpGet($url));
		return $res->access_token;

    }
	//请求
   public function httpGet($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 500);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, $url);
		$res = curl_exec($curl);
		curl_close($curl);
		return $res;
	  }
  
    //微信关注自动注册为用户  fid 为推荐人id
    private function wxAutoadd($data,$fid=''){  
    	$data['type'] = 'weixin';
		$data['open_id'] = $data['openid'];
		$data['create_time'] = NOW_TIME;
		
        $Connect = D('Connect')->getConnectByOpenid('weixin', $data['openid']);//判断是否注册
		//file_put_contents('tuio.txt', var_export($data, true));
		
        if(empty($Connect)){
            $Connect = $data;
            $Connect['connect_id'] = D('Connect')->add($data);
        } 
		
        if(empty($Connect['uid'])) {
            session('connect', $Connect['connect_id']);
      
            if($data['nickname'] == ''){
                $nickname = $data['type'] . $Connect['connect_id'];
            }else{
                $nickname = $data['nickname'];
            }
			
            $arr = array(
				'account' => $data['type'].'_event_' . $Connect['connect_id'], 
				'password' => rand(10000000, 999999999), 
				'nickname' => $nickname, 
				'ext0' => $data['type'].'_event' . $Connect['connect_id'], 
				'face' => $data['headimgurl'], 
				'token' => $data['token'], 
				'reg_time' => NOW_TIME, 
				'reg_ip' => get_client_ip()
			);
           
            $uid = D('Passport')->register($arr,$fid,1); //注册用户资料，返回用户ID
			
			//file_put_contents('uid.txt', var_export($uid, true));
			
            $token = D('Passport')->getToken(); //注册第三方接口，暂时废弃
			
			//file_put_contents('token.txt', var_export($token, true));
			
            D('Connect')->save(array('connect_id' => $Connect['connect_id'], 'uid' =>$uid));
			
			
            //发送帐号逻辑
			$Connect['uid'] = $uid;
			$this->PostMeg($Connect); 
			//file_put_contents('Connect.txt', var_export($Connect, true));
        }else{
			//再次关注逻辑没写
        }
        
    }
	
    //响应用户的事件
    private function event(){
        if ($this->shop_id == 0) {
			//相应用户事件开始
			if($this->_CONFIG['weixin']['user_auto'] == 1 && $this->_CONFIG['weixin']['user_add'] == 1){
				$data = $this->weixin->request();
				$token = $this->getAccessToken($this->_CONFIG['weixin']["appid"], $this->_CONFIG['weixin']["appsecret"]);				  
				$result = D('Connect')->wx_user_autoinfo($data['FromUserName'],$token);					  
				$this->wxAutoadd($result); //自动发送账户并入库
			}
			
			if($this->_CONFIG['weixin']['type'] == 1) {
				$this->weixin->response($this->_CONFIG['weixin']['description'], 'text');//发送文字回复
			}else{
				$content[] = array(
					$this->_CONFIG['weixin']['title'], 
					$this->_CONFIG['weixin']['description'], 
					$this->getImage($this->_CONFIG['weixin']['photo']), 
					$this->_CONFIG['weixin']['linkurl']
				);
				file_put_contents('event_shop.txt', var_export($content, true));
				$this->weixin->response($content, 'news');//发送图文回复
			}
			//相应用户事件结束
        }else{
            $data['get'] = $_GET;
            $data['post'] = $_POST;
            $data['data'] = $this->weixin->request();
            $weixin_msg = unserialize($this->shopdetails['weixin_msg']);
            if ($weixin_msg['type'] == 1) {
                $this->weixin->response($weixin_msg['description'], 'text');
            } else {
                $content[] = array(
					$weixin_msg['title'], 
					$weixin_msg['description'], 
					$this->getImage($weixin_msg['photo']), 
					$this->_CONFIG['weixin']['linkurl']
				);
                $this->weixin->response($content, 'news');//发送商家图片文字简介
            }
        }
    }
	
	//扫码相应事件
    private function events(){
        $data['get'] = $_GET;
        $data['post'] = $_POST;
        $data['data'] = $this->weixin->request();
        if (!empty($data['data'])) {
            $datas = explode('_', $data['data']['EventKey']);
            $id = $datas[1];
			
			//优先查询用户分享关注下级
			$uDate = D('Users')->find($id);
			if($uDate){
 				$token = $this->getAccessToken($this->_CONFIG['weixin']["appid"], $this->_CONFIG['weixin']["appsecret"]);	
				$result = D('Connect')->wx_user_autoinfo($data['data']['FromUserName'],$token);					  
				$this->wxAutoadd($result,$id); //自动入库
				$this->weixin->response($this->_CONFIG['weixin']['description'], 'text');//发送文字回复
			}else {		
			//二开结束
				if (!($detail = D('Weixinqrcode')->find($id))) {
					die;
				}
				$type = $detail['type'];
				if ($type == 1) {
					$shop_id = $detail['soure_id'];
					$shop = D('Shop')->find($shop_id);
					$content[] = array(
						$shop['shop_name'], 
						$shop['addr'], 
						$this->getImage($shop['photo']), __HOST__ . '/shop/detail/shop_id/' . $shop_id . '.html');
						
						file_put_contents('events_shop.txt', var_export($$content, true));
					$result = D('Connect')->getConnectByOpenid('weixin', $data['data']['FromUserName']);
					if (!empty($result)) {
						$user_id = $result['uid'];
					
						if (!($fans = D('Shopfavorites')->where(array('user_id' => $user_id, 'shop_id' => $shop_id))->find())) {
							$dataf = array('user_id' => $user_id, 'shop_id' => $shop_id, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
							D('Shopfavorites')->add($dataf);
							D('Shop')->updateCount($shop_id, 'fans_num');
						} else {
							if ($fans['closed'] == 1) {
								D('Shopfavorites')->save(array('favorites_id' => $fans['favorites_id'], 'closed' => 0));
							}
						}
					}
					$this->weixin->response($content, 'news');
				} elseif ($type == 2) {
					$tuan_id = $detail['soure_id'];
					$tuan = D('Tuan')->find($tuan_id);
					$content[] = array($tuan['title'], $tuan['intro'], $this->getImage($tuan['photo']), __HOST__ . '/wap/tuan/detail/tuan_id/' . $tuan_id . '.html');
					$result = D('Connect')->getConnectByOpenid('weixin', $data['data']['FromUserName']);
					if (!empty($result)) {
						$user_id = $result['uid'];
					
						if (!($fans = D('Shopfavorites')->where(array('user_id' => $user_id, 'shop_id' => $tuan['shop_id']))->find())) {
							$dataf = array('user_id' => $user_id, 'shop_id' => $tuan['shop_id'], 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
							D('Shopfavorites')->add($dataf);
							D('Shop')->updateCount($tuan['shop_id'], 'fans_num');
						} else {
							if ($fans['closed'] == 1) {
								D('Shopfavorites')->save(array('favorites_id' => $fans['favorites_id'], 'closed' => 0));
							}
						}
					}
					$this->weixin->response($content, 'news');
				} elseif ($type == 3) {
					//购物
					$goods_id = $detail['soure_id'];
					$goods = D('Goods')->find($goods_id);
					$shops = D('Shop')->find($goods['shop_id']);
					$content[] = array($goods['title'], $shops['shop_name'], $this->getImage($goods['photo']), __HOST__ . '/wap/mall/detail/goods_id/' . $goods_id . '.html');
					$result = D('Connect')->getConnectByOpenid('weixin', $data['data']['FromUserName']);
					if (!empty($result)) {
						$user_id = $result['uid'];
						
						if (!($fans = D('Shopfavorites')->where(array('user_id' => $user_id, 'shop_id' => $goods['shop_id']))->find())) {
							$dataf = array('user_id' => $user_id, 'shop_id' => $goods['shop_id'], 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
							D('Shopfavorites')->add($dataf);
							D('Shop')->updateCount($goods['shop_id'], 'fans_num');
						} else {
							if ($fans['closed'] == 1) {
								D('Shopfavorites')->save(array('favorites_id' => $fans['favorites_id'], 'closed' => 0));
							}
						}
					}
					$this->weixin->response($content, 'news');
				}
			}
		}
    }
    public function scan(){
        $data['data'] = $this->weixin->request();
        if (!empty($data['data'])) {
            $id = $data['data']['EventKey'];
            if (!($detail = D('Weixinqrcode')->find($id))) {
                die;
            }
            $type = $detail['type'];
            if ($type == 1) {
                $shop_id = $detail['soure_id'];
                $shop = D('Shop')->find($shop_id);
                $content[] = array($shop['shop_name'], $shop['addr'], $this->getImage($shop['photo']), __HOST__ . '/wap/shop/detail/shop_id/' . $shop_id . '.html');
                $result = D('Connect')->getConnectByOpenid('weixin', $data['data']['FromUserName']);
                if (!empty($result)) {
                    $user_id = $result['uid'];
                
                    if (!($fans = D('Shopfavorites')->where(array('user_id' => $user_id, 'shop_id' => $shop_id))->find())) {
                        $dataf = array('user_id' => $user_id, 'shop_id' => $shop_id, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
                        D('Shopfavorites')->add($dataf);
                        D('Shop')->updateCount($shop_id, 'fans_num');
                    } else {
                        if ($fans['closed'] == 1) {
                            D('Shopfavorites')->save(array('favorites_id' => $fans['favorites_id'], 'closed' => 0));
                        }
                    }
                }
                $this->weixin->response($content, 'news');
            } elseif ($type == 2) {
                //抢购
                $tuan_id = $detail['soure_id'];
                $tuan = D('Tuan')->find($tuan_id);
                $content[] = array($tuan['title'], $tuan['intro'], $this->getImage($tuan['photo']), __HOST__ . '/wap/tuan/detail/tuan_id/' . $tuan_id . '.html');
                $result = D('Connect')->getConnectByOpenid('weixin', $data['data']['FromUserName']);
                if (!empty($result)) {
                    $user_id = $result['uid'];
                    if (!($fans = D('Shopfavorites')->where(array('user_id' => $user_id, 'shop_id' => $tuan['shop_id']))->find())) {
                        $dataf = array('user_id' => $user_id, 'shop_id' => $tuan['shop_id'], 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
                        D('Shopfavorites')->add($dataf);
                        D('Shop')->updateCount($tuan['shop_id'], 'fans_num');
                    } else {
                        if ($fans['closed'] == 1) {
                            D('Shopfavorites')->save(array('favorites_id' => $fans['favorites_id'], 'closed' => 0));
                        }
                    }
                }
                $this->weixin->response($content, 'news');
            } elseif ($type == 3) {
                //购物
                $goods_id = $detail['soure_id'];
                $goods = D('Goods')->find($goods_id);
                $shops = D('Shop')->find($goods['shop_id']);
                $content[] = array($goods['title'], $shops['shop_name'], $this->getImage($goods['photo']), __HOST__ . '/wap/mall/detail/goods_id/' . $goods_id . '.html');
                $result = D('Connect')->getConnectByOpenid('weixin', $data['data']['FromUserName']);
                if (!empty($result)) {
                    $user_id = $result['uid'];
                    if (!($fans = D('Shopfavorites')->where(array('user_id' => $user_id, 'shop_id' => $goods['shop_id']))->find())) {
                        $dataf = array('user_id' => $user_id, 'shop_id' => $goods['shop_id'], 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
                        D('Shopfavorites')->add($dataf);
                        D('Shop')->updateCount($goods['shop_id'], 'fans_num');
                    } else {
                        if ($fans['closed'] == 1) {
                            D('Shopfavorites')->save(array('favorites_id' => $fans['favorites_id'], 'closed' => 0));
                        }
                    }
                }
                $this->weixin->response($content, 'news');
            }
        }
    }
    private function getImage($img){
		return config_weixin_img($img);
    }
}