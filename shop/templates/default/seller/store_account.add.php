<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<div class="ncsc-form-default">
  <form id="add_form" action="<?php echo urlShop('store_account', 'account_save');?>" method="post">
    <dl>
      <dt><i class="required">*</i>前台用户名<?php echo $lang['nc_colon'];?></dt>
      <dd><input class="w120 text" name="member_name" type="text" id="member_name" value="" />
          <span></span>
        <p class="hint"></p>
      </dd>
    </dl>
    <dl>
      <dt><i class="required">*</i>用户密码<?php echo $lang['nc_colon'];?></dt>
      <dd><input class="w120 text" name="password" type="password" id="password" value="" />
          <span></span>
        <p class="hint"></p>
      </dd>
    </dl>
    <dl>
      <dt><i class="required">*</i>登录账号<?php echo $lang['nc_colon'];?></dt>
      <dd><input class="w120 text" name="seller_name" type="text" id="seller_name" value="" />
          <span></span>
        <p class="hint">新账号登录商家中心的用户名，密码与该账号前台密码相同</p>
      </dd>
    </dl>
    <dl>
      <dt><i class="required">*</i>账号组<?php echo $lang['nc_colon'];?></dt>
      <dd><select name="group_id">
            <?php foreach($output['seller_group_list'] as $value) { ?>
            <option value="<?php echo $value['group_id'];?>"><?php echo $value['group_name'];?></option>
            <?php } ?>
          </select>
          <span></span>
        <p class="hint"></p>
      </dd>
    </dl>

    <dl>
      <dt>关联发货人<?php echo $lang['nc_colon'];?></dt>
      <dd>
        <select class="w200" name="daddress_id" id="menu1" class="select1" onchange="getMenuByajax()">
          <option value="0">请选择...</option>
          <?php if(is_array($output['daddress_list']) && !empty($output['daddress_list'])){?>
            <?php foreach ($output['daddress_list'] as $val) {?>
              <option value="<?php echo $val['address_id']; ?>" <?php if ($output['seller_info']['address_id'] == $val['address_id']){ echo 'selected=selected';}?>><?php echo $val['seller_name']; ?></option>
            <?php }?>
          <?php }?>
        </select>
        <p style="color: red;">账号组选择为供货商发布商品专用时需要指定关联发货人</p>
      </dd>
    </dl>

    <div class="bottom">
      <label class="submit-border">
        <input type="submit" class="submit" value="<?php echo $lang['nc_submit'];?>">
      </label>
    </div>
  </form>
</div>

<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script> 
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/select2/select2.min.css"/>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/select2/select2.full.min.js"></script>

<script>
$(function () {
    $("#menu1").select2();
});
//下拉框
function getMenuByajax(){
    //获取menu1的编号
  menuVal = $('#menu1').val();
  //alert('menuVal='+menuVal)
  $("#belong").attr("value",menuVal);//给隐藏的sysNum字段赋值。
}
$(document).ready(function(){
    jQuery.validator.addMethod("seller_name_exist", function(value, element, params) { 
        var result = true;
        $.ajax({  
            type:"GET",  
            url:'<?php echo urlShop('store_account', 'check_seller_name_exist');?>',  
            async:false,  
            data:{seller_name: $('#seller_name').val()},  
            success: function(data){  
                if(data == 'true') {
                    $.validator.messages.seller_name_exist = "卖家账号已存在";
                    result = false;
                }
            }  
        });  
        return result;
    }, '');

    jQuery.validator.addMethod("check_member_password", function(value, element, params) { 
        var result = true;
        $.ajax({  
            type:"GET",  
            url:'<?php echo urlShop('store_account', 'check_seller_member');?>',  
            async:false,  
            data:{member_name: $('#member_name').val(), password: $('#password').val()},  
            success: function(data){  
                if(data != 'true') {
                    $.validator.messages.check_member_password = "前台用户验证失败";
                    result = false;
                }
            }  
        });  
        return result;
    }, '');

    $('#add_form').validate({
        onkeyup: false,
        errorPlacement: function(error, element){
            element.nextAll('span').first().after(error);
        },
    	submitHandler:function(form){
    		ajaxpost('add_form', '', '', 'onerror');
    	},
        rules: {
            member_name: {
                required: true
            },
            password: {
                required: true,
                check_member_password: true
            },
            seller_name: {
                required: true,
                maxlength: 50, 
                seller_name_exist: true
            },
            group_id: {
                required: true
            }
        },
        messages: {
            member_name: {
                required: '<i class="icon-exclamation-sign"></i>前台用户名不能为空'
            },
            password: {
                required: '<i class="icon-exclamation-sign"></i>用户密码不能为空',
                remote: '<i class="icon-exclamation-sign"></i>用户名密码错误'
            },
            seller_name: {
                required: '<i class="icon-exclamation-sign"></i>卖家账号不能为空',
                maxlength: '<i class="icon-exclamation-sign"></i>卖家账号最多50个字'
            },
            group_id: {
                required: '<i class="icon-exclamation-sign"></i>请选择账号组'
            }
        }
    });
});
</script> 
