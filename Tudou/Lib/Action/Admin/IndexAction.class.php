<?php
class IndexAction extends CommonAction{
    public function index(){
        $menu = D('Menu')->where(array('is_show'=>1))->fetchAll();
        if ($this->_admin['role_id'] != 1) {
            if ($this->_admin['menu_list']) {
                foreach ($menu as $k => $val) {
                    if (!empty($val['menu_action']) && !in_array($k, $this->_admin['menu_list'])) {
                        unset($menu[$k]);
                    }
                }
                foreach ($menu as $k1 => $v1) {
                    if ($v1['parent_id'] == 0) {
                        foreach ($menu as $k2 => $v2) {
                            if ($v2['parent_id'] == $v1['menu_id']) {
                                $unset = true;
                                foreach ($menu as $k3 => $v3) {
                                    if ($v3['parent_id'] == $v2['menu_id']) {
                                        $unset = false;
                                    }
                                }
                                if ($unset) {
                                    unset($menu[$k2]);
                                }
                            }
                        }
                    }
                }
                foreach($menu as $k1 => $v1){
                    if($v1['parent_id'] == 0){
                        $unset = true;
                        foreach($menu as $k2 => $v2) {
                            if($v2['parent_id'] == $v1['menu_id']){
                                $unset = false;
                            }
                        }
                        if($unset){
                            unset($menu[$k1]);
                        }
                    }
                }
            }else{
                $menu = array();
            }
        }
        $this->assign('menuList', $menu);
        $this->display();
    }
	
	//条码生成器
	public function getBarcodeGen(){
		$barcode = I('barcode','','htmlspecialchars');
		if(!$barcode){
			$this->ajaxReturn(array('code' => '0', 'msg' => '请输入条码'));
		}
		if(strlen($barcode) != 13){
			$this->ajaxReturn(array('code' => '0', 'msg' => '条码位数错误'));
		}
		if(false == is_numeric($barcode)){
			$this->ajaxReturn(array('code' => '0', 'msg' => '条码必须为数字'));
		}
		$file = D('Api')->getBarcodeGen($barcode);
		if($file){
			$this->ajaxReturn(array('code' => '1', 'msg' => '生成成功','img'=>config_weixin_img($file)));
		}else{
			$this->ajaxReturn(array('code' => '0', 'msg' => '生成失败'));
		}
	}


	public function action_delete($log_id = 0){
		if(is_numeric($log_id) && ($log_id = (int) $log_id)){
			M('AdminActionLogs')->delete($log_id);
			$this->tuSuccess('删除成功', U('index/main'));
		}else{
			$log_id = $this->_post('log_id', false);
			if(is_array($log_id)){
				foreach($log_id as $id){
					M('AdminActionLogs')->delete($id);
				}
				$this->tuSuccess('批量删除成功', U('index/main'));
			}
			$this->tuError('非法操作');
		}
	}
	
	
	public function action_delete_all(){
		M('AdminActionLogs')->where(array('log_id'=>array('gt',0)))->delete();
		$this->tuSuccess('删除全部操作日志成功', U('index/main'));
	}
	
	
    public function main(){
		
		
		$obj = D('Menu');
        import('ORG.Util.Page');
        $map = array('is_show'=>'1','parent_id'=>array('gt',0));
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if($keyword){
            $map['menu_name|menu_action'] = array('LIKE', '%' . $keyword . '%');
        }
		if($keyword){
			$count = $obj->where($map)->count();
			$Page = new Page($count, 25);
			$show = $Page->show();
			$lists = $obj->where($map)->select();
			foreach($lists as $k => $val){
                if(empty($val['menu_action'])){
                    unset($lists[$k]);
				}
        	}
			$count = count($lists);
			$Page = new Page($count, 10);
			$show = $Page->show();
			$lists = array_slice($lists, $Page->firstRow, $Page->listRows);
			
			$this->assign('keyword', $keyword);
			$this->assign('lists', $lists);
		}
        $this->assign('page', $show);
		
		
		$condition = array();
		$count2 = M('AdminActionLogs')->count();
		$Page2 = new Page($count2,5);
		$show2 = $Page2->show();
		$action = M('AdminActionLogs')->where($condition)->select();
		foreach($action as $k =>$v){    
          $Admin = M('Admin')->where(array('admin_id'=>$v['admin_id']))->find();
          $action[$k]['admin'] = $Admin;
        }
		$this->assign('action', $action);
		$this->assign('page2', $show2);	
		
		
		
		$this->assign('warning',$warning = D('Admin')->find($this->_admin['admin_id']));
        $bg_time = strtotime(TODAY);
        $counts['totay_order'] = (int) D('Order')->where(array('type' => 'goods', 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
       
        $counts['order'] = (int) D('Order')->where(array('type' => 'goods', 'status' => array('EGT', 0)))->count();
        
        $counts['gold'] = (int) D('Order')->where(array('type' => 'gold', 'status' => array('EGT', 0)))->count();
        $counts['today_yuyue'] = (int) D('Shopyuyue')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
        
        //查询今日会员
		$counts['users'] = (int) D('Users')->count();
        $counts['totay_user'] = (int) D('Users')->where(array('reg_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
		$counts['user_moblie'] = (int) D('Users')->where(array('mobile'=>array('EXP','IS NULL')))->count();
		$counts['user_email'] = (int) D('Users')->where(array('email'=>array('EXP','IS NULL')))->count();
		$counts['user_weixin'] = (int) D('Connect')->where(array('type'=>weixin))->count();
		$counts['user_weibo'] = (int) D('Connect')->where(array('type'=>weibo))->count();
		$counts['user_qq'] = (int) D('Connect')->where(array('type'=>qq))->count();
		$counts['user_weixin_day'] = (int) D('Connect')->where(array('reg_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
		//查询资金
		$counts['moneylogs'] = (int) D('Usermoneylogs')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();

		$counts['money_and'] = (int) D('Users')->sum('money');
		$counts['money_integral'] = (int) D('Users')->sum('integral');
		$counts['money_cash'] = (int) D('Userscash')->sum('money');
		$counts['money_cash_day'] = (int) D('Userscash')->where(array('addtime' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->sum('money');
		$counts['money_cash_ok'] = (int) D('Userscash')->where(array('status'=>1))->sum('money');
		$counts['money_cash_audit'] = (int) D('Userscash')->where(array('status'=>0))->count();
	
        //查询今日商
		$counts['shop'] = (int) D('Shop')->count();
        $counts['totay_shop'] = (int) D('Shop')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
		$counts['shoprecognition'] = (int) D('Shop')->where(array('recognition' => 0))->count();
		$counts['totay_shop_audit'] = (int) D('Shop')->where(array('audit' => 0))->count();
		$counts['shop_audit'] = (int) D('Shop')->where(array('is_renzheng' => 1))->count();
		$counts['shop_weidian'] = (int) D('Weidiandetails')->count();
		$counts['shop_weidian_audit'] = (int) D('Weidiandetails')->where(array('audit' => 0))->count();
		$counts['totay_dianping'] = (int) D('Shopdianping')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
		
		//查询商城
		
	$counts['goods'] = (int) D('Goods')->count();
	$counts['goods_day'] = (int) D('Goods')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();	
	$counts['goods_audit'] = (int) D('Goods')->where(array('audit' => 0))->count();	
	$counts['order'] = (int) D('Ordergoods')->count();	
	$counts['order_day'] = (int) D('Ordergoods')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();	
	$counts['order_tui'] = (int) D('Ordergoods')->where(array('status' => 2))->count();
	$counts['totay_dianping_goods'] = (int) D('Goodsdianping')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
    $counts['totay_dianping_tuan'] = (int) D('Tuandianping')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
	
	 //团购数据
	$counts['tuan'] = (int) D('Tuan')->count();
	$counts['tuan_day'] = (int) D('Tuan')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
	$counts['tuan_audit'] = (int) D('Tuan')->where(array('audit' => 0))->count();
    $counts['totay_order_tuan'] = (int) D('Tuanorder')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
	$counts['order_tuan'] = (int) D('Tuanorder')->count();
    $counts['order_tuan_tui'] = (int) D('Tuancode')->where(array('status' => 1))->count();
	$counts['tuan_code_used'] = (int) D('Tuancode')->where(array('is_used' => 0))->count();	
	$counts['dianping_tuan'] = (int) D('Tuandianping')->count();
    $counts['totay_dianping_tuan'] = (int) D('Tuandianping')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
	
	 //外卖数据
	$counts['ele'] = (int) D('Ele')->count();
	$counts['eleproduct'] = (int) D('Eleproduct')->count();
	$counts['eleproduct_day'] = (int) D('Eleproduct')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
	$counts['eleproduct_audit'] = (int) D('Eleproduct')->where(array('audit' => 0))->count();
	$counts['order_waimai'] = (int) D('Eleorder')->count();
    $counts['totay_order_waimai'] = (int) D('Eleorder')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
    $counts['order_waimai_tui'] = (int) D('Eleorder')->where(array('status' => 3))->count();  
	$counts['dianping_waimai'] = (int) D('Eledianping')->count(); 
	$counts['totay_dianping_waimai'] = (int) D('Eledianping')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();		
	
	
	//菜市场数据
	$counts['market'] = (int) D('Market')->count();
	$counts['marketproduct'] = (int) D('Marketproduct')->count();
	$counts['marketproduct_day'] = (int) D('Marketproduct')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
	$counts['marketproduct_audit'] = (int) D('Marketproduct')->where(array('audit' => 0))->count();
	$counts['order_market'] = (int) D('Marketorder')->count();
    $counts['totay_order_market'] = (int) D('Marketorder')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
    $counts['order_market_tui'] = (int) D('Marketorder')->where(array('status' => 3))->count();  
	$counts['dianping_market'] = (int) D('Marketdianping')->count(); 
	$counts['totay_dianping_market'] = (int) D('Marketdianping')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();		
	
	//便利店数据
	$counts['store'] = (int) D('Store')->count();
	$counts['mstoreproduct'] = (int) D('Storeproduct')->count();
	$counts['storeproduct_day'] = (int) D('Storeproduct')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
	$counts['storeproduct_audit'] = (int) D('Storeproduct')->where(array('audit' => 0))->count();
	$counts['order_store'] = (int) D('Storeorder')->count();
    $counts['totay_order_store'] = (int) D('Storeorder')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
    $counts['order_store_tui'] = (int) D('Storeorder')->where(array('status' => 3))->count();  
	$counts['dianping_store'] = (int) D('Storedianping')->count(); 
	$counts['totay_dianping_store'] = (int) D('Storedianping')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();	
    
 	
	
	//优惠劵数据
	
	$counts['coupon'] = (int) D('Coupon')->count();
	$counts['coupon_day'] = (int) D('Coupon')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
	$counts['coupon_audit'] = (int) D('Coupon')->where(array('audit' => 0))->count();
	$counts['coupon_download'] = (int) D('Coupondownload')->count();
	$counts['coupon_download_day'] = (int) D('Coupondownload')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
	$counts['today_coupon'] = (int) D('Coupondownload')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
	
	
	//分类信息数据
	$counts['life'] = (int) D('Life')->count();
	$counts['totay_life'] = (int) D('Life')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
	$counts['life_audit'] = (int) D('Life')->where(array('audit' => 0))->count();
	$counts['totay_life_audit'] = (int) D('Life')->where(array('audit' => 0))->count();
	$counts['life_views'] = (int) D('Life')->sum('views');
	
	//小区数据
	$counts['community'] = (int) D('Community')->count();
	$counts['community_bbs'] = (int) D('Communityposts')->count();
	$counts['community_bbs_audit'] = (int) D('Communityposts')->where(array('audit' => 0))->count();
	$counts['community_feedback'] = (int) D('Feedback')->count();
	$counts['community_phone'] = (int) D('Convenientphone')->count();
	$counts['community_news'] = (int) D('Communitynews')->count();
	$counts['community_news_day'] = (int) D('Communitynews')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
	$counts['community_order'] = (int) D('Communityorderproducts')->where(array('status'=>0))->sum('money');
	
   
	
	//自媒体数据
	$counts['article'] = (int) D('Article')->count();
	$counts['article_audit'] = (int) D('Article')->where(array('audit' => 0))->count();
	$counts['article_day'] = (int) D('Article')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
	$counts['article_reply'] = (int) D('Articlecomment')->count();
	$counts['article_vies'] = (int) D('Article')->sum('views');
	$counts['article_zan'] = (int) D('Article')->sum('zan');


        //增加IP通知
        $ad['last_ip'] = $this->ipToArea($admin['last_ip']);
        $this->assign('ad', $ad);
        $v = (require BASE_PATH . '/version.php');
        //
        $this->assign('v', $v);
        $this->assign('counts', $counts);
		
		$money['money'] = D('Paymentlogs')->where(array('type' => 'money','is_paid'=>1))->sum('need_pay');
		$money['ele'] = D('Paymentlogs')->where(array('type' => 'ele','is_paid'=>1))->sum('need_pay');
		$money['goods'] = D('Paymentlogs')->where(array('type' => 'goods','is_paid'=>1))->sum('need_pay');
		$money['appoint'] = D('Paymentlogs')->where(array('type' => 'appoint','is_paid'=>1))->sum('need_pay');
		$money['hotel'] = D('Paymentlogs')->where(array('type' => 'hotel','is_paid'=>1))->sum('need_pay');
		$money['farm'] = D('Paymentlogs')->where(array('type' => 'farm','is_paid'=>1))->sum('need_pay');
		$money['all'] = D('Paymentlogs')->where(array('is_paid'=>1))->sum('need_pay');
		$this->assign('money', $money);
        $this->display();
    }
    public function check(){
        //后期获得通知使用！
        die('1');
    }
}