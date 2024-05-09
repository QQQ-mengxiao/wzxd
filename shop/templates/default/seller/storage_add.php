<?php defined('In718Shop') or exit('Access Invalid!'); ?>

<div class="eject_con">
    <div id="warning" class="alert alert-error"></div>
    <form method="post" action="index.php?act=storage&op=storage_add" id="storage_form" target="_parent">
        <input type="hidden" name="form_submit" value="ok"/>
        <input type="hidden" name="storage_id" value="<?php echo $output['storage_info']['storage_id']; ?>"/>
        <!--<dl>
            <dt><i class="required">*</i>登陆账号：</dt>
            <dd style="padding-top: 8px">
                <input type="text" class="text" name="storage_username" id="storage_username"
                       value="<?php echo $output['storage_info']['storage_username']; ?>"/>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>登陆密码：</dt>
            <dd style="padding-top: 8px">
                <div>
                    <input type="text" name="storage_password" id="storage_password"
                           value="<?php echo $output['storage_info']['storage_password']; ?>"/>
                </div>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>接口IP：</dt>
            <dd style="padding-top: 8px">
                <div>
                    <input type="text" name="storage_url" id="storage_url"
                           value="<?php echo $output['storage_info']['storage_url']; ?>"/>
                    <i class="icon-exclamation-sign"></i>请输入<B style="color: red">127.0.0.1:8081</B>格式的IP
                </div>
                <a class="ncsc-btn ncsc-btn-blue" onclick="login()">登陆</a>
            </dd>
        </dl>-->
        <dl>
            <dt><i class="required">*</i>仓库名称：</dt>
            <dd style="padding-top: 8px">
                <input class="text w300" type="text" name="storage_name" id="storage_name"
                       value="<?php echo $output['storage_info']['storage_name']; ?>"/>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>仓库编码：</dt>
            <dd style="padding-top: 8px">
                <input type="text" class="text" name="storage_code" id="storage_code"
                       value="<?php echo $output['storage_info']['storage_code']; ?>"/>
            </dd>
        </dl>
        <dl>
          <dt><i class="required">*</i>是否wms分拣：</dt>
          <dd>
              <select name="is_picked" id="is_picked" class="querySelect">
                  <option value='0' <?php echo $output['storage_info']['is_picked'] == 0?'selected':''; ?>>否</option>
                  <option value='1' <?php echo $output['storage_info']['is_picked'] == 1?'selected':''; ?>>是</option>
              </select>
          </dd>
        </dl>
        <dl>
          <dt><i class="required">*</i>截单次数：</dt>
          <dd>
              <select name="times" id="times" class="querySelect">
                
                <option value='1' <?php echo $output['storage_info']['times'] == 1?'selected':''; ?>>一天一次截单</option>
                <option value='2' <?php echo $output['storage_info']['times'] == 2?'selected':''; ?>>一天两次截单</option>
                  
              </select>
          </dd>
        </dl>
        <dl>
          <dt><i class="required">*</i>是否包邮：</dt>
          <dd>
              <select name="by_post" id="by_post" class="querySelect">
                
                <option value='0' <?php echo $output['storage_info']['by_post'] == 0?'selected':''; ?>>不包邮</option>
                <option value='1' <?php echo $output['storage_info']['by_post'] == 1?'selected':''; ?>>包邮</option>
                  
              </select>
          </dd>
        </dl>        
        <dl>
            <dt>备注：</dt>
            <dd style="padding-top: 8px">
                <textarea class="text" style="height: 60px;width:300px;resize:none" name="storage_explain"
                          id="storage_explain"
                          value="<?php echo $output['storage_info']['storage_explain']; ?>"><?php echo $output['storage_info']['storage_explain']; ?></textarea>
            </dd>
        </dl>
        <div class="bottom">
            <label class="submit-border"><input type="submit" nctype="storage_add_submit" class="submit"
                                                value="保存"/></label>
        </div>
    </form>
</div>
<script>
    var SITEURL = "<?php echo SHOP_SITE_URL; ?>";

    $("#storage_username").blur(function(){
        $('#storage_name').val('');
        $('#storage_code').val('');
    });
    $("#storage_password").blur(function(){
        $('#storage_name').val('');
        $('#storage_code').val('');
    });
    $("#storage_url").blur(function(){
        $('#storage_name').val('');
        $('#storage_code').val('');
    });
	
    function login() {
        $.post('<?php echo urlShop('storage', 'warehose_login');?>',
            {
                storage_username: $('#storage_username').val(),
                storage_password: $('#storage_password').val(),
                storage_url: $('#storage_url').val(),
            },
            function (data) {
                if (data) {
                    if(data.code == 500){
                        document.getElementById('warning').innerHTML='<i class="icon-exclamation-sign"></i>'+data.message;
                        $('#warning').show();
                    }else {
                        $('#storage_name').val(data.WarehouseName);
                        $('#storage_code').val(data.WarehouseCode);
                    }
                }else{
                    document.getElementById('warning').innerHTML='<i class="icon-exclamation-sign"></i>仓库信息已存在！';
                    $('#warning').show();
                }
            },
            'json');
    }

    $(document).ready(function () {
        $('input[nctype="storage_add_submit" ]').click(function () {
            if ($('#storage_form').valid()) {
                ajaxpost('storage_form', '', '', 'onerror');
            }
        });
        $('#storage_form').validate({
            errorLabelContainer: $('#warning'),
            invalidHandler: function (form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    $('#warning').show();
                } else {
                    $('#warning').hide();
                }
            },
            rules: {
                //storage_username: {
                    //required: true
                //},
                //storage_password: {
                    //required: true
                //},
                //storage_url: {
                    //required: true
                },
                storage_name: {
                    required: true
                },
                storage_code: {
                    required: true
                }
            },
            messages: {
                storage_username: {
                    required: '<i class="icon-exclamation-sign"></i>请输入登陆名称！'
                },
                storage_password: {
                    required: '<i class="icon-exclamation-sign"></i>请输入登陆密码！'
                },
                storage_url: {
                    required: '<i class="icon-exclamation-sign"></i>请输入IP！'
                },
                storage_name: {
                    required: '<i class="icon-exclamation-sign"></i>请点击登陆获取仓库名称！'
                },
                storage_code: {
                    required: '<i class="icon-exclamation-sign"></i>请点击登陆获取仓库编码！'
                }
            }
        });
    });
</script> 
