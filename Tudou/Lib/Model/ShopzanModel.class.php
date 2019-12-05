<?php
class ShopzanModel extends CommonModel{
    protected $pk = 'zan_id';
    protected $tableName = 'shop_zan';
	
	//商家点赞
    public function zan($shop_id, $user_id){
		$config = D('Setting')->fetchAll();
		$Shop = D('Shop')->find($shop_id);
        $bg_time = strtotime(TODAY);
		
		if(empty($config['shop']['is_shop_zan'])){
			$this->error = '网站并没有开启商家点赞功能';
			return false;
		}
		
		$count = $this->where(array('user_id' => $user_id,'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'closed'=>0))->count();
		if($config['shop']['day_zan_num']){
			if($count >= $config['shop']['day_zan_num']){
				$this->error = '您点赞太频繁，系统限制每天最多点赞【'.$config['shop']['day_zan_num'].'】次，明天再来吧';
				return false;
			}
		}
		
		if($config['shop']['day_shop_zan_num']){
			$day_shop_zan_num = $this->where(array('shop_id' => $shop_id,'user_id' => $user_id,'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'closed'=>0))->count();
			if($day_shop_zan_num >= $config['shop']['day_shop_zan_num']){
				$this->error = '您对商家【'.$Shop['shop_name'].'】点赞太频繁了，系统限制每天对同一个商家最多点赞【'.$config['shop']['day_shop_zan_num'].'】次';
				return false;
			}
		}
        $data = array('shop_id' => $shop_id, 'user_id' => $user_id, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
		
		
        if($this->add($data)){
			D('Shop')->updateCount($shop_id, 'zan_num');
			if($config['shop']['zan_reward_integral']){
				D('Users')->addIntegral($user_id, $config['shop']['zan_reward_integral'], '给商家【'.$Shop['shop_name'].'】点赞奖励积分');
			}
			return true;
        }else{
			$this->error = '操作失败';
			return false;
		}
    }
	
}