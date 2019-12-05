<?php
class CommunityAccessUserOpenModel extends CommonModel{
    protected $pk = 'open_id';
    protected $tableName = 'community_access_user_open';
	
   //用户开门
   public function user_access_open($type,$id,$user_id) {
		if(!$detail = D('CommunityAccessUser')->find($id)){
			$this->error = '信息不存在';
			return false;
		}
		if($detail['user_id'] != $user_id){
			$this->error = '门禁不属于您管理';
			return false;
		}
		if(!$access = D('CommunityAccessList')->find($detail['list_id'])){
			$this->error = '门禁不存在';
			return false;
		}
		
		$open = $this->where(array('list_id'=>$detail['list_id'],'id'=>$id,'user_id'=>$detail['user_id']))->order('open_time desc')->find();
		if(NOW_TIME - $open['open_time'] < 15){
			$this->error = '请不要频繁开门';
			return false;
		}
		$res = D('CommunityAccessList')->Lockstate($detail['list_id']);
		if($res['state'] != 1){
			$this->error = '门禁不在线'.$result['state_msg'];
			return false;
		}
		
		$result = D('CommunityAccessList')->Openlock($detail['list_id']);
		if($result['state'] != 1){
			$this->error = $result['state_msg'];
			return false;
		}else{
			$Users = D('Users')->find($user_id);
			$owner = D('Communityowner')->find($detail['owner_id']);
			$intro = '用户【'.$Users['nickname'].'】开门成功，业主姓名【'.$owner['name'].'】';
			$this->openLogs($type,$detail['list_id'],$detail['community_id'],$user_id,$detail['owner_id'],$intro);
			return true;
		}
    }
	
	
   //游客开门
   public function touristUnlock($type,$id,$list_id,$user_id){
	   
	   	$User = D('CommunityAccessUser')->find($id);
	    $Users = D('Users')->find($User['user_id']);
		$Communityowner = D('Communityowner')->where(array('user_id'=>$Users['user_id']))->find();
		
	   
		if(!$access = D('CommunityAccessList')->find($list_id)){
			$this->error = '门禁不存在';
			return false;
		}
		$res = D('CommunityAccessList')->Lockstate($list_id);
		if($res['state'] != 1){
			$this->error = '门禁不在线'.$result['state_msg'];
			return false;
		}
		$result = D('CommunityAccessList')->Openlock($list_id);
		if($result['state'] != 1){
			$this->error = $result['state_msg'];
			return false;
		}else{
			$tourist = D('Users')->find($user_id);
			$intro = '业主【'.$Communityowner['name'].'】会员昵称【'.$Users['nickname'].'】分享的二维码，用户或者游客【'.$tourist['nickname'].'】开门成功';
			$this->openLogs($type,$access['list_id'],$access['community_id'],$user_id,$access['owner_id'],$intro);
			return true;
		}
    }
	
	
	
	//日志
	public function openLogs($type,$list_id,$community_id,$user_id,$owner_id,$intro){
		$this->add(array(
			'type' => $type, 
			'list_id' => $list_id, 
			'community_id' => $community_id, 
			'user_id' => $user_id, 
			'owner_id' => $owner_id,
			'intro' => $intro, 
			'open_time' => NOW_TIME, 
			'create_ip' => get_client_ip()
		));
		return true;
	}
	
	
}