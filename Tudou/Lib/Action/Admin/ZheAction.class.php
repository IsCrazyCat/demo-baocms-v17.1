<?php
class ZheAction extends CommonAction {

    private $create_fields = array('shop_id','city_id','area_id','zhe_name','cate_id', 'photo','bg_date','end_date','week_id','date_id', 'walkin', 'person', 'limit', 'description','credit','orderby', 'views','content');
    private $edit_fields = array('shop_id','city_id','area_id','zhe_name','cate_id', 'photo','bg_date','end_date','week_id','date_id', 'walkin', 'person', 'limit', 'description','credit','orderby', 'views','content');
	private $order_create_fields = array('city_id','city_id','status','type','need_pay', 'user_id', 'number','start_time','end_time'); 
    public function _initialize() {
        parent::_initialize();
        $this->getZheWeek = D('Zhe')->getZheWeek();
        $this->assign('weeks',  $this->getZheWeek);
        $this->getZheDate = D('Zhe')->getZheDate();
        $this->assign('dates',  $this->getZheDate);
		$this->assign('cates', D('Shopcate')->fetchAll());
		$this->assign('zhe_city', D('City')->fetchAll());
    }

    
    public function index() {
        $Zhe = D('Zhe');
        import('ORG.Util.Page'); 
        $map = array('closed' =>0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['hotel_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
		if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $Zhe->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Zhe->where($map)->order(array('zhe_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$shop_ids = array();
        foreach($list as $k => $val){
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        if(!empty($shop_ids)){
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
	//添加五折卡
    public function create() {
        $obj = D('Zhe');
        if ($this->isPost()) {
            $data = $this->createCheck();
			$week_id = $this->_post('week_id', false);
            $week_id = implode(',', $week_id);
            $data['week_id'] = $week_id;
			
			$date_id = $this->_post('date_id', false);
            $date_id = implode(',', $date_id);
            $data['date_id'] = $date_id;
			
            if ($zhe_id = $obj->add($data)) {
                $this->tuSuccess('操作成功', U('zhe/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
       
    }
    //添加五折卡验证
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
	    $data['shop_id'] = (int)$data['shop_id'];
        if(empty($data['shop_id'])){
            $this->tuError('商家不能为空');
        }
		if(!$shop = D('Shop')->find($data['shop_id'])){
            $this->tuError('商家不存在');
        }
		
		
		$list = D('Zhe')->where(array('shop_id'=>$data['shop_id'],'closed'=>'1'))->select();//先删除添加
		foreach($list as $k => $val) {
             D('Zhe')->where(array('zhe_id'=>$val['zhe_id']))->delete();
        }
		$count = D('Zhe')->where(array('shop_id'=>$data['shop_id'],'closed'=>'0'))->count();
		if($count){
            $this->tuError('该商家已存在无折卡');
        }
		
		$data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        
        $data['zhe_name'] = htmlspecialchars($data['zhe_name']);
        if (empty($data['zhe_name'])) {
            $this->tuError('五折卡名称不能为空');
        }
		$data['cate_id'] = (int)$data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->tuError('五折卡分类没有选择');
        }
		$data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['bg_date'])) {
            $this->tuError('开始时间不能为空');
        }
        if (!isDate($data['bg_date'])) {
            $this->tuError('开始时间格式不正确');
        } $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->tuError('结束时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->tuError('结束时间格式不正确');
        }
		$data['walkin'] = (int)$data['walkin'];
		$data['person'] = htmlspecialchars($data['person']);
		$data['limit'] = (int)$data['limit'];
		$data['description'] = SecurityEditorHtml($data['description']);
        if (empty($data['description'])) {
            $this->tuError('五折卡说明不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['description'])) {
            $this->tuError('五折卡说明含有敏感词：' . $words);
        }
		$data['credit'] = (int)$data['credit'];
		$data['views'] = (int)$data['views'];
		$data['orderby'] = (int)$data['orderby'];
		$data['content'] = SecurityEditorHtml($data['content']);
        if (empty($data['content'])) {
            $this->tuError('五折卡详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['content'])) {
            $this->tuError('五折卡详情含有敏感词：' . $words);
        } 
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['audit'] = 1;
        return $data;
    }
    
    //编辑五折卡商家
    public function edit($zhe_id = 0) {
        if ($zhe_id = (int) $zhe_id) {
            $obj = D('Zhe');
            if (!$detail = $obj->find($zhe_id)) {
                $this->tuError('请选择要编辑的五折卡');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['zhe_id'] = $zhe_id;
				
				$week_id = $this->_post('week_id', false);
				$week_id = implode(',', $week_id);
				$data['week_id'] = $week_id;
				
				$date_id = $this->_post('date_id', false);
				$date_id = implode(',', $date_id);
				$data['date_id'] = $date_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('zhe/index'));
                }
                $this->tuError('操作失败');
            } else {
				$this->assign('shop',D('Shop')->find($detail['shop_id']));
                $this->assign('week_ids', $week_ids = explode(',', $detail['week_id']));
				$this->assign('date_ids', $date_ids = explode(',', $detail['date_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的五折卡');
        }
    }
    //编辑五折卡验证
    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['shop_id'] = (int)$data['shop_id'];
        if(empty($data['shop_id'])){
            $this->tuError('商家不能为空');
        }
		if(!$shop = D('Shop')->find($data['shop_id'])){
            $this->tuError('商家不存在');
        }
		
		$count = D('Zhe')->where(array('shop_id'=>$data['shop_id'],'closed'=>'0'))->count();
		if($count > 1){
            $this->tuError('该商家已存在无折卡');
        }
		
		$data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        
        $data['zhe_name'] = htmlspecialchars($data['zhe_name']);
        if (empty($data['zhe_name'])) {
            $this->tuError('五折卡名称不能为空');
        }
		$data['cate_id'] = (int)$data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->tuError('五折卡分类没有选择');
        }
		$data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['bg_date'])) {
            $this->tuError('开始时间不能为空');
        }
        if (!isDate($data['bg_date'])) {
            $this->tuError('开始时间格式不正确');
        } $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->tuError('结束时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->tuError('结束时间格式不正确');
        }
		$data['walkin'] = (int)$data['walkin'];
		$data['person'] = htmlspecialchars($data['person']);
		$data['limit'] = (int)$data['limit'];
		$data['description'] = SecurityEditorHtml($data['description']);
        if (empty($data['description'])) {
            $this->tuError('五折卡说明不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['description'])) {
            $this->tuError('五折卡说明含有敏感词：' . $words);
        }
		$data['credit'] = (int)$data['credit'];
		$data['views'] = (int)$data['views'];
		$data['orderby'] = (int)$data['orderby'];
		$data['content'] = SecurityEditorHtml($data['content']);
        if (empty($data['content'])) {
            $this->tuError('五折卡详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['content'])) {
            $this->tuError('五折卡详情含有敏感词：' . $words);
        } 
        return $data;
    }
	//添加五折卡订单
	public function order_create() {
            if ($this->isPost()) {
                $data = $this->order_createCheck();
                if ($order_id = D('Zheorder')->add($data)) {
					D('Sms')->sms_zhe_notice_user($order_id);//购买五折卡成功通知买家，不用通知网站了
                    $this->tuSuccess('操作成功', U('zhe/order'));
                }
                $this->tuError('操作失败');
            } else {
                $this->display();
            }
    }
    //添加五折卡订单验证
	
    private function order_createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->order_create_fields);
		$data['city_id'] = $data['city_id'];
		$data['type'] = (int)$data['type'];
		$data['need_pay'] = (int) ($data['need_pay'] * 100);
		if ($data['need_pay'] <= 10) {
            $this->tuError('请输入正确的金额');
        }
        $data['user_id'] = (int) $data['user_id'];
		if (empty($data['user_id'])) {
            $this->tuError('用户不能为空');
        }
		if(D('Zheorder')->where(array('user_id'=>$data['user_id'],'closed'=>0,'status'=>1,'end_time' => array('EGT', NOW_TIME)))->find()){
		   $this->tuError('该用户已经用五折卡了');
		}
		$data['number'] = (int)$data['number'];
		if (empty($data['number'])) {
            $this->tuError('请输入8位数编码');
        }
		if ($data['number'] < 8) {
            $this->tuError('五折卡编码必须大于等于8位数');
        }
		if(D('Zheorder')->where(array('number'=>$data['number']))->find()){
		   $this->tuError('五折卡编码重复');
		}
		$data['start_time'] = strtotime(htmlspecialchars($data['start_time']));
        if (empty($data['start_time'])) {
            $this->tuError('五折卡开始时间不能为空');
        }
		$data['end_time'] = strtotime(htmlspecialchars($data['end_time']));
        if (empty($data['end_time'])) {
            $this->tuError('五折卡结束时间不能为空');
        }
		if ($data['end_time'] <= $data['start_time']) {
            $this->tuError('开始时间不能大于结束时间');
        }
		$data['status'] = 1;
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    
    //删除五折卡
    public function delete($zhe_id = 0) {
        $obj = D('Zhe');
        if (is_numeric($zhe_id) && ($zhe_id = (int) $zhe_id)) {
            $obj->save(array('zhe_id' => $zhe_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('zhe/index'));
        } else {
            $zhe_id = $this->_post('zhe_id', false);
            if (is_array($zhe_id)) {
                foreach ($zhe_id as $id) {
                    $obj->save(array('zhe_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('删除成功', U('zhe/index'));
            }
            $this->tuError('请选择要删除的五折卡');
        }
    }
	
	//删除五折卡
	public function delete2($yuyue_id = 0) {
        $obj = D('Zhe');
        if($zhe_id = (int) $zhe_id){
            $obj->where(array('zhe_id'=>$zhe_id))->delete();
            $this->tuSuccess('彻底删除成功', U('zhe/index'));
        }else{
            $this->tuError('ID不存在');
        }
    }
	
	
	//审核五折卡
    public function audit($zhe_id = 0) {
        $obj = D('Zhe');
        if (is_numeric($zhe_id) && ($zhe_id = (int) $zhe_id)) {
            $obj->save(array('zhe_id' => $zhe_id, 'audit' => 1));
            $this->tuSuccess('审核成功', U('zhe/index'));
        } else {
            $zhe_id = $this->_post('zhe_id', false);
            if (is_array($zhe_id)) {
                foreach ($zhe_id as $id) {
                    $obj->save(array('zhe_id' => $id, 'audit' => 1));
                }
                $this->tuSuccess('批量审核成功', U('zhe/index'));
            }
            $this->tuError('请选择要审核的五折卡');
        }
    }

     public function order() {
        $Zheorder = D('Zheorder');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0);
        if ($order_id = (int) $this->_param('order_id')) {
            $map['order_id'] = $order_id;
            $this->assign('order_id', $order_id);
        }
		 if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		if (($bg_date = $this -> _param('bg_date', 'htmlspecialchars')) && ($end_date = $this -> _param('end_date', 'htmlspecialchars'))) {
			$bg_time = strtotime($bg_date);
			$end_time = strtotime($end_date);
			$map['create_time'] = array( array('ELT', $end_time), array('EGT', $bg_time));
			$this -> assign('bg_date', $bg_date);
			$this -> assign('end_date', $end_date);
		} else {
			if ($bg_date = $this -> _param('bg_date', 'htmlspecialchars')) {
				$bg_time = strtotime($bg_date);
				$this -> assign('bg_date', $bg_date);
				$map['create_time'] = array('EGT', $bg_time);
			}
			if ($end_date = $this -> _param('end_date', 'htmlspecialchars')) {
				$end_time = strtotime($end_date);
				$this -> assign('end_date', $end_date);
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
        $count = $Zheorder->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Zheorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids  = $city_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			$city_ids[$val['city_id']] = $val['city_id'];
        }
		session('zhe_order_map', $map);//存储session
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
		$this->assign('citys', D('City')->itemsByIds($city_ids));
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
	//编辑五折卡订单
	 public function order_edit($order_id = 0) {
        $order_id = (int) $order_id;
		if(!$detail = D('Zheorder')->find($order_id)){
			$this->tuError('订单不存在');
		}else{
			if ($this->isPost()) {
				$data = $this->checkFields($this->_post('data', false), array('end_time'));
                $data['order_id'] = $order_id;
				$data['end_time'] = strtotime(htmlspecialchars($data['end_time']));
                if (false !== D('Zheorder')->save($data)) {
                    $this->tuSuccess('操作成功', U('zhe/order'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
		}
    }
	//删除五折卡订单
	 public function order_delete($order_id = 0) {
        $order_id = (int) $order_id;
		if(!$detail = D('Zheorder')->find($order_id)){
			$this->tuError('订单不存在');
		}else{
			if($detail['status'] == 1){
				if($detail['end_time'] >= time()){
					$this->tuError('该五折卡没到过期时间');
				}
			}
			D('Zheorder')->save(array('order_id' => $order_id, 'closed' => 1));
			$this->tuSuccess('删除成功', U('zhe/order'));
		}
    }
	
	 public function yuyue() {
        $Zheyuyue = D('Zheyuyue');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0);
        if ($yuyue_id = (int) $this->_param('yuyue_id')) {
            $map['yuyue_id'] = $oyuyue_id;
            $this->assign('yuyue_id', $yuyue_id);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		if (($bg_date = $this -> _param('bg_date', 'htmlspecialchars')) && ($end_date = $this -> _param('end_date', 'htmlspecialchars'))) {
			$bg_time = strtotime($bg_date);
			$end_time = strtotime($end_date);
			$map['create_time'] = array( array('ELT', $end_time), array('EGT', $bg_time));
			$this -> assign('bg_date', $bg_date);
			$this -> assign('end_date', $end_date);
		} else {
			if ($bg_date = $this -> _param('bg_date', 'htmlspecialchars')) {
				$bg_time = strtotime($bg_date);
				$this -> assign('bg_date', $bg_date);
				$map['create_time'] = array('EGT', $bg_time);
			}
			if ($end_date = $this -> _param('end_date', 'htmlspecialchars')) {
				$end_time = strtotime($end_date);
				$this -> assign('end_date', $end_date);
				$map['create_time'] = array('ELT', $end_time);
			}
		}
        if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['is_used'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
        $count = $Zheyuyue->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Zheyuyue->where($map)->order(array('yuyue_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids  = $city_ids = $shop_ids = $zhe_ids= array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			$city_ids[$val['city_id']] = $val['city_id'];
			$zhe_ids[$val['zhe_id']] = $val['zhe_id'];
        }
		session('zhe_yuyue_map', $map);//存储session
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
		$this->assign('citys', D('City')->itemsByIds($city_ids));
		$this->assign('zhe', D('Zhe')->itemsByIds($zhe_ids));
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
	
	//删除五折卡订单
	 public function yuyue_delete($yuyue_id = 0) {
        $yuyue_id = (int) $yuyue_id;
		if(!$detail = D('Zheyuyue')->find($yuyue_id)){
			$this->tuError('订单不存在');
		}else{
			if($detail['is_used'] != -1){
				$this->tuError('该订单状态不能删除');
			}else{
				D('Zheyuyue')->save(array('yuyue_id' => $yuyue_id, 'closed' => 1));
				D('Zhe')->where(array('zhe_id' =>$detail['zhe_id']))->setDec('buy_num');
			    $this->tuSuccess('删除成功', U('zhe/yuyue'));
			}
			
		}
    }

}
