<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="tabmenu">
    <?php include template('layout/submenu');?>

</div>


  <div class="alert alert-block mt10 mb10">
      <ul>
          <li>1、新增代金券后选择的代金券类型为品类券或商品券可以设置具体规则</li>
          <li>2、可以设置是否包含设置的规则</li>
    </ul>
  </div>

  <table class="ncsc-default-table">
    <thead>
      <tr>

        <th class="w50"></th>
        <th class="tl">代金券名称 </th>
        <th class="tl">代金券模板id</th>
        <th class="w200">代金券类型</th>
        <th class="w200">有效期</th>
        <th class="w100">规则是否包含</th>
         <th class="w100">设置</th>
        <th class="w100"><?php echo $lang['nc_handle'];?></th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($output['list'])>0) { ?>
      <?php foreach($output['list'] as $val) { ?>
      <tr class="bd-line">
        <td class="tl"></td>
        <td class="tl"><?php echo $val['voucher_t_title'];?></td>
        <td class="tl"><?php echo $val['voucher_t_id'];?></td>
         <td class="goods-time"><?php echo $val['voucher_t_typename'];?></td>
        <td class="goods-time"><?php echo date("Y-m-d H:i",$val['voucher_t_start_date']).'~'.date("Y-m-d H:i",$val['voucher_t_end_date']);?></td>
       <td class="goods-time"><?php echo $val['is_usename'];?></td>
       <td>
        <?php if($val['is_use']==1) {?>
 <p><a href="javascript:void(0)" class="ncsc-btn-mini ncsc-btn-orange mt10" nc_type="dialog" dialog_id="seller_order_receive_order" dialog_width="400" dialog_title="是否设置为不包含" uri="index.php?act=store_voucher_type&op=use_set&t_id=<?php echo $val['voucher_t_id']; ?>&is_use=<?php echo $val['is_use']; ?>" id="order<?php echo $val['voucher_t_id']; ?>_action_confirm">设为不包含</a></p>
                <?php }else{?>
               <p><a href="javascript:void(0)" class="ncsc-btn-mini ncsc-btn-orange mt10" nc_type="dialog" dialog_id="seller_order_receive_order" dialog_width="400" dialog_title="是否设置为包含" uri="index.php?act=store_voucher_type&op=use_set&t_id=<?php echo $val['voucher_t_id']; ?>&is_use=<?php echo $val['is_use']; ?>" id="order<?php echo $val['voucher_t_id']; ?>_action_confirm">设为包含</a></p>
                   <?php }?>
      </td>
        <td class="nscs-table-handle">

          //代金券模板有效并且没有领取时可以编辑?>
        		<span>
              
            <?php if($val['voucher_t_type']==1) {?>
        		  <a class="btn-blue" href="index.php?act=store_voucher_type&op=vouchertype1_manage&tid=<?php echo $val['voucher_t_id'];?>">
        		      <i class="icon-edit"></i><p>管理</p>
        		  </a>
        	   </span>
              <?php }else{?>
                <a class="btn-blue" href="index.php?act=store_voucher_type&op=vouchertype2_manage&tid=<?php echo $val['voucher_t_id'];?>">
                  <i class="icon-edit"></i><p>管理</p>
              </a>

            <?php }?>
        </td>
      </tr>
          <?php }?>
           <?php }else{?>
          <tr>
              <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
          </tr>
      <?php } ?>
    </tbody>
      <tfoot>
    <tfoot>
      <?php  if (count($output['list'])>0) { ?>
      <tr>
        <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
      </tr>
      <?php } ?>
    </tfoot>
  </table>
<link type="text/css" rel="stylesheet" href="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/themes/ui-lightness/jquery.ui.css";?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-1.8.17.custom.css"  />
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-timepicker-addon.css"/>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8" ></script>

<script type="text/javascript">
$(document).ready(function(){
	$('#txt_startdate').datepicker();  //日期
	$('#txt_enddate').datepicker();
});
</script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js"></script> 
<script>
$(function (){
  $('input[name="is_default"]').on('click',function(){
    $.get('index.php?act=store_voucher_type&op=use_set&t_id='+$(this).val(),function(result){})
  });
});
</script> 
