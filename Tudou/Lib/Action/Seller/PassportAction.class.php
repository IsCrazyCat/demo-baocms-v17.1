<?php
class PassportAction extends CommonAction{
    private $create_fields = array('account', 'password', 'nickname');
    public function bind(){
        $this->display();
    }
	
	 public function login(){
        if ($this->isPost()) {
            $account = $this->_post('account');
			$password = $this->_post('password');
			
            if (true == D('Passport')->login($account, $password)) {
                $backurl = U('index/index');
                $this->ajaxReturn(array('status' => 'success', 'message' => '登录成功!', 'backurl' => $backurl));
            }else{
				$this->ajaxReturn(array('status' => 'error', 'message' => '登录失败'));
			}
			
        } else {
			
			$this->assign('backurl', $backurl);
            $this->display();
				
				
            /*if(!empty($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) && !strstr($_SERVER['HTTP_REFERER'], 'passport')) {
                $backurl = $_SERVER['HTTP_REFERER'];
            }else{
                $backurl = U('index/index');
            }
            $backurl = U('index/index');
            $getuid = (int)getuid();
			
            if($getuid>0){
                redirect($backurl);
            }else{
                $this->assign('backurl', $backurl);
                $this->display();
            }*/
        }
    }
	

    public function logout(){
        D('Passport')->logout();
        $this->success('退出登录成功', U('passport/login'));
    }
}