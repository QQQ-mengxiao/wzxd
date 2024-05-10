<?php defined('In718Shop') or exit('Access Invalid!');?>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/template.min.js" charset="utf-8"></script>
<script type="text/javascript">
    $(document).ready(function(){

        // 当前编辑对象，默认为空
        $edit_item = {};

        //现实商品搜索
        $('#btn_show_goods_select').on('click', function() {
            $('#div_goods_select').show();
        });

        //隐藏商品搜索
        $('#btn_hide_goods_select').on('click', function() {
            $('#div_goods_select').hide();
        });

        //搜索商品
        $('#btn_search_goods').on('click', function() {
            // var url = "<?php echo urlShop('store_voucher_type', 'goods_select');?>";
            // url = url + '&' + $.param({goods_name: $('#search_goods_name').val()}) + '&' + $.param({goods_serial:$('#search_goods_serial').val()}) + '&' + $.param({tid :<?php echo $_GET['tid'];?>});stc_id: $('#stc_id').val()
            // $('#div_goods_search_result').load(url);
            var stc_id = $('#stc_id').val();
            var xianshi_id = <?php echo $_GET['tid'];?>;
                $.post('<?php echo urlShop('store_voucher_type', 'vouchertype_goodsclass_add');?>', 
                    {
                        stc_id: stc_id,
                        xianshi_id: xianshi_id,
                       
                    },
                    function(data) {console.log(data);
                        if(data.result) {
                            $('#dialog_add_xianshi_goods').hide();
                            $('#xianshi_goods_list').prepend(template.render('xianshi_goods_list_template', data.xianshi_goods)).hide().fadeIn('slow');
                            $('#xianshi_goods_list_norecord').hide();
                            showSucc(data.message);
                        } else {
                            showError(data.message);
                        }
                    }, 
                'json');
        });
        $('#div_goods_search_result').on('click', 'a.demo', function() {
            $('#div_goods_search_result').load($(this).attr('href'));
            return false;
        });

        //添加限时折扣商品弹出窗口 
        $('#div_goods_search_result').on('click', '[nctype="btn_add_xianshi_goods"]', function() {
            $('#dialog_goods_id').val($(this).attr('data-goods-id'));
            $('#dialog_goods_name').text($(this).attr('data-goods-name'));
            $('#dialog_goods_price').text($(this).attr('data-goods-price'));
            //$('#dialog_goods_marketprice').text($(this).attr('data-goods-marketprice'));
            $('#dialog_input_goods_price').val($(this).attr('data-goods-price'));
            $('#dialog_goods_img').attr('src', $(this).attr('data-goods-img'));
            $('#dialog_add_xianshi_goods').nc_show_dialog({width: 550, title: '添加商品'});
            $('#dialog_xianshi_price').val('');
            $('#dialog_xianshi_app_price').val('');
            $('#dialog_add_xianshi_goods_error').hide();
            $('#dialog_xianshi_upper_limit').val('');
            $('#dialog_xianshi_goods_sort').val('9999');
        });

        //添加限时折扣商品
        $('#div_goods_search_result').on('click', '#btn_submit', function() {
            var goods_id = $('#dialog_goods_id').val();
            var xianshi_id = <?php echo $_GET['tid'];?>;
                $.post('<?php echo urlShop('store_voucher_type', 'vouchertype_goods_add');?>', 
                    {
                        goods_id: goods_id,
                        xianshi_id: xianshi_id,
                       
                    },
                    function(data) {
                        if(data.result) {
                            $('#dialog_add_xianshi_goods').hide();
                            $('#xianshi_goods_list').prepend(template.render('xianshi_goods_list_template', data.xianshi_goods)).hide().fadeIn('slow');
                            $('#xianshi_goods_list_norecord').hide();
                            showSucc(data.message);
                        } else {
                            showError(data.message);
                        }
                    }, 
                'json');
        });

        //删除限时活动商品
        $('#xianshi_goods_list').on('click', '[nctype="btn_del_xianshi_goods"]', function() {
            var $this = $(this);
            if(confirm('确认删除？')) {
                var xianshi_goods_id = $(this).attr('data-xianshi-goods-id');
                 var xianshi_id =  <?php echo $_GET['tid'];?>;
                $.post('<?php echo urlShop('store_voucher_type', 'vouchertype_goodsclass_delete');?>',
                    {class_id: xianshi_goods_id,
                        xianshi_id: xianshi_id},
                    function(data) {console.log(data);
                        if(data.result) {
                            $this.parents('tr').hide('slow', function() {
                                var xianshi_goods_count = $('#xianshi_goods_list').find('.bd-line:visible').length;
                                if(xianshi_goods_count <= 0) {
                                    $('#xianshi_goods_list_norecord').show();
                                }
                            });
                        } else {
                            showError(data.message);
                        }
                    }, 'json'
                );
            }
        });

        //渲染限时折扣商品列表
//渲染限时折扣商品列表
        <?php $starsting = json_encode($output['xianshi_goods_list']);?>
        xianshi_goods_array = <?php ob_start();echo $starsting;ob_flush();flush();ob_end_clean();?>;//0712MX去掉json解析部分
        if(xianshi_goods_array.length > 0) {
            var xianshi_goods_list = '';
            $.each(xianshi_goods_array, function(index, xianshi_goods) {
                xianshi_goods_list += template.render('xianshi_goods_list_template', xianshi_goods);
            });
//            $('#xianshi_goods_list').prepend(xianshi_goods_list);
            $('#xianshi_goods_list').prepend(xianshi_goods_list);
        } else {
            $('#xianshi_goods_list_norecord').show();
        }
    });
</script>
<div class="tabmenu">
    <?php include template('layout/submenu');?>
    <?php if($output['xianshi_info']) { ?>
        
    <a id="btn_show_goods_select" class="ncsc-btn ncsc-btn-green" href="javascript:;"><i></i>添加分类</a> </div>
    <?php } ?>

<div class="alert">
  <strong><?php echo $lang['nc_explain'];?><?php echo $lang['nc_colon'];?></strong>
  <ul>
    <li>1、增加或者删除代金券满足条件的分类</li>
    <li>2、选择一级或者二级分类直接存储下属的所有三级分类</li>
  </ul>
</div>
<!-- 商品搜索 -->
<div id="div_goods_select" class="div-goods-select" style="display: none;">
    <table class="search-form">
      <tr><th class="w150"><strong>商品分类：</strong></th>
 <td class="w80">
            <select name="stc_id" id="stc_id" class="w150">
                    <option value="0"><?php echo $lang['nc_please_choose'];?></option>
                    <?php if(is_array($output['store_goods_class']) && !empty($output['store_goods_class'])){?>
                        <?php foreach ($output['store_goods_class'] as $val) {?>
                            <option value="<?php echo $val['gc_id']; ?>" <?php if ($_GET['gc_id'] == $val['gc_id']){ echo 'selected=selected';}?>><?php echo $val['gc_name']; ?></option>
                        <?php }?>
                    <?php }?>
                </select>
        </td>
        <td class="w70 tc"><a href="javascript:void(0);" id="btn_search_goods" class="ncsc-btn"/><i class="icon-search"></i>添加</a></td><td class="w10"></td><td><p class="hint">选择一级或者二级分类直接存储下属的所有三级分类</p></td>
      </tr>
    </table>
  <div id="div_goods_search_result" class="search-result"></div>
  <a id="btn_hide_goods_select" class="close" href="javascript:void(0);">X</a> </div>

  <form method="get">
  <table class="search-form">
    <input type="hidden" name="act" value="store_voucher_type" />
    <input type="hidden" name="op" value="vouchertype1_manage" />
    <tr>
      <td>&nbsp;</td>
      <td class="w160"><input type="text" style="display:none;" class="text w150" name="tid" value="<?php echo  $output['xianshi_info']['voucher_tid'];?>"/></td>
      <th class="w110">分类名称</th>
      <td class="w160"><input type="text"  class="text w150" name="goods_name" value="<?php echo $_GET['goods_name'];?>"/></td>
      <td class="w70 tc"><label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_search'];?>" /></label></td>
    </tr>
  </table>
</form>
<table class="ncsc-default-table">
  <thead>
    <tr>
      <th class="w10"></th>
      <th class="tl">商品分类名称</th>
      <th class="w90">商品分类id</th>
      <th class="w120"><?php echo $lang['nc_handle'];?></th>
    </tr>
  </thead>
  <tbody id="xianshi_goods_list">
    <tr id="xianshi_goods_list_norecord" style="display:none">
      <td class="norecord" colspan="20"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
    </tr>
  </tbody>
</table>
<tr>
    <td colspan="20">
        <div class="pagination">
            <?php if(!empty($output['xianshi_goods_list'])){echo $output['show_page'];} ?>
        </div>
    </td>
</tr>
<div class="bottom">
  <label class="submit-border"><input type="submit" class="submit" id="submit_back" value="<?php echo $lang['nc_back'].$lang['xianshi_index'];?>" onclick="window.location='index.php?act=store_promotion_xianshi&op=xianshi_list'"></label>
</div>
<div id="dialog_edit_xianshi_goods" class="eject_con" style="display:none;">
    <input id="dialog_xianshi_goods_id" type="hidden">
    <!--<dl><dt>市场价格：</dt><dd><span id="dialog_edit_goods_marketprice"></dd>
    </dl>-->
    <dl>
        <dt>商品价格：</dt>
        <dd style="padding-top: 5px;"><span id="dialog_edit_goods_price"></dd>
    </dl>
    <dl>
        <dt>秒杀价格：</dt>
        <dd style="padding-top: 5px;"><input id="dialog_edit_xianshi_price" type="text" class="text w70"><em class="add-on"><i
                        class="icon-renminbi"></i></em>
            <p id="dialog_edit_xianshi_goods_error" style="display:none;"><label for="dialog_edit_xianshi_goods_error"
                                                                                 class="error"><i
                            class='icon-exclamation-sign'></i>秒杀价格不能为空，且必须小于商品价格</label></p>
    </dl>
    <dl>
        <dt>购买上限：</dt>
        <dd style="padding-top: 5px;"><input id="dialog_xianshi_upper_limit" type="text" class="text w70"><em class="add-on"><i><B>件</B></i></em>
        <label>0为无限制</label></dd>
    </dl>
    <!--<dl><dt>App端折扣：</dt><dd><input id="dialog_edit_xianshi_app_price" type="text" class="text w70"><em class="add-on"><i class="icon-renminbi"></i></em>
    </dl>   --> 
    <div class="eject_con">
        <div class="bottom pt10 pb10"><a id="btn_edit_xianshi_goods_submit" class="submit" href="javascript:void(0);">提交</a></div>
    </div>
</div>
<script id="xianshi_goods_list_template" type="text/html">
<tr class="bd-line">
    
    <td><div class="pic-thumb"><a href="<%=goods_url%>" target="_blank"><img src="<%=image_url%>" alt=""></a></div></td>
    <td class="tl"><dl class="goods-name"><dt><a href="" target="_blank"><%=gc_name%></a></dt></dl></td>
    <td><%=gc_id%></td>
      <td class="nscs-table-handle">
        <span><a nctype="btn_del_xianshi_goods" class="btn-red" data-xianshi-goods-id="<%=gc_id%>" href="javascript:void(0);"><i class="icon-trash"></i><p><?php echo $lang['nc_del'];?></p></a></span>

    </td>
</tr>

</script>
<script>
    function sort(xianshi_goods_id,value) {
        $.post('<?php echo urlShop('store_promotion_xianshi', 'goods_sort');?>',
            {
                goods_sort: value,
                xianshi_goods_id: xianshi_goods_id,
            },
            function (data) {
                if (data) {
                    if (data.code == 1) {
                        showDialog(data.msg, 'succ');
                    } else {
                        showDialog(data.msg, 'fail');
                    }
                }
            },
            'json');
    }
</script>
