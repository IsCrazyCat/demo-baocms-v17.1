<?php
class LifeSubscribeModel extends CommonModel {
    protected $pk = 'subscribe_id';
    protected $tableName = 'life_subscribe';
    protected $token = 'life_subscribe';
	
	//推送模板消息
	public function pushSubscribe($life_id){
		$detail = D('Life')->find($life_id);
		$list = $this->where(array('cate_id'=>$detail['cate_id']))->select(); 
		foreach($list as $k => $val){
			D('Weixinmsg')->weixin_tmpl_life_subscribe($detail,$val['user_id']);
		}
		return true;
	}
}
