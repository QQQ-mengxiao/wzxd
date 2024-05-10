<?php defined('In718Shop') or exit('Access Invalid!'); ?>

<div class="tabmenu">
    <ul class="tab pngFix">
        <li class="active"><a>修改密码</a></li>
    </ul>
</div>
<div class="ncsc-form-default">
    <form id="modify_form" action="<?php echo urlShop('seller_security', 'modify_pwd'); ?>" method="post">
        <dl>
            <dt>当前账号：</dt>
            <dd><?php echo $_SESSION['seller_name']; ?>
                <span></span>
                <p class="hint"></p>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>旧密码：</dt>
            <dd><input class="w120 text" name="password_old" type="password" id="password_old" value=""/>
                <span></span>
                <p class="hint">请输入当前账号的密码。</p>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>新密码：</dt>
            <dd><input class="w120 text" name="password_new" type="password" id="password_new" value=""/>
                <span></span>
                <p class="hint">请输入新密码，新密码应设置不少于六位。</p>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>确认密码：</dt>
            <dd><input class="w120 text" name="password_ensure" type="password" id="password_ensure" value=""/>
                <span></span>
                <p class="hint">请重新输入新密码加以确认，两次输入密码应一致。</p>
            </dd>
        </dl>
        <div class="bottom">
            <label class="submit-border">
                <input type="submit" class="submit" value="确认修改">
            </label>
        </div>
    </form>
</div>
<script>
    $(document).ready(function () {
        jQuery.validator.addMethod("check_old_password", function (value, element, params) {
            var result = true;
            $.ajax({
                type: "POST",
                url: '<?php echo urlShop('seller_security', 'pwd_old_validate');?>',
                async: false,
                data: {password_old: $('#password_old').val()},
                success: function (data) {
                    if (data) {
                        $.validator.messages.check_old_password = '旧密码输入错误，请重新输入！';
                        result = false;
                    }
                }
            });
            return result;
        }, '');
        jQuery.validator.addMethod("check_ensure_password", function (value, element, params) {
            var result = true;
            $.ajax({
                type: "POST",
                url: '<?php echo urlShop('seller_security', 'pwd_ensure_validate');?>',
                async: false,
                data: {password_new: $('#password_new').val(), password_ensure: $('#password_ensure').val()},
                success: function (data) {
                    if (data) {
                        $.validator.messages.check_ensure_password = '两次密码输入不一致，请重新输入！';
                        result = false;
                    }
                }
            });
            return result;
        }, '');

        $('#modify_form').validate({
            onkeyup: false,
            errorPlacement: function (error, element) {
                element.nextAll('span').first().after(error);
            },
            submitHandler: function (form) {
                ajaxpost('modify_form', '', '', 'onerror');
            },
            rules: {
                password_old: {
                    required: true,
                    check_old_password: true
                },
                password_new: {
                    required: true,
                    minlength: 6,
                },
                password_ensure: {
                    required: true,
                    minlength: 6,
                    check_ensure_password: true
                },
            },
            messages: {
                password_old: {
                    required: '<i class="icon-exclamation-sign"></i>请输入旧密码。'
                },
                password_new: {
                    required: '<i class="icon-exclamation-sign"></i>请输入新密码。',
                    minlength: '<i class="icon-exclamation-sign"></i>新密码应设置不少于六位。'
                },
                password_ensure: {
                    required: '<i class="icon-exclamation-sign"></i>请重新输入新密码。',
                    minlength: '<i class="icon-exclamation-sign"></i>新密码应设置不少于六位。'
                },
            }
        });
    });
<?php if($output['seller_group_id']==46){?>
  $('.ncsc-nav dl:nth-of-type(2) dd ul li:nth-of-type(4)').attr('style','display:none');
  $('#quicklink_list dl dd:nth-of-type(4)').attr('style','display:none');
<?php }?>
</script>
