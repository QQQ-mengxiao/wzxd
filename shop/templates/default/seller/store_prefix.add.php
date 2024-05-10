<?php defined('In718Shop') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<div class="ncsc-form-default">
    <?php if(empty($output['prefix_info'])) { ?>
    <form id="add_form" action="index.php?act=store_prefix&op=prefix_save" method="post">
    <?php } else { ?>
    <form id="add_form" action="index.php?act=store_prefix&op=prefix_edit_save" method="post">
        <input type="hidden" name="prefix_id" value="<?php echo $output['prefix_info']['prefix_id'];?>">
        <?php if(!empty($output['search_name'])) { ?>
        <input type="hidden" name="search_name" value="<?php echo $output['search_name'];?>">
        <?php } ?>
    <?php } ?>
    <dl>
      <dt><i class="required">*</i>前缀名称<?php echo $lang['nc_colon'];?></dt>
      <dd>
          <input id="prefix_name" name="prefix_name" type="text"  maxlength="26" class="text w400" value="<?php echo empty($output['prefix_info'])?'':$output['prefix_info']['prefix_name'];?>"/>
          <span></span>
      </dd>
    </dl>
    <dl>
      <dt>备注<?php echo $lang['nc_colon'];?></dt>
      <dd>
          <input id="prefix_explain" name="prefix_explain" type="text"  maxlength="25" class="text w400" value="<?php echo empty($output['prefix_info'])?'':$output['prefix_info']['prefix_explain'];?>"/>
          <span></span>
      </dd>
    </dl>
    <div class="bottom">
      <label class="submit-border"><input id="submit_button" type="submit" class="submit" value="<?php echo $lang['nc_submit'];?>"></label>
    </div>
  </form>
</div>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css"  />
<script>
$(document).ready(function(){
    //页面输入内容验证
    $("#add_form").validate({
        errorPlacement: function(error, element){
            var error_td = element.parent('dd').children('span');
            error_td.append(error);
        },
        onfocusout: false,
    	submitHandler:function(form){
    		ajaxpost('add_form', '', '', 'onerror');
    	},
        rules : {
            buy_deliver_name : {
                required : true
            }
        },
        messages : {
            buy_deliver_name : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['buy_deliver_name_error'];?>'
            }
        }
    });
});
</script>
