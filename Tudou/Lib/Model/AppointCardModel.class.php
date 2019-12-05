<?php
class AppointCardModel extends CommonModel{
	
    protected $pk   = 'card_id';
    protected $tableName =  'appoint_card';
    protected $token = 'card_cert';
	
	
	protected $state = array(
        0 => '未使用',
        1 => '已使用',
    );
	
	
    public function getstate(){
       return  array(
			0 => '未使用',
			1 => '已使用',
	   );
    }
	
	public function getStatus(){
       return  array(
			0 => '未验证',
			1 => '已验证',
	   );
    }

	public function getError() {
        return $this->error;
    }
 
	//生成优惠卡编号
    public function getCardNumber(){
        $i = 0;
        while (true) {
            $i++;
			
			$code1 = rand_string(2,2,'');//2个大写字母
			$code2 = rand_string(8,1,'');//数字
			
			$code = str_shuffle($code1.''.$code2);//随机打乱字符串
			
            $data = $this->find(array('where' => array('cardNumber' => $code)));
            if (empty($data)) {
                return $code;
            }
            if ($i > 20) {
                return $code;
            }
        }
    }

  
   
}