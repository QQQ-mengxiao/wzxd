<div class="eject_con">
    <div id="warning" class="alert alert-error"></div>
    <form method="post" action="<?php echo urlShop('store_deliver', 'edit_goods_kuajing');?>" id="ajax_goods_kuajing">
        <input type="hidden" name="form_submit" value="ok" />
        <dl>
            <dt>
                    <i class="required">*</i>
                国外发货人：</dt>
            <dd>
                <select name="goods_shipper_id" id="goods_shipper_id">
                    <option value="0">请选择国外发货人</option>
                    <?php foreach ($output['kuajing_shipper'] as $key => $value) {?>
                        <option value='<?php echo $value['shipper_id']?>' <?php if ($output['goods']['goods_shipper_id'] == $value['shipper_id']) {?>selected="selected"<?php }?>><?echo $value['shipper_name']?></option>
                    <?php } ?>
                </select>
                <span></span>
                <p class="hint"></p>
            </dd>
        </dl>
        <div class="bottom">
            <label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_submit'];?>"/></label>
        </div>
    </form>
</div>
<script>
    $(function(){
        $('#ajax_goods_kuajing').validate({
            submitHandler:function(form){
                ajaxpost('ajax_goods_kuajing', '', '', 'onerror');
            }
        });
    });
</script>
