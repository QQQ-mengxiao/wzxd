<?php
/**
 * 接口父类
 *
 ***/

defined('In718Shop') or exit('Access Invalid!');


class BaseControl{
	public function _empty()
	    {
	        $res =[
	            "code"=>"400",
	            "msg"=>"访问页面不存在",
	        ];
	        return json_encode($res);
	    }
		/**
		**返回结果格式化json
		**@params code 状态
		**@params msg  提示
		**@params data 结果
		**/
		public function returnMsg($code,$msg='',$data){

			$return_data['code']=$code;
			$return_data['msg']=$msg;
			/*3-DES加密模块需要的话在这加*/
	       	// $base64Sign =encrypt(json_encode($data));//加密
			$return_data['data']=$data;
			return json_encode($return_data,320);
            
		}
		/**
		**返回结果格式化数组
		**@params code 状态
		**@params msg  提示
		**@params data 结果
		**/
		public function returnArray($code,$msg='',$data){
			$return_data['code']=$code;
			$return_data['msg']=$msg;
			$return_data['data']=$data;
			return $return_data;	
		}
		
		// 对二维数组进行指定key排序 $arr 二维数组 ，$shortKey 需要排序的列，$short 排序方式 $shortType 排序类型
    public function multi_array_sort($arr, $shortKey, $short = SORT_ASC, $shortType = SORT_REGULAR)
    {
        foreach ($arr as $key => $data) {
            $name[$key] = $data[$shortKey];
        }
        array_multisort($name, $shortType, $short, $arr);
        return $arr;
    }


}


