<?php
defined('In718Shop') or exit('Access Invalid!');
class ladder_priceControl extends BaseControl{
       //每页显示商品数
    const PAGESIZE = 12;
    //页数
    const PAGENUM = 1;
    
    /* 首页团购专项区
    */
    public function ladder_priceOp(){
        $model_goods = Model('goods');
        //获取首页团购商品列表
        $order = '-ladder_sort desc,goods_salenum desc';
        $goods_tuangou_list = $model_goods->getGoodsOnlineList(array('p_ladder_id' => array('gt',0)),'*','', $order,12);
         foreach ($goods_tuangou_list as $key => $value) {
          $goods_tuangou_list[$key]['goods_image']= cthumb($value['goods_image'], '', $value['store_id']);
        }
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
    public function ladder_price_listOp(){
        $model_goods = Model('goods');
        //获取首页团购商品列表
        $order = '-ladder_sort desc,goods_salenum desc';
        $goods_tuangou_list = $model_goods->getGoodsOnlineList(array('p_ladder_id' => array('gt',0)),'*','', $order,0);
           $totaldata = count($goods_tuangou_list);
          foreach ($goods_tuangou_list as $key => $value) {
          $goods_tuangou_list[$key]['goods_image']= cthumb($value['goods_image'], 240, $value['store_id']);
            $goods_tuangou_list[$key]['sale_num']= $value['goods_salenum'] + $value['goods_presalenum'];
        }
        if($_GET['key']==2) {//综合排序
            if ($_GET['order'] == 'true') {
                $goods_tuangou_list = $this->multi_array_sort($goods_tuangou_list, 'goods_id',SORT_DESC);
            }else{
                $goods_tuangou_list = $this->multi_array_sort($goods_tuangou_list, 'goods_id');
            }
        }elseif ($_GET['key']==1){//销量排序
            if($_GET['order'] == 'true'){
                $goods_tuangou_list = $this->multi_array_sort($goods_tuangou_list, 'sale_num');
            }else{
                $goods_tuangou_list = $this->multi_array_sort($goods_tuangou_list, 'sale_num',SORT_DESC);
            }
        }elseif ($_GET['key']==3) {//价格排序
            if($_GET['order'] == 'true'){
                $goods_tuangou_list = $this->multi_array_sort($goods_tuangou_list, 'goods_price');
            }else{
                $goods_tuangou_list = $this->multi_array_sort($goods_tuangou_list, 'goods_price',SORT_DESC);
            }
        }
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
            $goods_tuangou_list = array_slice($goods_tuangou_list, 0,  $pagecount * self::PAGESIZE);
            $data['goods_tuangou_list'] = $goods_tuangou_list;
            $res = array('code' => '100', 'message' => 'sucess', 'data' => $data);
            echo json_encode($res,320);die;
        }else{
            $res = array('code' => '200', 'message' => 'fail', 'data' => $data);
           echo json_encode($res,320);die;
        }
    }
     public function deliver_timeOp(){
        $time=$_GET['time'];
        $store_id=$_GET['store_id'];
         $model_mansong = Model('p_ladder');
        $model_mansong_rule = Model('p_ladder_rule');
        $condition = array();
        $condition['store_id'] =intval($store_id);
        $condition['is_default'] =1;
        $ladder_info = $model_mansong->getMansongInfo($condition);
        // var_dump($ladder_info);die;     
        $mansong_id = intval($ladder_info['p_ladder_id']);
        $ladder_rule = $model_mansong_rule->getMansongRuleListByID($mansong_id);
        // var_dump($ladder_info);die;
        // echo date( "h:i ");
        foreach ($ladder_rule as $key => $value) {
         $time_dian[]=$value['time'];
        }
        // var_dump($time_dian);die;
        $a=date('H',time())+date('s',time())/60;
        // $a=2.5;
        $chazhi=$time-$a;
        if($chazhi<2){
            $res = array('code' => '200', 'message' => '配送时限太少', 'data' => $data);
           echo json_encode($res,320);die;
        }
        $max=max( $time_dian);
        if($max>= $chazhi){
           $count=count($time_dian);
          for ($i=0; $i <$count ; $i++) {
             $arr2[]=$chazhi-$time_dian[$i];
          }
          for ($i=0; $i <$count ; $i++) {
            if ($arr2[$i]<=0) {
                 $time=$time_dian[$i];
                 break;
            }
          }
        }else{
          $time=$max;
        }
       foreach ($ladder_rule as $key => $value) {
         if( $time==$value['time']){
          $data=$value;
         }
       }
       if(!empty($data)){
        $res = array('code' => '100', 'message' => 'sucess', 'data' => $data);
            echo json_encode($res,320);die;
          }
       
    }

}