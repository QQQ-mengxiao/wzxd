<?php
defined('In718Shop') or exit('Access Invalid!');
class cartControl extends BaseControl{

      /* 购物车列表
     */
    public function indexOp(){
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
        $storecart_list= array();
        foreach($store_cart_list as $store_id => $cart_list) {
          // var_dump($store_id);die;
        $shipperArr = Model()->query("SELECT address_id FROM `718shop_daddress` "); 
        // var_dump($shipperArr);die;
        $shipperArr[]['address_id'] = '0';

         foreach($shipperArr as $shipper_id_arr) {
           $address_id = $shipper_id_arr['address_id'];
          foreach($cart_list as $cart_info){
         if( $cart_info['deliverer_id'] == $address_id ){
                       $storecart_list[$store_id][$address_id][]=$cart_info;

                    }
           }
         }
       }
     $result= array();
     foreach ($storecart_list as $key => $value) {
      foreach ($value as $k => $v) {
        if($k==0){
         $result[$v[0]['store_id']]=$v;
        }else{
          $result[$k]=$v;
        }
      
     }
     // var_dump($result);die;
     }
     // var_dump($result);die;
         $storecart_list=array_values($result); 
        $result_list= array();
        foreach ($storecart_list as $k=> $value) {
           $result_list[$k]['store_name'] = $value[0]['store_name'];
           $result_list[$k]['store_id']=$value[0]['store_id'];
            $daddress_info = Model('daddress')->getAddressInfo(array('address_id'=>$value[0]['deliverer_id']));
            // var_dump($daddress_info);die;
           $result_list[$k]['company']=$daddress_info['company'];
           $result_list[$k]['cart_list']=$value;
        }
		//var_dump($result_list[0]['cart_list']);
		$cart_goods_list = $result_list[0]['cart_list'];
		$cart_list_temp = array();
		foreach($cart_goods_list as $key=>$value){
			$cart_list_temp[$value['is_group_ladder']][] = $value;
			//array_push($cart_list_temp[$value['is_group_ladder']],$value);
		}
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
                $message='库存不足';
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
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$insert);
             echo json_encode($res,320);
      }else{
            $message='fail';
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
// var_dump($cart_info);die;
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