<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="eject_con">
  <div id="warning" class="alert alert-error"></div>
  <form method="post" action="index.php?act=store_order&op=edit_address_save" id="form" target="_parent">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" value="<?php echo $output['address_info']['city_id'];?>" name="city_id" id="_area_2">
    <input type="hidden" value="<?php echo $output['address_info']['area_id'];?>" name="area_id" id="_area">
    <input type="hidden" name="order_id" value="<?php echo $output['order_info']['order_id'];?>" />
    <dl>
      <dt>收货人<?php echo $lang['nc_colon'];?></dt>
      <dd>
        <input type="text" class="text w300" name="reciver_name" value="<?php echo $output['order_info']['extend_order_common']['reciver_name'];?>"/>
      </dd>
    </dl>
    <dl>
      <dt>联系电话<?php echo $lang['nc_colon'];?></dt>
      <dd>
        <input type="text" class="text w300" name="phone" value="<?php echo $output['order_info']['extend_order_common']['reciver_info']['phone'];?>"/>
      </dd>
    </dl>
    <dl>
      <dt>自提地址<?php echo $lang['nc_colon'];?></dt>
      <dd>
        <div>
          <select name="address" class='text w300'>
        <?php foreach($output['address_list'] as $val) { ?>
            <option <?php if($output['order_info']['extend_order_common']['reciver_ziti_id'] == $val['address_id']){?>selected<?php }?> value="<?php echo $val['address_id']; ?>"><?php echo $val['seller_name']; ?></option>
        <?php } ?>
		</select>
        </div>
      </dd>
    </dl>
    <dl>
      <dt>详细地址<?php echo $lang['nc_colon'];?></dt>
      <dd>
        <input class="text w300" type="text" name="mall_info" value="<?php echo @$output['order_info']['extend_order_common']['mall_info']?>"/>
      </dd>
    </dl>
    
    <div class="bottom">
      <label class="submit-border"><input type="submit" nctype="edit_address_save_submit" class="submit" value="<?php echo $lang['nc_common_button_save'];?>" /></label>
    </div>
  </form>
</div>
<script>
var SITEURL = "<?php echo SHOP_SITE_URL; ?>";
$(document).ready(function(){
	$("#region").nc_region();
	$('input[nctype="edit_address_save_submit" ]').click(function(){
		if ($('#form').valid()) {
			ajaxpost('form', '', '', 'onerror');
		}
	});
    $('#form').validate({
        errorLabelContainer: $('#warning'),
        invalidHandler: function(form, validator) {
           var errors = validator.numberOfInvalids();
           if(errors)
           {
               $('#warning').show();
           }
           else
           {
               $('#warning').hide();
           }
        },
        rules : {
            reciver_name : {
                required : true
            },
			mall_info : {
                required : true
            },
			phone : {
                required : true
            }
        },
        messages : {
            reciver_name : {
                required : '<i class="icon-exclamation-sign">收货人不能为空</i>'
            },
            mall_info : {
                required : '<i class="icon-exclamation-sign">详细地址不能为空</i>'
            },
            phone : {
                required : '<i class="icon-exclamation-sign">联系方式不能为空</i>'
            }
        }
    });
});
</script> 
