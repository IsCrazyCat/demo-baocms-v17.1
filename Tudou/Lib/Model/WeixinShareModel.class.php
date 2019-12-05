<?php
class WeixinShareModel extends CommonModel{
    protected $pk = 'share_id';
    protected $tableName = 'weixin_share';
	
	

	//预览接口
	 public function update($fuid,$user_id,$controller,$action){
		 $data = array();
		 $data['fuid'] = $fuid;
		 $data['user_id'] = $user_id;
		 $data['controller'] = $controller;
		 $data['action'] = $action;
		 $data['create_time'] = NOW_TIME;
         $data['create_ip'] = get_client_ip();
		 $this->add($data);
		 return true;
	}
}