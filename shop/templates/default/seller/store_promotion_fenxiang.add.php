<?php defined('In718Shop') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<div class="ncsc-form-default">
  <div class="alert alert-block">
    <h4><?php echo $lang['nc_explain'].$lang['nc_colon'];?></h4>
    <ul>
      <li>选择的代金券为已生成的代金券模板</li>
      <li>每个级别赠送至少需要选择一种</li> </ul>
  </div>
  <form id="add_form" action="index.php?act=store_promotion_fenxiang&op=mansong_save" method="post">
    <input id="level2_flag" type="hidden" value="false" />
    <input id="level3_flag" type="hidden" value="false" />
    <dl>
      <dt><i class="required">*</i><?php echo $lang['mansong_name'].$lang['nc_colon'];?></dt>
      <dd>
         <select   id="mansong_name"  name="mansong_name">
              <option value="0">分享送券</option>
              <option value="1">分享新人注册送券</option>
              <option value="2">分享新人下单送券</option>
              </select>
        <!-- <input id="mansong_name" name="mansong_name" type="text" maxlength="25" class="w400 text"/>
        <span class="error-message"></span> -->
        <p class="hint"><?php echo $lang['mansong_name_explain'];?></p>
      </dd>
    </dl>
    <!-- <dl>
      <dt><i class="required">*</i><?php echo '订单限额'.$lang['nc_colon'];?></dt>
      <dd>
        <input id="order_limit" name="order_limit" type="text" maxlength="25" class="w400 text"/>
        <span class="error-message"></span>
      </dd>
    </dl> -->
    <dl> 
      <dt><i class="required">*</i>代金券规则<?php echo $lang['nc_colon'];?></dt>
      <dd>
        <input type="hidden" id="mansong_rule_count" name="rule_count">
        <ul id="mansong_rule_list" class="ncsc-mansong-rule-list">
        </ul>
        <a href="javascript:void(0);" id="btn_add_rule" class="ncsc-btn ncsc-btn-acidblue"><i class="icon-plus-sign"></i>添加规则</a>
        <div id="div_add_rule" style="display:none;">
        <div class="ncsc-mansong-error"><span id="mansong_price_error" style="display:none;"><i class="icon-exclamation-sign"></i>代金券模板id不能为空且必须为数字</span><span id="mansong_discount_error" style="display:none;"><i class="icon-exclamation-sign"></i>发放的代金券数量为每人获得的数量</span></div>
        <div class="ncsc-mansong-rule">
        <span>代金券模板id&nbsp;<input id="mansong_price" type="text" class="text w50"></span>
        <span>发放的代金券数量&nbsp;<input id="mansong_discount" type="text" class="text w50"></span>
            <div id="mansong_rule_error" style="display:none;">请至少选择一种促销方式</div>
            <div class="mt10">
            <a href="javascript:void(0);" id="btn_save_rule" class="ncsc-btn ncsc-btn-acidblue"><i class="icon-ok-circle"></i>确定规则设置</a>
            <a href="javascript:void(0);" id="btn_cancel_add_rule" class="ncsc-btn ncsc-btn-orange"><i class="icon-ban-circle"></i>取消</a></div>
        </div>
        <span class="error-message"></span>
        <p class="hint">代金券模板id和发放数量为必填。<br/>系统最多支持设置五组等级规则。</p>
      </dd>
    </dl>
    <dl>
      <dt><?php echo $lang['text_remark'].$lang['nc_colon'];?></dt>
      <dd>
        <textarea name="remark" rows="3" id="remark" maxlength="100" class="textarea w400"></textarea>
        <p class="hint"><?php echo $lang['mansong_remark_explain'];?></p>
      </dd>
    </dl>
    <div class="bottom">
      <label class="submit-border"><input id="submit_button" type="submit" value="<?php echo $lang['nc_submit'];?>"  class="submit"></label>
    </div>
  </form>
</div>
<script id="mansong_rule_template" type="text/html">
<li nctype="mansong_rule_item">
<span>发放代金券模板id为<strong><%=price%></strong>的代金券 </span>
<span><strong><%=discount%></strong>张，</span>
<input type="hidden" name="mansong_rule[]" value="<%=price%>,<%=discount%>">
<a nctype="btn_del_mansong_rule" href="javascript:void(0);" class="ncsc-btn-mini ncsc-btn-red"><i class="icon-trash"></i>删除</a>
</li>
</script>
<script id="mansong_goods_template" type="text/html">
    <div nctype="mansong_goods" class="selected-mansong-goods">
    <a href="<%=goods_url%>" title="<%=goods_name%>" class="goods-thumb" target="_blank">
        <img src="<%=goods_image_url%>"/>
    </a>
    <input nctype="mansong_goods_id" type="hidden" value="<%=goods_id%>">
    </div><a nctype="btn_del_mansong_goods" href="javascript:void(0);" class="ncsc-btn-mini ncsc-btn-red"><i class="icon-trash"></i>删除已选择的礼品</a>
</script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/common.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/template.min.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css"  />
<script type="text/javascript">
$(document).ready(function(){
    $('#start_time').datetimepicker({
        controlType: 'select'
    });
    $('#end_time').datetimepicker({
        controlType: 'select'
    });

    jQuery.validator.methods.greaterThanDate = function(value, element, param) {
        var date1 = new Date(Date.parse(param.replace(/-/g, "/")));
        var date2 = new Date(Date.parse(value.replace(/-/g, "/")));
        return date1 < date2;
    };
    jQuery.validator.methods.lessThanDate = function(value, element, param) {
        var date1 = new Date(Date.parse(param.replace(/-/g, "/")));
        var date2 = new Date(Date.parse(value.replace(/-/g, "/")));
        return date1 > date2;
    };
    jQuery.validator.methods.greaterThanStartDate = function(value, element) {
        var start_date = $("#start_time").val();
        var date1 = new Date(Date.parse(start_date.replace(/-/g, "/")));
        var date2 = new Date(Date.parse(value.replace(/-/g, "/")));
        return date1 < date2;
    };

    //页面输入内容验证
    $("#add_form").validate({
        errorPlacement: function(error, element){
            var error_td = element.parent('dd').children('span.error-message');
            error_td.append(error);
        },
        onfocusout: false,
        submitHandler:function(form){
            ajaxpost('add_form', '', '', 'onerror');
        },
        rules : {
            mansong_name : {
                required : true
            },
            order_limit : {
                required : true,
                number : true
            },
            rule_count: {
                required: true,
                min: 1
            }
        },
        messages : {
            mansong_name : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['mansong_name_error'];?>'
            },
              order_limit : {
                required : '<i class="icon-exclamation-sign"></i>赠送限额不能为空且必须是数字',
                number : '<i class="icon-exclamation-sign"></i>赠送限额不能为空且必须是数字'
            },
            rule_count: {
                required: '<i class="icon-exclamation-sign"></i>请至少添加一条规则并确定',
                min: '<i class="icon-exclamation-sign"></i>请至少添加一条规则并确定'
            }
        }
    });

    // 限时添加规则窗口
    $('#btn_add_rule').on('click', function() {
        $('#mansong_price').val('');
        $('#mansong_discount').val('');
        $('#mansong_goods_item').html('');
        $('#mansong_price_error').hide();
        $('#mansong_rule_error').hide();
        $('#div_add_rule').show();
        $('#btn_add_rule').hide();
    });

    // 规则保存
    $('#btn_save_rule').on('click', function() {
        var mansong = {};
        mansong.price = Number($('#mansong_price').val());
        if(isNaN(mansong.price) || mansong.price <= 0) {
            $('#mansong_price_error').show();
            return false;
        } else {
            $('#mansong_price_error').hide();
        }
        mansong.discount = Number($('#mansong_discount').val());
        if(isNaN(mansong.discount) || mansong.discount < 0 || mansong.discount > mansong.price) {
            $('#mansong_discount_error').show();
            return false;
        } else {
            $('#mansong_discount_error').hide();
        }
        if(mansong.discount == 0 && mansong.goods_id == 0) {
            $('#mansong_rule_error').show();
            return false;
        } else {
            $('#mansong_rule_error').hide();
        }
        var mansong_rule_item = template.render('mansong_rule_template', mansong);
        $('#mansong_rule_list').append(mansong_rule_item);
        close_div_add_rule();
    });

    // 删除已添加的规则
    $('#mansong_rule_list').on('click', '[nctype="btn_del_mansong_rule"]', function() {
        $(this).parents('[nctype="mansong_rule_item"]').remove();
        close_div_add_rule();
    });

    // 取消添加规则
    $('#btn_cancel_add_rule').on('click', function() {
        close_div_add_rule();
    });

    // 关闭规则添加窗口
    function close_div_add_rule() {
        var rule_count = $('#mansong_rule_list').find('[nctype="mansong_rule_item"]').length;
        if( rule_count >= 5) {
            $('#btn_add_rule').hide();
        } else {
            $('#btn_add_rule').show();
        }
        $('#div_add_rule').hide();
        $('#mansong_rule_count').val(rule_count);
    }

    // 限时商品选择窗口
    $('#btn_show_search_goods').on('click', function() {
        $('#div_search_goods').show();
    });

    // 搜索商品
    $('#btn_search_goods').on('click', function() {
        var url = "<?php echo urlShop('store_promotion_mansong', 'search_goods');?>";
        url += '&' + $.param({goods_name: $('#search_goods_name').val()});
        $('#div_goods_search_result').load(url);
    });

    // 搜索商品翻页
    $('#div_goods_search_result').on('click', 'a.demo', function() {
        $('#div_goods_search_result').load($(this).attr('href'));
        return false;
    });

    // 关闭商品选择窗口
    $('#btn_hide_search_goods').on('click', function() {
        $('#div_search_goods').hide();
    });

    // 选择商品
    $('#div_goods_search_result').on('click', '[nctype="btn_add_mansong_goods"]', function() {
        var goods = {};
        goods.goods_id = $(this).attr('data-goods-id');
        goods.goods_name = $(this).attr('data-goods-name');
        goods.goods_image_url = $(this).attr('data-goods-image-url');
        goods.goods_url = $(this).attr('data-goods-url');
        var mansong_goods_item = template.render('mansong_goods_template', goods);
        $('#mansong_goods_item').html(mansong_goods_item);
        $('#div_search_goods').hide();
    });

    // 删除以选的商品
    $('#mansong_goods_item').on('click', '[nctype="btn_del_mansong_goods"]', function() {
        $('#mansong_goods_item').html('');
    });

});
</script>
