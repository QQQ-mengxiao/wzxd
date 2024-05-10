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
            var url = "<?php echo urlShop('store_buy_deliver', 'goods_select');?>";
            //url += '&' + $.param({goods_name: $('#search_goods_name').val()});
            url = url + '&' + $.param({goods_name: $('#search_goods_name').val()}) + '&' + $.param({goods_serial:$('#search_goods_serial').val()}) + '&' + $.param({buy_deliver_id:<?php echo $_GET['buy_deliver_id'];?>});
            $('#div_goods_search_result').load(url);
        });
        $('#div_goods_search_result').on('click', 'a.demo', function() {
            $('#div_goods_search_result').load($(this).attr('href'));
            return false;
        });

        //添加即买即送商品弹出窗口 
        $('#div_goods_search_result').on('click', '[nctype="btn_add_buy_deliver_goods"]', function() {
            $('#dialog_goods_id').val($(this).attr('data-goods-id'));
            $('#dialog_goods_name').text($(this).attr('data-goods-name'));
            $('#dialog_goods_price').text($(this).attr('data-goods-price'));
            //$('#dialog_goods_marketprice').text($(this).attr('data-goods-marketprice'));
            $('#dialog_input_goods_price').val($(this).attr('data-goods-price'));
            $('#dialog_goods_sort').val('9999');
            $('#dialog_goods_img').attr('src', $(this).attr('data-goods-img'));
            $('#dialog_add_buy_deliver_goods').nc_show_dialog({width: 550, title: '添加商品'});
            // $('#dialog_xinren_price').val('');
            // $('#dialog_xinren_app_price').val('');
            // $('#dialog_add_xinren_goods_error').hide();
        });

        //添加即买即送商品
        $('#div_goods_search_result').on('click', '#btn_submit', function() {
            var goods_id = $('#dialog_goods_id').val();
            var buy_deliver_id = <?php echo $_GET['buy_deliver_id'];?>;
            var goods_sort = $('#dialog_goods_sort').val();
            //var goods_price = Number($('#dialog_input_goods_price').val());
            //var xinren_price = Number($('#dialog_xinren_price').val());
            //var xinren_app_price = Number($('#dialog_xinren_app_price').val());
            // if(!isNaN(xinren_price) && xinren_price > 0 && xinren_price < goods_price) {
                $.post('<?php echo urlShop('store_buy_deliver', 'buy_deliver_goods_add');?>', 
                    {goods_id: goods_id,buy_deliver_id:buy_deliver_id,goods_sort:goods_sort},
                    function(data) {
                        if(data.result) {
                            $('#dialog_add_buy_deliver_goods').hide();
                            $('#buy_deliver_goods_list').prepend(template.render('buy_deliver_goods_list_template', data.buy_deliver_goods)).hide().fadeIn('slow');
                            $('#buy_deliver_goods_list_norecord').hide();
                            showSucc(data.message);
                        } else {
                            showError(data.message);
                        }
                    }, 
                'json');
            // } else {
            //     $('#dialog_add_xinren_goods_error').show();
            // }
        });

        //编辑即买即送商品
        // $('#xinren_goods_list').on('click', '[nctype="btn_edit_buy_deliver_goods"]', function() {
        //     $edit_item = $(this).parents('tr.bd-line');
        //     var buy_deliver_goods_id = $(this).attr('data-buy-deliver-goods-id');
        //     //var xinren_app_price = $edit_item.find('[nctype="xinren_app_price"]').text();
        //     var goods_price = $(this).attr('data-goods-price');
        //     //var goods_marketprice = $(this).attr('data-goods-marketprice');
        //     $('#dialog_buy_deliver_goods_id').val(buy_deliver_goods_id);
        //     $('#dialog_edit_goods_price').text(goods_price);
        //     //$('#dialog_edit_goods_marketprice').text(goods_marketprice);
        //     //$('#dialog_edit_xinren_price').val(xinren_price);
        //     //$('#dialog_edit_xianshi_app_price').val(xianshi_app_price);
        //     $('#dialog_edit_buy_deliver_goods').nc_show_dialog({width: 450, title: '修改价格'});
        // });

        // $('#btn_edit_xinren_goods_submit').on('click', function() {
        //     var xinren_goods_id = $('#dialog_xinren_goods_id').val();
        //     var xinren_price = Number($('#dialog_edit_xinren_price').val());
        //     //var xinren_app_price = Number($('#dialog_edit_xinren_app_price').val());
        //     <!--市场价-->
        //     //var goods_marketprice = Number($('#dialog_edit_goods_marketprice').text());
        //     var goods_price = Number($('#dialog_edit_goods_price').text());
        //     <!--市场价限制-->
        //     if(!isNaN(xinren_price) && xinren_price > 0 && xinren_price < goods_price ) {
        //         $.post('<?php echo urlShop('store_promotion_xinren', 'xinren_goods_price_edit');?>',
        //             {xinren_goods_id: xinren_goods_id, xinren_price: xinren_price},
        //             function(data) {
        //                 // console.log(data,"----------------------")
        //                 if(data.result) {
        //                     $edit_item.find('[nctype="xinren_price"]').text(data.xinren_price);
        //                     //$edit_item.find('[nctype="xinren_app_price"]').text(data.xianshi_app_price);
        //                     $edit_item.find('[nctype="xinren_discount"]').text(data.xinren_discount);
        //                     $('#dialog_edit_xinren_goods').hide();
        //                 } else {
        //                     showError(data.message);
        //                 }
        //             }, 'json'
        //         ); 
        //     } else {
        //         $('#dialog_edit_xinren_goods_error').show();
        //     }
        // });

        //删除即买即送商品
        $('#buy_deliver_goods_list').on('click', '[nctype="btn_del_buy_deliver_goods"]', function() {
            var $this = $(this);
            if(confirm('确认删除？')) {
                var buy_deliver_goods_id = $(this).attr('data-buy-deliver-goods-id');
                $.post('<?php echo urlShop('store_buy_deliver', 'buy_deliver_goods_delete');?>',
                    {buy_deliver_goods_id: buy_deliver_goods_id},
                    function(data) {
                        if(data.result) {
                            
                            $this.parents('tr').hide('slow', function() {
                                var buy_deliver_goods_count = $('#buy_deliver_goods_list').find('.bd-line:visible').length;
                                if(buy_deliver_goods_count <= 0) {
                                    $('#buy_deliver_goods_list_norecord').show();
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
        <?php $starsting = json_encode($output['buy_deliver_goods_list']);?>
        buy_deliver_goods_array = <?php ob_start();echo $starsting;ob_flush();flush();ob_end_clean();?>;//0712MX去掉json解析部分
        if(buy_deliver_goods_array.length > 0) {
            var buy_deliver_goods_list = '';
            $.each(buy_deliver_goods_array, function(index, buy_deliver_goods) {
                buy_deliver_goods_list += template.render('buy_deliver_goods_list_template', buy_deliver_goods);
            });
//            $('#xianshi_goods_list').prepend(xianshi_goods_list);
            $('#buy_deliver_goods_list').prepend(buy_deliver_goods_list);
        } else {
            $('#buy_deliver_goods_list_norecord').show();
        }
    });
</script>
<div class="tabmenu">
    <?php include template('layout/submenu');?>
    <a id="btn_show_goods_select" class="ncsc-btn ncsc-btn-green" href="javascript:;"><i></i>添加商品</a>
    <!-- <?php if($output['xinren_info']['editable']) { ?>
    <a id="btn_show_goods_select" class="ncsc-btn ncsc-btn-green" href="javascript:;"><i></i>添加商品</a> </div>
    <?php } ?> -->
<!-- <table class="ncsc-default-table">
  <tbody>
    <tr>
      <td class="w90 tr"><strong><?php echo $lang['xianshi_name'].$lang['nc_colon'];?></strong></td>
      <td class="w120 tl"><?php echo $output['xianshi_info']['xianshi_name'];?></td>
      <td class="w90 tr"><strong><?php echo $lang['start_time'].$lang['nc_colon'];?></strong></td>
      <td class="w120 tl"><?php echo date('Y-m-d H:i',$output['xianshi_info']['start_time']);?></td>
      <td class="w90 tr"><strong><?php echo $lang['end_time'].$lang['nc_colon'];?></strong></td>
      <td class="w120 tl"><?php echo date('Y-m-d H:i',$output['xianshi_info']['end_time']);?></td>
      <td class="w90 tr"><strong><?php echo '购买下限'.$lang['nc_colon'];?></strong></td>
      <td class="w120 tl"><?php echo $output['xianshi_info']['lower_limit'];?></td>
      <td class="w90 tr"><strong><?php echo '状态'.$lang['nc_colon'];?></strong></td>
      <td class="w120 tl"><?php echo $output['xianshi_info']['xianshi_state_text'];?></td>
    </tr>
</table> -->
<div class="alert">
  <strong>说明<?php echo $lang['nc_colon'];?></strong>
  <ul>
    <li>1.点击添加商品按钮可以搜索并添加参加活动的商品，点击删除按钮可以删除该商品</li>
    
  </ul>
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
    <input type="hidden" name="act" value="store_buy_deliver" />
    <input type="hidden" name="op" value="buy_deliver_manage" />
    <tr>
      <td>&nbsp;</td>
      <td class="w160"><input type="text" style="display:none;" class="text w150" name="buy_deliver_id" value="<?php echo  $output['buy_deliver_info']['buy_deliver_id'];?>"/></td>
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
      <th class="w10"></th>
      <th class="w50"></th>
      <th class="tl">商品名称</th>
      <th class="w90">商品价格</th>
      <!--<th class="w90">市场价</th>-->
      <!-- <th class="w120">新人专享价格</th> -->
      <!-- <th class="w120">App端折扣价格</th> -->
      <!--<th class="w120">折扣率</th>-->
      <th class="w120">操作</th>
    </tr>
  </thead>
  <tbody id="buy_deliver_goods_list">
    <tr id="buy_deliver_goods_list_norecord" style="display:none">
      <td class="norecord" colspan="20"><div class="warning-option"><i class="icon-warning-sign"></i><span>暂无符合条件的数据记录</span></div></td>
    </tr>
  </tbody>
</table>
<tr>
    <td colspan="20">
        <div class="pagination">
            <?php if(!empty($output['buy_deliver_goods_list'])){echo $output['show_page'];} ?>
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
    <!-- <dl><dt>App端折扣：</dt><dd><input id="dialog_edit_xianshi_app_price" type="text" class="text w70"><em class="add-on"><i class="icon-renminbi"></i></em>
    </dl>   -->  
    <div class="eject_con">
        <div class="bottom pt10 pb10"><a id="btn_edit_buy_deliver_goods_submit" class="submit" href="javascript:void(0);">提交</a></div>
    </div>
</div>
<script id="buy_deliver_goods_list_template" type="text/html">
<tr class="bd-line">
    <td><input id="goods_sort" type="text" class="text w40" value="<%=goods_sort%>" onblur="sort('<%=buy_deliver_goods_id%>',$(this).val())" style=" margin-right: 10px;"/></td>
    <td><div class="pic-thumb"><a href="<%=goods_url%>" target="_blank"><img src="<%=image_url%>" alt=""></a></div></td>
    <td class="tl"><dl class="goods-name"><dt><a href="<%=goods_url%>" target="_blank"><%=goods_name%></a></dt></dl></td>
    <td><?php echo $lang['currency']; ?><%=goods_price%></td>
    <!--//<td><?php echo $lang['currency']; ?><%=goods_marketprice%></td>-->
    <!-- <td><?php echo $lang['currency']; ?><span nctype="xinren_price"><%=xinren_price%></span></td> -->
    <!-- <td><?php echo $lang['currency']; ?><span nctype="xinren_app_price"><%=xianshi_app_price%></span></td> -->
    <!--<td><span nctype="xianshi_discount"><%=xianshi_discount%></span></td>-->
    <td class="nscs-table-handle">
    <!-- <span><a nctype="btn_edit_buy_deliver_goods" class="btn-blue" data-xinren-goods-id="<%=buy_deliver_goods_id%>" data-goods-price="<%=goods_price%>" href="javascript:void(0);"><i class="icon-edit"></i><p>编辑</p></a></span> -->
        <span><a nctype="btn_del_buy_deliver_goods" class="btn-red" data-buy-deliver-goods-id="<%=buy_deliver_goods_id%>" href="javascript:void(0);"><i class="icon-trash"></i><p>删除</p></a></span>
    </td>
</tr>

</script>
    <script>
        function sort(buy_deliver_goods_id,value) {
            $.post('<?php echo urlShop('store_buy_deliver', 'goods_sort');?>',
                {
                    goods_sort: value,
                    buy_deliver_goods_id: buy_deliver_goods_id,
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