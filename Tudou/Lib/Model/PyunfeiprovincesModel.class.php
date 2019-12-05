<?php

class PyunfeiprovincesModel extends CommonModel {

    protected $pk = 'id';
    protected $tableName = 'pyunfei_provinces';
	
	public function getIds($kuaidi_id){
        $datas = $this->where(array('kuaidi_id'=>$kuaidi_id))->select();
        $return = array();
        foreach ($datas as $val) {
            $return[$val['province_id']] = $val['province_id'];
        }
        return $return;
    }
	
	public function getIds2($yunfei_id){
        $datas = $this->where(array('yunfei_id'=>$yunfei_id))->select();
        $return = array();
        foreach ($datas as $val) {
            $return[$val['province_id']] = $val['province_id'];
        }
        return $return;
    }
	
	public function getIds3($kuaidi_id,$yunfei_id){
        $datas = $this->where(array('kuaidi_id'=>$kuaidi_id))->select();
        $return = array();
        foreach ($datas as $val) {
			if($val['yunfei_id'] == $yunfei_id) { 
				unset($val);
			}
			$return[$val['province_id']] = $val['province_id'];
        }
        return $return;
		
    }

}