<?php
/**
  * APP分销
  */

defined('In718Shop') or exit('Access Invalid!');

class mb_fenxiaoControl extends SystemControl{
    public $model;
	public function __construct(){
		parent::__construct();
		$this->model = Model('fenxiao');

	}

	/*
	 * 参数列表及编辑
	 * */
    public function fx_indexOp(){
        $fenxiao_info = $this->model->dataQuery();
        //var_dump($fenxiao_info);die;
        Tpl::output('fenxiao',$fenxiao_info);
    	Tpl::showpage('mb_fenxiao.manage');
    }

    /*
     * 设置保存
     * */
    public function setting_saveOp(){
        $data = $_POST;
        //var_dump($data);die;
        $tmp = [];
        foreach($data as $k=>$v){
            if($v) $tmp[$k] = trim($v);
        }
        if($tmp['fenxiao_con']==2) $tmp['fenxiao_con_val'] = serialize(explode(';',$tmp['fenxiao_con_val']));
        //$update_array['site_status'] = $_POST['site_status'];
        $res = $this->model->dataUpdate($tmp);
        //var_dump($res);die;
        if($res){
            $this->log('更新分销设置',null);
            showMessage('保存成功','','html','succ');
        }
    }
  }



























