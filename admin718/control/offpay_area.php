<?php
/**
  * 货到付款地区设置
  */

defined('In718Shop') or exit('Access Invalid!');

class offpay_areaControl extends SystemControl {
	public function __construct(){
		parent::__construct();
	}

	public function indexOp() {
	    $model_parea = Model('offpay_area');
	    $model_area = Model('area');
	    if (!defined('DEFAULT_PLATFORM_STORE_ID')) {
	        showMessage('请系统管理员配置完自营店铺后再设置货到付款','index.php?act=dashboard&op=aboutus','html','error',1,5000);
	    }
	    $store_id = DEFAULT_PLATFORM_STORE_ID;
	    if (chksubmit()) {
	        if (!preg_match('/^[\d,]+$/',$_POST['county'])) {
	            $_POST['county'] = '';
	        }
	        //内置自营店ID
	        $area_info = $model_parea->getAreaInfo(array('store_id'=>$store_id));
            $data = array();
            $county = trim($_POST['county'],',');
	        // 地区修改
			$county_array = explode(',',$county);
			$all_array = array();
			
			if(!empty($_POST['province']) && is_array($_POST['province'])){
				foreach($_POST['province'] as $v){
					$all_array[$v] = $v;
				}
			}
			
			if(!empty($_POST['city']) && is_array($_POST['city'])){
				foreach($_POST['city'] as $v){
					$all_array[$v] = $v;
				}
			}
			
			if(!empty($_POST['city']) && is_array($_POST['city'])){
				foreach($_POST['city'] as $v){
					$all_array[$v] = $v;
				}
			}
			
			foreach($county_array as $pid){
				$all_array[$pid] = $pid;
				$temp = $model_area->getChildsByPid($pid);
				if(!empty($temp) && is_array($temp) ){
					foreach($temp as $v){
						$all_array[$v] = $v;
					}
				}
			}
			
			$all_array = array_values($all_array);
            $data['area_id'] = serialize($all_array);
	        if (!$area_info) {
	            $data['store_id'] = $store_id;
	            $result = $model_parea->addArea($data);
	        } else {
	            $result = $model_parea->updateArea(array('store_id'=>$store_id),$data);
	        }
	        if ($result) {
	            showMessage('保存成功');
	        } else {
	            showMessage('保存失败','','html','error');
	        }
	    }
	    //取出支持货到付款的县ID及上级市ID
        $parea_info = $model_parea->getAreaInfo(array('store_id'=>$store_id));
        if (!empty($parea_info['area_id'])) {
            $parea_ids = @unserialize($parea_info['area_id']);
        }
        if (empty($parea_ids)) {
            $parea_ids = array();
        }
		
        //取出支持货到付款县ID的上级市ID
        $city_checked_child_array = array();
	    // 地区修改
        $county_array = $model_area->getAreaList(array('area_deep'=>3),'area_id,area_parent_id');
        foreach ($county_array as $v) {
            if (in_array($v['area_id'],$parea_ids)) {
                $city_checked_child_array[$v['area_parent_id']][] = $v['area_id'];
            }
        }
        Tpl::output('city_checked_child_array',$city_checked_child_array);
        //市级下面的县是不是全部支持货到付款，如果全部支持，默认选中，如果其中部分县支持货到付款，默认不选中但显示一个支付到付县的数量

        //格式 city_id => 下面支持到付的县ID数量
        $city_count_array = array();
        //格式 city_id => 是否选中true/false
        $city_checked_array = array();
        $list = $model_area->getAreaList(array('area_deep'=>3),'area_parent_id,count(area_id) as child_count','area_parent_id');
        foreach ($list as $k => $v) {
            $city_count_array[$v['area_parent_id']] = $v['child_count'];
        }
        foreach ($city_checked_child_array as $city_id => $city_child) {
            if (count($city_child) > 0) {
                if (count($city_child) == $city_count_array[$city_id]) {
                    $city_checked_array[$city_id] = true;
                }
            }
        }
        Tpl::output('city_checked_array',$city_checked_array);

        //取得省级地区及直属子地区(循环输出)
        require(BASE_DATA_PATH.'/area/area.php');

		// 地区修改 修改地区从3级变成5级，以及N级引发的错误 
		$province_array = array();
		foreach ($area_array as $k => $v) {
        	if ($v['area_parent_id'] == '0') {
        	    $province_array[$k] = $k;
        	}
        }
		
		foreach ($area_array as $k => $v) {
        	if ($v['area_parent_id'] != '0' ){
				if(in_array($v['area_parent_id'],$province_array)) {
        			$area_array[$v['area_parent_id']]['child'][$k] = $v['area_name'];
        		}
				unset($area_array[$k]);
        	}
        }
		
        Tpl::output('province_array',$area_array);

        //计算哪些省需要默认选中(即该省下面的所有县都支持到付，即所有市都是选中状态)
        $province_array = $area_array;
        foreach ($province_array as $pid => $value) {
        	if (is_array($value['child'])) {
        	    foreach ($value['child'] as $k => $v) {
        	    	if (!array_key_exists($k, $city_checked_array)) {
        	    	    unset($province_array[$pid]);
        	    	    break;
        	    	}
        	    }
        	}
        }
        Tpl::output('province_checked_array',$province_array);

	    Tpl::showpage('offpay_area.index');
	}
}
