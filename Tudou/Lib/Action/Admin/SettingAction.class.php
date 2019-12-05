<?php
class SettingAction extends CommonAction{
	
	
	
    public function site(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'site', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('站点设置成功', U('setting/site'));
        } else {
            $this->assign('citys', D('City')->fetchAll());
            $this->assign('ranks', D('Userrank')->fetchAll());
            //增加分销
            $this->display();
        }
    }
	
	
    public function config(){
        if($this->isPost()){
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'config', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('全局设置成功', U('setting/config'));
        }else{
            $this->display();
        }
    }
	
	 public function ad(){
        if($this->isPost()){
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'ad', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('广告设置成功', U('setting/ad'));
        }else{
            $this->display();
        }
    }
	
	
    public function attachs(){
        if($this->isPost()){
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'attachs', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('附件设置成功', U('setting/attachs'));
        }else{
            $this->display();
        }
    }
	
	
    public function mall(){
        if($this->isPost()){
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'mall', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('商城设置成功', U('setting/mall'));
        }else{
            $this->display();
        }
    }
	
	
    public function ucenter(){
        if($this->isPost()){
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'ucenter', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('设置成功', U('setting/ucenter'));
        }else{
            $this->display();
        }
    }
	
	
	
   public function sms(){
		$config = D('Setting')->fetchAll();
		if(!empty($config['sms']['sms_bao_account'])){
			$http = tmplToStr('http://www.smsbao.com/query?u='.$config["sms"]["sms_bao_account"].'&p='.md5($config["sms"]["sms_bao_password"]), $local);
			
			import("@/Net.Curl");
			$this->curl = new Curl();
	
			//$res = file_get_contents($http);
			
			//如果是选择get模式
			if($config['sms']['curl'] == 'get'){
				$res = $this->curl->get($http);
				$res = json_decode($res, true);
			}else{
				$res = file_get_contents($http);
			}
			
			
			
			
			$res1 = explode(",", $res);
			if($res1[1] > 0){
				$number = $res1[1];
			}else{
				$number = '短信宝账户或者密码错了';
			}
		}else{
			$number = '短信宝账或户密码未设置';
		}
		$this->assign('number',$number);
        if($this->isPost()){
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'sms', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('短信配置成功', U('setting/sms'));
        }else{
            $this->display();
        }
    }
	
	
	
	public function pay(){
        if($this->isPost()){
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'pay', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('支付设置成功', U('setting/pay'));
        }else{
            $this->display();
        }
    }
	
	
	
    public function weixin(){
        if($this->isPost()){
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'weixin', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('微信设置成功', U('setting/weixin'));
        }else{
            $this->display();
        }
    }
  
  
    public function weixinmenu(){
        if($this->isPost()){
            $data = $this->_post('data', false);
            $result = D('Weixin')->weixinmenu($data);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'weixinmenu', 'v' => $data));
            D('Setting')->cleanCache();
            if($result > 1){
				$this->tuError('菜单设置错误，错误码：'.$result);
			}else{
				$this->tuSuccess('菜单设置成功', U('setting/weixinmenu'));
			}
        }else{
            $this->display();
        }
    }
	
	
    public function connect(){
        if($this->isPost()){
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'connect', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('设置成功', U('setting/connect'));
        }else{
            $this->display();
        }
    }
	
	
    public function integral(){
        if($this->isPost()){
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'integral', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('积分设置成功', U('setting/integral'));
        }else{
            $this->display();
        }
    }
	
	
    public function weidian(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'weidian', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('微店设置成功', U('setting/weidian'));
        } else {
            $this->display();
        }
    }
    
    public function mail(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'mail', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('邮箱设置成功', U('setting/mail'));
        } else {
            $this->display();
        }
    }
    public function mobile(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'mobile', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('手机功能设置成功', U('setting/mobile'));
        } else {
            $this->display();
        }
    }
   
    public function other(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'other', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('设置成功', U('setting/other'));
        } else {
            $this->display();
        }
    }
	
	public function profit(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'profit', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('分销设置成功', U('setting/profit'));
        } else {
		
			$this->assign('ranks', D('Userrank')->fetchAll());
            $this->display();
        }
    }
    public function operation(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'operation', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('站点功能成功', U('setting/operation'));
        } else {
            $this->display();
        }
    }
    public function register(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'register', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('注册成功', U('setting/register'));
        } else {
            $this->display();
        }
    }
    public function share(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'share', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('分享设置成功', U('setting/share'));
        } else {
            $this->display();
        }
    }
    public function cash() {
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'cash', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('提现设置成功', U('setting/cash'));
        } else {
			$this->assign('ranks', D('Userrank')->fetchAll());
            $this->display();
        }
    }
    public function collects(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'collects', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('采集设置成功', U('setting/collects'));
        } else {
            $this->display();
        }
    }
    public function search(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'search', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('搜索设置成功', U('setting/search'));
        } else {
            $this->display();
        }
    }
    public function sms_shop(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'sms_shop', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('购买短信设置成功', U('setting/sms_shop'));
        } else {
            $this->display();
        }
    }
	public function running(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'running', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('跑腿设置成功', U('setting/running'));
        } else {
            $this->display();
        }
    }
	public function community(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'community', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('小区设置成功', U('setting/community'));
        } else {
            $this->display();
        }
    }
	 public function appoint(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'appoint', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('家政设置成功', U('setting/appoint'));
        } else {
            $this->display();
        }
    }
    public function ele(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data['time'] = time();
            $data = serialize($data);
            D('Setting')->save(array('k' => 'ele', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('外卖更新设置成功', U('setting/ele'));
        } else {
            $this->display();
        }
    }
	public function market(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data['time'] = time();
            $data = serialize($data);
            D('Setting')->save(array('k' => 'market', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('菜市场更新设置成功', U('setting/market'));
        } else {
            $this->display();
        }
    }
	public function store(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data['time'] = time();
            $data = serialize($data);
            D('Setting')->save(array('k' => 'store', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('便利店更新设置成功', U('setting/store'));
        } else {
            $this->display();
        }
    }
	public function goods(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data['time'] = time();
            $data = serialize($data);
            D('Setting')->save(array('k' => 'goods', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('商城更新设置成功', U('setting/goods'));
        } else {
            $this->display();
        }
    }
	public function zhe(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data['time'] = time();
            $data = serialize($data);
            D('Setting')->save(array('k' => 'zhe', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('五折卡设置成功', U('setting/zhe'));
        } else {
            $this->display();
        }
    }
	
	public function backers(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data['time'] = time();
            $data = serialize($data);
            D('Setting')->save(array('k' => 'backers', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('推手设置成功', U('setting/backers'));
        } else {
            $this->display();
        }
    }
	
	//城市代理设置
    public function city(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'city', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('城市配置成功', U('setting/city'));
        } else {
            $this->display();
        }
    }
	
	public function life(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data['time'] = time();
            $data = serialize($data);
            D('Setting')->save(array('k' => 'life', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('分类信息设置成功', U('setting/life'));
        } else {
            $this->display();
        }
    }
	
	public function shop(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data['time'] = time();
            $data = serialize($data);
            D('Setting')->save(array('k' => 'shop', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('商家全局配置成功', U('setting/shop'));
        } else {
            $this->display();
        }
    }
	public function village(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data['time'] = time();
            $data = serialize($data);
            D('Setting')->save(array('k' => 'village', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('智慧乡村配置成功', U('setting/village'));
        } else {
            $this->display();
        }
    }
	
	public function pinche(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data['time'] = time();
            $data = serialize($data);
            D('Setting')->save(array('k' => 'pinche', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('拼车配置成功', U('setting/pinche'));
        } else {
            $this->display();
        }
    }
	
	public function prestige(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'prestige', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('设置成功', U('setting/prestige'));
        } else {
            $this->display();
        }
    }
	
	public function stock(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'stock', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('股权成功', U('setting/stock'));
        } else {
            $this->display();
        }
    }
	
	public function online(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'online', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('农村电商配置成功', U('setting/online'));
        } else {
            $this->display();
        }
    }
	
	public function wxapp(){
        if ($this->isPost()){
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'wxapp', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('小程序配置成功', U('setting/wxapp'));
        }else{
            $this->display();
        }
    }
	
	public function delivery(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'delivery', 'v' => $data));
            D('Setting')->cleanCache();
            $this->tuSuccess('配送员配置成功', U('setting/delivery'));
        } else {
            $this->display();
        }
    }
	
	
}