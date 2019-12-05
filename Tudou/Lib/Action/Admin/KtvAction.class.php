<?php
class KtvAction extends CommonAction {
   public function _initialize() {
        parent::_initialize();
        $this->assign('dates',  $dates = D('Ktv')->getKtvDate());
        $this->assign('getTypes', D('KtvOrder')->getType());//订单状态
    }
	//KTV首页
    public function index() {
        $obj = D('Ktv');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['ktv_name|intro|addr|tel'] = array('LIKE', '%' . $keyword . '%');
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
		if (isset($_GET['audit']) || isset($_POST['audit'])) {
            $audit = (int) $this->_param('audit');
            if ($audit != 999) {
                $map['status'] = $audit;
            }
            $this->assign('audit', $audit);
        } else {
            $this->assign('audit', 999);
        }
        $count = $obj->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $obj->where($map)->order(array('ktv_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
	//编辑KTV
	public function edit($ktv_id = 0) {
        if ($ktv_id = (int) $ktv_id) {
            $obj = D('Ktv');
            if (!$detail = $obj->where(array('ktv_id'=>$ktv_id))->find()) {
                $this->tuError('请选择要编辑的KTV');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['ktv_id'] = $ktv_id;
				
				$date_id = $this->_post('date_id', false);
				$date_ids = implode(',', $date_id);
				$data['date_id'] = $date_ids;
				
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('ktv/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('shops',D('Shop')->find($detail['shop_id']));
				$this->assign('date_ids', $date_ids = explode(',', $detail['date_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的KTV');
        }
    }
   
    //编辑验证KTV
    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), array('shop_id', 'ktv_name','intro', 'tel', 'photo', 'addr', 'city_id', 'area_id', 'business_id','lat', 'lng','date_id', 'details','orderby','orders_num','views'));
		$data['shop_id'] = (int)$data['shop_id'];
        if(empty($data['shop_id'])){
            $this->tuError('商家不能为空');
        }elseif(!$shop = D('Shop')->find($data['shop_id'])){
            $this->tuError('商家不存在');
        }
		$data['city_id'] = $shop['city_id'];
		if(empty($data['city_id'])) {
            $this->tuError('商家没有城市ID');
        }
        $data['area_id'] = $shop['area_id'];
		if(empty($data['area_id'])) {
            $this->tuError('商家没有区域ID');
        }
        $data['business_id'] = $shop['business_id'];
        $data['ktv_name'] = htmlspecialchars($data['ktv_name']);
        if(empty($data['ktv_name'])) {
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
		$data['orderby'] = (int)$data['orderby'];
		$data['orders_num'] = (int)$data['orders_num'];
		$data['views'] = (int)$data['views'];
        return $data;
    }
    
  
    //删除KTV
    public function delete($ktv_id = 0) {
        $obj = D('Ktv');
        if (is_numeric($ktv_id) && ($ktv_id = (int) $ktv_id)) {
            $obj->where(array('ktv_id'=>$ktv_id))->save(array('closed' => 1));
            $this->tuSuccess('删除成功', U('ktv/index'));
        } else {
            $ktv_id = $this->_post('ktv_id', false);
            if (is_array($ktv_id)) {
                foreach ($ktv_id as $id) {
                    $obj->save(array('ktv_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('删除成功', U('ktv/index'));
            }
            $this->tuError('请选择要删除的KTV');
        }
    }
	//审核KTV
    public function audit($ktv_id = 0) {
        $obj = D('Ktv');
        if (is_numeric($ktv_id) && ($ktv_id = (int) $ktv_id)) {
			if($obj->where(array('ktv_id' => $ktv_id))->save(array('audit' => 1))){
				$this->tuSuccess('审核成功', U('ktv/index'));
			}else{
				$this->tuError('审核失败');
			}
        } else {
            $ktv_id = $this->_post('ktv_id', false);
            if (is_array($ktv_id)) {
                foreach ($ktv_id as $id) {
					$obj->where(array('ktv_id' => $id))->save(array('audit' => 1));
                }
                $this->tuSuccess('审核成功', U('ktv/index'));
            }
            $this->tuError('请选择要审核的KTV');
        }
    }
   
	//KTV订单
	public function order(){
        $obj = M('KtvOrder'); 
        import('ORG.Util.Page');
		$map = array('closed'=>0);
		if ($order_id = (int) $this->_param('order_id')) {
            $map['order_id|code|order_number'] = $order_id;
            $this->assign('order_id', $order_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
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
		if (isset($_GET['status']) || isset($_POST['status'])) {
            $status = (int) $this->_param('status');
            if ($status != 999) {
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        } else {
            $this->assign('status', 999);
        }
        $count = $obj->where($map)->count();
        $Page  = new Page($count,25);
        $show  = $Page->show();
        $list = $obj->where($map)->order('order_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$ktv_ids = array();
        foreach($list as $k => $v){
			$ktv_ids[$v['ktv_id']] = $v['ktv_id'];
            $room = D('KtvRoom') -> where(array('room_id'=>$v['room_id'])) -> find();
            $list[$k]['room'] = $room;
        }
		$this->assign('ktv', D('Ktv')->itemsByIds($ktv_ids));
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display(); 
    }
	//取消KTV订单
	 public function order_delete($order_id){
		$obj = D('KtvOrder');
        if($order_id = (int) $order_id){
            if(!$order = $obj->find($order_id)){
                $this->tuError('订单不存在');
            }elseif($order['status'] != 0){
                $this->tuError('该订单已删除');
            }else{
                if($obj->where(array('order_id' => $order_id))->save(array('closed' => 1))){
                    $this->tuSuccess('订单删除成功',U('ktv/order'));
                }else{
                    $this->tuError('订单删除失败');
                }
            }
        }else{
            $this->tuError('请选择要删除的订单');
        }
    }
	
	 //网站后台同意退款操作
    public function order_agree_refund($order_id){
        if($order_id = (int) $order_id){
			$obj = D('KtvOrder');
			if(!$detail = $obj->where('order_id =' . $order_id)->find()) {
			   $this->tuError('没有找到该订单');
			}elseif($detail['status'] != 3) {
			   $this->tuError('当前状态不永许退款');
			}else{
				if(false == $obj->ktv_agree_refund($order_id)) {
					$this->tuError($obj->getError());
				}else{
					$this->tuSuccess('退款操作成功', U('ktv/order'));
				}
			}
		}else{
			$this->tuError('请选择要退款的订单');
		}
    }
	
	//房间列表
    public function room($ktv_id = 0){ 
		$ktv_id = (int) $ktv_id;
		$obj = D('KtvRoom');
        if (!$detail = D('Ktv')->where(array('ktv_id'=>$ktv_id))->find()) {
          $this->tuError('请选择要编辑的KTV');
        }
		import('ORG.Util.Page'); 
		$map = array('ktv_id' =>$ktv_id,'closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title|intro'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $obj->where($map)->order(array('room_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('detail', $detail);
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display();
    }

  
    
 
   //编辑房间 
    public function editroom($room_id = 0,$ktv_id = 0){
		$ktv_id = (int) $ktv_id;
        if (!$ktv = D('Ktv')->where(array('ktv_id'=>$ktv_id))->find()) {
          $this->tuError('当前KTV不存在');
        }
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
				$data['ktv_id'] = $ktv_id;
                if (false !== $obj->save($data)) {
                   $this->tuSuccess('保存成功', U('ktv/room',array('ktv_id'=>$ktv_id)));
                }
                $this->tuError('操作失败');
            } else {
				$this->assign('ktv',$ktv);
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
        if (!empty($data['jiesuan_price'])) {
           if($data['jiesuan_price'] > $data['price']) {
				$this->tuError('结算价格不能大于套餐价格,但是可等于结算价格,但是可以填写位0');
			} 
        }
        return $data;
    }
	//删除房间
    public function room_delete($room_id = 0,$ktv_id = 0){
		$ktv_id = (int) $ktv_id;
        if (!$ktv = D('Ktv')->find($ktv_id)) {
          $this->tuError('KTV商家不存在');
        }
        if ($room_id = (int) $room_id) {
            $obj = D('KtvRoom');
            if (!$detail = $obj->find($room_id)) {
                $this->tuError('请选择要删除的房间');
            }
            if ($obj->where(array('room_id' => $room_id))->save(array('closed' => 1))) {
                $this->tuSuccess('删除房间成功', U('ktv/room',array('ktv_id'=>$ktv_id)));
            }else {
                $this->tuError('删除失败');
            }
        } else {
            $this->tuError('请选择要删除的房间');
        }
    }    
    
   
   
  

    
}
