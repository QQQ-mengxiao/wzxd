<?php
defined('In718Shop') or exit('Access Invalid!');
class pointsControl extends BaseControl{
      //每页显示商品数
    const PAGESIZE = 10;
    //页数
    const PAGENUM = 1;
    
    /* 积分明细
    */
    public function points_viewOp(){
      $condition_arr = array();
      $condition_arr['pl_memberid'] = $_GET['member_id'];
       //查询会员信息
      $obj_member = Model('member');
      $member_id = intval($_GET['member_id']);
      $member_info = $obj_member->getMemberInfo(array('member_id'=>$member_id));
      $data['member_points']=$member_info['member_points'];
      if ($_GET['stage']==2){
        $condition_arr['pl_stage'] = 'app';
        $points_model = Model('points');
        $list_log = $points_model->getPointsLogList($condition_arr,'','*','');
        if(!empty($list_log)){
          foreach($list_log as $key=>$value){ 
            $list_log[$key]['pl_stage'] = $this->insertarr($value['pl_stage']);
            $list_log[$key]['pl_addtime'] = date('Y-m-d',$value['pl_addtime']);
            $list_log[$key]['pl_stage']=$list_log[$key]['pl_stage']. '  '.$list_log[$key]['pl_addtime'];
          }
        }
      }else{
        $points_model = Model('points');
        $list_log = $points_model->getPointsLogList($condition_arr,'','*','');
        if(!empty($list_log)){
          foreach($list_log as $key=>$value){ 
            $list_log[$key]['pl_stage'] = $this->insertarr($value['pl_stage']);
            $list_log[$key]['pl_addtime'] = date('Y-m-d',$value['pl_addtime']);
            $list_log[$key]['pl_stage']=$list_log[$key]['pl_stage']. '  '.$list_log[$key]['pl_addtime'];
             if($value['pl_stage']=='app'){
              unset($list_log[$key]);
          }
          }
        }
         $list_log =array_values($list_log);
      }
       $totaldata = count($list_log);
           // var_dump( $totaldata);die;
        $totalpage =ceil( $totaldata/self::PAGESIZE);
          $data['totalpage'] = $totalpage;
        if (!empty($_GET['pagecount'])) {
            $pagecount = $_GET['pagecount'];
        } else {
            $pagecount = self::PAGENUM;
        }
         //当前页码
        $data['pagecount']=$pagecount;
        $baifenbi= 1/C('points_orderrate')* 100;
        $ruler='成功注册会员：增加'. C('points_reg').'积分，购物并付款成功后将获得订单总价'.$baifenbi.'%(最高限额不超过'.C('points_ordermax').')积分。如订单发生退款、退货等问题时，积分将不予退还。';
        $data['ruler'] = $ruler;
      if($list_log){
              $list_log = array_slice($list_log, ($pagecount - 1) * self::PAGESIZE,  self::PAGESIZE);
              $data['list_log'] = $list_log;
              $message='sucess';
              $res = array('code'=>'100' , 'message'=>$message,'data'=>$data);
              echo json_encode($res,320);
          }else{
              $message='fail';
              $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
              echo json_encode($res,320);
          }
    }
   
      private function insertarr($stage) { 
    switch ($stage){
      case 'regist':
        $insertarr = '注册会员';
        break;
      case 'login':
         $insertarr = '会员登录';
        break;
      case 'comments':
        $insertarr = '评论商品';
        break;
      case 'order':
        $insertarr = '购物消费';
        break;
      case 'pointorder':
        $insertarr = '兑换礼品';
        break;            
      case 'signin':
        $insertarr = '会员签到';
        break;
        case 'app':
        $insertarr = '积分兑换';
        break;
       case 'qiandao':
        $insertarr = '会员签到';
        break;
    }
    return $insertarr;
  }
}