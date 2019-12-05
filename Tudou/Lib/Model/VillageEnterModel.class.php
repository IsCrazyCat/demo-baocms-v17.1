<?php
class VillageEnterModel extends CommonModel{
    protected $pk = 'enter_id';
    protected $tableName = 'village_enter';
    protected $token = 'village_enter';
	
	
	 public function get_goods_name($enter_id){
		$detail = $this->find($enter_id); 
		if($detail['type'] == 1){
			$title = D('Goods')->find($detail['id']);
		}else{
			$title = D('Tuan')->find($detail['id']);
		}
		return $title['title']; 
	 }
	 //返回乡村名称
	 public function get_village_name($enter_id){
		 $data = $this->find($enter_id);
		 $detail = D('Village')->find($data['village']);
		 return $detail['name'];
	 }
	 //获取数据,城市，类型，乡村id，数量
	 public function get_enter($city_id,$type,$village_id,$limit){
		 if($type == 2){
			$query = array('village_id' => $village_id,'closed' => 0, 'audit' => 1, 'type' => 2);
			$lists = $this->where($query)->order(array('enter_id' => 'desc'))->select();
			foreach ($lists as $val) {
				$tuan_ids[$val['id']] = $val['id'];
			}
			$map = array('closed' => 0, 'audit' => 1, 'city_id' => $city_id, 'end_date' => array('EGT', TODAY), 'tuan_id' => array('in',$tuan_ids)); 
			$list = D('Tuan')->where($map)->limit($limit)->select(); 
		 }
		 return $list;
	 }
	 
	
		
}