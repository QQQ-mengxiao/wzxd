<div class="eject_con">
    <div id="warning" class="alert alert-error"></div>
    <form method="post" action="<?php echo urlShop('store_buy_deliver', 'buy_deliver_add_all_save'); ?>" id="plate_form">
        <input type="hidden" name="form_submit" value="ok"/>
        <input type="hidden" name="buy_deliver_id" value="<?php echo $output['buy_deliver_id']?>"/>
        <dl nc_type="spec_dl" class="spec-bg" style="overflow: visible;">
            <dd class="spec-dd" style="margin-left: 25px">
                <table border="0" cellpadding="0" cellspacing="0" class="spec_table" style="width: 550px">
                    <thead>
                    <th calss="w50"></th>
                    <th class="w300">商品名称</th>
                    <th class="w80">销售价格</th>
                    <th class="w60">商品排序</th>
                    </thead>
                    <tbody nc_type="spec_table">
                    <?php if(is_array($output['goods_list']) && !empty($output['goods_list'])){?>
                        <?php foreach ($output['goods_list'] as $k=>$val){?>
                            <tr>
                                <td>
                                    <div class="goods-thumb"><img src="<?php echo cthumb($val['goods_image'], 60, $_SESSION['store_id']); ?>" style="max-width: 32px;max-height: 32px;"></div>
                                </td>
                                <input type="hidden" name="goods[<?php echo $val['goods_id']; ?>][goods_id]" nc_type="<?php echo $val['goods_id']; ?>|id" value="<?php echo $val['goods_id']; ?>">
                                <input type="hidden" name="goods[<?php echo $val['goods_id']; ?>][goods_price]" nc_type="<?php echo $val['goods_id']; ?>|id" value="<?php echo $val['goods_price']; ?>">
                                <input type="hidden" name="goods[<?php echo $val['goods_id']; ?>][goods_name]" nc_type="<?php echo $val['goods_id']; ?>|id" value="<?php echo $val['goods_name']; ?>">
                                <td>
                                    <?php echo $val['goods_name']; ?>
                                </td>
                                <td>
                                    <?php echo $val['goods_price']; ?>
                                </td>
                                <td>
                                    <input style="width: 50px" class="text" type="text" name="goods[<?php echo $val['goods_id']; ?>][goods_sort]" nc_type="<?php echo $val['goods_id']; ?>|goods_sort" value="9999">
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