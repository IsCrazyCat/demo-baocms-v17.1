<?php
class SensitiveAction extends CommonAction
{
    private $create_fields = array('words');
    private $edit_fields = array('words');
    public function index()
    {
        $Sensitive = D('Sensitive');
        import('ORG.Util.Page');
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['words'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Sensitive->where($map)->count();
        $Page = new Page($count,50);
        $show = $Page->show();
        $list = $Sensitive->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
    public function create(){
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false),array('words'));
			$data['words'] = htmlspecialchars($data['words']);
			
			if(empty($data['words'])){
				$this->tuError('关键词不能为空');
			}
			$data['words'] = explode('|',$data['words']); 
			$i = 0;
			foreach ($data['words'] as $key => $val){
				$i++;
                D('Sensitive')->add(array('words' => $val));
            }
			D('Sensitive')->cleanCache();
			if($i > 0){
				$this->tuSuccess('添加成功'.$i.'条数据', U('sensitive/index'));
			}else{
				$this->tuError('操作失败');
			}
        }else{
            $this->display();
        }
    }

	
    public function edit($words_id = 0){
        if ($words_id = (int) $words_id){
            $obj = D('Sensitive');
            if(!($detail = $obj->find($words_id))){
                $this->tuError('请选择要编辑的敏感词');
            }
            if($this->isPost()){
                $data = $this->checkFields($this->_post('data', false),array('words'));
				$data['words'] = htmlspecialchars($data['words']);
				if(empty($data['words'])){
					$this->tuError('关键词不能为空');
				}
                $data['words_id'] = $words_id;
                if (false !== $obj->save($data)){
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('sensitive/index'));
                }
                $this->tuError('操作失败');
            }else{
                $this->assign('detail', $detail);
                $this->display();
            }
        }else{
            $this->tuError('请选择要编辑的敏感词');
        }
    }
   
   
    public function delete($words_id = 0){
        if (is_numeric($words_id) && ($words_id = (int) $words_id)) {
            $obj = D('Sensitive');
            $obj->delete($words_id);
            $obj->cleanCache();
            $this->tuSuccess('删除成功', U('sensitive/index'));
        } else {
            $words_id = $this->_post('words_id', false);
            if (is_array($words_id)) {
                $obj = D('Sensitive');
                foreach ($words_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->tuSuccess('删除成功', U('sensitive/index'));
            }
            $this->tuError('请选择要删除的敏感词');
        }
    }
}