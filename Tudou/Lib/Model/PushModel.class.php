<?php
class PushModel extends CommonModel{
    protected $pk   = 'push_id';
    protected $tableName =  'push';
	
	protected $type = array(
        1 => '短信',
        2 => '微信',
		3 => '站内信',
		4 => '安卓客户端',		
        5 => 'ipone客户端',
		6 => '小程序',
    );
	
	protected $category = array(
        1 => '会员',
        2 => '商家',
		3 => '分站管理员',
		4 => '物业管理员',	
		5 => '物流配送员',
		6 => '商家员工',
		7 => '智慧乡村管理员',	
    );
	
	
    public function getType(){
        return $this->type;
    }
	public function getCategory(){
        return $this->category;
    }
	//获取那些会员是应该发送的
	public function getUserIds($category,$rank_id,$grade_id){
			if($category == 1){
				
				$condition['closed'] = '0';
				if($rank_id){
					$condition['rank_id'] = $rank_id;
				}
				$Users = D('Users')->where($condition)->select();
				foreach($Users as $val) {
					$user_ids[$val['user_id']] = $val['user_id'];
				}
			}
			if($category == 2){
				
				$condition['audit'] = '1';
				$condition['closed'] = '0';
				if($grade_id){
					$condition['grade_id'] = $grade_id;
				}
				$Shop = D('Shop')->where($condition)->select();
				foreach($Shop as $val) {
					$user_ids[$val['user_id']] = $val['user_id'];
				}
			}
			if($category == 3){
				return false;
			}
			if($category == 4){
				$Community = D('Community')->where(array('closed'=>'0','audit'=>'1'))->select();
				foreach($Community as $val) {
					$user_ids[$val['user_id']] = $val['user_id'];
				}
			}
			
			if($category == 5){
				$Delivery = D('Delivery')->where(array('closed'=>'0','audit'=>'1'))->select();
				foreach($Delivery as $val) {
					$user_ids[$val['user_id']] = $val['user_id'];
				}
			}
			
			if($category == 6){
				$Shopworker = D('Shopworker')->where(array('closed'=>'0','status'=>'1'))->select();
				foreach($Shopworker as $val) {
					$user_ids[$val['user_id']] = $val['user_id'];
				}
			}
			if($category == 7){
				$Village = D('Village')->where(array('closed'=>'0'))->select();
				foreach($Village as $val) {
					$user_ids[$val['user_id']] = $val['user_id'];
				}
			}
			
			return  $user_ids;
		
	}
	//返回会员数组
 	public function getList($push_id){
		
		if(!$detail = $this->find($push_id)){
            return false;
        }
		
		$user_ids = $this->getUserIds($detail['category'],$detail['rank_id'],$detail['grade_id']);
		$condition['user_id'] = array('in',$user_ids);
		
		//给会员发送短信
		if($detail['type'] == 1){
			$list = D('Users')->where($condition)->select();
			foreach($list as $k => $val){
				if(!$val['mobile']){
					unset($list[$k]);
				}
			}
			return $list;
		}elseif($detail['type'] == 2){
			$list = D('Users')->where($condition)->select();
			
			foreach($list as $k => $val){
				$Connect = D('Connect')->where(array('type' =>'weixin','uid'=>$val['user_id']))->find();
				if(!$Connect['open_id']){
					unset($list[$k]);
				}
			}
			return $list;
		}else{
			$list = D('Users')->where($condition)->select();
			return $list;
		}
		return false;
    }
}