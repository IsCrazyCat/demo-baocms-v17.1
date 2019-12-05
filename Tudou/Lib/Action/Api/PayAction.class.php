<?php

class PayAction extends CommonAction{
  
	
	 //拼车支付
    public function Pay(){
      include APP_PATH . 'Lib/Action/Api/wxpay.php';
	  
        $res = D('Payment')->getPayment('weixin');;
		$config = D('Setting')->fetchAll();
		
        $appid = $config['wxapp']['appid'];//小程序appid
        $openid= I('openid');//oQKgL0ZKHwzAY-KhiyEEAsakW5Zg
        $mch_id = $res['mchid'];
        $key=$res['appkey'];//小程序appsecret
		
        $out_trade_no = $mch_id. time();
        $total_fee = I ('money');
		
           if(empty($total_fee)){
              $body = "订单付款";
              $total_fee = floatval(99*100);
            }else{
             $body = "订单付款";
             $total_fee = floatval($total_fee*100);
           }
		   
		   if($order_id = I ('order_id')){
		   		M('PincheOrder')->where(array('order_id'=>$order_id))->save(array('out_trade_no'=>$out_trade_no));
           }
		   //p($appid.'----'.$openid.'----'.$mch_id.'----'.$key.'----'.$out_trade_no.'----'.$body.'----'.$total_fee);die;
           $weixinpay = new WeixinPay($appid,$openid,$mch_id,$key,$out_trade_no,$body,$total_fee);//支付接口
           $return=$weixinpay->pay();
		   //p($return);die;
           echo json_encode($return);
    }
		 
	//回调地址	 
    public function SavePayLog(){
		$order_id = I('order_id','','trim');//订单ID
		$types = I('types','','trim,htmlspecialchars');//类型
		$money = I('money','','trim');//金额
		echo 2;
	}
	
   //商城付款改变订单状态
  public function PayOrder(){
      global $_W, $_GPC;
      //获取订单信息
      $orderinfo=pdo_get('zhtc_order',array('id'=>$_GPC['order_id']));
      pdo_update('zhtc_goods',array('goods_num -='=>$orderinfo['good_num'],'sales +='=>$orderinfo['good_num']),array('id'=>$orderinfo['good_id']));
      $res=pdo_update('zhtc_order',array('state'=>2,'pay_time'=>time()),array('id'=>$_GPC['order_id']));
      if($res){
        echo  '1';
      }else{
        echo  '2';
      }
  }
	
	
}
