<?php
defined('In718Shop') or exit('Access Invalid!');
class cartControl extends BaseControl{

      /* 购物车列表
     */
    public function indexOp(){
        $member_id      = $_POST['member_id'];
        $member_grade   = $member_id? (Model('member')->getGrade($member_id))+1 :-1;//用户等级
        $member_info    = Model()->table('member')->where(array('member_id' => $member_id, 'is_xinren' => 1))->find();
        $ziti_id        = Model()->table('member')->getfby_member_id($member_id,'ziti_id');
        $ziti_id        = $ziti_id?$ziti_id:3;

        if ($member_info) { //是新人
            $xinren_str = "";
        } else { //非新人
            $xinren_str = " AND sgp.promotion_type != 50 ";
        }
        //购物车列表
        $sql = "SELECT * FROM (SELECT e.goods_storage,e.goods_commonid,e.goods_id,e.goods_name,MIN(e.goods_price) AS goods_price,e.goods_image,e.cart_id,e.goods_num,e.by_post FROM (SELECT sg.goods_storage,sg.goods_commonid,sg.goods_name,sg.goods_image,sc.cart_id,sg.goods_id,sg.deliverer_id,ifnull(d.price,sg.goods_price) AS goods_price,sc.goods_num,ds.by_post FROM 718shop_cart sc LEFT JOIN 718shop_goods sg ON sc.goods_id=sg.goods_id LEFT JOIN (SELECT 718shop_daddress.address_id,718shop_storage.by_post FROM 718shop_daddress LEFT JOIN 718shop_storage ON 718shop_daddress.storage_id = 718shop_storage.storage_id) ds ON ds.address_id = sg.deliverer_id INNER JOIN (SELECT DISTINCT sg.goods_commonid FROM 718shop_goods sg) g ON g.goods_commonid=sg.goods_commonid LEFT JOIN (SELECT sgp.goods_id,min(price) AS price FROM 718shop_goods_promotion sgp WHERE CASE sgp.promotion_type WHEN 30 THEN sgp.member_levels<=2 ELSE 1 END ".$xinren_str." GROUP BY sgp.goods_id) d ON d.goods_id=sg.goods_id LEFT JOIN 718shop_buy_deliver_goods sbdg ON sg.goods_id=sbdg.goods_id LEFT JOIN 718shop_goods_common sgc ON sg.goods_commonid=sgc.goods_commonid WHERE sg.goods_state=1 AND (sbdg.ziti_id=".$ziti_id." OR sbdg.ziti_id IS NULL) AND sg.goods_verify=1 AND sg.is_deleted=0 AND sc.buyer_id=".$member_id.") e GROUP BY cart_id) a ORDER BY ( CASE WHEN goods_storage = 0 THEN 1 ELSE 0 END ), cart_id DESC";
        $cart_list      = Model()->query($sql);
        if($cart_list && is_array($cart_list)){
            foreach($cart_list as $key=>$goods){
                //通过goods_id在活动表中查询最低价格以及标签，最多三个
                // $sql    = "SELECT gp.goods_id,gp.promotion_type,gp.price FROM 718shop_goods_promotion gp WHERE gp.goods_id=".$goods['goods_id']." AND gp.promotion_type !=50 AND CASE gp.promotion_type WHEN 30 THEN gp.member_levels<=".$member_grade." ELSE 1 END GROUP BY gp.promotion_type ORDER BY gp.price ASC LIMIT 3";
                $sql = "SELECT a.* FROM ((SELECT sgp.promotion_type,sgp.goods_id,sgp.price,sgp.goods_promotion_id FROM 718shop_goods_promotion sgp WHERE sgp.goods_id=".$goods['goods_id']."  ".$xinren_str." AND sgp.promotion_type !=30) UNION (SELECT sgp1.promotion_type,sgp1.goods_id,sgp1.price,sgp1.goods_promotion_id FROM 718shop_goods_promotion sgp1 WHERE sgp1.goods_id=".$goods['goods_id']." AND sgp1.promotion_type=30 AND sgp1.member_levels<=".$member_grade." ORDER BY sgp1.price LIMIT 1)) a ORDER BY a.price ASC,a.goods_promotion_id ASC LIMIT 1";
                $goods_promotion_info = Model()->query($sql);
                $cart_list[$key]['promotion_name'] = $goods_promotion_info[0]['promotion_type']?promotion_typeName($goods_promotion_info[0]['promotion_type']):'普通商品';
                $cart_list[$key]['promotion_type'] = $goods_promotion_info[0]['promotion_type']?$goods_promotion_info[0]['promotion_type']:0;
                $cart_list[$key]['goods_image'] = cthumb($goods['goods_image']);
                $cart_list[$key]['member_grade'] = $member_grade;
                $cart_list[$key]['goods_price'] = $goods_promotion_info[0]['price']?$goods_promotion_info[0]['price']:$goods['goods_price'];
                $cart_list[$key]['goods_total'] = $cart_list[$key]['goods_price'] * $cart_list[$key]['goods_num'];
                //增加是否包邮分组
                if ($goods['by_post'] == 1) {
                    //包邮区
                    $cart_list_by_post[] = $cart_list[$key];
                }
                else{
                    //非包邮取
                    $cart_list_no_psot[] = $cart_list[$key];
                }
            }
        }
        $result_list = array();
        $result_list[0]['store_name'] = '物资小店';
        $result_list[0]['store_id'] = 4;
        //type=1,返回包邮数据，否则,返回非包邮数据
        if (isset($_POST['type']) && $_POST['type'] == 1) {
            $result_list[0]['cart_list'] = $cart_list_by_post?$cart_list_by_post:[];
        }else{
            $result_list[0]['cart_list'] = $cart_list_no_psot?$cart_list_no_psot:[];
        }
        if($result_list){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$result_list);
            die(json_encode($res,320));

        }else{
            $message='fail';
            $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
            die(json_encode($res,320));
        }
    }

      /* 购物车列表
     */
    public function index040701Op(){
        $model_cart = Model('cart');
        $logic_buy_1 = logic('buy_1');
        $model_goods = Model('goods');
        // $store_id =$_GET['store_id'];
        $member_id = $_GET['member_id'];
      // var_dump($_GET);die;
        //购物车列表
        $cart_list = $model_cart->listCart('db', array('buyer_id' =>$member_id));    
            // echo json_encode($cart_list,320);die;
        //购物车列表 [得到最新商品属性及促销信息] 
        $cart_list = $logic_buy_1->getGoodsCartList($cart_list,0,$member_id);
        // echo json_encode($cart_list,320);die;
        //购物车商品以店铺ID分组显示,并计算商品小计,店铺小计与总价由JS计算得出
        $store_cart_list = array();
        foreach ($cart_list as $cart) {
            if($cart['state']){
                $cart['goods_total'] = ncPriceFormat($cart['goods_price'] * $cart['goods_num']);
                $cart['goods_image'] = cthumb($cart['goods_image'], '', $cart['store_id']);
                if ($cart['is_group_ladder'] == 4) {
                        $model_xianshi_goods = Model('p_xianshi_goods');
                        $model_xianshi = Model('p_xianshi');
                        $xianshigoods = $model_xianshi_goods->getXianshiGoodsInfo(array('goods_id' => $cart['goods_id'] ));
                        $xianshi_info = $model_xianshi->getXianshiInfo(array('xianshi_id' => $xianshigoods['xianshi_id']));
                        // var_dump($xianshigoods);die;
                        $cart['xianshi_type']=$xianshi_info['xianshi_type'];
                        }
                 $store_cart_list[$cart['store_id']][] = $cart;
            }
        }
        // $store_cart_list=array_values($store_cart_list);
        // $result_list= array();
        // foreach ($store_cart_list as $k=> $value) {
        //      $result_list[$k]['store_name'] = $value[0]['store_name'];
        //      $result_list[$k]['store_id']=$value[0]['store_id'];
        //      $result_list[$k]['cart_list']=$value;
        // }
        // print_r($store_cart_list);die;
       //  $storecart_list= array();
       //  foreach($store_cart_list as $store_id => $cart_list) {
       //    // var_dump($store_id);die;
       //  $shipperArr = Model()->query("SELECT address_id FROM `718shop_daddress` "); 
       //  // var_dump($shipperArr);die;
       //  $shipperArr[]['address_id'] = '0';

       //   foreach($shipperArr as $shipper_id_arr) {
       //     $address_id = $shipper_id_arr['address_id'];
       //    foreach($cart_list as $cart_info){
       //   if( $cart_info['deliverer_id'] == $address_id ){
       //                 $storecart_list[$store_id][$address_id][]=$cart_info;

       //              }
       //     }
       //   }
       // }
     $result= array();
     // print_r($store_cart_list);die;
     foreach ($store_cart_list as $store_id => $value) {
        foreach ($value as $k => $v) {
          $result[$k]=$v;
        }
     }
     //print_r($result);die;
        $storecart_list=array_values($result); 
        $result_list= array();
        $result_list[0]['store_name'] = $storecart_list[0]['store_name'];
        $result_list[0]['store_id'] = $storecart_list[0]['store_id'];
        $result_list[0]['cart_list'] = $storecart_list;
       // print_r($result_list);die;
        // foreach ($storecart_list as $k=> $value) {
        //    $result_list[$k]['store_name'] = $value[0]['store_name'];
        //    $result_list[$k]['store_id']=$value[0]['store_id'];
        //     $daddress_info = Model('daddress')->getAddressInfo(array('address_id'=>$value[0]['deliverer_id']));
        //     // var_dump($daddress_info);die;
        //    $result_list[$k]['company']=$daddress_info['company'];
        //    $result_list[$k]['cart_list']=$value;
        // }
    //var_dump($result_list[0]['cart_list']);
    // $cart_goods_list = $result_list[0]['cart_list'];
    // $cart_list_temp = array();
    // foreach($cart_goods_list as $key=>$value){
    //   $cart_list_temp[$value['is_group_ladder']][] = $value;
    //   //array_push($cart_list_temp[$value['is_group_ladder']],$value);
    // }
    //var_dump($cart_list_temp);
    //$result_list[0]['cart_list'] = $cart_list_temp;
        if($result_list){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$result_list);
            echo json_encode($res,320);

          }else{
                $message='fail';
                $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
                echo json_encode($res,320);
          }
    }

/**
     * 添加购物车
     */
    public function addOp(){
        $cart_model = Model('cart');
        $model_goods = Model('goods');
        $goods_id = intval($_GET['goods_id']);
        $quantity = intval($_GET['quantity']);
        $member_id = intval($_GET['member_id']);  
        if (is_numeric($goods_id) && $goods_id>0) {
            //商品加入购物车(默认)
            if ($goods_id <= 0){ 
                $message='fail';
            $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
            echo json_encode($res,320);die;
            } 
            $goods_info = $model_goods->getGoodsOnlineInfoAndPromotionById($goods_id);
        }  

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
            $message='库存不足';
            $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
            echo json_encode($res,320);die;
        }

        //限时秒杀商品购买上限判断
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
                //判断已加+已买+本次加入数量<=购买上限
                if ($all >intval($upper_limit)) {
                    $message = '已超过购买上限';
                    $res = array('code' => '200', 'message' => $message, 'data' => '');
                    exit(json_encode($res, 320));
                }
            }
        }

         $cart_info = $cart_model->getCartInfo(array('goods_id' => $goods_id, 'buyer_id' => $member_id));
        
         if(!empty($cart_info)){
            $cart_id=$cart_info['cart_id'];
            $data = array();
            $allnum= $quantity+$cart_info['goods_num'];
            if (intval($goods_info['goods_storage']) < $allnum) {
                $data['goods_num'] = $goods_info['goods_num'];
                $data['goods_price'] = $goods_info['goods_price'];
                $data['subtotal'] = $goods_info['goods_price'] * $quantity;
                $cart_model->editCart(array('goods_num' => $goods_info['goods_storage']), array('cart_id' => $cart_id, 'buyer_id' => $member_id));
                $message='商品加购件数(含已加购件数)已超过库存';
                $res = array('code'=>'400' , 'message'=>$message,'data'=>$data );
                echo json_encode($res,320);die;
            }
             $data = array();
            $data['goods_num'] = $quantity+$cart_info['goods_num'];
            $data['goods_price'] = $goods_info['goods_price'];
            $insert = $cart_model->editCart($data, array('cart_id' => $cart_id, 'buyer_id' => $member_id));
         }else{
            $save_type = 'db';
            $goods_info['buyer_id'] = $member_id;
            $cart_model = model('cart');
            $insert = $cart_model->addCart($goods_info, $save_type, $quantity);
        }
        if($insert ){
            $message='加入购物车成功';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$insert);
             echo json_encode($res,320);
      }else{
            $message='加入购物车失败';
            $res = array('code'=>'400' , 'message'=>$message,'data'=>'' );
             echo json_encode($res,320);
      }
    }

    /**
     * 购物车更新商品数量
     */
    public function updateOp() {
        $logic_buy_1 = logic('buy_1');
        $cart_model = Model('cart');
        $goods_model = Model('goods');
        $cart_id = intval($_GET['cart_id']);
        $quantity = intval($_GET['quantity']);
        $member_id = intval($_GET['member_id']);  
        if (empty($cart_id) || empty($quantity)) {
            $message='fail';
            $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
            echo json_encode($res,320);die;
        }
        //存放返回信息
        $data = array();
      
        $cart_info = $cart_model->getCartInfo(array('cart_id' => $cart_id, 'buyer_id' => $member_id));
        // if ($cart_info['bl_id'] == '0') {

            //普通商品
            $goods_id = intval($cart_info['goods_id']);
            $goods_info = $logic_buy_1->getGoodsOnlineInfo($goods_id, $quantity ,$member_id);
            // var_dump($goods_info);die;
            if (empty($goods_info)) {
               $message='fail';
                $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
                echo json_encode($res,320);die;
            }

            if (intval($goods_info['goods_storage']) < $quantity) {
                $data['goods_num'] = $goods_info['goods_num'];
                $data['goods_price'] = $goods_info['goods_price'];
                $data['subtotal'] = $goods_info['goods_price'] * $quantity;
                $cart_model->editCart(array('goods_num' => $goods_info['goods_storage']), array('cart_id' => $cart_id, 'buyer_id' => $member_id));
                $message='库存不足';
                $res = array('code'=>'400' , 'message'=>$message,'data'=>$data );
                echo json_encode($res,320);die;
            }
			
			// //限时秒杀商品购买上限判断
   //      $goods_promotion_type = $goods_model->getGoodsInfo(array('goods_id'=>$goods_id))['goods_promotion_type'];//var_dump($goods_promotion_type);die;
   //      $model_xianshi_goods = Model('p_xianshi_goods');
   //      if ($goods_promotion_type == 2) {
              //限时秒杀商品购买上限判断
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
                               //已买，order_state>0
                $order_info = Model()->table('order,order_goods')->field('goods_num')->join('inner')->on('order.order_id = order_goods.order_id')->where(array('order_goods.goods_id' => $goods_id, 'order_goods.buyer_id' => $member_id, 'order_goods.promotions_id' => $xianshi_goods_info['xianshi_id'], 'order.order_state' => array('gt', 0)))->select();
                if (is_array($order_info)) {
                    $order_num = 0;
                    foreach ($order_info as $key => $value) {
                        $order_num += $value['goods_num'];
                    }
                }
                $all=$cart_num + $order_num + $quantity;
                //判断已加+已买+本次加入数量<=购买上限
                if ($all >intval($upper_limit)) {
                    $message = '已超过购买上限';
                    $res = array('code' => '400', 'message' => $message, 'data' => '');
                    exit(json_encode($res, 320));
                }
            }
        }
            // echo'222222';die;
        $data = array();
        $data['goods_num'] = $quantity;
        $data['goods_price'] = $goods_info['goods_price'];
        $update = $cart_model->editCart($data, array('cart_id' => $cart_id, 'buyer_id' => $member_id));
        if ($update) {
            $data = array();
            $data['subtotal'] = $goods_info['goods_price'] * $quantity;
            $data['goods_price'] = $goods_info['goods_price'];
            $data['goods_num'] = $quantity;
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$data);
            echo json_encode($res,320);
        } else {
             $message='fail';
            $res = array('code'=>'200' , 'message'=>$message,'data'=>$data);
            echo json_encode($res,320);
        }
    }
 /**
     * 购物车删除商品
     */
    public function delOp() {
        $cart_id =explode(',',$_GET['cart_id']);
        $num=count($cart_id);
        $cart_model = Model('cart');
        $member_id = $_GET['member_id'];
        $data = array();
        for ($i=0; $i <$num ; $i++) { 
             $delete = $cart_model->delCart('db', array('cart_id' => $cart_id[$i], 'buyer_id' => $member_id));
        }
           
            if ($delete) {
                 $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$delete);
           echo json_encode($res,320);
            } else {
                $message='fail';
            $res = array('code'=>'200' , 'message'=>$message,'data'=>$delete);
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
            exit(json_encode(array('msg'=>'库存不足')));
        }
    }






}