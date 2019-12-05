<?php
class FarmAction extends CommonAction {
   public function _initialize() {
        parent::_initialize();
        $this->group = D('Farm')->getFarmGroup();
        $this->assign('group', $this->group);
        $this->cate = D('Farm')->getFarmCate();
        $this->assign('cate', $this->cate);
    }

    public function index() {
        $farm = D('Farm');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'audit' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['farm_name'] = array('LIKE', '%' . $keyword . '%');
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
        $count = $farm->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $farm->where($map)->order(array('farm_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }


    public function noaudit(){
        $farm = D('Farm');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'audit' => array('IN',array(0,2)));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['farm_name'] = array('LIKE', '%' . $keyword . '%');
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
        $count = $farm->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $farm->where($map)->order(array('farm_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show);
        $this->display(); 
    }

    public function create() {
        $obj = D('Farm');
        if ($this->isPost()) {
            $data = $this->createCheck();
            $thumb = $this->_param('thumb', false);
            $cate_id = $this->_param('cate_id',false);
            $group_id = $this->_param('group_id',false);

            foreach ($thumb as $k => $val) {
                if (empty($val)) {
                    unset($thumb[$k]);
                }
                if (!isImage($val)) {
                    unset($thumb[$k]);
                }
            }
            if ($farm_id = $obj->add($data)) {
                foreach($thumb as $k=>$val){
                    D('FarmPics')->add(array('farm_id'=>$farm_id,'photo'=>$val));
                }
                foreach($group_id as $key=>$val){
                    D('FarmGroupAttr')->add(array('shop_id'=>$data['shop_id'],'attr_id'=>$val));
                }
                foreach($cate_id as $k=>$v){
                    D('FarmPlayAttr')->add(array('shop_id'=>$data['shop_id'],'attr_id'=>$v));
                }
                $this->tuSuccess('操作成功', U('farm/index'));
            }
            $this->tuError('操作失败');
        }else{
            $this->display();
        }
       
    }
    
    private function createCheck() {
        
        $data = $this->checkFields($this->_post('data', false), array('shop_id', 'farm_name','intro', 'tel', 'photo', 'addr', 'city_id', 'area_id', 'business_id','price','lat', 'lng', 'business_time', 'details','notice','environmental', 'have_room', 'have_washchange', 'have_wifi', 'have_shower', 'have_tv', 'have_ticket', 'have_toiletries', 'have_hotwater'));

        $data['farm_name'] = htmlspecialchars($data['farm_name']);
        if (empty($data['farm_name'])) {
            $this->tuError('名称不能为空');
        }$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('简介不能为空');
        }$data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->tuError('地址不能为空');
        }$data['price'] = (int)$data['price'];
        if (empty($data['price'])) {
            $this->tuError('起价不能为空');
        }$data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel'])) {
            $this->tuError('联系电话不能为空');
        }
        $data['type'] = (int)$data['type'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
                $this->tuError('坐标没有选择');
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
            $this->tuError('详情不能为空');
        }
        $data['notice'] = SecurityEditorHtml($data['notice']);
        if (empty($data['notice'])) {
            $this->tuError('须知不能为空');
        }
        $data['environmental'] = SecurityEditorHtml($data['environmental']);
        if (empty($data['environmental'])) {
            $this->tuError('环境不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->tuError('详情含有敏感词：' . $words);
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['audit'] = 1;
        return $data;
    }
    
    
    public function edit($farm_id = 0) {

        if ($farm_id = (int) $farm_id) {
            $obj = D('Farm');
            if (!$detail = $obj->where(array('farm_id'=>$farm_id))->find()) {
                $this->tuError('请选择要编辑的农家乐');
            }
            if ($this->isPost()) {
                
                $data = $this->editCheck();
                $thumb = $this->_param('thumb', false);
                $cate_id = $this->_param('cate_id',false);
                $group_id = $this->_param('group_id',false);
                foreach ($thumb as $k => $val) {
                    if (empty($val)) {
                        unset($thumb[$k]);
                    }
                    if (!isImage($val)) {
                        unset($thumb[$k]);
                    }
                }
                $data['farm_id'] = $farm_id;
                if (false !== $obj->save($data)) {
                    D('FarmPics')->where(array('farm_id'=>$farm_id))->delete();
                    foreach($thumb as $k=>$val){
                        D('FarmPics')->add(array('farm_id'=>$farm_id,'photo'=>$val));
                    }
                    D('FarmGroupAttr')->where(array('shop_id'=>$data['shop_id']))->delete();
                    foreach($group_id as $key=>$val){
                        D('FarmGroupAttr')->add(array('shop_id'=>$data['shop_id'],'attr_id'=>$val));
                    }
                    D('FarmPlayAttr')->where(array('shop_id'=>$data['shop_id']))->delete();
                    foreach($cate_id as $k=>$v){
                        D('FarmPlayAttr')->add(array('shop_id'=>$data['shop_id'],'attr_id'=>$v));
                    }
                    $this->tuSuccess('操作成功', U('farm/index'));
                }
                $this->tuError('操作失败');
            } else {
    
                $thumb = D('FarmPics')->where(array('farm_id'=>$farm_id))->select();
               
                $cates = D('Farm')->getFarmCate();
                $groups = D('Farm')->getFarmGroup();
                $new_cates = $new_groups = array();
                
                $cate_id = M('FarmGroupAttr')->where(array('shop_id'=>$detail['shop_id']))->select();
                $group_id = M('FarmPlayAttr')->where(array('shop_id'=>$detail['shop_id']))->select();
          
                foreach($cates as $k => $v){
                    foreach($cate_id as $kk => $vv){
                        $new_cates[$k]['name'] = $v;
                       if($vv['attr_id'] == $k){
                           $new_cates[$k]['sel'] = 1;
                       }
                    }
                }

                foreach($groups as $key => $val){
                    foreach($group_id as $kkey => $vval){
                        $new_groups[$key]['name'] = $val;
                       if($vval['attr_id'] == $key){
                           $new_groups[$key]['sel'] = 1;
                       }
                    }
                }

                $this->assign('thumb', $thumb);
                $this->assign('new_cates', $new_cates);
                $this->assign('new_groups', $new_groups);
                $this->assign('shop',D('Shop')->find($detail['shop_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的农家乐');
        }
    }
    
    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), array('shop_id', 'farm_name','intro', 'tel', 'photo', 'addr', 'city_id', 'area_id', 'business_id','price','lat', 'lng', 'business_time', 'details','notice','environmental', 'have_room', 'have_washchange', 'have_wifi', 'have_shower', 'have_tv', 'have_ticket', 'have_toiletries', 'have_hotwater'));
        $data['have_room'] = $data['have_room'] ? $data['have_room'] : 0;
        $data['have_washchange'] = $data['have_washchange'] ? $data['have_washchange'] : 0;
        $data['have_wifi'] = $data['have_wifi'] ? $data['have_wifi'] : 0;
        $data['have_shower'] = $data['have_shower'] ? $data['have_shower'] : 0;
        $data['have_tv'] = $data['have_tv'] ? $data['have_tv'] : 0;
        $data['have_ticket'] = $data['have_ticket'] ? $data['have_ticket'] : 0;
        $data['have_toiletries'] = $data['have_toiletries'] ? $data['have_toiletries'] : 0;
        $data['have_hotwater'] = $data['have_hotwater'] ? $data['have_hotwater'] : 0;

        
        $data['farm_name'] = htmlspecialchars($data['farm_name']);
        if (empty($data['farm_name'])) {
            $this->tuError('名称不能为空');
        }$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('简介不能为空');
        }$data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->tuError('地址不能为空');
        }$data['cate_id'] = (int)$data['cate_id'];
        if (empty($data['price'])) {
            $this->tuError('起价不能为空');
        }$data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel'])) {
            $this->tuError('联系电话不能为空');
        }
        $data['type'] = (int)$data['type'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
            $this->tuError('坐标没有选择');
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
            $this->tuError('详情不能为空');
        }
        $data['notice'] = SecurityEditorHtml($data['notice']);
        if (empty($data['notice'])) {
            $this->tuError('须知不能为空');
        }
        $data['environmental'] = SecurityEditorHtml($data['environmental']);
        if (empty($data['environmental'])) {
            $this->tuError('环境不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->tuError('详情含有敏感词：' . $words);
        }
        
        
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['audit'] = 1;
        return $data;
    }
    
    
    public function delete($farm_id = 0) {
        $obj = D('Farm');
        if (is_numeric($farm_id) && ($farm_id = (int) $farm_id)) {
			if($obj->where(array('farm_id' => $farm_id))->save(array('closed' => 1))){
				$this->tuSuccess('删除成功', U('farm/index'));
			}else{
				$this->tuError('删除失败');
			}
        } else {
            $farm_id = $this->_post('farm_id', false);
            if (is_array($farm_id)) {
                foreach ($farm_id as $id) {
                    $obj->where(array('farm_id' => $id))->save(array('closed' => 1));
                }
                $this->tuSuccess('删除成功', U('farm/index'));
            }
            $this->tuError('请选择要删除的农家乐');
        }
    }

    public function audit($farm_id = 0) {
        $obj = D('Farm');
        if (is_numeric($farm_id) && ($farm_id = (int) $farm_id)) {
			if($obj->where(array('farm_id' => $farm_id))->save(array('audit' => 1))){
				$this->tuSuccess('审核成功', U('farm/index'));
			}else{
				$this->tuError('审核失败');
			}
        } else {
            $farm_id = $this->_post('farm_id', false);
            if (is_array($farm_id)) {
                foreach ($farm_id as $id) {
					$obj->where(array('farm_id' => $id))->save(array('audit' => 1));
                }
                $this->tuSuccess('审核成功', U('farm/index'));
            }
            $this->tuError('请选择要审核的农家乐');
        }
    }

    public function refuse($farm_id){
        $obj = D('Farm');
         if (is_numeric($farm_id) && ($farm_id = (int) $farm_id)) {
            if ($this->isPost()) {
                $reason = htmlspecialchars($this->_param('reason'));
                if(!$reason){
                    $this->tuError('拒绝理由不能为空');
                }
                $obj->save(array('farm_id' => $farm_id, 'audit' => 2,'reason'=>$reason));
                $this->tuSuccess('操作成功', U('farm/index'));
            }else{
                $this->assign('farm_id',$farm_id);
                $this->display();
            }
         }
    }
	//农家乐订单
	public function order(){
        $fo = M('FarmOrder'); 
        import('ORG.Util.Page');
		$map = array();
		if ($order_id = (int) $this->_param('order_id')) {
            $map['order_id'] = $order_id;
            $this->assign('order_id', $order_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
        $count = $fo->where($map)->count();
        $Page  = new Page($count,25);
        $show  = $Page->show();
        $list = $fo->where($map)->order('farm_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$farm_ids = array();
        foreach($list as $k => $v){
			$farm_ids[$v['farm_id']] = $v['farm_id'];
            $p = D('FarmPackage') -> where(array('pid'=>$v['pid'])) -> find();
            $list[$k]['package'] = $p;
        }
		$this->assign('farms', D('Farm')->itemsByIds($farm_ids));
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display(); 
    }
	//取消农家乐订单
	 public function cancel($order_id){
        if($order_id = (int) $order_id){
            if(!$order = D('FarmOrder')->find($order_id)){
                $this->tuError('订单不存在');
            }elseif($order['order_status'] == -1){
                $this->tuError('该订单已取消');
            }else{
                if(false !== D('FarmOrder')->cancel($order_id)){
                    $this->tuSuccess('订单取消成功',U('farm/order'));
                }else{
                    $this->tuError('订单取消失败');
                }
            }
        }else{
            $this->tuError('请选择要取消的订单');
        }
    }
    
   //客户入住 
    public function complete($order_id){
        if($order_id = (int) $order_id){
            if(!$order = D('FarmOrder')->find($order_id)){
                $this->tuError('订单不存在');
            }elseif($order['order_status'] != 1){
                $this->tuError('该订单无法完成');
            }else{
                if(false !== D('FarmOrder')->complete($order_id)){
                    $this->tuSuccess('订单操作成功',U('farm/order'));
                }else{
                    $this->tuError('订单操作失败');
                }
            }
        }else{
            $this->tuError('请选择要完成的订单');
        }
    }
    
    //删除订单
    public function order_delete($order_id){
        if($order_id = (int) $order_id){
            if(!$order = D('FarmOrder')->find($order_id)){
                $this->tuError('订单不存在');
            }elseif($order['order_status'] != -1){
                $this->tuError('订单状态不正确');
            }else{
                if(false !== D('FarmOrder')->save(array('order_id'=>$order_id,'closed'=>1))){
                    $this->tuSuccess('订单删除成功',U('farm/order'));
                }else{
                    $this->tuError('订单删除失败');
                }
            }
        }else{
            $this->tuError('请选择要删除的订单');
        }
    }
	
	//套餐列表
    public function package($farm_id = 0){ 
		$farm_id = (int) $farm_id;
		$Farm = D('Farm');
        if (!$detail = $Farm->where(array('farm_id'=>$farm_id))->find()) {
          $this->tuError('请选择要编辑的农家乐');
        }
        $fp = D('FarmPackage');
        import('ORG.Util.Page'); 
        $map = array('farm_id' =>$farm_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $fp->where($map)->count();
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $fp->where($map)->order(array('pid' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('detail',$detail);
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display();
    }

    
    //添加套餐
    public function setpackage($farm_id = 0){ 
		$farm_id = (int) $farm_id;
		$Farm = D('Farm');
        if (!$detail = $Farm->find($farm_id)) {
          $this->tuError('请选择要编辑的套餐');
        }
        if ($this->isPost()) {
            $data = $this->roomCreateCheck();
			$data['farm_id'] = $farm_id;
            $fp = D('FarmPackage');
            if ($farm_id = $fp->add($data)) {
                $this->tuSuccess('添加成功', U('farm/package',array('farm_id'=>$detail['farm_id'])));
            }
            $this->tuError('操作失败');
        } else {
			$this->assign('detail',$detail);
            $this->display();
        }
    }
    
    
    private function roomCreateCheck() {
        $data = $this->checkFields($this->_post('data', false), array('title', 'price','jiesuan_price'));
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('套餐名称不能为空');
        }$data['price'] = (int)$data['price'];
        if (empty($data['price'])) {
            $this->tuError('套餐价格不能为空');
        }$data['jiesuan_price'] = (int)$data['jiesuan_price'];
        if (empty($data['jiesuan_price'])) {
            $this->tuError('套餐结算价格不能为空');
        }if ($data['jiesuan_price'] >=$data['price']) {
            $this->tuError('结算价格不能大于套餐价格');
        }
        return $data;
    }
    
     
   //编辑套餐 
    public function editpackage($pid = 0,$farm_id = 0){
		$farm_id = (int) $farm_id;
		$obj = D('Farm');
        if (!$farm = $obj->find($farm_id)) {
          $this->tuError('请选择要编辑的套餐');
        }
        if ($pid = (int) $pid) {
            $obj = D('FarmPackage');
            if (!$detail = $obj->find($pid)) {
                $this->tuError('请选择要编辑的套餐');
            }
            if ($this->isPost()) {
                $data = $this->packageEditCheck();
				$data['farm_id'] = $farm_id;
                $data['pid'] = $pid;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('保存成功', U('farm/package',array('farm_id'=>$farm_id)));
                }
                $this->tuError('操作失败');
            } else {
				$this->assign('farm',$farm);
                $this->assign('detail',$detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的套餐');
        }
    }
	
	 private function packageEditCheck() {
        $data = $this->checkFields($this->_post('data', false), array('title', 'price','jiesuan_price'));
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('套餐名称不能为空');
        }$data['price'] = (int)$data['price'];
        if (empty($data['price'])) {
            $this->tuError('套餐价格不能为空');
        }$data['jiesuan_price'] = (int)$data['jiesuan_price'];
        if (empty($data['jiesuan_price'])) {
            $this->tuError('套餐结算价格不能为空');
        }if ($data['jiesuan_price'] >=$data['price']) {
            $this->tuError('结算价格不能大于套餐价格');
        }
        return $data;
    }
	//删除套餐
    public function deletepackage($pid = 0,$farm_id = 0){
		$farm_id = (int) $farm_id;
		$obj = D('Farm');
        if (!$farm = $obj->find($farm_id)) {
          $this->tuError('请选择要编辑的套餐');
        }
        if ($pid = (int) $pid) {
            $obj = D('FarmPackage');
            if (!$detail = $obj->find($pid)) {
                $this->tuError('请选择要删除的套餐');
            }
            if (false !== $obj->delete($pid)) {
                $this->tuSuccess('删除成功', U('farm/package',array('farm_id'=>$farm_id)));
            }else {
                $this->tuError('删除失败');
            }
        } else {
            $this->tuError('请选择要删除的套餐');
        }
    }    
    
   
   
   private $comment_create_fields = array('user_id', 'shop_id', 'order_id','farm_id', 'score',  'content', 'reply');
    private $comment_edit_fields = array('user_id', 'shop_id', 'order_id', 'farm_id','score',  'content', 'reply');
	//农家乐点评
    public function comment() {
        $FarmComment = D('FarmComment');
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
        $count = $FarmComment->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $FarmComment->where($map)->order(array('comment_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
	//添加农家乐点评
    public function comment_create() {
        if ($this->isPost()) {
            $data = $this->comment_createCheck();
            $obj = D('FarmComment');
            if ($comment_id = $obj->add($data)) {
                $photos = $this->_post('photos', false);
                $local = array();
                foreach ($photos as $val) {
                    if (isImage($val))
                        $local[] = $val;
                }
                if (!empty($local))
                    D('FarmCommentPics')->upload($comment_id, $local);
                $this->tuSuccess('添加成功', U('farm/comment'));
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
        //农家乐订单ID找到对应信息开始
        $data['order_id'] = (int) $data['order_id'];
        if (empty($data['order_id'])) {
            $this->tuError('订单ID不能为空');
        }
        if (!$FarmOrder = D('FarmOrder')->find($data['order_id'])) {
            $this->tuError('订单ID不存在');
        }
		$Farm = D('Farm')->find($FarmOrder['farm_id']);
        $data['shop_id'] = (int) $Farm['shop_id'];
        $data['farm_id'] = (int) $FarmOrder['farm_id'];
		//农家乐订单ID找到对应信息结
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
	//农家乐点评编辑
   public function comment_edit($comment_id = 0) {
        if ($comment_id = (int) $comment_id) {
            $obj = D('FarmComment');
            if (!$detail = $obj->find($comment_id)) {
                $this->tuError('请选择要编辑的农家乐点评');
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
                        D('FarmCommentPics')->upload($comment_id, $local);
						D('Users')->prestige($data['user_id'], 'dianping');
                        D('Users')->updateCount($data['user_id'], 'ping_num');
                    	$this->tuSuccess('操作成功', U('farm/comment'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->assign('photos', D('FarmCommentPics')->get_pic($comment_id));
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的农家乐点评');
            
        }
    }

    private function comment_editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->comment_edit_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->tuError('用户不能为空');
        }
		//农家乐订单ID找到对应信息开始
        $data['order_id'] = (int) $data['order_id'];
        if (empty($data['order_id'])) {
            $this->tuError('订单ID不能为空');
        }
        if (!$FarmOrder = D('FarmOrder')->find($data['order_id'])) {
            $this->tuError('订单ID不存在');
        }
		$Farm = D('Farm')->find($FarmOrder['farm_id']);
        $data['shop_id'] = (int) $Farm['shop_id'];
        $data['farm_id'] = (int) $FarmOrder['farm_id'];
		//农家乐订单ID找到对应信息结束
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
	//农家乐点评删除
	 public function comment_delete($comment_id = 0) {
        if (is_numeric($comment_id) && ($comment_id = (int) $comment_id)) {
            $obj = D('FarmComment');
            $obj->delete($comment_id);
            $this->tuSuccess('删除成功', U('farm/comment'));
        } else {
            $comment_id = $this->_post('comment_id', false);
            if (is_array($comment_id)) {
                $obj = D('FarmComment');
                foreach ($comment_id as $id) {
                     $obj->delete($id);
                }
                $this->tuSuccess('删除成功', U('farm/comment'));
            }
            $this->tuError('请选择要删除的农家乐点评');
        }
    }

    
}
