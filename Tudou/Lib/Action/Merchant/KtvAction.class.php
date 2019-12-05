<?php
class KtvAction extends CommonAction {
    public function _initialize() {
        parent::_initialize();
		if(empty($this->_CONFIG['operation']['ktv'])) {
            $this->error('KTV功能已关闭');
            die;
        }
		$this->assign('dates',  $dates = D('Ktv')->getKtvDate());
        $this->assign('getTypes', D('KtvOrder')->getType());//订单状态
    }

    
    private function check_ktv(){
        $obj = D('Ktv');
        $res =  $obj->where(array('shop_id'=>$this->shop_id))->find();
        if(!$res){
            $this->error('请先完善KTV资料！',U('ktv/set_ktv'));
        }elseif($res['audit'] == 0){
            $this->error('您的KTV申请正在审核中，请耐心等待！',U('ktv/set_ktv'));
        }elseif($res['audit'] == 2){
            $this->error('您的KTV申请未通过审核！',U('ktv/set_ktv'));
        }else{
            return $res['ktv_id'];
        }
        
    }
    //订单首页
    public function index(){
        $ktv_id = $this->check_ktv();
        $Ktv = D('Ktv')->where(array('shop_id'=>$this->shop_id))->find();
        $obj = M('KtvOrder'); 
        $detail = $obj->where(array('ktv_id'=>$Ktv['ktv_id']))->find();
        $map = array('closed' => 0);
        $map['ktv_id'] = $detail['ktv_id'];
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		if(isset($_GET['status']) || isset($_POST['status'])) {
            $status = (int) $this->_param('status');
            if ($status != 999) {
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        }else{
            $this->assign('status', 999);
        }
        if($gotime = $this->_param('gotime', 'htmlspecialchars')){
            $gotime = strtotime($gotime);
            $map['gotime'] = array(array('ELT', $gotime+86399), array('EGT', $gotime));
        }
        import('ORG.Util.Page');
        $count  = $obj->where($map)->count();
        $Page  = new Page($count,25);
        $show  = $Page->show();
        $list = $obj->where($map)->order('order_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as $k => $v){
            $room = D('KtvRoom') -> where(array('room_id'=>$v['room_id'])) -> find();
            $list[$k]['room'] = $room;
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display(); 
    }
    
	//商家删除订单    
    public function delete($order_id){
        $ktv_id = $this->check_ktv();
		$obj = D('KtvOrder'); 
        if($order_id = (int) $order_id){
            if(!$detail = $obj->find($order_id)){
                $this->tuError('订单不存在');
            }elseif($detail['ktv_id'] != $ktv_id){
                $this->tuError('非法操作订单');
            }elseif($detail['status'] != 0){
                $this->tuError('订单状态不正确');
            }else{
                if(false !== $obj->save(array('order_id'=>$order_id,'closed'=>1))){
                    $this->tuSuccess('订单删除成功',U('ktv/index'));
                }else{
                    $this->tuError('订单删除失败');
                }
            }
        }else{
            $this->tuError('请选择要删除的订单');
        }
    }
    
	
	 //商家后台同意退款操作
    public function order_agree_refund($order_id){
        if($order_id = (int) $order_id){
			$obj = D('KtvOrder');
			if(!$detail = $obj->where('order_id =' . $order_id)->find()) {
			   $this->tuError('没有找到该订单');
			}elseif($detail['status'] != 3) {
			   $this->tuError('当前状态不永许退款');
			}elseif($detail['shop_id'] != $this->shop_id) {
			   $this->tuError('非法操作');
			}else{
				if(false == $obj->ktv_agree_refund($order_id)) {
					$this->tuError($obj->getError());
				}else{
					$this->tuSuccess('退款操作成功', U('ktv/index'));
				}
			}
		}else{
			$this->tuError('请选择要退款的订单');
		}
    }
    
    public function set_ktv(){
        $obj = D('Ktv');
        $detail = $obj->where(array('shop_id'=>$this->shop_id))->find();
        if ($this->isPost()) { 
           $data = $this->createCheck();
            if (empty($detail)) {
                $data['create_time'] = NOW_TIME;
                $data['create_ip'] = get_client_ip();
                if($ktv_id = $obj->add($data)){
					
					$date_id = $this->_post('date_id', false);
					$date_ids = implode(',', $date_id);
					$data['date_id'] = $date_ids;
				
                     $this->tuSuccess('添加KTV成功，请耐心等待审核', U('ktv/index'));
                }else{
                    $this->tuError('修改KTV失败');
                }
            }else{
				$date_id = $this->_post('date_id', false);
				$date_ids = implode(',', $date_id);
				$data['date_id'] = $date_ids;
					
                $data['update_time'] = NOW_TIME;
                $data['update_ip'] = get_client_ip();
                $data['audit'] = 0;
                if(false !== $obj->save($data)){
                    $this->tuSuccess('修改KTV成功，请耐心等待审核', U('ktv/index'));
                }else{
                    $this->tuError('修改KTV失败');
                }
            }
        } else {
            $this->assign('detail',$detail);
            $this->assign('shop',D('Shop')->find($detail['shop_id']));
			$this->assign('date_ids', $date_ids = explode(',', $detail['date_id']));
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), array('shop_id', 'ktv_name','intro', 'tel', 'photo', 'addr', 'city_id', 'area_id', 'business_id','lat', 'lng', 'date_id','details','audit'));
		$data['shop_id'] = $this->shop_id;
        $data['ktv_name'] = htmlspecialchars($data['ktv_name']);
        if (empty($data['ktv_name'])) {
            $this->tuError('名称不能为空');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('简介不能为空');
        }
		$data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->tuError('地址不能为空');
        }
		$data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel'])) {
            $this->tuError('联系电话不能为空');
        }
		$data['city_id'] = $this->shop['city_id'];
        $data['area_id'] = $this->shop['area_id'];
        $data['business_id'] = $this->shop['business_id'];
		
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
            $this->tuError('坐标没有选择');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('缩略图格式不正确');
        } 
        
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->tuError('商家简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->tuError('详情含有敏感词：' . $words);
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['audit'] = 0;
        return $data;
    }
    
    //Ktv房间列表
    public function room(){ 
        $ktv_id = $this->check_ktv();
        $obj = D('KtvRoom');
        import('ORG.Util.Page'); 
		$map = array('ktv_id' => $ktv_id,'closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title|intro'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $obj->where($map)->order(array('room_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display();
    }

    
   //添加套餐
    public function setroom(){ 
        $this->check_ktv();
        if ($this->isPost()) {
            $data = $this->roomCreateCheck();
            $obj = D('KtvRoom');
            if ($room_id = $obj->add($data)) {
                $this->tuSuccess('添加成功', U('ktv/room'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }
    
    
    private function roomCreateCheck() {
        $data = $this->checkFields($this->_post('data', false), array('title','photo','intro','num','price','daofu_price','small_price','accommodate_number','jiesuan_price'));
        $data['title'] = htmlspecialchars($data['title']);
        if(empty($data['title'])) {
            $this->tuError('房间名称不能为空');
        }
		$data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('缩略图格式不正确');
        } 
		$data['num'] = htmlspecialchars($data['num']);
        if(empty($data['num'])) {
            $this->tuError('每天可预约多人人次必填');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
        if(empty($data['intro'])) {
            $this->tuError('房间简介不能为空');
        }
		$data['price'] = (int)($data['price']*100);
        if (empty($data['price'])) {
            $this->tuError('套餐价格不能为空');
        }
		$data['daofu_price'] = (int)($data['daofu_price']*100);
		$data['small_price'] = (int)($data['small_price']*100);
		$data['accommodate_number'] = (int)$data['accommodate_number'];
        if (empty($data['accommodate_number'])) {
            $this->tuError('容纳人数不能为空');
        }
		$data['jiesuan_price'] = (int)($data['jiesuan_price']*100);
        $data['jiesuan_price'] = (int)($data['jiesuan_price']*100);
        if (!empty($data['jiesuan_price'])) {
           if($data['jiesuan_price'] > $data['price']) {
				$this->tuError('结算价格不能大于套餐价格,但是可等于结算价格,但是可以填写位0');
			} 
        }
        $detail = D('Ktv')->where(array('shop_id'=>$this->shop_id))->find();
        $data['ktv_id'] = $detail['ktv_id'];
        return $data;
    }
    
     
    
    public function editroom($room_id = 0){
        $ktv_id = $this->check_ktv();
        if ($room_id = (int) $room_id) {
            $obj = D('KtvRoom');
            if (!$detail = $obj->find($room_id)) {
                $this->tuError('请选择要编辑的套餐');
            }
            if ($detail['ktv_id'] != $ktv_id) {
                $this->tuError('非法操作');
            }
            if ($this->isPost()) {
                $data = $this->roomEditCheck();
                $data['room_id'] = $room_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('保存成功', U('ktv/room'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail',$detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的房间');
        }
    }

	private function roomEditCheck() {
        $data = $this->checkFields($this->_post('data', false), array('title','photo','intro','num','price','daofu_price','small_price','accommodate_number','jiesuan_price'));
         $data['title'] = htmlspecialchars($data['title']);
        if(empty($data['title'])) {
            $this->tuError('房间名称不能为空');
        }
		$data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('缩略图格式不正确');
        } 
		$data['num'] = htmlspecialchars($data['num']);
        if(empty($data['num'])) {
            $this->tuError('每天可预约多人人次必填');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
        if(empty($data['intro'])) {
            $this->tuError('房间简介不能为空');
        }
		$data['price'] = (int)($data['price']*100);
        if (empty($data['price'])) {
            $this->tuError('套餐价格不能为空');
        }
		$data['small_price'] = (int)($data['small_price']*100);
		$data['daofu_price'] = (int)($data['daofu_price']*100);
		$data['accommodate_number'] = (int)$data['accommodate_number'];
        if (empty($data['accommodate_number'])) {
            $this->tuError('容纳人数不能为空');
        }
		$data['jiesuan_price'] = (int)($data['jiesuan_price']*100);
        $data['jiesuan_price'] = (int)($data['jiesuan_price']*100);
        if (!empty($data['jiesuan_price'])) {
           if($data['jiesuan_price'] > $data['price']) {
				$this->tuError('结算价格不能大于套餐价格,但是可等于结算价格,但是可以填写位0');
			} 
        }
        $detail = D('Ktv')->where(array('shop_id'=>$this->shop_id))->find();
        $data['ktv_id'] = $detail['ktv_id'];
        return $data;
    }
	
    public function deleteroom($room_id = 0){
        $ktv_id = $this->check_ktv();
        if ($room_id = (int) $room_id) {
            $obj = D('KtvRoom');
            if (!$detail = $obj->find($pid)) {
                $this->tuError('请选择要删除的房间');
            }
            if ($detail['ktv_id'] != $ktv_id) {
                $this->tuError('非法操作');
            }
            if (false !== $obj->save(array('room_id'=>$room_id,'closed'=>1))){
                $this->tuSuccess('删除成功', U('ktv/room'));
            }else {
                $this->tuError('删除失败');
            }
        } else {
            $this->tuError('请选择要删除的套餐');
        }
    }    
    
   
}
