<?php
defined('In718Shop') or exit('Access Invalid!');
class qiandaoControl extends BaseControl{

      /*签到
     */
    public function indexOp(){

        $date1 = getdate(time());
        // $date11 = getdate(strtotime('2014-01-01'));
        // var_dump($date1 );die;
        $member_id = $_GET['member_id'];
        $member_info = Model('member')->getMemberInfo(array('member_id'=>$member_id));
        $qiandao_info=Model('qiandao')->getqiandaoInfo(array('member_id'=> $member_id));
        $model_qd_ruler= Model('qd_ruler');
     // 
        if(empty($qiandao_info)){
            $addary=array();
            $addary['member_id']=$member_id;
            $addary['num']=1;
            $addary['last_time']=time();
            $addary['update_time']=time();
            $result=Model('qiandao')->addqiandao($addary);
            $condition=array();
            $condition['id']=1;
            $qd_ruler= $model_qd_ruler->getqd_rulerInfo($condition);
            // var_dump($qd_ruler);die;
            $ponits=$qd_ruler['points'];
            $exp=$qd_ruler['exp'];
           
        }else{
            $date2 = getdate( $qiandao_info['last_time']);
            $upqiandao_array=array();
            $where=array();
            $where['id']=1;
            $qd_ruler= $model_qd_ruler->getqd_rulerInfo($where);
               //是否同一天，及一天只一次
            if($this->isDiffDays($date1,$date2)){
               if($date1['mday']==1){//是否为每月的一号
                    $upqiandao_array['num'] = 1;
                    $ponits=$qd_ruler['points'];
                    $exp=$qd_ruler['exp'];
               }else{
                    //是否相连
                    $condition=array();
                    $qd_rulerlist= $model_qd_ruler->getqd_rulerList($condition);
                    foreach ($qd_rulerlist as $key => $value) {
                        $mpoints_days[]= $value['days'];
                    }
                    //日常积分
                    $where=array();
                    $where['id']=1;
                    $qd_ruler1= $model_qd_ruler->getqd_rulerList($where);
                    if($this->isStreakDays($date1,$date2)){
                        $upqiandao_array['num'] = array('exp','num + 1');
                        //判断为多送的哪儿一天
                        if(in_array($qiandao_info['num'], $mpoints_days)){
                            foreach ($qd_rulerlist as $k => $v) {
                              if($v['num']==$qiandao_info['num'])
                                $ponits=$v['points'];
                                $exp=$v['exp'];
                            }
                        }else{
                           $ponits=$qd_ruler['points'];
                           $exp=$qd_ruler['exp'];
                        }
                    }else{
                        $upqiandao_array['num'] = 1;
                        $ponits=$qd_ruler['points'];
                        $exp=$qd_ruler['exp'];
                    }
               } 
            $upqiandao_array['last_time']=time();
            $upqiandao_array['update_time']=time();
            $result=Model('qiandao')->editqiandao($upqiandao_array,$member_id);
            }else{
                $message='今天已经签到过';
                $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
                echo json_encode($res,320);exit();
            }   
        }
        if($result){
            //新增日志
            $value_array = array();
            $value_array['pl_memberid'] = $member_id;
            $value_array['pl_membername'] = $member_info['member_name'];
            $value_array['pl_points'] = $ponits;
            $value_array['pl_addtime'] = time();
            $value_array['pl_desc'] ='连续签到送积分';
            $value_array['pl_stage'] = 'qiandao'; 
            $result = Model('points')->addPointsLog($value_array);
            //新增日志
            $value_array2 = array();
            $value_array2['exp_memberid'] = $member_id;
            $value_array2['exp_membername'] =$member_info['member_name'];
            $value_array2['exp_points'] = $exp;
            $value_array2['exp_addtime'] = time();
            $value_array2['exp_desc'] = '连续签到送经验';
            $value_array2['exp_stage'] ='qiandao'; 
            $result = Model('exppoints')->addExppointsLog($value_array2);

            $obj_member = Model('member');
            $upmember_array = array();
            $upmember_array['member_points'] = array('exp','member_points+'.$ponits);
            $upmember_array['member_exppoints'] = array('exp','member_exppoints+'.$exp);
            // var_dump($ponits);die;
            $result=$obj_member->editMember(array('member_id'=>$member_id),$upmember_array);
            if($result){
                $message='签到成功';
                $res = array('code'=>'100' , 'message'=>$message,'data'=>$result);
                echo json_encode($res,320);

          }else{
                $message='签到失败';
                $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
                echo json_encode($res,320);
          }
        }else{
                $message='签到失败';
                $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
                echo json_encode($res,320);
        }  
        
    }

    /*连续签到的时间
     */
    public function daysOp(){
        $member_id = $_GET['member_id'];
        $member_info = Model('member')->getMemberInfo(array('member_id'=>$member_id));
        $qiandao_info=Model('qiandao')->getqiandaoInfo(array('member_id'=> $member_id));
        $model_qd_ruler= Model('qd_ruler');
         if($qiandao_info){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$qiandao_info['num']);
            echo json_encode($res,320);

          }else{
            $message='fail';
            $res = array('code'=>'200' , 'message'=>$message,'data'=>0 );
            echo json_encode($res,320);
          }
        }
    //判断两天是否相连
    private function isStreakDays($last_date,$this_date){

        if(($last_date['year']===$this_date['year'])&&($this_date['yday']-$last_date['yday']===1)){
            return TURE;
        }elseif(($this_date['year']-$last_date['year']===1)&&($last_date['mon']-$this_date['mon']=11)&&($last_date['mday']-$this_date['mday']===30)){
            return TURE;
        }else{
            return FALSE;
        }
    }
    //判断两天是否是同一天
    private function isDiffDays($last_date,$this_date){

        if(($last_date['year']===$this_date['year'])&&($this_date['yday']===$last_date['yday'])){
            return FALSE;
        }else{
            return TRUE;
        }
    }




}