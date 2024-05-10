<!--商家管理中心-订单物流-实物交易订单-->
<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<!-- <div class="tabmenu">
  <ul class="tab pngFix">
  <li class="active"><a href="index.php?act=store_company_order">所有订单</a></li><li class="normal"><a href="index.php?act=store_company_order&amp;op=index&amp;state_type=state_new">待付款</a></li><li class="normal"><a href="index.php?act=store_company_order&amp;op=store_order&amp;state_type=state_pay">待发货</a></li><li class="normal"><a href="index.php?act=store_company_order&amp;op=index&amp;state_type=state_send">待取货</a></li><li class="normal"><a href="index.php?act=store_company_order&amp;op=index&amp;state_type=state_success">已完成</a></li><li class="normal"><a href="index.php?act=store_company_order&amp;op=index&amp;state_type=state_cancel">已取消</a></li></ul>
</div> -->
<form method="get" action="index.php" target="_self">
  <table class="search-form">
    <input type="hidden" name="act" value="store_company_order" />
    <input type="hidden" name="op" value="index" />
    <?php if ($_GET['state_type']) { ?>
    <input type="hidden" name="state_type" value="<?php echo $_GET['state_type']; ?>" /><!---->
    <?php } ?>
    <tr>
      
      <?php if ($_GET['state_type'] == 'store_order') { ?>
      <input type="checkbox" id="skip_off" value="1" <?php echo $_GET['skip_off'] == 1 ? 'checked="checked"' : null;?>  name="skip_off"><label for="skip_off">不显示已关闭的订单</label>
      <?php } ?>
    <th><?php echo $lang['store_order_add_time'];?></th><!--下单时间-->
     <td class="w450"><input type="text" class="ui_timepicker" name="query_start_date" id="query_start_date" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date" class=" ui_timepicker" type="text" name="query_end_date" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td><!--开始时间--><!--结束时间-->
    <th><?php echo $lang['store_order_buyer'];?></th><!--买家-->
    <td class="w100"><input type="text" class="text w150" name="buyer_name" value="<?php echo $_GET['buyer_name']; ?>" /></td>
    <th><?php echo $lang['store_order_order_sn'];?></th><!--订单编号-->
    <td class="w160"><input type="text" class="text w150" name="order_sn" value="<?php echo $_GET['order_sn']; ?>" /></td>
    </tr>
    <tr>
      <th>提货时间</th>
     <td class="w450"><input type="text" class="ui_timepicker" name="ziti_query_start_date" id="ziti_query_start_date" value="<?php echo $_GET['ziti_query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="ziti_query_end_date" class=" ui_timepicker" type="text" name="ziti_query_end_date" value="<?php echo $_GET['ziti_query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td><!--开始时间--><!--结束时间-->
     <th>提货地址</th>
     <td class="w200">
      <?php if (!empty($output['ziti_list'])) {?>
              <select name="ziti_id">
                  <option value="">请选择</option>
                  <?php foreach ($output['ziti_list'] as $val) {?>
                      <option value="<?php echo $val['address_id']?>"><?php echo $val['seller_name'];?></option>
                  <?php }?>
              </select>
          <?php }?>
     </td>
     <td class="w70 tc">
      <label class="submit-border">
        <input type="submit" class="submit" value="<?php echo $lang['store_order_search'];?>" /><!--搜索按钮-->
      </label>
      <!-- <label class="submit-border" style="background-color:#008000ba;width: 64px">
        <a id="export" href="index.php?act=store_company_order&op=export" style="color:white">导出</a>
      </label> -->
      </td>
      <td class="w70 tc" style="margin-left: -40px"><a style="margin-left: -40px;" id="export" href="index.php?act=store_company_order&op=export"><label class="submit-border" style="background-color:#008000ba;width: 64px;height: 28px;color:white;margin-left: -40px;cursor: pointer">导出</label></a>
      </td>
    </tr>
  </table>
</form>
<table class="ncsc-default-table order">
  <thead>
    <tr>
      <th class="w10"></th>
      <th colspan="2"><?php echo $lang['store_order_goods_detail'];?></th><!--商品详情-->
      <th class="w100"><?php echo $lang['store_order_goods_single_price'];?></th><!--单价（元）-->
      <th class="w40"><?php echo $lang['store_show_order_amount'];?></th><!--数量-->
      <th class="w110"><?php echo $lang['store_order_buyer'];?></th><!--买家-->
      <th class="w120"><?php echo $lang['store_order_sum'];?></th><!--订单金额-->
      <th class="w100">交易状态</th>
    </tr>
  </thead>
  <?php if (is_array($output['order_list']) and !empty($output['order_list'])) { ?>
  <?php foreach($output['order_list'] as $order_id => $order) { ?>
  <tbody>
    <tr>
      <td colspan="20" class="sep-row"></td>
    </tr>
    <tr>
      <th colspan="20"><span class="ml10"><?php echo $lang['store_order_order_sn'].$lang['nc_colon'];?><em><?php echo $order['order_sn']; ?></em><!--订单编号-->
        <?php if ($order['order_from'] != 1){?><!--下单用的是手机安卓端/IOS端/PC端-->
        <i class="icon-mobile-phone">
        </i>
        <?php }?>
</span> <span><?php echo $lang['store_order_add_time'].$lang['nc_colon'];?><em class="goods-time"><?php echo date("Y-m-d H:i:s",$order['add_time']); ?></em></span><!--下单时间-->
<?php if (!empty($order['ziti_ladder_time'])){ ?>
      <span>提货时间:<em class="goods-time"><?php echo date("Y-m-d H:i:s",$order['ziti_ladder_time']); ?></em></span><!--自提时间-->  <?php }?>
 </th>
    </tr>
    <?php $i = 0;?>
    <?php foreach($order['goods_list'] as $k => $goods) { ?><!--商品列表-->
    <?php $i++;?>
    <tr>
      <td class="bdl"></td>
      <td class="w70"><div class="ncsc-goods-thumb"><img src="<?php echo $goods['image_60_url'];?>" onMouseOver="toolTip('<img src=<?php echo $goods['image_240_url'];?>>')" onMouseOut="toolTip()"/></div></td>
      <td class="tl"><dl class="goods-name">
          <dt><?php echo $goods['goods_name']; ?></dt><!--商品名称及链接-->
          <dd>
            <?php if (!empty($goods['goods_type_cn'])){ ?>
            <span class="sale-type"><?php echo $goods['goods_type_cn'];?></span>
            <?php } ?>
          </dd>
        </dl></td>
      <td><?php echo $goods['goods_price']; ?></td><!--单价-->
      <td><?php echo $goods['goods_num']; ?></td><!--数量-->

      <!-- S 合并TD -->
      <?php if (($order['goods_count'] > 1 && $k ==0) || ($order['goods_count']) == 1){ ?>
      <td class="bdl" rowspan="<?php echo $order['goods_count'];?>">
	  <div class="buyer"><?php echo $order['buyer_name'];?>
          <p member_id="<?php echo $order['buyer_id'];?>">
            <?php if(!empty($order['extend_member']['member_qq'])){?>
            <a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $order['extend_member']['member_qq'];?>&site=qq&menu=yes" title="QQ: <?php echo $order['extend_member']['member_qq'];?>"><img border="0" src="http://wpa.qq.com/pa?p=2:<?php echo $order['extend_member']['member_qq'];?>:52" style=" vertical-align: middle;"/></a>
            <?php }?>
            <?php if(!empty($order['extend_member']['member_ww'])){?>
            <a target="_blank" href="http://amos.im.alisoft.com/msg.aw?v=2&uid=<?php echo $order['extend_member']['member_ww'];?>&site=cntaobao&s=2&charset=<?php echo CHARSET;?>" ><img border="0" src="http://amos.im.alisoft.com/online.aw?v=2&uid=<?php echo $order['extend_member']['member_ww'];?>&site=cntaobao&s=2&charset=<?php echo CHARSET;?>" alt="Wang Wang" style=" vertical-align: middle;" /></a>
            <?php }?>
          </p>
          <div class="buyer-info"> <em></em><!--买家信息-->
            <div class="con">
              <h3><i></i><span><?php echo $lang['store_order_buyer_info'];?></span></h3>
              <dl>
                <dt><?php echo $lang['store_order_receiver'].$lang['nc_colon'];?></dt>
                <dd><?php echo $order['extend_order_common']['reciver_name'];?></dd>
              </dl>
              <dl>
                <dt><?php echo $lang['store_order_phone'].$lang['nc_colon'];?></dt>
                <dd><?php echo $order['extend_order_common']['reciver_info']['phone'];?></dd>
              </dl>
              <dl>
                <dt>地址<?php echo $lang['nc_colon'];?></dt>
                <dd><?php echo $order['extend_order_common']['reciver_info']['address'];?></dd>
              </dl>
            </div>
          </div>
        </div></td>   
      <td class="bdl" rowspan="<?php echo $order['goods_count'];?>">
        <p class="ncsc-order-amount"><?php echo $order['order_amount']; ?></p>
      </td>
      <td class="bdl bdr" rowspan="<?php echo $order['goods_count'];?>"><p><?php if($order['refund_state']=='1'){echo '部分退款';}else if($order['refund_state']=='2'){echo '已关闭';}else{echo $order['state_desc'];}  ?>
          <?php if($order['evaluation_time']) { ?>
          <br/>
          <?php echo $lang['store_order_evaluated'];?>
          <?php } ?>
        </p>
        
        <!-- 订单查看 -->
        <!-- <p><a href="index.php?act=store_order&op=show_order&order_id=<?php echo $order_id;?>" target="_blank"><?php echo $lang['store_order_view_order'];?></a></p> -->
	    </td>

      <?php } ?>
      <!-- E 合并TD -->
    </tr>

    <!-- S 赠品列表 -->
    <?php if (!empty($order['zengpin_list']) && $i == count($order['goods_list'])) { ?>
    <tr>
      <td class="bdl"></td>
      <td colspan="4" class="tl"><div class="ncsc-goods-gift">赠品：
      <ul><?php foreach ($order['zengpin_list'] as $zengpin_info) { ?><li>
      <a title="赠品：<?php echo $zengpin_info['goods_name'];?> * <?php echo $zengpin_info['goods_num'];?>" href="<?php echo $zengpin_info['goods_url'];?>" target="_blank"><img src="<?php echo $zengpin_info['image_60_url'];?>" onMouseOver="toolTip('<img src=<?php echo $zengpin_info['image_240_url'];?>>')" onMouseOut="toolTip()"/></a></li></ul>
      <?php } ?>
      </div></td>
    </tr>
    <?php } ?>
    <!-- E 赠品列表 -->
      
    <?php }?>
     <?php } }else { ?> 
    <tr>
      <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
    </tr>
    <?php } ?> 
  </tbody>
  <tfoot>
    <?php if (is_array($output['order_list']) and !empty($output['order_list'])) { ?>
    <tr>
      <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
    </tr>
    <?php } ?>
  </tfoot>
</table>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-1.7.1.min.js" ></script>
<!--引用jquery-ui-timepicker-addon.js插件,Datepicker日期选择插件只能精确到日，不能选择时间（时分秒），而jquery-ui-timepicker-addon.js正是基于jQuery UI Datepicker的一款可选时间的插件-->
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-1.8.17.custom.min.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-timepicker-addon.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-timepicker-zh-CN.js" ></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-1.8.17.custom.css"  />
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-timepicker-addon.css"/>
<script type="text/javascript">
    function deliver_explainsave(order_id){
        var deliver_explains = 'deliver_explain'+order_id;
        var deliver_explain = $("#"+deliver_explains).val();
        $.getJSON('index.php?act=store_deliver&op=deliver_explainsave&deliver_explain='+deliver_explain+'&order_id='+order_id,        function(data){
            if(data){
                if(data.result){
                    alert('发货备忘保存成功');
                }else{
                    alert('发货备忘保存失败');
                }
            }else{
                alert('err');
            }
        });
    }
$(function(){
    $('#query_start_date').datetimepicker({dateFormat: 'yy-mm-dd',showSecond:true,timeFormat:'hh:mm:ss',stepHour:1,stepMinute:1,stepSecond:1});
    $('#query_end_date').datetimepicker({dateFormat: 'yy-mm-dd',showSecond:true,timeFormat:'hh:mm:ss',stepHour:1,stepMinute:1,stepSecond:1});
    $('#ziti_query_start_date').datetimepicker({dateFormat: 'yy-mm-dd',showSecond:true,timeFormat:'hh:mm:ss',stepHour:1,stepMinute:1,stepSecond:1});
    $('#ziti_query_end_date').datetimepicker({dateFormat: 'yy-mm-dd',showSecond:true,timeFormat:'hh:mm:ss',stepHour:1,stepMinute:1,stepSecond:1});
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
