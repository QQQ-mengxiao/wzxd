<?php
defined('In718Shop') or exit('Access Invalid!');

class tuangouControl extends BaseControl{
    //每页显示商品数
    const PAGESIZE = 12;
    //页数
    const PAGENUM = 1;
    /* 首页团购专项区
    */
    public function tuangou_shouyeOp(){
        $model_goods = Model('goods');
        //获取首页团购商品列表
        $order = 'goods_salenum desc';
        $goods_tuangou_list = $model_goods->getGoodsOnlineList(array('is_group_ladder' => 2),'*','', $order,4);
         foreach ($goods_tuangou_list as $key => $value) {
          $goods_tuangou_list[$key]['goods_image']= cthumb($value['goods_image'], 240, $value['store_id']);
        }
        // var_dump( $goods_tuangou_list);die;
        if(!empty($goods_tuangou_list)){
            $res = array('code' => '100', 'message' => 'sucess', 'data' => $goods_tuangou_list);
            echo json_encode($res,320);die;
        }else{
            $res = array('code' => '200', 'message' => 'fail', 'data' => $goods_tuangou_list);
           echo json_encode($res,320);die;
        }
    }
   /* 首页团购专项区
    */
    public function tuangou_listOp(){
        $model_goods = Model('goods');
        //获取首页团购商品列表
        $order = 'goods_salenum desc';
        $goods_tuangou_list = $model_goods->getGoodsOnlineList(array('is_group_ladder' => 2),'*','', $order,0);
         foreach ($goods_tuangou_list as $key => $value) {
          $goods_tuangou_list[$key]['goods_image']= cthumb($value['goods_image'],240, $value['store_id']);
        }
           $totaldata = count($goods_tuangou_list);
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
        if(!empty($goods_tuangou_list)){
            $goods_tuangou_list = array_slice($goods_tuangou_list, ($pagecount - 1) * self::PAGESIZE,  self::PAGESIZE);
            $data['goods_tuangou_list'] = $goods_tuangou_list;
            $res = array('code' => '100', 'message' => 'sucess', 'data' => $data);
            echo json_encode($res,320);die;
        }else{
            $res = array('code' => '200', 'message' => 'fail', 'data' => $data);
           echo json_encode($res,320);die;
        }
    }

}