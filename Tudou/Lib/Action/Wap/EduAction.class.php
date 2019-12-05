<?php
class EduAction extends CommonAction {
	protected $Activitycates = array();
    public function _initialize() {
        parent::_initialize();
		if(empty($this->_CONFIG['operation']['edu'])){
			$this->error('教育功能已关闭');die;
		}
        $this->age = D('Edu')->getEduage();
        $this->assign('age', $this->age);
        $this->get_time = D('Edu')->getEduTime();
        $this->assign('get_time', $this->get_time);
		$this->get_edu_class = D('Edu')->getEduClass();
        $this->assign('class', $this->get_edu_class);
		$this->assign('cates', D('Educate')->fetchAll());
		$this->assign('types', D('EduOrder')->getType());
		$this->educates = D('Educate')->fetchAll();//分类表
    }
	//教育首页
	public function index() {
		$map = array('audit' => 1, 'closed' => 0);
        $cate_id = (int) $this->_param('cate_id');
        if ($cat) {
            $catids = D('Educate')->getChildren($cate_id);
            if (!empty($catids)) {
                $map['cate_id'] = array('IN', $catids);
				
            } else {
                $map['cate_id'] = $cate_id;
            }
        }
		$cate_id = (int) $this->_param('cate_id');
        $list = D('Educourse')->where($map)->order('views desc')->limit(6)->select();
		
		foreach ($list as $k => $val ){
			$edu = D('Edu')->find($val['edu_id']);
			if($edu['closed']  == 1 ||  $edu['audit']  == 0){
				unset($list[$k]);
			}
        }
        $this->assign('list',$list);
        $this->display(); 
    }
	//手机版首页
	public function course() {
        $linkArr = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $linkArr['keyword'] = $keyword;
		
		
		$cat = (int) $this->_param('cat');
        $this->assign('cat', $cat);
        $linkArr['cat'] = $cat;
		
		
        $cate_id = (int) $this->_param('cate_id');
        $this->assign('cate_id', $cate_id);
        $linkArr['cate_id'] = $cate_id;
		
        $age_id = (int) $this->_param('age_id');
        $this->assign('age_id', $age_id);
        $linkArr['age_id'] = $age_id;
        
        $time_id = (int) $this->_param('time_id');
        $this->assign('time_id', $time_id);
        $linkArr['time_id'] = $time_id;
		
		$class_id = (int) $this->_param('class_id');
        $this->assign('class_id', $class_id);
        $linkArr['class_id'] = $class_id;
		
        $order = $this->_param('order', 'htmlspecialchars');
        $this->assign('order', $order);
        $linkArr['order'] = $order;
		
        $this->assign('nextpage', LinkTo('edu/loaddata',$linkArr,array('t' => NOW_TIME,'p' => '0000')));
        $this->assign('linkArr',$linkArr);
        $this->display(); // 输出模板
    }

	public function loaddata() {
        $Educourse = D('Educourse');
        import('ORG.Util.Page'); 
        $map = array('audit' => 1, 'closed' => 0, 'city_id' => $this->city_id);
        $linkArr = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['hotel_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
            $linkArr['keywrod'] = $keyword;
        }
        
		$cate_id = (int) $this->_param('cate_id');
		$cat = (int) $this->_param('cat');
		
        if ($cat) {
            $catids = D('Educate')->getChildren($cat);
            if (!empty($catids)) {
                $map['cate_id'] = array('IN', $catids);
            } else {
                $map['cate_id'] = $cate_id;
				$linkArr['cate_id'] = $cate_id;
            }
        }else{
			if($cate_id){
				$map['cate_id'] = $cate_id;
				$linkArr['cate_id'] = $cate_id;
			}
			
		}
		
		$this->assign('cat', $cat);
		$this->assign('cate_id', $cate_id);
		
        $age_id = (int) $this->_param('age_id');
        if ($age_id) {
            $map['age_id'] = $age_id;
            $linkArr['age_id'] = $age_id;
        }
        $this->assign('age_id', $age_id);
		
        $time_id = (int) $this->_param('time_id');
        if ($time_id) {
            $map['time_id'] = $time_id;
            $linkArr['time_id'] = $time_id;
        }
        $this->assign('time_id', $time_id);
		
		$class_id = (int) $this->_param('class_id');
        if ($class_id) {
            $map['class_id'] = $class_id;
            $linkArr['class_id'] = $class_id;
        }
        $this->assign('class_id', $class_id);
		
        $order = $this->_param('order', 'htmlspecialchars');
        $orderby = '';
        switch ($order) {
            case 'p':
                $orderby = array('course_price' => 'asc');
                break;
            case 's':
                $orderby = array('sale' => 'desc');
                break;
            default:
                $orderby = array('sale' => 'desc', 'course_id' => 'desc');
                break;
        }
        $this->assign('order', $order);
        $count = $Educourse->where($map)->count();
        $Page = new Page($count, 20); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Educourse->where($map)->order($orderby)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

    public function course_detail($course_id) {
        $course_id = (int) $course_id;
		$Educourse = D('Educourse');
		if(!$detail = $Educourse->find($course_id)){
            $this->error('该课程项目不存在');
            die;
        }
		if($detail['closed'] == 1){
            $this->error('课程已删除');
            die;
        }
		$Educourse->updateCount($course_id, 'views');//更新浏览量
		$this->assign('shops', D('Shop')->find($detail['shop_id']));
		//用户评价列表
        $EduComment = D('EduComment'); 
        import('ORG.Util.Page');
        $count = $EduComment->where('course_id = '.$course_id)->count();
        $Page  = new Page($count,10);
        $show  = $Page->show();
        $list = $EduComment->where('course_id = '.$course_id)->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as $k => $v){
           if($pics = D('EduCommentPics') -> where('comment_id ='.$v['comment_id']) -> select()){
              $list[$k]['pics'] = $pics;
           }
        }
		
		//点评结束
		$this->assign('list',$list);
		$this->assign('edu',D('Edu')->where(array('edu_id'=>$detail['edu_id']))->find());
        $this->assign('page', $show);
		$this->assign('detail', $detail);
        $this->display();

    }
	
	//课程点评
    public function comment(){
        $course_id = (int) $this->_get('course_id');
        if (!($detail = D('Educourse')->find($course_id))) {
            $this->error('没有该课程');
            die;
        }
        if ($detail['closed']) {
            $this->error('该课程已经被删除');
            die;
        }
        $this->assign('next', LinkTo('mall/comment_load', $linkArr, array('course_id' => $course_id, 't' => NOW_TIME, 'p' => '0000')));
        $this->assign('detail', $detail);
        $this->display();
    }
	//课程点评内容加载
    public function comment_load(){
        $course_id = (int) $this->_get('course_id');
        if (!($detail = D('Educourse')->find($course_id))) {
            die('0');
        }
        if ($detail['closed']) {
            die('0');
        }
        $EduComment = D('EduComment');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'course_id' => $course_id, 'show_date' => array('ELT', TODAY));
        $count = $EduComment->where($map)->count();
        $Page = new Page($count, 5);
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $EduComment->where($map)->order(array('comment_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $comment_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $comment_ids[$val['comment_id']] = $val['comment_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($comment_ids)) {
            $this->assign('pics', D('EduCommentPics')->where(array('comment_id' => array('IN', $comment_ids)))->select());
        }
        $this->assign('count', $count);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('detail', $detail);
        $this->display();
    }
	
	//课程点评图片详情
    public function comment_detail(){
        $comment_id = (int) $this->_get('comment_id');
        if (!($detail = D('EduComment')->find($comment_id))) {
            $this->error('没有该点评');
            die;
        }
        if ($detail['closed']) {
            $this->error('该点评已经被删除');
            die;
        }
        $list =  D('EduCommentPics')->where(array('comment_id' => $comment_id))->select();
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->display();
    }
	

    //课程购买直接支付页面
	 public function buy(){
        if (empty($this->uid)) {
            $this->tuMsg("请登录后购买" , U('passport/login'));
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
            $this->tuMsg('该课程不存在');
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
			$data['create_time'] =	time();
			$data['code'] = $code;
			$data['create_ip'] = get_client_ip();
			if($order_id = $obj->add($data)){
				$this->tuMsg('恭喜您下单成功，正在为您跳转付款页面',U('edu/pay',array('order_id'=>$order_id)));
			}else {
				 $this->tuMsg('创建订单失败，请稍后再试试');
			}
		}else{
			$this->tuMsg('获取价格失败，请稍后再试试');
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
		$this->assign('course',D('Educourse')->where(array('course_id'=>$order['course_id']))->find());
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
            $this->tuMsg('该订单不存在');
            die;
        }
        if (!($code = $this->_post('code'))) {
            $this->tuMsg('请选择支付方式');
        }
        if ($code == 'wait') {
             $this->tuMsg('暂不支持货到付款，请重新选择支付方式');
        } else {
            $payment = D('Payment')->checkPayment($code);
            if (empty($payment)) {
                $this->tuMsg('该支付方式不存在，请稍后再试试');
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
			$this->tuMsg('下单成功',U('payment/payment', array('log_id' => $logs['log_id'])));
        }
    }
}

