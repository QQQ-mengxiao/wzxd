<?php defined('In718Shop') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<div class="ncsc-form-default">
    <?php if(empty($output['dayin_info'])) { ?>
    <form id="add_form" action="index.php?act=dayin_setting&op=add_save" method="post">
    <?php } else { ?>
    <form id="add_form" action="index.php?act=dayin_setting&op=edit_save" method="post">
        <input type="hidden"  id="dayin_id" name="dayin_id" value="<?php echo $output['dayin_info']['dayin_id'];?>">
    <?php } ?>
      <dl>
        <dt><i class="required">*</i>打印机编码：</dt>
        <dd>
            <input id="dayin_sn" name="dayin_sn" type="text"  maxlength="25" class="text w400" value="<?php echo empty($output['dayin_info'])?'':$output['dayin_info']['dayin_sn'];?>"/>
            <span></span>
          <!-- <p class="hint"><?php echo $lang['xianshi_name_explain'];?></p> -->
        </dd>
      </dl>
      <dl>
        <dt><i class="required">*</i>密钥KEY：</dt>
        <dd>
            <input id="dayin_key" name="dayin_key" type="text"  maxlength="25" class="text w400" value="<?php echo empty($output['dayin_info'])?'':$output['dayin_info']['dayin_key'];?>"/>
            <span></span>
          <!-- <p class="hint"><?php echo $lang['xianshi_name_explain'];?></p> -->
        </dd>
      </dl>
      <dl>
        <dt><i class="required">*</i>注册账号名：</dt>
        <dd>
            <input id="dayin_user" name="dayin_user" type="text"  maxlength="25" class="text w400" value="<?php echo empty($output['dayin_info'])?'':$output['dayin_info']['dayin_user'];?>"/>
            <span></span>
            <p class="hint"><font color="red">请填写打印机官网后台注册账号名，非本系统登录用户名!</font></p> 
        </dd>
      </dl>
      <dl>
        <dt><i class="required">*</i>用户账号密钥(UKEY)：</dt>
        <dd>
            <input id="ukey" name="ukey" type="text"  maxlength="25" class="text w400" value="<?php echo empty($output['dayin_info'])?'':$output['dayin_info']['ukey'];?>"/>
            <span></span>
            <!-- <p class="hint"><font color="red">请填写打印机官网后台注册账号名，非本系统登录用户名！></font></p>  -->
        </dd>
      </dl>
      <dl>
        <dt>打印机名称：</dt>
        <dd>
            <input id="dayin_name" name="dayin_name" type="text"  maxlength="25" class="text w400" value="<?php echo empty($output['dayin_info'])?'':$output['dayin_info']['dayin_name'];?>"/>
            <span></span>
          <!-- <p class="hint"><?php echo $lang['xianshi_name_explain'];?></p> -->
        </dd>
      </dl>
      <dl>
        <dt>流量卡手机号：</dt>
        <dd>
            <input id="mobile" name="mobile" type="text"  maxlength="25" class="text w400" value="<?php echo empty($output['dayin_info'])?'':$output['dayin_info']['mobile'];?>"/>
            <span></span>
          <!-- <p class="hint"><?php echo $lang['xianshi_name_explain'];?></p> -->
        </dd>
      </dl>
      <dl>
        <dt>备注：</dt>
        <dd>
            <input id="note" name="note" type="text"  maxlength="25" class="text w400" value="<?php echo empty($output['dayin_info'])?'':$output['dayin_info']['note'];?>"/>
            <span></span>
          <!-- <p class="hint"><?php echo $lang['xianshi_name_explain'];?></p> -->
        </dd>
      </dl>
      <div class="bottom">
       <!--  <label class="submit-border"><input id="submit_button" type="submit" class="submit" value="提交"></label> -->
       <label class="submit-border"><a href="JavaScript:void(0);" class="submit" id="submitBtn2" type="submit"><span>提交</span></a></label>
      </div>
    </form>
</div>
<script type="text/javascript">
$(function(){$("#submitBtn2").click(function(){
    if($("#add_form").valid()){
       $("#add_form").submit();
  }
  });
});
$(document).ready(function(){
    $('#add_form').validate({
    errorPlacement: function(error, element){
      error.appendTo(element.parent().parent().prev().find('td:first'));
        },

      rules : {
            dayin_sn : {
                required : true
            },
             dayin_user : {
                required : true
            },
             ukey : {
                required : true
            },
            dayin_key : {
                required : true
            }
        },
        messages : {
            dayin_sn : {
                required : '<i class="icon-exclamation-sign"></i>打印机编码号不能为空！'
            },
            dayin_user : {
                required : '<i class="icon-exclamation-sign"></i>注册账号名不能为空！'
            },
            ukey : {
                required : '<i class="icon-exclamation-sign"></i>用户账号密钥(UKEY)不能为空！'
            },
            dayin_key : {
                required : '<i class="icon-exclamation-sign"></i>打印机密钥KEY不能为空！'
            }
        }
      
    });
    
});

</script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css"  />

<!-- <script>
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
            dayin_sn : {
                required : true
            },
             dayin_user : {
                required : true
            },
             ukey : {
                required : true
            },
            dayin_key : {
                required : true
            }
        },
        messages : {
            dayin_sn : {
                required : '<i class="icon-exclamation-sign"></i>打印机编码号不能为空！'
            },
            dayin_user : {
                required : '<i class="icon-exclamation-sign"></i>注册账号名不能为空！'
            },
            ukey : {
                required : '<i class="icon-exclamation-sign"></i>用户账号密钥(UKEY)不能为空！'
            },
            dayin_key : {
                required : '<i class="icon-exclamation-sign"></i>打印机密钥KEY不能为空！'
            }
        }
    });
});
</script> -->
