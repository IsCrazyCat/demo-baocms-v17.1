<?php
class CommunityModel extends CommonModel{
    protected $pk = 'community_id';
    protected $tableName = 'community';
    protected $orderby = array('orderby' => 'asc');
    public function _format($data){
        static $area = null;
        if ($area == null) {
            $area = D('Area')->fetchAll();
        }
        $data['area_name'] = $area[$data['area_id']]['area_name'];
        return $data;
    }
	//检测会员是不是在管理了
	public function check_user_id_occupy($user_id){
        if($this->where(array('user_id'=>$user_id))->find()){
			return false;
		}else{
			return true;	
	    }
    }
	
	//检测编辑的时候会员是否占用
	public function check_user_id_neq_community($user_id,$community_id){
		$detail = $this->where(array('user_id'=>$user_id,'community_id' => array('NEQ', $community_id)))->find();
        if($detail){
			return false;
		}else{
			return true;	
	    }
    }
}