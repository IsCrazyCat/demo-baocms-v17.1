<?php


class WeixinModel {

    /**
     * 微信推送过来的数据或响应数据
     * @var array
     */
    private $data = array();
    private $token = 'weixintoken'; 
    private $access_token = '';
    private $config = array();
    private $curl = null;

    /**
     * 构造方法，用于实例化微信SDK
     * @param string $token 微信开放平台设置的TOKEN
     */
    public function __construct() {
        import("@/Net.Curl");
        $this->curl = new Curl();
    }
    
    public function mass($data,$shop_id = 0){
        $token = $this->getToken($shop_id);
        $url = 'http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token='.$token.'&type=thumb';
        $result = $this->curl->post($url,array('media'=>'@' . realpath (realpath(__ROOT__).config_img($data['photo']))));
        $result = json_decode($result,true);
        if($result['errcode']){
             return  $result['errcode'].$result['errmsg'];
        }
        $msg['articles']= array(
            array(
                'thumb_media_id'     => $result['thumb_media_id'],
                'author'            => $_SERVER['HTTP_HOST'],
                'title'             => urlencode($data['title']),  
                'content_source_url'=> $data['url'],
                'content'           => urlencode($data['contents']),
                'show_cover_pic'    => 1,
            ),
        );
		$msg= urldecode(json_encode($msg));
        $result2 = $this->curl->post('https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token='.$token,$msg);
		$result2 = json_decode($result2,true);
        if($result2['errcode']){
             return  $result2['errcode'].$result2['errmsg'];
        }else{
			$datas = array();
			$datas['media_id'] = $result2['media_id'];
			$datas['thumb_media_id'] = $result2['media_id'];
			$datas['author'] = $_SERVER['HTTP_HOST'];
			$datas['title'] = $data['title'];
			$datas['content_source_url'] = $data['url'];
			$datas['content'] = $data['contents'];
			$datas['show_cover_pic'] =1;
			$datas['create_time'] =$result2['created_at'];
			if(D('WeixinMass')->add($datas)){
				return true;
			}
			return '素材添加成功，可是写入数据库失败';
		}
        return true;
    }
    
  
    //判断是否关注公众号接口
	public function subscribe($uid){
		$this->config = D('Setting')->fetchAll();
		
	
		if($this->config['weixin']['is_subscribe']){
			
			if($Connect = D('Connect')->where(array('uid'=>$uid,'type'=>'weixin'))->find()){
				$token = $this->getSiteToken();
				$info_url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $token . '&openid='.$Connect['open_id'];
				$info = $this->curl->get($info_url);
				$info = json_decode($info, true);
				
				if($info['subscribe'] == 1){
					return 1;
				}else{
					return 2;
				}
			}else{
				return 3; 
			}
			
	    }else{
			return 1; 
		}
		return 1; 
	}
	

    public function tmplmesg($data,$msg_id){
        $site_token = $this->getSiteToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$site_token}";
        $result = $this->curl->post($url, json_encode($data) );
        $result = (array)json_decode($result);
        if($result['errcode']){
			D('Weixinmsg')->where(array('msg_id' => $msg_id))->save(array('status' => $result['errcode'],'info'=>$result['errmsg']));
            return true;//忽略报错
        }else{
			D('Weixinmsg')->where(array('msg_id' => $msg_id))->save(array('status' => $result['errcode'],'info'=>$result['errmsg']));
			return true;
		}
        return true;
    }
    /*
     * 账号后台模板ID 
     * @param  string $short_id 模板库模板ID
     * @return string 账号后台模板ID
     */
    public function getTmplId($short_id){
        $site_token = $this->getSiteToken();
        $url = "https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token={$site_token}";
        $result = $this->curl->post($url, json_encode(array('template_id_short'=>$shop_id)));
        $result = (array)json_decode($result);
        if($result['errcode']){
            return false;
        }
        return $result['template_id'];
    }

    public function getToken($shop_id=0) {
        
        if(!$shop_id) return  $this->getSiteToken();
        return $this->getShopToken($shop_id);
    }
	
    //获取商家的TOKEN
    private function getShopToken($shop_id){ 
        if(!$data = D('Shopweixinaccess')->getToken($shop_id)){
            $details = D('Shopdetails')->find($shop_id);
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' .$details['app_id'] . '&secret=' .$details['app_key'];
            $result = $this->curl->get($url);
            $result = json_decode($result, true);
            if(!empty($result['errcode'])){
				return false;
			}else{
				$data = $result['access_token'];
            	D('Shopweixinaccess')->setToken($shop_id, $data);
			}
        }
        return $data;
    }

	

	public function admin_wechat_client($shop_id=0){
        static $clients = array();
		if($weixin_admin = D('Shopdetails')->find($shop_id)){
			include_once "Tudou/Lib/Action/Weixin/wechat.class.php";
			$client = new WechatClient($weixin_admin['app_id'], $weixin_admin['app_key']);
		}
        return $client;
    }
    
    
	
	
	//获取主站的TOKEN
	public function getSiteToken(){
		$this->config = D('Setting')->fetchAll();
		$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' .$this->config['weixin']['appid'] . '&secret=' .$this->config['weixin']['appsecret'];
		$data = json_decode(file_get_contents(BASE_PATH."/access_token2.json"));
		//file_put_contents('data.txt', var_export($data, true));
		if($data->expire_time < time()) {
		    $result = $this->curl->get($url);
            $result = json_decode($result, true);
			//file_put_contents('result.txt', var_export($result, true));
			if(!empty($result['errcode'])){
				return false;
			}else{
				$data->expire_time = time() + 7200;
				$data->access_token = $result['access_token'];
				$fp = fopen(BASE_PATH."/access_token.json2", "w");
				fwrite($fp, json_encode($data));
				fclose($fp);
				//file_put_contents('result_access_token.txt', var_export($result['access_token'], true));
				return $result['access_token'];
			}
		}
		//file_put_contents('data_access_token.txt', var_export($data->access_token, true));
		return $data->access_token;
    }
	
	
	
    
    public function getCode($soure_id,$type){ //生成二维码
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $this->getSiteToken();
        $str = "";
        $detail = D('Weixinqrcode')->where(array('soure_id'=>$soure_id,'type'=>$type))->find();
        if(!empty($detail)){
            $str = $detail['id'];
        }else{
            $id = D('Weixinqrcode')->add(array('soure_id'=>$soure_id,'type'=>$type));
            $str = $id;
        }
        
        $data = array(
            'action_name' => 'QR_LIMIT_SCENE',
            'action_info' =>array(
                'scene' => array(
                    'scene_id' => $str,
                ),
            ),
        );
        $datastr = json_encode($data);
        $result = $this->curl->post($url, $datastr);
        $result = json_decode($result, true);
        
        if ($result['errcode']) {
            return false;
        }
        $ticket = urlencode($result['ticket']);
        $imgurl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=". $ticket;
        return $imgurl;
    }
    
     //自定义菜单接口
    public function weixinmenu($data,$shop_id = 0 ) {
		//p($data);die;
        $datas = array();
        foreach ($data['button'] as $key => $val) {
            if (!empty($val)) {
                $local = array(
					'type' => 'view',
                    'name' => urlencode($val['name']),
					'url' => $val['url'],
                );
                foreach ($data['child'][$key] as $k => $v) {
                    if (!empty($v['name'])) {
                        $local['sub_button'][] = array(
                            'type' => 'view',
                            'name' => urlencode($v['name']),
                            'url' => $v['url'],
                        );
                    }
                }
                $datas[] = $local;
            }
        }


        $datastr = urldecode(json_encode(array('button'=>$datas)));
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->getToken($shop_id);
        $result = $this->curl->post($url, $datastr);
        $result = json_decode($result, true);
        if ($result['errcode'] != 0) {
            return $result['errcode'].'错误原因：'.$result['errmsg'];
        }
        return true;
    }


    //此TOKEN 是由网站分配
    public function init($token) {

        if (!empty($_GET['echostr'])) {
            exit($_GET['echostr']);
        } else {
            $xml = file_get_contents("php://input");
            if (!empty($xml)) {
                $xml = new SimpleXMLElement($xml);

                $xml || exit;

                foreach ($xml as $key => $value) {
                    $this->data[$key] = strval($value);
                }
            }
        }
    }

    /**
     * 获取微信推送的数据
     * @return array 转换为数组后的数据
     */
    public function request() {
        return $this->data;
    }

    /**
     * * 响应微信发送的信息（自动回复）
     * @param  string $to      接收用户名
     * @param  string $from    发送者用户名
     * @param  array  $content 回复信息，文本信息为string类型
     * @param  string $type    消息类型
     * @param  string $flag    是否新标刚接受到的信息
     * @return string          XML字符串
     */
    public function response($content, $type = 'text', $flag = 0) {
        /* 基础数据 */
        $this->data = array(
            'ToUserName' => $this->data['FromUserName'],
            'FromUserName' => $this->data['ToUserName'],
            'CreateTime' => NOW_TIME,
            'MsgType' => $type,
        );

        /* 添加类型数据 */
        $this->$type($content);

        /* 添加状态 */
        $this->data['FuncFlag'] = $flag;
        /* 转换数据为XML */
        $xml = new SimpleXMLElement('<xml></xml>');
        $this->data2xml($xml, $this->data);
        exit($xml->asXML());
    }

    /**
     * 回复文本信息
     * @param  string $content 要回复的信息
     */
    private function text($content) {
        $this->data['Content'] = $content;
    }

    /**
     * 回复音乐信息
     * @param  string $content 要回复的音乐
     */
    private function music($music) {
        list(
                $music['Title'],
                $music['Description'],
                $music['MusicUrl'],
                $music['HQMusicUrl']
                ) = $music;
        $this->data['Music'] = $music;
    }

    /**
     * 回复图文信息
     * @param  string $news 要回复的图文内容
     */
    private function news($news) {
        $articles = array();

        foreach ($news as $key => $value) {
            list(
                    $articles[$key]['Title'],
                    $articles[$key]['Description'],
                    $articles[$key]['PicUrl'],
                    $articles[$key]['Url']
                    ) = $value;
            if ($key >= 9) {
                break;
            } //最多只允许10调新闻
        }
        $this->data['ArticleCount'] = count($articles);
        $this->data['Articles'] = $articles;
    }

    /**
     * 数据XML编码
     * @param  object $xml  XML对象
     * @param  mixed  $data 数据
     * @param  string $item 数字索引时的节点名称
     * @return string
     */
    private function data2xml($xml, $data, $item = 'item') {
        foreach ($data as $key => $value) {
            /* 指定默认的数字key */
            is_numeric($key) && $key = $item;

            /* 添加子元素 */
            if (is_array($value) || is_object($value)) {
                $child = $xml->addChild($key);
                $this->data2xml($child, $value, $item);
            } else {
                if (is_numeric($value)) {
                    $child = $xml->addChild($key, $value);
                } else {
                    $child = $xml->addChild($key);
                    $node = dom_import_simplexml($child);
                    $node->appendChild($node->ownerDocument->createCDATASection($value));
                }
            }
        }
    }

    /**
     * 对数据进行签名认证，确保是微信发送的数据
     * @param  string $token 微信开放平台设置的TOKEN
     * @return boolean       true-签名正确，false-签名错误
     */
    private function auth($token) {
        /* 获取数据 */
        $data = array($_GET['timestamp'], $_GET['nonce'], $token);
        $sign = $_GET['signature'];
        /* 对数据进行字典排序 */
        sort($data);
        /* 生成签名 */
        $signature = sha1(implode($data));
        return $signature === $sign;
    }


}