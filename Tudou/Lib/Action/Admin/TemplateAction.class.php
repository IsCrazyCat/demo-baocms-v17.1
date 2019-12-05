<?php
class TemplateAction extends CommonAction{
	
	public function _initialize(){
        parent::_initialize();
        $this->getType = D('Template')->getType();
        $this->assign('types', $this->getType);
    }
	
    public function index(){
        $dirs = getDirName(BASE_PATH . '/themes/');
        $template = array();
        foreach ($dirs as $val) {
            $file = BASE_PATH . '/themes/' . $val . '/config.xml';
            if (file_exists($file)) {
                $local = objectToArray(simplexml_load_file($file));
                $template[] = $local;
            }
        }
		
		$obj = D('Template');
        $map = array('closed'=>0);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		if(isset($_GET['type']) || isset($_POST['type'])){
            $type = (int) $this->_param('type');
            if($type != 999){
                $map['type'] = $type;
            }
            $this->assign('type', $type);
        }else{
            $this->assign('type', 999);
        }
		$list = $obj->where($map)->order(array('template_id' => 'desc'))->select();
        $this->assign('list', $list);
		$this->assign('themes', D('Template')->fetchAll());
        $this->display();
    }
    public function install(){
        $theme = $this->_get('theme', 'htmlspecialchars');
        if (empty($theme)) {
            $this->tuError('请选择模版');
        }
        $file = BASE_PATH . '/themes/' . $theme . '/config.xml';
        if (!file_exists($file)) {
            $this->tuError('模版不存在');
        }
        $datas = D('Template')->fetchAll();
        if ($datas[$theme]) {
            $this->tuError('模版已安装');
        }
        $local = objectToArray(simplexml_load_file($file));
        $data = array('name' => $local['name'], 'theme' => $local['theme'], 'photo' => $local['photo']);
        if (D('Template')->add($data)) {
            D('Template')->cleanCache();
            $this->tuSuccess('安装成功', U('template/index'));
        }
    }
    public function uninstall(){
        $theme = $this->_get('theme', 'htmlspecialchars');
        if (empty($theme)) {
            $this->tuError('请选择模版');
        }
        $datas = D('Template')->fetchAll();
        if (!$datas[$theme]) {
            $this->tuError('模版已经卸载');
        }
        if (D('Template')->delete(array('where' => array('theme' => $theme)))) {
            D('Template')->cleanCache();
            $this->tuSuccess('卸载成功', U('template/index'));
        }
    }
	
    public function defaults(){
        $theme = $this->_get('theme', 'htmlspecialchars');
        if (empty($theme)) {
            $this->tuError('请选择模版');
        }
        $datas = D('Template')->fetchAll();
        if (!$datas[$theme]) {
            $this->tuError('该模版不存在');
        }
        D('Template')->save(array('is_default' => 0), array('where' => array('is_default' => 1)));
        D('Template')->save(array('is_default' => 1), array('where' => array('theme' => $theme)));
        cookie('think_template', $theme, 864000);
        D('Template')->cleanCache();
        $this->tuSuccess('设置成功', U('template/index'));
    }
	
	//编辑模板
	public function edit(){
        $template_id = (int) $this->_get('template_id');
        if(empty($template_id)){
            $this->tuError('请选择模板');
        }
        if(!($detail = D('Template')->find($template_id))){
            $this->tuError('没有该模板');
        }
        if($this->isPost()){
            $price = (int) ($this->_post('price')*100);
            $intro = $this->_post('intro', 'htmlspecialchars');
			if(empty($intro)){
                $this->tuError('模板简介不能为空');
            }
			D('Template')->where(array('template_id'=>$template_id))->save(array('price' =>$price,'intro' => $intro));
            $this->tuSuccess('编辑操作成功', U('template/index'));
        }else{
            $this->assign('detail', $detail);
            $this->display();
        }
    }
	
	//设置模板
    public function settings($theme){
        if (empty($theme)) {
            $this->tuError('模板不存在');
        }
        $details = D('Templatesetting')->detail($theme);
        if ($this->isPost()) {
            $obj = D('Templatesetting');
            $data = $this->_post('data', false);
            $data = serialize($data);
            $datas = array();
            $datas['theme'] = $theme;
            $datas['setting'] = $data;
            if (!empty($details)) {
                if (false !== $obj->save($datas)) {
                    $obj->cleanCache();
                    $this->tuSuccess('设置成功', U('template/settings', array('theme' => $theme)));
                }
            } else {
                if ($obj->add($datas)) {
                    $obj->cleanCache();
                    $this->tuSuccess('设置成功', U('template/settings', array('theme' => $theme)));
                }
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('theme', $theme);
            $this->assign('datas', $details['setting']);
            $this->display($theme);
        }
    }
}