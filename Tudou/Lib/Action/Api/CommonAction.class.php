<?php 
class CommonAction extends Action{

	protected function _initialize(){
        define('__HOST__', 'https://' . $_SERVER['HTTP_HOST']);
		$this->_CONFIG = D('Setting')->fetchAll();
    }

    public function checkLogin($rd_session){
        if(!empty($rd_session)){
            $user = D('Connect')->where("rd_session='{$rd_session}'")->find();

            if(empty($user))
                exit(json_encode(array('status'=>-2,'msg'=>'token无效，请重新登录获取','data'=>'')));
        }else{
            exit(json_encode(array('status'=>-1,'msg'=>'token不能为空','data'=>'')));
        }
        return $user;
    }

	protected function checkFields($data = array(), $fields = array()){
        foreach($data as $k => $val){
            if(!in_array($k, $fields)){
                unset($data[$k]);
            }
        }
        return $data;
    }
	
    //获取open_id
    public function getOpenId($rd_session){
        $user = $this->checkLogin($rd_session);
        return $user['open_id'];
    }
}


