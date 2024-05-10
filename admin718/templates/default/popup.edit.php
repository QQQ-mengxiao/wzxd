<?php defined('In718Shop') or exit('Access Invalid!');?>
<style type="text/css">
    .dialog_content {
        overflow: hidden;
        padding: 0 15px 15px !important;
    }
</style>

<div class="page">
    <!-- 页面导航 -->
    <div class="fixed-bar">
        <div class="item-title">
            <h3><?php echo '弹窗设置';?></h3>
            <ul class="tab-base">
                <li><a href="index.php?act=mb_special1&op=special_list"><span><?php echo $lang['nc_manage'];?></span></a></li>
                <!-- <li><a href="JavaScript:void(0);" class="current"><span><?php echo '弹窗';?></span></a></li>
                <li><a href="index.php?act=mb_special1&op=popup_set&item_id=320"><span><?php echo '弹窗开关';?></span></a></li> -->
            </ul>
        </div>
    </div>
    <div class="fixed-empty"></div>
    <form id="form_item" action="<?php echo urlAdmin('mb_special1', 'popup_save');?>" method="post">
        <input type="hidden" name="special_id" value="1000">
        <input type="hidden" name="item_id" value="320">
        <table class="table tb-type2 nohover">
            <tbody>
            <?php $item_data = $output['item_info']['item_data'];?>
            <?php $item_edit_flag = true;?>
            <tr class="noborder">
                <td style="height: auto; padding: 0;"><div id="item_edit_content" class="mb-item-edit-content">
                        <?php require('mb_special1_item.module_image2.php');?>
                    </div></td>
            </tr>
            </tbody>
            <tfoot>
            <tr class="tfoot">
                <td colspan="2"><a id="btn_save" class="btn" href="javascript:;"><span>保存编辑</span></a>
                    </td>
            </tr>
            </tfoot>
        </table>
    </form>
</div>
<div id="dialog_item_edit_image" style="display:none;">
    <div class="s-tips margintop"><i></i>请按提示尺寸制作上传图片，已达到最佳显示效果。</div>
    <div class="upload-thumb"> <img id="dialog_item_image" src="" alt=""></div>
    <input id="dialog_item_image_name" type="hidden">
    <input id="dialog_type" type="hidden">
    <form id="form_image" action="">
        <div class="dialog-handle-box clearfix">
            <h4 class="dialog-handle-title">选择要上传的图片：</h4>
            <span>
      <input id="btn_upload_image" type="file" name="special_image">
      </span> <span id="dialog_image_desc" class="dialog-image-desc"></span>
      <?php if($output['item_info']['item_type']=='icon'){?>
            <h4 class="dialog-handle-title">图片名称：</h4>
            <input id="dialog_item_image_names" type="text" class="txt w200 marginright marginbot vatop">
            <?php }?>
         <!--    <h4 class="dialog-handle-title">图片名称：</h4>
            <input id="dialog_item_image_names" type="text" class="txt w200 marginright marginbot vatop"> -->
            <h4 class="dialog-handle-title">操作类型：</h4>
            <div>
                <select id="dialog_item_image_type" name="" class="vatop">
                    <!-- <option value="">-请选择-</option> -->
                    <option value="keyword">关键字</option>
<!--                    <option value="special">活动编号</option>-->
                    <option value="active">活动编号</option>
                    <option value="goods">商品编号</option>
                </select>
                <input id="dialog_item_image_data" type="text" class="txt w200 marginright marginbot vatop"><br />
                <span id="dialog_item_image_desc" class="dialog-image-desc"></span>
            </div>
        </div>
        <a id="btn_save_item" class="btn" href="javascript:;"><span>保存</span></a>
    </form>
</div>
<script id="item_image_template" type="text/html">
    <div nctype="item_image" class="item">
        <img nctype="image" src="<%=image%>" alt="">
        <input nctype="image_name" name="item_data[item][<%=image_name%>][image]" type="hidden" value="<%=image_name%>">
        <input nctype="image_names" name="item_data[item][<%=image_name%>][name]" type="hidden" value="<%=image_names%>">
        <input nctype="image_type" name="item_data[item][<%=image_name%>][type]" type="hidden" value="<%=image_type%>">
        <input nctype="image_data" name="item_data[item][<%=image_name%>][data]" type="hidden" value="<%=image_data%>">
        <a nctype="btn_del_item_image" href="javascript:;">删除</a>
    </div>
</script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.iframe-transport.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.ui.widget.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/template.min.js" charset="utf-8"></script>
<script type="text/javascript">
    var url_upload_image = '<?php echo urlAdmin('mb_special1', 'special_image_upload');?>';

    $(document).ready(function(){
        var $current_content = null;
        var $current_image = null;
        var $current_image_name = null;
        var $current_image_names = null;
        var $current_image_type = null;
        var $current_image_data = null;
        var old_image = '';
        var $dialog_item_image = $('#dialog_item_image');
        var $dialog_item_image_name = $('#dialog_item_image_name');
        var $dialog_item_image_names = $('#dialog_item_image_names');
        var special_id = <?php echo $output['item_info']['special_id'];?>;

        //保存
        $('#btn_save').on('click', function() {
            $('#form_item').submit();
        });

        //编辑图片
        $('[nctype="btn_edit_item_image"]').on('click', function() {
            //初始化当前图片对象
            $item_image = $(this).parents('[nctype="item_image"]');
            $current_image = $item_image.find('[nctype="image"]');
            $current_image_name = $item_image.find('[nctype="image_name"]');
            $current_image_names = $item_image.find('[nctype="image_names"]');
            $current_image_type = $item_image.find('[nctype="image_type"]');
            $current_image_data = $item_image.find('[nctype="image_data"]');

            $('#dialog_item_image').attr('src', $current_image.attr('src'));
            $('#dialog_item_image_name').val($current_image_name.val());
            $('#dialog_item_image_names').val($current_image_names.val());
            $('#dialog_item_image_type').val($current_image_type.val());
            $('#dialog_item_image_data').val($current_image_data.val());
            $('#dialog_image_desc').text('推荐图片尺寸' + $(this).attr('data-desc'));
            $('#dialog_type').val('edit');
            change_image_type_desc($('#dialog_item_image_type').val());
            $('#dialog_item_edit_image').nc_show_dialog({
                width: 600,
                title: '编辑'
            });
        });

        //添加图片
        $('[nctype="btn_add_item_image"]').on('click', function() {
            $dialog_item_image.hide();
            $dialog_item_image_name.val('');
            $current_content = $(this).parent().find('[nctype="item_content"]');
            $('#dialog_image_desc').text('推荐图片尺寸' + $(this).attr('data-desc'));
            $('#dialog_type').val('add');
            change_image_type_desc($('#dialog_item_image_type').val());
            $('#dialog_item_edit_image').nc_show_dialog({
                width: 600,
                title: '添加'
            });
        });

        //删除图片
        $('#item_edit_content').on('click', '[nctype="btn_del_item_image"]', function() {
            $(this).parents('[nctype="item_image"]').remove();
        });

        //图片上传
        $("#btn_upload_image").fileupload({
            dataType: 'json',
            url: url_upload_image,
            formData: {special_id: special_id},
            add: function(e, data) {
                old_image = $dialog_item_image.attr('src');
                $dialog_item_image.attr('src', LOADING_IMAGE);
                data.submit();
            },
            done: function (e, data) {
                var result = data.result;
                if(typeof result.error === 'undefined') {
                    $dialog_item_image.attr('src', result.image_url);
                    $dialog_item_image.show();
                    $dialog_item_image_name.val(result.image_name);
                } else {
                    $dialog_item_image.attr('src') = old_image;
                    showError(result.error);
                }
            }
        });

        $('#btn_save_item').on('click', function() {
            var type = $('#dialog_type').val();
            if(type == 'edit') {
                edit_item_image_save();
            } else {
                if($dialog_item_image_name.val() == '') {
                    showError('请上传图片');
                    return false;
                }
                add_item_image_save();
            }
            $('#dialog_item_edit_image').hide();
        });

        function edit_item_image_save() {
            $current_image.attr('src', $('#dialog_item_image').attr('src'));
            $current_image_name.val($('#dialog_item_image_name').val());
            $current_image_names.val($('#dialog_item_image_names').val());
            $current_image_type.val($('#dialog_item_image_type').val());
            $current_image_data.val($('#dialog_item_image_data').val());
        }

        function add_item_image_save() {
            var $html_item_image = $('#html_item_image');
            var item = {};
            item.image = $('#dialog_item_image').attr('src');
            item.image_name = $('#dialog_item_image_name').val();
            item.image_names = $('#dialog_item_image_names').val();
            item.image_type = $('#dialog_item_image_type').val();
            item.image_data = $('#dialog_item_image_data').val();
            $current_content.append(template.render('item_image_template', item));
        }


        $('#dialog_item_image_type').on('change', function() {
            change_image_type_desc($(this).val());
        });

        function change_image_type_desc(type) {
            var desc_array = {};
            var desc = '操作类型一共三种，对应点击以后的操作。';
            if(type != '') {
                desc_array['keyword'] = '关键字类型会根据搜索关键字跳转到商品搜索页面，输入框填写搜索关键字。';
                desc_array['active'] = '活动编号会跳转到指定的活动列表，1-阶梯价，3-新人专享，4-限时秒杀，5-即买即送。';
                desc_array['goods'] = '商品编号会跳转到指定的商品详细页面，输入框填写商品编号。';
                desc = desc_array[type];
            }
            $('#dialog_item_image_desc').text(desc);
        }
    });
</script>
