<?php
class EduAction extends CommonAction {
   private $cate_create_fields = array('cate_name', 'photo', 'orderby');
   private $cate_edit_fields = array('cate_name', 'photo', 'orderby');
   private $course_create_fields = array('edu_id','title', 'photo','type','price','course_price','test_price','cate_id','age_id', 'time_id', 'type_id', 'class_id','class_time', 'course_time', 'apply_time','is_refund', 'views','sale','orderby','audit','details');
   private $course_edit_fields = array('edu_id','title', 'photo','type','price','course_price','test_price','cate_id','age_id', 'time_id', 'type_id','class_id', 'class_time', 'course_time', 'apply_time','is_refund', 'views','sale','orderby','audit','details');
    private $comment_create_fields = array('user_id', 'shop_id', 'order_id','edu_id', 'score',  'content', 'reply');
    private $comment_edit_fields = array('user_id', 'shop_id', 'order_id', 'edu_id','score',  'content', 'reply');
   
   public function _initialize() {
        parent::_initialize();
        $this->age = D('Edu')->getEduage();
        $this->assign('age', $this->age);
        $this->get_time = D('Edu')->getEduTime();
        $this->assign('get_time', $this->get_time);
		$this->get_edu_class = D('Edu')->getEduClass();
        $this->assign('class', $this->get_edu_class);
		$this->assign('cates', D('Educate')->fetchAll());
		$this->assign('types', D('EduOrder')->getType());
    }

    public function index() {
        $Edu = D('Edu');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'audit' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['edu_name'] = array('LIKE', '%' . $keyword . '%');
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
        $count = $Edu->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Edu->where($map)->order(array('edu_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }


    public function noaudit(){
        $Edu = D('Edu');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'audit' => array('IN',array(0,2)));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['edu_name'] = array('LIKE', '%' . $keyword . '%');
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
            $map['cate_id'] = array('IN', D('Educate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        $count = $Edu->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Edu->where($map)->order(array('edu_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show);
        $this->display(); 
    }

    public function create() {
        $obj = D('Edu');
        if ($this->isPost()) {
            $data = $this->createCheck();
            $cate_id = $this->_param('cate_id',false);
            $age_id = $this->_param('age_id',false);
			
			//$edu_id = $obj->add($data);
			//p($obj->getLastSql());die;
			
            if($edu_id = $obj->add($data)){
                $this->tuSuccess('操作成功', U('edu/index'));
            }
            $this->tuError('操作失败');
        }else{
            $this->display();
        }
       
    }
    
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), array('shop_id','edu_name','intro','tel','photo','addr','cate_id','city_id','area_id','business_id','lat','lng','rate','details','audit'));
		$data['shop_id'] = (int)$data['shop_id'];
        if(empty($data['shop_id'])){
            $this->tuError('商家不能为空');
        }
		if(!$shop = D('Shop')->find($data['shop_id'])){
            $this->tuError('商家不存在');
        }
		if($Edu = D('Edu')->where(array('shop_id'=>$data['shop_id']))->find()){
            $this->tuError('您选择的商家【'.$shop['shop_name'].'】已经开通交教育，请不要重复添加');
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
        $data['audit'] = 1;
        return $data;
    }
    
    
    public function edit($edu_id = 0) {
        if ($edu_id = (int) $edu_id) {
            $obj = D('Edu');
            if (!$detail = $obj->where(array('edu_id'=>$edu_id))->find()) {
                $this->tuError('请选择要编辑的教育');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['edu_id'] = $edu_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('edu/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('shop',D('Shop')->find($detail['shop_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的教育');
        }
    }
    
    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), array('shop_id', 'edu_name','intro', 'tel', 'photo', 'addr','cate_id', 'city_id', 'area_id', 'business_id','lat', 'lng', 'rate', 'details'));
       $data['shop_id'] = (int)$data['shop_id'];
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
        return $data;
    }
    
    //教育商家删除
    public function delete($edu_id = 0){
        $obj = D('Edu');
        if(is_numeric($edu_id) && ($edu_id = (int) $edu_id)) {
			if($obj->where(array('edu_id' => $edu_id))->save(array('closed' => 1))){
				$this->tuSuccess('删除成功', U('edu/index'));
			}else{
				$this->tuError('删除失败');
			}
        }else{
            $edu_id = $this->_post('edu_id', false);
            if(is_array($edu_id)){
                foreach($edu_id as $id){
                    $obj->save(array('edu_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('删除成功', U('edu/index'));
            }
            $this->tuError('请选择要删除的教育');
        }
    }
 //教育商家审核
    public function audit($edu_id = 0) {
		$edu_id = (int) $edu_id;
        if (false !== D('Edu')->where(array('edu_id'=>$edu_id))->save(array('audit'=>1))){
          $this->tuSuccess('审核成功', U('edu/index'));
        }
		$this->tuError('审核失败');
    }


  //课程列表
    public function course($edu_id = 0){ 
		$edu_id = (int) $edu_id;
		$Edu = D('Edu');
        if (!$detail = $Edu->where(array('edu_id'=>$edu_id))->find()) {
          $this->tuError('请选择要编辑的课程');
        }
        $Educourse = D('Educourse');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0,'edu_id'=>$edu_id);
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
    public function course_create($edu_id = 0){ 
		$edu_id = (int) $edu_id;
		$Edu = D('Edu');
        if (!$detail = $Edu->where(array('edu_id'=>$edu_id))->find()) {
          $this->tuError('请选择要编辑的课程');
        }
        if ($this->isPost()) {
			$data = $this->course_createCheck();
			$data['edu_id'] = $edu_id;
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
		$data['type'] = (int) $data['type'];
		
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
		$data['test_price'] = (int) ($data['test_price'] * 100);//试课价格
		if($data['type'] ==1){
			if (empty($data['test_price'])) {
				$this->tuError('试课价不能为空');
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
		$data['views'] = (int) $data['views'];
		$data['sale'] = (int) $data['sale'];
		$data['orderby'] = (int) $data['orderby'];
		$data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->tuError('详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->tuError('详情含有敏感词：' . $words);
        }
		$data['audit'] = 1;
		$data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    
     
   //编辑课程 
    public function course_edit($course_id = 0,$edu_id = 0){
		$edu_id = (int) $edu_id;
		$Edu = D('Edu');
        if (!$detail_edit = $Edu->where(array('edu_id'=>$edu_id))->find()) {
          $this->tuError('请选择要编辑的课程');
        }
        if ($course_id = (int) $course_id) {
            $obj = D('Educourse');
            if (!$detail = $obj->find($course_id)) {
                $this->tuError('请选择要编辑的课程');
            }
            if ($this->isPost()) {
                $data = $this->course_editCheck();
				$data['edu_id'] = $edu_id;
                $data['course_id'] = $course_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('保存成功', U('edu/course',array('edu_id'=>$edu_id)));
                }
                $this->tuError('操作失败');
            } else {
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
		$data['type'] = (int) $data['type'];
		
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
		$data['test_price'] = (int) ($data['test_price'] * 100);//试课价格
		if($data['type'] ==1){
			if (empty($data['test_price'])) {
				$this->tuError('试课价不能为空');
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
				$this->tuError('类型阶段不能为空');
		}
		$data['class_time'] = htmlspecialchars($data['class_time']);
		$data['course_time'] = htmlspecialchars($data['course_time']);
		$data['apply_time'] = htmlspecialchars($data['apply_time']);
		$data['is_refund'] = (int) $data['is_refund'];
		$data['views'] = (int) $data['views'];
		$data['sale'] = (int) $data['sale'];
		$data['orderby'] = (int) $data['orderby'];
		$data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->tuError('详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->tuError('详情含有敏感词：' . $words);
        }
        return $data;
    }
	//删除课程
    public function course_delete($course_id = 0,$edu_id = 0){
		$edu_id = (int) $edu_id;
		$obj = D('Edu');
        if (!$Edu = $obj->where(array('edu_id'=>$edu_id))->find()) {
          $this->tuError('请选择要编辑的课程');
        }
        if ($course_id = (int) $course_id) {
            $obj = D('Educourse');
            if (!$detail = $obj->find($course_id)) {
                $this->tuError('请选择要删除的课程');
            }
            if (false !== $obj->save(array('course_id' => $course_id, 'closed' => 1))) {
                $this->tuSuccess('删除成功', U('edu/course',array('edu_id'=>$edu_id)));
            }else {
                $this->tuError('删除失败');
            }
        } else {
            $this->tuError('请选择要删除的课程');
        }
    }
	//审核课程
    public function course_audit($course_id = 0,$edu_id = 0){
		$edu_id = (int) $edu_id;
		$obj = D('Edu');
        if (!$Edu = $obj->where(array('edu_id'=>$edu_id))->find()) {
          $this->tuError('课程商家不存在');
        }
        if ($course_id = (int) $course_id) {
            $obj = D('Educourse');
            if (!$detail = $obj->find($course_id)) {
                $this->tuError('请选择要审核的课程');
            }
            if (false !== $obj->save(array('course_id' => $course_id, 'audit' => 1))) {
                $this->tuSuccess('审核成功', U('edu/course',array('edu_id'=>$edu_id)));
            }else {
                $this->tuError('审核失败');
            }
        } else {
            $this->tuError('请选择要审核的课程');
        }
    }        
    
	//教育分类列表
    public function cate(){
        $Educate = D('Educate');
        $list = $Educate->fetchAll();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	//教育分类添加
    public function cate_create($parent_id = 0){
        if ($this->isPost()) {
            $data = $this->cate_createCheck();
            $obj = D('Educate');
            $data['parent_id'] = $parent_id;
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuSuccess('添加成功', U('edu/cate'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('parent_id', $parent_id);
            $this->display();
        }
    }
	//教育分类添加验证
    private function cate_createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->cate_create_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->tuError('分类不能为空');
        }
		$data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->tuError('分类图标不正确');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
	//教育分类编辑
    public function cate_edit($cate_id = 0){
        if ($cate_id = (int) $cate_id) {
            $obj = D('Educate');
            if (!($detail = $obj->find($cate_id))) {
                $this->tuError('请选择要编辑的家政分类');
            }
            if ($this->isPost()) {
                $data = $this->cate_editCheck();
                $data['cate_id'] = $cate_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('edu/cate'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的家政分类');
        }
    }
	//教育分类编辑验证
    private function cate_editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->cate_edit_fields);
		
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->tuError('分类不能为空');
        }
		$data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->tuError('分类图标不正确');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
	//教育分类删除
    public function cate_delete($cate_id = 0){
            $cate_id = (int) $cate_id;
            $obj = D('Educate');
			if(false == D('Educate')->check_parent_id($cate_id)){
				$this->tuError('当前分类下面还有二级分类');
			}
			if(false == D('Educate')->check_cate_id_edu($cate_id)){
				$this->tuError('当前分类下面还有教育服务删除');
			}
			
            $obj->delete($cate_id);
            $obj->cleanCache();
            $this->tuSuccess('删除成功', U('edu/cate'));
    }
	//教育分类更新
    public function cate_update(){
        $orderby = $this->_post('orderby', false);
        $obj = D('Educate');
        foreach ($orderby as $key => $val) {
            $data = array('cate_id' => (int) $key, 'orderby' => (int) $val);
            $obj->save($data);
        }
        $obj->cleanCache();
        $this->tuSuccess('更新成功', U('edu/cate'));
    }
	
	//教育订单
	public function order(){
        $EduOrder = M('EduOrder'); 
        import('ORG.Util.Page');
		$map = array();
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
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
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
	//取消教育订单
	 public function cancel($order_id){
        if($order_id = (int) $order_id){
            if(!$order = D('EduOrder')->find($order_id)){
                $this->tuError('订单不存在');
            }elseif($order['order_status'] == -1){
                $this->tuError('该订单已取消');
            }else{
                if(false !== D('EduOrder')->cancel($order_id)){
                    $this->tuSuccess('订单取消成功',U('edu/order'));
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
            if(!$order = D('EduOrder')->find($order_id)){
                $this->tuError('订单不存在');
            }elseif($order['order_status'] != 1){
                $this->tuError('该订单无法完成');
            }else{
                if(false !== D('EduOrder')->complete($order_id)){
                    $this->tuSuccess('订单操作成功',U('edu/order'));
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
            if(!$order = D('EduOrder')->find($order_id)){
                $this->tuError('订单不存在');
            }elseif($order['order_status'] != -1){
                $this->tuError('订单状态不正确');
            }else{
                if(false !== D('EduOrder')->save(array('order_id'=>$order_id,'closed'=>1))){
                    $this->tuSuccess('订单删除成功',U('edu/order'));
                }else{
                    $this->tuError('订单删除失败');
                }
            }
        }else{
            $this->tuError('请选择要删除的订单');
        }
    }

	//教育点评
    public function comment() {
        $EduComment = D('EduComment');
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
        $count = $EduComment->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $EduComment->where($map)->order(array('comment_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
	//添加教育点评
    public function comment_create() {
        if ($this->isPost()) {
            $data = $this->comment_createCheck();
            $obj = D('EduComment');
            if ($comment_id = $obj->add($data)) {
                $photos = $this->_post('photos', false);
                $local = array();
                foreach ($photos as $val) {
                    if (isImage($val))
                        $local[] = $val;
                }
                if (!empty($local))
                    D('EduCommentPics')->upload($comment_id, $local);
                $this->tuSuccess('添加成功', U('edu/comment'));
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
        //教育订单ID找到对应信息开始
        $data['order_id'] = (int) $data['order_id'];
        if (empty($data['order_id'])) {
            $this->tuError('订单ID不能为空');
        }
        if (!$EduOrder = D('EduOrder')->find($data['order_id'])) {
            $this->tuError('订单ID不存在');
        }
		$Edu = D('Edu')->find($EduOrder['edu_id']);
        $data['shop_id'] = (int) $Edu['shop_id'];
        $data['edu_id'] = (int) $EduOrder['edu_id'];
		//教育订单ID找到对应信息结
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
	//教育点评编辑
   public function comment_edit($comment_id = 0) {
        if ($comment_id = (int) $comment_id) {
            $obj = D('EduComment');
            if (!$detail = $obj->find($comment_id)) {
                $this->tuError('请选择要编辑的教育点评');
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
                        D('EduCommentPics')->upload($comment_id, $local);
						D('Users')->prestige($data['user_id'], 'dianping');
                        D('Users')->updateCount($data['user_id'], 'ping_num');
                    	$this->tuSuccess('操作成功', U('edu/comment'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->assign('photos', D('EduCommentPics')->get_pic($comment_id));
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的教育点评');
            
        }
    }

    private function comment_editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->comment_edit_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->tuError('用户不能为空');
        }
		//教育订单ID找到对应信息开始
        $data['order_id'] = (int) $data['order_id'];
        if (empty($data['order_id'])) {
            $this->tuError('订单ID不能为空');
        }
        if (!$EduOrder = D('EduOrder')->find($data['order_id'])) {
            $this->tuError('订单ID不存在');
        }
		$Edu = D('Edu')->find($EduOrder['edu_id']);
        $data['shop_id'] = (int) $Edu['shop_id'];
        $data['edu_id'] = (int) $EduOrder['edu_id'];
		//教育订单ID找到对应信息结束
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
	//教育点评删除
	 public function comment_delete($comment_id = 0) {
        if (is_numeric($comment_id) && ($comment_id = (int) $comment_id)) {
            $obj = D('EduComment');
            $obj->delete($comment_id);
            $this->tuSuccess('删除成功', U('edu/comment'));
        } else {
            $comment_id = $this->_post('comment_id', false);
            if (is_array($comment_id)) {
                $obj = D('EduComment');
                foreach ($comment_id as $id) {
                     $obj->delete($id);
                }
                $this->tuSuccess('删除成功', U('edu/comment'));
            }
            $this->tuError('请选择要删除的教育点评');
        }
    }

    
}
