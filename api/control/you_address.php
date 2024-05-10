<?php
defined('In718Shop') or exit('Access Invalid!');
class you_addressControl extends BaseControl{
  //邮寄地址接口
    /**
     * 新增配送地址
     */
    public function testOp(){
        $res = array('code'=>'100' , 'message'=>'联通测试成功','data'=>'' );
        echo json_encode($res,320);
    }

    /**
     * 新增配送地址
     */
    public function addOp(){
        $member_id =$_GET['member_id'];
        $name= $_GET['name'];//收货人姓名
        $phone=$_GET['phone'];
        $is_default = 0;
        $id_card =  !empty($_GET['id_card'])?$_GET['id_card']:0;
        
        $address_detail = $_GET['address_detail'];
        $area_info =$_GET['area_info']; //河南,郑州市,管城回族区
        $address_array=explode(',', $area_info);
        if(!empty($address_array)){
            // 收货人所在省
            $prov =$address_array[0];
            //收货人所在市
            $scity = $address_array[1];
            // 收货人所在区
            $area=$address_array[2];
        }
        //查询省ID
        $prov_map['area_parent_id']=0; 
        $prov_map['area_name']=$prov;
        $prov_map['area_deep']=1;   
        $prov_list=Model('area')->field('area_id')->where($prov_map)->find();
        if(empty($prov_list)){
            $res = array('code'=>'300' , 'message'=>'省份异常','data'=>'' );
            echo json_encode($res,320);
            die;
        }
        $prov_id = $prov_list['area_id'];

        //查询市ID
        $city_map['area_parent_id']=$prov_id; 
        $city_map['area_name']=$scity; 
        $city_map['area_deep']=2;   
        $scity_list = Model('area')->field('area_id')->where($city_map)->find();
        if(empty($scity_list)){
            $res = array('code'=>'300' , 'message'=>'市区异常','data'=>'' );
            echo json_encode($res,320);
            die;
        }
        $city_id=$scity_list['area_id'];
        //查询区ID
        $city_map['area_parent_id']=$city_id; 
        $where['area_name']=$area; 
        $city_map['area_deep']=3;     
        $area_list=Model('area')->field('area_id')->where($where)->find();
        if(empty($area_list)){
            $res = array('code'=>'300' , 'message'=>'区县异常','data'=>'' );
            echo json_encode($res,320);
            die;
        }
        $area_id=$area_list['area_id'];
        $area_info=implode(' ', $address_array);
        $add = array(
            'member_id' => $member_id,
            'true_name' => $name,
            'id_card' => $id_card,
            'prov_id' => $prov_id,
            'city_id' => $city_id,
            'area_id' => $area_id,
            'area_info'=> $area_info,
            'address' => $address_detail,
            'mob_phone' => $phone,
            'is_default' => $is_default,
            );
        // if ($is_default==1) {
        // $address_model->editAddress(array('is_default'=>0),array('member_id'=>$member_id,'is_default'=>1));}
        $insert_id = Model("address_you")->table("address_you")->insert($add);
        // var_dump($insert_id);die;
        // $address=Model('address_you')->where(array('address_id' => $insert_id))->find();
        // $data=$address;
        if(!empty($insert_id)){
            // $return = preg_replace('#\s+#', ' ',trim($data['area_info']));
            // $arr_str=explode(",",$return);
            // $data['area_info']=implode(" ", $arr_str);
            $data['address_id']= $insert_id;
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
        $address_id=$_GET['address_id'];
        $member_id =$_GET['member_id'];
        $name= $_GET['name'];//收货人姓名
        $phone=$_GET['phone'];
        //$is_default =  !empty($_GET['is_default'])?$_GET['is_default']:0;
        $id_card =  !empty($_GET['id_card'])?$_GET['id_card']:0;
        $address_detail = $_GET['address_detail'];
        $area_info =$_GET['area_info']; //河南,郑州市,管城回族区
        $address_array=explode(',', $area_info);
        if(!empty($address_array)){
            // 收货人所在省
            $prov =$address_array[0];
            //收货人所在市
            $scity = $address_array[1];
            // 收货人所在区
            $area=$address_array[2];
        }
        //查询省ID
        $prov_map['area_parent_id']=0; 
        $prov_map['area_name']=$prov;
        $prov_map['area_deep']=1;   
        $prov_list=Model('area')->field('area_id')->where($prov_map)->find();
        if(empty($prov_list)){
            $res = array('code'=>'300' , 'message'=>'省份异常','data'=>'' );
            echo json_encode($res,320);
            die;
        }
        $prov_id = $prov_list['area_id'];

        //查询市ID
        $city_map['area_parent_id']=$prov_id; 
        $city_map['area_name']=$scity; 
        $city_map['area_deep']=2;   
        $scity_list = Model('area')->field('area_id')->where($city_map)->find();
        if(empty($scity_list)){
            $res = array('code'=>'300' , 'message'=>'市区异常','data'=>'' );
            echo json_encode($res,320);
            die;
        }
        $city_id=$scity_list['area_id'];
        //查询区ID
        $city_map['area_parent_id']=$city_id; 
        $where['area_name']=$area; 
        $city_map['area_deep']=3;     
        $area_list=Model('area')->field('area_id')->where($where)->find();
        if(empty($area_list)){
            $res = array('code'=>'300' , 'message'=>'区县异常','data'=>'' );
            echo json_encode($res,320);
            die;
        }
        $area_id=$area_list['area_id'];
        $area_info=implode(' ', $address_array);
        $update = array(
            'true_name' => $name,
            'id_card' => $id_card,
            'prov_id' => $prov_id,
            'city_id' => $city_id,
            'area_id' => $area_id,
            'area_info'=> $area_info,
            'address' => $address_detail,
            'mob_phone' => $phone,
            );

        $result = Model("address_you")->table("address_you")->where(array('address_id' => $address_id, 'member_id' => $member_id))->update($update);
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
              
        //设置用户下默认地址为0
        Model("address_you")->table("address_you")->where(array('member_id'=>$member_id,'is_default'=>1))->update(array('is_default'=>0));

        $condition['address_id'] = $address_id;
        $condition['member_id'] = $member_id;
        $result = Model("address_you")->table("address_you")->where($condition)->update(array('is_default'=>1));
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
           $result= Model("address_you")->table("address_you")->where(array('address_id'=>$address_id,'member_id'=> $member_id))->delete();
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
     public function address_you_listOp(){
        $member_id =$_GET['member_id'];
        $condition['member_id'] = $member_id; 
        $address_info = Model("address_you")->table("address_you")->where($condition)->select();
        foreach ($address_info as $k => $v) {
            $return = preg_replace('#\s+#', ' ',trim($v['area_info']));
            $arr_str=explode(",",$return);
            $address_info[$k]['area_info']=implode(" ", $arr_str);
        }
        if(!empty($address_info)){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$address_info);
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
        $address_info = Model("address_you")->table("address_you")->where(array('address_id'=>$address_id))->find();

        $return = preg_replace('#\s+#', ' ',trim($address_info['area_info']));
        $arr_str=explode(" ",$return);
        //$address_info['area_info']=implode(" ", $arr_str);
        $address_info['area_info'] = $arr_str;
        if(!empty($address_info)){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$address_info);
            echo json_encode($res,320);
        }else{
                $message='fail';
                $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
                echo json_encode($res,320);
        }
    }
     // 提交订单——展示默认邮寄地址接口
     public function get_youinfoOp(){
        $member_id =$_GET['member_id'];
        $address_info = Model("address_you")->table("address_you")->where(array('member_id'=>$member_id,'member_id'=>$member_id))->order('is_default desc,address_id desc')->find();
        

        $return = preg_replace('#\s+#', ' ',trim($address_info['area_info']));
        $arr_str=explode(" ",$return);
        $address_info['area_info']=implode(" ", $arr_str);
        if(!empty($address_info)){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$address_info);
            echo json_encode($res,320);
        }else{
                $message='fail';
                $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
                echo json_encode($res,320);
        }
    }

    //省市区地址的三级联动数据
    public function lian_addressOp(){

      //省信息
      $prov_condition['area_parent_id'] = 0;
      $prov_condition['area_deep'] = 1;
      $prov_list = Model('area')->where($prov_condition)->select();
      $data['provinceList'] = $prov_list;
      //市信息列表
      foreach ($prov_list as $prov) {
        $city_condition['area_parent_id'] = $prov['area_id'];
        $city_condition['area_deep'] = 2;
        $city_list[$prov['area_id']] = Model('area')->table('area')->field('*')->where($city_condition)->select();
        foreach ($city_list[$prov['area_id']] as $city_ids) {
          //市ID
          $city_id_array[] =  $city_ids["area_id"];
        }        
      }
      //区
      foreach ($city_id_array as $city_id) {
        $city_condition['area_parent_id'] = $city_id;
        $city_condition['area_deep'] = 3;
        $city_list[$city_id] = Model('area')->table('area')->field('*')->where($city_condition)->select();
      }
      $data['cityList'] = $city_list;
      $res = array('code'=>'100' , 'message'=>$message,'data'=> $data);
      echo json_encode($res,320);

    }
}