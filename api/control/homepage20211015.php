<?php

defined('In718Shop') or exit('Access Invalid!');

class homepageControl
{
    //首页
    const SPECIAL_ID = 1;
    //每页显示的商品个数
    const PAGESIZE = 12;
    //页数
    const PAGENUM = 1;
    //模型对象
    private $_model_search;

    public function indexOp()
    {

    }

    //轮播图
    public function bannerOp()
    {
        $condition = array(
            'item_type' => 'banner',
            'special_id' => self::SPECIAL_ID
        );

        $mbSpecialModel = Model('mb_special1');
        $bannerInfo = $mbSpecialModel->getMbSpecialItemList($condition);

        if ($bannerInfo) {
            $bannerInfo = $this->listData($bannerInfo);
            foreach ($bannerInfo[0]['item_data']['item'] as $key => $value) {
                $bannerInfo[0]['item_data']['item'][$key]['data']='/pages/pageGood/goodlist/goodlist?search='.$value['data'];
            }
            $banner = array('code' => '400', 'msg' => '查询成功！', 'banner' => $bannerInfo[0]);
        } else {
            $banner = array('code' => '200', 'msg' => '查询失败！', 'banner' => []);
        }

        echo json_encode($banner);
    }

    //快捷导航，那八个圆形图标
    public function icon1Op()
    {
        $condition = array(
            'item_type' => 'icon',
            'special_id' => self::SPECIAL_ID
        );

        $mbSpecialModel = Model('mb_special1');
        $iconInfo = $mbSpecialModel->getMbSpecialItemList($condition);

        if ($iconInfo) {
            $iconInfo = $this->listData($iconInfo);
           /* print_r( $iconInfo );die;*/
             foreach ($iconInfo[0]['item_data']['item'] as $key => $value) {
                $iconInfo[0]['item_data']['item'][$key]['data']='/pages/pageGood/goodlist/goodlist?search='.$value['data'];
            }
             foreach ($iconInfo[0]['item_data']['item'] as $key => $value) {
                //获取后台设置的ICON 所对应的以及分类ID
                $model_gc = Model('goods_class');
                $gc_info = $model_gc->table('goods_class')->where(array('gc_name'=>$value['name'],'gc_parent_id'=>0))->find();
                if($gc_info){
                    $iconInfo[0]['item_data']['item'][$key]['type1'] = 'gc';
                    $iconInfo[0]['item_data']['item'][$key]['category_id'] = $gc_info['gc_id'];
                    $iconInfo[0]['item_data']['item'][$key]['data'] = '/pages/pageGood/goodlist/goodlist?search='.$gc_info['gc_id'];
                }else{
                    $iconInfo[0]['item_data']['item'][$key]['type1'] = $value['type'];
                    $iconInfo[0]['item_data']['item'][$key]['category_id'] = 0;
                }

            }
            // print_r($iconInfo[0]['item_data']['item']);die;
             # 按sort升序排序
            $iconInfo[0]['item_data']['item'] = $this->arraySort($iconInfo[0]['item_data']['item'], 'sort', SORT_ASC);
            $icon = array('code' => '400', 'msg' => '查询成功！', 'icon' => $iconInfo[0]);
        } else {
            $icon = array('code' => '200', 'msg' => '查询失败！', 'icon' => []);
        }

        echo json_encode($icon);
    }
    //快捷导航，那八个圆形图标
    public function iconOp()
    {
        $condition = array(
            'item_type' => 'icon',
            'special_id' => self::SPECIAL_ID
        );

        $mbSpecialModel = Model('mb_special1');
        $iconInfo = $mbSpecialModel->getMbSpecialItemList($condition);
       // print_r($iconInfo);die;
        if ($iconInfo) {
            $iconInfo = $this->listData($iconInfo);
            /*print_r($iconInfo);die;*/
             foreach ($iconInfo[0]['item_data']['item'] as $key => $value) {
                $iconInfo[0]['item_data']['item'][$key]['data']='/pages/pageGood/goodlist/goodlist?search='.$value['data'];
            }
            foreach ($iconInfo[0]['item_data']['item'] as $key => $value) {
                //获取后台设置的ICON 所对应的以及分类ID
                $model_gc = Model('goods_class');
                $gc_info = $model_gc->table('goods_class')->where(array('gc_name'=>$value['name'],'gc_parent_id'=>0))->find();
                if($gc_info){
                    $iconInfo[0]['item_data']['item'][$key]['type1'] = 'gc';
                    $iconInfo[0]['item_data']['item'][$key]['category_id'] = $gc_info['gc_id'];
                    $iconInfo[0]['item_data']['item'][$key]['data'] = '/pages/pageGood/goodlist/goodlist?search='.$gc_info['gc_id'];
                }else{
                    $iconInfo[0]['item_data']['item'][$key]['type1'] = $value['type'];
                    $iconInfo[0]['item_data']['item'][$key]['category_id'] = 0;
                }

            }
            // print_r($iconInfo[0]['item_data']['item']);die;
            # 按sort升序排序
            $iconInfo[0]['item_data']['item'] = $this->arraySort($iconInfo[0]['item_data']['item'], 'sort', SORT_ASC);
            $icon = array('code' => '400', 'msg' => '查询成功！', 'icon' => $iconInfo[0]);
        } else {
            $icon = array('code' => '200', 'msg' => '查询失败！', 'icon' => []);
        }

        echo json_encode($icon);
    }

    //广告，两个图并列的
    public function advOp()
    {
        $condition = array(
            'item_type' => 'images3',
            'special_id' => self::SPECIAL_ID
        );

        $mbSpecialModel = Model('mb_special1');
        $advInfo = $mbSpecialModel->getMbSpecialItemList($condition);
     
        if ($advInfo) {
            $advInfo = $this->listData($advInfo);
            foreach ($advInfo[0]['item_data']['item'] as $key => $value) {
                $advInfo[0]['item_data']['item'][$key]['data']='/pages/pageGood/goodlist/goodlist?search='.$value['data'];
            }
            $adv = array('code' => '400', 'msg' => '查询成功！', 'adv' => $advInfo);
        } else {
            $adv = array('code' => '200', 'msg' => '查询失败！', 'adv' => []);
        }

        echo json_encode($adv);
    }

    //通知
    public function noticeboardOp()
    {
        // $noticeboardModel = Model('noticeboard');
        // $noticeboardList = $noticeboardModel->getnoticeboardList(array(), 10);
        $noticeboardList =Model()->table('noticeboard')->where(array('is_open'=>1))->find();
        if ($noticeboardList) {
            $noticeboardList = array('code' => '400', 'msg' => '查询成功！', 'noticeboardList' => $noticeboardList);
        } else {
            $noticeboardList = array('code' => '200', 'msg' => '查询失败！', 'noticeboardList' => []);
        }

        echo json_encode($noticeboardList);
    }

    //今日主推
    public function hotOp()
    {
        $condition = array(
            'item_type' => 'image2',
            'special_id' => self::SPECIAL_ID
        );

        $mbSpecialModel = Model('mb_special1');
        $hotInfo = $mbSpecialModel->getMbSpecialItemList($condition);
        $hotInfo = $hotInfo[0];
        $hotInfo['item_data']['image'] = UPLOAD_SITE_URL . '/mobile/special1/s1/' . $hotInfo['item_data']['image'];

        if ($hotInfo) {
            $hot = array('code' => '400', 'msg' => '查询成功！', 'hotInfo' => $hotInfo);
        } else {
            $hot = array('code' => '200', 'msg' => '查询失败！', 'hotInfo' => []);
        }

        echo json_encode($hot);
    }

    //公益在行动
    public function charityOp()
    {
        $condition = array(
            'item_type' => 'image2',
            'special_id' => self::SPECIAL_ID
        );

        $mbSpecialModel = Model('mb_special1');
        $charityInfo = $mbSpecialModel->getMbSpecialItemList($condition);
        $charityInfo = $charityInfo[1];
        $charityInfo['item_data']['image'] = UPLOAD_SITE_URL . '/mobile/special1/s1/' . $charityInfo['item_data']['image'];

        if ($charityInfo) {
            $charity = array('code' => '400', 'msg' => '查询成功！', 'charity' => $charityInfo);
        } else {
            $charity = array('code' => '200', 'msg' => '查询失败！', 'charity' => []);
        }

        echo json_encode($charity);
    }

    //满减专区
    public function benefitOp()
    {
        $condition = array(
            'item_type' => 'image2',
            'special_id' => self::SPECIAL_ID
        );

        $mbSpecialModel = Model('mb_special1');
        $benefitInfo = $mbSpecialModel->getMbSpecialItemList($condition);
        $benefitInfo = $benefitInfo[2];
        $benefitInfo['item_data']['image'] = UPLOAD_SITE_URL . '/mobile/special1/s1/' . $benefitInfo['item_data']['image'];

        if ($benefitInfo) {
            $benefit = array('code' => '400', 'msg' => '查询成功！', 'benefit' => $benefitInfo);
        } else {
            $benefit = array('code' => '200', 'msg' => '查询失败！', 'benefit' => []);
        }

        echo json_encode($benefit);
    }
	
	 //首页各个活动上方图片
    public function image2Op()
    {
        //固定四个图固定值
        //311秒杀，312新人，314阶梯价，253推荐商品，315即买即送
        $item_id = $_POST['item_id'];
        if(!$item_id){
            die(json_encode(array('code' => '200', 'msg' => 'item_id不能为空', 'image' => [])));
        }
        $condition = array(
            'item_type' => 'image2',
            'special_id' => self::SPECIAL_ID,
            'item_id' => $item_id
        );

        $mbSpecialModel = Model('mb_special1');
        $image2Info = $mbSpecialModel->getMbSpecialItemList($condition);//var_dump($image2Info);die;
        $image2Info = $image2Info[0];
        $image2Info['item_data']['image'] = UPLOAD_SITE_URL . '/mobile/special1/s1/' . $image2Info['item_data']['image'];

        if ($image2Info) {
            $image = array('code' => '400', 'msg' => '查询成功！', 'image' => $image2Info);
        } else {
            $image = array('code' => '200', 'msg' => '查询失败！', 'image' => []);
        }

        echo json_encode($image);
    }

    //商品列表2
    public function goodsList2Op()
    {
        $condition = array(
            'item_type' => 'goods2',
            'special_id' => self::SPECIAL_ID
        );

        $mbSpecialModel = Model('mb_special1');
        $goodsList2 = $mbSpecialModel->getMbSpecialItemList($condition);

        if ($_POST['num']) {
            $num = $_POST['num'];
        } else {
            $num = self::PAGENUM;
        }

        if ($goodsList2) {
            $type = 'goods';
            $goodsList2 = $this->listData($goodsList2, $num, $type);
            $goodsList2 = array('code' => '400', 'msg' => '查询成功！', 'goodsList2' => $goodsList2[0]);
        } else {
            $goodsList2 = array('code' => '200', 'msg' => '查询失败！', 'goodsList2' => []);
        }

        echo json_encode($goodsList2);
    }

    //商品列表
    public function goodsListOp()
    {
        $condition = array(
            'item_type' => 'goods',
            'special_id' => self::SPECIAL_ID
        );

        $mbSpecialModel = Model('mb_special1');
        $goodsList = $mbSpecialModel->getMbSpecialItemList($condition);

        if ($_POST['num']) {
            $num = $_POST['num'];
        } else {
            $num = self::PAGENUM;
        }

        if ($goodsList) {
            $type = 'goods';
            $goodsList = $this->listData($goodsList, $num, $type);
            if (isset($_POST['ziti_id'])) {
                foreach ($goodsList as $key1 => $goodsInfo) {
                    foreach ($goodsInfo['item_data']['item'] as $key => $value) {
                        if ($value['is_group_ladder'] == 5) {
                        //获取活动id，活动id+goods_id是否存在，不存在剔除
                        $buy_deliver_info = model('buy_deliver')->getBuyDeliverInfo(array('ziti_id' => $_POST['ziti_id']));
                        //var_dump($buy_deliver_info['buy_deliver_id']);die;
                            if (empty($buy_deliver_info['buy_deliver_id'])) {
                                unset($goodsList[$key1]['item_data']['item'][$key]);
                            }else{
                                $buy_deliver_id = $buy_deliver_info['buy_deliver_id'];
                                $result = model('buy_deliver_goods')->getBuyDeliverGoodsList(array('buy_deliver_id'=>$buy_deliver_id,'goods_id'=>$value['goods_id']));
                                if (!$result) {
                                    unset($goodsList[$key1]['item_data']['item'][$key]);
                                }
                            }
                    }
                    }
                }
            }
            //foreach ($goodsList[0]['item_data']['item'] as $k=>$v){
                //if($v['goods_storage'] == 0){
                    //array_push($goodsList[0]['item_data']['item'], $goodsList[0]['item_data']['item'][$k]);//把为0的值追加到末尾
                    //unset($goodsList[0]['item_data']['item'][$k]);
                //}
            //}
            $goodsList[0]['count'] = count($goodsList[0]['item_data']['item']);
            $goodsList = array('code' => '400', 'msg' => '查询成功！', 'goodsList' => $goodsList[0]);
        } else {
            $goodsList = array('code' => '200', 'msg' => '查询失败！', 'goodsList' => []);
        }

        echo json_encode($goodsList);
    }
 //认证状态
  public function member_verifyOp()
    {
        $member_id = $_POST['member_id'];
        $member_info = Model('member')->getMemberInfo(array('member_id'=>$member_id));
        $data=$member_info['member_verify'];
        if ($member_info) {
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$data);
             echo json_encode($res,320);
        } else {
             $message='fail';
            $res = array('code'=>'200' , 'message'=>$message,'data'=>$member_info);
             echo json_encode($res,320);
        }
}
    //我的签约列表
    public function signListOp()
    {
        $user_id = $_POST['user_id'];

        if (empty($user_id)) {
            $signList = array('code' => '200', 'msg' => '请登录！', 'signList' => []);
        } else {
            $condition['user_id'] = $user_id;
            $signingModel = Model('signing');
            $signList = $signingModel->getSigningList($condition);

            $unit = 0;
            $quantity = 0;
            $price = 0;
            foreach ($signList as $key => $value) {
                $unit += $value['purchase_unit'];
                $quantity += $value['purchase_quantity'];
                $price += $value['purchase_price'];
                $daddress_info = Model('daddress')->getAddressInfo(array('address_id' => $value['address_id']));
                $signList[$key]['company'] = $daddress_info['company'];
            }

            if (!empty($_POST['num'])) {
                $num = $_POST['num'];
            } else {
                $num = self::PAGENUM;
            }

            if ($signList) {
                $signList = array_slice($signList, ($num - 1) * self::PAGESIZE, $num * self::PAGESIZE);
                $signList = array('code' => '400', 'msg' => '查询成功！', 'total_unit' => $unit, 'total_quantity' => $quantity, 'total_price' => $price, 'signList' => $signList);
            } else {
                $signList = array('code' => '200', 'msg' => '查询失败！', 'signList' => []);
            }
        }
        echo json_encode($signList);
    }

    //数据处理
    private function listData($list, $num = 1, $type = '')
    {
        if (!empty($list)) {
            foreach ($list as $key => $value) {
                if ($value['item_data']['item']) {
                    $list[$key]['item_data']['item'] = array_slice($value['item_data']['item'], ($num - 1) * self::PAGESIZE, $num * self::PAGESIZE);
                    $list[$key]['item_data']['item'] = array_values($value['item_data']['item']);
                }

                unset($list[$key]['usable_class']);
                unset($list[$key]['usable_text']);

                if ($type == 'goods' || $type == 'goods2') {
                    foreach ($list[$key]['item_data']['item'] as $k => $v) {
                        $goods_detail = Model('goods')->getGoodsDetail($v['goods_id']);
                        $list[$key]['item_data']['item'][$k]['goods_image'] = $goods_detail['goods_image_mobile'][0];
						$list[$key]['item_data']['item'][$k]['is_group_ladder'] = $goods_detail['goods_info']['is_group_ladder'];
                        $list[$key]['item_data']['item'][$k]['is_vip_price'] = $goods_detail['goods_info']['is_vip_price'];
                        $list[$key]['item_data']['item'][$k]['goods_storage'] = $goods_detail['goods_info']['goods_storage'];
                    }
                } else {
                    foreach ($list[$key]['item_data']['item'] as $k => $v) {
                        $list[$key]['item_data']['item'][$k]['image'] = UPLOAD_SITE_URL . '/mobile/special1/s1/' . $v['image'];
                    }
                }
            }
            return $list;
        }
        return false;
    }
	
	//首页弹窗
    public function popupOp()
    {
        //固定四个图固定值
        //311秒杀，312新人，314阶梯价，253推荐商品，315即买即送
        $item_id = '320';
        if(!$item_id){
            die(json_encode(array('code' => '200', 'msg' => 'item_id不能为空', 'image' => [])));
        }
        $condition = array(
            'item_type' => 'image2',
            'special_id' => '1000',//self::SPECIAL_ID,
            'item_id' => $item_id
        );

        $mbSpecialModel = Model('mb_special1');
        $image2Info = $mbSpecialModel->getMbSpecialItemList($condition);//var_dump($image2Info);die;
        $image2Info = $image2Info[0];
        $image2Info['item_data']['image'] = UPLOAD_SITE_URL . '/mobile/special1/s1000/' . $image2Info['item_data']['image'];
		$image2Info['item_data']['data'] =str_replace('/pages/pageGood/goodlist/goodlist?search=','',$image2Info['item_data']['data']);
        unset($image2Info['usable_text']);
        unset($image2Info['usable_class']);

        if ($image2Info) {
            $image = array('code' => '400', 'msg' => '查询成功！', 'image' => $image2Info);
        } else {
            $image = array('code' => '200', 'msg' => '查询失败！', 'image' => []);
        }

        echo json_encode($image);
    }

	/**
     * 搜索框下搜索热词
     */
    public function hot_searchOp(){
        $hot_search = explode(',',C('hot_search'));
        if($hot_search){
            die(json_encode(array('code' => '400', 'msg' => '查询成功！', 'hot_search' => $hot_search)));
        }else{
            die(json_encode(array('code' => '200', 'msg' => '查询失败！', 'hot_search' => [])));
        }
    }

	/**
     * 购物车角标
     */
    public function cart_countOp(){
        $model_member = Model('member');
        $member_id = $_POST['member_id'];
        if(!$member_id){
            die(json_encode(array('code'=>'-1','msg'=>'member_id不能为空！')));
        }
        $member_info = $model_member->getMemberInfoByID($member_id);
        if(!$member_info){
            die(json_encode(array('code'=>'-2','msg'=>'用户不存在！')));
        }
        $cart_list_v = Model()->table('cart,goods')->join('left')->on('cart.goods_id = goods.goods_id')->where(array('cart.buyer_id'=>$member_id,'goods.goods_state'=>1,'goods.goods_verify'=>1))->select();
        $cartCount = count($cart_list_v);
        if($cartCount){
            die(json_encode(array('code'=>'1','msg'=>'查询成功！','cartCount'=>$cartCount)));
        }else{
            die(json_encode(array('code'=>'-3','msg'=>'查询失败！','cartCount'=>0)));
        }
    }
     /**
     * 二维数组根据某个字段排序
     * @param array $array 要排序的数组
     * @param string $keys   要排序的键字段
     * @param string $sort  排序类型  SORT_ASC     SORT_DESC 
     * @return array 排序后的数组
     */
    function arraySort($array, $keys, $sort = SORT_DESC) {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[$k] = $v[$keys];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }

}