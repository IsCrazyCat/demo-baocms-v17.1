<?php


class BookingAction extends CommonAction{
    
    public function _initialize(){
        parent::_initialize();
		if($this->_CONFIG['operation']['booking'] == 0){
            $this->error('此功能已关闭');
            die;
        }
        $dingtypes = D('Booking')->getDingType();
        $this->assign('dingtypes',$dingtypes);
        $cates = D('Bookingcate')->where(array('shop_id' => $this->shop_id))->select();
        foreach($cates as $k=>$val){
            $dingcates[$val['cate_id']] = $val;
        }
        $this->assign('dingcates', $dingcates);   
		$this->assign('types', $types = D('Bookingroom')->getType());
    }

    public function index(){
	     redirect('booking/order(');
    }
    public function order() {
        $st = (int) $this->_param('st');
		$this->assign('st', $st);
        $this->display();
    } 
    public function loaddata() {
		$obj = D('Bookingorder');
		import('ORG.Util.Page'); 
		$map = array('shop_id' => $this->shop_id); 
		
		
		$st = I('st', '', 'trim,intval');
		if($st == 999){
			$map['order_status'] = array('in',array(0,-1,1,2,8));
		}elseif($st == 0 || $st == ''){
			$map['order_status'] = 0;
		}else{
			$map['order_status'] = $st;
		}
		$this->assign('st',$st);
    
	
		$count = $obj->where($map)->count(); 
          
		$Page = new Page($count, 10); 
		$show = $Page->show(); 
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if ($Page->totalPages < $p) {
			die('0');
		}
		$list = $obj->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$shop_ids = $room_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			$room_ids[$val['room_id']] = $val['room_id'];
        }
        if (!empty($shop_ids)) {
            $this->assign('shops', D('Booking')->itemsByIds($shop_ids));
        }
		if (!empty($room_ids)) {
            $this->assign('room', D('Bookingroom')->itemsByIds($room_ids));
        }
		$this->assign('list', $list); 
		$this->assign('page', $show); 
		$this->display(); 
	}

    
     public function detail($order_id){
		$Bookingorder = D('Bookingorder');
        if(!$order_id = (int) $order_id){
            $this->error('该订单不存在');
        }elseif(!$detail = $Bookingorder->find($order_id)){
            $this->error('该订单不存在');
        }elseif($detail['shop_id'] != $this->shop_id){
            $this->error('非法操作');
        }else{
            $shop = D('Booking')->find($detail['shop_id']);
            $list = D('Bookingordermenu')->where(array('order_id'=>$order_id))->select();
            $menu_ids = array();
            foreach($list as $k=>$val){
                $menu_ids[$val['menu_id']] = $val['menu_id'];
            }
            if($menu_ids){
                $this->assign('menus',D('Bookingmenu')->itemsByIds($menu_ids));
            }
            $log = D('Paymentlogs')->where(array('type'=>'ding','order_id'=>$order_id))->find();
			$this->assign('room', D('Bookingroom')->find($detail['room_id']));
            $this->assign('log',$log);
            $this->assign('list',$list);
            $this->assign('shop',$shop);
            $this->assign('detail',$detail);
            $this->display();
        }
	}

	
	
	//订座房间
    public function room(){
        $obj = D('Bookingroom');
        import('ORG.Util.Page'); 
        $map = array('shop_id'=> $this->shop_id);
        $keyword = trim($this->_param('keyword', 'htmlspecialchars'));
        if($keyword){
            $map['name'] = array('LIKE', '%'.$keyword.'%');
        }
        $this->assign('keyword',$keyword);
        if($type_id = (int)$this->_param('type_id')){
            $map['type_id'] = $type_id;
            $this->assign('type_id',$type_id);
        } else{
			$this->assign('type_id',0);
		}       
        $count = $obj->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('types',$obj->getType());
        $this->assign('list', $list); 
        $this->assign('page', $show);
        $this->display();
    }
    
    //订座房间添加
    public function room_create(){
         $obj = D('Bookingroom');
         if(IS_POST){
             $data['name'] = htmlspecialchars($_POST['data']['name']);
             if(empty($data['name'])){
                 $this->tuMsg('房间名称不能为空');
             }
             $data['type_id'] = (int)($_POST['data']['type_id']);
             if(empty($data['type_id'])){
                 $this->tuMsg('请选择房间大小');
             }
             $data['photo'] = htmlspecialchars($_POST['data']['photo']);
             if(empty($data['photo'])){
                 $this->tuMsg('请上传图片');
             }
             $data['intro'] = htmlspecialchars($_POST['data']['intro']);
             $data['money'] = (int)($_POST['data']['money']*100);
             $data['closed'] = (int)($_POST['data']['closed']);
             $data['shop_id'] = $this->shop_id;
             if($room_id = $obj->add($data)){
				 D('Bookingroom')->where(array('room_id'=>$room_id))->save(array('qrcode'=>$qrcode));
                 $this->tuMsg('恭喜你操作成功',U('booking/room'));
             }
             $this->tuMsg('操作失败');
         }else{             
             $this->assign('types',$obj->getType());
             $this->display();
         }
    }
	
    //订座房间编辑
    public function room_edit($room_id){
        $obj = D('Bookingroom');
        if(!$detail = $obj->find($room_id)){
            $this->error('参数错误');
        }
        if($detail['shop_id']!= $this->shop_id){
            $this->error('参数错误');
        }
        $obj = D('Bookingroom');
         if(IS_POST){
             $data['name'] = htmlspecialchars($_POST['data']['name']);
             if(empty($data['name'])){
                 $this->tuMsg('房间名称不能为空');
             }
             $data['type_id'] = (int)($_POST['data']['type_id']);
             if(empty($data['type_id'])){
                 $this->tuMsg('请选择房间大小');
             }
             $data['photo'] = htmlspecialchars($_POST['data']['photo']);
             if(empty($data['photo'])){
                 $this->tuMsg('请上传图片');
             }
             $data['intro'] = htmlspecialchars($_POST['data']['intro']);
             $data['money'] = (int)($_POST['data']['money']*100);
             $data['closed'] = (int)($_POST['data']['closed']);
             $data['room_id'] = $room_id;
             $data['shop_id'] = $this->shop_id;
             if(false !== $obj->save($data)){
				 D('Bookingroom')->where(array('room_id'=>$room_id))->save(array('qrcode'=>$qrcode));
                 $this->tuMsg('恭喜你操作成功',U('booking/room'));
             }
             $this->tuMsg('操作失败');
         }else{ 
             $this->assign('types',$obj->getType());
             $this->assign('detail',$detail);
             $this->display();
         }
    }
	
	
    //订座房间删除
    public function roomdelete($room_id){
         $obj = D('Bookingroom');
         if($room_id = (int)$room_id){
            if(!$detail = $obj->find($room_id)){
                $this->tuMsg('参数错误');
            }
            if($detail['shop_id']!= $this->shop_id){
                $this->tuMsg('参数错误');
            }
            $data['closed'] = $detail['closed'] ? 0 : 1;
            $data['room_id'] = $room_id;
            if(false != $obj->save($data)){
                $this->tuMsg('操作成功',U('booking/room'));
            }
            $this->tuMsg('操作失败');
        }else{
            $this->tuMsg('参数错误');
        }        
    }
    
    //设置
    public function set(){
        $obj = D('Booking');
        $booking = $obj->find($this->shop_id);
        if($this->isPost()){ 
            $data = $this->checkFields($this->_post('data', false), array('shop_name','addr','city_id', 'area_id','business_id','price','mobile','deposit','tel', 'photo','business_time'));
		    $data['shop_name'] = htmlspecialchars($data['shop_name']);
			if(empty($data['shop_name'])){
				$this->tuMsg('名称不能为空');
			}
			$data['addr'] = htmlspecialchars($data['addr']);
			if(empty($data['addr'])){
				$this->tuMsg('地址不能为空');
			}
			$data['price'] = (int)$data['price'];
			if(empty($data['price'])){
				$this->tuMsg('评价价格不能为空');
			}
			$data['tel'] = htmlspecialchars($data['tel']);
			$data['mobile'] = htmlspecialchars($data['mobile']);
			if (empty($data['mobile'])){
				$this->tuMsg('手机号不能为空');
			}if(!isMobile($data['mobile'])){
				$this->tuMsg('手机号格式不正确');
			}
			$data['deposit'] = (int)$data['deposit'];
			$data['area_id'] = $this->shop['area_id'];
			$data['business_id'] = $this->shop['business_id'];
			$data['city_id'] = $this->shop['city_id'];
			$data['photo'] = htmlspecialchars($data['photo']);
			if (empty($data['photo'])) {
				$this->tuMsg('请上传缩略图');
			}
			if (!isImage($data['photo'])) {
				$this->tuMsg('缩略图格式不正确');
			} 
			$data['business_time'] = htmlspecialchars($data['business_time']);
		
		
           $type = $this->_param('type',false);
            if(empty($booking)){
                $data['create_time'] = NOW_TIME;
                $data['create_ip'] = get_client_ip();
                $data['shop_id'] = $this->shop_id;
                if($obj->add($data)){
                    foreach($thumb as $k=>$val){
                        D('Bookingpics')->add(array('shop_id'=>$this->shop_id,'photo'=>$val));
                    }
                    foreach($type as $k=>$val){
                        D('Bookingattr')->add(array('shop_id'=>$this->shop_id,'type_id'=>$val));
                    }
                     $this->tuMsg('设置成功', U('booking/index'));
                }else{
                    $this->tuMsg('设置失败');
                }
            }else{
                $data['update_time'] = NOW_TIME;
                $data['update_ip'] = get_client_ip();
                $data['audit'] = 0;
                $data['shop_id'] = $this->shop_id;
                if(false !== $obj->save($data)){
                    D('Bookingpics')->where(array('shop_id'=>$this->shop_id))->delete();
                    foreach($thumb as $k=>$val){
                        D('Bookingpics')->add(array('shop_id'=>$this->shop_id,'photo'=>$val));
                    }
                    D('Bookingattr')->where(array('shop_id'=>$this->shop_id))->delete();
                    foreach($type as $k=>$val){
                        D('Bookingattr')->add(array('shop_id'=>$this->shop_id,'type_id'=>$val));
                    }
                    $this->tuMsg('修改成功', U('booking/index'));
                }else{
                    $this->tuMsg('修改失败');
                }
            }
        } else {
            $this->assign('booking',$booking);
            $thumb = D('Bookingpics')->where(array('shop_id'=>$this->shop_id))->select();
            $have_type = D('Bookingattr')->where(array('shop_id'=>$this->shop_id))->select();
            $typess = array();
            foreach($have_type as $k=>$val){
                $typess[$val['type_id']] = $val['type_id'];
            }
            $this->assign('have_type',$typess);
            $this->display();
        }
    }
    
    
	
    public function cate(){  
        $Bookingcate = D('Bookingcate');
        import('ORG.Util.Page'); 
        $map = array('shop_id'=>$this->shop_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')){
            $map['cate_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Bookingcate->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Bookingcate->where($map)->order(array('cate_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }


    public function cate_create(){
        if (IS_AJAX){
            $shop_id = $this->shop_id;
            $cate_name = I('cate_name', '', 'trim,htmlspecialchars');
            if(empty($cate_name)){
                $this->ajaxReturn(array('status' => 'error', 'message' => '分类名称不能为空'));
            }
            $obj = D('Bookingcate');
            $data = array('shop_id' => $shop_id, 'cate_name' => $cate_name, 'num' => 0, 'closed' => 0);
            if($obj->add($data)){
                $this->ajaxReturn(array('status' => 'success', 'message' => '添加成功'));
            }
            $this->ajaxReturn(array('status' => 'error', 'message' => '添加失败'));
        }
     }
	
    public function cate_edit(){
        if (IS_AJAX) {
            $cate_id = I('v', '', 'intval,trim');
            if ($cate_id) {
                $obj = D('Bookingcate');
                if(!($detail = $obj->find($cate_id))){
                    $this->ajaxReturn(array('status' => 'error', 'message' => '请选择要编辑的分类'));
                }
                if($detail['shop_id'] != $this->shop_id){
                    $this->ajaxReturn(array('status' => 'error', 'message' => '请不要操作其他商家的分类'));
                }
                $cate_name = I('cate_name', '', 'trim,htmlspecialchars');
                if(empty($cate_name)){
                    $this->ajaxReturn(array('status' => 'error', 'message' => '分类名称不能为空'));
                }
                $data = array('cate_name' => $cate_name);
                if (false !== $obj->where('cate_id =' . $cate_id)->setField($data)) {
                    $this->ajaxReturn(array('status' => 'success', 'message' => '操作成功'));
                }
                $this->ajaxReturn(array('status' => 'error', 'message' => '操作失败'));
            }else{
                $this->ajaxReturn(array('status' => 'error', 'message' => '请选择要编辑的分类'));
            }
        }
    }
    
    //菜品配置 
    
    public function menu(){
        $Bookingmenu = D('Bookingmenu');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0);
        if($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['menu_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		
		$closed = I('closed', '', 'trim,intval');
		if($closed == 999){
			$map['closed'] = array('in',array(0,1));
		}elseif($closed == 0 || $closed == ''){
			$map['closed'] = 0;
		}else{
			$map['closed'] = $closed;
		}
		$this->assign('closed',$closed);
		
        if($shop_id = $this->shop_id) {
            $map['shop_id'] = $shop_id;
            $this->assign('shop_id', $shop_id);
        }
        $count = $Bookingmenu->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Bookingmenu->where($map)->order(array('menu_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display();
    }

    public function menu_create(){
        if($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false), array('menu_name', 'cate_id', 'photo', 'price', 'ding_price', 'is_new', 'is_sale', 'is_tuijian'));
			$data['menu_name'] = htmlspecialchars($data['menu_name']);
			if (empty($data['menu_name'])) {
				$this->tuMsg('菜品名称不能为空');
			}
			$data['shop_id'] = $this->shop_id;
			$data['cate_id'] = (int) $data['cate_id'];
			if (empty($data['cate_id'])) {
				$this->tuMsg('菜品分类不能为空');
			}
			$data['photo'] = htmlspecialchars($data['photo']);
			if (empty($data['photo'])) {
				$this->tuMsg('请上传缩略图');
			}
			if (!isImage($data['photo'])) {
				$this->tuMsg('缩略图格式不正确');
			}
			$data['price'] = (int) ($data['price'] * 100);
			if (empty($data['price'])) {
				$this->tuMsg('价格不能为空');
			}
			$data['ding_price'] = (int) ($data['ding_price'] * 100);
			if (empty($data['ding_price'])) {
				$this->tuMsg('优惠价格不能为空');
			}
			$data['is_new'] = (int) $data['is_new'];
			$data['is_sale'] = (int) $data['is_sale'];
			$data['is_tuijian'] = (int) $data['is_tuijian'];
			$data['create_time'] = NOW_TIME;
			$data['create_ip'] = get_client_ip();
            $obj = D('Bookingmenu');
            if($obj->add($data)) {
                $this->tuMsg('添加成功', U('booking/menu'));
            }
            $this->tuMsg('操作失败');
        }else{
            $this->display();
        }
    }

  

    public function menu_edit($menu_id = 0){
        if($menu_id = (int) $menu_id){
            $obj = D('Bookingmenu');
            if(!$detail = $obj->find($menu_id)) {
                $this->error('请选择要编辑的菜品设置');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->error('请不要操作其他商家的菜品设置');
            }
            if ($this->isPost()){
                $data = $this->checkFields($this->_post('data', false), array('menu_name', 'cate_id', 'photo', 'price', 'ding_price', 'is_new', 'is_sale', 'is_tuijian'));
                $data['menu_id'] = $menu_id;
				$data['product_name'] = htmlspecialchars($data['product_name']);
				if (empty($data['menu_name'])) {
					$this->tuMsg('菜品名称不能为空');
				}$data['cate_id'] = (int) $data['cate_id'];
				if (empty($data['cate_id'])) {
					$this->tuMsg('菜品分类不能为空');
				} $data['photo'] = htmlspecialchars($data['photo']);
				if (empty($data['photo'])) {
					$this->tuMsg('请上传缩略图');
				}
				if (!isImage($data['photo'])) {
					$this->tuMsg('缩略图格式不正确');
				}
				$data['price'] = (int) ($data['price'] * 100);
				if (empty($data['price'])) {
					$this->tuMsg('价格不能为空');
				}
				$data['ding_price'] = (int) ($data['ding_price'] * 100);
				if (empty($data['ding_price'])) {
					$this->tuMsg('优惠价格不能为空');
				}
				$data['is_new'] = (int) $data['is_new'];
				$data['is_sale'] = (int) $data['is_sale'];
				$data['is_tuijian'] = (int) $data['is_tuijian'];
                if (false !== $obj->save($data)) {
                    $this->tuMsg('操作成功', U('booking/menu'));
                }
                $this->tuMsg('操作失败');
            }else{
                $this->assign('detail', $detail);
                $this->display();
            }
        }else{
            $this->error('请选择要编辑的菜品设置');
        }
    }

	//删除菜品
    public function update($menu_id = 0){
		 $obj = D('Bookingmenu');
         if($menu_id = (int)$menu_id){
            if(!$detail = $obj->find($menu_id)){
                $this->tuMsg('参数错误');
            }
            if($detail['shop_id']!= $this->shop_id){
                $this->tuMsg('参数错误');
            }
            $data['closed'] = $detail['closed'] ? 0 : 1;
            $data['menu_id'] = $menu_id;
            if(false != $obj->save($data)){
                $this->tuMsg('操作成功',U('booking/menu'));
            }
            $this->tuMsg('操作失败');
        }else{
            $this->tuMsg('参数错误');
        }        
    }
    
    
    public function cancel($order_id){
        if($order_id = (int) $order_id){
            if(!$order = D('Bookingorder')->find($order_id)){
                $this->tuMsg('订单不存在');
            }elseif($order['shop_id'] != $this->shop_id){
                $this->tuMsg('非法操作订单');
            }elseif($order['order_status'] == -1){
                $this->tuMsg('该订单已取消');
            }else{
                if(false !== D('Bookingorder')->cancel($order_id)){
                    $this->tuMsg('订单取消成功',U('booking/order'));
                }else{
                    $this->tuMsg('订单取消失败');
                }
            }
        }else{
            $this->tuMsg('请选择要取消的订单');
        }
    }
    
    
    public function complete($order_id){
        if($order_id = (int) $order_id){
            if(!$order = D('Bookingorder')->find($order_id)){
                $this->tuMsg('订单不存在');
            }elseif($order['shop_id'] != $this->shop_id){
                $this->tuMsg('非法操作订单');
            }elseif($order['order_status'] == -1 || $order['order_status'] == 2 || $order['order_status'] == 0){
                $this->tuMsg('订单状态不正确');
            }else{
                if(false !== D('Bookingorder')->complete($order_id)){
                    $this->tuMsg('订单操作成功',U('booking/order'));
                }else{
                    $this->tuMsg('订单操作失败');
                }
            }
        }else{
            $this->tuMsg('请选择要完成的订单');
        }
    }
    
    
    public function delete($order_id){
        if($order_id = (int) $order_id){
            if(!$order = D('Bookingorder')->find($order_id)){
                $this->tuMsg('订单不存在');
            }elseif($order['shop_id'] != $this->shop_id){
                $this->tuMsg('非法操作订单');
            }elseif($order['order_status'] != -1){
                $this->tuMsg('订单状态不正确');
            }else{
                if(false !== D('Bookingorder')->delete($order_id)){
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 7,$status = 11);
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 7,$status = 11);
                    $this->tuMsg('订单删除成功',U('booking/order'));
                }else{
                    $this->tuMsg('订单删除失败');
                }
            }
        }else{
            $this->tuMsg('请选择要删除的订单');
        }
    }
    
	 //订座配置
    public function setting(){
        $obj = D('Bookingsetting');
        if(IS_POST){
            $data['shop_id'] = $this->shop_id;
            $data['mobile'] = htmlspecialchars($_POST['data']['mobile']);
            if(!isMobile($data['mobile'])){
                $this->tuMsg('请填写正确的手机号码');
            }
            $data['money'] = (int)($_POST['data']['money']* 100);
			if(empty($data['money'])){
				$this->tuMsg('定金不能为空或者为0');
			}
            $data['bao_time'] = (int)$_POST['data']['bao_time'];
            $data['start_time'] = (int)$_POST['data']['start_time'];
            $data['end_time'] = (int)$_POST['data']['end_time'];
            $data['is_bao'] = (int)$_POST['data']['is_bao'];
            $data['is_ting'] = (int)$_POST['data']['is_ting'];
            $obj->save($data);
            $this->tuMsg('设置成功',U('booking/setting'));
        } else{
            $this->assign('cfg',$obj->getCfg());
            $this->assign('detail',$obj->detail($this->shop_id));
            $this->display();
        }
    }
  
}
