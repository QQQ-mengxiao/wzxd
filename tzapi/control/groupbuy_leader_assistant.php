<?php
defined('In718Shop') or exit('Access Invalid!');
class groupbuy_leader_assistantControl extends BaseControl
{
    /**
     * @api 添加团长助手
     * @method post
     * @param groupbuy_leader_id 团长ID
     * @param username 账号
     * @param password 密码
     * @param name 姓名
     * @param phone_number 电话号码
     * @param state 状态
     * @param remark 备注
     */
    public function addGroupbuyLeaderAssistantOp()
    {
        $groupbuy_leader_id = intval($_POST['groupbuy_leader_id']);

        if (!$groupbuy_leader_id) {

            die(json_encode(array('code' => '100', 'message' => '缺少团长ID', 'data' => ''), 320));

        }

        $model_groupbuy_leader = Model('groupbuy_leader');

        $model_groupbuy_leader_assistant = Model('groupbuy_leader_assistant');

        $groupbuy_leader_info = $model_groupbuy_leader->getGroupbuyLeaderInfo(['groupbuy_leader_id' => $groupbuy_leader_id]);

        if (!$groupbuy_leader_info) {

            die(json_encode(array('code' => '100', 'message' => '团长数据不存在', 'data' => ''), 320));

        }

        //获取团长对应的默认自提点id
        $groupbuy_leader_ziti_address_info = $model_groupbuy_leader->getGroupbuyLeaderAndZitiAddressInfo(['ziti_address.gl_id' => $groupbuy_leader_id, 'ziti_address.is_current' => 1], 'ziti_address.address_id');

        if (!$groupbuy_leader_ziti_address_info) {

            die(json_encode(array('code' => '100', 'message' => '默认自提点数据获取异常', 'data' => ''), 320));

        }

        $default_ziti_id = $groupbuy_leader_ziti_address_info['address_id'];

        //创建团长助手准备数据
        $username = $_POST['username'];

        if (!$username) {

            die(json_encode(array('code' => '100', 'message' => '团长助手登录名不能为空', 'data' => ''), 320));

        }

        $password = $_POST['password'];

        if (!$password) {

            die(json_encode(array('code' => '100', 'message' => '团长助手登录密码不能为空', 'data' => ''), 320));

        }

        //登录密码加密
        $password = md5(trim($password));

        $name = $_POST['name'];

        if (!$name) {

            die(json_encode(array('code' => '100', 'message' => '团长助手姓名不能为空', 'data' => ''), 320));

        }

        $phone_number = $_POST['phone_number'];

        if (!$phone_number) {

            die(json_encode(array('code' => '100', 'message' => '团长助手电话不能为空', 'data' => ''), 320));

        }

        $state = $_POST['state'];

        if ($state == '' || !in_array(intval($state), [0, 1])) {

            die(json_encode(array('code' => '100', 'message' => '团长助手状态值异常', 'data' => ''), 320));

        }

        $remark = $_POST['remark'];

        $groupbuy_leader_assistant = [
            'username' => $username,
            'password' => $password,
            'name' => $name,
            'phone_number' => $phone_number,
            'add_time' => TIMESTAMP,
            'gl_id' => $groupbuy_leader_id,
            'default_ziti_id' => $default_ziti_id,
            'auth' => 0,
            'state' => $state,
            'avatar' => '',
            'remark' => $remark,
        ];

        //添加团长助手
        $result = $model_groupbuy_leader_assistant->addGroupbuyLeaderAssistant($groupbuy_leader_assistant);

        if (!$result) {
            die(json_encode(array('code' => '100', 'message' => '团长助手新增失败', 'data' => ''), 320));
        }

        $groupbuy_leader_assistant_info = $model_groupbuy_leader_assistant->getGroupbuyLeaderAssistantInfo(['gl_assistant_id' => $result], 'gl_assistant_id,username,name,phone_number,add_time,gl_id,default_ziti_id,state,remark');

        die(json_encode(array('code' => '200', 'message' => '团长助手新增成功', 'data' => $groupbuy_leader_assistant_info), 320));

    }

    /**
     * @api 创建团长助手登录名
     * @method get
     * @param groupbuy_leader_id 团长ID
     */
    public function createUsernameOp()
    {
        $groupbuy_leader_id = intval($_GET['groupbuy_leader_id']);

        if (!$groupbuy_leader_id) {

            die(json_encode(array('code' => '100', 'message' => '缺少团长ID', 'data' => ''), 320));

        }

        $model_groupbuy_leader = Model('groupbuy_leader');

        $model_groupbuy_leader_assistant = Model('groupbuy_leader_assistant');

        $groupbuy_leader_info = $model_groupbuy_leader->getGroupbuyLeaderInfo(['groupbuy_leader_id' => $groupbuy_leader_id]);

        if (!$groupbuy_leader_info) {

            die(json_encode(array('code' => '100', 'message' => '团长数据不存在', 'data' => ''), 320));

        }

        //获取团长下所有团长助手个数
        $count_groupbuy_leader_assistant = $model_groupbuy_leader_assistant->getGroupbuyLeaderAssistantCount(['gl_id' => $groupbuy_leader_id]);

        //团长助手账号规则 团长ID+年月+该团长下的第几位助手
        $username = sprintf('%04d', $groupbuy_leader_id) . date("ym", time()) . sprintf('%03d', ($count_groupbuy_leader_assistant + 1));

        if (!$username) {

            die(json_encode(array('code' => '100', 'message' => '团长助手账号生成失败', 'data' => ''), 320));

        }

        die(json_encode(array('code' => '200', 'message' => '团长助手账号生成成功', 'data' => $username), 320));

    }

    /**
     * @api 删除团长助手
     * @method get
     * @param groupbuy_leader_assistant_id 团长助手ID
     */
    public function deleteGroupbuyLeaderAssistantOp()
    {
        $groupbuy_leader_assistant_id = $_GET['groupbuy_leader_assistant_id'];

        if (!$groupbuy_leader_assistant_id) {

            die(json_encode(array('code' => '100', 'message' => '缺少团长助手ID', 'data' => ''), 320));

        }

        $model_groupbuy_leader_assistant = Model('groupbuy_leader_assistant');

        $groupbuy_leader_assistant_info = $model_groupbuy_leader_assistant->getGroupbuyLeaderAssistantInfo(['gl_assistant_id' => $groupbuy_leader_assistant_id]);

        if (!$groupbuy_leader_assistant_info) {

            die(json_encode(array('code' => '100', 'message' => '团长助手数据不存在', 'data' => ''), 320));

        }

        $result = $model_groupbuy_leader_assistant->deleteGroupbuyLeaderAssistant(['gl_assistant_id' => $groupbuy_leader_assistant_id]);

        if (!$result) {

            die(json_encode(array('code' => '100', 'message' => '团长助手数据删除失败', 'data' => ''), 320));

        }

        die(json_encode(array('code' => '200', 'message' => '团长助手数据删除成功', 'data' => ''), 320));

    }

    /**
     * @api 获取所有团长助手信息列表
     * @method get
     * @param groupbuy_leader_id
     */
    public function getGroupbuyLeaderAssistantListOp()
    {
        $groupbuy_leader_id = intval($_GET['groupbuy_leader_id']);

        if (!$groupbuy_leader_id) {

            die(json_encode(array('code' => '100', 'message' => '缺少团长ID', 'data' => ''), 320));

        }

        $model_groupbuy_leader = Model('groupbuy_leader');

        $model_groupbuy_leader_assistant = Model('groupbuy_leader_assistant');

        $groupbuy_leader_info = $model_groupbuy_leader->getGroupbuyLeaderInfo(['groupbuy_leader_id' => $groupbuy_leader_id]);

        if (!$groupbuy_leader_info) {

            die(json_encode(array('code' => '100', 'message' => '团长数据不存在', 'data' => ''), 320));

        }

        $groupbuy_leader_assistant_list = $model_groupbuy_leader_assistant->getGroupbuyLeaderAssistantList(['gl_id' => $groupbuy_leader_id, 'state' => array('lt', 2)], 'gl_assistant_id,username,name,phone_number,add_time,gl_id,default_ziti_id,state,remark');

        if (!$groupbuy_leader_assistant_list) {

            die(json_encode(array('code' => '100', 'message' => '团长助手数据不存在', 'data' => ''), 320));

        }

        die(json_encode(array('code' => '200', 'message' => '团长助手列表查询成功', 'data' => $groupbuy_leader_assistant_list), 320));

    }

    /**
     * @api 获取单条团长助手信息
     * @method get
     * @param groupbuy_leader_assistant_id 团长助手ID
     */
    public function getGroupbuyLeaderAssistantInfoOp()
    {
        $groupbuy_leader_assistant_id = $_GET['groupbuy_leader_assistant_id'];

        if (!$groupbuy_leader_assistant_id) {

            die(json_encode(array('code' => '100', 'message' => '缺少团长助手ID', 'data' => ''), 320));

        }

        $model_groupbuy_leader_assistant = Model('groupbuy_leader_assistant');

        $groupbuy_leader_assistant_info = $model_groupbuy_leader_assistant->getGroupbuyLeaderAssistantInfo(['gl_assistant_id' => $groupbuy_leader_assistant_id], 'gl_assistant_id,username,name,phone_number,add_time,gl_id,default_ziti_id,state,avatar,remark');

        if (!$groupbuy_leader_assistant_info) {

            die(json_encode(array('code' => '100', 'message' => '团长助手数据不存在', 'data' => ''), 320));

        }

        $groupbuy_leader_assistant_info['add_time'] = date('Y-m-d H:i:s',$groupbuy_leader_assistant_info['add_time']);

        die(json_encode(array('code' => '200', 'message' => '团长助手数据获取成功', 'data' => $groupbuy_leader_assistant_info), 320));

    }

    /**
     * @api 编辑团长助手信息提交
     * @method post
     * @param groupbuy_leader_assistant_id 团长助手ID
     * @param password 密码
     * @param name 姓名
     * @param phone_number 电话号码
     * @param state 状态
     * @param remark 备注
     */
    public function editGroupbuyLeaderAssistantSaveOp()
    {
        $groupbuy_leader_assistant_id = $_POST['groupbuy_leader_assistant_id'];

        if (!$groupbuy_leader_assistant_id) {

            die(json_encode(array('code' => '100', 'message' => '缺少团长助手ID', 'data' => ''), 320));

        }

        $model_groupbuy_leader_assistant = Model('groupbuy_leader_assistant');

        $model_groupbuy_leader_assistant_info = $model_groupbuy_leader_assistant->getGroupbuyLeaderAssistantInfo(['gl_assistant_id' => $groupbuy_leader_assistant_id]);

        if (!$model_groupbuy_leader_assistant_info) {

            die(json_encode(array('code' => '100', 'message' => '团长助手数据不存在', 'data' => ''), 320));

        }

        $password = $_POST['password'];

        if (!$password) {

            die(json_encode(array('code' => '100', 'message' => '团长助手登录密码不能为空', 'data' => ''), 320));

        }

        //登录密码加密
        $password = md5(trim($password));

        $name = $_POST['name'];

        if (!$name) {

            die(json_encode(array('code' => '100', 'message' => '团长助手姓名不能为空', 'data' => ''), 320));

        }

        $phone_number = $_POST['phone_number'];

        if (!$phone_number) {

            die(json_encode(array('code' => '100', 'message' => '团长助手电话不能为空', 'data' => ''), 320));

        }

        $state = $_POST['state'];

        if ($state == '' || !in_array(intval($state), [0, 1])) {

            die(json_encode(array('code' => '100', 'message' => '团长助手状态值异常', 'data' => ''), 320));

        }

        $remark = $_POST['remark'];

        $groupbuy_leader_assistant_update = [
            'password' => $password,
            'name' => $name,
            'phone_number' => $phone_number,
            'state' => $state,
            'remark' => $remark,
        ];

        $result = $model_groupbuy_leader_assistant->editGroupbuyLeaderAssistant(['gl_assistant_id' => $groupbuy_leader_assistant_id], $groupbuy_leader_assistant_update);

        if (!$result) {

            die(json_encode(array('code' => '100', 'message' => '团长助手修改失败', 'data' => ''), 320));

        }

        die(json_encode(array('code' => '200', 'message' => '团长助手修改成功', 'data' => ''), 320));

    }

    /**
     * @api 团长助手获取所有自提点列表
     * @method get
     * @param groupbuy_leader_assistant_id
     */
    public function getGroupbuyLeaderAssistantZitiAddressListOp()
    {
        $groupbuy_leader_assistant_id = $_GET['groupbuy_leader_assistant_id'];

        if (!$groupbuy_leader_assistant_id) {

            die(json_encode(array('code' => '100', 'message' => '缺少团长助手ID', 'data' => ''), 320));

        }

        $model_groupbuy_leader_assistant = Model('groupbuy_leader_assistant');

        $modelGroupbyLeader = Model('groupbuy_leader');

        $groupbuy_leader_assistant_info = $model_groupbuy_leader_assistant->getGroupbuyLeaderAssistantInfo(['gl_assistant_id' => $groupbuy_leader_assistant_id], 'gl_id,default_ziti_id');

        if (!$groupbuy_leader_assistant_info) {

            die(json_encode(array('code' => '100', 'message' => '团长助手数据不存在', 'data' => ''), 320));

        }

        $groupbuy_leader_id = $groupbuy_leader_assistant_info['gl_id'];

        $default_ziti_id = $groupbuy_leader_assistant_info['default_ziti_id'];

        $condition['groupbuy_leader.groupbuy_leader_id'] = $groupbuy_leader_id;

        $groupbuy_leader_assistant_ziti_address_list = $modelGroupbyLeader->getGroupbuyLeaderAndZitiAddressList($condition, '', '', 0);

        if (!$groupbuy_leader_assistant_ziti_address_list) {

            die(json_encode(array('code' => '100', 'message' => '自提数据不存在', 'data' => ''), 320));

        }

        foreach ($groupbuy_leader_assistant_ziti_address_list as $key => $value) {

            //图片处理
            $groupbuy_leader_assistant_ziti_address_list[$key]['id_photo_front'] = $value['id_photo_front'] ? UPLOAD_SITE_URL . '/' . DIR_UPLOAD_GLID_FRONT . '/' . $value['groupbuy_leader_id'] . '/' . $value['id_photo_front'] : '';

            $groupbuy_leader_assistant_ziti_address_list[$key]['id_photo_back'] = $value['id_photo_back'] ? UPLOAD_SITE_URL . '/' . DIR_UPLOAD_GLID_BACK . '/' . $value['groupbuy_leader_id'] . '/' . $value['id_photo_back'] : '';

            $groupbuy_leader_assistant_ziti_address_list[$key]['wx_avatar'] = $value['wx_avatar'] ? UPLOAD_SITE_URL . '/' . ATTACH_TZAVATAR . '/' . $value['wx_avatar'] : '';

            $groupbuy_leader_assistant_ziti_address_list[$key]['ziti_photo'] = $value['ziti_photo'] ? UPLOAD_SITE_URL . '/' . DIR_UPLOAD_ZITI . '/' . $value['groupbuy_leader_id'] . '/' . $value['ziti_photo'] : '';

            if ($value['address_id'] == $default_ziti_id) {
                $groupbuy_leader_assistant_ziti_address_list[$key]['is_current'] = 1;
            } else {
                $groupbuy_leader_assistant_ziti_address_list[$key]['is_current'] = 0;
            }

        }

        die(json_encode(array('code' => '200', 'message' => '获取团长助手自提点列表成功', 'data' => $groupbuy_leader_assistant_ziti_address_list), 320));

    }

    /**
     * @api 团长助手切换自提点
     * @method get
     * @param groupbuy_leader_assistant_id
     * @param ziti_address_id
     */
    public function changeGroupbuyLeaderAssistantZitiAddressOp()
    {
        $groupbuy_leader_assistant_id = $_GET['groupbuy_leader_assistant_id'];

        if (!$groupbuy_leader_assistant_id) {

            die(json_encode(array('code' => '100', 'message' => '缺少团长助手ID', 'data' => ''), 320));

        }

        $ziti_address_id = $_GET['ziti_address_id'];

        if (!$ziti_address_id) {

            die(json_encode(array('code' => '100', 'message' => '缺少自提点ID', 'data' => ''), 320));

        }

        $model_groupbuy_leader_assistant = Model('groupbuy_leader_assistant');

        $groupbuy_leader_assistant_info = $model_groupbuy_leader_assistant->getGroupbuyLeaderAssistantInfo(['gl_assistant_id' => $groupbuy_leader_assistant_id]);

        if (!$groupbuy_leader_assistant_info) {

            die(json_encode(array('code' => '100', 'message' => '团长助手数据不存在', 'data' => ''), 320));

        }

        $groupbuy_leader_assistant_update = ['default_ziti_id' => $ziti_address_id];

        $result = $model_groupbuy_leader_assistant->editGroupbuyLeaderAssistant(['gl_assistant_id' => $groupbuy_leader_assistant_id], $groupbuy_leader_assistant_update);

        if (!$result) {

            die(json_encode(array('code' => '100', 'message' => '自提点数据切换失败', 'data' => ''), 320));

        }

        die(json_encode(array('code' => '200', 'message' => '自提点数据切换成功', 'data' => ''), 320));

    }


}
