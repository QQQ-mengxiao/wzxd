<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="tabmenu">
    <?php include template('layout/submenu');?>
</div>
<div class="alert alert-block mt10 mb10">
    <ul>
        <li><?php echo '代金券发放情况';?></li>
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
        <input type="hidden" id="tid" name="tid" value="<?php echo $output['list']['voucher_t_id'];?>"/>
        <input type="hidden" id="form_submit" name="form_submit" value="ok"/>
         <?php if(!empty($output['user_info'])&& is_array($output['user_info'])) {?>
         <table class="ncsc-default-table">
            <thead>
            <tr>
                <th ><?php echo '会员ID'; ?></th>
                <th ><?php echo '会员昵称';?></th>
                <th ><?php echo '联系方式';?></th>
                <th ><?php echo '领取时间';?></th>
                <th ><?php echo '使用状态';?></th>
            </tr>
            </thead>
            <tbody>           
                <?php foreach($output['user_info'] as $key =>$v) {?>
                    <tr class="bd-line">
                        <td><?php echo $v['member_id'];?></td>
                        <td><?php echo $v['member_name'];?></td>
                        <td><?php echo $v['member_mobile']==null?'--空--':$v['member_mobile'];?></td>
                        <td><?php echo date("Y-m-d H:i:s",$v['voucher_start_date']);?></td>
                        <td><?php switch($v['voucher_state']){case 1 : echo '未用'; break;case 2 : echo '已用'; break;case 3 : echo '过期'; break;case 4 : echo '收回'; break;}?></td>
                    </tr>
                <?php unset($user_info);}?>
            
            </tbody>
            <tfoot>
            <?php  if (count($output['list'])>0) { ?>
                <tr>
                    <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
                </tr>
            <?php } ?>
            </tfoot>
        </table>
        <?php }else { ?>
            <div class="alert-info">暂无用户领取此代金券！</div>
        <?php } ?>
        <div class="bottom">
            <a href="javascript:void(0);" class="submit" onclick="window.location='index.php?act=store_voucher&op=templatelist'" > <?php echo $lang['voucher_template_backlist'];?></a>
        </div>

<link type="text/css" rel="stylesheet" href="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/themes/ui-lightness/jquery.ui.css";?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css"  /><!--MX20170705-->



