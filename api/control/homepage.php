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
        // if(rkcache('banner')){
        //     echo json_encode(array('code' => '400', 'msg' => '查询成功！', 'banner' => rkcache('banner')));
        //     die;
        // }
        $condition = array(
            'item_type' => 'banner',
            'special_id' => self::SPECIAL_ID
        );

        $mbSpecialModel = Model('mb_special1');
        $bannerInfo = $mbSpecialModel->getMbSpecialItemList($condition);
        if ($bannerInfo) {
            $bannerInfo = $this->listData($bannerInfo);
            foreach ($bannerInfo[0]['item_data']['item'] as $key => $value) {
            	if($bannerInfo[0]['item_data']['item'][$key]['data'] == '社区服务' ){
            		$bannerInfo[0]['item_data']['item'][$key]['data'] = '社区服务';
            	}elseif($bannerInfo[0]['item_data']['item'][$key]['type'] == 't_url'){
                    $bannerInfo[0]['item_data']['item'][$key]['t_url']=$value['data'];
                }elseif($bannerInfo[0]['item_data']['item'][$key]['type'] == 'g_type'){
                     //g_type '活动编号会跳转到指定的活动列表，1-阶梯价，3-新人专享，4-限时秒杀，5-即买即送，7-新品, 8-限时折扣, 9-邮寄商品'
                    $bannerInfo[0]['item_data']['item'][$key]['data']=$value['data'];
                }else{
            		$bannerInfo[0]['item_data']['item'][$key]['data']='/pages/pageGood/goodlist/goodlist?search='.$value['data'];
            	}
                
            }
            // wkcache('banner',$bannerInfo[0]);
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
        // sleep(50);
        if ($iconInfo) {
            $iconInfo = $this->listData($iconInfo);
           /* print_r( $iconInfo );die;*/
             foreach ($iconInfo[0]['item_data']['item'] as $key => $value) {
                $iconInfo[0]['item_data']['item'][$key]['data']='/pages/pageGood/goodlist/goodlist?search='.$value['data'];
                $iconInfo[0]['item_data']['item'][$key]['data1']=$value['data'];
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
        if ($iconInfo) {
            $iconInfo = $this->listData($iconInfo);
            /*print_r($iconInfo);die;*/
             foreach ($iconInfo[0]['item_data']['item'] as $key => $value) {
                if($value['type']=='t_url'){
                    $iconInfo[0]['item_data']['item'][$key]['t_url'] = $value['data'];
                }
                $iconInfo[0]['item_data']['item'][$key]['data']='/pages/pageGood/goodlist/goodlist?search='.$value['data'];
            }

            foreach ($iconInfo[0]['item_data']['item'] as $key => $value) {
                //获取后台设置的ICON 所对应的以及分类ID
                $model_gc = Model('goods_class');
                $iconInfo[0]['item_data']['item'][$key]['ngoods_sign'] = 0;
                if($value['data']=='/pages/pageGood/goodlist/goodlist?search=新品'){
                    $iconInfo[0]['item_data']['item'][$key]['ngoods_sign'] = 1;
                }

                

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
        $member_id      = $_POST['member_id'];
        $member_info    = Model()->table('member')->where(array('member_id' => $member_id, 'is_xinren' => 1))->find();
        $member_grade   = $member_id?(Model('member')->getGrade($member_id))+1:-1;//用户等级
        $mbSpecialModel = Model('mb_special1');
        $ziti_id        = $_POST['ziti_id']?$_POST['ziti_id']:3;
        $goodsList = $mbSpecialModel->getMbSpecialItemList($condition);
        if ($member_info) { //是新人
            $xinren_str = "";
        } else { //非新人
            $xinren_str = "AND sgp.promotion_type != 50";
        }

        if ($goodsList) {
            $goodsL = array();
            $goods_list = $goodsList[0]['item_data']['item'];
            foreach($goods_list as $key=>$goods_info){
                $sql_ziti = "SELECT ziti_id FROM 718shop_buy_deliver_goods WHERE goods_id=".$goods_info['goods_id'];
                $ziti_info = Model()->query($sql_ziti);
                if(!$ziti_info || in_array($ziti_id,array_column($ziti_info,'ziti_id'))){
                    //解决规格取消问题
                    $goodsInfo = Model()->table('goods,daddress,storage,cart')->field('goods.goods_price,goods.goods_storage,goods.goods_image,goods.goods_commonid,storage.by_post,cart.goods_num as cart_num')->join('left,left,left')->on('goods.deliverer_id=daddress.address_id,daddress.storage_id=storage.storage_id,goods.goods_id=cart.goods_id')->where(array('goods.goods_id'=>$goods_info['goods_id'],'goods.is_deleted'=>0))->find();
                    if(!$goodsInfo){
                        continue;
                    }
                    
                    // $sql = "SELECT sgp.goods_id,sgp.price AS goods_promotion_price,sgp.promotion_type,sg.goods_price,sg.goods_storage,sg.goods_image FROM 718shop_goods sg LEFT JOIN 718shop_goods_promotion sgp ON sg.goods_id=sgp.goods_id WHERE sg.goods_id=".$goods_info['goods_id']." AND sg.goods_state=1 AND sg.goods_verify=1 AND sg.is_deleted=0 AND sg.goods_storage>0 ".$xinren_str." AND CASE sgp.promotion_type WHEN 30 THEN sgp.member_levels<=".$member_grade." ELSE 1 END ORDER BY goods_promotion_price ASC LIMIT 3";
                    $sql = "SELECT a.* FROM ((SELECT sgp.promotion_type,sgp.goods_id,sgp.price as goods_promotion_price FROM 718shop_goods_promotion sgp WHERE sgp.goods_id=".$goods_info['goods_id']."  ".$xinren_str." AND sgp.promotion_type !=30) UNION (SELECT sgp1.promotion_type,sgp1.goods_id,sgp1.price FROM 718shop_goods_promotion sgp1 WHERE sgp1.goods_id=".$goods_info['goods_id']." AND sgp1.promotion_type=30 AND sgp1.member_levels<=".$member_grade." ORDER BY sgp1.price LIMIT 1)) a GROUP BY a.promotion_type ORDER BY a.goods_promotion_price ASC LIMIT 3";
                    $goods_promotion_info = Model()->query($sql);
                    //$goodsInfo = Model()->table('goods')->field('goods_price,goods_storage,goods_image,goods_commonid')->where(array('goods_id'=>$goods_info['goods_id']))->find();

                    /****************************存在问题****************************/
                    /**
                     * 后台设置了某个商品后，商家中心删除了该商品id对应的规格，则查出来的库存包括已删规格的库存
                     * 如果在条件中加入goods.is_deleted=0，则无法查出对应数据，同时下方未判断$goodsInfo是否有数据，接口数据还存在，会导致前端页面显示少图少数据
                    */
                    //联查获取商品是否包邮
                    // $goodsInfo = Model()->table('goods,daddress,storage')->field('goods.goods_price,goods.goods_storage,goods.goods_image,goods.goods_commonid,storage.by_post')->join('left,left')->on('goods.deliverer_id=daddress.address_id,daddress.storage_id=storage.storage_id')->where(array('goods.goods_id'=>$goods_info['goods_id']))->find();
                    //解决办法，放在循环体第一步，判断如果不存在商品信息，则跳出循环
                    /****************************存在问题****************************/
                    
                    if($goods_promotion_info){
                        if(count($goods_promotion_info)==3){
                            $goods_info['promotion_name'] = promotion_typeName($goods_promotion_info[0]['promotion_type']).'/'.promotion_typeName($goods_promotion_info[1]['promotion_type']).'/'.promotion_typeName($goods_promotion_info[2]['promotion_type']);
                            $goods_info['promotion_type'] = $goods_promotion_info[0]['promotion_type'];
                            $goods_info['promotion_length'] = count($goods_promotion_info);//长度
                            $goods_info['promotion_name1'] = promotion_typeName($goods_promotion_info[0]['promotion_type']);
                            $goods_info['promotion_name2'] = promotion_typeName($goods_promotion_info[1]['promotion_type']);
                            $goods_info['promotion_name3'] = promotion_typeName($goods_promotion_info[2]['promotion_type']);
                            //会员折扣率计算
                            if($goods_promotion_info[0]['promotion_type'] == 30){
                                $discount = number_format($goods_promotion_info[0]['goods_promotion_price'] / $goods_info['goods_price'],2) * 10;
                                $goods_info['hui_discount1'] = $discount.'折';
                            }
                        }elseif(count($goods_promotion_info)==2){
                            $goods_info['promotion_name'] = promotion_typeName($goods_promotion_info[0]['promotion_type']).'/'.promotion_typeName($goods_promotion_info[1]['promotion_type']);
                            $goods_info['promotion_type'] = $goods_promotion_info[0]['promotion_type'];
                            $goods_info['promotion_length'] = count($goods_promotion_info);//长度
                            $goods_info['promotion_name1'] = promotion_typeName($goods_promotion_info[0]['promotion_type']);
                            $goods_info['promotion_name2'] = promotion_typeName($goods_promotion_info[1]['promotion_type']);
                            //会员折扣率计算
                            if($goods_promotion_info[0]['promotion_type'] == 30){
                                $discount = number_format($goods_promotion_info[0]['goods_promotion_price'] / $goods_info['goods_price'],2) * 10;
                                $goods_info['hui_discount1'] = $discount.'折';
                            }
                        }else{
                            $goods_info['promotion_name'] = promotion_typeName($goods_promotion_info[0]['promotion_type']);
                            $goods_info['promotion_type'] = $goods_promotion_info[0]['promotion_type'];
                            $goods_info['promotion_length'] = count($goods_promotion_info);//长度
                            $goods_info['promotion_name1'] = promotion_typeName($goods_promotion_info[0]['promotion_type']);
                            //会员折扣率计算
                            if($goods_promotion_info[0]['promotion_type'] == 30){
                                $discount = number_format($goods_promotion_info[0]['goods_promotion_price'] / $goods_info['goods_price'],2) * 10;
                                $goods_info['hui_discount1'] = $discount.'折';
                            }
                        }
                        $goods_info['goods_promotion_price'] = $goods_promotion_info[0]['goods_promotion_price'];
                        $goods_info['goods_price'] = $goodsInfo['goods_price'];
                        $goods_info['goods_storage'] = $goodsInfo['goods_storage'];
                        $goods_info['cart_num'] = $goodsInfo['cart_num']?$goodsInfo['cart_num']:0;
                        $goods_info['goods_image'] = cthumb($goodsInfo['goods_image']);
                    }else{
                        $goods_info['promotion_name'] = '普通商品';//
                        $goods_info['promotion_type'] = 0;
                        $goods_info['promotion_length'] = 0;//长度
                        $goods_info['goods_promotion_price'] = $goodsInfo['goods_price'];
                        $goods_info['goods_price'] = $goodsInfo['goods_price'];
                        $goods_info['goods_storage'] = $goodsInfo['goods_storage'];
                        $goods_info['cart_num'] = $goodsInfo['cart_num']?$goodsInfo['cart_num']:0;
                        $goods_info['goods_image'] = cthumb($goodsInfo['goods_image']);
                    }
                    unset($goods_info['hui_discount']);
                    $goods_info['member_grade'] = $member_grade;
                    $goods_info['by_post'] = $goodsInfo['by_post'];
                    $goods_info['is_vip_price'] = Model()->table('goods_common')->getfby_goods_commonid($goodsInfo['goods_commonid'],'is_vip_price');
                    $goodsL[] = $goods_info;
                }
            }

            $gList['item_data']['item'] = $goodsL;
            $gList['count'] = count($goodsL);
            $list = array('code' => '400', 'msg' => '查询成功！', 'goodsList' => $gList);
        } else {
            $list = array('code' => '200', 'msg' => '查询失败！', 'goodsList' => []);
        }

        echo json_encode($list);
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
                        $goods_commond_info = Model('goods_common')->table('goods_common')->field('is_vip_price')->where(array('goods_commonid'=>$v['goods_commonid']))->find();
                        $list[$key]['item_data']['item'][$k]['is_vip_price'] = $goods_commond_info['is_vip_price'];
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
        $member_info = $model_member->getMemberInfoByID($member_id,'member_id');
        if(!$member_info){
            die(json_encode(array('code'=>'-2','msg'=>'用户不存在！')));
        }
        $cart_list_v = Model()->table('cart,goods')->field('goods.goods_id')->join('left')->on('cart.goods_id = goods.goods_id')->where(array('cart.buyer_id'=>$member_id,'goods.goods_state'=>1,'goods.goods_verify'=>1,'goods.is_deleted'=>0))->select();

        $cartCount = count($cart_list_v);
        if($cartCount>=0){
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