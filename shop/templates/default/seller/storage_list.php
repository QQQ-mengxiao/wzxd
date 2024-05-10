<?php defined('In718Shop') or exit('Access Invalid!'); ?>

<div class="tabmenu">
    <?php include template('layout/submenu'); ?>
    <a href="javascript:void(0)" class="ncsc-btn ncsc-btn-green" nc_type="dialog" dialog_title="新增仓库"
       dialog_id="storage_add" uri="index.php?act=storage&op=storage_add" dialog_width="550" title="新增仓库">新增</a></div>
<div></div>
<table class="ncsc-default-table">
    <thead>
    <tr>
        <th class="w250" style="padding: 16px 8px">仓库名称</th>
        <!--<th class="w60" style="padding: 8px 8px">仓库编号</th>
        <th class="w120" style="padding: 8px 8px">账号/密码</th>-->
        <th class="w120" style="padding: 8px 8px">上次同步时间</th>
        <th class="w60" style="padding: 8px 8px">上次同步人</th>
        <th class="w120" style="padding: 8px 8px">备注</th>
        <th class="w121" style="padding: 8px 8px">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($output['storage_list']) && is_array($output['storage_list'])) { ?>
        <?php foreach ($output['storage_list'] as $key => $storage) { ?>
            <tr class="bd-line">
                <td><?php echo $storage['storage_name']; ?></td>
                <!--<td><?php echo $storage['storage_code']; ?></td>
                <td>
                    <span><?php echo $storage['storage_username']; ?></span><br/><span><?php echo $storage['storage_password']; ?></span>
                </td>-->
                <td><?php echo date('Y-m-d H:i:s',$storage['last_synchro_time']); ?></td>
                <td><?php echo $storage['last_synchro_member_id']==0?'系统':$storage['last_synchro_member_id']; ?></td>
                <td><?php echo $storage['storage_explain']; ?></td>
                <td class="nscs-table-handle">
                    <!--<span>
                        <a href="javascript:void(0)" onclick="ajax_get_confirm('确定要同步吗？', 'index.php?act=storage&op=storage_synchro&storage_id=<?php echo $storage['storage_id']; ?>');" class="btn-red" style="color: blue;">
                            <i class="icon-refresh"></i>
                            <p>同步</p>
                        </a>
                    </span>-->
                    <span>
                        <a href="javascript:void(0);" dialog_id="storage_detail" dialog_width="1040" dialog_title="详情" nc_type="dialog" uri="index.php?act=storage&op=storage_detail&storage_id=<?php echo $storage['storage_id']; ?>" class="btn-blue">
                            <i class="icon-th-list"></i>
                            <p>详情</p>
                        </a>
                    </span>
                    <span>
                        <a href="javascript:void(0);" dialog_id="storage_edit" dialog_width="620" dialog_title="编辑" nc_type="dialog" uri="index.php?act=storage&op=storage_add&storage_id=<?php echo $storage['storage_id']; ?>" class="btn-blue">
                            <i class="icon-edit"></i>
                            <p>编辑</p>
                        </a>
                    </span>
                    <span style="display: none;">
                        <a href="javascript:void(0)" onclick="ajax_get_confirm('您确定要删除吗？', 'index.php?act=storage&op=storage_del&storage_id=<?php echo $storage['storage_id']; ?>');" class="btn-red" style="color: red;">
                            <i class="icon-trash"></i>
                            <p>删除</p>
                        </a>
                    </span>
                </td>
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
    <tr>
        <td colspan="20">&nbsp;</td>
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
