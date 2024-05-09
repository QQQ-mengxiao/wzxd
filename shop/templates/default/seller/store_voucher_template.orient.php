<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="tabmenu">
    <?php include template('layout/submenu');?>
</div>
  <div class="alert alert-block mt10 mb10">
      <ul>
          <li><?php echo '代金券定向发放';?></li>
          <li><?php echo $lang['voucher_template_list_tip1'];?></li>
          <li><?php echo $lang['voucher_template_list_tip2'];?></li>
    </ul>
  </div>
  <table class="ncsc-default-table">
    <thead>
    <tr>
        <th class="w50"></th>
        <th class="tl"><?php echo $lang['voucher_template_title']; ?></th>
        <th class="w200"><?php echo '在积分中心显示';?></th>
        <th class="w100"><?php echo $lang['voucher_template_orderpricelimit'];?></th>
        <th class="w60"><?php echo $lang['voucher_template_price'];?></th>
        <th class="w200"><?php echo $lang['voucher_template_enddate'];?></th>
        <th class="w60"><?php echo $lang['nc_status'];?></th>
    </tr>
    </thead>
      <?php //var_dump($output['display']);?>
    <tbody>
      <tr class="bd-line">
        <td><div class="pic-thumb"><img src="<?php echo $output['list']['voucher_t_customimg'];?>"/></div></td>
          <td class="tl"><?php echo $output['list']['voucher_t_title'];?></td>
          <?php foreach($output['display'] as $key =>$v) {?>
              <?php if($output['list']['voucher_t_display']==$key) {?>
                  <td class="voucher_t_display"><?php echo $v;?></td>
              <?php }?>
          <?php }?>
          <td>￥<?php echo $output['list']['voucher_t_limit'];?></td>
        <td class="goods-price">￥<?php echo $output['list']['voucher_t_price'];?></td>
        <td class="goods-time"><?php echo date("Y-m-d",$output['list']['voucher_t_start_date']).'~'.date("Y-m-d",$output['list']['voucher_t_end_date']-3600*24*1);?></td>
        <td><?php if($output['list']['voucher_t_state']== $output['templatestate_arr']['usable'][0]) echo $output['templatestate_arr']['usable'][1];
                  if($output['list']['voucher_t_state']== $output['templatestate_arr']['disabled'][0]) echo $output['templatestate_arr']['disabled'][1]; ?></td>
      </tr>
    </tbody>
  </table>


<form method="get" name="formSearch" id="formSearch">
    <table class="search-form">
        <input type="hidden" id='act' name='act' value='store_voucher' />
        <input type="hidden" value="templateedit1" name="op">
        <input type="hidden" value="<?php echo $_GET['tid'];?>" name="tid">
        <tr>
            <th><?php echo '注册时间';?></th>
            <td class="w240">
                <input type="text" class="text w70"  readonly="readonly" value="<?php echo $_GET['txt_startdate'];?>" id="txt_startdate" name="txt_startdate"/><label class="add-on">
                    <i class="icon-calendar"></i>
                </label>
                &#8211;
                <input type="text" class="text w70"  readonly="readonly" value="<?php echo $_GET['txt_enddate'];?>" id="txt_enddate" name="txt_enddate"/><label class="add-on">
                    <i class="icon-calendar"></i>
                </label></td>
            <th><select name="search_field_name"  class="240w">
                    <option <?php if(trim($_GET['search_field_name']) == 'member_name'){ ?>selected='selected'<?php } ?> value="member_name"><?php echo '会员名';?></option>
                     <option <?php if(trim($_GET['search_field_name']) == 'member_id'){ ?>selected='selected'<?php } ?> value="member_id"><?php echo '会员ID';?></option>
                </select><td><input type="text" value="<?php echo trim($_GET['search_field_value']);?>" name="search_field_value" class="txt"></td></th>
            <td><?php echo '会员类型';?>
            <?php //var_dump($_GET['serach_member'] == 'member_nobuy');?>
            <th><select name="search_member"  class="240w">
                    <option value="0" <?php if ($_GET['search_member'] == 0) {?>selected="selected"<?php }?>>已消费</option>
                    <?php if($output['isOwnShop']) {?>
                        <option <?php if($_GET['search_member'] == 1){ ?>selected='selected'<?php } ?> value="1"><?php echo '未消费';?></option>
                        <option <?php if($_GET['search_member'] ==2){ ?>selected='selected'<?php } ?> value="2"><?php echo '全部';?></option>
                    <?php }?>

                </select></th></td>
            <td><?php echo '选择会员级别';?>
                <select name="search_grade" class="200w">
                    <option value='-1'>会员级别</option>
                    <?php if ($output['member_grade']){?>
                        <?php foreach ($output['member_grade'] as $k=>$v){?>
                            <option <?php if(isset($_GET['search_grade']) && $_GET['search_grade'] == $k){ ?>selected='selected'<?php } ?> value="<?php echo $k;?>"><?php echo $v['level_name'];?></option>
                        <?php }?>
                    <?php }?>
                </select></td>
            <td class="tc w70"><label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_search'];?>" /></label></td>
            <td><input  type="reset" class="submit"  name="reset"  value="重置"/></td>
        </tr>

    </table>
</form>

<div class="ncsc-form-default">
    <form method="post" action="index.php?act=store_voucher&op=orient" id="myform" name="myform">
        <input type="hidden" id="tid" name="tid" value="<?php echo $output['list']['voucher_t_id'];?>"/>
        <input type="hidden" id="form_submit" name="form_submit" value="ok"/>
        <dl style="display: none">
            <dt><?php echo '用户名'; ?></dt>
            <dd>
                <td class="vatop rowform"><textarea id="user_name" name="user_name" rows="6" class="tarea" ></textarea></td>
            </dd>
        </dl>
        <table class="ncsc-default-table">
            <thead>
            <tr>
                <th class="w50"></th>
                <th class="w50"></th>
                <th class="tl"><?php echo '会员'; ?></th>
                <th class="w200"><?php echo '登陆次数';?></th>
                <th class="w100"><?php echo '最后登录';?></th>
                <th class="w60"><?php echo '积分';?></th>
                <th class="w200"><?php echo '预存款';?></th>
                <th class="w80"><?php echo '经验值';?></th>
                <th class="w60"><?php echo '级别';?></th>
            </tr>
            </thead>
            <?php //var_dump($output['member_list']);?>
            <tbody>
                <?php if(!empty($output['member_list'])&& is_array($output['member_list'])) {?>
                <?php foreach($output['member_list'] as $key =>$v) {?>
                    <tr class="bd-line">
                        <td><input type="checkbox" id="add_id" name='add_id[]' value="<?php echo $v['member_id']; ?>" class="checkitem"></td>
                        <td><div class="pic-thumb"><img src="<?php if ($v['member_avatar'] != ''){ echo UPLOAD_SITE_URL.DS.ATTACH_AVATAR.DS.$v['member_avatar'];}else { echo UPLOAD_SITE_URL.'/'.ATTACH_COMMON.DS.C('default_user_portrait');}?>?<?php echo microtime();?>"  onload="javascript:DrawImage(this,44,44);"/></span></div></td>
                        <td><p class="t1"><strong><?php echo $v['member_name']; ?></strong>(<?php echo '真实姓名'?>: <?php echo $v['member_truename']; ?>) 
                            <?php echo '会员ID'?>:&nbsp;<?php echo $v['member_id']; ?></p>
                            <p><?php echo '注册时间'?>:&nbsp;<?php echo $v['member_time']; ?></p>
                            <div><span class="email" >
                <?php if($v['member_email'] != ''){ ?>
                                    <a href="mailto:<?php echo $v['member_email']; ?>" class=" yes" title="<?php echo $lang['member_index_email']?>:<?php echo $v['member_email']; ?>"><?php echo $v['member_email']; ?></a><?php echo $v['member_email']; ?></span>
                                <?php }else { ?>
                                    <a href="JavaScript:void(0);" class="" title="<?php echo $lang['member_index_null']?>" ><?php echo $v['member_email']; ?></a></span>
                                <?php } ?>
                                <?php if($v['member_ww'] != ''){ ?>
                                    <a target="_blank" href="http://web.im.alisoft.com/msg.aw?v=2&uid=<?php echo $v['member_ww'];?>&site=cnalichn&s=11" class="" title="WangWang: <?php echo $v['member_ww'];?>"><img border="0" src="http://web.im.alisoft.com/online.aw?v=2&uid=<?php echo $v['member_ww'];?>&site=cntaobao&s=2&charset=<?php echo CHARSET;?>" /></a>
                                <?php } ?>
                                <?php if($v['member_qq'] != ''){ ?>
                                    <a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $v['member_qq'];?>&site=qq&menu=yes" class=""  title="QQ: <?php echo $v['member_qq'];?>"><img border="0" src="http://wpa.qq.com/pa?p=2:<?php echo $v['member_qq'];?>:52"/></a>
                                <?php } ?>
                                <!--显示手机号码-->
                                <?php if($v['member_mobile'] != ''){ ?>
                                    <div style="font-size:13px; padding-left:10px">&nbsp;&nbsp;<?php echo $v['member_mobile']; ?></div>
                                <?php } ?>
                            </div></td>
                        <td><?php echo $v['member_login_num']; ?></td>
                        <td><p><?php echo $v['member_login_time']; ?></p>
                            <p><?php echo $v['member_login_ip']; ?></p></td>
                        <td><?php echo $v['member_points']; ?></td>
                        <td><p><?php echo '可用存款';?>:&nbsp;<strong class="red"><?php echo $v['available_predeposit']; ?></strong>&nbsp;<?php echo $lang['currency_zh']; ?></p>
                            <p><?php echo '冻结';?>:&nbsp;<strong class="red"><?php echo $v['freeze_predeposit']; ?></strong>&nbsp;<?php echo $lang['currency_zh']; ?></p>
                        </td>
                        <td><?php echo $v['member_exppoints'];?></td>
                        <td><?php echo $v['member_grade'];?></td>
                    </tr>
                <?php }?>
                <?php }?>
            </tbody>
        </table>
        <dl>
            <dt><?php echo '每人发放代金券张数'; ?></dt>
            <dd>
                <input type="text" class="w300 text" id="txt_voucher_number" name="txt_voucher_number">
                <span></span>
                <td><?php echo '是否选择全部发放';?>
                    <input type="hidden" name="post_data" id="post_data" value="<?=base64_encode(serialize($output['member']))?>" />
                <td><input type="checkbox" id="add" name="add" value="<?php echo '1';?>"></td>
            </dd>
        </dl>
        <?php //var_dump($output['member']);?>

        <td><?php echo '全选';?></td>
        <td class="w50"><input type="checkbox" class="checkall" id="checkallBottom"></td>
        <div class="bottom">
            <td><input id='btn_add' type="button" class="submit" value="<?php echo $lang['nc_submit'];?>" onclick="goo(this);"/><div class="pagination"> <?php if(!empty($output['show_page'])){?><?php echo $output['show_page'];?><?php }?> </div></td>
        </div>

    </form>
</div>
<link type="text/css" rel="stylesheet" href="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/themes/ui-lightness/jquery.ui.css";?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css"  /><!--MX20170705-->
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8" ></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js"></script><!--MX20170705-->
<script type="text/javascript">
$(document).ready(function(){
//	$('#txt_startdate').datepicker();//日期
//	$('#txt_enddate').datepicker();
    $('#txt_startdate').datetimepicker({//MX20170705
        controlType: 'select'
    });//日期
	$('#txt_enddate').datetimepicker({
        controlType: 'select'
    });
});
function goo(obj)
 {
 obj.disabled =true;
 obj.value='正在提交';
 document.getElementById("myform").submit();
 };

</script>


