<?php
class EduAction extends CommonAction {
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
    //订单列表
    public function index() {
        $EduOrder = D('EduOrder'); 
        $order_id = I('order_id',0,'trim,intval');
        $map = array();
        $map['user_id'] = $this->uid;
        if($order_id){
            $map['order_id'] = $order_id;
        }
        import('ORG.Util.Page');
        $count = $EduOrder->where($map)->count();
        $Page = new Page($count,25); 
        $show = $Page->show();
        $list = $EduOrder->where($map)->order('order_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as $k => $v){
            $course = D('Educourse')->where(array('course_id'=>$v['course_id']))->find();
            $list[$k]['course'] = $course;
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display(); 
    }
  
    
	//课程取消订单
    public function cancel($order_id){
       if(!$order_id = (int)$order_id){
           $this->tuError('订单不存在');
       }elseif(!$detail = D('EduOrder')->find($order_id)){
           $this->tuError('订单不存在');
       }elseif($detail['user_id'] != $this->uid){
           $this->tuError('非法操作订单');
       }else{
           if(false !== D('EduOrder')->cancel($order_id)){
               $this->tuSuccess('订单取消成功',U('edu/index'));
           }else{
               $this->tuError('订单取消失败');
           }
       }
    }

}
