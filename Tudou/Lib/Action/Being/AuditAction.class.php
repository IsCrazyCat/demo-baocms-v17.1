<?php
class AuditAction extends CommonAction
{
    private $create_fields = array('shop_id', 'photo', 'name', 'zhucehao', 'addr', 'end_date', 'zuzhidaima', 'user_name', 'pic', 'mobile', 'audit');
    private $edit_fields = array('shop_id', 'photo', 'name', 'zhucehao', 'addr', 'end_date', 'zuzhidaima', 'user_name', 'pic', 'mobile', 'audit');
    public function index()
    {
        $Audit = D('Audit');
        import('ORG.Util.Page');
        // 导入分页类 二开qq 120--585--022   www.juhucms.com
        $mapss = array('city_id' => $this->city_id);
        //查询城市ID为当前登录账户的ID
        $shop_city = D('Shop')->where($mapss)->order(array('shop_id' => 'desc'))->select();
        //查询所在城市的商家
        foreach ($shop_city as $val) {
            $cityids[$val['shop_id']] = $val['shop_id'];
            //对比shop_id
        }
        $maps = array();
        $maps['shop_id'] = array('in', $cityids);
        //取得当前商家ID，给下面的maps查
        $maps['closed'] = 0;
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $maps['name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $maps['cate_id'] = array('IN', D('Shopcate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $maps['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($audit = (int) $this->_param('audit')) {
            $maps['audit'] = $audit === 1 ? 1 : 0;
            $this->assign('audit', $audit);
        }
        $count = $Audit->where($maps)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Audit->where($maps)->order(array('audit' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = array();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->assign('cates', D('Shopcate')->fetchAll());
        $this->display();
        // 输出模板
    }
    public function edit($audit_id = 0)
    {
        if ($audit_id = (int) $audit_id) {
            //查询上级ID编辑处代码开始
            $shop_ids = D('Audit')->find($audit_id);
            $shop_id = $shop_ids['shop_id'];
            $city_ids = D('Shop')->find($shop_id);
            $citys = $city_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('audit/index'));
            }
            $obj = D('Audit');
            if (!($detail = $obj->find($audit_id))) {
                $this->tuError('请选择要编辑的商家认证');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['audit_id'] = $audit_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('audit/index'));
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
        $data['shop_id'] = (int) $data['shop_id'];
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
        return $data;
    }
    public function delete($audit_id = 0)
    {
        if (is_numeric($audit_id) && ($audit_id = (int) $audit_id)) {
            //查询上级ID编辑处代码开始
            $shop_ids = D('Audit')->find($audit_id);
            $shop_id = $shop_ids['shop_id'];
            $city_ids = D('Shop')->find($shop_id);
            $citys = $city_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('audit/index'));
            }
            $obj = D('Audit');
            $obj->save(array('audit_id' => $audit_id, 'closed' => 1));
            $shop_ids = D('Audit')->where(array('audit_id' => $audit_id))->find();
            $shop_id = $shop_ids['shop_id'];
            //审核商家
            $shop = D('Shop');
            $shop->save(array('shop_id' => $shop_id, 'is_renzheng' => 0));
            $this->tuSuccess('删除成功', U('audit/index'));
        } else {
            $audit_id = $this->_post('audit_id', false);
            if (is_array($audit_id)) {
                $obj = D('Audit');
                foreach ($audit_id as $id) {
                    $obj->save(array('audit_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('批量删除成功', U('audit/index'));
            }
            $this->tuError('请选择要删除的商家认证');
        }
    }
    public function audit($audit_id = 0)
    {
        if (is_numeric($audit_id) && ($audit_id = (int) $audit_id)) {
            //查询上级ID编辑处代码开始
            $shop_ids = D('Audit')->find($audit_id);
            $shop_id = $shop_ids['shop_id'];
            $city_ids = D('Shop')->find($shop_id);
            $citys = $city_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('audit/index'));
            }
            $obj = D('Audit');
            $obj->save(array('audit_id' => $audit_id, 'audit' => 1));
            $shop_ids = D('Audit')->where(array('audit_id' => $audit_id))->find();
            $shop_id = $shop_ids['shop_id'];
            //审核商家
            $shop = D('Shop');
            $shop->save(array('shop_id' => $shop_id, 'is_renzheng' => 1));
            $this->tuSuccess('审核成功', U('audit/index'));
        } else {
            $audit_id = $this->_post('audit_id', false);
            if (is_array($audit_id)) {
                $obj = D('Audit');
                foreach ($audit_id as $id) {
                    $obj->save(array('audit_id' => $id, 'audit' => 1));
                }
                $this->tuSuccess('审核成功', U('audit/index'));
            }
            $this->tuError('请选择要审核的商家认证');
        }
    }
}