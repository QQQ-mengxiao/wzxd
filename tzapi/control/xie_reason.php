<?php
defined('In718Shop') or exit('Access Invalid!');
class xie_reasonControl extends BaseControl{

    // *获取歇业原因
    public function xie_listOp(){
      $xie_reason = Model()->table('xie_reason')->order('sort ASC')->select();
      if(!empty($xie_reason)){
        $message='sucess';
        $res = array('code'=>'100' , 'message'=>$message,'data'=>$xie_reason);
        echo json_encode($res,320);
      }else{
        $message='fail';
        $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
        echo json_encode($res,320);
      }
    }

    //歇业状态详情接口
    public function xie_infoOp(){
      //团长ID
      $gl_id = $_GET['tz_id'];
      //自提点ID
      $address_id = $_GET['zi_address_id'];
      //查询该团长下该自提点是否正常营业
      $zi_address = Model()->table('ziti_address')->where(array('address_id'=>$address_id,'gl_id'=>$gl_id))->order('gl_id ASC')->find();
      if(!empty($zi_address)){
        $data['xie_time_start'] = date('Y-m-d',$zi_address['xie_time_start']);
        $data['xie_time_end'] = date('Y-m-d',$zi_address['xie_time_end']);
        $data['xie_reason'] = $zi_address['xie_reason'];
        //歇业申请状态：0未歇业 3待审核 1审核成功，2审核失败
        $data['xie_state'] = $zi_address['xie_state'];
        $message='自提点信息列表！';
        $res = array('code'=>'100' , 'message'=>$message,'data'=>$data );
        echo json_encode($res,320);
      }else{
        $message='信息传输异常！';
        $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
        echo json_encode($res,320);
      }
    }

    // *保存歇业申请
    public function xie_saveOp(){
      //团长ID
      $gl_id = $_POST['tz_id'];
      //自提地址ID
      $address_id = $_POST['zi_address_id'];
      $xie_time_start = strtotime($_POST['xie_time_start']);
      $xie_time_end = strtotime($_POST['xie_time_end']) + 60*60*24 -1;
      $xie_reason = $_POST['xie_reason_id'];
      //查询该团长下该自提点是否正常营业
      $zi_address = Model()->table('ziti_address')->where(array('address_id'=>$address_id,'gl_id'=>$gl_id, 'state'=>1))->order('gl_id ASC')->find();
      if(!empty($zi_address)){
        //更新歇业申请信息
        $result = Model()->table('ziti_address')->where(array('address_id'=>$address_id,'gl_id'=>$gl_id, 'state'=>1))->update(array('xie_time_start'=>$xie_time_start,'xie_time_end'=>$xie_time_end, 'xie_reason'=>$xie_reason, 'xie_state'=>3));
        if ($result){
            $message='提交成功，待审核！';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$xie_reason);
            echo json_encode($res,320);
        }
      }else{
        $message='自提点非正常营业状态，不可提交歇业申请！';
        $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
        echo json_encode($res,320);
      }
    }

    // *营业时间展示
    public function ying_timeOp(){
      //团长ID
      $gl_id = $_GET['tz_id'];
      //自提地址ID
      $address_id = $_GET['address_id'];
      $zi_address = Model()->table('ziti_address')->field('open_time_start,open_time_end')->where(array('address_id'=>$address_id,'gl_id'=>$gl_id))->order('address_id ASC')->find();
      if ($zi_address){
        $message='营业时间查询成功！';
        $data['tz_id'] = $gl_id;
        $data['address_id'] = $address_id;
        $data['open_time_start'] = $zi_address['open_time_start'];
        $data['open_time_end'] = $zi_address['open_time_end'];
        $res = array('code'=>'100' , 'message'=>$message,'data'=>$data);
        echo json_encode($res,320);
      }else{
        $message='营业时间查询失败！';
        $res = array('code'=>'300' , 'message'=>$message,'data'=>'');
        echo json_encode($res,320);
      }
    }
    
    // *保存营业时间
    public function ying_time_saveOp(){
      //团长ID
      $gl_id = $_POST['tz_id'];
      //自提地址ID
      $address_id = $_POST['address_id'];
      //营业时间(起)
      $open_time_start = $_POST['open_time_start'];
      //营业时间(终)
      $open_time_end = $_POST['open_time_end'];
      
      $result = Model()->table('ziti_address')->where(array('address_id'=>$address_id,'gl_id'=>$gl_id))->update(array('open_time_start'=>$open_time_start,'open_time_end'=>$open_time_end));
      if ($result){
        $message='营业时间提交成功！';
        $data['address_id'] = $address_id;
        $data['open_time_start'] = $open_time_start;
        $data['open_time_end'] = $open_time_end;
        $res = array('code'=>'100' , 'message'=>$message,'data'=>$data);
        echo json_encode($res,320);
      }else{
        $message='营业时间提交失败！';
        $data['address_id'] = $address_id;
        $data['open_time_start'] = $open_time_start;
        $data['open_time_end'] = $open_time_end;
        $res = array('code'=>'300' , 'message'=>$message,'data'=>$data);
        echo json_encode($res,320);
      }
    }
}