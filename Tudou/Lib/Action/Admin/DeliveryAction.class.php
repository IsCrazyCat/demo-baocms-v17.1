<?php
class DeliveryAction extends CommonAction{
	
	private $create_fields = array('city_id', 'user_id','photo', 'name', 'mobile', 'addr');
	private $edit_fields = array('city_id', 'user_id','photo', 'name', 'mobile', 'addr');
	
    public function index(){
        $obj = D('Delivery');
        import('ORG.Util.Page');
		$map = array('closed' => 0);
		if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['user_id|name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $obj->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order('create_time')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $user_ids[$val['user_id']] = $val['user_id'];
            }
			$list[$k]['price'] = (int) D('Runningmoney')->where(array('user_id'=>$val['user_id']))->sum('money');
			$list[$k]['order']['ing'] = (int) D('DeliveryOrder')->where(array('delivery_id'=>$val['id'],'closed'=>'0','status'=>'2'))->count();
			$list[$k]['order']['end'] = (int) D('DeliveryOrder')->where(array('delivery_id'=>$val['id'],'closed'=>'0','status'=>'8'))->count();
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
		$obj = D('Delivery');
        if($this->isPost()){
            $data = $this->createCheck();
            if($id = $obj->add($data)) {
                $this->tuSuccess('添加成功', U('delivery/index'));
            }
            $this->tuError('申请失败或者配送员ID重复');
        }else{
			$this->assign('user_delivery', $user_delivery);
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['id'] = (int) $data['user_id'];
		$data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->tuError('用户不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传身份证');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('身份证格式不正确');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->tuError('姓名不能为空');
        }
		$data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->tuError('手机号不能为空');
        }
        if (!isPhone($data['mobile']) && !isMobile($data['mobile'])) {
            $this->tuError('手机号格式不正确');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->tuError('地址不能为空');
        } 
		$data['audit'] = 1;       
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	
     public function edit($id = 0){
        if($id = (int) $id){
            $obj = D('Delivery');
            if (!($detail = $obj->find($id))){
                $this->tuError('请选择要编辑的配送员');
            }
            if($this->isPost()){
                $data = $this->editCheck();
                $data['id'] = $id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('delivery/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的配送员');
        }
    }
	
	
	 private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->tuError('用户不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传身份证');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('身份证格式不正确');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->tuError('姓名不能为空');
        }
		$data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->tuError('手机号不能为空');
        }
        if (!isPhone($data['mobile']) && !isMobile($data['mobile'])) {
            $this->tuError('手机号格式不正确');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->tuError('地址不能为空');
        } 
		$data['audit'] = 1;       
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	
	
	
    public function lists(){
        $id = I('id', '', 'intval,trim');
        if(!$id) {
            $this->tuError('没有选择');
        }else{
			$Delivery = D('Delivery')->where('id =' . $id)->find();
			$users = D('Users')->find($Delivery['user_id']);
            $this->assign('delivery', D('Delivery')->where('id =' . $id)->find());
            $dvo = D('DeliveryOrder');
            import('ORG.Util.Page');
			
			if($order_id = (int) $this->_param('order_id')){
				$map['order_id'] = $order_id;
				$this->assign('order_id', $order_id);
			}
		    if(isset($_GET['st']) || isset($_POST['st'])){
				$st = (int) $this->_param('st');
				if($st != 999){
					$map['status'] = $st;
				}
				$this->assign('st', $st);
			}else{
				$this->assign('st', 999);
			}
            $count = $dvo->where('delivery_id =' . $id)->count();
            $Page = new Page($count, 25);
            $show = $Page->show();
            $list = $dvo->where('delivery_id =' . $id)->order('order_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $this->assign('list', $list);
            $this->assign('page', $show);
            $this->display();
        }
    }
	
	//新增选择配送员
	public function select(){
        $obj = D('Delivery');
        import('ORG.Util.Page'); 
        $map = array();
        if($name = $this->_param('name','htmlspecialchars')){
            $map['name'] = array('LIKE','%'.$name.'%');
            $this->assign('name',$name);
        }
        $count =  $obj->where($map)->count(); 
        $Page = new Page($count, 8); 
        $pager = $Page->show(); 
        $list =  $obj->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
        $this->assign('list', $list);
        $this->assign('page', $pager); 
        $this->display(); 
        
    }
	
	public function delete($id = 0){
        if (is_numeric($id) && ($id = (int) $id)) {
            $obj = D('Delivery');
            $obj->save(array('id' => $id,'closed'=>1));
            $this->tuSuccess('删除成功', U('delivery/index'));
        } else {
            $id = $this->_post('id', false);
            if (is_array($id)) {
                $obj = D('Delivery');
                foreach ($id as $id) {
                    $obj->save(array('id'=>$id, 'closed'=>1));
                }
                $this->tuSuccess('删除成功', U('delivery/index'));
            }
            $this->tuError('请选择要删除的配送员');
        }
    }
    public function audit($id = 0){
        if (is_numeric($id) && ($id = (int) $id)) {
            $obj = D('Delivery');
            $obj->save(array('id' => $id, 'audit' => 1));
            $this->tuSuccess('审核成功', U('delivery/index'));
        } else {
            $id = $this->_post('id', false);
            if (is_array($id)) {
                $obj = D('Delivery');
                foreach ($id as $id) {
                    $obj->save(array('id' => $id, 'audit' => 1));
                }
                $this->tuSuccess('审核成功', U('delivery/index'));
            }
            $this->tuError('请选择要审核的配送员');
        }
    }
	
	public function order() {
        $DeliveryOrder = D('DeliveryOrder');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0);
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
        if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
		
		if (isset($_GET['type']) || isset($_POST['type'])) {
            $type = (int) $this->_param('type');
            if ($type != 999) {
                $map['type'] = $type;
            }
            $this->assign('type', $type);
        } else {
            $this->assign('type', 999);
        }
		
        $count = $DeliveryOrder->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $DeliveryOrder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
	
	public function del_order($order_id = 0){
            $order_id = (int) $order_id;
			$obj = D('DeliveryOrder');
			if (!$detail = $obj->find($order_id)) {
                $this->tuError('没有找到该订单号');
            }
			if($detail['status'] >1 ){
				$this->tuError('当前状态不能删除该订单');
			}
            $obj->save(array('order_id' => $order_id,'closed'=>1));
            $this->tuSuccess('删除成功', U('delivery/order'));
        
    }
	//配送员所有的费用记录
	 public function finance(){
        $Runningmoney = D('Runningmoney');
        import('ORG.Util.Page');
		if(($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
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
		if($order_id = (int) $this->_param('order_id')){
			$map['order_id'] = $order_id;
			$this->assign('order_id', $order_id);
		}
        if($user_id = (int) $this->_param('user_id')){
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
		if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if($keyword = $this->_param('keyword', 'htmlspecialchars')){
            $map['intro'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		if(isset($_GET['type']) || isset($_POST['type'])){
            $type = $this->_param('type');
            if($type == 1) {
                $map['type'] = goods;
            }elseif($type == 2){
				$map['type'] = ele;
			}elseif($type == 3){
				$map['type'] = running;
			}else{
				$map['type'] = $type;
			}
            $this->assign('type', $type);
        }else{
            $this->assign('type', 999);
        }
        $count = $Runningmoney->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Runningmoney->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	

    public function comment(){
        $obj = D('DeliveryComment');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
       
        if($comment_id= (int) $this->_param('comment_id')){
            $map['comment_id'] = $comment_id;
            $this->assign('comment_id', $comment_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $user = D('Users')->find($user_id);
            $this->assign('nickname', $user['nickname']);
            $this->assign('user_id', $user_id);
        }
        $count = $obj->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $obj->where($map)->order(array('comment_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $list[$k] = $val;
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        if(!empty($user_ids)){
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if(!empty($shop_ids)){
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
           
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

    public function comment_create(){
        if ($this->isPost()) {
            $data = $this->comment_createCheck();
            $obj = D('DeliveryComment');
            if($comment_id= $obj->add($data)){
                $photos = $this->_post('photos', false);
                $local = array();
                foreach($photos as $val){
                    if(isImage($val))
                        $local[] = $val;
                }
                if(!empty($local))
                    D('DeliveryCommentPics')->upload($comment_id, $local,$data['order_id']);
                $this->tuSuccess('添加成功', U('delivery/comment'));
            }
            $this->tuError('操作失败');
        }else{
            $this->display();
        }
    }

    private function comment_createCheck() {
        $data = $this->checkFields($this->_post('data', false), array('user_id','order_id','score','d1', 'd2','d3','content','reply'));
        $data['user_id'] = (int) $data['user_id'];
        if(empty($data['user_id'])){
            $this->tuError('用户不能为空');
        }
        $data['order_id'] = (int) $data['order_id'];
        if(empty($data['order_id'])){
            $this->tuError('配送订单号不能为空');
        }
        if(!$order = D('DeliveryOrder')->where(array('order_id'=>$data['order_id']))->find()){
            $this->tuError('配送订单不存在');
        }
		$data['shop_id'] = $order['shop_id'];
		$data['user_id'] = $order['user_id'];
		$data['type'] = $order['type'];
		$data['type_order_id'] = $order['type_order_id'];
		$data['delivery_id'] = $order['delivery_id'];
        $data['score'] = (int) $data['score'];
        if(empty($data['score'])){
            $this->tuError('评分不能为空');
        }
        if($data['score'] > 5 || $data['score'] < 1){
            $this->tuError('评分为1-5之间的数字');
        }
		$config = $config = D('Setting')->fetchAll();
		$data['d1'] = (int) $data['d1'];
		if(empty($data['d1'])){
			$this->tuError($config['delivery']['d1'].'评分不能为空');
		}
		if($data['d1'] > 5 || $data['d1'] < 1){
			$this->tuError($config['delivery']['d1'].'格式不正确');
		}
		$data['d2'] = (int) $data['d2'];
		if(empty($data['d2'])){
			$this->tuError($config['delivery']['d2'].'评分不能为空');
		}
		if($data['d2'] > 5 || $data['d2'] < 1){
			$this->tuError($config['delivery']['d2'].'格式不正确');
		}
		$data['d3'] = (int) $data['d3'];
		if(empty($data['d3'])){
			$this->tuError($config['delivery']['d3'].'评分不能为空');
		}
		if($data['d3'] > 5 || $data['d3'] < 1){
			$this->tuError($config['delivery']['d3'].'格式不正确');
		}

        $data['content'] = htmlspecialchars($data['content']);
        if(empty($data['content'])){
            $this->tuError('评价内容不能为空');
        }
        $data['reply'] = htmlspecialchars($data['reply']);
		if($data['reply']){
			$data['reply_time'] = time();
        	$data['reply_ip'] = get_client_ip();
		}
		
        $data['create_time'] = time();
        $data['create_ip'] = get_client_ip();
        return $data;
    }


    public function comment_edit($comment_id= 0){
        if($comment_id= (int) $comment_id){
            $obj = D('DeliveryComment');
            if(!$detail = $obj->find($comment_id)){
                $this->tuError('请选择要编辑的点评');
            }
            if($this->isPost()){
                $data = $this->comment_editCheck();
                $data['comment_id'] = $comment_id;
                if(false !== $obj->save($data)){
                    $photos = $this->_post('photos', false);
                    $local = array();
                    foreach($photos as $val){
                        if(isImage($val))
                            $local[] = $val;
                    }
                    if(!empty($local))
                        D('DeliveryCommentPics')->upload($comment_id, $local,$detail['order_id']);
                    	$this->tuSuccess('操作成功', U('delivery/comment'));
                	}
                	$this->tuError('操作失败');
            }else{
                $this->assign('detail', $detail);
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->assign('photos', D('DeliveryCommentPics')->getPics($comment_id));
                $this->display();
            }
        }else{
            $this->tuError('请选择要编辑的点评');
            
        }
    }

    private function comment_editCheck() {
        $data = $this->checkFields($this->_post('data', false), array('user_id','order_id','score','d1', 'd2','d3','content','reply'));
		$data['comment_id'] = $comment_id;
        $data['user_id'] = (int) $data['user_id'];
        if(empty($data['user_id'])){
            $this->tuError('用户不能为空');
        }
        $data['order_id'] = (int) $data['order_id'];
        if(empty($data['order_id'])){
            $this->tuError('配送订单号不能为空');
        }
        if(!$order = D('DeliveryOrder')->find($data['order_id'])){
            $this->tuError('配送订单不存在');
        }
		$data['shop_id'] = $order['shop_id'];
		$data['user_id'] = $order['user_id'];
		$data['type'] = $order['type'];
		$data['type_order_id'] = $order['type_order_id'];
		$data['delivery_id'] = $order['delivery_id'];
        $data['score'] = (int) $data['score'];
        if(empty($data['score'])){
            $this->tuError('评分不能为空');
        }
        if($data['score'] > 5 || $data['score'] < 1){
            $this->tuError('评分为1-5之间的数字');
        }
		$config = $config = D('Setting')->fetchAll();
		$data['d1'] = (int) $data['d1'];
		if(empty($data['d1'])){
			$this->tuError($config['delivery']['d1'].'评分不能为空');
		}
		if($data['d1'] > 5 || $data['d1'] < 1){
			$this->tuError($config['delivery']['d1'].'格式不正确');
		}
		$data['d2'] = (int) $data['d2'];
		if(empty($data['d2'])){
			$this->tuError($config['delivery']['d2'].'评分不能为空');
		}
		if($data['d2'] > 5 || $data['d2'] < 1){
			$this->tuError($config['delivery']['d2'].'格式不正确');
		}
		$data['d3'] = (int) $data['d3'];
		if(empty($data['d3'])){
			$this->tuError($config['delivery']['d3'].'评分不能为空');
		}
		if($data['d3'] > 5 || $data['d3'] < 1){
			$this->tuError($config['delivery']['d3'].'格式不正确');
		}

        $data['content'] = htmlspecialchars($data['content']);
        if(empty($data['content'])){
            $this->tuError('评价内容不能为空');
        }
        $data['reply'] = htmlspecialchars($data['reply']);
		if($data['reply']){
			$data['reply_time'] = time();
        	$data['reply_ip'] = get_client_ip();
		}
        return $data;
    }

	//删除点评
	 public function comment_delete($comment_id= 0) {
        if(is_numeric($comment_id) && ($comment_id= (int) $comment_id)){
            $obj = D('DeliveryComment');
            $obj->save(array('comment_id' => $comment_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('delivery/comment'));
        }else{
            $comment_id= $this->_post('comment_id', false);
            if(is_array($comment_id)){
                $obj = D('DeliveryComment');
                foreach ($comment_id as $id) {
                    $obj->save(array('comment_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('批量删除成功', U('delivery/comment'));
            }
            $this->tuError('请选择要删除的点评');
        }
    }
	
	
	//标签列表
    public function tag() {
        $obj = D('DeliveryCommentTag');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        if($keyword){
            $map['tagName'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }  
        $count = $obj->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $obj->where($map)->order(array('orderby' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
	
	
	//添加标签
	public function tag_create(){
        if($this->isPost()){
            $data = $this->checkFields($this->_post('data', false), array('tagName','orderby'));
			$data['tagName'] = htmlspecialchars($data['tagName']);
			if(empty($data['tagName'])){
				$this->tuError('标签名称不能为空');
			}
			$data['orderby'] = (int) $data['orderby'];
            $obj = D('DeliveryCommentTag');
            if($obj->add($data)){
                $this->tuSuccess('添加标签成功', U('delivery/tag'));
            }
            $this->tuError('操作失败');
        }else{
            $this->display();
        }
    }

    //编辑标签
    public function tag_edit($tag_id= 0){
        if($tag_id= (int) $tag_id){
            $obj = D('DeliveryCommentTag');
            if(!$detail = $obj->find($tag_id)){
                $this->tuError('请选择要编辑的标签');
            }
            if($this->isPost()){
                $data = $this->checkFields($this->_post('data', false), array('tagName','orderby'));
				$data['tagName'] = htmlspecialchars($data['tagName']);
				if(empty($data['tagName'])){
					$this->tuError('标签名称不能为空');
				}
				$data['orderby'] = (int) $data['orderby'];
                $data['tag_id'] = $tag_id;
                if(false !== $obj->save($data)){
                  $this->tuSuccess('操作成功', U('delivery/tag')); 
                }   	
            }else{
                $this->assign('detail', $detail);
                $this->display();
            }
        }else{
            $this->tuError('请选择要编辑的标签');
            
        }
    }
	
	//删除点评
	 public function tag_delete($tag_id= 0) {
        if(is_numeric($tag_id) && ($tag_id= (int) $tag_id)){
            $obj = D('DeliveryCommentTag');
            $obj->save(array('tag_id' => $tag_id, 'closed' => 1));
            $this->tuSuccess('删除标签成功', U('delivery/tag'));
        }else{
            $tag_id= $this->_post('tag_id', false);
            if(is_array($tag_id)){
                $obj = D('DeliveryCommentTag');
                foreach ($tag_id as $id) {
                    $obj->save(array('tag_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('批量删除标签成功', U('delivery/tag'));
            }
            $this->tuError('请选择要删除的标签');
        }
    }
	
	
}