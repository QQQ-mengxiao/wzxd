<?php
/**
 * 会员卡管理
 * */

defined('In718Shop') or exit('Access Invalid!');
class vip_cardControl extends SystemControl{
    const EXPORT_SIZE = 5000;
	public function __construct(){
		parent::__construct();		
	}
	/**
	 * 会员卡列表页
	 */
	public function indexOp(){
	    $model_vip_card = Model('vip_card');
	    $condition = array();
        if ($_POST['vip_card_num']) {
            $condition['vip_card_num'] = $_POST['vip_card_num'];
        }
        if ($_POST['vip_card_grade'] || $_POST['vip_card_grade']=='0') {
            $condition['vip_card_grade'] = $_POST['vip_card_grade'];
        }
        if ($_POST['is_used'] || $_POST['is_used']=='0') {
            $condition['is_used'] = $_POST['is_used'];
        }
        if ($_POST['used_member_id']) {
            $condition['used_member_id'] = $_POST['used_member_id'];
        }
        $saddtime = strtotime($_POST['stime']);
        $eaddtime = strtotime($_POST['etime']);
        if($saddtime || $eaddtime){
            $condition['use_time'] = array('time',array($saddtime,$eaddtime));
        }

        //获取等级名称
        $model_setting = Model('setting');
        $list_setting = $model_setting->getListSetting();
        $member_grade = $list_setting['member_grade']?unserialize($list_setting['member_grade']):array();
        Tpl::output('member_grade', $member_grade);

        $vip_card_list = $model_vip_card->getVipCardList($condition,10);
        Tpl::output('show_page',$model_vip_card->showpage());
        Tpl::output('vip_card_list',$vip_card_list);

		Tpl::showpage('vip_card.index');
	}

    /**
     * 会员卡发卡
     *
     */
	public function addvipcardOp(){
	    if(chksubmit()){
	        if(!$_POST['vip_card_prefix']){
                showMessage('会员卡号前缀不能为空','','','error');
            }
	        if(!$_POST['count']){
                showMessage('会员卡张数不能为空','','','error');
            }
	        $data = array();
            $data['count'] = $_POST['count'];
	        $data['vip_card_prefix'] = $_POST['vip_card_prefix'];
	        $data['vip_card_grade'] = $_POST['vip_card_grade'];
	        Model('vip_card')->addVipCard($data);//增加会员卡
//            if($result){
                showMessage('发卡成功','');
//            }else{
//                showMessage("发卡失败",'index.php?act=vip_card&op=add','','error');
//            }
        }
        //获取等级名称
        $model_setting = Model('setting');
        $list_setting = $model_setting->getListSetting();
        $member_grade = $list_setting['member_grade']?unserialize($list_setting['member_grade']):array();
        Tpl::output('member_grade', $member_grade);
        Tpl::showpage('vip_card.add');
    }

	/**
	 * 会员卡列表导出
	 */
	public function exportOp(){
        $model_vip_card = Model('vip_card');
        $condition = array();
        if ($_POST['vip_card_num']) {
            $condition['vip_card_num'] = $_POST['vip_card_num'];
        }
        if ($_POST['vip_card_grade'] || $_POST['vip_card_grade']=='0') {
            $condition['vip_card_grade'] = $_POST['vip_card_grade'];
        }
        if ($_POST['is_used'] || $_POST['is_used']=='0') {
            $condition['is_used'] = $_POST['is_used'];
        }
        if ($_POST['used_member_id']) {
            $condition['used_member_id'] = $_POST['used_member_id'];
        }
        $saddtime = strtotime($_POST['stime']);
        $eaddtime = strtotime($_POST['etime']);
        if($saddtime || $eaddtime){
            $condition['use_time'] = array('time',array($saddtime,$eaddtime));
        }

        $vip_card_list = $model_vip_card->getVipCardList($condition,'','',self::EXPORT_SIZE);

		if (!is_numeric($_POST['curpage'])){
			$count = $model_vip_card->getVipCardCount($condition);
			$array = array();
			if ($count > self::EXPORT_SIZE ){	//显示下载链接
				$page = ceil($count/self::EXPORT_SIZE);
				for ($i=1;$i<=$page;$i++){
					$limit1 = ($i-1)*self::EXPORT_SIZE + 1;
					$limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
					$array[$i] = $limit1.' ~ '.$limit2 ;
				}
				Tpl::output('list',$array);
				Tpl::showpage('export.excel');
			}else{	//如果数量小，直接下载
				$this->createExcel($vip_card_list);
			}
		}else{	//下载
			$this->createExcel($vip_card_list);
		}
	}

	/**
	 * 生成excel
	 *
	 * @param array $data
	 */
	private function createExcel($data = array()){
		import('libraries.excel');
		$excel_obj = new Excel();
		$excel_data = array();
		//设置样式
		$excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
		//header
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'会员卡等级');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'会员卡号');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'会员卡密码');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'是否激活');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'用户ID');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'激活时间');
		$stage_arr = Model('exppoints')->getStage();

        $model_setting = Model('setting');
        $list_setting = $model_setting->getListSetting();
        $member_grade = $list_setting['member_grade']?unserialize($list_setting['member_grade']):array();
		foreach ((array)$data as $k=>$v){
			$tmp = array();
			$tmp[] = array('data'=>$member_grade[$v['vip_card_grade']]['level_name']);
			$tmp[] = array('data'=>$v['vip_card_num']);
			$tmp[] = array('data'=>$v['vip_card_pwd']);
			$tmp[] = array('data'=>$v['is_used']?'已激活':'未激活');
			$tmp[] = array('data'=>$v['used_member_id']);
			$tmp[] = array('data'=>$v['use_time']?date('Y-m-d H:i:s',$member_grade['use_time']):'');
			$excel_data[] = $tmp;
		}
		$excel_data = $excel_obj->charset($excel_data,CHARSET);
		$excel_obj->addArray($excel_data);
		$excel_obj->addWorksheet($excel_obj->charset('会员卡明细',CHARSET));
		$excel_obj->generateXML($excel_obj->charset('会员卡明细',CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
	}
	/**
     * 删除会员卡
     */
	public function del_vip_cardOp(){
        if (empty($_GET['vip_card_id'])) {
            showMessage('参数错误', '', 'html', 'error');
        }

        Model('vip_card')->delVipCarddById($_GET['vip_card_id']);

        $this->log("删除会员卡（#会员卡ID: {$_GET['vip_card_id']}）");

        showMessage('操作成功', getReferer());
    }
}
