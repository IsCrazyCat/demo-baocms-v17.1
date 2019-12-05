<?php
class CommunityorderModel extends CommonModel{
    protected $pk = 'order_id';
    protected $tableName = 'community_order';
    public function getType(){
        return array(
			'1' => '水费', 
			'2' => '电费', 
			'3' => '燃气费', 
			'4' => '停车费', 
			'5' => '物业费'
		);
    }
   //在线支付回调，已做好安全
    public function orderpay($log_id){
		
		$Paymentlogs = D('Paymentlogs')->find($log_id);
	
		
        $detail = $this->find($Paymentlogs['order_ids']);
		$order_id =  $detail['order_id'];
        $user_id = $Paymentlogs['user_id'];
		$type = explode('-',$Paymentlogs['types']);
		
		
        $products = D('Communityorderproducts')->where(array('order_id' => $order_id))->select();
		
        $needs = array();
        foreach ($products as $k => $val) {
            foreach ($type as $kk => $v) {
                if ($v == $val['type']) {//传到支付日志后，这里返回回来
                    $needs[$k] = $val;
                }
            }
        }
           
        foreach ($needs as $k => $value) {
       	 $check = D('Communityorderproducts')->find( $value['id']);
         	if($check['is_pay']==1){//避免重复缴费，再次
				return true;
			}  
        }   
  
	   
        foreach ($needs as $k => $val) {
            D('Communityorderproducts')->save(array('id' => $val['id'], 'is_pay' => 1));
            D('Communityorderlogs')->add(array(
                'user_id' => $user_id,
                'community_id' => $detail['community_id'],
                'money' => $val['money'],
                'type' => $val['type'],
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip()
            ));
        }
		
		$Community = D('Community')->find($detail['community_id']);
		$Users = D('Users')->find($Community['user_id']);
		
		D('Users')->addMoney($Users['user_id'], $Paymentlogs['need_pay'], '订单ID【'.$order_id.'】缴纳物业费结算给物业管理员');//给业主管理员钱钱
		//短信通知微信通知代码暂时先删除，需要联系qq 286099981
        return true;
    }
	//获取账单月份
	public function getDays(){
		$days = array();
        $Y = date('Y', NOW_TIME);
        $m = date('m', NOW_TIME);
        $d = 12 - $m;
        for ($k = 1; $k <= $d; $k++) {
            $days[$k] = $Y - 1 . '-' . (12 - $k);
        }
        for ($i = 1; $i <= $m; $i++) {
            $days[$d + $i] = $Y . '-0' . $i;
        }
		return $days;
	}	
}