<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="eject_con">
  <div id="warning" class="alert alert-error"></div>
  <form method="post" action="index.php?act=pei_setting&op=pei_add" id="pei_form" target="_parent">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="pei_id" value="<?php echo $output['pei_info']['id'];?>" />
    <dl style="margin-top: 13px;">
      <dt style="width: 150px;"><i class="required">*</i>配货方式名称：</dt>
      <dd style="width: 1140px;">
        <input type="text" class="text" name="p_name" value="<?php echo $output['pei_info']['p_name'];?>" style="width: 300px;"/>
      </dd>
    </dl>
    <dl>
      <dt style="width: 150px;"><i class="required">*</i>关联发货人：</dt>
        <dd style="width: 1130px;">
           <?php if (is_array($output['pei_info']['deliever_id'])){ ?>
            <?php foreach($output['daddress_list'] as $key =>$daddress){?>
              <label><input type="checkbox" value="<?php echo $daddress['address_id'];?>" name="deliever_id[]" <?php if(in_array($daddress['address_id'], $output['pei_info']['deliever_id'])){echo 'checked';}?> />&nbsp;&nbsp;<?php echo $daddress['seller_name'];?>&nbsp;&nbsp;&nbsp;</label>
            <?php }?>
             
            <?php }else{ ?>
              <?php foreach($output['daddress_list'] as $key=>$daddress){?>
                <label><input type="checkbox" value="<?php echo $daddress['address_id'];?>" name="deliever_id[]"  />&nbsp;&nbsp;<?php echo $daddress['seller_name'];?>&nbsp;&nbsp;&nbsp;</label>
              <?php }?>
               <?php } ?>
        </dd>
      </dl>
       <dl>
      <dt style="width: 150px;">备注：</dt>
      <dd>
        <input class="text w300" type="text" name="note" style="width: 300px;" value="<?php echo $output['pei_info']['note'];?>"/>
      </dd>
    </dl>
    <div class="bottom">
      <label class="submit-border"><input type="submit" nctype="pei_add_submit" class="submit" value="保存" /></label>
    </div>
  </form>
</div>
<script>
    window.onload=function()({
        var obj=document.querySelector("dialog_wrapper");
            obj.style.top="160px !important";
        })
    
</script>
<script>
var SITEURL = "<?php echo SHOP_SITE_URL; ?>";
$(document).ready(function(){
	$("#region").nc_region();
	$('input[nctype="pei_add_submit" ]').click(function(){
		if ($('#pei_form').valid()) {
			ajaxpost('pei_form', '', '', 'onerror');
		}
	});
    $('#pei_form').validate({
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
            p_name : {
                required : true
            },
            deliever_id : {
            	checklast: true
            }
        },
        messages : {
            p_name : {
                required : '<i class="icon-exclamation-sign"></i>请填写配货方式名称！'
            },
            deliever_id : {
                checklast : '<i class="icon-exclamation-sign"></i>请选择关联发货人!'
            }
        }
    });
});
</script> 
