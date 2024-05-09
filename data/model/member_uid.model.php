<?php
/**
 *线下余额模型
 */
defined('In718Shop') or exit('Access Invalid!');

class member_uidModel extends Model {

    //private $url = "http://xls.zhonghaokeji.net/api/smallprogram/center/";//测试：'http://xlsyy.ngrok2.xiaomiqiu.cn/api/smallprogram/center/';//线下接口url
    private $url = "http://xls.zitcloud.cn/api/smallprogram/center/";
    public function __construct(){
        parent::__construct('member_uid');
    }

    /**
     * 绑定线下用户id
     * @param array $data [description]
     */
    public function addUid($data = array())
    {
        return $this->insert($data);
    }

    /**
     * 获取线下用户id
     * @param  [type] $member_id [description]
     * @return [type]            [description]
     */
    public function getUid($member_id)
    {
        $condition = array();
        $condition['member_id'] = $member_id;
        $condition['status'] = 1;
        $result = $this->where($condition)->field('uid')->find();
        if ($result) {
            return $result['uid'];
        }
        return false;
    }
    
    /**
     * 更新信息
     * @param  array  $data      [description]
     * @param  array  $condition [description]
     * @return [type]            [description]
     */
    public function editUid($data = array(),$condition = array())
    {
        return $this->where($condition)->update($data);
    }

    /**
     * 查询线下余额
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function selectBalance($uid)
    {
        $fun = 'selectBalance';
        $data = array('uid' => $uid);
        return $this->sendCurl($fun,$data);
    }

    /**
     * 线下余额支付或退款
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function payOrRefundBalance($data)
    {
        $fun = 'payOrRefundBalance';
        return $this->sendCurl($fun,$data);
    }

    /**
     * 查询消费记录
     */
    public function selectLineRecord($data)
    {
        $fun = 'selectLineRecord';
        return $this->sendCurl($fun,$data);
    }

    /**
     * 验证uid是否合法
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function bindUser($data)
    {
        $fun = 'bindUser';
        return $this->sendCurl($fun,$data);
    }

    private function sendCurl($fun,$data)
    {
        $curl = curl_init();
        $url = $this->url.$fun;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query($data),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //判断返回数据是否json格式
        if (is_null(json_decode($response))){
            return false;
        }
        return json_decode($response,true);
    }
}
