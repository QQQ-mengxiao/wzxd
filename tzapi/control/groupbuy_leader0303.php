<?php
defined('In718Shop') or exit('Access Invalid!');
class groupbuy_leaderControl extends BaseControl
{

    /**
     * 申请团长-自提点照片上传
     */
    public function ziti_photo_uploadOp()
    {
        $groupbuy_leader_id = intval($_POST['groupbuy_leader_id']);

        $upload = new UploadFile();

        $dir = DIR_UPLOAD_ZITI . '/' . $groupbuy_leader_id;

        $result = $upload->set('default_dir', $dir);

        $upload->set('allow_type', array('jpg', 'jpeg', 'gif', 'png'));

        $result = $upload->upfile('ziti');

        if ($result) {

            $file_name = $upload->file_name;

            die(json_encode(array('code' => '200', 'message' => '自提点照片上传成功', 'data' => ['file_groupbuy_leader_id'=>$groupbuy_leader_id,'file_name'=>$file_name,'file_path'=>UPLOAD_SITE_URL . '/' . $dir . '/' . $file_name]),320));

        } else {

            die(json_encode(array('code' => '100', 'message' => '自提点照片上传失败', 'data' => ''),320));

        }

    }
    
    /**
     * 申请团长-身份证正面照片上传
     */
    public function id_photo_f_uploadOp()
    {
        $groupbuy_leader_id = intval($_POST['groupbuy_leader_id']);

        $upload = new UploadFile();

        $dir = DIR_UPLOAD_GLID_FRONT . '/' . $groupbuy_leader_id;

        $result = $upload->set('default_dir', $dir);

        $upload->set('allow_type', array('jpg', 'jpeg', 'gif', 'png'));

        $result = $upload->upfile('front');

        if ($result) {

            $file_name = $upload->file_name;
            
            die(json_encode(array('code' => '200', 'message' => '身份证正面照片上传成功', 'data' => ['file_groupbuy_leader_id'=>$groupbuy_leader_id,'file_name'=>$file_name,'file_path'=>UPLOAD_SITE_URL . '/' . $dir . '/' . $file_name]),320));

        } else {

            die(json_encode(array('code' => '100', 'message' => '身份证正面照片上传失败', 'data' => ''),320));

        }

    }

    /**
     * 申请团长-身份证反面照片上传
     */
    public function id_photo_b_uploadOp()
    {
        $groupbuy_leader_id = intval($_POST['groupbuy_leader_id']);

        $upload = new UploadFile();

        $dir = DIR_UPLOAD_GLID_BACK . '/' . $groupbuy_leader_id;

        $result = $upload->set('default_dir', $dir);

        $upload->set('allow_type', array('jpg', 'jpeg', 'gif', 'png'));

        $result = $upload->upfile('back');

        if ($result) {

            $file_name = $upload->file_name;

            die(json_encode(array('code' => '200', 'message' => '身份证反面照片上传成功', 'data' => ['file_groupbuy_leader_id'=>$groupbuy_leader_id,'file_name'=>$file_name,'file_path'=>UPLOAD_SITE_URL . '/' . $dir . '/' . $file_name]),320));

        } else {

            die(json_encode(array('code' => '100', 'message' => '身份证反面照片上传失败', 'data' => ''),320));

        }

    }

    /**
     * 申请团长
     */
    public function applyOp()
    {
        $groupbuy_leader_id = $_POST['groupbuy_leader_id'];

        $groupbuy_leader_info = Model('groupbuy_leader')->getGroupbuyLeaderInfo(['groupbuy_leader_id'=>$groupbuy_leader_id]);

        if(!$groupbuy_leader_info){

            die(json_encode(array('code' => '100', 'message' => '参数错误', 'data' => ''), 320));

        }

        $seller_name    = $_POST['seller_name'];//自提点名称

        if(!$seller_name){

            die(json_encode(array('code' => '100', 'message' => '自提点名称不能为空！', 'data' => ''), 320));

        }

        $area_info      = $_POST['area_info'];//自提点地址

        if(!$area_info){

            die(json_encode(array('code' => '100', 'message' => '自提点地址不能为空！', 'data' => ''), 320));

        }

        $address        = $_POST['address'];//具体门牌号

        if(!$address){

            die(json_encode(array('code' => '100', 'message' => '具体门牌号不能为空！', 'data' => ''), 320));

        }

        $ziti_photo     = $_POST['ziti_photo'];//自提点照片

        if(!$ziti_photo){

            die(json_encode(array('code' => '100', 'message' => '自提点照片不能为空！', 'data' => ''), 320));

        }

        $phone_num      = $_POST['phone_num'];//电话

        if(!$phone_num){

            die(json_encode(array('code' => '100', 'message' => '团长电话不能为空！', 'data' => ''), 320));

        }

        $id_photo_front = $_POST['id_photo_front'];//身份证正面照片

        if(!$id_photo_front){

            die(json_encode(array('code' => '100', 'message' => '团长正面身份证照片不能为空！', 'data' => ''), 320));

        }

        $id_photo_back  = $_POST['id_photo_back'];//身份证反面照片

        if(!$id_photo_back){

            die(json_encode(array('code' => '100', 'message' => '团长反面身份证照片不能为空！', 'data' => ''), 320));

        }

        $have_license   = $_POST['have_license'];//是否有营业执照

        //保存自提点信息
        $model_ziti_address = Model('ziti_address');

        $ziti_address_info = [
            'store_id'          => 4,
            'seller_name'       => $seller_name,
            'area_info'         => $area_info,
            'address'           => $address,
            'gl_id'             => $groupbuy_leader_id,
            'state'             => 0, //状态 0待审核 1正常营业，2歇业，3关闭
            'open_time_start'   => '08:00',
            'open_time_end'     => '22:00',
            'ziti_photo'        => $ziti_photo,
            'xie_state'         => 0,
            'add_time'          => TIMESTAMP
        ];

        $add_result = $model_ziti_address->addAddress($ziti_address_info);

        if(!$add_result){

            die(json_encode(array('code' => '100', 'message' => '申请失败【未生成自提点信息】', 'data' => ''), 320));
            
        }

        //修改团长信息
        $model_groupbuy_leader = Model('groupbuy_leader');
        
        $groupbuy_leader_info = [
            'phone_num'         => $phone_num,
            'id_photo_front'    => $id_photo_front,
            'id_photo_back'     => $id_photo_back,
            'have_license'      => $have_license
        ];

        $edit_result = $model_groupbuy_leader->editGroupbuyLeader(['groupbuy_leader_id'=>$groupbuy_leader_id],$groupbuy_leader_info);

        if(!$edit_result){

            die(json_encode(array('code' => '100', 'message' => '申请失败【团长信息更新失败】', 'data' => ''), 320));

        }

        die(json_encode(array('code' => '200', 'message' => '申请成功，请等待审核', 'data' => ''), 320));
        
    }

    public function editApplyOp(){

        $groupbuy_leader_id = $_GET['groupbuy_leader_id'];

        if(!$groupbuy_leader_id){

            die(json_encode(array('code' => '100', 'message' => '团长ID不能为空', 'data' => ''), 320));

        }

        $modelGroupbyLeader = Model('groupbuy_leader');

        $condition['groupbuy_leader.groupbuy_leader_id'] = $groupbuy_leader_id;

        $groupbuy_leader_info = $modelGroupbyLeader->getGroupbuyLeaderAndZitiAddressInfo($condition);

        $groupbuy_leader_info['ziti_photo'] = UPLOAD_SITE_URL . '/' . DIR_UPLOAD_ZITI . '/' . $groupbuy_leader_id . '/' . $groupbuy_leader_info['ziti_photo'];

        $groupbuy_leader_info['id_photo_front'] = UPLOAD_SITE_URL . '/' . DIR_UPLOAD_GLID_FRONT . '/' . $groupbuy_leader_id . '/' . $groupbuy_leader_info['id_photo_front'];

        $groupbuy_leader_info['wx_avatar'] = UPLOAD_SITE_URL.'/'.ATTACH_TZAVATAR.'/'.$groupbuy_leader_info['wx_avatar'];

        $groupbuy_leader_info['id_photo_back'] = UPLOAD_SITE_URL . '/' . DIR_UPLOAD_GLID_BACK . '/' . $groupbuy_leader_id . '/' . $groupbuy_leader_info['id_photo_back'];

        if(!$groupbuy_leader_info){
            die(json_encode(array('code' => '200', 'message' => '自提地址信息不存在', 'data' => ''), 320));
        }

        die(json_encode(array('code' => '200', 'message' => 'succ', 'data' => $groupbuy_leader_info), 320));

    }

    /**
     * 编辑
     */
    public function editApplySaveOp(){

        $groupbuy_leader_id = $_POST['groupbuy_leader_id'];

        if(!$groupbuy_leader_id){

            die(json_encode(array('code' => '100', 'message' => '团长ID不能为空', 'data' => ''), 320));

        }

        $modelGroupbyLeader = Model('groupbuy_leader');

        $condition['groupbuy_leader.groupbuy_leader_id'] = $groupbuy_leader_id;

        $groupbuy_leader_info = $modelGroupbyLeader->getGroupbuyLeaderAndZitiAddressInfo($condition);

        $seller_name    = $_POST['seller_name'];//自提点名称

        if(!$seller_name){

            die(json_encode(array('code' => '100', 'message' => '自提点名称不能为空！', 'data' => ''), 320));

        }

        $area_info      = $_POST['area_info'];//自提点地址

        if(!$area_info){

            die(json_encode(array('code' => '100', 'message' => '自提点地址不能为空！', 'data' => ''), 320));

        }

        $address        = $_POST['address'];//具体门牌号

        if(!$address){

            die(json_encode(array('code' => '100', 'message' => '具体门牌号不能为空！', 'data' => ''), 320));

        }

        $ziti_photo     = $_POST['ziti_photo'];//自提点照片

        if(!$ziti_photo){

            die(json_encode(array('code' => '100', 'message' => '自提点照片不能为空！', 'data' => ''), 320));

        }

        $phone_num      = $_POST['phone_num'];//电话

        if(!$phone_num){

            die(json_encode(array('code' => '100', 'message' => '团长电话不能为空！', 'data' => ''), 320));

        }

        $id_photo_front = $_POST['id_photo_front'];//身份证正面照片

        if(!$id_photo_front){

            die(json_encode(array('code' => '100', 'message' => '团长正面身份证照片不能为空！', 'data' => ''), 320));

        }

        $id_photo_back  = $_POST['id_photo_back'];//身份证反面照片

        if(!$id_photo_back){

            die(json_encode(array('code' => '100', 'message' => '团长反面身份证照片不能为空！', 'data' => ''), 320));

        }

        $have_license   = $_POST['have_license'];//是否有营业执照

        //保存自提点信息
        $model_ziti_address = Model('ziti_address');

        $ziti_address_info = [
            'store_id'          => 4,
            'seller_name'       => $seller_name,
            'area_info'         => $area_info,
            'address'           => $address,
            'gl_id'             => $groupbuy_leader_id,
            'state'             => 0, //状态 0待审核 1正常营业，2歇业，3关闭
            'open_time_start'   => '08:00',
            'open_time_end'     => '22:00',
            'ziti_photo'        => $ziti_photo,
            'xie_state'         => 0,
            'add_time'          => TIMESTAMP
        ];

        $add_result = $model_ziti_address->editAddress($ziti_address_info,['address_id'=>$groupbuy_leader_info['address_id']]);

        if(!$add_result){

            die(json_encode(array('code' => '100', 'message' => '修改信息失败【未成功修改地址信息】', 'data' => ''), 320));

        }

        //修改团长信息
        $model_groupbuy_leader = Model('groupbuy_leader');

        $groupbuy_leader_info = [
            'phone_num'         => $phone_num,
            'id_photo_front'    => $id_photo_front,
            'id_photo_back'     => $id_photo_back,
            'have_license'      => $have_license
        ];

        $edit_result = $model_groupbuy_leader->editGroupbuyLeader(['groupbuy_leader_id'=>$groupbuy_leader_id],$groupbuy_leader_info);

        if(!$edit_result){

            die(json_encode(array('code' => '100', 'message' => '修改信息失败【团长信息更新失败】', 'data' => ''), 320));

        }

        die(json_encode(array('code' => '200', 'message' => '修改信息成功，请等待审核', 'data' => ''), 320));

    }


    /**
     * 获取团长下所有自提点
     */
    public function getZitiAddressListByGroupbuyLeaderIDOp(){
        
        //传入团长id
        $groupbuy_leader_id = $_GET['groupbuy_leader_id'];

        if(!$groupbuy_leader_id){

            die(json_encode(array('code' => '100', 'message' => '参数异常', 'data' => ''), 320));

        }

        $modelGroupbyLeader = Model('groupbuy_leader');

        $condition['groupbuy_leader.groupbuy_leader_id'] = $groupbuy_leader_id;

        $groupbuy_leader_list = $modelGroupbyLeader->getGroupbuyLeaderAndZitiAddressList($condition);

        if($groupbuy_leader_list){

            foreach($groupbuy_leader_list as $key=>$value){

                //图片处理
                $groupbuy_leader_list[$key]['id_photo_front']     = $value['id_photo_front'] ? UPLOAD_SITE_URL.'/'.DIR_UPLOAD_GLID_FRONT.'/'.$value['groupbuy_leader_id'].'/'.$value['id_photo_front'] : '';

                $groupbuy_leader_list[$key]['id_photo_back']      = $value['id_photo_back'] ? UPLOAD_SITE_URL.'/'.DIR_UPLOAD_GLID_BACK.'/'.$value['groupbuy_leader_id'].'/'.$value['id_photo_back'] : '';

                $groupbuy_leader_list[$key]['wx_avatar']          = $value['wx_avatar'] ? UPLOAD_SITE_URL.'/'.ATTACH_TZAVATAR.'/'.$value['wx_avatar'] : '';

                $groupbuy_leader_list[$key]['ziti_photo']         = $value['ziti_photo'] ? UPLOAD_SITE_URL.'/'.DIR_UPLOAD_ZITI.'/'.$value['groupbuy_leader_id'].'/'.$value['ziti_photo'] : '';

            }

        }

        if(!$groupbuy_leader_list){

            die(json_encode(array('code' => '100', 'message' => '获取团长自提点列表失败', 'data' => ''), 320));

        }

        die(json_encode(array('code' => '200', 'message' => '获取团长自提点列表成功', 'data' => $groupbuy_leader_list), 320));

    }

    /**
     * 团长切换自提点
     */
    public function changeZitiAddressOp(){

        //获取团长id以及提交的自提点id
        $groupbuy_leader_id = $_GET['groupbuy_leader_id'];

        $ziti_address_id = $_GET['ziti_address_id'];

        if(!$groupbuy_leader_id || !$ziti_address_id){
            
            die(json_encode(array('code' => '100', 'message' => '参数异常', 'data' => ''), 320));

        }

        $condition['groupbuy_leader.groupbuy_leader_id'] = $groupbuy_leader_id;
        
        $condition['ziti_address.address_id'] = $ziti_address_id;

        $modelGroupbyLeader = Model('groupbuy_leader');

        $groupbuy_leader_info = $modelGroupbyLeader->getGroupbuyLeaderAndZitiAddressInfo($condition);

        if(!$groupbuy_leader_info){

            die(json_encode(array('code' => '100', 'message' => '该自提点不存在', 'data' => ''), 320));

        }

        //图片处理
        $groupbuy_leader_info['id_photo_front']     = $groupbuy_leader_info['id_photo_front'] ? UPLOAD_SITE_URL.'/'.DIR_UPLOAD_GLID_FRONT.'/'.$groupbuy_leader_info['groupbuy_leader_id'].'/'.$groupbuy_leader_info['id_photo_front'] : '';

        $groupbuy_leader_info['id_photo_back']      = $groupbuy_leader_info['id_photo_back'] ? UPLOAD_SITE_URL.'/'.DIR_UPLOAD_GLID_BACK.'/'.$groupbuy_leader_info['groupbuy_leader_id'].'/'.$groupbuy_leader_info['id_photo_back'] : '';

        $groupbuy_leader_info['wx_avatar']          = $groupbuy_leader_info['wx_avatar'] ? UPLOAD_SITE_URL.'/'.ATTACH_TZAVATAR.'/'.$groupbuy_leader_info['wx_avatar'] : '';

        $groupbuy_leader_info['ziti_photo'] = $groupbuy_leader_info['ziti_photo'] ? UPLOAD_SITE_URL.'/'.DIR_UPLOAD_ZITI.'/'.$groupbuy_leader_info['groupbuy_leader_id'].'/'.$groupbuy_leader_info['ziti_photo'] : '';

        die(json_encode(array('code' => '200', 'message' => '自提点获取成功', 'data' => $groupbuy_leader_info), 320));

    }
    
}
