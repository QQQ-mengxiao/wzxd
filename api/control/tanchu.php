<?php
/**
 *弹出框
 *
 *
 *
 ***/
defined('In718Shop') or exit('Access Invalid!');
class tanchuControl  extends BaseControl{

    /* 链接测试
    */
    public function testOp(){
        echo $this->returnMsg(10000, '请求成功！', '');exit;
    }
    /* 自动更新弹出框状态（每分钟的时间任务）
    */
    public function test1Op(){
        $condition = array();
        $condition['is_open'] = 0;
        $tanchu_list = Model('tanchu')->table('tanchu')->where($condition)->order("acs asc")->select();
        foreach ($tanchu_list as $val) {
             $now = TIMESTAMP;
            if($now >= $val['start_time'] && $now <= $val['end_time'] ){
                $data['state']       = 1;
            }else{
                $data['state']       = 2;
            }
            $condition1['tanchu_id'] = $val['tanchu_id'];
            Model('tanchu')->table('tanchu')->where($condition1)->update($data);
        }
    }

    /* 首页弹出框列表
    */
   /* public function tanchulistOp(){
        $model_tanchu = Model('tanchu');
        $condition['is_open'] = 1;
        $tanchu_list = $model_tanchu->table('tanchu')->where($condition)->order("acs asc")->select();
		
        foreach ($tanchu_list as $k => $tanchu_info) {
            
           $tanchu_list[$k]['pic'] = getMbSpecial1ImageUrl( $tanchu_info['pic']);
        }
        if(!empty($tanchu_list)){
            echo $this->returnMsg(10000, '查询成功！',$tanchu_list);exit;
        }else{
            /*echo $this->returnMsg(10001, '', '');exit;
        }
        
    }*/
    /* 首页弹出框列表
    */
    public function tanchulistOp(){
        $model_tanchu = Model('tanchu');
        $condition['state'] = 1;
        $tanchu_list = $model_tanchu->table('tanchu')->where($condition)->order("acs asc")->select();
        
        foreach ($tanchu_list as $k => $tanchu_info) {
            
           $tanchu_list[$k]['pic'] = getMbSpecial1ImageUrl( $tanchu_info['pic']);
           if($tanchu_info['type'] == 3){
                $condition1['goods_id'] = $tanchu_info['content'];
                $goods_info = Model()->table('goods')->where($condition1)->find();
                // print_r($goods_info);die;
                $tanchu_list[$k]['is_group_ladder'] = $goods_info['is_group_ladder'];
           }else{
             $tanchu_list[$k]['is_group_ladder'] = null;
           }
        }
        if(!empty($tanchu_list)){
            echo $this->returnMsg(10000, '查询成功！',$tanchu_list);exit;
        }
        else{
            echo $this->returnMsg(10001, '查询成功,无启用的弹出框！', '');exit;
        }
        
    }
}