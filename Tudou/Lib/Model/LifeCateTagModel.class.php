<?php
class LifeCateTagModel extends CommonModel{
	
    protected $pk = 'tag_id';
    protected $tableName = 'life_cate_tag';
    protected $token = 'life_cate_tag';
    protected $orderby = array('orderby' => 'asc', 'tag_id' => 'asc');
	
	
    public function getTags($cate_id){
        $items = $this->where(array('cate_id' => (int) $cate_id))->select();
        $return = array();
        foreach ($items as $val) {
            $return[$val['type']][$val['tag_id']] = $val;
        }
        return $return;
    }
}