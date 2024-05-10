<?php defined('In718Shop') or exit('Access Invalid!'); ?>

<div class="eject_con">
    <div id="warning" class="alert alert-error"></div>
    <form method="post" action="index.php?act=commission&op=edit_save" id="commission_form" target="_parent">
        <input type="hidden" name="form_submit" value="ok"/>
        <input type="hidden" name="gc_id" value="<?php echo $output['commission']['gc_id']; ?>"/>
        <dl>
            <dt>佣金比例：</dt>
            <dd style="padding-top: 8px">
            <input class="text w70" type="text" name="commis_rate" id="commis_rate"
                       value="<?php echo $output['commission']['commis_rate']; ?>"/><em class="add-on"><i><B>%</B></i></em>
            </dd>
        </dl>
        <div class="bottom">
            <label class="submit-border"><input type="submit" nctype="submit" class="submit"
                                                value="保存"/></label>
        </div>
    </form>
</div>
<script>
    var SITEURL = "<?php echo SHOP_SITE_URL; ?>";
</script> 
