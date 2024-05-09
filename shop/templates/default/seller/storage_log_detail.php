<?php defined('In718Shop') or exit('Access Invalid!'); ?>

<div class="tabmenu">
    <ul class="tab pngFix">
        <?php if(is_array($output['member_menu']) and !empty($output['member_menu'])) {
            foreach ($output['member_menu'] as $key => $val) {
                if($val['menu_key'] == $output['menu_key']) {
                    echo '<li class="normal"><a '.(isset($val['target'])?"target=".$val['target']:"").' href="'.$val['menu_url'].'">'.$val['menu_name'].'</a></li>';
                } else {
                    echo '<li class="normal"><a '.(isset($val['target'])?"target=".$val['target']:"").' href="'.$val['menu_url'].'">'.$val['menu_name'].'</a></li>';
                }
            }
        }
        ?>
        <li class="active"><a>同步详情查看</a></li>
    </ul>

</div>
<div></div>
<table class="ncsc-default-table">
    <thead>
    <tr>
        <th class="w40">仓库编码</th>
        <th class="w80">仓库名称</th>
        <th class="w40">操作人</th>
        <th class="w120">同步时间</th>
        <th class="w20">状态</th>
    </tr>
    </thead>
    <tbody>
            <tr class="bd-line">
                <td><?php echo $output['storage_log_info']['storage_code']; ?></td>
                <td><?php echo $output['storage_log_info']['storage_name']; ?></td>
                <td><?php echo $output['storage_log_info']['member_name']; ?></td>
                <td><?php echo date('Y-m-d H:i:s',$output['storage_log_info']['addtime']); ?></td>
                <td><?php echo $output['storage_log_info']['state']; ?></td>
            </tr>
    </tbody>
    <tfoot></tfoot>
</table>
<div class="item-publish">
    <form method="post" id="goods_form" action="<?php if ($output['edit_goods_sign']) { echo urlShop('store_goods_online', 'edit_save_goods');} else { echo urlShop('store_goods_add', 'save_goods');}?>">
        <div class="ncsc-form-goods">
            <dl>
                <dt>同步的商品信息</dt>
                <dd style="word-break: break-all">
                    <?php echo $output['storage_log_info']['goods_serial_all']; ?>
                </dd>
            </dl>
            <dl>
                <dt>同步成功的商品信息</dt>
                <dd style="word-break: break-all">
                    <?php echo $output['storage_log_info']['goods_serial_succ']; ?>
                </dd>
            </dl>
            <dl>
                <dt>同步失败的商品信息</dt>
                <dd style="word-break: break-all">
                    <?php echo $output['storage_log_info']['goods_serial_fail']; ?>
                </dd>
            </dl>
        </div>
    </form>
</div>



<script src="<?php echo RESOURCE_SITE_URL; ?>/js/common_select.js"></script>
<script>
    $(function () {
        $('input[name="is_default"]').on('click', function () {
            $.get('index.php?act=store_deliver_set&op=daddress_default_set&address_id=' + $(this).val(), function (result) {
            })
        });
    });
</script> 
