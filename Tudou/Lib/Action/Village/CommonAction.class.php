<?php
class CommonAction extends Action{
    protected $uid = 0;
    protected $member = array();
    protected $_CONFIG = array();
    protected $village_id = 0;
    protected $VILLAGE = array();
    protected function _initialize(){
        define('IN_MOBILE', true);
        $is_weixin = is_weixin();
        $is_weixin = $is_weixin == false ? false : true;
        define('IS_WEIXIN', $is_weixin);
		
		$this->_CONFIG = D('Setting')->fetchAll();
        if($this->_CONFIG['site']['https'] == 1){
			define('__HOST__', 'https://' . $_SERVER['HTTP_HOST']);
		}else{
			define('__HOST__', 'http://' . $_SERVER['HTTP_HOST']);
		}
        $this->assign('CONFIG', $this->_CONFIG);
		
        $this->uid = getUid();
        if (!empty($this->uid)) {
            $this->member = D('Users')->find($this->uid);
        }
        if (strtolower(MODULE_NAME) != 'passport' && strtolower(MODULE_NAME) != 'public') {
            if (empty($this->uid)) {
                header("Location: " . U('passport/login'));
                die;
            }
            $this->village = D('Village')->find(array("where" => array('user_id' => $this->uid, 'closed' => 0)));
            if (empty($this->village)) {
				$this->error('您不是乡村管理员', U('passport/login'));
                die;
            }
			$this->village_id = $this->village['village_id'];
            $this->assign('VILLAGE', $this->village);
        }
		
        
		
        $this->assign('MEMBER', $this->member);
        $this->assign('ctl', strtolower(MODULE_NAME));
        $this->assign('act', ACTION_NAME);
        $this->assign('today', TODAY);
        $this->assign('nowtime', NOW_TIME);
		$this->assign('color', $color = $this->_CONFIG['other']['color']);
		
        $bg_time = strtotime(TODAY);
        $this->assign('msg_day', $counts['msg_day'] = (int) D('Msg')->where(array('cate_id' => 7, 'views' => 0, 'village_id' => $this->village_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count());
    }
    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = ''){
        parent::display($this->parseTemplate($templateFile), $charset, $contentType, $content = '', $prefix = '');
    }
    private function parseTemplate($template = ''){
        $depr = C('TMPL_FILE_DEPR');
        $template = str_replace(':', $depr, $template);
        $theme = $this->getTemplateTheme();
        define('NOW_PATH', BASE_PATH . '/themes/' . $theme . 'Village/');
        define('THEME_PATH', BASE_PATH . '/themes/default/Village/');
        define('APP_TMPL_PATH', __ROOT__ . '/themes/default/Village/');
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
    protected function checkFields($data = array(), $fields = array()){
        foreach ($data as $k => $val) {
            if (!in_array($k, $fields)) {
                unset($data[$k]);
            }
        }
        return $data;
    }
    protected function tuMsg($message, $jumpUrl = '', $time = 3000){
        $str = '<script>';
        $str .= 'parent.boxmsg("' . $message . '","' . $jumpUrl . '","' . $time . '");';
        $str .= '</script>';
        exit($str);
    }
    protected function ipToArea($_ip){
        return IpToArea($_ip);
    }
}