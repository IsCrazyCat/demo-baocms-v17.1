<?php
class IntegralAction extends CommonAction{

     public function restore(){
		$this->closed = '';
		$this->obj = D('Userintegralrestore');
		$this->order = array('restore_id' => 'desc');
		$this->showdata();
        $this->display();
     }
	 public function logs(){
		$this->closed = '';
		$this->obj = D('Userintegrallogs');
		$this->order = array('create_time' => 'desc');
		$this->showdata();
        $this->display();
     }
	 public function cancel(){
		$this->closed = '';
		$this->obj = D('Userintegralcancel');
		$this->order = array('cancel_id' => 'desc');
		$this->showdata();
        $this->display();
     }
	 public function library(){
		$this->closed = 0;
		$this->obj = D('Userintegrallibrary');
		$this->order = array('library_id' => 'desc');
		$this->showdata();
        $this->display();
     }
	 public function showdata(){
        $obj = $this->obj;
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid,'closed' => $this->closed);
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order($this->order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
    }
	//积分核销二维码
	public function qrcode(){
        $user_id = $this->uid;
        if (!($detail = D('Users')->find($user_id))) {
            $this->error('你没登录或者会员不存在');
        }
        $url = U('user/integral/url', array('user_id' => $user_id, 't' => NOW_TIME, 'sign' => md5($user_id . C('AUTH_KEY') . NOW_TIME)));
        $token = 'integral_cancel_code_' . $user_id;
        $file = tuQrCode($token, $url);
        $this->assign('file', $file);
        $this->assign('detail', $detail);
        $this->display();
     }
	//中间件
	public function url (){
		$user_id = (int) $this->_param('user_id');
        if (!($detail = D('Users')->find($this->uid))) {
            $this->error('你没登录或者会员不存在');
        }
		$detail = D('Shopworker')->find(array("where" => array('user_id' => $this->uid,'status'=>1)));
		
		
		if($detail){
			header('Location:' . U('worker/integral/used',array('user_id'=>$user_id,'type'=>2)));
            die;
		}else{
			header('Location:' . U('seller/integral/used',array('user_id'=>$user_id,'type'=>1)));
            die;
		}
		
	}
	
   
}