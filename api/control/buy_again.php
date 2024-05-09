<?php
defined('In718Shop') or exit('Access Invalid!');
class buy_againControl extends BaseControl{

     
/**
* 添加购物车
*/
    public function buyOp(){
        $cart_model = Model('cart');
        $model_goods = Model('goods');
        $goods_idarr = explode(',',$_GET['goods_id']);
         // $buy_items = $this->_parseItems($goods_idarr);
        //  var_dump( $buy_items);die;
        // $quantity = intval($_GET['quantity']);
        $member_id = intval($_GET['member_id']);
        foreach ($goods_idarr as $key => $value) {
               $goods_id=$value;
               $quantity=1;
               // unset($goods_sx);unset($goods_buzu);
               $goods_buzu=NULL; $goods_sx=NULL;
                if (is_numeric($goods_id) && $goods_id>0) {
                    //商品加入购物车(默认)
                    if ($goods_id <= 0){ 
                        $message='fail';
                    $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
                    echo json_encode($res,320);die;
                    } 
                    $goods_info = $model_goods->getGoodsOnlineInfoAndPromotionById($goods_id,'');
                }  
                   // var_dump($goods_id);die;
                //普通商品享受会员价 
                if($goods_info['is_group_ladder'] == 0){
                  $is_vip_price = $model_goods->getGoodsCommonList(array('goods_commonid' => $goods_info['goods_commonid']))[0]['is_vip_price'];
                  if ($is_vip_price == 1) {
                        $member_model = Model('member');
                        $discount = $member_model->getDiscount($member_id);
                        $goods_tax_rate = $model_goods->getGoodsCommonList(array('goods_commonid' =>  $goods_info['goods_commonid']))[0]['goods_tax_rate'];
                        //原价
                        $goods_info['or_goods_price'] = $goods_info['goods_price'];
                        //会员折扣价
                        $goods_info['goods_price'] = ncPriceFormat($discount * $goods_info['goods_price']);
                        $goods_info['goods_tax'] = ncPriceFormat($goods_info['goods_price']  *$goods_tax_rate);
                  }

                }

                if(intval($goods_info['goods_storage']) < 1 || intval($goods_info['goods_storage']) < $quantity){
                    // var_dump($goods_info);die;
                    $goods_buzu=$goods_info['goods_name'];

                    // $message=$goods_info['goods_name'].'库存不足';
                    // $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
                    // echo json_encode($res,320);die;
                    // var_dump('expression');die;
                    //  break;
                }
                 // var_dump($goods_buzu);die;


               $model_goodspromotion=Model('goods_promotion'); 
        $arr_goodspromotion=array();
        $arr_goodspromotion['goods_id']=$goods_id;
        $arr_goodspromotion['promotion_type']=array('in', array(10,20));
        $info=$model_goodspromotion->getgoods_promotionInfo($arr_goodspromotion);
        $model_xianshi_goods = Model('p_xianshi_goods');
        if (!empty($info)) {
            $xianshi_goods_info = $model_xianshi_goods->where(array('goods_id' => $goods_id, 'start_time' => array('lt', time()), 'end_time' => array('gt', time()), 'state' => 1))->find();
            //获得秒杀商品购买上限
            $upper_limit = $xianshi_goods_info['upper_limit'];
            if($upper_limit > 0) {//购买上限为0时不做控制
                //已加
                $cart_info = $cart_model->where(array('buyer_id' => $member_id, 'goods_id' => $goods_id))->find();
                if ($cart_info) {
                    $cart_num = $cart_info['goods_num'];
                } else {
                    $cart_num = 0;
                }
                //已买，order_state>0
                $order_info = Model()->table('order,order_goods')->field('goods_num')->join('inner right')->on('order.order_id = order_goods.order_id')->where(array('order_goods.goods_id' => $goods_id, 'order_goods.buyer_id' => $member_id, 'order_goods.promotions_id' => $xianshi_goods_info['xianshi_goods_id'], 'order.order_state' => array('gt', 0)))->select();
                
                if (is_array($order_info)) {
                    $order_num = 0;
                    foreach ($order_info as $key => $value) {
                        $order_num += $value['goods_num'];
                    }
                }
                $all=$cart_num + $order_num + $quantity;
                // if($_GET['test']==1){
                //     var_dump($all);die;
                // }
                //判断已加+已买+本次加入数量<=购买上限
                if ($all >intval($upper_limit)) {
                             $goods_sx=$goods_info['goods_name'];//上限的商品
                             // break;
                        }
                    }
                }

                 $cart_info = $cart_model->getCartInfo(array('goods_id' => $goods_id, 'buyer_id' => $member_id));
                 // var_dump($goods_sx);die;
                 if(!empty($cart_info)){
                    $cart_id=$cart_info['cart_id'];
                    $data = array();
                    $allnum= $quantity+$cart_info['goods_num'];
                    if (intval($goods_info['goods_storage']) < $allnum) {
                        $data['goods_num'] = $goods_info['goods_num'];
                        $data['goods_price'] = $goods_info['goods_price'];
                        $data['subtotal'] = $goods_info['goods_price'] * $quantity;
                        $cart_model->editCart(array('goods_num' => $goods_info['goods_storage']), array('cart_id' => $cart_id, 'buyer_id' => $member_id));
                        $goods_buzu=$goods_info['goods_name'];
                        // $message=$goods_info['goods_name'].'库存不足';
                        // $res = array('code'=>'400' , 'message'=>$message,'data'=>$data );
                        // echo json_encode($res,320);die;
                        // break;
                    }
                    
                    if(empty($goods_buzu)&&empty($goods_sx)){ 
                        $data = array();
                        $data['goods_num'] = $quantity+$cart_info['goods_num'];
                        $data['goods_price'] = $goods_info['goods_price'];
                        $insert = $cart_model->editCart($data, array('cart_id' => $cart_id, 'buyer_id' => $member_id));
                        $cart_ids[]=$insert;

                    }else{
                        if(!empty($goods_sxs)){
                            $goods_sxs[]=$goods_sx;
                        }else{
                            $goods_buzus[]=$goods_buzu;
                        }
                    }
                    
                 }else{
                    // var_dump('$goods_buzu');die;
                    if(empty($goods_buzu)&&empty($goods_sx)){
                    $save_type = 'db';
                    $goods_info['buyer_id'] = $member_id;
                    $cart_model = model('cart');
                    $insert = $cart_model->addCart($goods_info, $save_type, $quantity);
                     $cart_ids[]=$insert;
                    }else{
                        if(!empty($goods_sxs)){
                            $goods_sxs[]=$goods_sx;
                        }else{
                            $goods_buzus[]=$goods_buzu;
                        }
                    }
                  
                }
               
             }    
             // var_dump($cart_ids);die;
        if($insert){
                $message='添加购物车成功';
                // $cart_ids
                if(!empty($goods_buzus)){
                   $message= implode(',',$goods_buzus).'库存不足';
                }
                if(!empty($goods_sxs)){
                    $message=  $message.implode(',',$goods_sxs).'超过购买上限';
                }
                //购物车id传过去，后续接口请求购买第一部
                $cart_ids=json_encode($cart_ids,320);
                $res = array('code'=>'100' , 'message'=>$message,'data'=>$cart_ids);
                 echo json_encode($res,320);
          }else{
                $message='库存不足或商品不存在';
                $res = array('code'=>'400' , 'message'=>$message,'data'=>'' );
                 echo json_encode($res,320);
          }
    }

    
/**
     * 检查商品是否符合加入购物车条件
     * @param unknown $goods
     * @param number $quantity
     */
    private function _check_goods($goods_info, $quantity) {
        if(intval($goods_info['goods_storage']) < 1) {
            exit(json_encode(array('msg'=>'购买数量错误')));
        }
        if(intval($goods_info['goods_storage']) < $quantity) {
            exit(json_encode(array('msg'=>$goods_info['goods_name'].'库存不足')));
        }
    }
 private function _parseItems($cart_id) {
        //存放所购商品ID和数量组成的键值对
        $buy_items = array();
        if (is_array($cart_id)) {
            foreach ($cart_id as $value) {
                if (preg_match_all('/^(\d{1,10})\|(\d{1,6})$/', $value, $match)) {
                    if (intval($match[2][0]) > 0) {
                        $buy_items[$match[1][0]] = $match[2][0];
                    }
                }
            }
        }
        return $buy_items;
    }





}