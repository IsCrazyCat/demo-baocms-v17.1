<?php
class CityagentModel extends CommonModel{
    protected $pk   = 'agent_id';
    protected $tableName =  'city_agents';
    protected $token = 'city_agent';
    protected $orderby = array('orderby'=>'asc');
    
    public function  getParentsId($id){
        $data = $this->fetchAll();
        $parent_id = $data[$id]['parent_id'];
        $parent_id2 = $data[$parent_id]['parent_id'];
        if($parent_id2 == 0) return $parent_id;
        return  $parent_id2;
    }
	public function GetCityAgentCity($agent_id){
		$agent_ids = $this->getChildren($agent_id);
		$count = D('City')->where(array('agent_id' => array('IN', $agent_ids), 'audit' => 1))->count();
        return  $count;
    }
	//返回数组分类
    public function getChildren($agent_id){
		if($agent_id){
			$local = array();
			$data = $this->fetchAll();
			$detail = $this->find($agent_id);
			if($detail['parent_id'] != 0){
				$local[]= $agent_id;
			}else{
				foreach($data as $val){
					foreach($data as  $val1){
						if($val1['parent_id'] == $val['agent_id']){
							$child = FALSE;
							$local[]=$val1['agent_id'];
						}
					}
				}
			}
			return $local;
		}else{
			return false;	
		}
    }
	//返回城市ID数组
    public function get_city_ids($agent_ids){
		$local = array();
		$list = D('City')->where(array('agent_id' => array('IN', $agent_ids), 'audit' => 1))->select();
		foreach($list as $k => $val){
			$local[]=$val['city_id'];
        }
        return $local;
    }
	
}