<?php
class FarmAction extends CommonAction {
    protected $types = array();
    public function _initialize() {
        parent::_initialize();
		if(empty($this->_CONFIG['operation']['farm'])){
			$this->error('农家乐功能已关闭');die;
		}
        $this->group = D('Farm')->getFarmGroup();
        $this->assign('group', $this->group);
        $this->cate = D('Farm')->getFarmCate();
        $this->assign('cate', $this->cate);
        $this->people = D('Farm')->getPeople();
        $this->assign('people', $this->people);
        $this->days = D('Farm')->getDays();
        $this->assign('days', $this->days);
    }
	//新版首页
    public function index(){
        $map = array();
		$keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $scity_id = (int) $this->_param('scity_id');
        if($scity_id){
            $this->assign('scity_id',$scity_id);
        }
        $fp = (int) $this->_param('fp');
        $tp = (int) $this->_param('tp');
        if($fp){
            $this->assign('fp',$fp);
        }
        if($tp){
            $this->assign('tp',$tp);
        }
        $cate_id = (int) $this->_param('cate_id');
        $group_id = (int) $this->_param('group_id');
        
        if($cate_id){
            $this->assign('cate_id',$cate_id);
        }
        
        if($group_id){
            $this->assign('group_id',$group_id);
        }
        $this->assign('nextpage', LinkTo('farm/loaddata', array('scity_id'=>$scity_id,'fp'=>$fp,'tp'=>$tp,'cate_id'=>$cate_id,'group_id'=>$group_id,'keyword'=>$keyword,'t'=>NOW_TIME,'p' => '0000')));
        $this->display();
    }
    
    public function loaddata() {
        $f = M('Farm');
        import('ORG.Util.Page');
        $map = array();
		if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['farm_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $scity_id = (int) $this->_param('scity_id');
        if($scity_id){
            $map['city_id'] = $scity_id;
        }
        $fp = (int) $this->_param('fp');
        $tp = (int) $this->_param('tp');
        if(!$fp && $tp){
            $map['price'] = array('ELT', $tp);
        }elseif($fp && !$tp){
            $map['price'] = array('GT', $fp);
        }elseif($fp&&$tp){
            $map['price'] = array('between', $fp.','.$tp);
        }
        
        $cate_id = (int) $this->_param('cate_id');
        $group_id = (int) $this->_param('group_id');
        if($cate_id){
            $shop2 = D('Farmplayattr')->where(array('attr_id'=>$cate_id))->select();
            $shoplist2 = array();
            foreach($shop2 as $sk=>$sv){
                $shoplist2[] = $sv['shop_id'];
            }
            $shoplist2 = array_unique($shoplist2);
        }
        if($group_id){
            $shop1 = D('Farmgroupattr')->where(array('attr_id'=>$group_id))->select();
            $shoplist1 = array();
            foreach($shop1 as $sk=>$sv){
                $shoplist1[] = $sv['shop_id'];
            }
            $shoplist1 = array_unique($shoplist1);
        }
        
        if($shoplist1 && $shoplist2){
            $shop_list = array_unique($shoplist1+$shoplist2);
        }elseif($shoplist1 && !$shoplist2){
            $shop_list = $shoplist1;
        }elseif($shoplist2 && !$shoplist1){
            $shop_list = $shoplist2;
        }
        
        if($shop_list){
           $map['shop_id'] = array('in',$shop_list); 
        }
        $count = $f->where($map)->count();
        $Page  = new Page($count,10);
        $show = $Page->show();
        
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $f->where($map)->order('farm_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        foreach($list as $k => $val){
             $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
             if($package = D('FarmPackage')->where(array('farm_id'=>$val['farm_id']))->find()){
                 $list[$k]['package'] = $package;
             }
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display(); 
    }
    
    

    public function detail($farm_id){
        $obj = D('Farm');
        if(!$farm_id = (int)$farm_id){
            $this->error('该农家乐不存在');
        }elseif(!$detail = $obj->where(array('farm_id'=>$farm_id))->find()){
            $this->error('该农家乐不存在');
        }elseif($detail['closed'] == 1||$detail['audit'] == 0){
            $this->error('该农家乐已删除或未通过审核');
        }else{
            $lat = addslashes(cookie('lat'));
            $lng = addslashes(cookie('lng'));
            if (empty($lat) || empty($lng)) {
                $lat = $this->city['lat'];
                $lng = $this->city['lng'];
            }
            $detail['d'] = getDistance($lat, $lng, $detail['lat'], $detail['lng']);
            $pics = D('FarmPics')->where(array('farm_id'=>$farm_id))->select();
            $pics[] = array('photo'=>$detail['photo']);
            
            $groupid = $obj->getid($detail['shop_id'],1);
            $playid = $obj->getid($detail['shop_id'],2);
            
            $package = D('FarmPackage')->where(array('farm_id'=>$detail['farm_id']))->select();

            $tuan_list = D('Tuan')->where(array('audit' => 1, 'closed' => 0, 'city_id' => $this->city_id, 'end_date' => array('EGT', NOW),'bg_date' => array('ELT', NOW),'shop_id'=>$detail['shop_id']))->limit(2)->select();
            
            //其它农家
            $other_farm = D('Farm')->where(array('farm_id'=>array('neq',$detail['farm_id'])))->order('rand()')->limit(2)->select();
   
            foreach($other_farm as $k => $v){
                $other_farm[$k]['d'] = getDistance($lat, $lng, $v['lat'], $v['lng']);
            }
            
            $comment = D('FarmComment')->where(array('farm_id'=>$detail['farm_id']))->limit(10)->select();
            foreach($comment as $kk => $vv){
                $comment[$k]['pic'] = D('FarmCommentPics')->where(array('comment_id'=>$vv['comment_id']))->find();
                $comment[$k]['u'] = D('Users')->where(array('user_id'=>$vv['user_id']))->find();
            }

            
            //高于同行的比例
            $bl_map = array();
            $bl_map['score']  = array('elt',$detail['score']);
            $a = D('Farm')->where($bl_map)->count();
            $all = D('Farm')->count();
            
            $bl = intval($a/$all*100);

            $this->assign('bl',$bl);
            $this->assign('tuan_list',$tuan_list);
            $this->assign('package',$package);
            $this->assign('comment',$comment);
            $this->assign('other_farm',$other_farm);
            $this->assign('groupid',$groupid);
            $this->assign('playid',$playid);
            $this->assign('detail',$detail);
            $this->assign('pics',$pics);
            $this->display();
        }
    }

    public function info($hotel_id){
        $obj = D('Hotel');
        if(!$hotel_id = (int)$hotel_id){
            $this->error('该酒店不存在');
        }elseif(!$detail = $obj->find($hotel_id)){
            $this->error('该酒店不存在');
        }elseif($detail['closed'] == 1||$detail['audit'] == 0){
            $this->error('该酒店已删除或未通过审核');
        }else{
            $this->assign('detail',$detail);
            $this->display();
        }
    }
    
    public function order($farm_id,$pid){
        if(!$farm_id){
            $this->error('农家错误!');
        }elseif(!$f = D('Farm')->where(array('farm_id'=>$farm_id))->find()){
            $this->error('农家不存在!');
        }elseif(!$pid){
            $this->error('套餐没有选择!');
        }elseif(!$p = D('FarmPackage')->where(array('pid'=>$pid))->find()){
            $this->error('套餐不存在!');
        }else{
            $package = D('FarmPackage')->where(array('farm_id'=>$farm_id))->select();
            $this->assign('farm_id',$farm_id);
            $this->assign('pid',$pid);
            $this->assign('package',$package);
            $this->display();
        }
    }

    
    public function orderCreate(){
        
        if (empty($this->uid)) {
            $this->tuMsg('您还没有登录',U('passport/login'));
            die;
        }else{
            $data = I('data');

            $gotime = I('gotime',0,'trim');
            $data['gotime'] = strtotime(trim($data['gotime']));
            $data['name'] = htmlspecialchars(trim($data['name']));
            $data['mobile'] = trim($data['mobile']);
            $data['pid'] = intval(trim($data['pid']));
            $data['note'] = htmlspecialchars(trim($data['note']));

            if(!$data['gotime']){
                $this->tuMsg('没有选择时间');
            }else if(!$data['name']){
                $this->tuMsg('没有填写联系人');
            }else if(!$data['mobile'] || !isMobile($data['mobile'])){
                $this->tuMsg('手机号码不正确！'.$mobile);
            }else if(!$data['pid']){
                $this->tuMsg('没有选择套餐');
            }else{
                $p = D('FarmPackage')->find($data['pid']);
                $data['user_id'] = $this->uid;
                $data['farm_id'] =$p['farm_id'];
                $data['amount'] = $p['price'];
                $data['jiesuan_amount'] = $p['jiesuan_price'];
                $data['create_time'] = time();
                $data['create_ip'] = get_client_ip();
                if($add = D('FarmOrder')->add($data)){
                    $this->tuMsg('下单成功',U('farm/pay',array('order_id'=>$add)));
                }else{
                    $this->tuMsg('下单失败!');
                }
            }
        }
        
    }

    public function pay(){
        if (empty($this->uid)) {
            $this->error('您还没有登录',U('passport/login'));
            die;
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('FarmOrder')->find($order_id);
        if (empty($order) || $order['order_status'] != 0 || $order['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
        $f = D('FarmPackage')->find($order['pid']);
        if (!$f) {
            $this->error('该套餐不存在');
            die;
        }
        $this->assign('payment', D('Payment')->getPayments(true));
        $this->assign('f', $f);
        $this->assign('order', $order);
        $this->display();
    }
    
    public function order2(){
        $this->display();
    }

    public function pay2(){
        if (empty($this->uid)) {
           $this->tuMsg('您还没有登录',U('passport/login'));
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('FarmOrder')->find($order_id);
        if (empty($order) || $order['order_status'] != 0 || $order['user_id'] != $this->uid) {
            $this->tuMsg('该订单不存在');
            die;
        }
        if (!$code = $this->_post('code')) {
            $this->tuMsg('请选择支付方式');
        }
        
        $payment = D('Payment')->checkPayment($code);
        if (empty($payment)) {
            $this->tuMsg('该支付方式不存在');
        }
        $f = D('FarmPackage')->find($order['pid']);
        if (empty($f)) {
            $this->tuMsg('该套餐不存在');
        }
        $logs = D('Paymentlogs')->getLogsByOrderId('farm', $order_id);
        if (empty($logs)) {
            $logs = array(
                'type' => 'farm',
                'user_id' => $this->uid,
                'order_id' => $order_id,
                'code' => $code,
                'need_pay' => $order['amount']*100,
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip(),
                'is_paid' => 0
            );
            $logs['log_id'] = D('Paymentlogs')->add($logs);
        } else {
            $logs['need_pay'] = $order['amount']*100;
            $logs['code'] = $code;
            D('Paymentlogs')->save($logs);
        }
        D('Weixintmpl')->weixin_notice_farm_user($order_id,$this->uid,1);//农家乐微信通知用户
        $this->tuMsg('选择支付方式成功！下面请进行支付', U('payment/payment', array('log_id' => $logs['log_id'])));
    }
    
    
     public function favorites() {
        if (empty($this->uid)) {
            $this->tuMsg('您还没有登录',U('passport/login'));
        }
        $farm_id = (int) $this->_get('farm_id');
        if (!$detail = D('Farm')->where(array('farm_id'=>$farm_id))->find()) {
            $this->tuMsg('没有该农家');
        }
        if ($detail['closed']) {
            $this->tuMsg('该商家已经被删除');
        }
        if (D('Shopfavorites')->check($detail['shop_id'], $this->uid)) {
            $this->tuMsg('您已经收藏过了');
        }
        $data = array(
            'shop_id' => $detail['shop_id'],
            'user_id' => $this->uid,
            'create_time' => NOW_TIME,
            'create_ip' => get_client_ip()
        );
        if (D('Shopfavorites')->add($data)) {
            $this->tuMsg('恭喜您收藏成功', U('farm/detail', array('farm_id' => $farm_id)));
        }
        $this->tuMsg('收藏失败');
    }

}
