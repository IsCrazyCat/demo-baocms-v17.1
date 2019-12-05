<?php
class HotelAction extends CommonAction {
    public function _initialize() {
        parent::_initialize();
        $this->cates = D('Hotel')->getHotelCate();
        $this->assign('cates', $this->cates);
        $this->types = D('Hotelbrand')->fetchAll();
        $this->assign('hoteltypes',$this->types);
        $this->stars = D('Hotel')->getHotelStar();
        $this->assign('stars', $this->stars);
		$this->assign('roomtype',D('Hotelroom')->getRoomType());
    }
    public function index() {
        $hotel = D('Hotel');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'audit' => 1);
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
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $hotel->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $hotel->where($map)->order(array('hotel_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }


    public function noaudit(){
        $hotel = D('Hotel');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'audit' => array('IN',array(0,2)));
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
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $hotel->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $hotel->where($map)->order(array('hotel_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

    public function create() {
        $obj = D('Hotel');
        if ($this->isPost()) {
            $data = $this->createCheck();
            $thumb = $this->_param('thumb', false);
            foreach ($thumb as $k => $val) {
                if (empty($val)) {
                    unset($thumb[$k]);
                }
                if (!isImage($val)) {
                    unset($thumb[$k]);
                }
            }
            if ($hotel_id = $obj->add($data)) {
                foreach($thumb as $k=>$val){
                    D('Hotelpics')->add(array('hotel_id'=>$hotel_id,'photo'=>$val));
                }
                $this->tuSuccess('操作成功', U('hotel/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
       
    }
    
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), array('hotel_name','shop_id','addr', 'city_id', 'area_id','business_id','cate_id', 'type','price','star', 'tel', 'details', 'photo', 'lng', 'lat','is_wifi','is_kt','is_nq','is_tv','is_xyj','is_ly','is_bx','is_base','is_rsh','in_time','out_time'));
        $data['hotel_name'] = htmlspecialchars($data['hotel_name']);
        if (empty($data['hotel_name'])) {
            $this->tuError('酒店名称不能为空');
        }$data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->tuError('酒店地址不能为空');
        }$data['cate_id'] = (int)$data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->tuError('酒店级别没有选择');
        }$data['star'] = (int)$data['star'];
        if (empty($data['star'])) {
            $this->tuError('酒店星级不能为空');
        }$data['price'] = (int)$data['price'];
        if (empty($data['price'])) {
            $this->tuError('酒店起价不能为空');
        }$data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel'])) {
            $this->tuError('酒店联系电话不能为空');
        }
        $data['type'] = (int)$data['type'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
                $this->tuError('酒店坐标没有选择');
            }
        $data['shop_id'] = (int)$data['shop_id'];
        if(empty($data['shop_id'])){
            $this->tuError('商家不能为空');
        }elseif(!$shop = D('Shop')->find($data['shop_id'])){
            $this->tuError('商家不存在');
        }
        $data['area_id'] = $shop['area_id'];
        $data['business_id'] = $shop['business_id'];
        $data['city_id'] = $shop['city_id'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('缩略图格式不正确');
        } 
        
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->tuError('酒店详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->tuError('酒店详情含有敏感词：' . $words);
        } 
        $data['in_time'] = htmlspecialchars($data['in_time']);
        $data['out_time'] = htmlspecialchars($data['out_time']);
        $data['is_wifi'] = (int)$data['is_wifi'];
        $data['is_wifi'] = (int)$data['is_wifi'];
        $data['is_kt'] = (int)$data['is_kt'];
        $data['is_nq'] = (int)$data['is_nq'];
        $data['is_tv'] = (int)$data['is_tv'];
        $data['is_xyj'] = (int)$data['is_xyj'];
        $data['is_ly'] = (int)$data['is_ly'];
        $data['is_bx'] = (int)$data['is_bx'];
        $data['is_base'] = (int)$data['is_base'];
        $data['is_rsh'] = (int)$data['is_rsh'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['audit'] = 1;
        return $data;
    }
    
    
    public function edit($hotel_id = 0) {

        if ($hotel_id = (int) $hotel_id) {
            $obj = D('Hotel');
            if (!$detail = $obj->find($hotel_id)) {
                $this->tuError('请选择要编辑的酒店');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $thumb = $this->_param('thumb', false);
                foreach ($thumb as $k => $val) {
                    if (empty($val)) {
                        unset($thumb[$k]);
                    }
                    if (!isImage($val)) {
                        unset($thumb[$k]);
                    }
                }
                $data['hotel_id'] = $hotel_id;
                if (false !== $obj->save($data)) {
                    D('Hotelpics')->where(array('hotel_id'=>$hotel_id))->delete();
                    foreach($thumb as $k=>$val){
                        D('Hotelpics')->add(array('hotel_id'=>$hotel_id,'photo'=>$val));
                    }
                    $this->tuSuccess('操作成功', U('hotel/index'));
                }
                $this->tuError('操作失败');
            } else {
                $thumb = D('Hotelpics')->where(array('hotel_id'=>$hotel_id))->select();
                $this->assign('thumb', $thumb);
                $this->assign('shop',D('Shop')->find($detail['shop_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的酒店');
        }
    }
    
    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), array('hotel_name','shop_id','addr', 'city_id', 'area_id','business_id','cate_id', 'type','price','star', 'tel', 'details', 'photo', 'lng', 'lat','is_wifi','is_kt','is_nq','is_tv','is_xyj','is_ly','is_bx','is_base','is_rsh','in_time','out_time'));
        $data['hotel_name'] = htmlspecialchars($data['hotel_name']);
        if (empty($data['hotel_name'])) {
            $this->tuError('酒店名称不能为空');
        }$data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->tuError('酒店地址不能为空');
        }$data['cate_id'] = (int)$data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->tuError('酒店级别没有选择');
        }$data['star'] = (int)$data['star'];
        if (empty($data['star'])) {
            $this->tuError('酒店星级不能为空');
        }$data['price'] = (int)$data['price'];
        if (empty($data['price'])) {
            $this->tuError('酒店起价不能为空');
        }$data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel'])) {
            $this->tuError('酒店联系电话不能为空');
        }
        $data['type'] = (int)$data['type'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
                $this->tuError('酒店坐标没有选择');
            }
        $data['shop_id'] = (int)$data['shop_id'];
        if(empty($data['shop_id'])){
            $this->tuError('商家不能为空');
        }elseif(!$shop = D('Shop')->find($data['shop_id'])){
            $this->tuError('商家不存在');
        }
        $data['area_id'] = $shop['area_id'];
        $data['business_id'] = $shop['business_id'];
        $data['city_id'] = $shop['city_id'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('缩略图格式不正确');
        } 
        
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->tuError('酒店详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->tuError('酒店详情含有敏感词：' . $words);
        } 
        $data['in_time'] = htmlspecialchars($data['in_time']);
        $data['out_time'] = htmlspecialchars($data['out_time']);
        $data['is_wifi'] = (int)$data['is_wifi'];
        $data['is_kt'] = (int)$data['is_kt'];
        $data['is_nq'] = (int)$data['is_nq'];
        $data['is_tv'] = (int)$data['is_tv'];
        $data['is_xyj'] = (int)$data['is_xyj'];
        $data['is_ly'] = (int)$data['is_ly'];
        $data['is_bx'] = (int)$data['is_bx'];
        $data['is_base'] = (int)$data['is_base'];
        $data['is_rsh'] = (int)$data['is_rsh'];
        $data['update_time'] = NOW_TIME;
        $data['update_ip'] = get_client_ip();
        return $data;
    }
    
    
    public function delete($hotel_id = 0) {
        $obj = D('Hotel');
        if (is_numeric($hotel_id) && ($hotel_id = (int) $hotel_id)) {
            $obj->save(array('hotel_id' => $hotel_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('hotel/index'));
        } else {
            $hotel_id = $this->_post('hotel_id', false);
            if (is_array($hotel_id)) {
                foreach ($hotel_id as $id) {
                    $obj->save(array('hotel_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('删除成功', U('hotel/index'));
            }
            $this->tuError('请选择要删除的酒店');
        }
    }

    public function audit($hotel_id = 0) {
        $obj = D('Hotel');
        if (is_numeric($hotel_id) && ($hotel_id = (int) $hotel_id)) {
            $obj->save(array('hotel_id' => $hotel_id, 'audit' => 1));
            $this->tuSuccess('审核成功', U('hotel/index'));
        } else {
            $hotel_id = $this->_post('hotel_id', false);
            if (is_array($hotel_id)) {
                foreach ($hotel_id as $id) {
                    $obj->save(array('hotel_id' => $id, 'audit' => 1));
                }
                $this->tuSuccess('审核成功', U('hotel/index'));
            }
            $this->tuError('请选择要审核的酒店');
        }
    }

    public function refuse($hotel_id){
        $obj = D('Hotel');
         if (is_numeric($hotel_id) && ($hotel_id = (int) $hotel_id)) {
            if ($this->isPost()) {
                $reason = htmlspecialchars($this->_param('reason'));
                if(!$reason){
                    $this->tuError('拒绝理由不能为空');
                }
                $obj->save(array('hotel_id' => $hotel_id, 'audit' => 2,'reason'=>$reason));
                $this->tuSuccess('操作成功', U('hotel/index'));
            }else{
                $this->assign('hotel_id',$hotel_id);
                $this->display();
            }
         }
    }
	
	//酒店订单列表
	public function order(){
        $hotelorder = D('Hotelorder');
        import('ORG.Util.Page'); 
        $map = array();
        $map['closed'] = 0;
        
		if ($order_id = (int) $this->_param('order_id')) {
            $map['order_id'] = $order_id;
            $this->assign('order_id', $order_id);
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
        $count = $hotelorder->where($map)->count(); 
        $Page = new Page($count, 15); 
        $show = $Page->show(); 
        $list = $hotelorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $room_ids = array();
        foreach($list as $k=>$val){
            $room_ids[$val['room_id']] = $val['room_id'];
        }
        $this->assign('rooms',D('Hotelroom')->itemsByIds($room_ids));
        $this->assign('list', $list); 
        $this->assign('page', $show);
        $this->display(); 
    }
	//取消酒店房间
	public function cancel($order_id){
        if($order_id = (int) $order_id){
            if(!$order = D('Hotelorder')->find($order_id)){
                $this->tuError('订单不存在');
            }elseif($order['order_status'] == -1){
                $this->tuError('该订单已取消');
            }else{
                if(false !== D('Hotelorder')->cancel($order_id)){
                    $this->tuSuccess('订单取消成功',U('hotel/order'));
                }else{
                    $this->tuError('订单取消失败');
                }
            }
        }else{
            $this->tuError('请选择要取消的订单');
        }
    }
    
    //入驻酒店房间
    public function complete($order_id){
        if($order_id = (int) $order_id){
            if(!$order = D('Hotelorder')->find($order_id)){
                $this->tuError('订单不存在');
            }elseif(($order['online_pay'] == 1&&$order['order_status'] != 1)||($order['online_pay'] == 0&&$order['order_status'] != 0)){
                $this->tuError('该订单无法完成');
            }else{
                if(false !== D('Hotelorder')->complete($order_id)){
                    $this->tuSuccess('订单操作成功',U('hotel/order'));
                }else{
                    $this->tuError('订单操作失败');
                }
            }
        }else{
            $this->tuError('请选择要完成的订单');
        }
    }
    
    //删除酒店房间
    public function order_delete($order_id){
        if($order_id = (int) $order_id){
            if(!$order = D('Hotelorder')->find($order_id)){
                $this->tuError('订单不存在');
            }elseif($order['order_status'] != -1){
                $this->tuError('订单状态不正确');
            }else{
                if(false !== D('Hotelorder')->save(array('order_id'=>$order_id,'closed'=>1))){
                    $this->tuSuccess('订单删除成功',U('hotel/order'));
                }else{
                    $this->tuError('订单删除失败');
                }
            }
        }else{
            $this->tuError('请选择要删除的订单');
        }
    }
	//酒店房间列表
	 public function room($hotel_id = 0){ 
	    $hotel_id = (int) $hotel_id;
		$Hotel = D('Hotel');
        if (!$detail = $Hotel->find($hotel_id)) {
          $this->tuError('请选择要编辑的酒店房间');
        }
        $room = D('Hotelroom');
        import('ORG.Util.Page'); 
        $map = array('hotel_id' => $hotel_id);
        $count = $room->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $room->where($map)->order(array('room_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('detail',$detail);
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

    
	//酒店房间添加
    public function setroom($hotel_id = 0){ 
		$hotel_id = (int) $hotel_id;
		$Hotel = D('Hotel');
        if (!$detail = $Hotel->find($hotel_id)) {
          $this->tuError('请选择要编辑的酒店房间');
        }
        if ($this->isPost()) {
            $data = $this->roomCreateCheck();
			$data['hotel_id'] = $hotel_id;
            $obj = D('Hotelroom');
            if ($room_id = $obj->add($data)) {
                $this->tuSuccess('添加成功', U('hotel/room',array('hotel_id'=>$detail['hotel_id'])));
            }
            $this->tuError('操作失败');
        } else {
			$this->assign('detail',$detail);
            $this->display();
        }
    }
    
    private function roomCreateCheck() {
        $data = $this->checkFields($this->_post('data', false), array('title', 'price','settlement_price', 'type', 'photo','hotel_id','is_zc', 'is_kd','is_cancel','sku'));
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('房间名称不能为空');
        }$data['price'] = (int)$data['price'];
        if (empty($data['price'])) {
            $this->tuError('房间价格不能为空');
        }$data['settlement_price'] = (int)$data['settlement_price'];
        if (empty($data['settlement_price'])) {
            $this->tuError('房间结算价格不能为空');
        }if ($data['settlement_price'] >=$data['price']) {
            $this->tuError('结算价格不能大于房间价格');
        }$data['type'] = (int)$data['type'];
        if (empty($data['type'])) {
            $this->tuError('房间类型不能为空');
        }
        $data['type'] = (int)$data['type'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传房间图片');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('房间图片格式不正确');
        } 
        $data['sku'] = (int) $data['sku'];
        $data['is_zc'] = (int)$data['is_zc'];
        $data['is_kd'] = (int)$data['is_kd'];
        $data['is_cancel'] = (int)$data['is_cancel'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    //酒店房间编辑
    public function editroom($hotel_id = 0,$room_id = 0){
        $hotel_id = (int) $hotel_id;
		$obj = D('Hotel');
        if (!$hotel = $obj->find($hotel_id)) {
          $this->tuError('请选择要编辑的酒店房间');
        }
        if ($room_id = (int) $room_id) {
            $obj = D('Hotelroom');
            if (!$detail = $obj->find($room_id)) {
                $this->tuError('请选择要编辑的房间');
            }
            if ($this->isPost()) {
                $data = $this->roomEditCheck();
                $data['room_id'] = $room_id;
				$data['hotel_id'] = $hotel_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('hotel/room',array('hotel_id'=>$hotel['hotel_id'])));
                }
                $this->tuError('操作失败');
            } else {
				$this->assign('hotel',$hotel);
                $this->assign('detail',$detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的房间');
        }
    }
    private function roomEditCheck() {
        $data = $this->checkFields($this->_post('data', false), array('title', 'price','settlement_price', 'type', 'photo','is_zc', 'is_kd','is_cancel','sku'));
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('房间名称不能为空');
        }$data['price'] = (int)$data['price'];
        if (empty($data['price'])) {
            $this->tuError('房间价格不能为空');
        }$data['settlement_price'] = (int)$data['settlement_price'];
        if (empty($data['settlement_price'])) {
            $this->tuError('房间结算价格不能为空');
        }if ($data['settlement_price'] >=$data['price']) {
            $this->tuError('结算价格不能大于房间价格');
        }$data['type'] = (int)$data['type'];
        if (empty($data['type'])) {
            $this->tuError('房间类型不能为空');
        }
        $data['type'] = (int)$data['type'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传房间图片');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('房间图片格式不正确');
        } 
        $data['sku'] = (int) $data['sku'];
        $data['is_zc'] = (int)$data['is_zc'];
        $data['is_kd'] = (int)$data['is_kd'];
        $data['is_cancel'] = (int)$data['is_cancel'];
        return $data;
    }
	
	//删除房间
    public function deleteroom($hotel_id = 0,$room_id = 0){
		$hotel_id = (int) $hotel_id;
		$obj = D('Hotel');
        if (!$hotel = $obj->find($hotel_id)) {
          $this->tuError('请选择要编辑的酒店房间');
        }
        if ($room_id = (int) $room_id) {
            $obj = D('Hotelroom');
            if (!$detail = $obj->find($room_id)) {
                $this->tuError('请选择要删除的房间');
            }
            if (false !== $obj->delete($room_id)) {
                $this->tuSuccess('删除成功', U('farm/room',array('hotel_id'=>$hotel['hotel_id'])));
            }else {
                $this->tuError('删除失败');
            }
        } else {
            $this->tuError('请选择要删除的酒店房间');
        }
    }  
	
	
	private $comment_create_fields = array('user_id', 'shop_id', 'order_id','hotel_id', 'score',  'content', 'reply');
    private $comment_edit_fields = array('user_id', 'shop_id', 'order_id', 'hotel_id','score',  'content', 'reply');
	//酒店点评
    public function comment() {
        $Hotelcomment = D('Hotelcomment');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0);
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $user = D('Users')->find($user_id);
            $this->assign('nickname', $user['nickname']);
            $this->assign('user_id', $user_id);
        }
		if ($order_id = (int) $this->_param('order_id')) {
            $map['order_id'] = $order_id;
            $this->assign('order_id', $order_id);
        }
		if ($comment_id = (int) $this->_param('comment_id')) {
            $map['comment_id'] = $comment_id;
            $this->assign('comment_id', $comment_id);
        }
        $count = $Hotelcomment->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Hotelcomment->where($map)->order(array('comment_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($shop_ids)) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
	//添加酒店点评
    public function comment_create() {
        if ($this->isPost()) {
            $data = $this->comment_createCheck();
            $obj = D('Hotelcomment');
            if ($comment_id = $obj->add($data)) {
                $photos = $this->_post('photos', false);
                $local = array();
                foreach ($photos as $val) {
                    if (isImage($val))
                        $local[] = $val;
                }
                if (!empty($local))
                    D('Hotelcommentpics')->upload($comment_id, $local);
                $this->tuSuccess('添加成功', U('hotel/comment'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }

    private function comment_createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->comment_create_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->tuError('用户不能为空');
        }
        $data['order_id'] = (int) $data['order_id'];
        if (empty($data['order_id'])) {
            $this->tuError('订单ID不能为空');
        }
        if (!$Hotelorder = D('Hotelorder')->find($data['order_id'])) {
            $this->tuError('订单ID不存在');
        }
        $data['shop_id'] = (int) $Hotelorder['shop_id'];
        $data['hotel_id'] = (int) $Hotelorder['hotel_id'];
        $data['score'] = (int) $data['score'];
        if (empty($data['score'])) {
            $this->tuError('评分不能为空');
        }
        if ($data['score'] > 5 || $data['score'] < 1) {
            $this->tuError('评分为1-5之间的数字');
        }
        $data['content'] = htmlspecialchars($data['content']);
        if (empty($data['content'])) {
            $this->tuError('评价内容不能为空');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
		$data['reply'] = htmlspecialchars($data['reply']);
		if (!empty($data['reply'])) {
            $data['reply_time'] = NOW_TIME;
        	$data['reply_ip'] = get_client_ip();
        }
        return $data;
    }
	//酒店点评编辑
   public function comment_edit($comment_id = 0) {
        if ($comment_id = (int) $comment_id) {
            $obj = D('Hotelcomment');
            if (!$detail = $obj->find($comment_id)) {
                $this->tuError('请选择要编辑的酒店点评');
            }
            if ($this->isPost()) {
                $data = $this->comment_editCheck();
                $data['comment_id'] = $comment_id;
                if (false !== $obj->save($data)) {
                    $photos = $this->_post('photos', false);
                    $local = array();
                    foreach ($photos as $val) {
                        if (isImage($val))
                            $local[] = $val;
                    }
                    if (!empty($local))
                        D('Hotelcommentpics')->upload($comment_id, $local);
						D('Users')->prestige($data['user_id'], 'dianping');//默认无效
                        D('Users')->updateCount($data['user_id'], 'ping_num');
                    	$this->tuSuccess('操作成功', U('hotel/comment'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->assign('photos', D('Hotelcommentpics')->getPics($comment_id));
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的酒店点评');
            
        }
    }

    private function comment_editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->comment_edit_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->tuError('用户不能为空');
        }
        $data['order_id'] = (int) $data['order_id'];
        if (empty($data['order_id'])) {
            $this->tuError('订单ID不能为空');
        }
        if (!$Hotelorder = D('Hotelorder')->find($data['order_id'])) {
            $this->tuError('订单ID不存在');
        }
        $data['shop_id'] = (int) $Hotelorder['shop_id'];
        $data['hotel_id'] = (int) $Hotelorder['hotel_id'];
        $data['score'] = (int) $data['score'];
        if (empty($data['score'])) {
            $this->tuError('评分不能为空');
        }
        if ($data['score'] > 5 || $data['score'] < 1) {
            $this->tuError('评分为1-5之间的数字');
        }
        $data['content'] = htmlspecialchars($data['content']);
        if (empty($data['content'])) {
            $this->tuError('评价内容不能为空');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
		$data['reply'] = htmlspecialchars($data['reply']);
		if (!empty($data['reply'])) {
            $data['reply_time'] = NOW_TIME;
        	$data['reply_ip'] = get_client_ip();
        }
       
		//图像处理开始
        $photos = $this->_post('photos', false);
        $local = array();
        foreach ($photos as $val) {
            if (isImage($val))
                $local[] = $val;
        }
        $data['photos'] = json_encode($local);
		//图像处理结束
        return $data;
    }
	//酒店点评删除
	 public function comment_delete($comment_id = 0) {
        if (is_numeric($comment_id) && ($comment_id = (int) $comment_id)) {
            $obj = D('Hotelcomment');
            $obj->delete($comment_id);
            $this->tuSuccess('删除成功', U('hotel/comment'));
        } else {
            $comment_id = $this->_post('comment_id', false);
            if (is_array($comment_id)) {
                $obj = D('Hotelcomment');
                foreach ($comment_id as $id) {
                     $obj->delete($id);
                }
                $this->tuSuccess('删除成功', U('hotel/comment'));
            }
            $this->tuError('请选择要删除的酒店点评');
        }
    }
  //同意退款操作
    public function agree_refund(){
        $order_id = I('order_id', 0, 'trim,intval');
        $Hotelorder = D('Hotelorder');
        $hotel_order = $Hotelorder->where('order_id =' . $order_id)->find();
		if (!($detial = $Hotelorder->find($order_id))) {
                $this->tuError('该订单不存在');
        }elseif($hotel_order['order_status'] != 3){
				$this->tuError('订单状态不正确，无法退款');
		}else{
			if (false == $Hotelorder->hotel_refund_user($order_id)) {//退款操作
				$this->tuError('非法操作');
			}else{
				$this->tuSuccess('已成功退款',U('hotel/order'));	
			}
		}
    }
   
   function diffBetweenTwoDays ($day1, $day2){
          $second1 = strtotime($day1);
          $second2 = strtotime($day2);
          if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
          }
          return ($second1 - $second2) / 86400;
    }    
}
