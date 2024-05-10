<?php
/**
 * 邀请返利页面
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class inviteControl extends BaseHomeControl{
	public function indexOp(){
		Tpl::showpage('invite');
	}
}
