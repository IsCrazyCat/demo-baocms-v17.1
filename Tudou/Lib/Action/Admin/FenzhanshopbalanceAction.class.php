<?php


class FenzhanshopbalanceAction extends CommonAction { 
    private $edit_fields = array('shop_id', 'top_date', );
    public function _initialize() {
        parent::_initialize();
        $this->Tuancates = D('Tuancate')->fetchAll();
        $this->assign('cates', $this->Tuancates);
        $this->assign('ranks',D('Userrank')->fetchAll());
    }

    public function index() {
        $User = D('Users');
        import('ORG.Util.Page'); 
		$map_shop['user_id']  = array('neq','');
		$list_shop = D('Shop')->where($map_shop)->select();
		foreach ($list_shop as $val) {
			$user_ids[$val['user_id']] = $val['user_id'];
		}
        $map = array();
		$map['user_id']  = array('in',$user_ids);
		$map['closed']  = array('in','0,-1');
        if($account = $this->_param('account','htmlspecialchars')){
            $map['account'] = array('LIKE','%'.$account.'%');
            $this->assign('account',$account);
        }
        if($nickname = $this->_param('nickname','htmlspecialchars')){
            $map['nickname'] = array('LIKE','%'.$nickname.'%');
            $this->assign('nickname',$nickname);
        }
		if($mobile = $this->_param('mobile','htmlspecialchars')){
            $map['mobile'] = array('LIKE','%'.$mobile.'%');
            $this->assign('mobile',$mobile);
        }
        $count = $User->where($map)->count(); 
        $Page = new Page($count, 25);
        $show = $Page->show(); 
        $list = $User->where($map)->order(array('user_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
	
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display(); 
    }


public function frozen(){
       $user_id = (int)$this->_get('user_id'); 
       if(empty($user_id)) $this->baoError ('请选择用户');
       if(!$detail = D('Users')->find($user_id)){
           $this->baoError('没有该用户！');
       }
       if($this->isPost()){
		   $frozen = (int)  ($this->_post('frozen') * 100);
           if($frozen == 0){
               $this->tuError('请输入正确冻结金');
           }
           $intro =  $this->_post('intro', 'htmlspecialchars');
		   if(empty($intro)){
               $this->tuError('请输入理由');
           }
		   if($detail['gold'] < $frozen){
               $this->tuError('冻结金不得大于商户资金余额');
           }
		   if($frozen < $detail['frozen']){
               $this->tuError('恢复冻结金不得大于'.($detail['frozen']/100).'元');
           }
		   
		   $data = array();
		   $data['user_id'] = $user_id;
		   $data['gold'] = $detail['gold']-$frozen;
		   $data['frozen_gold'] = $detail['frozen'] + $frozen;
		   $data['frozen_time'] = NOW_TIME;
		   
		   $intro = '管理员设置商家冻结金'.$intro ;
		   D('Users')->save($data);
           D('Usergoldlogs')->add(array('user_id' => $user_id,'gold'=>$frozen,'intro' => $intro,'create_time' => NOW_TIME,'create_ip'  => get_client_ip()));
           $this->tuSuccess('操作成功',U('fenzhanshopbalance/index'));
       }else{
           $this->assign('user_id',$user_id);
           $this->display();
       }       
   }
   
   
}