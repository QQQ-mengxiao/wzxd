<?php
defined('In718Shop') or exit('Access Invalid!');
class maizengControl  extends BaseControl{
   /* 链接测试
    */
    public function testOp(){
        echo $this->returnMsg(10000, '请求成功！', '');exit;
    }
    /* 订单满赠
    */
    public function maicengruleOp(){
      //订单预计实付金额
      $order_price = $_GET['order_price'];
      //查询是否有开启时间且开启的满赠活动
      $condition['start_time']  = array('lt',time());
      $condition['end_time']  = array('gt',time());
      $condition['state']  = 2; 
      //$condition['store_id']  =$_GET['store_id'] ; 
      $mansong_rule_list = Model()->table('p_mansong')->where($condition)->find();
      //print_r($mansong_rule_list);die;
      if(empty($mansong_rule_list)){
        echo $this->returnMsg(10001, '无开启的满赠活动！', '');exit;
      }else{
        $order = 'price desc';
        //查询满赠规则信息
        $condition1['mansong_id']  = $mansong_rule_list['mansong_id'] ;
        $condition1['price']  = array('elt',$order_price);
        $mansong_rule = Model()->table('p_mansong_rule')->where($condition1)->order($order)->find();
        //print_r($mansong_rule);die;
        //查询赠送商品信息
        $zeng_goods_condition['goods_id'] = $mansong_rule['goods_id'];
        $zeng_goods_condition['goods_state'] = 1;
        $zeng_goods_condition['goods_storage'] = array('gt',0);
        $zeng_goods = Model('goods')->table('goods')->field('goods_id,goods_name,goods_price,goods_image,is_group_ladder,goods_storage')->where($zeng_goods_condition)->find();
        //print_r($zeng_goods);die;
        if(!empty($zeng_goods)){
          $data['goods_id'] = $zeng_goods['goods_id'];
          $data['goods_name'] = $zeng_goods['goods_name'];
          $data['is_group_ladder'] = $zeng_goods['is_group_ladder'];
          $data['goods_num'] = $mansong_rule['count'];
          $data['goods_price'] = 0.00;
          $data['goods_yuanprice'] = $zeng_goods['goods_price'];
          $data['goods_image'] = $zeng_goods['goods_image'];
          $data['goods_image_url'] = cthumb($zeng_goods['goods_image']);
          $data['goods_storage'] = $zeng_goods['goods_storage'];
          echo $this->returnMsg(10000, '查询成功',  $data);exit;
        }else{
          echo $this->returnMsg(10002, '赠品没库存或不在售！', '');exit;
        }
      }
    }
    // /* 订单满赠提交
    // */
    // public function maicengruleOp(){
    //   //订单预计实付金额
    //   $order_price = $_GET['order_price'];
    //   //查询是否有开启时间且开启的满赠活动
    //   $condition['start_time']  = array('lt',time());
    //   $condition['end_time']  = array('gt',time());
    //   $condition['state']  = 2; 
    //   //$condition['store_id']  =$_GET['store_id'] ; 
    //   $mansong_rule_list = Model()->table('p_mansong')->where($condition)->find();
    //   //print_r($mansong_rule_list);die;
    //   if(!empty($mansong_rule_list)){
    //     $order = 'price desc';
    //     //查询满赠规则信息
    //     $condition1['mansong_id']  = $mansong_rule_list['mansong_id'] ;
    //     $condition1['price']  = array('elt',$order_price);
    //     $mansong_rule = Model()->table('p_mansong_rule')->where($condition1)->order($order)->find();
    //     //print_r($mansong_rule);die;
    //     //查询赠送商品信息
    //     $zeng_goods_condition['goods_id'] = $mansong_rule['goods_id'];
    //     $zeng_goods_condition['goods_state'] = 1;
    //     $zeng_goods_condition['goods_storage'] = array('gt',0);
    //     $zeng_goods = Model('goods')->table('goods')->where($zeng_goods_condition)->find();
    //     //print_r($zeng_goods);die;
    //     if(!empty($zeng_goods)){
          
    //       $data['goods_id'] = $zeng_goods['goods_id'];
    //       $data['goods_num'] = 1;
    //       $data['goods_price'] = 0.00;
    //       $data['goods_yuanprice'] = $zeng_goods['goods_price'];
    //       $data['goods_image'] = $zeng_goods['goods_image'];
    //       $data['goods_image_url'] = cthumb($zeng_goods['goods_image']);
    //       $data['goods_storage'] = $zeng_goods['goods_storage'];
    //       echo $this->returnMsg(10000, '查询成功',  $data);exit;
    //     }else{
    //       echo $this->returnMsg(10002, '赠品没库存或不在售！', '');exit;
    //     }
    //   }
    // }
 /* 订单满赠提交
    */
    public function test1Op(){
       $model_order = Model('order');
        $condition = array();
        $condition['order_sn'] = $_GET['order_sn'];
        
        //$condition['order_sn'] = $out_trade_no;
         //$order_info = $model_order->getFpOrderInfo($condition,array('order_common','order_goods','member'));
         $order_info = $model_order->getFpOrderInfo($condition,array('order_common','order_goods','member'));
         print_r($order_info);die;

    }
  // /**
  // 查询
  // */
  // public function selectdelieverOp(){
  //   $sql="SELECT 718shop_goods.* FROM 718shop_goods JOIN 718shop_goods_common ON 718shop_goods.goods_commonid=718shop_goods_common.goods_commonid where 718shop_goods.deliverer_id !=  718shop_goods_common.deliverer_id ";
  //   $result =Model()->query($sql);
  //       // $on = 'goods.goods_commonid=goods_common.goods_commonid';
  //       // $condition['goods.deliverer_id'] = array('neq','goods_common.deliverer_id');
  //       // $result = $this->table('goods,goods_common')->join('left')->on($on)->where($condition)->select();
  //     var_dump(count($result));
  //       print_r($result);die;
  //   $result = Model()->table('goods')->where(array('deliverer_id '=>0))->select();
  //   foreach ($result as $key => $value) {
  //     $com = Model()->table('goods_common')->where(array('goods_commonid'=>$value['goods_commonid']))->find();
  //     $condition1['goods_commonid'] = $com['goods_commonid'];
  //       $updata['deliverer_id'] = $com['deliverer_id'];
  //           $a = Model()->table('goods')->where($condition1)->update($updata);

  //   }
  //   if($a){
  //     echo '成功';
  //   }else{
  //     echo 'bu成功';
  //   }
  // } 
}