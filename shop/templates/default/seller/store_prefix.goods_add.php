<div class="eject_con">
    <div id="warning" class="alert alert-error"></div>
    <form method="post" action="<?php echo urlShop('store_prefix', 'prefix_goods_add_all_save'); ?>" id="plate_form">
        <input type="hidden" name="form_submit" value="ok"/>
        <input type="hidden" name="prefix_id" value="<?php echo $output['prefix_id']?>"/>
        <dl nc_type="spec_dl" class="spec-bg" style="overflow: visible;">
            <dd class="spec-dd" style="margin-left: 25px">
                <table border="0" cellpadding="0" cellspacing="0" class="spec_table" style="width: 550px">
                    <thead>
                    <th calss="w50"></th>
                    <th class="w300">商品名称</th>
                    </thead>
                    <tbody nc_type="spec_table">
                    <?php if(is_array($output['goods_list']) && !empty($output['goods_list'])){?>
                        <?php foreach ($output['goods_list'] as $k=>$val){?>
                            <tr>
                                <td>
                                    <div class="goods-thumb"><img src="<?php echo cthumb($val['goods_image'], 60, $_SESSION['store_id']); ?>" style="max-width: 32px;max-height: 32px;"></div>
                                </td>
                                <input type="hidden" name="goods[<?php echo $val['goods_commonid']; ?>][goods_id]" nc_type="<?php echo $val['goods_commonid']; ?>|id" value="<?php echo $val['goods_commonid']; ?>">
                                <!-- <input type="hidden" name="goods[<?php echo $val['goods_id']; ?>][goods_price]" nc_type="<?php echo $val['goods_id']; ?>|id" value="<?php echo $val['goods_price']; ?>"> -->
                                <input type="hidden" name="goods[<?php echo $val['goods_commonid']; ?>][goods_name]" nc_type="<?php echo $val['goods_commonid']; ?>|id" value="<?php echo $val['goods_name']; ?>">
                                <td>
                                    <?php echo $val['goods_name']; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    </tbody>
                </table>
                <p class="hint"><?php echo $lang['store_goods_index_goods_stock_help']; ?></p>
                <p class="hint"><?php echo $lang['store_goods_index_goods_no_help']; ?></p>
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
            ajaxpost('plate_form', '', 3, 'onerror');
            return false;
        });
    });
</script>