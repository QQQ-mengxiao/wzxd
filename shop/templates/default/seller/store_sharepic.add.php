
<div class="eject_con" style="top:160px !important ">
  <div id="warning" class="alert alert-error" style="top:160px"></div>
  <form method="post" target="_parent" action="index.php?act=store_sharepic&op=<?php if ($output['sharepic_array']['sharepic_id']!='') echo 'sharepic_edit'; else echo 'sharepic_save'; ?>"enctype="multipart/form-data" id="sharepic_apply_form" style="top:160px">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="sharepic_id" value="<?php echo $output['sharepic_array']['sharepic_id']; ?>" />
    <dl>
      <dt><i class="required">*</i><?php echo '图片名称'.$lang['nc_colon'];?></dt>
      <dd>
        <input type="text" class="text" name="sharepic_name" value="<?php echo $output['sharepic_array']['sharepic_name']; ?>" id="sharepic_name" />
      </dd>
    </dl>
    <dl>
      <dt><i class="required">*</i><?php echo '背景图片'.$lang['nc_colon'];?></dt>
      <dd>
      <div class=""><span class="sign"><img src="<?php echo UPLOAD_SITE_URL.'/'.ATTACH_SHAREPIC.'/'. $output['sharepic_array']['share_pic'];?>" onload="javascript:DrawImage(this,88,44);" nc_type="logo1"/></span></div>
      <!--<div class=""><span class="sign"><img src="<?php echo UPLOAD_SITE_URL.'/'.ATTACH_SHAREPIC.'/'. $output['sharepic_array']['share_pic'];?>" onload="javascript:DrawImage(this,88,44);" nc_type="logo1"/></span></div>-->
        <div class="ncsc-upload-btn"> <a href="javascript:void(0);"><span>
          <input type="file" hidefocus="true" size="30" class="input-file" name="sharepic_pic" id="sharepic_pic" nc_type="logo"/>
          </span>
          <p><i class="icon-upload-alt"></i>图片上传</p>
          </a> </div>
        <p class="hint">建议上传大小为150x150的图片，您可以编辑或撤销申请。</p>
      </dd>
    </dl>
    <div class="bottom">
      <label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_submit'];?>"/></label>
    </div>
  </form>
</div>
<!-- <style type="text/css">
.eject_con{
    background-color: rgba(130, 130, 130, 0.25);
    border-radius: 4px;
    padding: 4px;
    box-shadow: 0 0 12px rgba(0,0,0,0.75);
   top:  166px !important;
 
}
</style> -->
<script>
    window.onload=function()({
        var obj=document.querySelector("dialog_wrapper");
            obj.style.top="160px !important";
        })
    
</script>
<script>
$(function(){
	$.getScript('<?php echo RESOURCE_SITE_URL;?>/js/common_select.js', function(){
		gcategoryInit('gcategory');
	});

    jQuery.validator.addMethod("initial", function(value, element) {
        return /^[A-Za-z0-9]$/i.test(value);
    }, "");
    $('#sharepic_apply_form').validate({
        errorLabelContainer: $('#warning'),
        invalidHandler: function(form, validator) {
               $('#warning').show();
        },
    	submitHandler:function(form){
    		ajaxpost('sharepic_apply_form', '', '', 'onerror') 
    	},
        rules : {
            sharepic_name : {
                required : true,
                rangelength: [0,100]
            },
   //          sharepic_initial : {
   //              initial  : true
   //          }
			// <?php if ($output['sharepic_array']['sharepic_id']=='') { ?>
			// ,
            sharepic_pic : {
                required : true
			}
			<?php } ?>		
        },
        messages : {
            sharepic_name : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['store_goods_sharepic_input_name'];?>',
                rangelength: '<i class="icon-exclamation-sign"></i><?php echo $lang['store_goods_sharepic_name_error'];?>'
            },
            sharepic_initial : {
                initial : '<i class="icon-exclamation-sign"></i>请填写正确首字母',
            }
			<?php if ($output['sharepic_array']['sharepic_id']=='') { ?>
			,
            sharepic_pic : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['store_goods_sharepic_icon_null'];?>'
			}
			<?php } ?>
        }
    });
	$('input[nc_type="logo"]').change(function(){
		var src = getFullPath($(this)[0]);
		$('img[nc_type="logo1"]').attr('src', src);
	});
});

</script>