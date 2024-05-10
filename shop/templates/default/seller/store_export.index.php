<?php defined('In718Shop') or exit('Access Invalid!');?>
<!--
<div class="tabmenu">
  <?php //include template('layout/submenu');?>
</div>
-->
<form method="get" action="index.php" target="_blank">
  <table class="search-form">
    <input type="hidden" name="act" value="store_export" />
    <input type="hidden" name="op" value="export_order" />
    <?php if ($_GET['state_type']) { ?>
    <input type="hidden" name="state_type" value="<?php echo $_GET['state_type']; ?>" />
    <?php } ?>
    <tr>
    <th>商品模式</th>
      <td>      
      <select name="is_mode">
        <option value=0 <?php if ($_GET['is_mode'] == 0) {?>selected="selected"<?php }?>>一般</option>
        <option value=1 <?php if ($_GET['is_mode'] == 1) {?>selected="selected"<?php }?>>备货</option>
        <option value=2 <?php if ($_GET['is_mode'] == 2) {?>selected="selected"<?php }?>>集货</option>
        <option value='' <?php if ($_GET['is_mode'] == '') {?>selected="selected"<?php }?>>所有</option>
        </select>
        &nbsp;&nbsp;支付方式&nbsp;
      <select name="pay_code">
        <option value='online' <?php if ($_GET['pay_code'] == 'online') {?>selected="selected"<?php }?>>微信支付</option>
          <option value='zihpay' <?php if ($_GET['pay_code'] == 'zihpay') {?>selected="selected"<?php }?>>豫卡通支付</option>
        <option value='offpay' <?php if ($_GET['pay_code'] == 'offpay') {?>selected="selected"<?php }?>>站内余额</option>
          <option value='jicardpay' <?php if ($_GET['pay_code'] == 'jicardpay') {?>selected="selected"<?php }?>>集团餐卡</option>
            <option value='newpay' <?php if ($_GET['pay_code'] == 'newpay') {?>selected="selected"<?php }?>>新零售余额</option>
        <option value='' <?php if ($_GET['pay_code'] == '') {?>selected="selected"<?php }?>>所有</option>
        </select>
        <!--
      &nbsp;&nbsp;退款状态&nbsp;
      <select name="refund_state">
        <option value=0 <?php if ($_GET['refund_state'] == 0) {?>selected="selected"<?php }?>>无退款</option>
        <option value=1 <?php if ($_GET['refund_state'] == 1) {?>selected="selected"<?php }?>>部分退款</option>
        <option value=2 <?php if ($_GET['refund_state'] == 2) {?>selected="selected"<?php }?>>全部退款</option>
        <option value='' <?php if ($_GET['refund_state'] == '') {?>selected="selected"<?php }?>>所有</option>
        </select>
        -->
      &nbsp;&nbsp;订单状态&nbsp;
      <select name="order_state">
        <option value=20 <?php if ($_GET['order_state'] == 20) {?>selected="selected"<?php }?>>待发货</option>
        <option value=30 <?php if ($_GET['order_state'] == 30) {?>selected="selected"<?php }?>>待收货</option>
        <option value=40 <?php if ($_GET['order_state'] == 40) {?>selected="selected"<?php }?>>交易完成</option>
        <option value=10 <?php if ($_GET['order_state'] == 10) {?>selected="selected"<?php }?>>待付款</option>
        <option value=0  <?php if ($_GET['order_state'] == 0) {?>selected="selected"<?php }?>>已取消</option>
        <option value='' <?php if ($_GET['order_state'] == '') {?>selected="selected"<?php }?>>所有</option>
        </select>

      </td>
      <?php if ($_GET['state_type'] == 'store_order') { ?>
      <th><input type="checkbox" id="skip_off" value="1" <?php echo $_GET['skip_off'] == 1 ? 'checked="checked"' : null;?>  name="skip_off"> </th>
      <td><label for="skip_off">不显示已关闭的订单</label></td>
      <?php } ?>
      <th class=""><input  type="reset"  name="reset"  value="重置"/></th>
    </tr>
    <tr>  
      <th><?php echo $lang['store_order_add_time'];?></th>
      <td class="w380"><input type="text" class="text w70" name="query_start_date" id="query_start_date" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date" class="text w70" type="text" name="query_end_date" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
      <th>发货时间</th>
      <td class="w240"><input type="text" class="text w70" name="query_start_date_fahuo" id="query_start_date_fahuo" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_fahuo" class="text w70" type="text" name="query_end_date_fahuo" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>

    </tr>
    <tr>
    <th>支付时间</th><!--xinzeng-->
      <td class="w380"><input type="text" class="text w70" name="query_start_date_pay" id="query_start_date_pay" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_pay" class="text w70" type="text" name="query_end_date_pay" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
    <th>订单完成 时间</th>
      <td class="w240"><input type="text" class="text w70" name="query_start_date_finish" id="query_start_date_finish" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_finish" class="text w70" type="text" name="query_end_date_finish" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
    </tr>
    <tr>  
      <th>买家帐号</th>  
      <td class="w100"><input type="text" class="text w150" name="buyer_name" value="<?php echo $_GET['buyer_name']; ?>" /></td>
      <th>收货人</th>  
      <td class="w100"><input type="text" class="text w150" name="consignee_name" value="<?php echo $_GET['consignee_name']; ?>" /></td>

    </tr>
    <tr>
    <th><?php echo $lang['store_order_order_sn'];?></th>
      <td class="w160"><input type="text" class="text w150" name="order_sn" value="<?php echo $_GET['order_sn']; ?>" /></td>
      <!--xinzeng 11.1-->
      <th>发货人</th>
      <td class="w100"><input type="text" class="text w150" name="senderusername" value="<?php echo $_GET['senderusername']; ?>" /></td>
      </td>
    </tr>
      <tr>
          <th>活动类型</th>
          <td>
              <select name="order_type">
                  <option value=0 <?php if ($_GET['order_type'] == 0) {?>selected="selected"<?php }?>>无活动</option>
                  <option value=1 <?php if ($_GET['order_type'] == 1) {?>selected="selected"<?php }?>>阶梯价</option>
                  <option value=2 <?php if ($_GET['order_type'] == 2) {?>selected="selected"<?php }?>>团购</option>
                  <option value=3 <?php if ($_GET['order_type'] == 3) {?>selected="selected"<?php }?>>新人专享</option>
                  <option value=4 <?php if ($_GET['order_type'] == 4) {?>selected="selected"<?php }?>>限时秒杀</option>
                  <option value=5 <?php if ($_GET['order_type'] == 5) {?>selected="selected"<?php }?>>即买即送</option>
                  <option value='' <?php if ($_GET['order_type'] == '') {?>selected="selected"<?php }?>>所有</option>
              </select>
      </tr>


      <tr>
      <td>&nbsp;</td>
      </tr>
      <tr>


      <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>

      <td class="w70 tc"><label class="submit-border">
          <input type="submit" class="submit" value="导出订单" />
        </label></td>
    </tr>
  </table>
</form>

<form method="get" action="index.php" target="_blank">
  <table class="search-form">
    <input type="hidden" name="act" value="store_export" />
    <input type="hidden" name="op" value="export_order_sub" id="op"/>
    <?php if ($_GET['state_type']) { ?>
    <input type="hidden" name="state_type" value="<?php echo $_GET['state_type']; ?>" />
    <?php } ?>

      <tr>
      <td>&nbsp;</td>
      </tr>
    <tr>
      
      <th>商品模式</th>
      <td>      
      <select name="is_mode">
        <option value=0 <?php if ($_GET['is_mode'] == 0) {?>selected="selected"<?php }?>>一般</option>
        <option value=1 <?php if ($_GET['is_mode'] == 1) {?>selected="selected"<?php }?>>备货</option>
        <option value=2 <?php if ($_GET['is_mode'] == 2) {?>selected="selected"<?php }?>>集货</option>
        <option value='' <?php if ($_GET['is_mode'] == '') {?>selected="selected"<?php }?>>所有</option>
        </select>
        &nbsp;&nbsp;支付方式&nbsp;
      <select name="payment_code">
          <option value='online' <?php if ($_GET['payment_code'] == 'online') {?>selected="selected"<?php }?>>微信支付</option>
          <option value='zihpay' <?php if ($_GET['payment_code'] == 'zihpay') {?>selected="selected"<?php }?>>豫卡通支付</option>
          <option value='offpay' <?php if ($_GET['payment_code'] == 'offpay') {?>selected="selected"<?php }?>>站内余额</option>
          <option value='jicardpay' <?php if ($_GET['payment_code'] == 'jicardpay') {?>selected="selected"<?php }?>>集团餐卡</option>
            <option value='newpay' <?php if ($_GET['pay_code'] == 'newpay') {?>selected="selected"<?php }?>>兑换餐补</option>
          <option value='' <?php if ($_GET['payment_code'] == '') {?>selected="selected"<?php }?>>所有</option>
        </select>
      <!--  
      &nbsp;&nbsp;退款状态&nbsp;
      <select name="refund_state">
        <option value=0 <?php if ($_GET['refund_state'] == 0) {?>selected="selected"<?php }?>>无退款</option>
        <option value=1 <?php if ($_GET['refund_state'] == 1) {?>selected="selected"<?php }?>>部分退款</option>
        <option value=2 <?php if ($_GET['refund_state'] == 2) {?>selected="selected"<?php }?>>全部退款</option>
        <option value='' <?php if ($_GET['refund_state'] == '') {?>selected="selected"<?php }?>>所有</option>
        </select>
      -->
      &nbsp;&nbsp;订单状态&nbsp;
      <select name="order_state">
        <option value=20 <?php if ($_GET['order_state'] == 20) {?>selected="selected"<?php }?>>待发货</option>
        <option value=30 <?php if ($_GET['order_state'] == 30) {?>selected="selected"<?php }?>>待收货</option>
        <option value=40 <?php if ($_GET['order_state'] == 40) {?>selected="selected"<?php }?>>交易完成</option>
        <option value=10 <?php if ($_GET['order_state'] == 10) {?>selected="selected"<?php }?>>待付款</option>
        <option value=0  <?php if ($_GET['order_state'] == 0) {?>selected="selected"<?php }?>>已取消</option>
        <option value='' <?php if ($_GET['order_state'] == '') {?>selected="selected"<?php }?>>所有</option>
        </select>  
      </td>
      <?php if ($_GET['state_type'] == 'store_order') { ?>
      <th><input type="checkbox" id="skipoff2" value="1" <?php echo $_GET['skipoff2'] == 1 ? 'checked="checked"' : null;?>  name="skipoff2"> </th>
      <td align="left"><label for="skipoff2">不显示已关闭的订单</label></td>
      <?php } ?>
      <th><input  type="reset"  name="reset"  value="重置"/></th>      
          </tr>
    <tr> 
      <th><?php echo $lang['store_order_add_time'];?></th>
      <td class="w380"><input type="text" class="text w70" name="query_start_date2" id="query_start_date2" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date2" class="text w70" type="text" name="query_end_date2" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
      <th>发货时间</th>
      <td class="w240"><input type="text" class="text w70" name="query_start_date2_fahuo" id="query_start_date2_fahuo" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date2_fahuo" class="text w70" type="text" name="query_end_date2_fahuo" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>

    </tr>
    <tr>
    <th>支付时间</th><!--xinzeng-->
      <td class="w380"><input type="text" class="text w70" name="query_start_date_pay2" id="query_start_date_pay2" value="<?php echo $_GET['query_start_date']; ?>" style="width: 100px !important;"/><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_pay2" class="text w70" type="text" name="query_end_date_pay2" value="<?php echo $_GET['query_end_date']; ?>" style="width: 100px !important;"/><label class="add-on"><i class="icon-calendar"></i></label></td>
        <th>订单完成时间</th>
        <td class="w240"><input type="text" class="text w70" name="query_start_date_finish2" id="query_start_date_finish2" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_finish2" class="text w70" type="text" name="query_end_date_finish2" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
    </tr>
    <tr>    
      <th>买家帐号</th>  
      <td class="w100"><input type="text" class="text w150" name="buyer_name" value="<?php echo $_GET['buyer_name']; ?>" /></td>
      <th>收货人</th>  
      <td class="w100"><input type="text" class="text w150" name="consignee_name" value="<?php echo $_GET['consignee_name']; ?>" /></td>
    </tr>
    <tr>  
      <th>商品名称</th>
      <td class="w160"><input type="text" class="text w150" name="goods_name" value="<?php echo $_GET['goods_name']; ?>" /></td>
      <th>商品货号</th>
      <td class="w160"><input type="text" class="text w150" name="goods_serial" value="<?php echo $_GET['goods_serial']; ?>" /></td>
    </tr>
    <tr>  

          </tr>
    <tr> 

    </tr>
    <tr> 
      
          </tr>
    <tr> 
    <th><?php echo $lang['store_order_order_sn'];?></th>
      <td class="w160"><input type="text" class="text w150" name="order_sn" value="<?php echo $_GET['order_sn']; ?>" /></td>
      <th>发货人</th>
      <td class="w100">
      <select name="senderusername" id="menu1" class="select1">
          <option  value=0  <?php if ($_GET['senderusername'] == 0) { ?>selected="selected" <?php } ?>>所有</option>
          <?php foreach ($output['daddress_list'] as $val) { ?>
              <option value="<?php echo $val['address_id']; ?>" <?php if ($_GET['senderusername'] == 1) { ?>selected="selected" <?php } ?>><?php echo $val['seller_name'];?></option>
          <?php } ?>
      </select>
    </td>
      <tr>
          <th>活动类型</th>
          <td>
              <select name="order_type">
                  <option value=0 <?php if ($_GET['order_type'] == 0) {?>selected="selected"<?php }?>>无活动</option>
                  <option value=1 <?php if ($_GET['order_type'] == 1) {?>selected="selected"<?php }?>>阶梯价</option>
                  <option value=2 <?php if ($_GET['order_type'] == 2) {?>selected="selected"<?php }?>>团购</option>
                  <option value=3 <?php if ($_GET['order_type'] == 3) {?>selected="selected"<?php }?>>新人专享</option>
                  <option value=4 <?php if ($_GET['order_type'] == 4) {?>selected="selected"<?php }?>>限时秒杀</option>
                  <option value=5 <?php if ($_GET['order_type'] == 5) {?>selected="selected"<?php }?>>即买即送</option>
                  <option value='' <?php if ($_GET['order_type'] == '') {?>selected="selected"<?php }?>>所有</option>
              </select>
            </td>
                <th>自提点</th>
            <td >
            <select name="address_id" class="w100">
            <option value=""><?php echo $lang['nc_please_choose'];?></option>
            <?php foreach($output['address_list'] as $val) { ?>
            <option <?php if($_GET['address_id'] == $val['address_id']){?>selected<?php }?> value="<?php echo $val['address_id']; ?>"><?php echo $val['seller_name']; ?></option>
            <?php } ?>
                     </select>
         </td>
      </tr>
    </tr>
    <tr>
    <th>发货人类别</th>
          <td>
          <select name="peisong">
          <option value=0 <?php if ($_GET['peisong'] == 0) { ?>selected="selected" <?php } ?>>所有</option>
          <?php if (is_array($output['pei_list'])) { ?>
            <?php foreach ($output['pei_list'] as $key => $value) { ?>
              <option value=<?php echo $value['id'];?> <?php if ($_GET['peisong'] == 1) { ?>selected="selected" <?php } ?>><?php echo $value['p_name'];?></option>
            <?php } ?>
          <?php } ?>
        </select>
            </td>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
      <td class="w70 tc"><label class="submit-border">
          <input type="submit" class="submit" value="导出子订单" id="export"/>
        </label></td>
      <td class="w80 tc">
          <input type="submit" class="green w80" value="小店专用导出" id="export_wzxd"/>
<!--          <a type="submit" class="ncsc-btn ncsc-btn-green">小店专用导出</a>-->
      </td>
    </tr>
  </table>
</form>
<form method="get" action="index.php" target="_blank">
    <input type="hidden" name="act" value="store_export"/>
    <input type="hidden" name="op" value="memberInfo_export" id="op"/>
    <input type="submit" class="submit w120" value="导出会员信息" id="memberInfo_export"/>
</form>
<!--
<div style="text-align:right;"><a class="btns" target="_blank" href="index.php?<?php echo $_SERVER['QUERY_STRING'];?>&op=export_step1"><span><?php echo $lang['nc_export'];?>Excel</span></a></div>
-->
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/select2/select2.min.css"/>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/select2/select2.full.min.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/lib/jquery/1.9.1/jquery.min.js"></script>
<style>
    .select1{
        margin: 60px auto 0;
        width: 250px;
        display:inline;
    }
</style>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" ></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css"  />
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js"></script>
<script type="text/javascript">
//页面加载完成后初始化select2控件
$(function () {
    $("#menu1").select2();
});
$(function(){
    $('#query_start_date').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_start_date_fahuo').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date_fahuo').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_start_date2').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date2').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_start_date2_fahuo').datetimepicker({controlType: 'select'});
    $('#query_end_date2_fahuo').datetimepicker({controlType: 'select'});

    $('#query_start_date_pay').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date_pay').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_start_date_pay2').datetimepicker({controlType: 'select'});
    $('#query_end_date_pay2').datetimepicker({controlType: 'select'});   
    $('#query_start_date_finish').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date_finish').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_start_date_finish2').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date_finish2').datepicker({dateFormat: 'yy-mm-dd'});

    $('#export').click(function(){
        $('#op').val('export_order_sub');
        $('#formmx').submit();
    });
    $('#export_wzxd').click(function(){
        $('#op').val('export_order_sub_wzxd');
        $('#formmx').submit();
    });

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
    $('#skipoff2').click(function(){
        url = location.href.replace(/&skipoff2=\d*/g,'');
        window.location.href = url + '&skipoff2=' + ($('#skipoff2').attr('checked') ? '1' : '0');
    });
});
</script> 
