<?php
defined('In718Shop') or exit('Access Invalid!');
class authControl  extends BaseControl{

    /* 认证服务
    */
    public function indexOp(){
        $member_id = intval($_POST['member_id']);
        
        $member=Model('member')->getMemberInfoByID($member_id);
        if ($member['member_verify']==20) {
            $res = array('code'=>'300' , 'message'=>'认证信息正在审核，请耐心等待','data'=>null);
            echo json_encode($res,320);exit();
        }
        $member_truename = $_POST['member_truename'];
        $ID_card = $_POST['ID_card'];
        $ID_card_photo = $_POST['ID_card_photo'];
        $member_mobile = $_POST['member_mobile'];
        
        if (empty($member_id)) {
            $res = array('code'=>'300' , 'message'=>'用户id不能为空','data'=>null);
            echo json_encode($res,320);
        }elseif (empty($member_truename)) {
            $res = array('code'=>'300' , 'message'=>'真实姓名不能为空','data'=>null);
            echo json_encode($res,320);
        }elseif (empty($ID_card)) {
            $res = array('code'=>'300' , 'message'=>'身份证号码不能为空','data'=>null);
            echo json_encode($res,320);
        }elseif (empty($ID_card_photo)) {
            $res = array('code'=>'300' , 'message'=>'请上传身份证正面照','data'=>null);
            echo json_encode($res,320);
        }else{
            $data=array();
            $data['member_id'] = $member_id;
            $data['member_truename'] =$member_truename;
            $data['ID_card'] = $ID_card;
            $data['ID_card_photo'] =$ID_card_photo;
            $data['member_mobile'] =$member_mobile;
            $data['member_verify'] =20;
            
            $result=Model('member')->editMember(array('member_id'=>$member_id),$data);
            if ($result) {
               echo $this->returnMsg(200, '资料上传成功，请耐心等待审核', null);
            }else{
                echo $this->returnMsg(400, '资料上传失败，请联系管理员', null);
            }
        }
    }
    /*
    封装认证图片上传
    */
    public function pic_uploadOp(){
        // define('IDCARD_IMAGES_XCX',UPLOAD_SITE_URL.'/'.ATTACH_MOBILE.'/idcard/');
        // define('UPLOAD_SITE_URL',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");
        // define('DIR_UPLOAD_IDCARD',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");
        $member_id = intval($_POST['member_id']);
        $type = intval($_POST['type']);//方便接口备注类型信息
        $upload = new UploadFile();
        $result =$upload->set('default_dir',DIR_UPLOAD_IDCARD);
        $result = $upload->upfile('idcard');
        if ($result){
            $_POST['pic'] = $upload->file_name;
        }else {
            echo $this->returnMsg(400, '图片上传失败', null);exit();
        }
        if ($result){
            $data = array();
            $data['file_member_id'] = $member_id;
            $data['file_name'] = $_POST['pic'];

            $data['file_path'] =UPLOAD_SITE_URL.'/'.DIR_UPLOAD_IDCARD.'/'.$_POST['pic'];
            if (!empty($type)) {
                $data['type'] = $type;
            }
            /**
             * 整理为json格式
             */
            echo $this->returnMsg(200, '图片上传成功', $data);
        }

    }
}