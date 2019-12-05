<?php
class CommonAction extends Action{
    protected $uid = 0;
    protected $member = array();
    protected $_CONFIG = array();
    protected $citys = array();
    protected $city_id = 0;
    protected $city = array();
    protected function _initialize(){
		
		$this->_CONFIG = D('Setting')->fetchAll();
		if($this->_CONFIG['site']['https'] == 1){
			define('__HOST__', 'https://' . $_SERVER['HTTP_HOST']);
		}else{
			define('__HOST__', 'http://' . $_SERVER['HTTP_HOST']);
		}
		
        define('IN_MOBILE', TRUE);
		define('TU_DOMAIN', $this->_CONFIG['site']['hostdo']);
		define('IS_MOBILE', is_mobile());
		define('TU_HOST_PREFIX', $this->_CONFIG['site']['host_prefix']);
		
        $is_weixin = is_weixin();
        $is_weixin = !$is_weixin ? FALSE : TRUE;
        define('IS_WEIXIN', $is_weixin);
        searchwordfrom();
		
        $this->uid = (int) getuid();
        if (empty($this->uid)) {
            header("Location: " . U("wap/passport/login"));
            exit;
        }
        $this->member = D('Users')->find($this->uid);
		
        if (empty($this->member)) {
            setuid(0);
            header("Location: " . U("wap/passport/login"));
            exit;
        }
        $this->ex = D('Usersex')->find($this->uid);
		
		
        
		
		
		$http = ($this->_CONFIG['site']['https'] == 1) ? 'https' : 'http';
		if(IS_MOBILE){
			if($this->_CONFIG['site']['https'] == 1){
			   if($_SERVER['HTTPS'] == ''){
				   header('Location: https://'.TU_HOST_PREFIX.'.'.TU_DOMAIN.$_SERVER['REQUEST_URI']);
				   die;
			   }
			}else{
				if($_SERVER['HTTP_HOST'] == TU_DOMAIN || $_SERVER['HTTP_HOST'] != TU_HOST_PREFIX .'.'. TU_DOMAIN){
				   header('Location: http://'.TU_HOST_PREFIX.'.'.TU_DOMAIN.$_SERVER['REQUEST_URI']);
				   die;
			   }
			}
		}
		
		
		
        $this->citys = D('City')->fetchAll();
        $this->city_id = cookie('city_id');
        if (empty($this->city_id)) {
            import('ORG/Net/IpLocation');'UTFWry.dat';
            $IpLocation = new IpLocation();
            $result = $IpLocation->getlocation($_SERVER['REMOTE_ADDR']);
            foreach ($this->citys as $val) {
                if (!strstr($result['country'], $val['name'])) {
                    continue;
                }
                $city = $val;
                $this->city_id = $val['city_id'];
                break;
            }
            if (empty($city)) {
                $this->city_id = $this->_CONFIG['site']['city_id'];
                $city = $this->citys[$this->_CONFIG['site']['city_id']];
            }
        } else {
            $city = $this->citys[$this->city_id];
        }
        $this->assign('city', $city);
        $this->assign('citys', $this->citys);
        $this->assign('city_id', $this->city_id);
		

        $this->assign('CONFIG', $this->_CONFIG);
        $this->assign('MEMBER', $this->member);
        $this->assign('MEMBER_EX', $this->ex);
		$this->assign('ranks', D('Userrank')->fetchAll());
        $this->assign('today', TODAY);
        $this->assign('nowtime', NOW_TIME);
        $this->assign('ctl', strtolower(MODULE_NAME));
        $this->assign('act', ACTION_NAME);
        $this->assign('is_weixin', IS_WEIXIN);
		$this->assign('check_connect_uid', $check_connect_uid = $this->check_connect_uid($this->uid));
		$this->assign('color', $color = $this->_CONFIG['other']['color']);
		
		$this->assign('is_subscribe', $is_subscribe = D('Weixin')->subscribe($this->uid));//判断是否关注公众号
		
        //查询用户通知
        $bg_time = strtotime(TODAY);
        $this->assign('msg_day', $counts['msg_day'] = (int) D('Msg')->where(array('cate_id' =>1,'closed' => 0,'views' => 0,'user_id' =>$this->uid))->count());
		$this->assign('push', $counts['push'] = (int) D('Push')->where(array('category' =>1,'type'=>3))->count());
		
        $this->assign('sign_day', $sign_day = D('Usersign')->where(array('user_id' => $this->uid, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->select());
        //查询通知结束
		
		
		import("@/Net.Jssdk");
        $jssdk = new JSSDK($this->_CONFIG['weixin']["appid"],$this->_CONFIG['weixin']["appsecret"]);
        $signPackage = $jssdk->GetSignPackage();
        $this->signPackage = $signPackage;
		
		$this->assign('lat',$lat = cookie('lat'));
		$this->assign('lng',$lat = cookie('lng'));
		
        if(!$this->_CONFIG['site']['web_close']){
            $this->display('public:web_close');die;
        }
		
		
    }
	//检测用户有没有绑定微信
    private function check_connect_uid($uid){
        $connect = D('Connect')->where(array('uid' => $uid,'type'=>'weixin'))->find();
		if(!empty($connect)){
			return $connect;
		}else{
			return false;
		}
    }

    private function seo(){
        $seo = D('Seo')->fetchAll();
        $this->assign('seo_title', $this->_CONFIG['site']['title']);
        $this->assign('seo_keywords', $this->_CONFIG['site']['keyword']);
        $this->assign('seo_description', $this->_CONFIG['site']['description']);
    }
    private function tmplToStr($str, $datas){
        preg_match_all('/{(.*?)}/', $str, $arr);
        foreach ($arr[1] as $k => $val) {
            $v = isset($datas[$val]) ? $datas[$val] : '';
            $str = str_replace($arr[0][$k], $v, $str);
        }
        return $str;
    }
    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = ''){
        $this->seo();
        parent::display($this->parseTemplate($templateFile), $charset, $contentType, $content = '', $prefix = '');
    }
    private function parseTemplate($template = ''){
        $depr = C('TMPL_FILE_DEPR');
        $template = str_replace(':', $depr, $template);
        $theme = $this->getTemplateTheme();
        define('NOW_PATH', BASE_PATH . '/themes/' . $theme . 'User/');
        define('THEME_PATH', BASE_PATH . '/themes/default/User/');
        define('APP_TMPL_PATH', __ROOT__ . '/themes/default/User/');
        if ('' == $template) {
            $template = strtolower(MODULE_NAME) . $depr . strtolower(ACTION_NAME);
        } elseif (false === strpos($template, '/')) {
            $template = strtolower(MODULE_NAME) . $depr . strtolower($template);
        }
        $file = NOW_PATH . $template . C('TMPL_TEMPLATE_SUFFIX');
        if (file_exists($file)) {
            return $file;
        }
        return THEME_PATH . $template . C('TMPL_TEMPLATE_SUFFIX');
    }
    private function getTemplateTheme(){
        define('THEME_NAME', 'default');
        if ($this->theme) {
            $theme = $this->theme;
        } else {
            $theme = D('Template')->getDefaultTheme();
            if (C('TMPL_DETECT_THEME')) {
                $t = C('VAR_TEMPLATE');
                if (isset($_GET[$t])) {
                    $theme = $_GET[$t];
                } elseif (cookie('think_template')) {
                    $theme = cookie('think_template');
                }
                if (!in_array($theme, explode(',', C('THEME_LIST')))) {
                    $theme = C('DEFAULT_THEME');
                }
                cookie('think_template', $theme, 864000);
            }
        }
        return $theme ? $theme . '/' : '';
    }
   
    protected function tuMsg($message, $jumpUrl = '', $time = 3000){
        $str = '<script>';
        $str .= 'parent.boxmsg("' . $message . '","' . $jumpUrl . '","' . $time . '");';
        $str .= '</script>';
        exit($str);
    }
   
    protected function ajaxLogin(){
        $str = '<script>';
        $str .= 'parent.ajaxLogin();';
        $str .= '</script>';
        exit($str);
    }
    protected function checkFields($data = array(), $fields = array()){
        foreach ($data as $k => $val) {
            if (!in_array($k, $fields)) {
                unset($data[$k]);
            }
        }
        return $data;
    }
    protected function ipToArea($_ip){
        return IpToArea($_ip);
    }
}