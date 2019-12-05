<?php
//商家中心的控制器不要搞错了
class EduAction extends CommonAction {  
   private $cate_create_fields = array('cate_name', 'photo', 'orderby');
   private $cate_edit_fields = array('cate_name', 'photo', 'orderby');
   private $course_create_fields = array('edu_id','title', 'photo','type','price','course_price','cate_id','age_id', 'time_id', 'type_id', 'class_id','class_time', 'course_time', 'apply_time','is_refund','details');
   private $course_edit_fields = array('edu_id','title', 'photo','type','price','course_price','cate_id','age_id', 'time_id', 'type_id','class_id', 'class_time', 'course_time', 'apply_time','is_refund','details');
    private $comment_create_fields = array('user_id', 'shop_id', 'order_id','edu_id', 'score',  'content', 'reply');
    private $comment_edit_fields = array('user_id', 'shop_id', 'order_id', 'edu_id','score',  'content', 'reply');
   
   public function _initialize() {
        parent::_initialize();
		if(empty($this->_CONFIG['operation']['edu'])) {
            $this->error('教育功能已关闭');
            die;
        }
        $this->age = D('Edu')->getEduage();
        $this->assign('age', $this->age);
        $this->get_time = D('Edu')->getEduTime();
        $this->assign('get_time', $this->get_time);
		$this->get_edu_class = D('Edu')->getEduClass();
        $this->assign('class', $this->get_edu_class);
		$this->assign('cates', D('Educate')->fetchAll());
		$this->assign('types', D('EduOrder')->getType());
		$this->edu_id = D('Edu')->get_edu_id($this->shop_id);
    }
   
    private function check_edu(){
        $edu = D('Edu');
        $res =  $edu->where(array('shop_id'=>$this->shop_id))->find();
        if(!$res){
            $this->error('请先完善教育频道资料！',U('edu/set_edu'));
        }elseif($res['audit'] == 0){
            $this->error('您的教育频道申请正在审核中，请耐心等待！',U('edu/set_edu'));
        }elseif($res['audit'] == 2){
            $this->error('您的教育频道申请未通过审核！',U('edu/set_edu'));
        }else{
            return $res['edu_id'];
        }
    }
    
    public function index(){
        $edu_id = $this->check_edu();
        $EduOrder = M('EduOrder'); 
        import('ORG.Util.Page');
		$map = array('edu_id'=>$edu_id);
		if ($order_id = (int) $this->_param('order_id')) {
            $map['order_id'] = $order_id;
            $this->assign('order_id', $order_id);
        }
		if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['order_status'] = $st;
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
        $count = $EduOrder->where($map)->count();
        $Page  = new Page($count,25);
        $show  = $Page->show();
        $list = $EduOrder->where($map)->order('order_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$course_ids = $user_ids =  array();
        foreach($list as $k => $v){
			$course_ids[$v['course_id']] = $v['course_id'];
			$user_ids[$v['user_id']] = $v['user_id'];
        }
		$this->assign('courses', D('Educourse')->itemsByIds($course_ids));
		$this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display(); 
    }
    
    //编辑教育商家
    public function set_edu(){
        $obj = D('Edu');
        $edu = $obj->where(array('shop_id'=>$this->shop_id))->find();
        if ($this->isPost()) { 
           $data = $this->createCheck();
            if (empty($edu)) {
                $data['create_time'] = NOW_TIME;
                $data['create_ip'] = get_client_ip();
                if($edu_id = $obj->add($data)){
                     $this->tuSuccess('设置成功', U('edu/index'));
                }else{
                    $this->tuError('设置失败');
                }
            }else{
                $data['update_time'] = NOW_TIME;
                $data['update_ip'] = get_client_ip();
                $data['audit'] = 0;
                if(false !== $obj->save($data)){
                    $this->tuSuccess('修改成功', U('edu/index'));
                }else{
                    $this->tuError('修改失败');
                }
            }
        } else {
			$this->assign('parent_id',D('Educate')->getParentsId($edu['cate_id']));
            $this->assign('edu',$edu);
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    //教育商家编辑验证
     private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), array('shop_id', 'edu_name','intro', 'tel', 'photo', 'addr','cate_id', 'city_id', 'area_id', 'business_id','lat', 'lng', 'rate', 'details'));
		$data['shop_id'] = $this->shop_id;
        if(empty($data['shop_id'])){
            $this->tuError('商家不能为空');
        }elseif(!$shop = D('Shop')->find($data['shop_id'])){
            $this->tuError('商家不存在');
        }
        $data['area_id'] = $shop['area_id'];
        $data['business_id'] = $shop['business_id'];
        $data['city_id'] = $shop['city_id'];
		$data['cate_id'] = (int)$data['cate_id'];
		if (empty($data['cate_id'])) {
            $this->tuError('教育商家分类不能为空');
        }
        $data['edu_name'] = htmlspecialchars($data['edu_name']);
        if (empty($data['edu_name'])) {
            $this->tuError('名称不能为空');
        }$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('简介不能为空');
        }$data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->tuError('地址不能为空');
        }$data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel'])) {
            $this->tuError('联系电话不能为空');
        }
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
          $this->tuError('坐标没有选择');
        }
		$data['rate'] = (int)$data['rate'];
		if (empty($data['rate'])) {
            $this->tuError('结算费率不能为空');
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
            $this->tuError('详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->tuError('详情含有敏感词：' . $words);
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['audit'] = 0;
        return $data;
    }
   
   //课程列表
    public function course(){ 
	    $this->check_edu();
		$Edu = D('Edu');
        if (!$detail = $Edu->where(array('edu_id'=>$this->edu_id))->find()) {
          $this->tuError('请选择要编辑的课程');
        }
		if ($detail['edu_id'] != $this->edu_id) {
          $this->tuError('请不要打开别人的课程');
        }
        $Educourse = D('Educourse');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0,'edu_id'=>$this->edu_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
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
		if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Educate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        $count = $Educourse->where($map)->count();
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Educourse->where($map)->order(array('course_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('detail',$detail);
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display();
    }

    
    //添加课程
    public function course_create(){ 
	    $this->check_edu();
		$Edu = D('Edu');
        if (!$detail = $Edu->where(array('edu_id'=>$this->edu_id))->find()) {
          $this->tuError('请选择要编辑的课程');
        }
		if ($detail['edu_id'] != $this->edu_id) {
          $this->tuError('请不要添加别人的课程');
        }
        if ($this->isPost()) {
			$data = $this->course_createCheck();
			$data['edu_id'] = $this->edu_id;
            $obj = D('Educourse');
            if ($course_id = $obj->add($data)){
                $this->tuSuccess('添加成功', U('edu/course',array('edu_id'=>$detail['edu_id'])));
            }
            $this->tuError('操作失败');
        } else {
			$this->assign('edu_id',$edu_id);
            $this->display();
        }
    }
    
    
    private function course_createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->course_create_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('课程名称不能为空');
        }
		$data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传课程封面');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('课程封面格式不正确');
        } 
		$data['type'] = 0;
		$data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->tuError('原价不能为空');
        }
		$data['course_price'] = (int) ($data['course_price'] * 100);//完整课程价格
		if($data['type'] ==0){
			if (empty($data['course_price'])) {
				$this->tuError('课程销售价不能为空');
			}
		}
		
		$data['cate_id'] = (int) $data['cate_id'];//ID
		if (empty($data['cate_id'])) {
				$this->tuError('类型ID不能为空');
		}
		$Educate= D('Educate')->where(array('cate_id' => $data['cate_id']))->find();
		if ($Educate['parent_id'] == 0) {
			$this->tuError('请选择二级分类');
		}
		$data['age_id'] = (int) $data['age_id'];
		if (empty($data['age_id'])) {
				$this->tuError('年龄阶段不能为空');
		}
		$data['time_id'] = (int) $data['time_id'];
		if (empty($data['time_id'])) {
				$this->tuError('学时阶段不能为空');
		}
		$data['class_id'] = (int) $data['class_id'];
		if (empty($data['class_id'])) {
				$this->tuError('类型不能为空');
		}
		$data['class_time'] = htmlspecialchars($data['class_time']);
		$data['course_time'] = htmlspecialchars($data['course_time']);
		$data['apply_time'] = htmlspecialchars($data['apply_time']);
		$data['is_refund'] = (int) $data['is_refund'];
		$data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->tuError('详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->tuError('详情含有敏感词：' . $words);
        }
		$data['audit'] = 0;
		$data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    
     
   //编辑课程 
    public function course_edit($course_id = 0){
		$this->check_edu();
		$Edu = D('Edu');
        if (!$detail_edit = $Edu->where(array('edu_id'=>$this->edu_id))->find()) {
          $this->tuError('请选择要编辑的课程');
        }
		if ($detail_edit['edu_id'] != $this->edu_id) {
          $this->tuError('请不要打开别人的课程');
        }
        if ($course_id = (int) $course_id) {
            $obj = D('Educourse');
            if (!$detail = $obj->find($course_id)) {
                $this->tuError('请选择要编辑的课程');
            }
            if ($this->isPost()) {
                $data = $this->course_editCheck();
				$data['edu_id'] = $this->edu_id;
                $data['course_id'] = $course_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('保存成功', U('edu/course'));
                }
                $this->tuError('操作失败');
            } else {
				$this->assign('parent_id',D('Educate')->getParentsId($detail['cate_id']));
				$this->assign('detail_edit',$detail_edit);
                $this->assign('detail',$detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的课程');
        }
    }
	
	 private function course_editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->course_edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('课程名称不能为空');
        }
		$data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传课程封面');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('课程封面格式不正确');
        } 
		$data['type'] = 0;
		$data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->tuError('原价不能为空');
        }
		$data['course_price'] = (int) ($data['course_price'] * 100);//完整课程价格
		if($data['type'] ==0){
			if (empty($data['course_price'])) {
				$this->tuError('课程销售价不能为空');
			}
		}
		
		$data['cate_id'] = (int) $data['cate_id'];//ID
		if (empty($data['cate_id'])) {
				$this->tuError('类型ID不能为空');
		}
		$Educate= D('Educate')->where(array('cate_id' => $data['cate_id']))->find();
		if ($Educate['parent_id'] == 0) {
			$this->tuError('请选择二级分类');
		}
		$data['age_id'] = (int) $data['age_id'];
		if (empty($data['age_id'])) {
				$this->tuError('年龄阶段不能为空');
		}
		$data['time_id'] = (int) $data['time_id'];
		if (empty($data['time_id'])) {
				$this->tuError('学时阶段不能为空');
		}
		$data['class_id'] = (int) $data['class_id'];
		if (empty($data['class_id'])) {
				$this->tuError('类型不能为空');
		}
		$data['class_time'] = htmlspecialchars($data['class_time']);
		$data['course_time'] = htmlspecialchars($data['course_time']);
		$data['apply_time'] = htmlspecialchars($data['apply_time']);
		$data['is_refund'] = (int) $data['is_refund'];
		$data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->tuError('详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->tuError('详情含有敏感词：' . $words);
        }
		$data['audit'] = 0;
        return $data;
    }

    
   //删除课程
    public function course_delete($course_id = 0){
		$this->check_edu();
		$obj = D('Edu');
        if ($course_id = (int) $course_id) {
            $obj = D('Educourse');
            if (!$detail = $obj->find($course_id)) {
                $this->tuError('请选择要删除的课程');
            }
			if ($detail['edu_id'] != $this->edu_id) {
			  $this->tuError('请不要操作别人的课程');
			}
            if (false !== $obj->save(array('course_id' => $course_id, 'closed' => 1))) {
                $this->tuSuccess('删除成功', U('edu/course'));
            }else {
                $this->tuError('删除失败');
            }
        } else {
            $this->tuError('请选择要删除的课程');
        }
    }
   //课程取消订单
    public function cancel($order_id){
        $this->check_edu();
        if($order_id = (int) $order_id){
            if(!$order = D('EduOrder')->find($order_id)){
                $this->tuError('订单不存在');
            }elseif($order['edu_id'] != $this->edu_id){
                $this->tuError('非法操作订单');
            }elseif($order['order_status'] == -1){
                $this->tuError('该订单已取消');
            }else{
                if(false !== D('EduOrder')->cancel($order_id)){
                    $this->tuSuccess('订单取消成功',U('edu/index'));
                }else{
                    $this->tuError('订单取消失败');
                }
            }
        }else{
            $this->tuError('请选择要取消的订单');
        }
    }
    
    //课程完整验证订单
    public function complete($order_id){
        $this->check_edu();
        if($order_id = (int) $order_id){
            if(!$order = D('EduOrder')->find($order_id)){
                $this->tuError('订单不存在');
            }elseif($order['edu_id'] != $this->edu_id){
                $this->tuError('非法操作订单');
            }elseif($order['order_status'] != 1){
                $this->tuError('该订单无法完成');
            }else{
                if(false !== D('EduOrder')->complete($order_id)){
                    $this->tuSuccess('订单操作成功',U('edu/index'));
                }else{
                    $this->tuError('订单操作失败');
                }
            }
        }else{
            $this->tuError('请选择要完成的订单');
        }
    }
    
    //删除教育订单
    public function delete($order_id){
        $this->check_edu();
        if($order_id = (int) $order_id){
            if(!$order = D('FarmOrder')->find($order_id)){
                $this->tuError('订单不存在');
            }elseif($order['edu_id'] != $this->edu_id){
                $this->tuError('非法操作订单');
            }elseif($order['order_status'] != -1){
                $this->tuError('订单状态不正确');
            }else{
                if(false !== D('FarmOrder')->save(array('order_id'=>$order_id,'closed'=>1))){
                    $this->tuSuccess('订单删除成功',U('edu/index'));
                }else{
                    $this->tuError('订单删除失败');
                }
            }
        }else{
            $this->tuError('请选择要删除的订单');
        }
    }
    //教育订单详情
    public function detail($order_id=null){
        $this->check_edu();
        if(!$order_id = (int)$order_id){
            $this->error('订单不存在');
        }elseif(!$detail = D('EduOrder')->find($order_id)){
             $this->error('订单不存在');
        }elseif($detail['closed'] == 1){
             $this->error('订单已删除');
        }elseif($detail['edu_id'] != $this->edu_id){
             $this->error('非法的订单操作');
        }else{
            $f = D('Edu')->where(array('edu_id'=>$detail['edu_id']))->find();
            $detail['farm'] = $f;
            $this->assign('detail',$detail);            
            $this->display();
        }
    }
	
	//选择分类
	public function child($parent_id=0){
        $datas = D('Educate')->fetchAll();
        $str = '';
        foreach($datas as $var){
            if($var['parent_id'] == 0 && $var['cate_id'] == $parent_id){
                foreach($datas as $var2){
                    if($var2['parent_id'] == $var['cate_id']){
                        $str.='<option value="'.$var2['cate_id'].'">'.$var2['cate_name'].'</option>'."\n\r";
                        foreach($datas as $var3){
                            if($var3['parent_id'] == $var2['cate_id']){
                               $str.='<option value="'.$var3['cate_id'].'">&nbsp;&nbsp;--'.$var3['cate_name'].'</option>'."\n\r"; 
                            }
                            
                        }
                    }  
                }
                             
            }           
        }
        echo $str;die;
    }


  
}
