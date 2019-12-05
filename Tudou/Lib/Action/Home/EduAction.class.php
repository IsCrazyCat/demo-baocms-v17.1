<?php
class EduAction extends CommonAction {
	protected $Activitycates = array();
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
		$this->educates = D('Educate')->fetchAll();//分类表
		$this->assign('host',__HOST__);
    }
	//课程列表
    public function course() {
        $Educourse = D('Educourse');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0,'audit' => 1);
		$linkArr = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        //搜索二开结束
		$cates = D('Educate')->fetchAll();
        $cat = (int) $this->_param('cat');
        $cate_id = (int) $this->_param('cate_id');
        if ($cat) {
            if (!empty($cate_id)) {
                $map['cate_id'] = $cate_id;
                $this->seodatas['cate_name'] = $cates[$cate_id]['cate_name'];
                $linkArr['cat'] = $cat;
                $linkArr['cate_id'] = $cate_id;
            } else {
                $catids = D('Appointcate')->getChildren($cat);
                if (!empty($catids)) {
                    $map['cate_id'] = array('IN', $catids);
                }
                $this->seodatas['cate_name'] = $cates[$cat]['cate_name'];
                $linkArr['cat'] = $cat;
            }
        }
        $this->assign('cat', $cat);
        $this->assign('cate_id', $cate_id);
        $area = (int) $this->_param('area');
        if ($area) {
            $map['area_id'] = $area;
            $this->seodatas['area_name'] = $this->areas[$area]['area_name'];
            $linkArr['area'] = $area;
        }
        $this->assign('area_id', $area);
        $business = (int) $this->_param('business');
        if ($business) {
            $map['business_id'] = $business;
            $this->seodatas['business_name'] = $this->bizs[$business]['business_name'];
            $linkArr['business'] = $business;
        }
        $this->assign('business_id', $business);
		
		//增加年龄
		$age_id = (int) $this->_param('age_id');
        if ($age_id) {
			$map['age_id'] = $age_id;
            $this->seodatas['age_id'] = $this->age[$age_id];
            $linkArr['age_id'] = $age_id;
        }
        $this->assign('age_id', $age_id);
		
		//增加时间
		$time_id = (int) $this->_param('time_id');
        if ($time_id) {
			$map['time_id'] = $time_id;
            $this->seodatas['time_id'] = $this->get_time[$time_id];
            $linkArr['time_id'] = $time_id;
        }
        $this->assign('time_id', $time_id);
		
		//增加类型
		$class_id = (int) $this->_param('class_id');
        if ($class_id) {
			$map['class_id'] = $class_id;
            $this->seodatas['class_id'] = $this->get_edu_class[$class_id];
            $linkArr['class_id'] = $class_id;
        }
        $this->assign('class_id', $class_id);
		
		$order = $this->_param('order', 'htmlspecialchars');
        $orderby = '';
        switch ($order) {
            case 's':
                $orderby = array('sale' => 'asc');
                $linkArr['order'] = $order;
                break;
            case 'p':
                $orderby = array('price' => 'asc');
                $linkArr['order'] = $order;
                break;
            case 't':
                $orderby = array('create_time' => 'asc');
                $linkArr['order'] = $order;
                break;
            case 'v':
                $orderby = array('views' => 'asc');
                $linkArr['order'] = $order;
                break;
            default:
                $orderby = array('sale' => 'desc', 'course_id' => 'desc');
                break;
        }
		$this->assign('order', $order);
		//搜索二开结束
        $count = $Educourse->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $list = $Educourse->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $cate_ids = array();
        foreach ($list as $k => $val) {
				$cate_ids[$val['cate_id']] = $val['cate_id'];
				$edu_ids[$val['edu_id']] = $val['edu_id'];
        }
		if ($cate_ids) {
            $this->assign('edu_cates', D('Educate')->itemsByIds($cate_ids));
        }
		if ($edu_ids) {
            $this->assign('edus', D('Edu')->itemsByIds($edu_ids));
        }
		$selArr = $linkArr;
        foreach ($selArr as $k => $val) {
            if ($k == 'order' || $k == 'new' || $k == 'freebook' || $k == 'hot' || $k == 'tui' || $k == 'area'|| $k == 'age_id'|| $k == 'time_id'|| $k == 'class_id') {
                unset($selArr[$k]);
            }
        }		
        $this->assign('list', $list); 
		$this->assign('selArr', $selArr);
		$this->assign('cates', $cates);
        $this->assign('page', $show);
		$this->assign('linkArr', $linkArr);
        $this->display(); 
    }



    public function course_detail($course_id) {
        $course_id = (int) $course_id;
		$Educourse = D('Educourse');
		if (!$detail = $Educourse->find($course_id)) {
            $this->error('该课程项目不存在');
            die;
        }
		$this->seodatas['cate_name'] = $this->educates[$detail['cate_id']]['cate_name'];
        $this->seodatas['cate_area'] = $this->areas[$detail['area_id']]['area_name'];
        $this->seodatas['cate_business'] = $this->bizs[$detail['business_id']]['business_name'];
        $this->seodatas['title'] = $detail['title'];
		
        if ($this->educates[$detail['cate_id']]['parent_id'] == 0) {
            $this->assign('catstr', $this->educates[$detail['cate_id']]['cate_name']);
        } else {
            $this->assign('catstr', $this->educates[$this->Educates[$detail['cate_id']]['parent_id']]['cate_name']);
            $this->assign('cat', $this->educates[$detail['cate_id']]['parent_id']);
            $this->assign('catestr', $this->educates[$detail['cate_id']]['cate_name']);
        }
		$Educourse->updateCount($course_id, 'views');//更新浏览量
		$this->assign('shops', D('Shop')->find($detail['shop_id']));
        $this->assign('totalnum', $count);
        
		//用户评价列表
        $EduComment = D('EduComment'); 
        import('ORG.Util.Page');
        $count = $EduComment->where('farm_id = '.$farm_id)->count();
        $Page  = new Page($count,10);
        $show  = $Page->show();
        $list = $EduComment->where('farm_id = '.$farm_id)->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as $k => $v){
           if($pics = D('EduCommentPics') -> where('comment_id ='.$v['comment_id']) -> select()){
              $list[$k]['pics'] = $pics;
           }
        }
		$this->assign('list',$list);
		//点评结束
        $this->assign('page', $show);
		$this->assign('detail', $detail);
		$this->assign('height_num', 760);//下拉横条导航
        $this->display();

    }

    //课程购买直接支付页面
	 public function buy(){
        if (empty($this->uid)) {
            $this->tuSuccess("请登录后购买" , U('passport/login'));
            die;
        }
		$obj = D('EduOrder');
        $course_id = (int) $this->_get('course_id');
		$type = (int) $this->_get('type');
		if(empty($type)){
			$get_type = 0;
		}else{
			$get_type = 1;
		}
		if (!$detail = D('Educourse')->find($course_id)) {
            $this->tuError('该课程不存在');
            die;
        }
		$edu =  D('Edu')->where(array('edu_id'=>$detail['edu_id']))->find();
		$code = $obj->getCode();
		$need_pay = $obj->get_edu_need_pay($get_type,$course_id);
		if(false !== $need_pay){
			$data = array();
			$data['shop_id'] = $edu['shop_id'];
			$data['edu_id'] = $detail['edu_id'];
			$data['type'] = $type;
			$data['user_id'] = $this->uid;
			$data['course_id'] = $course_id;
			$data['price'] = $detail['price'];
			$data['need_pay'] = $need_pay;
			$data['status'] = 0;
			$data['code'] = $code;
			$data['create_time'] =	time();
			$data['create_ip'] = get_client_ip();
			if($order_id = $obj->add($data)){
				$this->tuMsg('恭喜您下单成功，正在为您跳转付款页面',U('edu/pay',array('order_id'=>$order_id)));
			}else {
				 $this->tuError('创建订单失败，请稍后再试试');
			}
		}else{
			$this->tuError('获取价格失败，请稍后再试试');
		};
        $this->display();
    }
	
	//课程购买直接支付页面
	 public function pay(){
        if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('EduOrder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
		$this->assign('order', $order);
		$this->assign('type', crowd);
        $this->assign('payment', D('Payment')->getPayments());
        $this->display();
    }
	//去付款
	 public function pay2(){
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('EduOrder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->tuError('该订单不存在');
            die;
        }
        if (!($code = $this->_post('code'))) {
            $this->tuError('请选择支付方式');
        }
        if ($code == 'wait') {
             $this->tuError('暂不支持货到付款，请重新选择支付方式');
        } else {
            $payment = D('Payment')->checkPayment($code);
            if (empty($payment)) {
                $this->tuError('该支付方式不存在，请稍后再试试');
            }
            $logs = D('Paymentlogs')->getLogsByOrderId('edu', $order_id);//查找日志
			$need_pay = $order['need_pay'];//再更新防止篡改支付日志
            if (empty($logs)) {//独家再更新
                $logs = array(
					'type' => 'edu', 
					'user_id' => $this->uid, 
					'order_id' => $order_id, 
					'code' => $code, 
					'need_pay' => $need_pay, 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'is_paid' => 0
				);
                $logs['log_id'] = D('Paymentlogs')->add($logs);
            } else {
                $logs['need_pay'] = $need_pay;
                $logs['code'] = $code;
                D('Paymentlogs')->save($logs);
            }
			$this->tuJump(U('payment/payment', array('log_id' => $logs['log_id'])));
        }
    }
}

