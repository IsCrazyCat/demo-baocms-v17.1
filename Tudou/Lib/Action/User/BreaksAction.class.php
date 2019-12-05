<?php

class BreaksAction extends CommonAction {

	public function index() {
		$aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->assign('nextpage', LinkTo('breaks/loaddata',array('aready' => $aready,'t' => NOW_TIME, 'p' => '0000')));
		$this->display(); 
	}
  
    public function loaddata(){
		$breaks = D('Breaksorder');
		import('ORG.Util.Page');
		$map = array('user_id' => $this->uid);
        
		if(isset($_GET['aready'])|| isset($_POST['aready'])){
            $aready = (int) $this->_param('aready');
            if($aready != 0){
                $map['status'] = $aready;
            }
            $this->assign('aready', $aready);
        }else{
			$map['status'] = 0;
            $this->assign('aready', 0);
        }
		$count = $breaks->where($map)->count();
		$Page = new Page($count, 20);
		$show = $Page->show();
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if ($Page->totalPages < $p) {
			die('0');
		}
		$list = $breaks->where($map)->order(array('order_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$shop_ids = array();
		foreach($list as $k => $val){
            $list[$k]['yh'] = $val['amount'] - $val['need_pay'];
			$shop_ids[$val['shop_id']] = $val['shop_id'];
		}
		$shops = D('Shop')->itemsByIds($shop_ids);
		$this->assign('shops', $shops);
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	
	
	//买单详情
	public function detail($order_id){
        if(!$order_id = (int)$order_id){
            $this->error('该订单不存在');
        }
		if(!$detail = D('Breaksorder')->find($order_id)){
            $this->error('该订单不存在');
        }
		if($detail['user_id'] != $this->uid){
            $this->error('非法的订单操作');
        }
       
		$envelope = M('Envelope')->where(array('bg_date' => array('ELT', TODAY),'closed'=>'0','type'=>'2','shop_id'=>$detail['shop_id']))->find();
   		if(!$envelope){
			$envelope = M('Envelope')->where(array('bg_date' => array('ELT', TODAY),'closed'=>'0','type'=>'1'))->find();
		}

		$date = date("Y-m-d",$detail['pay_time']);
		$this->assign('show',$show = ($date >= $envelope['bg_date']) ? '1' : '0');
		 
		$this->assign('envelope',$envelope);   
        $this->assign('detail',$detail);
        $this->display();
    }
	
	
	//领取红包
    public function envelope($envelope_id = 0,$order_id = 0,$orderType = 0){
        $oenvelope_id = I('envelope_id', '', 'intval,trim');
		$order_id = I('order_id', '', 'intval,trim');
		$orderType = I('orderType', '', 'intval,trim');
		
		if(!$envelope_id){
			$this->ajaxReturn(array('code'=>'0','msg'=>'红包ID不存在'));
		}
		if(!$Envelope = M('Envelope')->find($envelope_id)){
			$this->ajaxReturn(array('code'=>'0','msg'=>'红包详情'));
		}
		if($EnvelopeLogs = M('EnvelopeLogs')->where(array('user_id'=>$this->uid,'order_id'=>$order_id,'orderType'=>$orderType))->find()){
			$this->ajaxReturn(array('code'=>'0','msg'=>'您已经领取过了'));
		}
		if(!$order_id){
			$this->ajaxReturn(array('code'=>'0','msg'=>'订单ID不存在'));
		}
		if(!$Breaksorder = D('Breaksorder')->find($order_id)){
			$this->ajaxReturn(array('code'=>'0','msg'=>'订单详情'));
		}
		if(!$orderType){
			$this->ajaxReturn(array('code'=>'0','msg'=>'订单类型不能为空'));
		}
		if($Breaksorder['need_pay']*100 <= 0){
			$this->ajaxReturn(array('code'=>'0','msg'=>'订单金额有错误'));
		}
		
		
		$ratio = $Envelope['ratio']/100;
		$money = (int)(($Breaksorder['need_pay']*$ratio)*100);
	
		if($Envelope['prestore'] < $money){
			M('Envelope')->where(array('envelope_id'=>$envelope_id))->save(array('closed'=>1)); //关闭返还
			if($Envelope['type'] < 2){
				$shop = M('Shop')->find($Envelope['shop_id']);
				D('Users')->addMoney($shop['user_id'],$Envelope['prestore'],'用户兑换的金额【'.round($money/100,2).'】大于红包剩余余额，自动关闭该红包');
			}
			$this->ajaxReturn(array('code'=>'0','msg'=>'当前红包库金额不足无法领取'));
		}
		
		
		$rand = rand(10,80);
		if($money > 0){
			$intro = '买单订单ID【'.$order_id.'】分享后领取红包【'.round($money/100,2).'】元';
		}else{
			$money = $rand;
			$intro = '买单订单ID【'.$order_id.'】分享后领取随机红包【'.round($money/100,2).'】元';
		}
		
		if($money > 0){
			$arr['type'] = $Envelope['type'];
			$arr['orderType'] = $orderType;
			$arr['envelope_id'] = $envelope_id;
			$arr['shop_id'] = $Envelope['shop_id'];
			$arr['user_id'] = $this->uid;
			$arr['order_id'] = $order_id;
			$arr['money'] = $money;
			$arr['surplus_prestore'] = $Envelope['prestore'] - $money;
			$arr['intro'] = $intro;
			$arr['create_time'] = time();
			$arr['create_ip'] = get_client_ip();
			if(M('EnvelopeLogs')->add($arr)){
				D('Users')->addMoney($this->uid,$money,$intro);
				D('Envelope')->where(array('envelope_id'=>$envelope_id))->setDec('prestore',$money); 
				$this->ajaxReturn(array('code'=>'1','msg'=>$intro,'url'=>U('logs/moneylogs')));        
			}else{
				$this->ajaxReturn(array('code'=>'0','msg'=>'领取失败'));
			}
		}else{
			$this->ajaxReturn(array('code'=>'0','msg'=>'商家红包配置有误'));
		}
		
    }

    
}