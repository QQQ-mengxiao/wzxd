<?php
/**
 * 我的地址
 *
 * 
 *
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class stockModel extends Model {
    
       public function __construct(){
        parent::__construct('stock');
    }
     /**
     * 登录
     */
    public function login($username,$password,$url){
        $data = array();
        $data['username'] =$username;//账户名
        $data['password'] = $password;//密码
        $url = 'http://'.$url.'/gunsApi/auth';//
        // var_dump($data);die;
		$res =$this->Post_curls($url, $data);
		$res=json_decode($res,ture);
        return $res;
    }
     /**
     * 根据商品编码以及仓库编码库存
     */
    public function kuncun($array,$url){
		// $header[] = 'Authorization:'.'Bearer '.$array['token'];
		// // $header[] = 'Accept:application/json';
		// // $header[] = 'Content-Type:application/json;charset=utf-8';

    	 $head = array();
    	  $head['Authorization'] ='Bearer '.$array['token'];//token
        $data = array();
        // $data['Authorization'] ='Bearer '.$array['token'];//token
        $data['goodsCodeList'] =  $array['goodsCodeList'];//商品编码数组
        $data['warehousCode'] = $array['WarehouseCode'];//仓库编码
        // var_dump($head);die;
        // var_dump($data);die;
        $url ='http://'.$url.'/gunsApi/warehouseGoods/getWarehouseInventoryByGoodsCode';//
        // var_dump($url);die;
		$res =$this->Post_curls2($url, $data,$head);
		$res=json_decode($res,ture);
        return $res;
    }
     /**
     * 根据仓库id同步库存库存
     */
    public function tongbu_stockByid($storage_id)
    {
        $storage_info = Model('storage')->getStorageInfo(array('storage_id' => $storage_id));
        $res = $this->login($storage_info['storage_username'], $storage_info['storage_password'], $storage_info['storage_url']);
        $data = array();
        $data['token'] = $res['token'];//token
        $data['WarehouseCode'] = $res['WarehouseCode'];//仓库编码

        //根据仓库编码获取商品编码
        $address_list = Model('daddress')->getAddressList(array('storage_id' => $storage_id));
        foreach ($address_list as $k => $v) {
            $shipper_id_array[] = $v['address_id'];
        }

        $condion = array();
        $condion['goods_shipper_id'] = array('in', $shipper_id_array);
        $model_goods = Model('goods');
        $goods_list = $model_goods->getGoodsList($condion, 'goods_serial', 0);
        foreach ($goods_list as $key1 => $value1) {
            $goods_code_info[] = $value1['goods_serial'];
        }
        $goods_code = implode(',', array_filter($goods_code_info));//去空

        //$goods_code写入storage_log表
        if(empty($_SESSION['member_id'])){
        	$member_id=0;
        }
        $model_storage_log = Model('storage_log');
        $storage_log_info = array(
            'goods_serial_all' => $goods_code,
            'member_id' =>$member_id,
            'store_id' => $storage_info['store_id'],
            'addtime' => time(),
            'storage_id' => $storage_id
        );
        $storage_log_id = $model_storage_log->addStorageLog($storage_log_info);

        $data['goodsCodeList'] = $goods_code;
        $res = $this->kuncun($data, $storage_info['storage_url']);
        if ($res['code'] == 200) {
            $goods_serial_succ = array();
            $goods_serial_fail = array();
            foreach ($res['data']['WarehouseGoodsList'] as $k1 => $v1) {
                //更新商品表库存
                $goods_storage_change = $model_goods->editGoods(array('goods_storage' => $v1['saleInventory']), array('goods_serial' => $v1['goodsCode']));
                if ($goods_storage_change) {//更新成功，即同步成功
                    $goods_serial_succ[] = $v1['goodsCode'];
                } else {
                    $goods_serial_fail[] = $v1['goodsCode'];
                }
            }
            //更新仓库日志
            if ($goods_serial_succ) {
                $storage_log_edit_info['goods_serial_succ'] = implode(',', $goods_serial_succ);
            } else {
                $storage_log_edit_info['goods_serial_succ'] = '';
            }
            $goods_serial_diff = array_diff($goods_code_info,$goods_serial_succ);
            $goods_serial_fail_info = array_merge($goods_serial_diff,$goods_serial_fail);//同步失败的数据加上更新商品表时出错的数据
            $goods_serial_fail_infos = array_filter(array_unique($goods_serial_fail_info));//数组去空去重
//            ob_start();
//            var_dump($goods_serial_diff);
//            $result = ob_get_clean();
//            file_put_contents('C:\Users\Administrator\Desktop\abcd.txt', $result);die;
            if ($goods_serial_fail_infos) {
                $storage_log_edit_info['goods_serial_fail'] = implode(',', $goods_serial_fail_infos);
            } else {
                $storage_log_edit_info['goods_serial_fail'] = '';
            }
            $storage_log_edit_info['state'] = 1;
            // var_dump($storage_log_id);die;
            $storage_result = $model_storage_log->editStorageLog($storage_log_edit_info,array('storage_log_id'=>$storage_log_id,'storage_id'=>$storage_id));
            if($storage_result){//库存同步成功
                return 1;
            }else{//库存同步失败
                return 2;
            }
        } else {
            $storage_log_edit_info['state'] = 0;
            $model_storage_log->editStorageLog($storage_log_edit_info,array('storage_log_id'=>$storage_log_id,'storage_id'=>$storage_id));
            return 3;
        }
    }
	
	
    /**
     * 根据goods_commonid同步库存库存
     */
    public function tongbu_stockBygoods_commonid($goods_commonid)
    {
        $model_goods = Model('goods');
        $model_daddress = Model('daddress');
        $model_storage = Model('storage');

        $deliverer_id = $model_goods->table('goods_common')->getfby_goods_commonid($goods_commonid,'deliverer_id');
        $storage_id = $model_daddress->getfby_address_id($deliverer_id,'storage_id');
        $storage_info = $model_storage->getStorageInfo(array('storage_id' => $storage_id));
        $res = $this->login($storage_info['storage_username'], $storage_info['storage_password'], $storage_info['storage_url']);

        $data = array();
        $data['token'] = $res['token'];//token
        $data['WarehouseCode'] = $res['WarehouseCode'];//仓库编码

        $condion = array();
        $condion['deliverer_id'] = $deliverer_id;
        $condion['goods_commonid'] = $goods_commonid;
        $goods_list = $model_goods->getGoodsList($condion, 'goods_serial', 0);

        foreach ($goods_list as $key => $value) {
            $goods_code_info[] = $value['goods_serial'];
        }
        $goods_code = implode(',', array_filter($goods_code_info));//去空

        //$goods_code写入storage_log表
        $model_storage_log = Model('storage_log');
        $storage_log_info = array(
            'goods_serial_all' => $goods_code,
            'member_id' => $_SESSION['member_id'],
            'store_id' => $model_goods->table('goods_common')->getfby_goods_commonid($goods_commonid,'store_id'),
            'addtime' => time(),
            'storage_id' => $storage_id
        );
        $storage_log_id = $model_storage_log->addStorageLog($storage_log_info);

        $data['goodsCodeList'] = $goods_code;
        $res = $this->kuncun($data, $storage_info['storage_url']);
        if ($res['code'] == 200) {
            $goods_serial_succ = array();
            $goods_serial_fail = array();
            foreach ($res['data']['WarehouseGoodsList'] as $k => $v) {
                //更新商品表库存
                $goods_storage_change = $model_goods->editGoods(array('goods_storage' => $v['saleInventory']), array('goods_serial' => $v['goodsCode']));
                if ($goods_storage_change) {//更新成功，即同步成功
                    $goods_serial_succ[] = $v['goodsCode'];
                } else {
                    $goods_serial_fail[] = $v['goodsCode'];
                }
            }
            //更新仓库日志
            if ($goods_serial_succ) {
                $storage_log_edit_info['goods_serial_succ'] = implode(',', $goods_serial_succ);
            } else {
                $storage_log_edit_info['goods_serial_succ'] = '';
            }
            $goods_serial_diff = array_diff($goods_code_info,$goods_serial_succ);
            $goods_serial_fail_info = array_merge($goods_serial_diff,$goods_serial_fail);//同步失败的数据加上更新商品表时出错的数据
            $goods_serial_fail_infos = array_filter(array_unique($goods_serial_fail_info));//数组去空去重

            if ($goods_serial_fail_infos) {
                $storage_log_edit_info['goods_serial_fail'] = implode(',', $goods_serial_fail_infos);
            } else {
                $storage_log_edit_info['goods_serial_fail'] = '';
            }
            $storage_log_edit_info['state'] = 1;
            $storage_result = $model_storage_log->editStorageLog($storage_log_edit_info,array('storage_log_id'=>$storage_log_id,'storage_id'=>$storage_id));
            if($storage_result){//库存同步成功
                return 1;
            }else{//库存同步失败
                return 2;
            }
        } else {
            $storage_log_edit_info['state'] = 0;
            $model_storage_log->editStorageLog($storage_log_edit_info,array('storage_log_id'=>$storage_log_id,'storage_id'=>$storage_id));
            return 3;
        }
    }

	
    /**
     * POST请求http接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $post [请求的参数]
     * @return  string
     */
    public function Post_curls($url, $post)
    {

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
        	// curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: ".$head['Authorization']));
        // curl_setopt($ch, CURLOPT_HEADER,$header);
        curl_setopt($curl, CURLOPT_HEADER,0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $res = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $res; // 返回数据，json格式
 
    }
    /**
     * POST请求http接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $post [请求的参数]
     * @return  string
     */
    public function Post_curls2($url, $post,$head)
    {
        $header[] = 'Authorization:'.$head['Authorization'];
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
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $res; // 返回数据，json格式
 
    }
}
