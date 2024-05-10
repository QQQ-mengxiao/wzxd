<?php
defined('In718Shop') or exit('Access Invalid!');

/*
 *一卡通会员管理
 */
class member_cardControl extends SystemControl{
	public function __construct(){
		parent::__construct();
		Language::read('member');
	}
    //首页列表及搜索
    public function indexOp(){
    	$model_member=Model('member');
    	$model_card=Model('card');
    	// $condition['member_id']=$_POST['member_id'];
        $con=array();
    	if($_GET['search_field_value']!=''){
    		switch($_GET['search_field_name']){
    			case 'member_id': 
    			    //区分类似000016和16的问题
                    if(strlen($_GET['search_field_value'])<6){
    			        $con['member_id']=trim($_GET['search_field_value']);}
    			    else{$con['member_id']='';}
    			break;
    			case 'cardno': 
    			    $con1['cardno']=trim($_GET['search_field_value']).'';
                    $member_id=Model()->table('member_card')->where($con1)->find();
                    $con['member_id']=$member_id['member_id'];
                break;
    		}
    	}
    	// $sql="SELECT * FROM `718shop_member_card` where member_id";
    	$member=Model()->table('member_card')->field('*')->where($con)->limit(10000)->select(); 
    	$cardno=array();
    	$card_info=array();
    	foreach($member as $key=>$val){
    		$info[$key]=$val['member_id'];
    	}
    	$con['member_id']=array('in',$info);
    	$member_list=$model_member->table('member')->where($con)->page(10)->order('member_id desc')->select();
        if (is_array($member_list)){
			foreach ($member_list as $k=> $v){
				$member_list[$k]['member_time'] = $v['member_time']?date('Y-m-d H:i:s',$v['member_time']):'';
				$member_list[$k]['member_login_time'] = $v['member_login_time']?date('Y-m-d H:i:s',$v['member_login_time']):'';
				$member_list[$k]['member_grade'] = ($t = $model_member->getOneMemberGrade($v['member_exppoints'], false, $member_grade))?$t['level_name']:'';
			}
		}
    	foreach($member_list as $k=>$v){
    		$member_id=$v['member_id'];
    		$card_no=Model()->table('member_card')->where(array('member_id'=>$member_id))->find();
    		$card_info=$model_card->getMemberCardInfo($card_no['cardno']);
    		$member_list[$k]['cardno']=$card_info['Sno'];
    		$member_list[$k]['balance']=ncPriceFormat($card_info['Balance']);
    	}
    	Tpl::output('member',$member);
    	Tpl::output('card_info',$card_info);
        Tpl::output('member_list',$member_list);
        Tpl::output('page',$model_member->showpage());
    	Tpl::showpage('member_card.index');
    }

    //解绑会员
	public function editOp(){
		$lang=Language::getLangContent();
        $con=array();
        $insert=array();
        $con['member_id']=$_POST['member_id'];
        $cardno=$_POST['cardno'];
        $insert['member_id']=$con['member_id'];
        $insert['log_time'] =time();
        $insert['log_desc'] ='解绑卡号'.$cardno;
        $admin_info=$this->getAdmin();
        $insert['log_admin']=$admin_info['admin_name'];
        $result=Model()->table('member_card')->where($con)->delete();
        $result1=Model()->table('member_card_log')->insert($insert);
        if(!$result1){
        	showMessage('更新日志表失败','error');
        }
        echo $result;
	}

	// 新增会员	 
	public function member_addOp(){
		$lang=Language::getLangContent();
		$model_member=Model('member');
		if (chksubmit()){
			$obj_validate = new Validate();
			$obj_validate->validateparam = array(
			    array("input"=>$_POST["member_id"], "require"=>"true", "message"=>'会员ID不能为空'),
			    array("input"=>$_POST["cardno"], "require"=>"true", "message"=>'一卡通卡号不能为空')
			);
			$error = $obj_validate->validate();
			if ($error != ''){
				showMessage($error);
			}else {
				// try{              //这里用事务保存不了数据，把添加会员的代码写在model里应该可以
					$model=Model();
                    // $model->beginTransaction();
				    $insert_array = array();
				    $insert_array['member_id']	= trim($_POST['member_id']);
				    $insert_array['cardno']	= trim($_POST['cardno']);
                    // $insert_array['status'] = trim($_POST['status']);
				    $insert_array['status']	= 1;
                    $result = $model->table('member_card')->insert($insert_array);
                    //插入绑定日志表
                    $insert=array();
			        $insert['member_id']=$_POST['member_id'];
			        $insert['log_time']=time();
			        $insert['log_desc']='新增绑定卡号'.$insert_array['cardno'];
		            $admin_info=$this->getAdmin();
		            $insert['log_admin']=$admin_info['admin_name'];
		            $result1=$model->table('member_card_log')->insert($insert);
		            if(!$result1){
		    	        throw new Exception('更新日志表失败');
		            }
				    if($result){
					    $url = array(
					      array('url'=>'index.php?act=member_card&op=index','msg'=>'返回一卡通会员列表'),
					      array('url'=>'index.php?act=member_card&op=member_add','msg'=>'继续新增会员'),
					    );
					    // $this->log(L('nc_add,member_index_name').'[	'.$_POST['member_name'].']',1);//admin_log表记录
					    showMessage($lang['member_add_succ'],$url);                  
				    }else {
					   throw new Exception($lang['member_add_fail']);
				    }
				    // $model->commit();
			    // }
			    // catch (Exception $e){
			    	// $model->rollback();
			    // }
			}				
		}
		Tpl::showpage('member_card.add');
	}

	/**
	 * ajax操作
	 */
	public function ajaxOp(){
		switch ($_GET['branch']){
            //验证会员ID
			case 'check_member_id':
				$condition['member_id']	= $_GET['member_id'];
				// $list = $model_member->getMemberInfo($condition);
				$list=Model()->table('member_card')->where($condition)->find();
				if (empty($list)){	echo 'true'; exit; }
				else { echo 'false'; exit; }
				break;
		    //验证一卡通卡号
			case 'check_cardno':			
				$condition['cardno'] = $_GET['cardno'];
				$list=Model()->table('member_card')->where($condition)->find();
				if (empty($list)){	echo 'true'; exit; }
				else { echo 'false'; exit;	}
				break;
		}
	}

    //批量导入会员
	public function batch_addOp(){
		Tpl::showpage('member_batch.add');
	}

	public function batch_add_handle1Op(){
		$dir=dirname(dirname(__FILE__));
        $upload_folder=$dir."/upload/";
        $file=$_FILES['upfile'];
        if(!file_exists($upload_folder)){ //file_exists() 函数检查文件或目录是否存在。  
            mkdir($upload_folder);  //mkdir() 函数创建目录。
        }
        $filename = $file["tmp_name"];
        $pinfo = pathinfo($file["name"]); //pathinfo() 函数以数组或字符串的形式返回关于文件路径的信息。
        $ftype = $pinfo['extension'];  //"extension"在PHP.INI文件里面 因为我们要用到GD库
        $doc_name = $upload_folder.time().".".$ftype;
        // if(file_exists($doc_name) && $overwrite != true){  //判断是否存在同名文件            
        //     exit ('同名文件已经存在了');
        // }
        if(!move_uploaded_file ($filename, $doc_name)){
        	return '移动文件出错';
        	exit;
        }
        $pinfo = pathinfo($doc_name);
        $fname = $pinfo["basename"];
        $msg=$this->readExcel($pinfo['dirname']."/".$pinfo['basename']);  
        Tpl::output('msg',$msg);

	}
	//读取Excel文件
	private function readExcel($path){
	    $dir=dirname(dirname(__FILE__));
	    include_once("$dir/resource/Excel/PHPExcel.php");   //11.15 18:25修改文件权限
	    include_once("$dir/resource/Excel/PHPExcel/IOFactory.php");
        // $this->load("$dir/resource/Excel/PHPExcel.php");    // 11.15 18:07改
        // $this->load("$dir/resource/Excel/PHPExcel/IOFactory.php");
	    $admin_info=$this->getAdmin();
	    $type=explode('.',$path);
	    if($type[1]=='xls'||$type[1]=='XLS'){
		    $data=PHPExcel_IOFactory::createReader('Excel5');
	    }
	    else if($type[1]=='xlsx'||$type[1]=='XLSX'){
		    $data=PHPExcel_IOFactory::createReader('Excel2007');
	    }

        $data->setReadDataOnly(true);   //跳过特殊字符
	    $Excel=$data->load($path);
	    $sheet=$Excel->getSheet(0);
	    $row=$sheet->getHighestRow();
	    $column=$sheet->getHighestColumn();
	    $dataset=array();
	    $insert=array();
	    $log_insert=array();
	    $m=0;//插入成功数
	    $r=0;//数据重复数
	    for($i=2;$i<=$row;$i++){
		    $n=0;
		    for($j='A';$j<=$column;$j++){			
			    $dataset[$n]=$sheet->getCell($j.$i)->getValue();
			    $n++;				
		    }
		    //获取会员ID
            $insert['member_id']=$dataset[2];
            //补全六位卡号
            // $dataset[3]=$dataset[3].'';
            // $card_len=strlen($dataset[3]);
            // if($card_len<6){
            // 	for($l=1;$l<=6-$card_len;$l++){
            // 		$dataset[3]='0'.$dataset[3];
            // 	}
            // }
            //获取卡号，卡号自动补全六位
            // $dataset[3]=str_pad($dataset[3],6,'0',STR_PAD_LEFT);
            $insert['cardno']=$dataset[3].'';
            $insert['status']=1;
            
            //批量导入前的会员id和卡号验证
            $member_id=$insert['member_id'];
            $cardno=$insert['cardno'];
            $sql="SELECT * FROM `718shop_member_card` where member_id=$member_id or cardno=$cardno limit 1";
            $query_res=Model()->query($sql);
            if(!empty($query_res)){
            	$r++;
            	continue;
            }
            $log_insert['member_id']=$insert['member_id'];
            $log_insert['log_time']=time();
            $log_insert['log_desc']='批量导入一卡通会员';
            $log_insert['log_admin']=$admin_info['admin_name'];
            $result=Model()->table('member_card')->insert($insert);
            $result1=Model()->table('member_card_log')->insert($log_insert);
			if($result){  $m++;	}
			if(!$result1){  return '插入日志表失败'; }	    
	    }
	    // print_r($data_excel);
	    unlink($path);
	    return '共'.($row-1).'条会员信息，插入成功'.$m.'条，插入失败<font color="red">'.($row-1-$m).'</font>条，<font color="red">'.$r.'</font>条重复信息';
    }

    //获取管理员信息
	private function getAdmin(){		
		$model=Model('admin');
		$admin=$this->getAdminInfo();
		$con['admin_id']=$admin['id'];
		$admin_info=$model->infoAdmin($con);
		return $admin_info;
	}

    //无插件读取Excel
    public function batch_add_handleOp(){
        $csv=explode('.',$_FILES['upfile']['name']);
        if($csv[1]=='csv'){
            $doc_open=@fopen($_FILES['upfile']['tmp_name'],'rb');
            $i=0;       //用于跳过第一次循环，存储数据总数
            $s=0;       //存储插入成功数
            $r=0;       //存储数据重复数
            while(!feof($doc_open)){
                $data=fgets($doc_open,4096);
                switch(strtoupper($_POST['charset'])){
                    case 'UTF-8':
                    if(strtoupper(CHARSET)!=='UTF-8'){
                        $data=iconv('UTF-8',strtoupper(CHARSET),$data);
                    }
                    break;
                    case 'GBK':
                    if(strtoupper(CHARSET)!=='GBK'){
                        $data=iconv('GBK',strtoupper(CHARSET),$data);
                    }
                    break;
                }
                if($i!=0){
                    if(!empty($data)){
                        $admin_info=$this->getAdmin();
                        $data_arr=array();
                        $insert=array();
                        $data=str_replace('"','',$data);
                        $data_arr=explode(',',$data);
                        //插入数据库的数据
                        $insert['member_id']=$data_arr[2];
                        //卡号6位的补齐
                        $insert['cardno']=str_pad(trim($data_arr[3]),6,'0',STR_PAD_LEFT);
                        $insert['status']=1;
                    
                        //插入前member_id和cardno的验证
                        $member_id=$insert['member_id'];
                        $cardno=$insert['cardno'];
                        $sql="SELECT * FROM `718shop_member_card` where member_id=$member_id or cardno=$cardno limit 1";
                        $check_res=Model()->query($sql);
                        if(!empty($check_res)){
                            $r++;    continue;
                        }
                        //插入member_card_log表
                        $log_insert['member_id']=$insert['member_id'];
                        $log_insert['log_time']=time();
                        $log_insert['log_desc']='批量导入一卡通会员';
                        $log_insert['log_admin']=$admin_info['admin_name'];
                        $insert_res=Model()->table('member_card')->insert($insert);
                        $log_res=Model()->table('member_card_log')->insert($log_insert);
                        if($insert_res){  $s++; }
                        if(!$log_res){  return '插入日志表失败'; }                        
                    }
                }
                $i++;
            }
            $msg='共'.($i-2+$r).'条会员信息，插入成功'.$s.'条，插入失败<font color="red">'.($i-2+$r-$s).'</font>条，<font color="red">'.$r.'</font>条重复信息';
            Tpl::output('msg',$msg);
            Tpl::showpage('member_batch.add');
        }
    }
}
