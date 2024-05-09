<?php
defined('In718Shop') or exit('Access Invalid!');
class storeControl extends BaseControl{
         /**
     * 新增配送地址
     */
    public function addOp(){
            $param = array();
            $member_id= $_POST['member_id'];
            $member_info = Model('member')->getMemberInfo(array('member_id'=>$member_id));
            $param['store_name'] = $_POST['company_name'];
            $param['seller_name'] = $member_info['member_name'];
            $param['store_name'] = $member_info['member_name'];
            $param['member_name'] = $member_info['member_name'];
            $param['company_name'] = $_POST['company_name'];//公司名称
            $param['company_address'] = $_POST['company_address'];//公司地址
            $param['company_address_detail'] = $_POST['company_address_detail'];//详细地址
            $param['company_phone'] = $_POST['company_phone'];//公司电话
            $param['contacts_name'] = $_POST['contacts_name'];//联系人
            $param['bank_account_name'] = $_POST['bank_account_name'];//收款人
            $param['bank_name'] = $_POST['bank_name'];//开户行
            $param['bank_account_number'] = $_POST['bank_account_number'];//开户行账号
            $param['joinin_state']=11;
             $model_store_joinin = Model('store_joinin');
            $joinin_info = $model_store_joinin->getOne(array('member_id' => $member_id));
            if(empty($joinin_info)) {
                $param['member_id'] = $member_id;
            $data=$model_store_joinin->save($param);
            } else {
            $data=$model_store_joinin->modify($param, array('member_id'=>$_SESSION['member_id']));
            }
        if($data){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$data);
            echo json_encode($res,320);
      }else{
            $message='fail';
            $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
            echo json_encode($res,320);
      }
    }

   








}