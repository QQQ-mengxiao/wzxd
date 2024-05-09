<?php
defined('In718Shop') or exit('Access Invalid!');
class ziti_addressControl extends BaseControl{
         /**
     * 新增配送地址
     */
    public function ziti_defaultOp(){
        $address_model=Model('address');
        $member_id =$_GET['member_id'];
        $address_id= $_GET['address_id'];//收货人姓名
         $data=array();
            $data['ziti_id'] = $address_id;
            $result=Model('member')->editMember(array('member_id'=>$member_id),$data);
        if($result){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$data);
            echo json_encode($res,320);
      }else{
            $message='fail';
            $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
            echo json_encode($res,320);
      }
    }
    // *获取收货地址
    // 多条地址
     public function ziti_listOp(){
           $model_daddress = Model('ziti_address');
           $member_id =$_GET['member_id'];
           $condition = array();
           //正常营业的地址
           $condition['state'] =  1;
           //地址搜索栏
           if(!empty($_GET['address_name'])){
              $condition['seller_name'] = array('like',"%".$_GET['address_name']."%");
          }
           // $condition['store_id'] =  $store_id;
           $address_list = $model_daddress->getAddressList($condition);
           $member_info = Model('member')->getMemberInfo(array('member_id'=>$member_id));
           $address_id=$member_info['ziti_id'];
           foreach ($address_list as $key => $value) {   
               if($member_info['ziti_id']==$value['address_id']){
                $address_list[$key]['is_default']=1;
               }else{
                 $address_list[$key]['is_default']=0;
               }          
            }
        if(!empty($address_list)){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$address_list);
            echo json_encode($res,320);
        }else{
                $message='fail';
                $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
                echo json_encode($res,320);
        }
    }
public function ziti_addressOp(){
           $model_daddress = Model('ziti_address');
           $member_id =$_GET['member_id'];
           $condition = array();
           //正常营业的地址
           $condition['state'] =  1;
           $fields='address_id,seller_name,area_id,city_id,area_info,latitude,longitude,state';
           $address_list = $model_daddress->getAddressList($condition,$fields);
           foreach ($address_list as $key => $value) {
            // var_dump($value['latitude']);die;
               $address_list[$key]['location']=array('lnt'=>(float)$value['longitude'] ,'lat'=>(float)$value['latitude']);
               unset($address_list[$key]['latitude']);
               unset($address_list[$key]['longitude']);
           }
        if(!empty($address_list)){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$address_list);
            echo json_encode($res,320);
        }else{
                $message='fail';
                $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
                echo json_encode($res,320);
        }
    }




}