<?php
class AuditAction extends CommonAction
{
    private $create_fields = array('shop_id', 'photo', 'name', 'zhucehao', 'addr', 'end_date', 'zuzhidaima', 'user_name', 'pic', 'mobile', 'audit');
    private $edit_fields = array('shop_id', 'photo', 'name', 'zhucehao', 'addr', 'end_date', 'zuzhidaima', 'user_name', 'pic', 'mobile', 'audit');
    public function index()
    {
        $shop_audit = D('Audit')->where('shop_id =' . $this->shop_id)->find();
        $this->assign('shop_audit', $shop_audit);
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Audit');
            if ($obj->add($data)) {
                $this->tuSuccess('添加成功', U('audit/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['shop_id'] = $this->shop_id;
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传营业执照');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('图片格式不正确');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->tuError('企业名称不能为空');
        }
        $data['zhucehao'] = htmlspecialchars($data['zhucehao']);
        if (empty($data['zhucehao'])) {
            $this->tuError('营业执照注册号不能为空');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->tuError('营业地址不能为空');
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->tuError('到期时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->tuError('到期时间格式不正确');
        }
        $data['zuzhidaima'] = htmlspecialchars($data['zuzhidaima']);
        if (empty($data['zuzhidaima'])) {
            $this->tuError('组织机构代码证为空');
        }
        $data['user_name'] = htmlspecialchars($data['user_name']);
        if (empty($data['user_name'])) {
            $this->tuError('员工姓名为空');
        }
        $data['pic'] = htmlspecialchars($data['pic']);
        if (empty($data['pic'])) {
            $this->tuError('请上传员工身份证');
        }
        if (!isImage($data['pic'])) {
            $this->tuError('员工身份证图片格式不正确');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->tuError('员工手机不能为空');
        }
        if (!isMobile($data['mobile'])) {
            $this->tuError('员工手机格式不正确');
        }
        $data['audit'] = 0;
        //默认不通过
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($audit_id = 0)
    {
        if ($audit_id = (int) $audit_id) {
            $obj = D('Audit');
            if (!($detail = $obj->find($audit_id))) {
                $this->tuError('请选择要编辑的商家认证');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->tuError('请不要操作别人的认证');
            }
            if ($detail['closed'] != 0) {
                $this->tuError('该认证已被删除');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['audit_id'] = $audit_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('编辑操作成功', U('audit/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的商家认证');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['audit_id'] = (int) $data['audit_id'];
        $data['shop_id'] = $this->shop_id;
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传营业执照');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('图片格式不正确');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->tuError('企业名称不能为空');
        }
        $data['zhucehao'] = htmlspecialchars($data['zhucehao']);
        if (empty($data['zhucehao'])) {
            $this->tuError('营业执照注册号不能为空');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->tuError('营业地址不能为空');
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->tuError('到期时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->tuError('到期时间格式不正确');
        }
        $data['zuzhidaima'] = htmlspecialchars($data['zuzhidaima']);
        if (empty($data['zuzhidaima'])) {
            $this->tuError('组织机构代码证为空');
        }
        $data['user_name'] = htmlspecialchars($data['user_name']);
        if (empty($data['user_name'])) {
            $this->tuError('员工姓名为空');
        }
        $data['pic'] = htmlspecialchars($data['pic']);
        if (empty($data['pic'])) {
            $this->tuError('请上传员工身份证');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('员工身份证图片格式不正确');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->tuError('员工手机不能为空');
        }
        if (!isMobile($data['mobile'])) {
            $this->tuError('员工手机格式不正确');
        }
        return $data;
    }
}