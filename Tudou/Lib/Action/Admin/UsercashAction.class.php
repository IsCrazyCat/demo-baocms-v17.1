<?php
class UsercashAction extends CommonAction{
	
    public function index(){
        $Userscash = D('Userscash');
        import('ORG.Util.Page');
        $map = array('type' => user);
        if($account = $this->_param('account', 'htmlspecialchars')){
            $map['account'] = array('LIKE', '%' . $account . '%');
            $this->assign('account', $account);
        }
		if($cash_id = (int) $this->_param('cash_id')){
            $map['cash_id'] = $cash_id;
            $this->assign('cash_id', $cash_id);
        }
		if($user_id = (int) $this->_param('user_id')){
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		if(isset($_GET['st']) || isset($_POST['st'])){
            $st = (int) $this->_param('st');
            if($st != 999){
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        }else{
            $this->assign('st', 999);
        }
		if($code = $this->_param('code', 'htmlspecialchars')){
            if($code != 999){
                $map['code'] = $code;
            }
            $this->assign('code', $code);
        }else{
            $this->assign('code', 999);
        }
        $count = $Userscash->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Userscash->where($map)->order(array('cash_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach($list as $row){
            $ids[] = $row['user_id'];
        }
        $Usersex = D('Usersex');
        $map = array();
        $map['user_id'] = array('in', $ids);
        $ex = $Usersex->where($map)->select();
        $tmp = array();
        foreach ($ex as $row) {
            $tmp[$row['user_id']] = $row;
        }
        foreach ($list as $key => $row) {
            $list[$key]['bank_name'] = empty($list[$key]['bank_name']) ? $tmp[$row['user_id']]['bank_name'] : $list[$key]['bank_name'];
            $list[$key]['bank_num'] = empty($list[$key]['bank_num']) ? $tmp[$row['user_id']]['bank_num'] : $list[$key]['bank_num'];
            $list[$key]['bank_branch'] = empty($list[$key]['bank_branch']) ? $tmp[$row['user_id']]['bank_branch'] : $list[$key]['bank_branch'];
            $list[$key]['bank_realname'] = empty($list[$key]['bank_realname']) ? $tmp[$row['user_id']]['bank_realname'] : $list[$key]['bank_realname'];
        }
		$this->assign('user_cash', round($user_cash = $Userscash->where(array('type' => user,'status' =>1))->sum('money')/100,2));
		$this->assign('user_cash_commission', round($user_cash_commission = $Userscash->where(array('type' => user,'status' =>1))->sum('commission')/100,2));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	
    public function gold(){
        $Userscash = D('Userscash');
        import('ORG.Util.Page');
        $map = array('type' => shop);
        if($account = $this->_param('account', 'htmlspecialchars')){
            $map['account'] = array('LIKE', '%' . $account . '%');
            $this->assign('account', $account);
        }
		if($cash_id = (int) $this->_param('cash_id')){
            $map['cash_id'] = $cash_id;
            $this->assign('cash_id', $cash_id);
        }
		if ($user_id = (int) $this->_param('user_id')){
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		if(isset($_GET['st']) || isset($_POST['st'])){
            $st = (int) $this->_param('st');
            if($st != 999){
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        }else{
            $this->assign('st', 999);
        }
		if($code = $this->_param('code', 'htmlspecialchars')){
            if($code != 999){
                $map['code'] = $code;
            }
            $this->assign('code', $code);
        }else{
            $this->assign('code', 999);
        }
        $count = $Userscash->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Userscash->where($map)->order(array('cash_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $row){
            $ids[] = $row['user_id'];
        }
        $Usersex = D('Usersex');
        $map = array();
        $map['user_id'] = array('in', $ids);
        $ex = $Usersex->where($map)->select();
        $tmp = array();
        foreach ($ex as $row) {
            $tmp[$row['user_id']] = $row;
        }
        foreach ($list as $key => $row) {
            $list[$key]['bank_name'] = empty($list[$key]['bank_name']) ? $tmp[$row['user_id']]['bank_name'] : $list[$key]['bank_name'];
            $list[$key]['bank_num'] = empty($list[$key]['bank_num']) ? $tmp[$row['user_id']]['bank_num'] : $list[$key]['bank_num'];
            $list[$key]['bank_branch'] = empty($list[$key]['bank_branch']) ? $tmp[$row['user_id']]['bank_branch'] : $list[$key]['bank_branch'];
            $list[$key]['bank_realname'] = empty($list[$key]['bank_realname']) ? $tmp[$row['user_id']]['bank_realname'] : $list[$key]['bank_realname'];
        }
		$this->assign('shop_cash', round($shop_cash = $Userscash->where(array('type' => shop,'status' =>1))->sum('gold')/100,2));
		$this->assign('shop_cash_commission', round($shop_cash_commission = $Userscash->where(array('type' => shop,'status' =>1))->sum('commission')/100,2));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	
	
	
	//微信提现
    public function weixin_audit($cash_id = 0, $status = 0){
        if(!$status){
            $this->tuError('参数错误');
        }
        $obj = D('Userscash');
        $cash_id = (int) $cash_id;
		$detail = $obj->find($cash_id);
		if($detail = $obj->find($cash_id)){
			if ($detail['status'] == 0){
                $data = array();
                $data['cash_id'] = $cash_id;
                $data['status'] = $status;
				if(false == $obj->weixinUserCach($cash_id,1)){//微信提现逻辑封装
					$this->tuError($obj->getError());
				}else{
					
						$obj->save($data);
						D('Weixintmpl')->weixin_cash_user($detail['user_id'],1,$detail['type']);//申请提现：1会员申请，2商家同意，3商家拒绝
						if($detail['type'] == shop){
							$this->tuSuccess('商家提现操作成功', U('usercash/gold'));	
						}else{
							$this->tuSuccess('会员提现操作成功', U('usercash/index'));	
						}
					
				}
            }else{
                $this->tuError('当前订单状态不正确');
			}
	    }else{
			$this->tuError('没找到对应的提现订单');
		}
    }
	
	
	
	//支付宝提现
    public function alipay_audit($cash_id = 0, $status = 0){
        if(!$status){
            $this->tuError('参数错误');
        }
		$detail = D('Userscash')->find($cash_id);
		if($detail['status'] == 0){
			$data = array();
            $data['cash_id'] = $cash_id;
            $data['status'] = $status;
			
			if(false == D('Userscash')->alipayUserCach($cash_id,1)){//微信提现逻辑封装
				$this->tuError(D('Userscash')->getError());
			}else{
				D('Userscash')->save($data);
				D('Weixintmpl')->weixin_cash_user($detail['user_id'],1,$detail['type']);//申请提现：1会员申请，2商家同意，3商家拒绝
				if($detail['type'] == shop){
					$this->tuSuccess('商家支付宝提现操作成功', U('usercash/gold'));	
				}else{
					$this->tuSuccess('会员支付宝提现操作成功', U('usercash/index'));	
				}
			}
		}else{
            $this->tuError('当前订单状态不正确');
		}
    }
	
	
	
	//银行卡提现
	public function bank_audit($cash_id = 0, $status = 0){
        if(!$status){
            $this->tuError('参数错误');
        }
        $obj = D('Userscash');
		$cash_id = (int) $cash_id;
		if($detail = $obj->find($cash_id)){
			if ($detail['status'] == 0) {
                $data = array();
                $data['cash_id'] = $cash_id;
                $data['status'] = $status;
                if($obj->save($data)){
					D('Weixintmpl')->weixin_cash_user($detail['user_id'],1);//申请提现：1会员申请，2商家同意，3商家拒绝
                	$this->tuSuccess('操作成功', U('usercash/index'));
				}else{
					$this->tuError('更新数据库失败');
				}
            } else {
                $this->tuError('请不要重复操作');
            }
			
		}else{
			$this->tuError('没找到对应的提现订单');
		}
    }
		
		
		
	//商户微信提现
	public function weixin_audit_gold($cash_id = 0, $status = 0){
        if(!$status){
            $this->tuError('参数错误');
        }
        $obj = D('Userscash');
        $cash_id = (int) $cash_id;
		if($detail = $obj->find($cash_id)){
			if ($detail['status'] == 0) {
                $data = array();
                $data['cash_id'] = $cash_id;
                $data['status'] = $status;
				if(false == $obj-> weixinUserCach($cash_id,2)) {//微信提现逻辑封装，1会员，2商家
					$this->tuError($obj->getError());
				}else{
					if($obj->save($data)){
						D('Weixintmpl')->weixin_cash_user($detail['user_id'],1);//申请提现：1会员申请，2商家同意，3商家拒绝
						$this->tuSuccess('操作成功', U('usercash/gold'));
					}else{
						$this->tuError('请不要重复操作');
					}
				}
			}else{
				$this->tuError('更新数据库失败');
			}
	    }else{
			$this->tuError('没找到对应的提现订单');
		}
    }
	
	
	
	//商户银行卡提现
	public function bank_audit_gold($cash_id = 0, $status = 0){
        if(!$status){
            $this->tuError('参数错误');
        }
        $obj = D('Userscash');
		$cash_id = (int) $cash_id;
		if($detail = $obj->find($cash_id)){
			if ($detail['status'] == 0) {
                $data = array();
                $data['cash_id'] = $cash_id;
                $data['status'] = $status;
                if($obj->save($data)){
					D('Weixintmpl')->weixin_cash_user($detail['user_id'],1);//申请提现：1会员申请，2商家同意，3商家拒绝
                	$this->tuSuccess('操作成功', U('usercash/index'));
				}else{
					$this->tuError('更新数据库失败');
				}
            } else {
                $this->tuError('请不要重复操作');
            }
			
		}else{
			$this->tuError('没找到对应的提现订单');
		}
    }

    //拒绝用户提现
    public function jujue(){
		$status = (int) $_POST['status'];
		$cash_id = (int) $_POST['cash_id'];
        $value = $this->_param('value', 'htmlspecialchars');
        if(empty($value)){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '拒绝理由请填写'));
        }
        if(empty($cash_id)){
            $this->ajaxReturn(array('status' => 'error', 'msg' => 'ID错误'));
        }
		if(!($detail = D('Userscash')->find($cash_id))){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '提现订单详情错误'));
        }
		if($detail['status'] != 0){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '拒绝状态错误'));
        }
        if($status == 2){
            D('Users')->addMoney($detail['user_id'], $detail['money'] + $detail['commission'], '提现ID【'.$cash_id.'】会员申请提现拒绝退款，理由【'.$value.'】');
			if(D('Userscash')->save(array('cash_id' => $cash_id, 'status' => $status, 'reason' => $value))){
				D('Weixintmpl')->weixin_cash_user($detail['user_id'],3);
            	$this->ajaxReturn(array('status' => 'success', 'msg' => '拒绝退款操作成功', 'url' => U('usercash/index')));
			}else{
				$this->ajaxReturn(array('status' => 'error', 'msg' => '拒绝失败'));
			}
        }else{
			$this->ajaxReturn(array('status' => 'error', 'msg' => '提现状态不正确'));
		}
	
    }
    //拒绝商家提现
    public function jujue_gold(){
		$status = (int) $_POST['status'];
		$cash_id = (int) $_POST['cash_id'];
        $value = $this->_param('value', 'htmlspecialchars');
        if(empty($value)){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '拒绝理由请填写'));
        }
        if(empty($cash_id)){
            $this->ajaxReturn(array('status' => 'error', 'msg' => 'ID错误'));
        }
		if(!($detail = D('Userscash')->find($cash_id))){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '提现订单详情错误'));
        }
		if($detail['status'] != 0){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '拒绝状态错误'));
        }
        if($status == 2){
            D('Users')->addGold($detail['user_id'], $detail['gold'] + $detail['commission'], '提现ID【'.$cash_id.'】商家申请提现拒绝退款，理由【'.$value.'】');
			if(D('Userscash')->save(array('cash_id' => $cash_id, 'status' => $status, 'reason' => $value))){
				D('Weixintmpl')->weixin_cash_user($detail['user_id'],3);
            	$this->ajaxReturn(array('status' => 'success', 'msg' => '拒绝退款操作成功', 'url' => U('usercash/gold')));
			}else{
				$this->ajaxReturn(array('status' => 'error', 'msg' => '拒绝失败'));
			}
        }else{
			$this->ajaxReturn(array('status' => 'error', 'msg' => '提现状态不正确'));
		}
    }
   
}