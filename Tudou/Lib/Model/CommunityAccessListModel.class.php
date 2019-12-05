<?php
class CommunityAccessListModel extends CommonModel{
    protected $pk = 'list_id';
    protected $tableName = 'community_access_list';
	
	
	public function config(){
		$config = D('Setting')->fetchAll();
		return array($config['community']['appid'],$config['community']['appsecret'],$config['community']['aeskey']);
    }
	//添加
	public function addAccessList($lock_sn){
		list($appid,$appsecret,$aeskey)= $this->config();
        import('ORG.Util.Access');
		$data = new Access();
		$data->appid = $appid;
		$data->appsecret = $appsecret;
		$data->aeskey = $aeskey;
		$data->lock_sn = $lock_sn;
		$datas = $data->getPostlock();
		return $datas;
    }
	//删除模块
	public function Dellock($list_id){
		$detail = $this->find($list_id);
		list($appid,$appsecret,$aeskey)= $this->config();
        import('ORG.Util.Access');
		$data = new Access();
		$data->appid = $appid;
		$data->appsecret = $appsecret;
		$data->aeskey = $aeskey;
		$data->lock_sn = $detail['lock_sn'];
		$datas = $data->getDellock();
		return $datas;
    }
	
	//开门模块
	public function Openlock($list_id){
		$detail = $this->find($list_id);
		list($appid,$appsecret,$aeskey)= $this->config();
        import('ORG.Util.Access');
		$data = new Access();
		$data->appid = $appid;
		$data->appsecret = $appsecret;
		$data->aeskey = $aeskey;
		$data->lock_sn = $detail['lock_sn'];
		$datas = $data->getOpenlock();
		return $datas;
    }
	
	//查询模块
	public function Lockstate($list_id){
		$detail = $this->find($list_id);
		list($appid,$appsecret,$aeskey)= $this->config();
        import('ORG.Util.Access');
		$data = new Access();
		$data->appid = $appid;
		$data->appsecret = $appsecret;
		$data->aeskey = $aeskey;
		$data->lock_sn = $detail['lock_sn'];
		$datas = $data->getLockstate();
		return $datas;
    }
	//删除设备删除用户记录，开门记录
	public function delete_user_open($list_id){
		 D('CommunityAccessUser')->delete(array('where' => "list_id = '{$list_id}'"));
		 D('CommunityAccessUserOpen')->delete(array('where' => "list_id = '{$list_id}'"));
		return true;
    }
	
}