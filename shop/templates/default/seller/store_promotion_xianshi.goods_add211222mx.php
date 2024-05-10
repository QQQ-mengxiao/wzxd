<div class="eject_con">
    <div id="warning" class="alert alert-error"></div>
    <form method="post" action="<?php echo urlShop('store_promotion_xianshi', 'xianshi_goods_add_all_save'); ?>" id="plate_form">
        <input type="hidden" name="form_submit" value="ok"/>
        <input type="hidden" name="xianshi_id" value="<?php echo $output['xianshi_id']?>"/>
<!--        <input type="hidden" name="commonid" value="--><?php //echo $_GET['commonid']; ?><!--"/>-->
        <dl nc_type="spec_dl" class="spec-bg" style="overflow: visible;">
            <dd class="spec-dd" style="margin-left: 25px">
                <table border="0" cellpadding="0" cellspacing="0" class="spec_table" style="width: 550px">
                    <thead>
                    <th calss="w50"></th>
                    <th class="w150">商品名称</th>
                    <th class="w80">销售价格</th>
                    <th class="w80">秒杀价格</th>
                    <th class="w50">购买上限</th>
                    <th class="w50">佣金比例</th>
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
                                    <input style="width: 50px" class="text" type="text" name="goods[<?php echo $val['goods_id']; ?>][xianshi_price]" nc_type="<?php echo $val['goods_id']; ?>|xianshi_price" value="">
                                </td>
                                <td>
                                    <input style="width: 50px" class="text" type="text" name="goods[<?php echo $val['goods_id']; ?>][upper_limit]" nc_type="<?php echo $val['goods_id']; ?>|upper_limit" value="0">
                                </td>
                                <td>
                                    <input style="width: 50px" class="text" type="text" name="goods[<?php echo $val['goods_id']; ?>][commis_rate]" nc_type="<?php echo $val['goods_id']; ?>|commis_rate" value="0">
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
            var formData = $('#plate_form').serializeArray();
            formData.shift();
            formData.shift();
            var groupedArray = group(formData, 7);
            var a = 1;
            for (var item in groupedArray) {
                if(groupedArray[item][3]['value'] == ''){
                    showDialog(groupedArray[item][2]['value']+'的秒杀价格不能为空！');
                    a = 0;
                    break;
                }
                if(groupedArray[item][3]['value']>groupedArray[item][1]['value']){
                    showDialog(groupedArray[item][2]['value']+'的秒杀价格不能高于当前售价！');
                    a = 0;
                    break;
                }
            }
            if(a == 1) {
                ajaxpost('plate_form', '', 3, 'onerror');
                return false;
            }else {
                return false;
            }
        });
    });

    function group(array, subGroupLength) {
        let index = 0;
        let newArray = [];
        while(index < array.length) {
            newArray.push(array.slice(index, index += subGroupLength));
        }
        return newArray;
    }
</script>