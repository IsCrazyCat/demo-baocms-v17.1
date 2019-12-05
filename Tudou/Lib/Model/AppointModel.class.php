<?php

class AppointModel extends CommonModel {

    protected $pk = 'appoint_id';
    protected $tableName = 'appoint';
	
	
	public function getAppoinOrderType() {
        return array(
            '1' => '预约订单',
            '2' => '候选面试',
        );
    }
	

    public function getAppoinStar() {
        return array(
            '1' => '一星技师',
            '2' => '二星技师',
            '3' => '三星技师',
            '4' => '四星技师',
            '5' => '五星技师',
        );
    }
	
	 public function getAppoinCert() {
        return array(
            '1' => '健康证',
            '2' => '催乳师证',
            '3' => '母婴护理证',
            '4' => '育婴师证',
            '5' => '月嫂证',
			'6' => '资格证',
			'7' => '保健按摩证件',
			'8' => '身份证',
			'9' => '驾驶证',
			'10' => '学历证',
			'11' => '其他证件',
        );
    }
	
	public function getAppoinDate(){
        return array(
			'1' => '1日', 
			'2' => '2日', 
			'3' => '3日', 
			'4' => '4日', 
			'5' => '5日', 
			'6' => '6日',
			'7' => '7日',
			'8' => '8日', 
			'9' => '9日', 
			'10' => '10日', 
			'11' => '11日', 
			'12' => '12日', 
			'13' => '13日',
			'14' => '14日',
			'15' => '15日', 
			'16' => '16日', 
			'17' => '17日', 
			'18' => '18日', 
			'19' => '19日', 
			'20' => '20日',
			'21' => '21日',
			'22' => '22日', 
			'23' => '23日', 
			'24' => '24日', 
			'25' => '25日', 
			'26' => '26日', 
			'27' => '27日',
			'28' => '28日',
			'29' => '29日', 
			'30' => '30日', 
			'31' => '31日', 
		);
    }
	//生肖
	 public function getAppoinZodiac() {
        return array(
            '1' => '鼠',
            '2' => '牛',
            '3' => '虎',
            '4' => '兔',
            '5' => '龙',
			'6' => '蛇',
            '7' => '马',
            '8' => '羊',
            '9' => '猴',
			'10' => '鸡',
            '11' => '狗',
            '13' => '猪',
        );
    }
    //星座
	public function getAppoinConstellatory() {
        return array(
			'1' => '白羊座', 
			'2' => '金牛座', 
			'3' => '双子座', 
			'4' => '巨蟹座', 
			'5' => '狮子座', 
			'6' => '处女座', 
			'7' => '天秤座', 
			'8' => '天蝎座', 
			'9' => '射手座', 
			'10' => '魔蝎座', 
			'11' => '水瓶座', 
			'12' => '双鱼座'
		);
    }
	//普通话水平
	public function getAppoinMandarin() {
        return array(
            '1' => '一般',
            '2' => '较好',
            '3' => '很好',
        );
    }
	 public function add_appoint($appoint_id){
        $appoint_id = (int)$appoint_id;
        $data = $this->find($appoint_id);
        if(empty($data)){
            $data = array('appoint_id'=>$appoint_id);
            $this->add($data);
        }
        return $data;
    }
	
	public function appoint_buy($user_id,$appoint_id,$price,$order_id){
        $Appoint = D('Appoint')->find($appoint_id);//商品状态
		$shop = D('Shop')->find($Appoint['shop_id']);
		
		$user_intro = '购买家政'.$Appoint['title'].'订单号'.$order_id;
		$shop_intro = '用户购买家政结算：订单号'.$order_id;
		
		D('Users')->addMoney($user_id, -$price, $user_intro);//扣余额
		if ($price > 0) {
          D('Shopmoney')->add(array(
				'shop_id' => $shop['shop_id'], 
				'city_id' => $shop['city_id'], 
				'area_id' => $shop['area_id'], 
				'money' => $price, 
				'create_time' => NOW_TIME, 
				'create_ip' => $ip, 
				'type' => 'goods', 
				'order_id' => $order_id, 
				'intro' => $shop_intro
			));
          D('Users')->Money($shop['user_id'],$price,$shop_intro);//写入金块
         }
        return true;
    }
	
     public function getCfg(){
        
        return  array(
            1 => '00:30',
            2 => '01:00',
            3 => '01:30',
            4 => '02:00',
            5 => '02:30',
            6 => '03:00',
            7 => '03:30',
            8 => '04:00',
            9 => '04:30',
            10=> '05:00',
            11=> '05:30',
            12=> '06:00',
            13=> '06:30',
            14=> '07:00',
            15=> '07:30',
            16=> '08:00',
            17=> '08:30',
            18=> '09:00',
            19=> '09:30',
            20=> '10:00',
            21=> '10:30',
            22=> '11:00',
            23=> '11:30',
            24=> '12:00',
            25=> '12:30',
            26=> '13:00',
            27=> '13:30',
            28=> '14:00',
            29=> '14:30',
            30=> '15:00',
            31=> '15:30',
            32=> '16:00',
            33=> '16:30',
            34=> '17:00',
            35=> '17:30',
            36=> '18:00',
            37=> '18:30',
            38=> '19:00',
            39=> '19:30',
            40=>'20:00',
            41=>'20:30',
            42=>'21:00',
            43=>'21:30',
            44=>'22:00',
            45=>'22:30',
            46=>'23:00',
            47=>'23:30',
            48=>'24:00',
        );
    }

}
