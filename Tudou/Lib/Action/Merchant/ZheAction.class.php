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
		
		
		$Zhe = D('Zhe')->where(array('shop_id'=>$this->shop_id,'closed' => 0))->find();
        if(empty($Zhe) && ACTION_NAME != 'apply'){
            $this->error('您还没无折卡', U('zhe/apply'));
        }
        if(!empty($Zhe) && $Zhe['audit'] == 0) {
            $this->error('您的无折卡申请还在审核中');
        }
    }

    
    public function index(){
        $detail = D('Zhe')->where(array('shop_id'=>$this->shop_id,'closed' => 0))->find();
		$this->assign('detail', $detail);
        $this->display(); 
    }
	//添加五折卡
    public function apply() {
        $obj = D('Zhe');
        if ($this->isPost()) {
            $data = $this->createCheck();
			$week_id = $this->_post('week_id', false);
            $week_id = implode(',', $week_id);
            $data['week_id'] = $week_id;
			
			$date_id = $this->_post('date_id', false);
            $date_id = implode(',', $date_id);
            $data['date_id'] = $date_id;
			
            if ($Zhe_id = $obj->add($data)) {
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
	    $data['shop_id'] = $this->shop_id;
		$data['city_id'] = $this->shop['city_id'];
        $data['area_id'] = $this->shop['area_id'];
        
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
        $data['audit'] = 0;
        return $data;
    }
    
    //删除五折卡
    public function delete($Zhe_id = 0) {
        $obj = D('Zhe');
        if($Zhe_id = (int) $Zhe_id){
			$detail = $obj->find($Zhe_id);
			if($detail['shop_id'] != $this->shop_id){
				$this->tuError('请不要非法操作');
			}
            $obj->save(array('zhe_id' => $Zhe_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('zhe/index'));
        } 
    }
	//无折卡订单
     public function order() {
        $Zheorder = D('Zheorder');
        import('ORG.Util.Page'); 
        $map = array('closed' => '0','shop_id' =>$this->shop_id);
        if($order_id = (int) $this->_param('order_id')){
            $map['order_id'] = $order_id;
            $this->assign('order_id', $order_id);
        }
		if($city_id = (int) $this->_param('city_id')){
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
		if(($bg_date = $this -> _param('bg_date', 'htmlspecialchars')) && ($end_date = $this -> _param('end_date', 'htmlspecialchars'))){
			$bg_time = strtotime($bg_date);
			$end_time = strtotime($end_date);
			$map['create_time'] = array( array('ELT', $end_time), array('EGT', $bg_time));
			$this -> assign('bg_date', $bg_date);
			$this -> assign('end_date', $end_date);
		}else{
			if($bg_date = $this -> _param('bg_date', 'htmlspecialchars')){
				$bg_time = strtotime($bg_date);
				$this -> assign('bg_date', $bg_date);
				$map['create_time'] = array('EGT', $bg_time);
			}
			if($end_date = $this -> _param('end_date', 'htmlspecialchars')){
				$end_time = strtotime($end_date);
				$this -> assign('end_date', $end_date);
				$map['create_time'] = array('ELT', $end_time);
			}
		}
        if(isset($_GET['status']) || isset($_POST['status'])){
            $status = (int) $this->_param('status');
            if($status != 999) {
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        }else{
            $this->assign('status', 999);
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
		}elseif($detail['shop_id'] != $this->shop_id){
			$this->tuError('请不要非法操作');
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
		}elseif($detail['shop_id'] != $this->shop_id){
			$this->tuError('请不要非法操作');
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
	//无折卡预约
	public function yuyue() {
        $Zheyuyue = D('Zheyuyue');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0,'shop_id'=>$this->shop_id);
        if($yuyue_id = (int) $this->_param('yuyue_id')){
            $map['yuyue_id'] = $oyuyue_id;
            $this->assign('yuyue_id', $yuyue_id);
        }
		if(($bg_date = $this -> _param('bg_date', 'htmlspecialchars')) && ($end_date = $this -> _param('end_date', 'htmlspecialchars'))) {
			$bg_time = strtotime($bg_date);
			$end_time = strtotime($end_date);
			$map['create_time'] = array( array('ELT', $end_time), array('EGT', $bg_time));
			$this -> assign('bg_date', $bg_date);
			$this -> assign('end_date', $end_date);
		}else{
			if($bg_date = $this -> _param('bg_date', 'htmlspecialchars')){
				$bg_time = strtotime($bg_date);
				$this -> assign('bg_date', $bg_date);
				$map['create_time'] = array('EGT', $bg_time);
			}
			if($end_date = $this -> _param('end_date', 'htmlspecialchars')){
				$end_time = strtotime($end_date);
				$this -> assign('end_date', $end_date);
				$map['create_time'] = array('ELT', $end_time);
			}
		}
        if(isset($_GET['is_used']) || isset($_POST['is_used'])){
            $is_used = (int) $this->_param('is_used');
            if($is_used != 999){
                $map['is_used'] = $is_used;
            }
            $this->assign('is_used', $is_used);
        }else{
            $this->assign('is_used', 999);
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
		}elseif($detail['shop_id'] != $this->shop_id){
			$this->tuError('请不要非法操作');
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
	
	
	//核销二维码
	public function verify($yuyue_id = 0){
		$obj = D('Zheyuyue');
        $yuyue_id = (int) $yuyue_id;
		if(!$detail = $obj->find($yuyue_id)){
            $this->error('该订单不存在');
        }
		if ($this->isPost()) {
			$yzm = $this->_post('yzm');
			$zhe_used_mobile = session('zhe_used_mobile');
			$zhe_used_code = session('zhe_used_code');
			if (empty($yzm)) {
			   $this->tuError('请输入短信验证码');
			}
			if ($detail['mobile'] != $zhe_used_mobile) {
			   $this->tuError('手机号码和收取验证码的手机号不一致');
			}
			if ($yzm != $zhe_used_code) {
			   $this->tuError('短信验证码不正确');
			}
			if(false !== $obj->complete($detail['yuyue_id'])){
				$this->tuSuccess('验证成功', U('zhe/index',array('aready'=>1)));
			}else{
				$this->tuError('操作失败');
			 }
		}else {
			$this->assign('detail',$detail);
            $this->display();
        }
		
    }
	
	 //验证码
    public function check() {
		$yuyue_id = (int) $this->_param('yuyue_id');
        if ($this->isPost()) {
            $code = $this->_post('code', false);
            if (empty($code)) {
                $this->tuError('请输入验证码');
            }
            $obj = D('Zheyuyue');
            if(!$detail = $obj->where(array('code'=> $code))->find()){
			   $this->tuError('该订单不存在或者验证码错误');
		    }
			if(!$obj->zhe_verify_yuyue($detail['yuyue_id'],$this->shop_id)){//判断一切错误
				$this->tuError($obj->getError());	  
			}else{
				$this->tuSuccess('我们已向用户手机发送短信，正在为您跳转到下一页', U('zhe/verify',array('yuyue_id'=>$detail['yuyue_id'])));
			}
        } else {
            $this->display();
        }
    }

}
