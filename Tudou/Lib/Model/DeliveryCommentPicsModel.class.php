<?php


class DeliveryCommentPicsModel extends CommonModel{
    protected $pk   = 'photo_id';
    protected $tableName =  'delivery_comment_pics';


    public function upload($comment_id,$photos,$order_id){
        $comment_id = (int)$comment_id;
        $this->delete(array("where"=>array('comment_id'=>$comment_id)));
        foreach($photos as $val){
            $this->add(array('photo'=>$val,'comment_id'=>$comment_id,'order_id'=>$order_id));
        }
        return true;
    }

    public function getPics($comment_id){
        $comment_id = (int)$comment_id;
        return $this->where(array('comment_id'=>$comment_id))->select();
    }

}