<?php defined('In718Shop') or exit('Access Invalid!'); ?>

<div class="tabmenu">
    <?php include template('layout/submenu'); ?>
</div>
<div class="alert mt15 mb5"><strong>操作提示：</strong>
    <ul>
        <li>1、填写需要设置的佣金比例（佣金比例设置范围为所选一级分类下的所有商品）。</li>
        <li>
            <font color="red">2、限时秒杀、限时折扣正在活动中的商品佣金比例也会更新，请谨慎操作。</font>
        </li>
    </ul>
</div>
<table class="ncsc-default-table">
    <thead>
        <tr>
            <th class="w80" style="padding: 16px 8px;font-weight:revert;color:darkcyan">ID</th>
            <th class="w150" style="padding: 16px 8px;font-weight:revert;color:darkcyan">一级分类名称</th>
            <th class="w100" style="padding: 8px 8px;font-weight:revert;color:darkcyan">佣金比例</th>
            <th class="w120" style="padding: 8px 8px;font-weight:revert;color:darkcyan">上次设置时间</th>
            <th class="w60" style="padding: 8px 8px;font-weight:revert;color:darkcyan">上次设置账号</th>
            <th class="w100" style="padding: 8px 8px;font-weight:revert;color:darkcyan">设置</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($output['gc_list']) && is_array($output['gc_list'])) { ?>
            <?php foreach ($output['gc_list'] as $key => $goods_class) { ?>
                <tr class="bd-line" style="line-height: 40px;">
                    <td style="color:#555"><?php echo $goods_class['gc_id']; ?></td>
                    <td style="color:#555"><?php echo $goods_class['gc_name']; ?></td>
                    <td style="color:#555"><?php echo $goods_class['commis_rate'].'%'; ?></td>
                    <td style="color:#555"><?php echo $goods_class['edittime']; ?></td>
                    <td style="color:#555"><?php echo $goods_class['seller_name']; ?></td>
                    <td style="color:#555">
                        <span>
                            <a href="javascript:void(0);" dialog_id="edit" dialog_width="300" dialog_title="设置佣金比例" nc_type="dialog" uri="index.php?act=commission&op=edit&gc_id=<?php echo $goods_class['gc_id']; ?>" class="btn-blue">
                                <i class="icon-edit">设置</i>
                            </a>
                        </span>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>
<script src="<?php echo RESOURCE_SITE_URL; ?>/js/common_select.js"></script>
<script>
</script>