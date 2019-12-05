<?php
class VillageAction extends CommonAction {
	protected function _initialize(){
        parent::_initialize();
        if(!$this->_CONFIG['operation']['village']) {
            $this->error('此功能已关闭');die;
        }
    }
    public function index() {
        $this->display(); 
    }


    public function loaddata() {
        $map = array('user_id' => $this->uid);
		$joined = D('Villagejoin')->where($map)->order(array('join_id' => 'desc'))->limit(0,20)-> select();	
		foreach ($joined as $val) {
			$cmm_ids[$val['village_id']] = $val['village_id'];
		}
		$this->assign('list', D('Village')->itemsByIds($cmm_ids));		
        $this->display();

    }
	

}