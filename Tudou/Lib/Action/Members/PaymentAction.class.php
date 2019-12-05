<?php
class IndexAction extends CommonAction{
    public function pay() {
        $logs_id = (int) $this->_get('logs_id');
        if (empty($logs_id)) {
            $this->error('没有有效的支付');
        }
        if (!($detail = D('Paymentlogs')->find($logs_id))) {
            $this->error('没有有效的支付');
        }
        if ($detail['code'] != 'money') {
            $this->error('没有有效的支付');
        }
        $member = D('Users')->find($this->uid);
        if ($detail['is_paid']) {
            $this->error('没有有效的支付');
        }
        if ($member['money'] < $detail['need_pay']) {
            $this->error('很抱歉您的账户余额不足', U('members/index'));
        }
        $member['money'] = $member['money'] - $detail['need_pay'];
        if (D('Users')->save(array('user_id' => $this->uid, 'money' => $member['money']))) {
            D('Usermoneylogs')->add(array(
				'user_id' => $this->uid, 
				'money' => -$detail['need_pay'], 
				'create_time' => NOW_TIME, 
				'create_ip' => get_client_ip(), 
				'intro' => '余额支付' . $logs_id
			));
            D('Payment')->logsPaid($logs_id);
            $this->success('支付成功', U('members/index'));
        }
    }
   
    public function recharge(){
        if ($this->isPost()) {
            $card_key = $this->_post('card_key', htmlspecialchars);
            if (!($detail = D('Rechargecard')->where(array('card_key' => $card_key))->find())) {
                $this->tuError('该充值卡不存在');
            }
            if ($detail['is_used'] == 1) {
                $this->tuErrorJump('该充值卡已经使用过了', U('members/recharge'));
            }
            $member = D('Users')->find($this->uid);
            $member['money'] += $detail['value'];
            if (D('Users')->save(array('user_id' => $this->uid, 'money' => $member['money']))) {
                D('Usermoneylogs')->add(array(
					'user_id' => $this->uid, 
					'money' => +$detail['value'], 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'intro' => '代金券充值' . $detail['card_id']
				));
                $res = D('Rechargecard')->save(array('card_id' => $detail['card_id'], 'is_used' => 1));
                if (!empty($res)) {
                    D('Rechargecard')->save(array('card_id' => $detail['card_id'], 'user_id' => $this->uid, 'used_time' => NOW_TIME));
                }
                $this->tuSuccess('充值成功', U('members/rechargecard'));
            }
   
        } else {
            $this->display();
        }
    }
}