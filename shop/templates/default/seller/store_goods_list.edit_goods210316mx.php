<div class="eject_con">
    <div id="warning" class="alert alert-error"></div>
    <form method="post" action="<?php echo urlShop('store_goods_online', 'edit_goods_simple'); ?>" id="plate_form">
        <input type="hidden" name="form_submit" value="ok"/>
        <input type="hidden" name="commonid" value="<?php echo $_GET['commonid']; ?>"/>
        <dl>
            <dt>商品库存：</dt>
            <dd nc_type="no_spec" style="margin-top: 10px">
                <input name="goods_storage" value="<?php echo $output['goods_info']['goods_storage']; ?>" type="text" class="text w60"/>
                <p class="hint"><?php echo $lang['store_goods_index_goods_stock_help']; ?></p>
            </dd>
        </dl>
        <dl>
            <dt>商家货号：</dt>
            <dd nc_type="no_spec" style="margin-top: 10px">
                <input name="goods_serial" value="<?php echo $output['goods_info']['goods_serial']; ?>" type="text" class="text"/>
                <p class="hint" style="margin-bottom: 15px;"><?php echo $lang['store_goods_index_goods_no_help']; ?></p>
            </dd>
        </dl>
        <div class="bottom">
            <label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_submit']; ?>"/></label>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('#plate_form').submit(function () {
            ajaxpost('plate_form', '', '', 'onerror');
            return false;
        });
    });
</script>