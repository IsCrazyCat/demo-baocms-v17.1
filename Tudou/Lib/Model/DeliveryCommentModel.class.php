<?php

class DeliveryCommentModel extends CommonModel {
    protected $pk = 'comment_id';
    protected $tableName = 'delivery_comment';

    public function check($comment_id, $user_id) {
        $data = $this->find(array('where' => array('comment_id' => (int) $comment_id, 'user_id' => (int) $user_id)));
        return $this->_format($data);
    }

   
    

}