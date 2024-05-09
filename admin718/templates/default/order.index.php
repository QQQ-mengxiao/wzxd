<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['order_manage'];?></h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['manage'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" action="index.php" name="formSearch" id="formSearch">
    <input type="hidden" name="act" value="order" />
    <input type="hidden" name="op" value="index" />
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
         <th><label><?php echo $lang['order_number'];?></label></th>
         <td><input class="txt2" type="text" name="order_sn" value="<?php echo $_GET['order_sn'];?>" /></td>
         <th><?php echo $lang['store_name'];?></th>
         <td><input class="txt-short" type="text" name="store_name" value="<?php echo $_GET['store_name'];?>" /></td>
          <th>收货人姓名</th>
         <td><input class="txt-short" type="text" name="reciver_name" value="<?php echo $_GET['reciver_name'];?>" /></td>
         <th><label><?php echo $lang['order_state'];?></label></th>
          <td colspan="4">
          <select name="order_state" class="querySelect">
              <option value=""><?php echo $lang['nc_please_choose'];?></option>
              <option value="10" <?php if($_GET['order_state'] == '10'){?>selected<?php }?>><?php echo $lang['order_state_new'];?></option>
              <option value="20" <?php if($_GET['order_state'] == '20'){?>selected<?php }?>><?php echo $lang['order_state_pay'];?></option>
              <option value="30" <?php if($_GET['order_state'] == '30'){?>selected<?php }?>><?php echo $lang['order_state_send'];?></option>
              <option value="40" <?php if($_GET['order_state'] == '40'){?>selected<?php }?>><?php echo $lang['order_state_success'];?></option>
              <option value="0" <?php if($_GET['order_state'] == '0'){?>selected<?php }?>><?php echo $lang['order_state_cancel'];?></option>
              <option value="1" <?php if($_GET['refund_state'] == '1'){?>selected<?php }?>>部分退款</option>
            </select></td>


            <!--jinp06281646代码不起作用-->


          <th><label><?php echo 推单状态;?></label></th>
          <td colspan="2">
           <select name="crossborder_pay_state" class="querySelect">
              <option value=""><?php echo $lang['nc_please_choose'];?></option>
              <option value="0" <?php if($_GET['order_state'] == '0'){?>selected<?php }?>><?php echo 失败;?></option>
              <option value="1" <?php if($_GET['order_state'] == '1'){?>selected<?php }?>><?php echo 成功;?></option>
              
           </select>
          </td>
        
        
        </tr>
        <tr>
          <th><label for="query_start_time"><?php echo $lang['order_time_from'];?></label></th>
          <td><input class="txt date ui_timepicker" type="text" value="<?php echo $_GET['query_start_time'];?>" id="query_start_time" name="query_start_time">
            <label for="query_start_time">~</label>
            <input class="txt date ui_timepicker" type="text" value="<?php echo $_GET['query_end_time'];?>" id="query_end_time" name="query_end_time"/></td>
        <!--xinzeng支付时间11.2-->
            <th><label for="query_start_time_pay">支付时间</label></th>
            <td><input class="txt date" type="text" value="<?php echo $_GET['query_start_time_pay'];?>" id="query_start_time_pay" name="query_start_time_pay">
                <label for="query_start_time_pay">~</label>
                <input class="txt date" type="text" value="<?php echo $_GET['query_end_time_pay'];?>" id="query_end_time_pay" name="query_end_time_pay"/></td>


         <th><?php echo $lang['buyer_name'];?></th>
         <td><input class="txt-short" type="text" name="buyer_name" value="<?php echo $_GET['buyer_name'];?>" />
         </td> 
         <th>付款方式</th>
         <td>
            <select name="payment_code" class="w100">
            <option value=""><?php echo $lang['nc_please_choose'];?></option>
            <?php foreach($output['payment_list'] as $val) { ?>
            <option <?php if($_GET['payment_code'] == $val['payment_code']){?>selected<?php }?> value="<?php echo $val['payment_code']; ?>"><?php echo $val['payment_name']; ?></option>
            <?php } ?>
             <option <?php if($_GET['payment_code'] == 'online'){?>selected<?php }?> value="online"><?php echo '微信支付';?></option>
			<option <?php if($_GET['payment_code'] == 'zihpay'){?>selected<?php }?> value="zihpay"><?php echo 'zihpay';?></option>
       <option <?php if($_GET['payment_code'] == 'predeposit'){?>selected<?php }?> value="predeposit"><?php echo '站内余额支付';?></option>
                     </select>
         </td>
        </tr>
        </tr>
        <tr>
            <th><label for="query_start_time_finish">订单完成时间</label></th>
            <td><input class="txt date" type="text" value="<?php echo $_GET['query_start_time_finish'];?>" id="query_start_time_finish" name="query_start_time_finish">
                <label for="query_start_time_finish">~</label>
                <input class="txt date" type="text" value="<?php echo $_GET['query_end_time_finish'];?>" id="query_end_time_finish" name="query_end_time_finish"/></td>
             <th>促销活动类型</th>
          <td >
          <select name="order_type" class="w100">
              <option value=""><?php echo $lang['nc_please_choose'];?></option>
              <option value="1" <?php if($_GET['order_type'] == '1'){?>selected<?php }?>>阶梯价</option>
              <option value="2" <?php if($_GET['order_type'] == '2'){?>selected<?php }?>>团购</option>
              <option value="3" <?php if($_GET['order_type'] == '3'){?>selected<?php }?>>新人专享</option>
              <option value="4" <?php if($_GET['order_type'] == '4'){?>selected<?php }?>>限时秒杀</option>
              <option value="5" <?php if($_GET['order_type'] == '5'){?>selected<?php }?>>即买即送</option>
            </select></td>      
            <th>自提点</th>
            <td >
            <select name="address_id" class="w100">
            <option value=""><?php echo $lang['nc_please_choose'];?></option>
            <?php foreach($output['address_list'] as $val) { ?>
            <option <?php if($_GET['address_id'] == $val['address_id']){?>selected<?php }?> value="<?php echo $val['address_id']; ?>"><?php echo $val['seller_name']; ?></option>
            <?php } ?>
                     </select>
         </td>    
          <td><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a>
            
            </td>
        </tr>
      </tbody>
    </table>
  </form>
  <table class="table tb-type2" id="prompt">
    <tbody>
      <tr class="space odd">
        <th colspan="12"><div class="title"><h5><?php echo $lang['nc_prompts'];?></h5><span class="arrow"></span></div></th>
      </tr>
      <tr>
        <td>
        <ul>
            <li style="color:#7171b1"><?php echo $lang['order_help1'];?>jp</li>
            <li><?php echo $lang['order_help2'];?></li>
            <li><?php echo $lang['order_help3'];?></li>
          </ul></td>
      </tr>
    </tbody>
  </table>
  <div style="text-align:right;"><a class="btns" target="_blank" href="index.php?<?php echo $_SERVER['QUERY_STRING'];?>&op=export_step1"><span><?php echo $lang['nc_export'];?>Excel</span></a></div>
  <table class="table tb-type2 nobdb">
    <thead>
      <tr class="thead" >
        <th style="color:#7171b1"><?php echo $lang['order_number'];?></th>
        <th><?php echo $lang['store_name'];?></th>
        <th><?php echo $lang['buyer_name'];?></th>
        <!-- <th>订单来源</th> -->
        <th class="align-center"><?php echo $lang['order_time'];?></th>
        <th class="align-center">实际支付金额</th>
        <th class="align-center">商品总额(不含税,不含运费)</th>
        <th class="align-center"><?php echo $lang['payment'];?></th>
		<!-- <th class="align-center">税金</th> -->
        <!-- <th class="align-center">推单状态</th> -->
                <th class="align-center">自提点</th>
        <th class="align-center">活动类型</th>

        <th class="align-center"><?php echo $lang['order_state'];?></th>
        <th class="align-center"><?php echo $lang['nc_handle'];?></th>
      </tr>
    </thead>
    <tbody>
      <?php if(count($output['order_list'])>0){?>
      <?php foreach($output['order_list'] as $order){?>
      <tr class="hover">
        <td><?php echo $order['order_sn'];?></td>
        <td><?php echo $order['store_name'];?></td>
        <td style="color:#7171b1"><?php echo $order['buyer_name'];?></td>
       <!--  <td class="align-center"><?php if($order['order_from']=='1') {echo 'PC端';}else if($order['order_from']=='2'){echo 'wap端';}else if($order['order_from']=='3'){echo 'android端';}else if($order['order_from']=='4'){echo 'IOS端';}else{echo '';}?></td> -->
        <td class="nowrap align-center"><?php echo date('Y-m-d H:i:s',$order['add_time']);?></td>
        <td class="align-center" style="color:#7171b1"><?php echo $order['order_amount'];?></td>
        <td class="align-center" style="color:#7171b1"><?php echo $order['goods_amount'];?></td>
        <td class="align-center"><?php echo orderPaymentName($order['payment_code']);?></td>
                  <td class="align-center" style="color:#7171b1"><?php echo $order['ziti_name'];?></td>
        <td class="align-center" style="color:#7171b1"><?php echo orderTypeName($order['order_type']);?></td>
        <?php if($order['refund_state']=='1') {?> 
               <td class="align-center">部分退款</td>
          <?php }else{?>
        <td class="align-center"><?php echo orderState($order);?></td>
          <?php }?>

        <!-- <td class="align-center"><?php echo orderState($order);?></td> -->
        <td class="w144 align-center"><a href="index.php?act=order&op=show_order&order_id=<?php echo $order['order_id'];?>"><?php echo $lang['nc_view'];?></a>

        <!-- 取消订单 -->
    		<?php if($order['if_cancel']) {?>
        	| <a href="javascript:void(0)" onclick="if(confirm('<?php echo $lang['order_confirm_cancel'];?>')){location.href='index.php?act=order&op=change_state&state_type=cancel&order_id=<?php echo $order['order_id']; ?>'}">
        	<?php echo $lang['order_change_cancel'];?></a>
        	<?php }?>

        	<!-- 收款 
    		<?php if($order['if_system_receive_pay']) {?>
	        	| <a href="index.php?act=order&op=change_state&state_type=receive_pay&order_id=<?php echo $order['order_id']; ?>">
	        	<?php echo $lang['order_change_received'];?></a>
    		<?php }?>-->
        	</td>
      </tr>
      <?php }?>
      <?php }else{?>
      <tr class="no_data">
        <td colspan="15"><?php echo $lang['nc_no_record'];?></td>
      </tr>
      <?php }?>
    </tbody>
    <tfoot>
      <tr class="tfoot">
        <td colspan="15" id="dataFuncs"><div class="pagination"> <?php echo $output['show_page'];?> </div></td>
      </tr>
    </tfoot>
  </table>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script> 
 <script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script> 
 <link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  /> 
<link  type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-1.8.17.custom.css" rel="stylesheet" />
<link  type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-timepicker-addon.css" rel="stylesheet" />
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-1.7.1.min.js"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-1.8.17.custom.min.js"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-timepicker-addon.js"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-timepicker-zh-CN.js"></script>
<script type="text/javascript">
$(function(){
    $("#query_start_time").datetimepicker({  
                    //defaultDate: $('#query_start_time').val(),  
                    dateFormat: "yy-mm-dd",  
                    showSecond: true,  
                    timeFormat: 'hh:mm:ss',  
                    stepHour: 1,  
                    stepMinute: 1,  
                    stepSecond: 1  
                });  
      $("#query_end_time").datetimepicker({  
                    //defaultDate: $('#query_end_time').val(),  
                    dateFormat: "yy-mm-dd",  
                    showSecond: true,  
                    timeFormat: 'hh:mm:ss',  
                    stepHour: 1,  
                    stepMinute: 1,  
                    stepSecond: 1  
                });
    $('#query_start_time_pay').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_time_pay').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_start_time_finish').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_time_finish').datepicker({dateFormat: 'yy-mm-dd'});
    $('#ncsubmit').click(function(){
    	$('input[name="op"]').val('index');$('#formSearch').submit();
    });
});
</script> 
