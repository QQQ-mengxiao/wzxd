<?php defined('In718Shop') or exit('Access Invalid!');?>
<style type="text/css">
  .eject_con{
    display: none;
    position: absolute; z-index: 1; top: 200px; left: 200px;
    width:550px;
    border: solid 3px #E6E6E6; box-shadow: 0 0 3px rgba(153,153,153,0.25);
    background-color: #F5F5F5;
  }
  .eject_con h5{margin-left: 10px;}
  .eject_con h6{position:absolute;top:0;right:5px;cursor:pointer;}
  .eject_con h6:hover{color:#24D310;animation:spin 0.7s infinite linear;animation-iteration-count:1;}
  @keyframes spin{
    form{transform:rotate(0deg);}
    to{transform:rotate(360deg);color:silver;}
  }
</style>
<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<div class="alert alert-block mt10">
  <ul class="mt5">
    <li>1、可以对待发货的订单进行发货操作，发货时可以设置收货人和发货人信息，填写一些备忘信息，选择相应的物流服务，打印发货单。</li>
    <li>2、已经设置为发货中的订单，您还可以继续编辑上次的发货信息。</li>
    <li>3、如果因物流等原因造成买家不能及时收货，您可使用点击延迟收货按钮来延迟系统的自动收货时间。</li>
    <li id="alr"></li>
  </ul>
</div>
<form method="get" action="index.php" target="_self">
  <table class="search-form">
    <input type="hidden" name="act" value="store_deliver" />
    <input type="hidden" name="op" value="index" />
    <?php if ($_GET['state'] !='') { ?>
    <input type="hidden" name="state" value="<?php echo $_GET['state']; ?>" />
    <?php } ?>
    <tr>

      <th><?php echo $lang['store_order_add_time'];?></th>
            <td class="w380"><input type="text" class="text w100" name="query_start_date" id="query_start_date"
                                    value="<?php echo $_GET['query_start_date']; ?>"/><label class="add-on"><i
                            class="icon-calendar"></i></label>
        &nbsp;&#8211;&nbsp;
                <input id="query_end_date" class="text w100" type="text" name="query_end_date"
                       value="<?php echo $_GET['query_end_date']; ?>"/><label class="add-on"><i
                            class="icon-calendar"></i></label></td>

      <th><?php echo $lang['store_order_buyer'];?></span></th>
            <td class="w150"><input type="text" class="text w110" name="buyer_name"
                                    value="<?php echo trim($_GET['buyer_name']); ?>"/></td>

            <th>发货人</th>
            <td class="w150"><input type="text" class="text w110" name="senderusername"
                                    value="<?php echo trim($_GET['senderusername']); ?>"/></td>
        </tr>
        <tr>
            <th><?php echo $lang['store_order_order_sn']; ?></th>
            <td class="w150"><input type="text" class="text w130" name="order_sn"
                                    value="<?php echo trim($_GET['order_sn']); ?>"/></td>
            <th>总单号</th>
            <td class="w130"><input type="text" class="text w110" name="logisticsNo"
                                    value="<?php echo trim($_GET['logisticsNo']); ?>"/></td>
            <!-- <th>
        <select name="order_type">
          <option value=0  <?php if($_GET['order_type']==0){?>selected="selected"<?php }?>>订单编号</option>
          <option value=1  <?php if($_GET['order_type']==1){?>selected="selected"<?php }?>>运单号</option>
        </select>
      </th>

      <td class="w130"><input type="text" class="text w130" name="order_num" value="<?php echo trim($_GET['order_num']) ?>"/></td>-->
      <th><?php echo "模式";?></th>
      <td class="w45">
      <select name="is_mode">
        <option value=0 <?php if ($_GET['is_mode'] == 0) {?>selected="selected"<?php }?>>一般</option>
        <option value=1 <?php if ($_GET['is_mode'] == 1) {?>selected="selected"<?php }?>>备货</option>
        <option value=2 <?php if ($_GET['is_mode'] == 2) {?>selected="selected"<?php }?>>集货</option>
        <option value='' <?php if ($_GET['is_mode'] == '') {?>selected="selected"<?php }?>>所有</option>
        </select>
      </td>
      <td class="w45 tc"><label class="submit-border">
          <input type="submit" class="submit"value="<?php echo $lang['store_order_search'];?>" />
        </label></td>
    </tr>
  </table>
</form>
<table class="ncsc-default-table order deliver" id="id_Push">
  <tr>
<!--    <input type = "checkbox" onclick="selectAll()" id="select_all">--><?php //echo "全选/全不选";?>
        <!--   <input type = "checkbox" onclick="selectAll()" name="sel_all" id="sel_all">--><?php //echo "全选/全不选";?>
        <input type="checkbox" name="sel_all" id="sel_all"><?php echo "全选/全不选"; ?><br/>
    共有订单数：<?php echo $output['num'];?>

      <input type = "hidden" name="list_all" id="list_all" value=<?php echo $output['order_all_id'];?> >

  </tr>
  <tr><!--id循环取值-->
    <a class="ncsc-btn-mini fr" id="kuajing_fill" name="kuajing_fill"><i class="icon-edit"></i>跨境信息填写</a>
    <a class="ncsc-btn-mini fr" id="order_Push" name="order_Push"><i class="icon-edit"></i>订单报文生成</a>
    <a class="ncsc-btn-mini fr" id="list_Push" name="list_Push"><i class="icon-edit"></i>清单报文生成</a>
  </tr>
  <?php if (is_array($output['order_list']) and !empty($output['order_list'])) { ?>
  <?php foreach($output['order_list'] as $order_id => $order) {?>
  <tbody>
    <tr>
      <td colspan="21" class="sep-row"></td>
    </tr>
    <tr>
      <th colspan="21"><span class="ml5">
        <!--  <input type = "checkbox" name="check_name" value =<?php //echo $order['order_id'];?> > -->
          <?php echo $lang['store_order_order_sn'].$lang['nc_colon'];?><strong><?php echo $order['order_sn']; ?></strong></span><span><?php echo $lang['store_order_add_time'].$lang['nc_colon'];?><em class="goods-time"><?php echo date("Y-m-d H:i:s",$order['add_time']); ?></em></span>
        <?php if (!empty($order['extend_order_common']['shipping_time'])) {?>
        <span><?php echo '发货时间'.$lang['nc_colon'];?><em class="goods-time"><?php echo date("Y-m-d H:i:s",$order['extend_order_common']['shipping_time']); }?></em></span> <span class="fr mr10">
        <?php if ($order['shipping_code'] != ''){?>
        <a href="index.php?act=store_deliver&op=search_deliver&order_sn=<?php echo $order['order_sn']; ?>" class="ncsc-btn-mini"><i class="icon-compass"></i><?php echo $lang['store_order_show_deliver'];?></a>
        <?php }?>
        <a href="index.php?act=store_order_print&order_id=<?php echo $order['order_id'];?>" target="_blank"  class="ncsc-btn-mini" title="<?php echo $lang['store_show_order_printorder'];?>"/><i class="icon-print"></i><?php echo $lang['store_show_order_printorder'];?></a></span></th>
    </tr>
    <?php $i = 0; ?>
    <?php foreach($order['goods_list'] as $k => $goods) { ?>
    <?php $i++; ?>
    <tr>
      <td class="bdl w10"></td>
      <td class="w50"><div class="pic-thumb"><a href="<?php echo $goods['goods_url'];?>" target="_blank"><img src="<?php echo $goods['image_60_url']; ?>" onMouseOver="toolTip('<img src=<?php echo $goods['image_240_url'];?>>')" onMouseOut="toolTip()" /></a></div></td>
      <td class="tl"><dl class="goods-name">
          <dt><a target="_blank" href="<?php echo $goods['goods_url'];?>">
          <?php
          $arr = explode(' ',$goods['goods_name']);
          if(count($arr)-1>1){
          $arr_sub = explode(' ',$goods['goods_name'],-1);
         foreach($arr_sub as $v){
          echo $v." ";
        }
        ?>
        <font color='orange'><B>
        <?php       
          echo end($arr);
          ?>
          </B></font>
        <?php 
         } else {
          echo $goods['goods_name'];
        }
        ?>
          </a></dt>
          
          <dd><strong>￥<?php echo $goods['goods_price']; ?></strong>&nbsp;x&nbsp;<em><?php echo $goods['goods_num']; ?></em>件</dd>
        </dl></td>

      <!-- S 合并TD -->
      <?php if (($order['goods_count'] > 1 && $k == 0) || ($order['goods_count'] == 1)){?>
      <td class="bdl bdr order-info w500" rowspan="<?php echo $order['goods_count'];?>"><dl>
          <dt><?php echo $lang['store_deliver_buyer_name'].$lang['nc_colon'];?></dt>
          <dd><?php echo $order['buyer_name']; ?> <span member_id="<?php echo $order['buyer_id'];?>"></span>
            <?php if(!empty($order['extend_member']['member_qq'])){?>
            <a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $order['extend_member']['member_qq'];?>&site=qq&menu=yes" title="QQ: <?php echo $order['extend_member']['member_qq'];?>"><img border="0" src="http://wpa.qq.com/pa?p=2:<?php echo $order['extend_member']['member_qq'];?>:52" style=" vertical-align: middle;"/></a>
            <?php }?>
            <?php if(!empty($order['extend_member']['member_ww'])){?>
            <a target="_blank" href="http://amos.im.alisoft.com/msg.aw?v=2&uid=<?php echo $order['extend_member']['member_ww'];?>&site=cntaobao&s=2&charset=<?php echo CHARSET;?>" class="vm" ><img border="0" src="http://amos.im.alisoft.com/online.aw?v=2&uid=<?php echo $order['extend_member']['member_ww'];?>&site=cntaobao&s=2&charset=<?php echo CHARSET;?>" alt="Wang Wang" style=" vertical-align: middle;"/></a>
            <?php }?>
          </dd>
        </dl>
        <dl>
          <dt><?php echo '收货人'.$lang['nc_colon'];?></dt>
          <dd>
            <div class="alert alert-info m0">
              <p><i class="icon-user"></i><?php echo $order['extend_order_common']['reciver_name']?><span class="ml30" title="<?php echo '电话';?>"><i class="icon-phone"></i><?php echo $order['extend_order_common']['reciver_info']['phone'];?></span></p>
              <p class="mt5" title="<?php echo $lang['store_deliver_buyer_address'];?>"><i class="icon-map-marker"></i><?php echo $order['extend_order_common']['reciver_info']['address'];?></p>
              <?php if ($order['extend_order_common']['order_message'] != '') {?>
              <p class="mt5" title="<?php echo $lang['store_deliver_buyer_address'];?>"><i class="icon-map-marker"></i><?php echo $order['extend_order_common']['order_message'];?></p>
              <?php } ?>
            </div>
          </dd>
        </dl>
        <dl>
          <dt><?php echo $lang['store_deliver_shipping_amount'].$lang['nc_colon'];?> </dt>
          <dd>
            <?php if (!empty($order['shipping_fee']) && $order['shipping_fee'] != '0.00'){?>
            ￥<?php echo $order['shipping_fee'];?>
            <?php }else{?>
            <?php echo $lang['nc_common_shipping_free'];?>
            <?php }?>
            <?php if (empty($order['lock_state'])) {?>
            <?php if ($order['order_state'] == ORDER_STATE_PAY) {?>
            <span><a href="index.php?act=store_deliver&op=send&order_id=<?php echo $order['order_id'];?>" class="ncsc-btn-mini ncsc-btn-green fr"><i class="icon-truck"></i><?php echo $lang['store_order_send'];?></a></span>
            <?php } elseif ($order['order_state'] == ORDER_STATE_SEND){?>
            <span>
            <a href="javascript:void(0)" class="ncsc-btn-mini ncsc-btn-orange ml5 fr" uri="index.php?act=store_deliver&op=delay_receive&order_id=<?php echo $order['order_id']; ?>" dialog_width="480" dialog_title="延迟收货" nc_type="dialog" dialog_id="seller_order_delay_receive" id="order<?php echo $order['order_id']; ?>_action_delay_receive" /><i class="icon-time"></i></i>延迟收货</a>
            <a href="index.php?act=store_deliver&op=send&order_id=<?php echo $order['order_id'];?>" class="ncsc-btn-mini ncsc-btn-acidblue fr"><i class="icon-edit"></i><?php echo $lang['store_deliver_modify_info'];?></a>
            </span>
            <?php }?>
            <?php }?>
          </dd>
        </dl></td>
      <?php } ?>
      <!-- E 合并TD -->
    </tr>

    <!-- S 赠品列表 -->
    <?php if (!empty($order['zengpin_list']) && $i == count($order['goods_list'])) { ?>
    <tr>
    <td class="bdl w10"></td>
    <td colspan="2" class="tl">
    <div class="ncsc-goods-gift">赠品：
    <ul>
    <?php foreach ($order['zengpin_list'] as $k => $zengpin_info) { ?>
    <li><a title="赠品：<?php echo $zengpin_info['goods_name'];?> * <?php echo $zengpin_info['goods_num'];?>" href="<?php echo $zengpin_info['goods_url'];?>" target="_blank"><img src="<?php echo $zengpin_info['image_60_url'];?>" onMouseOver="toolTip('<img src=<?php echo $zengpin_info['image_240_url'];?>>')" onMouseOut="toolTip()"/></a></li>
    <?php } ?>
    </ul>
    </div>
    </td>
    </tr>
    <?php } ?>
    <!-- E 赠品列表 -->

    <?php } ?>
    <?php } } else { ?>
    <tr>
      <td colspan="21" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
    </tr>
    <?php } ?>
  </tbody>
  <tfoot>
    <?php if (!empty($output['order_list'])) { ?>
    <tr>
      <td colspan="21"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
    </tr>
    <?php } ?>
  </tfoot>
</table>
<!--跨境信息填写-->
<div class="eject_con">
  <div class="adds">
    <div id="warning"></div>
    <form method="post" action="index.php?act=store_deliver&op=buyer_address&order_id=<?php echo $_GET['order_id'];?>" id="sto_form" target="_parent">
      <input type="hidden" name="form_submit" value="ok" />
      <h5>跨境信息填写</h5>
      <h6 id="close">✖</h6>
      <dl>
        <dt class="required">总单号</dt>
        <dd>
          <input type="text" class="text" name="totalLogisticsNo" id="totalLogisticsNo" value=""/>
        </dd>
      </dl>
      <dl>
        <dt class="required">航班航次号</dt>
        <dd>
          <input type="text" class="text" name="voyageNo" id="voyageNo" value=""/>
        </dd>
      </dl>
      <div class="bottom"><label class="submit-border"><a href="javascript:void(0);" id="submit" class="submit">保存</a></label></div>
    </form>
  </div>
</div>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" ></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script type="text/javascript">
$(function(){
    $('#query_start_date').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date').datepicker({dateFormat: 'yy-mm-dd'});
    $('#order_Push').click(function(){
      var str = '';
      str = getId();
      if (str) {
        order_Push(str);
      }
});
    $('#list_Push').click(function(){
      var str = '';
      str = getId();
      if (str) {
        list_Push(str);
      }
    });
    $('#kuajing_fill').on('click',function(){
      $('.eject_con').slideDown('');
    });
});

function getId() {
  var str = '';
        if ($("#sel_all").attr('checked')) {
            str = $('#list_all').val();
    }
  return str;
}

function order_Push(ids) {
        if(ids) {
  var id_array = ids.split(",");
        }
    window.open("index.php?act=store_deliver&op=DorderPush1&order_id=" + id_array + "&op_type=1");
}

function list_Push(ids) {
        if(ids) {
  var id_array = ids.split(",");
        }
    window.open("index.php?act=store_deliver&op=DlistPush1&order_id=" + id_array + "&op_type=1");
}

$(document).ready(function(){
  $('#sto_form').validate({
    rules : {
      totalLogisticsNo : { required : true },
      voyageNo : { required : true }
    },
    messages : {
      totalLogisticsNo: { required : '<i class="icon-exclamation-sign"></i>总单号不能为空' },
      voyageNo: { required : '<i class="icon-exclamation-sign"></i>航班航次号不能为空' }
    }
  });
  $('#submit').on('click',function(){
            var str = '';
    str = getId();
            if(str) {
    if ($('#sto_form').valid()) {
      var reciver_voyageNo = $('#voyageNo').val();
      var reciver_totalLogisticsNo = $('#totalLogisticsNo').val();

      $.post(
        "<?php echo urlShop('store_deliver', 'kuajing_info_save');?>",
        {
          reciver_voyageNo: reciver_voyageNo,
          reciver_totalLogisticsNo: reciver_totalLogisticsNo,
          str_order_id: str
      }).done(function(data) {
          if(data=='flase'){
            showError('保存失败');
          }
          else{
            alert('保存成功');
            $('.eject_con').fadeOut('slow');
          }
        });
    }
            }
  });
  $('#close').on('click',function(){
      $('.eject_con').slideUp();
  });
});
</script>
