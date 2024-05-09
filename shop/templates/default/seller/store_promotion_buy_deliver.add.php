<?php defined('In718Shop') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<div class="ncsc-form-default">
    <?php if(empty($output['buy_deliver_info'])) { ?>
    <form id="add_form" action="index.php?act=store_buy_deliver&op=buy_deliver_save" method="post">
    <?php } else { ?>
    <form id="add_form" action="index.php?act=store_buy_deliver&op=buy_deliver_edit_save" method="post">
        <input type="hidden" name="buy_deliver_id" value="<?php echo $output['buy_deliver_info']['buy_deliver_id'];?>">
    <?php } ?>
    <dl>
      <dt><i class="required">*</i>物资小店(即送)<?php echo $lang['nc_colon'];?></dt>
      <dd>
          <input id="buy_deliver_name" name="buy_deliver_name" type="text"  maxlength="25" class="text w400" value="<?php echo empty($output['buy_deliver_info'])?'':$output['buy_deliver_info']['buy_deliver_name'];?>"/>
          <span></span>
        <p class="hint"><?php echo $lang['buy_deliver_name_explain'];?></p>
      </dd>
    </dl>
    <dl>
      <dt>活动标题<?php echo $lang['nc_colon'];?></dt>
      <dd>
          <input id="buy_deliver_title" name="buy_deliver_title" type="text"  maxlength="10" class="text w200" value="<?php echo empty($output['buy_deliver_info'])?'':$output['buy_deliver_info']['buy_deliver_title'];?>"/>
          <span></span>
        <p class="hint"><?php echo $lang['buy_deliver_title_explain'];?></p>
      </dd>
    </dl>
    <dl>
      <dt>活动描述<?php echo $lang['nc_colon'];?></dt>
      <dd>
          <input id="buy_deliver_explain" name="buy_deliver_explain" type="text"  maxlength="30" class="text w400" value="<?php echo empty($output['buy_deliver_info'])?'':$output['buy_deliver_info']['buy_deliver_explain'];?>"/>
          <span></span>
        <p class="hint"><?php echo $lang['buy_deliver_explain_explain'];?></p>
      </dd>
    </dl>
    <dl>
      <dt>自提点<?php echo $lang['nc_colon'];?></dt>
      <dd>
          <?php if (!empty($output['ziti_list'])) {?>
              <select name="ziti_id">
                  <!-- <option value="1"><?php echo '全部商品';//xinjia?></option> -->
                  <?php foreach ($output['ziti_list'] as $val) {?>
                      <option <?php if ($output['buy_deliver_info']['ziti_id'] == $val['address_id']) {?> selected = "selected" <?php }?> value="<?php echo $val['address_id']?>"><?php echo $val['seller_name'];?></option>
                  <?php }?>
              </select>
          <?php }?>
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
