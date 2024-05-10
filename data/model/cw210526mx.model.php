<?php
/**
 * 云仓
 */
defined('In718Shop') or exit('Access Invalid!');

class cwModel extends Model
{

    public function __construct()
    {
        parent::__construct('cw');
    }

    /**
     * 同步库存接口
     */
    public function cwPlatGoodsSyn($tenantId, $goodsList)
    {
        $data = array();
        $data['tenantId'] = $tenantId;
        $data['goodsList'] = $goodsList;
        $url = 'http://amjggb.natappfree.cc/cwPlatGoodsSyn';
        $res = $this->Post_curls($url, json_encode($data));
        $res = json_decode($res, ture);
//        echo '<pre>';var_dump($res);die;
//        $res = '{"msg":"同步成功！共 2 条数据同步失败！共 1 条数据不正确，错误如下：商品编码8信息有误","code":0,"data":[{"id":19,"goodsPhoto":"http://192.168.1.56:8888/cloud/2021/04/20210428095055000373939019.png","goodsName":"甜玉米","goodsCategoryid":12,"goodsCategory":"玉米","goodsCurrentstate":"1","createTime":"2021-04-28 09:50:57","updateTime":"2021-04-28 14:20:08","createUser":"yuncang","updateUser":"","delFlag":"0","goodsUnit":"个","tenantId":1,"saleInventory":"94","saleTransit":"-212","saleTotal":"-118","goodsCode":"1","goodsSvaluation":"1","goodsPrice":"10","goodsBid":"","goodsNorms":"个","brandCode":"123","brandName":"生鲜","distributeNum":"0"},{"id":20,"goodsPhoto":"http://192.168.1.56:8888/cloud/2021/04/20210428095055000373939019.png","goodsName":"水果玉米","goodsCategoryid":4,"goodsCategory":"玉米","goodsCurrentstate":"1","createTime":"2021-04-28 09:50:57","updateTime":"2021-04-28 14:20:08","createUser":"yuncang","updateUser":"","delFlag":"0","goodsUnit":"个","tenantId":1,"saleInventory":"92","saleTransit":"8","saleTotal":"100","goodsCode":"2","goodsSvaluation":"1","goodsPrice":"5","goodsBid":"","goodsNorms":"个","brandCode":"123","brandName":"生鲜","distributeNum":"0"}]}';
        return $res;
    }

    /**
     * 已支付订单同步接口
     */
    public function cwOrderSubmit($orderList)
    {
//        var_dump($orderList);
//        die;
        $url = 'http://192.168.1.162/cloud-admin/cwApi/cwOrderSubmit';
        $res = $this->Post_curls($url, json_encode($orderList));
        $res = json_decode($res, ture);
		var_dump($res);die;
        $date = date("y-m");
        $dateday = date("y-m-d");
        $path = '../logsmx/' . $date . '/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $filename = $path . $dateday . ".txt";
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            $content = $content . "\r\n------------------------\r\n" . json_encode($res);
            file_put_contents($filename, $content);
        } else {
            file_put_contents($filename, json_encode($res));
        }

    }

    /**
     * 完成订单接口（确认收货的订单）
     */
    public function cwOrderComplete($data)
    {var_dump(json_encode($data));die;
        $url = 'http://a75dds.natappfree.cc/cloud-admin/cwApi/cwOrderComplete';
        $res = $this->Post_curls($url, json_encode($data));
        $res = json_decode($res, ture);
    }

    /**
     * 退款接口
     */
    public function cwOrderCancle($data)
    {
        $url = 'http://a75dds.natappfree.cc/cloud-admin/cwApi/cwOrderCancle';
        $res = $this->Post_curls($url, json_encode($data));
        $res = json_decode($res, ture);
    }


    /**
     * POST请求http接口返回内容
     * @param string $url [请求的URL地址]
     * @param string $post [请求的参数]
     * @return  string
     */
    public function Post_curls($url, $post)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }

    /**
     * POST请求http接口返回内容
     * @param string $url [请求的URL地址]
     * @param string $post [请求的参数]
     * @return  string
     */
    public function Post_curls2($url, $post, $head)
    {
        $header[] = 'Authorization:' . $head['Authorization'];
        // $header[] = 'Accept:application/json';
        // $header[] = 'Content-Type:application/json;charset=utf-8';
        // var_dump($header);die;
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        // curl_setopt($ch, CURLOPT_HEADER,$header);
        // curl_setopt($curl, CURLOPT_HEADER,$head); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $res = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno' . curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $res; // 返回数据，json格式

    }
}
