<?php defined('In718Shop') or exit('Access Invalid!'); ?>


<table class="ncsc-default-table" style="margin: 10px 10px 10px 10px;width: 98%">
    <thead>
    <tr style="border-bottom: double;border-width: thin;">
        <th class="w90">发货人</th>
        <th class="w150">发货地址</th>
        <th class="w150">发货仓库</th>
        <th class="w150">联系电话</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($output['address_list']) && is_array($output['address_list'])) { ?>
        <?php foreach ($output['address_list'] as $key => $address) { ?>
            <tr class="bd-line">
                <td><?php echo $address['seller_name']; ?></td>
                <td class="tel"><?php echo $address['area_info']; ?>&nbsp;<?php echo $address['address']; ?></td>
                <td><span class="tel"><?php echo $address['storage_name']; ?></span></td>
                <td><span class="tel"><?php echo $address['telphone']; ?></span></td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr>
            <td colspan="20" class="norecord">
                <div class="warning-option"><i
                            class="icon-warning-sign"></i><span><?php echo $lang['no_record']; ?></span></div>
            </td>
        </tr>
    <?php } ?>
    </tbody>
    <tfoot>
    </tfoot>
</table>
<script src="<?php echo RESOURCE_SITE_URL; ?>/js/common_select.js"></script>
<script>
    $(function () {
        $('input[name="is_default"]').on('click', function () {
            $.get('index.php?act=store_deliver_set&op=daddress_default_set&address_id=' + $(this).val(), function (result) {
            })
        });
    });
</script> 
