<?php
defined('In718Shop') or exit('Access Invalid!');
class AddressControl extends BaseControl{
         /**
     * 新增配送地址
     */
    public function addOp(){
        $address_model=Model('address');
        $member_id =$_GET['member_id'];
        $name= $_GET['name'];//收货人姓名
        $address_detail = $_GET['address_detail'];
        $phone=$_GET['phone'];
        $area_info =$_GET['area_info'];
        $is_default =1;
        $address_array=explode(',', $area_info);
        if(!empty($address_array)){
                 // 收货人所在省
                 $prov =$address_array[0];
                 //收货人所在市
                 $scity = $address_array[1];
                   // 收货人所在区
                  $area=$address_array[2];
             }
        $map=array();
        $map['area_name']=$scity;    
        $scity_list=Model('area')->where($map)->find();
        // var_dump($scity_list);die;
        $city_id=$scity_list['area_id'];
        $where=array();
        $where['area_name']=$area;    
        $area_list=Model('area')->where($where)->find();
        $area_id=$area_list['area_id'];
        // var_dump($area);die;
        $area_info=implode(' ', $address_array);
        $add = array(
            'member_id' => $member_id,
            'true_name' => $name,
            'area_id' => $area_id,
            'city_id' => $city_id,
            'area_info'=> $area_info,
            'address' => $address_detail,
            'tel_phone' => '',
            'mob_phone' => $phone,
            'is_default' => $is_default,
            );
        // var_dump($add);die;
        if ($is_default==1) {
        $address_model->editAddress(array('is_default'=>0),array('member_id'=>$member_id,'is_default'=>1));}
        $insert_id =  $address_model->addAddress($add);
        // var_dump($insert_id);die;
        $address=Model('address')->where(array('address_id' => $insert_id))->find();
        $data=$address;
        if(!empty($data)){
            $return = preg_replace('#\s+#', ' ',trim($data['area_info']));
            $arr_str=explode(" ",$return);
            $data['area_info']=implode(" ", $arr_str);
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$data);
            echo json_encode($res,320);
      }else{
            $message='fail';
            $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
            echo json_encode($res,320);
      }
    }

   /**
     * 编辑配送地址
     */
    public function editOp(){
        $address_model= Model('address');
        $member_id =$_GET['member_id'];
        $address_id=$_GET['address_id'];
        $name= $_GET['name'];//收货人姓名
        $address_detail = $_GET['address_detail'];
        $phone=$_GET['phone'];
        $area_info =$_GET['area_info'];
        $address_array=explode(',', $area_info);
        if(!empty($address_array)){
                 // 收货人所在省
                 $prov =$address_array[0];
                 //收货人所在市
                 $scity = $address_array[1];
                   // 收货人所在区
                  $area=$address_array[2];
             }
        $map=array();
        $map['area_name']=$scity;    
        $scity_list=Model('area')->where($map)->find();
        $city_id=$scity_list['area_id'];
        $where=array();
        $where['area_name']=$area;    
        $area_list=Model('area')->where($where)->find();
        $area_id=$area_list['area_id'];
        // var_dump($area);die;
        $area_info=implode(' ', $address_array);
        $update = array(
            'true_name' => $name,
            'area_id' => $area_id,
            'city_id' => $city_id,
            'area_info'=> $area_info,
            'address' => $address_detail,
            'tel_phone' => '',
            'mob_phone' => $phone,
            );
         $result = $address_model->editAddress($update,array('member_id' => $member_id, 'address_id' => $address_id));
        if($result){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$result);
            echo json_encode($res,320);
        }else{
            $message='fail';
            $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
            echo json_encode($res,320);
        }
     }
     /**
     * 设置默认地址
     */
    public function set_defaultOp(){
         $address_id = $_GET['address_id'];
         $member_id =$_GET['member_id'];
        $address_model = Model('address');
        $where = array();
        $where['is_default'] = 1;
        $condition = array();
        $condition['address_id'] = $address_id;
        $condition['member_id'] = $member_id;
        $address_model->editAddress(array('is_default'=>0),array('member_id'=>$member_id,'is_default'=>1));
        $result = $address_model->editAddress($where,$condition);
        if($result){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$result);
            echo json_encode($res,320);
        }else{
            $message='fail';
            $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
            echo json_encode($res,320);
      }
    }
     /**
     * 删除地址
     */
    public function delOp(){
          $address_id = $_GET['address_id'];
         $member_id =$_GET['member_id'];
        $address_model = Model('address');
        //如果传入ID 则删除再查询
        if ($address_id > 0) {
           $result= $address_model->delAddress(array('address_id'=>$address_id,'member_id'=> $member_id));
        }
        if($result){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$result);
            echo json_encode($res,320);
      }else{
            $message='fail';
            $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
            echo json_encode($res,320);
      }
    }
    // *获取收货地址
    // 多条地址
     public function getinfoOp(){
        $member_id =$_GET['member_id'];
        $condition=array();
                $condition['member_id']=$member_id; 
                $address_info = Model('address')->where($condition)->select();
                $data=$address_info;
                foreach ($data as $k => $v) {
                    $return = preg_replace('#\s+#', ' ',trim($v['area_info']));
                    $arr_str=explode(" ",$return);
                    $data[$k]['area_info']=implode(" ", $arr_str);
                }
        if(!empty($data)){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$data);
            echo json_encode($res,320);
        }else{
                $message='fail';
                $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
                echo json_encode($res,320);
        }
    }
    // 获取单条
     public function getoneinfoOp(){
        $address_id =$_GET['address_id']; 
                 $address_info = Model('address')->getOneAddress($address_id);
                $data=$address_info;
                // var_dump($data);die;
                    $return = preg_replace('#\s+#', ' ',trim($data['area_info']));
                    $arr_str=explode(" ",$return);
                    $data['area_info']=implode(" ", $arr_str);
        if(!empty($address_info)){
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