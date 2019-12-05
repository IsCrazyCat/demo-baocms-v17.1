<?php
class MarketcateModel extends CommonModel{
    protected $pk   = 'cate_id';
    protected $tableName =  'market_cate';
    
    public function updateNum($cate_id){
        $cate_id = (int) $cate_id;
        $count = D('Marketproduct')->where(array('cate_id'=>$cate_id))->count();
        return $this->save(array(
            'cate_id' => $cate_id,
            'num'     => (int)$count
        ));
    }
    
	public function getParentsId($id){
        $data = $this->fetchAll();
        $parent_id = $data[$id]['parent_id'];
        return $parent_id;
    }
	

    public function getChildren($id){
        $local = array();
		$arr = D('Marketcate')->select();
        $local[] = $id;
        foreach ($arr as $val) {
            if ($val['parent_id'] == $id) {
                $local[] = $val['cate_id'];
            }
        }
        return $local;
    }
	
}