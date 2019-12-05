<?php
class EleAction extends CommonAction{
    private $create_fields = array('shop_id', 'cate', 'distribution', 'is_open', 'is_pay', 'is_fan', 'fan_money', 'is_new', 'full_money', 'new_money', 'logistics', 'since_money', 'sold_num', 'month_num', 'intro', 'audit', 'orderby', 'rate');
    private $edit_fields = array('is_open', 'cate', 'distribution', 'is_pay', 'is_fan', 'fan_money', 'is_new', 'full_money', 'new_money', 'logistics', 'since_money', 'sold_num', 'month_num', 'intro', 'orderby', 'audit', 'rate');
    public function _initialize(){
        parent::_initialize();
        $getEleCate = D('Ele')->getEleCate();
        $this->assign('getEleCate', $getEleCate);
    }
    public function index(){
        $Ele = D('Ele');
        import('ORG.Util.Page');// 导入分页类 二开qq 120--585--022   www.juhucms.com
        $map = array('city_id' => $this->city_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Shopcate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        $count = $Ele->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Ele->where($map)->order(array('shop_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('cates', D('Shopcate')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Ele');
            $cate = $this->_post('cate', false);
            $cate = implode(',', $cate);
            $data['cate'] = $cate;
            if ($obj->add($data)) {
                $this->tuSuccess('添加成功', U('ele/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->tuError('ID不能为空');
        }
        if (!($shop = D('Shop')->find($data['shop_id']))) {
            $this->tuError('商家不存在');
        }
        $data['shop_name'] = $shop['shop_name'];
        $data['lng'] = $shop['lng'];
        $data['lat'] = $shop['lat'];
        $data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        $data['business_id'] = $shop['business_id'];
        $data['is_open'] = (int) $data['is_open'];
        $data['is_pay'] = (int) $data['is_pay'];
        $data['is_fan'] = (int) $data['is_fan'];
        $data['fan_money'] = (int) ($data['fan_money'] * 100);
        $data['is_new'] = (int) $data['is_new'];
        $data['full_money'] = (int) ($data['full_money'] * 100);
        $data['new_money'] = (int) ($data['new_money'] * 100);
        $data['logistics'] = (int) ($data['logistics'] * 100);
        $data['since_money'] = (int) ($data['since_money'] * 100);
        $data['sold_num'] = (int) $data['sold_num'];
        $data['month_num'] = (int) $data['month_num'];
        $data['rate'] = (int) $data['rate'];
        $data['audit'] = (int) $data['audit'];
        $data['distribution'] = (int) $data['distribution'];
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('说明不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function edit($shop_id = 0) {
        if ($shop_id = (int) $shop_id) {
     
			
            $obj = D('Ele');
            if (!($detail = $obj->find($shop_id))) {
                $this->tuError('请选择要编辑的餐饮商家');
            }
			 if ($detail['city_id'] != $this->city_id) {
                $this->error('非法操作', U('ele/index'));
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['shop_id'] = $shop_id;
                $cate = $this->_post('cate', false);
                $cate = implode(',', $cate);
                $data['cate'] = $cate;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('ele/index'));
                }
                $this->tuError('操作失败');
            } else {
                $cate = explode(',', $detail['cate']);
                $this->assign('cate', $cate);
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的餐饮商家');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['is_open'] = (int) $data['is_open'];
        $data['is_pay'] = (int) $data['is_pay'];
        $data['is_fan'] = (int) $data['is_fan'];
        $data['fan_money'] = (int) ($data['fan_money'] * 100);
        $data['is_new'] = (int) $data['is_new'];
        $data['full_money'] = (int) ($data['full_money'] * 100);
        $data['new_money'] = (int) ($data['new_money'] * 100);
        $data['logistics'] = (int) ($data['logistics'] * 100);
        $data['since_money'] = (int) ($data['since_money'] * 100);
        $data['sold_num'] = (int) $data['sold_num'];
        $data['month_num'] = (int) $data['month_num'];
        $data['distribution'] = (int) $data['distribution'];
        $data['audit'] = (int) $data['audit'];
        $data['intro'] = htmlspecialchars($data['intro']);
        $data['rate'] = (int) $data['rate'];
        if (empty($data['intro'])) {
            $this->tuError('说明不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function delete($shop_id = 0){
        if (is_numeric($shop_id) && ($shop_id = (int) $shop_id)) {
			
            $obj = D('Ele');
			if (!($detail = $obj->find($shop_id))) {
                $this->tuError('请选择要编辑的餐饮商家');
            }
			 if ($detail['city_id'] != $this->city_id) {
                $this->error('非法操作', U('ele/index'));
            }

            $obj->delete($shop_id);
            $this->tuSuccess('删除成功', U('ele/index'));
        } else {
            $shop_id = $this->_post('shop_id', false);
            if (is_array($shop_id)) {
                $obj = D('Ele');
				$obj = D('Ele');
				if (!($detail = $obj->find($shop_id))) {
					$this->tuError('请选择要编辑的餐饮商家');
				}
				 if ($detail['city_id'] != $this->city_id) {
					$this->error('非法操作', U('ele/index'));
				}
			
                foreach ($shop_id as $id) {
                    $obj->delete($id);
                }
                $this->tuSuccess('删除成功', U('ele/index'));
            }
            $this->tuError('请选择要删除的餐饮商家');
        }
    }
    public function opened($shop_id = 0, $type = 'open'){
        if (is_numeric($shop_id) && ($shop_id = (int) $shop_id)) {
            $obj = D('Ele');
			$obj = D('Ele');
			if (!($detail = $obj->find($shop_id))) {
                $this->tuError('请选择要编辑的餐饮商家');
            }
			 if ($detail['city_id'] != $this->city_id) {
                $this->error('非法操作', U('ele/index'));
            }
            $is_open = 0;
            if ($type == 'open') {
                $is_open = 1;
            }
            $obj->save(array('shop_id' => $shop_id, 'is_open' => $is_open));
            $this->tuSuccess('操作成功', U('ele/index'));
        }
    }
    public function select(){
        $ele = D('Ele');
        import('ORG.Util.Page');
        $map = array('audit' => 1, 'city_id' => $this->city_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name|intro'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $ele->where($map)->count();
        $Page = new Page($count, 10);
        $pager = $Page->show();
        $list = $ele->where($map)->order(array('shop_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $pager);
        $this->display();
    }
	
	public function delivery(){
		import('ORG.Util.Page');
		$map = array();
		if($keyword = $this->_param('keyword','htmlspecialchars')){
			$map['name|mobile|addr|user_id'] = array('LIKE','%'.$keyword.'%');
			$this->assign('keyword',$keyword);
		}
		//时间筛选
		if(($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))){
			$bg_time = strtotime($bg_date);
			$end_time = strtotime($end_date);
			$condition['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
			$condition2['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
			$this->assign('bg_date', $bg_date);
			$this->assign('end_date', $end_date);
		}else{
			if($bg_date = $this->_param('bg_date', 'htmlspecialchars')){
				$bg_time = strtotime($bg_date);
				$this->assign('bg_date', $bg_date);
				$condition['create_time'] = array('EGT', $bg_time);
				$condition2['create_time'] = array('EGT', $bg_time);
			}
		if($end_date = $this->_param('end_date', 'htmlspecialchars')){
				$end_time = strtotime($end_date);
				$this->assign('end_date', $end_date);
				$condition['create_time'] = array('ELT', $end_time);
				$condition2['create_time'] = array('ELT', $end_time);
			}
		}
		
		$count = D('Delivery')->where($map)->count(); 
		$Page = new Page($count,8); 
		$pager = $Page->show();
		$list = D('Delivery')->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val){
			
			$condition['user_id'] = $val['user_id'];
			$condition['type'] = 'ele';
			
			$condition2['delivery_id'] = $val['id'];
			$condition2['closed'] = '0';
			
			$list[$k]['price'] = D('Runningmoney')->where($condition)->sum('money');
			$list[$k]['num'] = D('DeliveryOrder')->where($condition2)->count();
		}
		$this->assign('list', $list);
		$this->assign('page', $pager); 
		
		//今日配送结算逻辑
		$bg_time = strtotime(TODAY);
		$counts['money_day'] = (int) D('Runningmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->sum('money');
		$counts['delivery_order_day'] = (int) D('DeliveryOrder')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'closed'=>0))->count();
		$counts['delivery_logistics_price'] = (int) D('DeliveryOrder')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'closed'=>0))->sum('logistics_price');
		
		
		$this->assign('counts', $counts);
		$this->display();	
	}
	//配送详情
	public function delivery_detail(){
		$id = I('id', '', 'intval,trim');
        if(!$id){
            $this->tuError('没有选择');
        }else{
			$Delivery = D('Delivery')->where('id =' . $id)->find();
			$users = D('Users')->find($Delivery['user_id']);
            $this->assign('delivery', D('Delivery')->where('id =' . $id)->find());
            import('ORG.Util.Page');
			//$map['shop_id'] = $this->shop_ids;
			if($order_id = (int) $this->_param('order_id')){
				$map['order_id'] = $order_id;
				$this->assign('order_id', $order_id);
			}
			if(isset($_GET['status']) || isset($_POST['status'])){
				$status = (int) $this->_param('status');
				if($status != 999){
					$map['status'] = $status;
				}
				$this->assign('status', $status);
			}else{
				$this->assign('status', 999);
			}
			
            $count = D('DeliveryOrder')->where('delivery_id =' . $users['user_id'])->count();
            $Page = new Page($count, 25);
            $show = $Page->show();
            $list = D('DeliveryOrder')->where('delivery_id =' . $users['user_id'])->order('order_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $this->assign('list', $list);
            $this->assign('page', $show);
			$this->assign('id', $id);
			
			
		//今日配送结算逻辑
		$bg_time = strtotime(TODAY);
		$counts['money_day'] = (int) D('Runningmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->sum('money');
		$counts['delivery_order_day'] = (int) D('DeliveryOrder')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'closed'=>0))->count();
		$counts['delivery_logistics_price'] = (int) D('DeliveryOrder')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'closed'=>0))->sum('logistics_price');
		
		
			$this->display();	
		}
	}
	
	public function shop(){
		import('ORG.Util.Page');
		$map = array();
		if($keyword = $this->_param('keyword','htmlspecialchars')){
			$map['shop_name'] = array('LIKE','%'.$keyword.'%');
			$this->assign('keyword',$keyword);
		}
		
		if(($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))){
			$bg_time = strtotime($bg_date);
			$end_time = strtotime($end_date);
			$condition['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
			$this->assign('bg_date', $bg_date);
			$this->assign('end_date', $end_date);
		}else{
			if($bg_date = $this->_param('bg_date', 'htmlspecialchars')){
				$bg_time = strtotime($bg_date);
				$this->assign('bg_date', $bg_date);
				$condition['create_time'] = array('EGT', $bg_time);
			}
		if($end_date = $this->_param('end_date', 'htmlspecialchars')){
				$end_time = strtotime($end_date);
				$this->assign('end_date', $end_date);
				$condition['create_time'] = array('ELT', $end_time);
			}
		}
			
			
		$count = D('Ele')->where($map)->count(); 
		$Page = new Page($count,8); 
		$page = $Page->show();
		$list = D('Ele')->where($map)->order(array('shop_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val){
			
			$condition['shop_id'] = $val['shop_id'];
			$condition['closed'] = '0';
			$condition['status'] = array('gt',0);
			
			$list[$k]['need_pay'] = D('Eleorder')->where($condition)->sum('need_pay');
			$list[$k]['num'] = D('Eleorder')->where($condition)->count();
			$list[$k]['order_logistics'] = D('Eleorder')->where($condition)->sum('logistics');
			$list[$k]['total_price'] = D('Eleorder')->where($condition)->sum('total_price');
		}
		$this->assign('list', $list);
		$this->assign('page', $page); 
		
		//今日外卖订单
		$bg_time = strtotime(TODAY);
		$counts['ele_order_need_pay'] = (int) D('Eleorder')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'closed'=>'0','status'=>array('gt',0)))->sum('need_pay');
		$counts['ele_order_count'] = (int) D('Eleorder')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'closed'=>0,'status'=>array('gt',0)))->count();
		$counts['ele_order_logistics'] = (int) D('Eleorder')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'closed'=>0,'status'=>array('gt',0)))->sum('logistics');
		
		
		$this->display();		
	}
	
	public function shop_detail($shop_id = 0){
		$shop_id = I('shop_id', '', 'intval,trim');
        if(!$shop_id){
            $this->tuError('没有选择');
        }else{
			import('ORG.Util.Page');
			$map = array('shop_id'=>$shop_id);
			if($keyword = $this->_param('keyword','htmlspecialchars')){
				$map['shop_name'] = array('LIKE','%'.$keyword.'%');
				$this->assign('keyword',$keyword);
			}
			if(($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))){
				$bg_time = strtotime($bg_date);
				$end_time = strtotime($end_date);
				$map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
				$this->assign('bg_date', $bg_date);
				$this->assign('end_date', $end_date);
			}else{
				if($bg_date = $this->_param('bg_date', 'htmlspecialchars')){
					$bg_time = strtotime($bg_date);
					$this->assign('bg_date', $bg_date);
					$map['create_time'] = array('EGT', $bg_time);
				}
				if($end_date = $this->_param('end_date', 'htmlspecialchars')){
					$end_time = strtotime($end_date);
					$this->assign('end_date', $end_date);
					$map['create_time'] = array('ELT', $end_time);
				}
			}
			$count = D('Eleorder')->where($map)->count(); 
			$Page = new Page($count,8); 
			$page = $Page->show();
			$list = D('Eleorder')->where($map)->order(array('shop_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
			$user_ids = $shop_ids = array();
			foreach ($list as $k => $val) {
				$user_ids[$val['user_id']] = $val['user_id'];
				$shop_ids[$val['shop_id']] = $val['shop_id'];
			}
			
			$this->assign('shops', D('Shop')->itemsByIds($shop_ids));
			$this->assign('users', D('Users')->itemsByIds($user_ids));
			$this->assign('cfg', D('Eleorder')->getCfg());
			$this->assign('list', $list);
			$this->assign('page', $page); 
			$this->assign('shop_id', $shop_id);
			$this->display();	
		}
	}
	
	//打印
	public function shop_print($shop_id = 0){
		$shop_id = I('shop_id', '', 'intval,trim');
        if(!$shop_id){
            $this->tuError('没有选择');
        }else{
			$map['shop_id'] = $shop_id;
			$detail = D('Ele')->where($map)->find();
				
			$condition['shop_id'] = $shop_id;
			$condition['closed'] = '0';
			$condition['status'] = array('gt',0);
				
			$detail['need_pay'] = D('Eleorder')->where($condition)->sum('need_pay');
			$detail['num'] = D('Eleorder')->where($condition)->count();
			$detail['order_logistics'] = D('Eleorder')->where($condition)->sum('logistics');
			$detail['total_price'] = D('Eleorder')->where($condition)->sum('total_price');
				
			$this->assign('detail', $detail);
			$this->assign('shop_id', $shop_id);
			$this->display();	
		}
	}
		
}