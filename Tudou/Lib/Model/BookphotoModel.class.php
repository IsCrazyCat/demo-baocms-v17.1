<?php
class BookphotoModel extends CommonModel{
    protected $pk = 'pic_id';
    protected $tableName = 'book_photos';
    public function upload($order_id, $photos){
        $this->delete(array("where" => array('order_id' => $order_id)));
        foreach ($photos as $val) {
            $this->add(array('order_id' => $order_id, 'photo' => htmlspecialchars($val)));
        }
        return true;
    }
    public function getPics($order_id){
        $order_id = (int) $order_id;
        return $this->where(array('order_id' => $order_id))->select();
    }
}