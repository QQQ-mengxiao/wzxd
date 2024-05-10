<?php defined('In718Shop') or exit('Access Invalid!'); ?>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.ajaxContent.pack.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.iframe-transport.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.ui.widget.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css"
      href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"/>

<div class="tabmenu">
    <?php include template('layout/submenu'); ?>
</div>
<div class="ncsc-form-default">
    <form id="add_form" action="index.php?act=store_promotion_xianshi&op=xianshi_goods_import" method="post" enctype="multipart/form-data">
        <dl>
            <dt><i class="required">*</i>文件：</dt>
            <dd>
                <div class="handle">
                    <div class="ncsc-upload-btn"><a href="javascript:void(0);"><span>
          <input type="file" hidefocus="true" size="15" name="csv" id="csv">
          </span></a></div>
                </div>
            </dd>
        </dl>
        <dl style="display: none">
            <dt>活动选择：</dt>
            <dd id="xianshi_name">
                <span></span>
                <?php if (!empty($output['xianshi_list'])) { ?>
                    <select>
                        <option>请选择</option>
                        <?php foreach ($output['xianshi_list'] as $val) { ?>
                            <option value="<?php echo $val['xianshi_name'] ?>"><?php echo $val['xianshi_name']; ?></option>
                        <?php } ?>
                    </select>
                <?php } ?>
                <p>请选择活动</p>
                <input type="hidden" id="gc_id" name="gc_id" value="" class="mls_id"/>
                <input type="hidden" id="cate_name" name="cate_name" value="" class="mls_names"/>
            </dd>
        </dl>
        <div class="bottom">
            <label class="submit-border"><input id="submit_button" type="submit" class="submit"
                                                value="<?php echo $lang['nc_submit']; ?>"></label>
        </div>
    </form>
</div>
<script src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/i18n/zh-CN.js"></script>
<script src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js"></script>
<link rel="stylesheet" type="text/css"
      href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css"/>
<script>
    // $(document).ready(function () {
    //
    //     //页面输入内容验证
    //     $("#add_form").validate({
    //     errorPlacement: function (error, element) {
    //         var error_td = element.parent('dd').children('span');
    //         error_td.append(error);
    //     }
    // ,
    //     onfocusout: false,
    //         submitHandler
    // :
    //
    //     function (form) {
    //         ajaxpost('add_form', '', '', 'onerror');
    //     }
    //
    // ,
        //rules : {
        //    xianshi_name: {
        //        required: true
        //    },
        //},
        //messages : {
        //    xianshi_name: {
        //        required: '<i class="icon-exclamation-sign"></i><?php //echo $lang['xianshi_name_error'];?>//'
        //    },
        //}
    // })
    //     ;
    // });
</script>
