<?php
class ShopguideModel extends CommonModel{
    protected $pk   = 'guide_id';
    protected $tableName =  'shop_guide';
    public function getError() {
        return $this->error;
    } 
   public function upAdd($user_guide_ids,$shop_id){
	   foreach ($user_guide_ids as $id) {
		   $data = array();
		   $data['user_id'] = $id;
		   $data['shop_id'] = $shop_id;
		   $data['rate'] = 10;
		   $data['intro'] = '会员申请';
		   $data['closed'] = 0;
		   $data['create_time'] = NOW_TIME;
		   $data['create_ip'] = get_client_ip();
		   $this->add($data);
       }
      return TRUE;
    }
  
  
    //检测各种错误
    public function check_user_guide_id($user_ids){
		if (is_numeric($user_ids) && ($user_id = (int) $user_ids)) {
           if(!D('Users')->find(array('where' => array('user_id' => $user_id)))) {
				$this->error = '您输入的推荐会员ID不存在,请认证填写会员UID';
				return false;
			}	
		   return true;
        }else{
			if (is_array($user_ids)) {
			 	foreach ($user_ids as $id) {
					$Users = D('Users')->find(array('where' => array('user_id' => (int)$id)));
                    if(!$Users) {
						$this->error = '您输入的推荐会员ID：'.(int)$id.'不存在,请重新填写';
						return false;
					}	
                }
            }else{
				$this->error = '会员ID格式不正确';
				return false;
			}
			return true;
		}
		return true;
	}
			
			
			
}