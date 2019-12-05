<?php
class TuanfavoritesModel extends CommonModel{
    protected $pk = 'favorites_id';
    protected $tableName = 'tuan_favorites';
	
    public function check($tuan_id, $user_id){
        $data = $this->find(array('where' => array('tuan_id' => (int) $tuan_id, 'user_id' => (int) $user_id)));
        return $this->_format($data);
    }
}