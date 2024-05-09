<div class="eject_con">
    <div id="warning" class="alert alert-error"></div>
    <form method="post" action="<?php echo urlShop('store_goods_online', 'edit_goods_simple'); ?>" id="plate_form">
        <input type="hidden" name="form_submit" value="ok"/>
        <input type="hidden" name="commonid" value="<?php echo $_GET['commonid']; ?>"/>
        <dl nc_type="spec_dl" class="spec-bg" style="overflow: visible;">
            <dt style="display: none"></dt>
            <dd class="spec-dd" style="margin-left: 50px">
                <table border="0" cellpadding="0" cellspacing="0" class="spec_table" style="width: 450px">
                    <thead>
                    <th class="w100">规格</th>
                    <th class="w60">库存</th>
                    <th class="w100">商品货号</th>
                    </thead>
                    <tbody nc_type="spec_table">
                    <?php if(is_array($output['goods_list']) && !empty($output['goods_list'])){?>
                        <?php foreach ($output['goods_list'] as $k=>$val){?>
                    <tr>
                                <input type="hidden" name="spec[i_<?php echo $val['spec_id']; ?>][goods_id]" nc_type="i_<?php echo $val['spec_id'];?>|id" value="<?php echo $val['goods_id'];?>">
                                <td><input type="hidden" name="spec[i_<?php echo $val['spec_id']; ?>][sp_value][<?php echo $val['spec_id']; ?>]" value="1"><?php echo $val['goods_name']; ?></td>
                                <td><input style="width: 50px" class="text storage" type="text" name="spec[i_<?php echo $val['spec_id']; ?>][storage]" data_type="storage" nc_type="i_<?php echo $val['spec_id']; ?>|storage" value="<?php echo $val['goods_storage']; ?>"></td>
                                <td><input style="width: 100px" class="text serial" type="text" name="spec[i_<?php echo $val['spec_id']; ?>][serial]" nc_type="i_<?php echo $val['spec_id']; ?>|serial" value="<?php echo $val['goods_serial']; ?>"></td>
                    </tr>
                        <?php }?>
                    <?php }?>
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
            ajaxpost('plate_form', '', '', 'onerror');
            return false;
        });
    });
</script>