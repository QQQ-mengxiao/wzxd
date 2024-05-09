<?php

defined('In718Shop') or exit('Access Invalid!');

class todayhotControl
{
    //今日主推
    const SPECIAL_ID = 2;
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
    public function imageOp()
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
            $goodsList = array('code' => '400', 'msg' => '查询成功！', 'goodsList' => $goodsList[0]);
        } else {
            $goodsList = array('code' => '200', 'msg' => '查询失败！', 'goodsList' => []);
        }

        echo json_encode($goodsList);
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
                        $list[$key]['item_data']['item'][$k]['salenum'] = $goods_detail['goods_info']['goods_salenum']+$goods_detail['goods_info']['goods_presalenum'];
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

}