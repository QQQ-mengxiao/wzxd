<?php defined('In718Shop') or exit('Access Invalid!'); ?>

<div class="tabmenu">
    <?php include template('layout/submenu'); ?></div>
<div></div>
<table class="ncsc-default-table">
    <thead>
    <tr>
        <th class="w20" style="padding: 16px 8px">ID</th>
        <th class="w40" style="padding: 8px 8px">仓库编码</th>
        <th class="w80" style="padding: 8px 8px">仓库名称</th>
        <th class="w40" style="padding: 8px 8px">操作人</th>
        <th class="w120" style="padding: 8px 8px">同步时间</th>
        <th class="w20" style="padding: 8px 8px">状态</th>
        <th class="w20" style="padding: 8px 8px">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($output['storage_log_list']) && is_array($output['storage_log_list'])) { ?>
        <?php foreach ($output['storage_log_list'] as $key => $storage_log) { ?>
            <tr class="bd-line">
                <td><?php echo $storage_log['storage_log_id']; ?></td>
                <td><?php echo $storage_log['storage_code']; ?></td>
                <td>
                    <span><?php echo $storage_log['storage_name']; ?></span>
                </td>
                <td><?php echo $storage_log['member_name']; ?></td>
                <td><?php echo date('Y-m-d H:i:s',$storage_log['addtime']); ?></td>
                <td><?php echo $storage_log['state']; ?></td>
                <td><a href="index.php?act=storage&op=storage_log_detail&storage_log_id=<?php echo $storage_log['storage_log_id'];?>"class="btn-blue">
                        <p>查看</p>
                    </a>
                </td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr>
            <td colspan="20" class="norecord">
                <div class="warning-option"><i
                            class="icon-warning-sign"></i><span>没有符合条件的记录</span></div>
            </td>
        </tr>
    <?php } ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
    </tr>
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
