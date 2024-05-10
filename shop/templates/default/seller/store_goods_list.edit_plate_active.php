<div class="eject_con">
    <div id="warning" class="alert alert-error"></div>
    <form method="post" action="<?php echo urlShop('store_goods_online', 'edit_plate_active'); ?>" id="plate_form">
        <input type="hidden" name="form_submit" value="ok"/>
        <input type="hidden" name="commonid" value="<?php echo $_GET['commonid']; ?>"/>
        <dl>
            <dt>商品信息：</dt>
            <dd style="padding-top: 5px">
                <p>
                    <label>活动类型</label>
                    <select name="active">
                        <option>请选择</option>
                        <?php if (!empty($output['active_list'])) { ?>
                            <?php foreach ($output['active_list'] as $key => $value) { ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </p>
            </dd>
        </dl>
        <dl>
            <dt>关联版式：</dt>
            <dd style="padding-top: 5px">
                <p>
                    <label>顶部版式</label>
                    <select name="plate_top">
                        <option>请选择</option>
                        <?php if (!empty($output['plate_list'][1])) { ?>
                            <?php foreach ($output['plate_list'][1] as $val) { ?>
                                <option value="<?php echo $val['plate_id'] ?>"
                                        <?php if ($output['goods']['plateid_top'] == $val['plate_id']) { ?>selected="selected"<?php } ?>><?php echo $val['plate_name']; ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </p>
                <p>
                    <label>底部版式</label>
                    <select name="plate_bottom">
                        <option>请选择</option>
                        <?php if (!empty($output['plate_list'][0])) { ?>
                            <?php foreach ($output['plate_list'][0] as $val) { ?>
                                <option value="<?php echo $val['plate_id'] ?>"
                                        <?php if ($output['goods']['plateid_bottom'] == $val['plate_id']) { ?>selected="selected"<?php } ?>><?php echo $val['plate_name']; ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </p>
                <p class="hint">如不填，所有已选活动的版式将制空，请谨慎操作。秒杀活动结束后商品版式需手动更改。</p>
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