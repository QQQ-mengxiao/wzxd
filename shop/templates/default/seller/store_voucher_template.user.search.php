<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="tabmenu">
    <?php include template('layout/submenu');?>
</div>
<div class="alert alert-block mt10 mb10">
    <ul>
        <li>用户ID查询</li>
    </ul>
</div>
<form method="get" name="formSearch" id="formSearch">
    <table class="search-form">
        <input type="hidden" id='act' name='act' value='store_voucher' />
        <input type="hidden" value="template_user_search" name="op">
        <input type="hidden" id="form_submit" name="form_submit" value="ok"/>
        <tr>
            <th class="w80">订单编号</th>
            <td><input type="text" id="order_sn" name="order_sn" class="txt" value='<?php echo $_GET['order_sn'];?>'></td></th>
            <th class="w80">收货电话</th>
            <td><input type="text" id="phone" name="phone" class="txt" value='<?php echo $_GET['phone'];?>'></td></th>

            <td class="tc w70"><label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_search'];?>" /></label></td>
        </tr>

    </table>
</form>
  <tbody>
        <input type="hidden" id="form_submit" name="form_submit" value="ok"/>
         <?php if(!empty($output['user_info']) && is_array($output['user_info'])) {?>
         <table class="ncsc-default-table">
            <thead>
            <tr>
                <th ><?php echo '会员ID'; ?></th>
                <th ><?php echo '会员昵称';?></th>
            </tr>
            </thead>
            <tbody>           
                <?php foreach($output['user_info'] as $key =>$v) {?>
                    <tr class="bd-line">
                        <td><?php echo $v['buyer_id'];?></td>
                        <td><?php echo $v['buyer_name'];?></td>
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
            <div class="alert-info">无数据！</div>
        <?php } ?>
           </tbody>
        <div class="bottom">
            <a href="javascript:void(0);" class="submit" onclick="window.location='index.php?act=store_voucher&op=templatelist'" > <?php echo $lang['voucher_template_backlist'];?></a>
        </div>

<link type="text/css" rel="stylesheet" href="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/themes/ui-lightness/jquery.ui.css";?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css"  /><!--MX20170705-->




