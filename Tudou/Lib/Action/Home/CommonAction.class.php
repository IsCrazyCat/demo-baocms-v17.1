<?php
class CommonAction extends Action{
	private $datas = 'catedatas'; 
    protected $uid = 0;
    protected $member = array();
    protected $_CONFIG = array();
    protected $seodatas = array();
    protected $shopcates = array();
    protected $citys = array();
    protected $template_setting = array();
    protected $city_id = 0;//城市ID
    protected $city = array();
    protected $city_host = '';
    protected $_admin = array();
    protected function _initialize(){
        define('__HOST__', 'http://' . $_SERVER['HTTP_HOST']);
        preg_match('#http://(.*?)\\.#i', __HOST__, $match);
        $nowhost = $match[1];
		$this->_NOWHOST = $nowhost;//方便调用
        $this->_CONFIG = D('Setting')->fetchAll();
        define('TU_DOMAIN', $this->_CONFIG['site']['hostdo']);
		define('IS_MOBILE', is_mobile());
		define('TU_HOST_PREFIX',$this->_CONFIG['site']['host_prefix']);
		$this->_TU_DOMAIN = TU_DOMAIN;//方便调用
        //设置根域名
        $this->citys = D('City')->fetchAll();
        foreach ($this->citys as $val) {
            if ($val['pinyin'] == $nowhost) {
                if ($val['domain'] == 1) {
                    cookie('city_id', $val['city_id'], 86400 * 30);
                }
            }
        }
		
        $this->assign('citys', $this->citys);
        $this->assign('hostdo', $this->_CONFIG['site']['hostdo']);
        $this->city_id = cookie('city_id');
        $this->city_host = $this->citys[$this->city_id]['pinyin'];
        $ks = $this->citys[$this->city_id];
		
		$http = ($this->_CONFIG['site']['https'] == 1) ? 'https' : 'http';
		
		

	
        if(__ACTION__ == '/payment/payment' || __ACTION__ == '/passport/login' || __ACTION__ == '/passport/register' || __ACTION__ == '/passport/sendsms'|| __ACTION__ == '/payment/check' || __ACTION__ == '/payment/check_pay_password' || __ACTION__ == '/payment/set_pay_password') {
            if($_SERVER['HTTP_HOST'] == TU_DOMAIN || $_SERVER['HTTP_HOST'] != TU_HOST_PREFIX.'.' . TU_DOMAIN){
                header('Location: '.$http.'://'.TU_HOST_PREFIX.'.' . TU_DOMAIN . $_SERVER['REQUEST_URI']);
                die;
            }
        }else {
	
			if($_SERVER['HTTP_HOST'] == TU_DOMAIN || $_SERVER['HTTP_HOST'] == TU_HOST_PREFIX.'.' . TU_DOMAIN){
			   if(empty($this->city_id)){
					import('ORG/Net/IpLocation');
					$IpLocation = new IpLocation('UTFWry.dat');
					$result = $IpLocation->getlocation($_SERVER['REMOTE_ADDR']);
					foreach($this->citys as $val) {
						 if (strstr($result['country'], $val['name'])) {
							$city = $val;
							$this->city_id = $val['city_id'];
							$this->city_host = $val['pinyin'];
							break;
							}
					}
					if(empty($city)){
						$this->city_id = $this->_CONFIG['site']['city_id'];
						$city = $this->citys[$this->_CONFIG['site']['city_id']];
						$this->city_host = $this->citys[$this->city_id]['pinyin'];
					}
					if(!empty($city)){
						$ks = $this->citys[$this->city_id];
						if($ks['domain'] == 1) {
							header('Location: '.$http.'://' . $this->city_host . '.' . TU_DOMAIN . $_SERVER['REQUEST_URI']);
							die;
						}
					}
			   }else{
				   $city = $this->citys[$this->city_id];
				   if($ks['domain'] == 1){
					   header('Location: '.$http.'://' . $this->city_host . '.' . TU_DOMAIN . $_SERVER['REQUEST_URI']);
					   die;
					}
			   }
			
			}else{
				if(TU_HOST_PREFIX != 'www'){
					if(substr($_SERVER['HTTP_HOST'],0,3) == 'www'){
						header('Location: '.$http.'://'.TU_HOST_PREFIX.'.' . TU_DOMAIN . $_SERVER['REQUEST_URI']);
						die;
					}
				}
				$city = $this->citys[$this->city_id];
			}
		}	
        $this->city = $city;
        $this->city_host = $this->city['pinyin'];
        searchwordfrom();
        $this->uid = getuid();
        if (!empty($this->uid)) {
            $this->member = D('Users')->find($this->uid);
        }
		
	
		//分类调用开始
        $this->shopcates = D('Shopcate')->fetchAll();
        $this->assign('shopcates', $this->shopcates);
        $this->Tuancates = D('Tuancate')->fetchAll();
        $this->assign('tuancates', $this->Tuancates);
		$this->assign('educates',$educates = D('Educate')->fetchAll());
		$this->assign('appointcates',$appointcates = D('Appointcate')->fetchAll());
        $this->assign('goodscates', D('Goodscate')->fetchAll());
        $this->assign('lifecates', D('Lifecate')->fetchAll());
        $this->assign('channelmeans', D('Lifecate')->getChannelMeans());
		$this->assign('threadcates', D('Threadcate')->fetchAll());
		
		
		

  
		
		
		//分类调用结束
       
        $this->assign('ctl', strtolower(MODULE_NAME));
        $this->assign('contrl', strtolower(CONTROLLER_NAME));
        $this->assign('act', ACTION_NAME);
        $this->assign('nowtime', NOW_TIME);
        $this->assign('city_name', $city['name']);
        $this->assign('city', $city);
        $this->assign('city_id', $this->city_id);
        $this->getTemplateTheme();
        $this->template_setting = d('Templatesetting')->detail($this->theme);
        $this->assign('CONFIG', $this->_CONFIG);
        $this->assign('MEMBER', $this->member);
        $this->assign('today', TODAY);
        $city_ids = array('0', $this->city_id);
        $city_ids = join(',', $city_ids);
        $this->assign('city_ids', $city_ids);
        $url_jump = $this->_CONFIG['other']['url_jump'];
        if ($url_jump == 1) {
            if (is_mobile()) {
                $url_mobile = $http.'://' . $_SERVER['HTTP_HOST'] . '/wap' . $_SERVER['REQUEST_URI'];
                header('Location:' . $url_mobile);
                die;
            }
        }
        if ($this->_CONFIG['operation']['pchome'] == 0) {
            header('Location:' . U('wap/index/index'));
        }
        $fuid = (int) $this->_get('fuid');
        if (!empty($fuid)) {
            $profit_expire = (int) $this->_CONFIG['profit']['profit_expire'];
            if ($profit_expire) {
                cookie('fuid', $fuid, $profit_expire * 60 * 60);
            } else {
                cookie('fuid', $fuid);
            }
        }
        $citylists = array();
        foreach ($this->citys as $val) {
            if ($val['is_open'] == 1) {
                $a = strtoupper($val['first_letter']);
                $citylists[$a][] = $val;
            }
        }
        ksort($citylists);
        $this->assign('citylists', $citylists);
		

		
		//统计PC导航
		if ($nav_id = (int) $this->_param('nav_id')) {
            D('Navigation')->navigation_click($nav_id);
        }
		
		$this->assign('card', $Usercard = D('Usercard')->where(array('user_id' => $this->uid))->find());

        $mapssss = array('status' => 4, 'closed' => 0);
        $this->assign('navigations', $navigations = D('Navigation')->where($mapssss)->order(array('orderby' => 'asc'))->select());
        $this->assign('msg_day', $counts['msg_day'] = (int) D('Msg')->where(array('cate_id' => 2, 'views' => 0, 'user_id' => $this->uid))->count());
        $this->assign('color', $color = $this->_CONFIG['other']['color']);
        $web_close = $this->_CONFIG['site']['web_close'];
        $web_close_title = $this->_CONFIG['site']['web_close_title'];
        if ($web_close == 0) {
            $this->display('public:web_close');
            die;
        }
		
    }

	
    private function seo(){
        $seo = D('Seo')->fetchAll();
        $this->seodatas['sitename'] = $this->_CONFIG['site']['sitename'];
        $this->seodatas['tel'] = $this->_CONFIG['site']['tel'];
        $this->seodatas['city_name'] = $this->city['name'];
        $key = strtolower(MODULE_NAME . '_' . ACTION_NAME);
        if (isset($seo[$key])) {
            $this->assign('seo_title', $this->tmplToStr($seo[$key]['seo_title'], $this->seodatas));
            $this->assign('seo_keywords', $this->tmplToStr($seo[$key]['seo_keywords'], $this->seodatas));
            $this->assign('seo_description', $this->tmplToStr($seo[$key]['seo_desc'], $this->seodatas));
        } else {
            $this->assign('seo_title', $this->_CONFIG['site']['title']);
            $this->assign('seo_keywords', $this->_CONFIG['site']['keyword']);
            $this->assign('seo_description', $this->_CONFIG['site']['description']);
        }
    }
    private function tmplToStr($str, $datas){
        return tmpltostr($str, $datas);
    }
    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = ''){
        $this->seo();
        parent::display($this->parseTemplate($templateFile), $charset, $contentType, $content = '', $prefix = '');
    }
    private function parseTemplate($template = ''){
        $depr = C('TMPL_FILE_DEPR');
        $template = str_replace(':', $depr, $template);
        $theme = $this->getTemplateTheme();
		
        define('NOW_PATH', BASE_PATH . '/themes/' . $theme . 'Home/');
        define('THEME_PATH', BASE_PATH . '/themes/default/Home/');
        define('APP_TMPL_PATH', __ROOT__ . '/themes/default/Home/');
        if ('' == $template) {
            $template = strtolower(MODULE_NAME) . $depr . strtolower(ACTION_NAME);
        } else {
            if (false === strpos($template, '/')) {
                $template = strtolower(MODULE_NAME) . $depr . strtolower($template);
            }
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
            $default = D('Template')->getDefaultTheme();
            $themes = D('Template')->fetchAll();
            if (C('TMPL_DETECT_THEME')) {
                $t = C('VAR_TEMPLATE');
                if (isset($_GET[$t])) {
                    $theme = $_GET[$t];
                    cookie('think_template', $theme, 864000);
                } else {
                    if (!empty($this->city['theme'])) {
                        $theme = $this->city['theme'];
                    } else {
                        if (cookie('think_template')) {
                            $theme = cookie('think_template');
                        }
                    }
                }
                if (!isset($themes[$theme])) {
                    $theme = $default;
                }
            } else {
                $theme = $default;
            }
        }
        return $theme ? $theme . '/' : '';
    }
    protected function tuMsg($message, $jumpUrl = '', $time = 3000, $callback = '', $parent = true)
    {
        $parents = $parent ? 'parent.' : '';
        $str = '<script>';
        $str .= $parents . 'bmsg("' . $message . '","' . $jumpUrl . '","' . $time . '","' . $callback . '");';
        $str .= '</script>';
        die($str);
    }
    protected function tuSuccess($message, $jumpUrl = '', $time = 3000, $parent = true)
    {
        $parent = $parent ? 'parent.' : '';
        $str = '<script>';
        $str .= $parent . 'success("' . $message . '",' . $time . ',\'jumpUrl("' . $jumpUrl . '")\');';
        $str .= '</script>';
        die($str);
    }
    protected function tuSuccess2($message, $jumpUrl = '', $time = 3000, $parent = true)
    {
        $parent = $parent ? 'parent.' : '';
        $str = '<script>';
        $str .= $parent . 'success("' . $message . '",' . $time . ',\'jump("' . $jumpUrl . '")\');';
        $str .= '</script>';
        die($str);
    }
   
    protected function tuErrorJump($message, $jumpUrl = '', $time = 3000)
    {
        $str = '<script>';
        $str .= 'parent.error("' . $message . '",' . $time . ',\'jumpUrl("' . $jumpUrl . '")\');';
        $str .= '</script>';
        die($str);
    }
    protected function tuError($message, $time = 3000, $yzm = false, $parent = true)
    {
        $parent = $parent ? 'parent.' : '';
        $str = '<script>';
        if ($yzm) {
            $str .= $parent . 'error("' . $message . '",' . $time . ',"yzmCode()");';
        } else {
            $str .= $parent . 'error("' . $message . '",' . $time . ');';
        }
        $str .= '</script>';
        die($str);
    }
    protected function tuLoginSuccess()
    {
        $str = '<script>';
        $str .= 'parent.parent.LoginSuccess();';
        $str .= '</script>';
        die($str);
    }
    protected function tuOpen($message, $close = true, $style)
    {
        $str = '<script>';
        $str .= 'parent.bopen("' . $message . '","' . $close . '","' . $style . '");';
        $str .= '</script>';
        die($str);
    }
   
    protected function tuJump($jumpUrl)
    {
        $str = '<script>';
        $str .= 'parent.jumpUrl("' . $jumpUrl . '");';
        $str .= '</script>';
        die($str);
    }
    
    
    protected function checkFields($data = array(), $fields = array())
    {
        foreach ($data as $k => $val) {
            if (!in_array($k, $fields)) {
                unset($data[$k]);
            }
        }
        return $data;
    }
    protected function ipToArea($_ip)
    {
        return iptoarea($_ip);
    }
    protected function getMenus()
    {
        $menus = $this->memberMenu();
        return $menus;
    }
}