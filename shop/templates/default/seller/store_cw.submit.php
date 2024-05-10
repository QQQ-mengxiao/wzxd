<!--商家管理中心-订单物流-实物交易订单-->
<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div><!--所有订单/待付款/待发货/已发货/已完成/已取消导航栏-->
<form method="get" action="index.php" target="_self">
  <table class="search-form">
    <input type="hidden" name="act" value="store_cw_submit" />
    <input type="hidden" name="op" value="order_submit" />
    <?php if ($_GET['state_type']) {?>
    <input type="hidden" name="state_type" value="<?php echo $_GET['state_type']; ?>" /><!---->
    <?php }?>
    <tr>

      <?php if ($_GET['state_type'] == 'store_order') {?>
      <input type="checkbox" id="skip_off" value="1" <?php echo $_GET['skip_off'] == 1 ? 'checked="checked"' : null; ?>  name="skip_off"><label for="skip_off">不显示已关闭的订单</label>
      <?php }?>
      <th>下单时间</th><!--下单时间-->
     <td class="w450"><input type="text" class="ui_timepicker" name="query_start_date" id="query_start_date" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date" class=" ui_timepicker" type="text" name="query_end_date" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td><!--开始时间--><!--结束时间-->
      <th>买家</th><!--买家-->
      <td class="w100"><input type="text" class="text w150" name="buyer_name" value="<?php echo $_GET['buyer_name']; ?>" /></td>

      <!--xinzeng 11.1-->
       <th>发货人</th><!--发货人-->
    <td class="w100"><input type="text" class="text w150" name="senderusername" value="<?php echo $_GET['senderusername']; ?>" /></td>
  </tr>
      <tr>

	  <!--xinjia-->
	  <th>收货人</th><!--收货人-->
	  <td class="w100"><input type="text" class="text w200" name="consignee_name" value="<?php echo $_GET['consignee_name']; ?>" /></td>

    <th>自提点</th>
    <td >
      <select name="address_id" class="w100">
        <option value="">--请选择--</option>
        <?php foreach ($output['address_list'] as $val) {?>
            <option <?php if ($_GET['address_id'] == $val['address_id']) {?>selected<?php }?> value="<?php echo $val['address_id']; ?>"><?php echo $val['seller_name']; ?></option>
        <?php }?>
      </select>
    </td>
    </tr>
  <tr>
      <th>订单编号</th><!--订单编号-->
      <td class="w160"><input type="text" class="text w150" name="order_sn" value="<?php echo $_GET['order_sn']; ?>" /></td>
      <td class="w160"></td>
      <td class="w70 tc"><label class="submit-border">
          <input type="submit" class="submit" value="搜索" /><!--搜索按钮-->
        </label></td>
    </tr>
  </table>
</form>
<table class="ncsc-default-table order">
  <thead>
    <tr>
      <th class="w10"></th>
      <th colspan="2">商品详情</th><!--商品详情-->
      <th class="w100">单价（元）</th><!--单价（元）-->
      <th class="w40">数量</th><!--数量-->
      <th class="w100">商品货号</th><!--商品货号-->
      <th class="w100">商品条码</th><!--商品条码-->
      <th class="w80">买家</th><!--买家-->
      <th class="w80">订单金额</th><!--订单金额-->
      <th class="w100">状态/操作</th>
    </tr>
  </thead>
  <?php if (is_array($output['order_list']) and !empty($output['order_list'])) {?>
  <?php foreach ($output['order_list'] as $order_id => $order) {?>
  <tbody>
    <tr>
      <td colspan="20" class="sep-row"></td>
    </tr>
    <tr>
      <th colspan="20"><span class="ml10">订单编号：<em><?php echo $order['order_sn']; ?></em><!--订单编号-->
        <?php if ($order['order_from'] != 1) {?><!--下单用的是手机安卓端/IOS端/PC端-->
        <i class="icon-mobile-phone">
        </i>
        <?php }?>
</span> <span>下单时间：<em class="goods-time"><?php echo date("Y-m-d H:i:s", $order['add_time']); ?></em></span><!--下单时间-->
<?php if (!empty($order['ziti_ladder_time'])) {?>
      <span>自提时间:<em class="goods-time"><?php echo date("Y-m-d H:i:s", $order['ziti_ladder_time']); ?></em></span><!--自提时间-->  <?php }?>
<span class="fr mr5"> <a href="index.php?act=store_order_print&order_id=<?php echo $order_id; ?>" class="ncsc-btn-mini" target="_blank" title="打印发货单"/><i class="icon-print"></i>打印发货单</a></span><!--打印发货单-->
 </th>
    </tr>
    <?php $i = 0;?>
    <?php foreach ($order['goods_list'] as $k => $goods) {?><!--商品列表-->
    <?php $i++;?>
    <tr>
      <td class="bdl"></td>
      <td class="w70"><div class="ncsc-goods-thumb"><a href="<?php echo $goods['goods_url']; ?>" target="_blank"><img src="<?php echo $goods['image_60_url']; ?>" onMouseOver="toolTip('<img src=<?php echo $goods['image_240_url']; ?>>')" onMouseOut="toolTip()"/></a></div></td>
      <td class="tl"><dl class="goods-name">
          <dt><a target="_blank" href="<?php echo $goods['goods_url']; ?>"><?php echo $goods['goods_name']; ?></a></dt><!--商品名称及链接-->
          <dd>
            <?php if (!empty($goods['goods_type_cn'])) {?>
            <span class="sale-type"><?php echo $goods['goods_type_cn']; ?></span>
            <?php }?>
              <?php if ($goods['is_cw'] == 1) {?>
                  <i class="icon-cloud blue"></i>
              <?php }?>
          </dd>
        </dl></td>
      <td><?php echo $goods['goods_price']; ?></td><!--单价-->
      <td><?php echo $goods['goods_num']; ?></td><!--数量-->
      <td><input id="goods_serial" type="text" class="text w140" onblur="serial('<?php echo $goods['goods_id']; ?>','<?php echo $goods['order_id']; ?>',$(this).val())" value="<?php echo $goods['goods_serial']; ?>"/></td><!--货号-->
      <td><input id="goods_barcode" type="text" class="text w140" onblur="barcode('<?php echo $goods['goods_id']; ?>','<?php echo $goods['order_id']; ?>',$(this).val())" value="<?php echo $goods['goods_barcode']; ?>"/></td><!--条码-->

      <!-- S 合并TD -->
      <?php if (($order['goods_count'] > 1 && $k == 0) || ($order['goods_count']) == 1) {?>
      <td class="bdl" rowspan="<?php echo $order['goods_count']; ?>">
	  <div class="buyer"><?php echo $order['buyer_name']; ?>
          <p member_id="<?php echo $order['buyer_id']; ?>">
            <?php if (!empty($order['extend_member']['member_qq'])) {?>
            <a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $order['extend_member']['member_qq']; ?>&site=qq&menu=yes" title="QQ: <?php echo $order['extend_member']['member_qq']; ?>"><img border="0" src="http://wpa.qq.com/pa?p=2:<?php echo $order['extend_member']['member_qq']; ?>:52" style=" vertical-align: middle;"/></a>
            <?php }?>
            <?php if (!empty($order['extend_member']['member_ww'])) {?>
            <a target="_blank" href="http://amos.im.alisoft.com/msg.aw?v=2&uid=<?php echo $order['extend_member']['member_ww']; ?>&site=cntaobao&s=2&charset=<?php echo CHARSET; ?>" ><img border="0" src="http://amos.im.alisoft.com/online.aw?v=2&uid=<?php echo $order['extend_member']['member_ww']; ?>&site=cntaobao&s=2&charset=<?php echo CHARSET; ?>" alt="Wang Wang" style=" vertical-align: middle;" /></a>
            <?php }?>
          </p>
          <div class="buyer-info"> <em></em><!--买家信息-->
            <div class="con">
              <h3><i></i><span>联系信息</span></h3>
              <dl>
                <dt>姓名：</dt>
                <dd><?php echo $order['extend_order_common']['reciver_name']; ?></dd>
              </dl>
              <dl>
                <dt>电话：</dt>
                <dd><?php echo $order['extend_order_common']['reciver_info']['phone']; ?></dd>
              </dl>
              <dl>
                <dt>地址：</dt>
                <dd><?php echo $order['extend_order_common']['reciver_info']['address']; ?></dd>
              </dl>
            </div>
          </div>
        </div></td>
        <!-- <td class="bdl" rowspan="<?php echo $order['goods_count']; ?>"> -->


      <td class="bdl" rowspan="<?php echo $order['goods_count']; ?>"><p class="ncsc-order-amount"><?php echo $order['order_amount']; ?></p>
        <p class="goods-freight">
          <?php if ($order['shipping_fee'] > 0) {?>
          (<?php echo $lang['store_show_order_shipping_han'] ?>运费<?php echo $order['shipping_fee']; ?>)
          <?php } else {?>
          <?php echo $lang['nc_common_shipping_free']; ?>
          <?php }?>
        </p>
        <p class="goods-pay" title="<?php echo $lang['store_order_pay_method'] . $lang['nc_colon']; ?><?php echo $order['payment_name']; ?>"><?php echo $order['payment_name']; ?></p></td>
      <td class="bdl bdr" rowspan="<?php echo $order['goods_count']; ?>"><p><?php if ($order['refund_state'] == '1') {echo '部分退款';} else if ($order['refund_state'] == '2') {echo '已关闭';} else {echo $order['state_desc'];}?>
          <?php if ($order['evaluation_time']) {?>
          <br/>
          <?php echo $lang['store_order_evaluated']; ?>
          <?php }?>
        </p>

        <!-- 订单查看 -->
        <p><a href="javascript:void(0)" class="ncsc-btn-mini ncsc-btn-blue mt10" uri="index.php?act=store_cw_submit&op=cw_submit&order_id=<?php echo $order['order_id']; ?>" dialog_width="480" dialog_title="补推订单" nc_type="dialog"  dialog_id="store_cw_submit"/><i class="icon-share"></i>补推订单</a></p>
        <!-- <p><a href="index.php?act=store_order&op=show_order&order_id=<?php echo $order_id; ?>" target="_blank">订单详情</a></p> -->

        <!-- 物流跟踪 -->
        <p>
          <?php if ($order['if_deliver']) {?>
          <a href='index.php?act=store_deliver&op=search_deliver&order_sn=<?php echo $order['order_sn']; ?>'><?php echo $lang['store_order_show_deliver']; ?></a>
          <?php }?>
        </p>


	</td>


      <?php }?>
      <!-- E 合并TD -->
    </tr>

    <!-- S 赠品列表 -->
    <?php if (!empty($order['zengpin_list']) && $i == count($order['goods_list'])) {?>
    <tr>
      <td class="bdl"></td>
      <td colspan="4" class="tl"><div class="ncsc-goods-gift">赠品：
      <ul><?php foreach ($order['zengpin_list'] as $zengpin_info) {?><li>
      <a title="赠品：<?php echo $zengpin_info['goods_name']; ?> * <?php echo $zengpin_info['goods_num']; ?>" href="<?php echo $zengpin_info['goods_url']; ?>" target="_blank"><img src="<?php echo $zengpin_info['image_60_url']; ?>" onMouseOver="toolTip('<img src=<?php echo $zengpin_info['image_240_url']; ?>>')" onMouseOut="toolTip()"/></a></li></ul>
      <?php }?>
      </div></td>
    </tr>
    <?php }?>
    <!-- E 赠品列表 -->

    <?php }?>
     <?php }} else {?>
    <tr>
      <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record']; ?></span></div></td>
    </tr>
    <?php }?>
  </tbody>
  <tfoot>
    <?php if (is_array($output['order_list']) and !empty($output['order_list'])) {?>
    <tr>
      <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
    </tr>
    <?php }?>
  </tfoot>
</table>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/i18n/zh-CN.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/time-add/jquery-1.7.1.min.js" ></script>
<!--引用jquery-ui-timepicker-addon.js插件,Datepicker日期选择插件只能精确到日，不能选择时间（时分秒），而jquery-ui-timepicker-addon.js正是基于jQuery UI Datepicker的一款可选时间的插件-->
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/time-add/jquery-ui-1.8.17.custom.min.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/time-add/jquery-ui-timepicker-addon.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/time-add/jquery-ui-timepicker-zh-CN.js" ></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL; ?>/js/time-add/jquery-ui-1.8.17.custom.css"  />
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL; ?>/js/time-add/jquery-ui-timepicker-addon.css"/>
<script type="text/javascript">
   function serial(goods_id,order_id,value) {
        $.post('<?php echo urlShop('store_cw_submit', 'goods_serial'); ?>',
            {
              goods_id: goods_id,
              order_id: order_id,
              value: value,
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
    function barcode(goods_id,order_id,value) {
        $.post('<?php echo urlShop('store_cw_submit', 'goods_barcode'); ?>',
            {
              goods_id: goods_id,
              order_id: order_id,
              value: value,
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
$(function(){
    $('#query_start_date').datetimepicker({dateFormat: 'yy-mm-dd',showSecond:true,timeFormat:'hh:mm:ss',stepHour:1,stepMinute:1,stepSecond:1});
    $('#query_end_date').datetimepicker({dateFormat: 'yy-mm-dd',showSecond:true,timeFormat:'hh:mm:ss',stepHour:1,stepMinute:1,stepSecond:1});
    $('.checkall_s').click(function(){
        var if_check = $(this).attr('checked');
        $('.checkitem').each(function(){
            if(!this.disabled)
            {
                $(this).attr('checked', if_check);
            }
        });
        $('.checkall_s').attr('checked', if_check);
    });
    $('#skip_off').click(function(){
        url = location.href.replace(/&skip_off=\d*/g,'');
        window.location.href = url + '&skip_off=' + ($('#skip_off').attr('checked') ? '1' : '0');
    });
});

</script>
