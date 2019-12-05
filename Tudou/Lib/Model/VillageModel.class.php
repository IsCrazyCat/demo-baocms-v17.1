<?php

class VillageModel extends CommonModel {

    protected $pk = 'village_id';
    protected $tableName = 'village';
    protected $token = 'village';
    protected $orderby = array('orderby' => 'asc');



    public function _format($data) {
        static $area = null;
        if ($area == null) {
            $area = D('Area')->fetchAll();
        }
        $data['area_name'] = $area[$data['area_id']]['area_name'];
        return $data;

    }

	 public function getVillageCate() {
			return array(
				'1' => '休闲娱乐',
				'2' => '旅游胜地',
				'3' => '养殖乡村',
				'4' => '水果之乡',
				'5' => '鱼米之乡',
			);
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
	public function check_user_id_neq_village($user_id,$village_id){
		$detail = $this->where(array('user_id'=>$user_id,'village_id' => array('NEQ', $village_id)))->find();
        if($detail){
			return false;
		}else{
			return true;	
	    }
    }
}

