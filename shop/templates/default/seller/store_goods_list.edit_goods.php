<div class="eject_con">
    <div id="warning" class="alert alert-error"></div>
    <form method="post" action="<?php echo urlShop('store_goods_online', 'edit_goods_simple'); ?>" id="plate_form">
        <input type="hidden" name="form_submit" value="ok"/>
        <input type="hidden" name="commonid" value="<?php echo $_GET['commonid']; ?>"/>
        <input type="hidden" name="is_cw" value="<?php echo $output['is_cw']; ?>"/>
        <dl nc_type="spec_dl" class="spec-bg" style="overflow: visible;">
            <dt style="display: none"></dt>
            <dd class="spec-dd" style="margin-left: 20px;width:720px;overflow-x: scroll">
                <table border="0" cellpadding="0" cellspacing="0" class="spec_table" style="width: 700px">
                    <thead>
                    <th class="w100">规格</th>
                    <th class="w60">库存</th>
                    <th class="w100">商品货号</th>
                    <th class="w100">商品条码</th>
                    <th class="w100">阶梯价活动</th>
                    </thead>
                    <tbody nc_type="spec_table">
                    <?php if(is_array($output['goods_list']) && !empty($output['goods_list'])){?>
                        <?php foreach ($output['goods_list'] as $k=>$val){?>
                    <tr>
                                <input type="hidden" name="spec[i_<?php echo $val['spec_id']; ?>][goods_id]" nc_type="i_<?php echo $val['spec_id'];?>|id" value="<?php echo $val['goods_id'];?>">
                                <td><input type="hidden" name="spec[i_<?php echo $val['spec_id']; ?>][sp_value][<?php echo $val['spec_id']; ?>]" value="1"><?php echo $val['goods_name']; ?></td>
                                <td><input style="width: 50px" class="text storage" type="text" name="spec[i_<?php echo $val['spec_id']; ?>][storage]" data_type="storage" nc_type="i_<?php echo $val['spec_id']; ?>|storage" value="<?php echo $val['goods_storage']; ?>"></td>
                                <td><input style="width: 100px" class="text serial" type="text" name="spec[i_<?php echo $val['spec_id']; ?>][serial]" nc_type="i_<?php echo $val['spec_id']; ?>|serial" value="<?php echo $val['goods_serial']; ?>"></td>
                                <td><input style="width: 100px" class="text barcode" type="text" name="spec[i_<?php echo $val['spec_id']; ?>][barcode]" nc_type="i_<?php echo $val['spec_id']; ?>|barcode" value="<?php echo $val['goods_barcode']; ?>" title="<?php echo $val['goods_barcode']; ?>"></td>
                        <td><select name="spec[i_<?php echo $val['spec_id']; ?>][ladder]"><?php echo $val['p_ladder_id']; ?>
                                <option name="spec[i_<?php echo $val['spec_id'];?>][ladder]" nc_type=i_<?php echo $val['spec_id'];?>ladder" data_type="ladder" value='0' <?php if($val['p_ladder_id']==0)echo 'selected'?>>请选择</option>
                                <?php foreach ($output['ladder_list'] as $key=>$ladder){?>
                                    <option name="spec[i_<?php echo $val['spec_id'];?>][ladder]" nc_type=i_<?php echo $val['spec_id'];?>ladder" data_type="ladder" value=<?php echo $ladder['p_ladder_id'];?> <?php if($val['p_ladder_id']==$ladder['p_ladder_id'])echo 'selected'?>><?php echo $ladder['p_name'];?></option>
                                <?php }?>
                            </select></td>
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
    $('input[data_type="storage"]').change(function() {
        if($('input[name="is_cw"]').val()=='1') {
            alert("该商品为云仓商品，如需修改库存请和云仓做好沟通");
        }
    });
</script>