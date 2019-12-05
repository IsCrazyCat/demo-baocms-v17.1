<?php
class OrderAction extends CommonAction {
	public function _initialize() {
        parent::_initialize();
		if ($this->_CONFIG['operation']['mall'] == 0) {
			$this->error('此功能已关闭');die;
		}
        $this->assign('logistics', $logistics = D('Logistics')->where(array('closed'=>0))->select());
    }
  
	public function checkNotify() {
		$time = time() - 3;
		$bool = D('Order')->where(array('shop_id' => $shop_id))->find();
    }
	
	public function index(){
        $this->status = array('IN',array(0,1,2,3,4,5,6,7,8));
		$this->is_daofu = array('IN',array(0,1));
        $this->showdata();
        $this->display();
    }
	
	public function wait(){
        $this->status = 1;
		$this->is_daofu = array('IN',array(0,1));
        $this->showdata();
        $this->display();
    }
	public function wait2(){
		$this->status = 1;
		$this->is_daofu = 1;
        $this->showdata();
        $this->display();
    }
	
	public function wait_refunded(){
        $this->status = 4;
		$this->is_daofu = 0;
        $this->showdata();
        $this->display();
    }
	public function delivery(){
        $this->status = array('IN',array(2,3));
		$this->is_daofu = 0;
        $this->showdata();
        $this->display();
    }
	public function over(){
        $this->status = 8;
		$this->is_daofu = array('IN',array(0,1));
        $this->showdata();
        $this->display();
    }
	public function refunded(){
        $this->status = 5;
		$this->is_daofu = 0;
        $this->showdata();
        $this->display();
    }
	//剩下的控制器
	public function showdata() {
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'status' => $this->status , 'is_daofu' => $this->is_daofu ,'shop_id'=> $this->shop_id );
         if (($bg_date = $this->_param('bg_date', 'htmlspecialchars') ) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
		if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
		if (isset($_GET['profit']) || isset($_POST['profit'])) {
            $profit = (int) $this->_param('profit');
            if ($profit != 999) {
                $map['is_profit'] = $profit;
            }
            $this->assign('profit', $profit);
        } else {
            $this->assign('profit', 999);
        }
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('keyword', $keyword);
		$Order = D('Order');
        $count = $Order->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $list = $Order->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = $shop_ids = $addr_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			$address_ids[$val['address_id']] = $val['address_id'];
        }
        if (!empty($order_ids)) {
            $goods = D('Ordergoods')->where(array('order_id' => array('IN', $order_ids)))->select();
            $goods_ids = array();
            foreach ($goods as $val) {
                $goods_ids[$val['goods_id']] = $val['goods_id'];
            }
            $this->assign('goods', $goods);
            $this->assign('products', D('Goods')->itemsByIds($goods_ids));
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('addrs', D('Paddress')->itemsByIds($address_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('types', D('Order')->getType());
        $this->assign('goodtypes', D('Ordergoods')->getType());
		$this->assign('types', D('Order')->getType());
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->assign('picks', session('order'));
    }


 
	
    public function pick() {
        $order_ids = session('order');
        $orders = $this->_post('order_id', false);
        foreach ($orders as $val) {
            if ($detail = D('Order')->find($val)) {
                if (($detail['status'] == 1 && $detail['status'] != 3 && $detail['closed'] == 0) || ($detail['staus'] == 0 && $detail['is_daofu'] == 1 && $detail['shop_id'] == $this->shop_id && $detail['closed'] == 0)) {
                    $order_ids[$val] = $val;
                }
            }
        }
        session('order', $order_ids);
        if ($this->_get('wait')) {
            $this->tuSuccess('加入捡货单成功', U('order/wait2'));
        } else {
            $this->tuSuccess('加入捡货单成功', U('order/wait'));
        }
    }

    public function clean() {
        session('order', null);
        if ($this->_get('wait')) {
            $this->tuSuccess('清空捡货队列成功', U('order/wait2'));
        } else {
            $this->tuSuccess('清空捡货队列成功', U('order/wait'));
        }
    }
    
     //创建捡货单
    public function create() {
        $order_ids = session('order');
        $local = array();
        foreach ($order_ids as $val) {
            if ($detail = D('Order')->find($val)) {
                if ($detail['status'] == 1 || ($detail['staus'] == 0 && $detail['is_daofu'] == 1  && $detail['shop_id'] == $this->shop_id)) {
                    $local[$val] = $val;
                }
            }
        }
        if (empty($local)) {
            $this->tuError('请选择要加入捡货的订单');
        }

        $data = array(
            'admin_id' => 0,
            'shop_id' => $this->shop_id,
            'create_time' => NOW_TIME,
            'create_ip' => get_client_ip(),
            'order_ids' => join(',', $local),
            'name' => '捡货单' . date('Y-m-d H:i:s'),
        );
        if ($pick_id = D('Orderpick')->add($data)) {
            D('Order')->save(array('status' => 2), array("where" => array('order_id' => array('IN', $local))));
            D('Ordergoods')->save(array('status' => 1), array("where" => array('order_id' => array('IN', $local))));
            session('order', null);
            $this->tuSuccess('创建捡货单成功', U('order/pickdetail', array('pick_id' => $pick_id)));
        }
        $this->tuError('创建捡货单失败');
    }
    
    
      public function pickdetail($pick_id) {
        $pick_id = (int) $pick_id;
        $pick = D('Orderpick')->find($pick_id);
        if($pick['shop_id'] != $this->shop_id){
            $this->error('请不要恶意操作其他人的订单');
        }
        $orderids = explode(',', $pick['order_ids']);

        $Order = D('Order');
        import('ORG.Util.Page'); 
        $map = array('order_id' => array('IN', $orderids));
        $list = $Order->where($map)->order(array('order_id' => 'asc'))->select();
        $user_ids = $order_ids = $addr_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
			$address_ids[$val['address_id']] = $val['address_id'];
        }
        if (!empty($order_ids)) {
            $goods = D('Ordergoods')->where(array('order_id' => array('IN', $order_ids)))->select();
            $goods_ids  = array();
            foreach ($goods as $val) {
                $goods_ids[$val['goods_id']] = $val['goods_id'];
            }
            $this->assign('goods', $goods);
            $this->assign('products', D('Goods')->itemsByIds($goods_ids));
        }
        $this->assign('addrs', D('Paddress')->itemsByIds($address_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('types', D('Order')->getType());
        $this->assign('goodtypes', D('Ordergoods')->getType());
        $this->display();
    }
    
    public function count(){
        $dvo = D('DeliveryOrder'); 
        $bg_date = strtotime(I('bg_date',0,'trim'));
        $end_date = strtotime(I('end_date',0,'trim'));
        $this->assign('btime',$bg_date);
        $this->assign('etime',$end_date);
        if($bg_date && $end_date){
            $pre_btime = date('Y-m-d H:i:s',$bg_date);
            $pre_etime = date('Y-m-d H:i:s',$end_date);
            $this->assign('pre_btime',$pre_btime);
            $this->assign('pre_etime',$pre_etime);
        }
        $map = array();
        $map['shop_id'] = $this->shop_id;
        $map['type'] = 0;
        if($bg_date && $end_date){
           $map['create_time'] = array('between',array($bg_date,$end_date)); 
        }
        import('ORG.Util.Page');
        $count = $dvo->where($map)->count();
        $Page  = new Page($count,25);
        $show = $Page->show();
        $list = $dvo->where($map)->order('order_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $k => $v){
            if($Delivery = D('Delivery')->where(array('user_id'=>$v['delivery_id']))->find()){
                $list[$k]['delivery'] = $Delivery;
            }
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display();
        
    }
    
    
    function delivery_count(){
        $delivery_id = I('did',0,'intval,trim');
        $btime = I('btime',0,'trim');
        $etime = I('etime',0,'trim');
        $map = array();
        if($btime && $etime){
           $map['create_time'] = array('between',array($btime,$etime)); 
        }
  
        if(!$delivery_id || !($this->shop_id)){
            $this->ajaxReturn(array('status'=>'error','message'=>'错误'));
        }else{
            $map['delivery_id'] = $delivery_id;
            $map['shop_id'] = $this->shop_id;
            $map['type'] = 0;
            $count = D('DeliveryOrder') ->where($map)-> count();
            if($count){
                $this->ajaxReturn(array('status'=>'success','count'=>$count));
            }else{
                $this->ajaxReturn(array('status'=>'error','message'=>'错误'));
            }
        }
    }
    
    
    public function picks() {
        if(empty($this->shop['is_pei'])){
        }
        $Orderpick = D('Orderpick');
        import('ORG.Util.Page'); 
        $map = array('shop_id'=>  $this->shop_id);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars') ) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('keyword', $keyword);
        $count = $Orderpick->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Orderpick->where($map)->order('pick_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('keyword', $keyword);
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

	public function send($pick_id) {
        $pick_id = (int) $pick_id;
        $pick = D('Orderpick')->find($pick_id);
        $orderids = explode(',', $pick['order_ids']);
        if($pick['shop_id'] != $this->shop_id){
            $this->error('请不要恶意操作其他人的订单');
        }
        $Order = D('Order');
        import('ORG.Util.Page'); 
        $map = array('order_id' => array('IN', $orderids));

        $list = $Order->where($map)->order(array('order_id' => 'asc'))->select();

        $user_ids = $order_ids  = $addr_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
			$address_ids[$val['address_id']] = $val['address_id'];
        }
        if (!empty($order_ids)) {
            $goods = D('Ordergoods')->where(array('order_id' => array('IN', $order_ids)))->select();
            $goods_ids = array();
            foreach ($goods as $val) {
                $goods_ids[$val['goods_id']] = $val['goods_id'];
            }
            $this->assign('goods', $goods);
            $this->assign('products', D('Goods')->itemsByIds($goods_ids));
        }
        $this->assign('addrs', D('Paddress')->itemsByIds($address_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('types', D('Order')->getType());
        $this->assign('goodtypes', D('Ordergoods')->getType());
        $this->assign('list', $list);
        $this->display();
    }

    public function distribution() {
        $order_id = (int) $this->_get('order_id');
        $config = D('Setting')->fetchAll();
        $days = isset($config['site']['goods']) ? (int)$config['site']['goods'] : 15;
        $t = NOW_TIME - $days*86400;
        if (!$order_id) {
            $this->tuError('参数错误');
        }else if(!$order = D('Order')->find($order_id)){
            $this->tuError('该订单不存在');
        }else if($order['shop_id'] != $this->shop_id){
            $this->tuError('不能管理不是您的订单');
        }else if(($order['status'] != 2) && ($order['status']!=3)){
            $this->tuError('该订单状态不正确，不能发货');
        }else{
            D('Order')->overOrder($order_id); //发货订单接口
            $this->tuSuccess('确认订单完成，资金已结算', U('order/delivery'));
        }		
        $this->tuError('确认订单失败');
    }
	
	 //只支持单个退款
    public function refund($order_id = 0){
        $order_id = (int) $order_id;
		$order = D('Order');
        $detail = $order->find($order_id);
        if ($detail['is_daofu'] == 0) {
            if ($detail['status'] != 4) {
                $this->tuError('操作错误');
            }
			if($detail['shop_id'] != $this->shop_id){
            	$this->tuError('请不要恶意操作其他人的订单');
       		}
			if(false !== $order->implemented_refund($order_id)){
               $this->tuSuccess('退款成功', U('order/wait_refunded'));
            }else{
                $this->tuError('退款失败');
            }
        } else {
            $this->tuError('当前订单状态不正确');
        }
    }

	 public function express($order_id = 0){
		$data = $_POST;
        $order_id = $data['order_id'];
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        if (!($detail = D('Order')->find($order_id))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '没有该订单'.$order_id));
        }
        if ($detail['closed'] != 0) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该订单已经被删除'));
        }
		if ($detail['status'] == 2 || $detail['status'] == 3 || $detail['status'] == 8 || $detail['status'] == 4 || $detail['status'] == 5) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该订单状态不正确，不能发货'));
        }
		$express_id = $data['express_id'];
		if (empty($express_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '请选择快递'));
        }
		if (!($detail = D('Logistics')->find($express_id))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '没有'.$detail['express_name'].'快递'));
        }
		if ($detail['closed'] != 0) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该快递已关闭'));
        }
		$express_number = $data['express_number'];
        if (empty($express_number)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '快递单号不能为空'));
        }
        $add_express = array(
				'order_id' => $order_id,
				'express_id' => $express_id, 
				'express_number' => $express_number 
		);
		if(D('Order')->save($add_express)){
			D('Order')->pc_express_deliver($order_id);//执行发货
			D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 2,$status = 2);
			D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 2,$status = 2);
			$this->ajaxReturn(array('status' => 'success', 'msg' => '一键发货成功', 'url' => U('order/wait')));
		}else{
			$this->ajaxReturn(array('status' => 'error', 'msg' => '发货失败'));	
		}
	}
	
	
	 public function detail($order_id){
        $order_id = (int) $order_id;
        if (empty($order_id) || !($detail = D('Order')->find($order_id))) {
            $this->error('该订单不存在');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->error('请不要操作其他商家的订单');
        }
        $order_goods = D('Ordergoods')->where(array('order_id' => $order_id))->select();
        $goods_ids = array();
        foreach ($order_goods as $k => $val) {
            $goods_ids[$val['goods_id']] = $val['goods_id'];
        }
        if (!empty($goods_ids)) {
            $this->assign('goods', D('Goods')->itemsByIds($goods_ids));
        }
		$data = D('Logistics')->get_order_express($order_id);//查询清单物流
		$this->assign('data', $data);
        $this->assign('ordergoods', $order_goods);
		$this->assign('users', D('Users')->find($detail['user_id']));
        $this->assign('Paddress', D('Paddress')->find($detail['address_id']));
		$this->assign('logistics', D('Logistics')->find($detail['express_id']));
        $this->assign('types', D('Order')->getType());
        $this->assign('goodtypes', D('Ordergoods')->getType());
        $this->assign('detail', $detail);
        $this->display();
    }
	
	
	 public function profit($order_id){
        $order_id = (int) $order_id;
        if (empty($order_id) || !($Order = D('Order')->find($order_id))) {
            $this->error('该订单不存在');
        }
        if ($Order['shop_id'] != $this->shop_id) {
            $this->error('请不要操作其他商家的订单');
        }
		$list = D('Userprofitlogs')->where(array('order_id' => $order_id))->select();
		$user_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }		
        $this->assign('users', D('Users')->itemsByIds($user_ids));
		$this->assign('profit_price',$profit_price = D('Userprofitlogs')->where(array('order_id' => $order_id))->sum('money'));
		$this->assign('list', $list);
		$this->assign('detail', $Order);
        $this->display();
    }
	
	//改价
	public function changePrice($order_id = 0){
		$data = $_POST;
        $order_id = $data['order_id'];
        if(empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        if(!($detail = D('Order')->find($order_id))){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '没有该订单'.$order_id));
        }
        if($detail['closed'] != 0){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该订单已经被删除'));
        }
		if ($detail['status'] != 0 ) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '订单状态不正确，不支持改价'));
        }
		$change_price = ($data['change_price'])*100;
		if($change_price <= 1){
           $this->ajaxReturn(array('status' => 'error', 'msg' => '修改的价格有误'));
        }
		if(false != D('OrderChangeLogs')->orderChangePrice($order_id,$change_price)){
			$this->ajaxReturn(array('status' => 'success', 'msg' => '改价成功', 'url' => U('order/index',array('st'=>0))));
		}else{
			$this->ajaxReturn(array('status' => 'error', 'msg' => D('OrderChangeLogs')->getError()));	
		}
	}
	
	
}
