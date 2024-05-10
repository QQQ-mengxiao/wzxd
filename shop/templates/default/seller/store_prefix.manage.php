<?php defined('In718Shop') or exit('Access Invalid!');?>
<style type="text/css">
    .tabmenu .search-form a.none{
        position:relative;
    }
</style>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/template.min.js" charset="utf-8"></script>
<script type="text/javascript">
    $(document).ready(function(){

        // 当前编辑对象，默认为空
        $edit_item = {};

        //发货人搜索
        $('#btn_show_daddress_select').on('click', function() {
            $('#div_daddress_select').show();
        });

        //隐藏发货人搜索
        $('#btn_hide_daddress_select').on('click', function() {
            $('#div_daddress_select').hide();
        });

        //搜索发货人
        $('#btn_search_daddress').on('click', function() {
            var url = "<?php echo urlShop('store_prefix', 'daddress_select');?>";
            url = url + '&' + $.param({seller_name: $('#search_daddress_name').val()}) + '&' + $.param({prefix_id:<?php echo $_GET['prefix_id'];?>});
            $('#div_daddress_search_result').load(url);
        });
        $('#div_goods_search_result').on('click', 'a.demo', function() {
            $('#div_goods_search_result').load($(this).attr('href'));
            return false;
        });

        //添加发货人
        $('#div_daddress_search_result').on('click', '[nctype="btn_add_prefix_daddress"]', function() {
            var daddress_id = $(this).attr('data-daddress-id');
            var prefix_id = <?php echo $_GET['prefix_id'];?>;
            $.post('<?php echo urlShop('store_prefix', 'prefix_daddress_add');?>', 
                {daddress_id: daddress_id,prefix_id:prefix_id},
                function(data) {
                    if(data.result) {
                        // $('#dialog_add_prefix_goods').hide();
                        $('#prefix_goods_list').prepend(template.render('prefix_goods_list_template', data.prefix_goods)).hide().fadeIn('slow');
                        $('#prefix_goods_list_norecord').hide();
                        $('#prefix_goods_list_norecord').hide();
                        $('#div_goods_search_result').hide();
                        showSucc(data.message);
                    } else {
                        showError(data.message);
                    }
                }, 
            'json');
        });

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
            var url = "<?php echo urlShop('store_prefix', 'goods_select');?>";
            url = url + '&' + $.param({goods_name: $('#search_goods_name').val()}) + '&' + $.param({goods_serial:$('#search_goods_serial').val()}) + '&' + $.param({prefix_id:<?php echo $_GET['prefix_id'];?>});
            $('#div_goods_search_result').load(url);
        });
        $('#div_goods_search_result').on('click', 'a.demo', function() {
            $('#div_goods_search_result').load($(this).attr('href'));
            return false;
        });

        //添加商品弹出窗口 
        $('#div_goods_search_result').on('click', '[nctype="btn_add_prefix_goods"]', function() {
            $('#dialog_goods_id').val($(this).attr('data-goods-id'));
            $('#dialog_goods_name').text($(this).attr('data-goods-name'));
            $('#dialog_goods_img').attr('src', $(this).attr('data-goods-img'));
            $('#dialog_add_prefix_goods').nc_show_dialog({width: 550, title: '添加商品'});
        });

        //添加商品
        $('#div_goods_search_result').on('click', '#btn_submit', function() {
            var goods_commonid = $('#dialog_goods_id').val();
            var prefix_id = <?php echo $_GET['prefix_id'];?>;
            $.post('<?php echo urlShop('store_prefix', 'prefix_goods_add');?>', 
                {goods_commonid: goods_commonid,prefix_id:prefix_id},
                function(data) {
                    if(data.result) {
                        $('#dialog_add_prefix_goods').hide();
                        $('#prefix_goods_list').prepend(template.render('prefix_goods_list_template', data.prefix_goods)).hide().fadeIn('slow');
                        $('#prefix_goods_list_norecord').hide();
                        showSucc(data.message);
                    } else {
                        showError(data.message);
                    }
                }, 
            'json');
        });

        //删除即买即送商品
        $('#prefix_goods_list').on('click', '[nctype="btn_del_prefix_goods"]', function() {
            var $this = $(this);
            if(confirm('确认删除？')) {
                var prefix_goods_id = $(this).attr('data-prefix-goods-id');
                $.post('<?php echo urlShop('store_prefix', 'prefix_goods_delete');?>',
                    {prefix_goods_id: prefix_goods_id},
                    function(data) {
                        if(data.result) {
                            
                            $this.parents('tr').hide('slow', function() {
                                var prefix_goods_count = $('#prefix_goods_list').find('.bd-line:visible').length;
                                if(prefix_goods_count <= 0) {
                                    $('#prefix_goods_list_norecord').show();
                                }
                            });
                        } else {
                            showError(data.message);
                        }
                    }, 'json'
                );
            }
        });

        //渲染即买即送商品列表
        <?php $starsting = json_encode($output['prefix_goods_list']);?>
        prefix_goods_array = <?php ob_start();echo $starsting;ob_flush();flush();ob_end_clean();?>;//0712MX去掉json解析部分
        if(prefix_goods_array.length > 0) {
            var prefix_goods_list = '';
            $.each(prefix_goods_array, function(index, prefix_goods) {
                prefix_goods_list += template.render('prefix_goods_list_template', prefix_goods);
            });
            $('#prefix_goods_list').prepend(prefix_goods_list);
        } else {
            $('#prefix_goods_list_norecord').show();
        }
    });
</script>
<div class="tabmenu">
    <?php include template('layout/submenu');?>
    <a id="btn_show_daddress_select" class="ncsc-btn ncsc-btn-green" href="javascript:;"><i>添加发货人&emsp;&emsp;&emsp;&emsp;&emsp;</i></a>
    <a id="btn_show_goods_select" class="ncsc-btn ncsc-btn-green" href="javascript:;"><i></i>添加商品</a>
<div class="alert">
  <strong>说明<?php echo $lang['nc_colon'];?></strong>
  <ul>
    <li>1.点击添加商品按钮可以搜索并添加商品，点击删除按钮可以删除该商品</li>
    <li>2.点击添加发货人按钮可以搜索并添加发货人，确认添加可以添加发货人下所有商品</li>
  </ul>
</div>
<!-- 发货人搜索 -->
<div id="div_daddress_select" class="div-goods-select" style="display: none;">
    <table class="search-form">
      <tr><th class="w150"><strong>第一步：搜索发货人</strong></th>
          <td class="w160">发货人名称：<input id="search_daddress_name" type="text w150" class="text" name="daddress_name" value=""/></td>
        <td class="w70 tc"><a href="javascript:void(0);" id="btn_search_daddress" style="position:initial" class="ncsc-btn"/><i class="icon-search"></i><?php echo $lang['nc_search'];?></a></td><td class="w10"></td><td><p class="hint">不输入名称或货号直接搜索将显示店内所有发货人。</p></td>
      </tr>
    </table>
  <div id="div_daddress_search_result" class="search-result"></div>
  <a id="btn_hide_daddress_select" class="close" href="javascript:void(0);">X</a>
</div>
<!-- 商品搜索 -->
<div id="div_goods_select" class="div-goods-select" style="display: none;">
    <table class="search-form">
      <tr><th class="w150"><strong>第一步：搜索店内商品</strong></th>
          <td class="w160">商品名称：<input id="search_goods_name" type="text w150" class="text" name="goods_name" value=""/></td>
          <td class="w80">商品货号：<input id="search_goods_serial" type="text w70" class="text" name="goods_serial" value=""/></td>
        <td class="w70 tc"><a href="javascript:void(0);" id="btn_search_goods" style="position:initial" class="ncsc-btn"/><i class="icon-search"></i><?php echo $lang['nc_search'];?></a></td><td class="w10"></td><td><p class="hint">不输入名称或货号直接搜索将显示店内所有普通商品，特殊商品不能参加。</p></td>
      </tr>
    </table>
  <div id="div_goods_search_result" class="search-result"></div>
  <a id="btn_hide_goods_select" class="close" href="javascript:void(0);">X</a> </div>

  <form method="get">
  <table class="search-form">
    <input type="hidden" name="act" value="store_prefix" />
    <input type="hidden" name="op" value="prefix_manage" />
    <tr>
      <td>&nbsp;</td>
      <td class="w160"><input type="text" style="display:none;" class="text w150" name="prefix_id" value="<?php echo  $output['prefix_info']['prefix_id'];?>"/></td>
      <th class="w110">商品名称</th>
      <td class="w160"><input type="text"  class="text w150" name="goods_name" value="<?php echo $_GET['goods_name'];?>"/></td>
      <th class="w60">商品货号</th>
      <td class="w100"><input type="text"  class="text w100" name="goods_serial" value="<?php echo $_GET['goods_serial'];?>"/></td>
      <td class="w70 tc"><label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_search'];?>" /></label></td>
    </tr>
  </table>
</form>
<table class="ncsc-default-table">
  <thead>
    <tr>
      <!-- <th class="w10"></th> -->
      <th class="w50"></th>
      <th class="tl">商品名称</th>
      <th class="w120">操作</th>
    </tr>
  </thead>
  <tbody id="prefix_goods_list">
    <tr id="prefix_goods_list_norecord" style="display:none">
      <td class="norecord" colspan="20"><div class="warning-option"><i class="icon-warning-sign"></i><span>暂无符合条件的数据记录</span></div></td>
    </tr>
  </tbody>
</table>
<tr>
    <td colspan="20">
        <div class="pagination">
            <?php if(!empty($output['prefix_goods_list'])){echo $output['show_page'];} ?>
        </div>
    </td>
</tr>

<div id="dialog_edit_xinren_goods" class="eject_con" style="display:none;">
    <input id="dialog_xinren_goods_id" type="hidden">
    <!--<dl><dt>市场价格：</dt><dd><span id="dialog_edit_goods_marketprice"></dd>
    </dl>-->
    <dl><dt>商品价格：</dt><dd><span id="dialog_edit_goods_price"></dd>
    </dl>
    <dl><dt>新人专享价格：</dt><dd><input id="dialog_edit_xinren_price" type="text" class="text w70"><em class="add-on"><i class="icon-renminbi"></i></em>
    <p id="dialog_edit_xinren_goods_error" style="display:none;"><label for="dialog_edit_xinren_goods_error" class="error"><i class='icon-exclamation-sign'></i>新人专享价格不能为空，且必须小于商品价格</label></p>
    </dl>
    <div class="eject_con">
        <div class="bottom pt10 pb10"><a id="btn_edit_prefix_goods_submit" class="submit" href="javascript:void(0);">提交</a></div>
    </div>
</div>
<script id="prefix_goods_list_template" type="text/html">
<tr class="bd-line">
    <!-- <td><input id="goods_sort" type="text" class="text w40" value="<%=goods_sort%>" onblur="sort('<%=prefix_goods_id%>',$(this).val())" style=" margin-right: 10px;"/></td> -->
    <td><div class="pic-thumb"><a href="<%=goods_url%>" target="_blank"><img src="<%=image_url%>" alt=""></a></div></td>
    <td class="tl"><dl class="goods-name"><dt><a href="<%=goods_url%>" target="_blank"><%=goods_name%></a></dt></dl></td>
    <!-- <td><?php echo $lang['currency']; ?><%=goods_price%></td> -->
    <td class="nscs-table-handle">
        <span><a nctype="btn_del_prefix_goods" class="btn-red" data-prefix-goods-id="<%=prefix_goods_id%>" href="javascript:void(0);"><i class="icon-trash"></i><p>删除</p></a></span>
    </td>
</tr>

</script>
    <script>
        function sort(prefix_goods_id,value) {
            $.post('<?php echo urlShop('store_prefix', 'goods_sort');?>',
                {
                    goods_sort: value,
                    prefix_goods_id: prefix_goods_id,
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