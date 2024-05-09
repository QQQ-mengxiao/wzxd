<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="eject_con">
  <div id="warning" class="alert alert-error"></div>
  <form method="post" action="index.php?act=store_ziti_address&op=daddress_add" id="ziti_address_form" target="_parent">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" value="<?php echo $output['address_info']['city_id'];?>" name="city_id" id="_area_2">
    <input type="hidden" value="<?php echo $output['address_info']['area_id'];?>" name="area_id" id="_area">
    <input type="hidden" name="address_id" value="<?php echo $output['address_info']['address_id'];?>" />
    <dl>
      <dt><i class="required">*</i>地址名称：</dt>
      <dd>
        <input type="text" class="text" name="seller_name" value="<?php echo $output['address_info']['seller_name'];?>"/>
      </dd>
    </dl>
    <dl>
      <dt><i class="required">*</i><?php echo $lang['store_daddress_location'].$lang['nc_colon'];?></dt>
      <dd>
        <div>
          <input type="hidden" name="region" id="region" value="<?php echo $output['address_info']['area_info'];?>"/>
        </div>
      </dd>
    </dl>
    <dl>
      <dt><i class="required">*</i><?php echo $lang['store_daddress_address'].$lang['nc_colon'];?></dt>
      <dd>
        <input class="text w300" type="text" name="address" value="<?php echo $output['address_info']['address'];?>"/>
        <p class="hint"><?php echo $lang['store_daddress_not_repeat'];?></p>
      </dd>
    </dl>
       <dl>
        <dt><i class="required"></i>自提时间：</dt>
        <dd>
          <?php if (is_array($output['address_info']['week'])){ ?>
              <label><input type="checkbox" value="1" name="week[]" <?php if (in_array("1", $output['address_info']['week'])){echo 'checked';}?> />&nbsp;&nbsp;周一&nbsp;&nbsp;&nbsp;</label>
               <label><input type="checkbox" value="2" name="week[]" <?php if (in_array("2", $output['address_info']['week'])){echo 'checked';}?> />&nbsp;&nbsp;周二&nbsp;&nbsp;&nbsp;</label>
                <label><input type="checkbox" value="3" name="week[]" <?php if (in_array("3", $output['address_info']['week'])){echo 'checked';}?> />&nbsp;&nbsp;周三&nbsp;&nbsp;&nbsp;</label>
                <label><input type="checkbox" value="4" name="week[]" <?php if (in_array("4", $output['address_info']['week'])){echo 'checked';}?> />&nbsp;&nbsp;周四&nbsp;&nbsp;&nbsp;</label>
                <label><input type="checkbox" value="5" name="week[]" <?php if (in_array("5", $output['address_info']['week'])){echo 'checked';}?> />&nbsp;&nbsp;周五&nbsp;&nbsp;&nbsp;</label>
                <label><input type="checkbox" value="6" name="week[]" <?php if (in_array("6", $output['address_info']['week'])){echo 'checked';}?> />&nbsp;&nbsp;周六&nbsp;&nbsp;&nbsp;</label>
                <label><input type="checkbox" value="7" name="week[]" <?php if (in_array("7", $output['address_info']['week'])){echo 'checked';}?> />&nbsp;&nbsp;周日&nbsp;&nbsp;&nbsp;</label>
            <?php }else{ ?>
              <label><input type="checkbox" value="1" name="week[]"  />&nbsp;&nbsp;周一&nbsp;&nbsp;&nbsp;</label>
               <label><input type="checkbox" value="2" name="week[]"  />&nbsp;&nbsp;周二&nbsp;&nbsp;&nbsp;</label>
                <label><input type="checkbox" value="3" name="week[]"  />&nbsp;&nbsp;周三&nbsp;&nbsp;&nbsp;</label>
                <label><input type="checkbox" value="4" name="week[]"  />&nbsp;&nbsp;周四&nbsp;&nbsp;&nbsp;</label>
               <label><input type="checkbox" value="5" name="week[]"  />&nbsp;&nbsp;周五&nbsp;&nbsp;&nbsp;</label>
                <label><input type="checkbox" value="6" name="week[]"  />&nbsp;&nbsp;周六&nbsp;&nbsp;&nbsp;</label>
                <label><input type="checkbox" value="7" name="week[]"  />&nbsp;&nbsp;周日&nbsp;&nbsp;&nbsp;</label>
               <?php } ?>
          <p class="hint">注：若不选择，则默认为每天都可自提</p>
        </dd>
      </dl>
       <dl>
      <dt><i class="required">*</i>自提时间点</dt>
      <dd>
        <input class="text w300" type="text" name="time" value="<?php echo $output['address_info']['time'];?>"/>
                <p class="hint" ><font color="red">填写每天配送的时间点（整点数），并以英文逗号','隔开，例（8,10,12,14,16,18）</font></p>
      </dd>
    </dl>
 <!--    <dl>
      <dt><i class="required">*</i><?php echo $lang['store_daddress_phone_num'].$lang['nc_colon'];?></dt>
      <dd>
        <input type="text" class="text" name="telphone" value="<?php echo $output['address_info']['telphone'];?>"/>
      </dd>
    </dl>
    <dl>
      <dt class="required"><?php echo $lang['store_daddress_company'].$lang['nc_colon'];?></dt>
      <dd>
        <input type="text" class="text" name="company" value="<?php echo $output['address_info']['company'];?>"/>
      </dd>
    </dl> -->
    <div class="bottom">
      <label class="submit-border"><input type="submit" nctype="address_add_submit" class="submit" value="<?php echo $lang['nc_common_button_save'];?>" /></label>
    </div>
  </form>
</div>
<script>
var SITEURL = "<?php echo SHOP_SITE_URL; ?>";
$(document).ready(function(){
	$("#region").nc_region();
	$('input[nctype="address_add_submit" ]').click(function(){
		if ($('#ziti_address_form').valid()) {
			ajaxpost('ziti_address_form', '', '', 'onerror');
		}
	});
    $('#ziti_address_form').validate({
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
            seller_name : {
                required : true
            },
            region : {
            	checklast: true
            },
            address : {
                required : true
            },
            telphone : {
                required : true,
                minlength : 6
            },
              time : {
                required : true,
            }
        },
        messages : {
            seller_name : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['store_daddress_input_receiver'];?>'
            },
            region : {
                checklast : '<i class="icon-exclamation-sign"></i>请选择所在地区'
            },
            address : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['store_daddress_input_address'];?>'
            },
            telphone : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['store_daddress_phone_rule'];?>',
                minlength: '<i class="icon-exclamation-sign"></i><?php echo $lang['store_daddress_phone_rule'];?>'
            },
            time : {
                required : '<i class="icon-exclamation-sign"></i>请填写时间点'            
              }
        }
    });
});
</script> 
