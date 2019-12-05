<?php



class AddrAction extends CommonAction {

	//获取用户地址
	public function get_addr(){
		$rd_session = $this->_param('rd_session');
		$user = $this->checkLogin($rd_session);
        $this->uid = $user['uid'];
		
        $addr = D('UserAddr') -> where(array('user_id'=>$this->uid,'closed'=>0)) -> select();

        exit(json_encode(array('status'=>1,'msg'=>'地址获取成功','data'=>$addr)));   		
	}
	
	//获取某条地址
	public function get_addr_by_addr_id(){
		$rd_session = $this->_param('rd_session');
		$addr_id = $this->_param('addr_id');
		$user = $this->checkLogin($rd_session);
        $this->uid = $user['uid'];
        $addr = D('UserAddr') -> where(array('user_id'=>$this->uid,'closed'=>0,'addr_id'=>$addr_id)) -> find();
		exit(json_encode(array('status'=>1,'msg'=>'地址获取成功','data'=>$addr)));   
	}
	
	
	//添加地址
    public function add_addr(){
        $rd_session = $this->_post('rd_session');
        $user = $this->checkLogin($rd_session);
        $this->uid = $user['uid'];

        $name = I('name', '', 'trim,htmlspecialchars');
        $mobile = I('mobile', '', 'trim');
        $addr = I('addr', '', 'trim,htmlspecialchars');
 
        if (!isMobile($mobile)) {
             exit(json_encode(array('status'=>-1,'msg'=>'请填写正确的手机号码','data'=>'')));
        }

        $data = array();
        $data['name'] = $name;
        $data['mobile'] = $mobile;
        $data['addr'] = $addr;
        $data['user_id'] = $this->uid;
        $data['is_default'] = 0;
        $data['closed'] = 0;
        $ud = D('UserAddr');
        $add = $ud->add($data);
        if ($add) {
           exit(json_encode(array('status'=>1,'msg'=>'添加成功','data'=>'')));
        } else {
           exit(json_encode(array('status'=>-1,'msg'=>'添加失败','data'=>'')));
        }
    }

}
