<!--商家管理中心-订单物流-实物交易订单-->
<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div><!--所有订单/待付款/待发货/已发货/已完成/已取消导航栏-->
<form method="get" action="index.php" target="_self">
  <table class="search-form">
    <input type="hidden" name="act" value="store_order" />
    <input type="hidden" name="op" value="index" />
    <?php if ($_GET['state_type']) { ?>
    <input type="hidden" name="state_type" value="<?php echo $_GET['state_type']; ?>" /><!---->
    <?php } ?>
    <tr>
      
      <?php if ($_GET['state_type'] == 'store_order') { ?>
      <input type="checkbox" id="skip_off" value="1" <?php echo $_GET['skip_off'] == 1 ? 'checked="checked"' : null;?>  name="skip_off"><label for="skip_off">不显示已关闭的订单</label>
      <?php } ?>
      <th><?php echo $lang['store_order_add_time'];?></th><!--下单时间-->
    <td colspan="2">
      <input type="text" class="ui_timepicker" name="query_start_date" id="query_start_date" value="<?php echo $_GET['query_start_date']; ?>" style="width:120px" autocomplete="off"/>
      <label class="add-on">
        <i class="icon-calendar"></i>
      </label>
      &nbsp;&#8211;&nbsp;
      <input id="query_end_date" class=" ui_timepicker" type="text" name="query_end_date" value="<?php echo $_GET['query_end_date']; ?>" style="width:120px" autocomplete="off"/>
      <label class="add-on">
        <i class="icon-calendar"></i>
      </label>
    </td><!--开始时间--><!--结束时间-->
    <td><input type="reset" name="reset" id="reset" value="重置时间"/></td>
      </tr>
      <tr>
      <th><?php echo $lang['store_order_buyer'];?></th><!--买家-->
      <td class="w80"><input type="text" class="text w150" name="buyer_name" value="<?php echo $_GET['buyer_name']; ?>" /></td>
      <th>发货人:</th>
      <td>
        <?php if (!empty($output['deliverer'])) { ?>
         <select name="deliverer_id" id="menu1" class="select1" onchange="getMenuByajax()">
            <option  value="">--请选择发货人--</option>
            <?php foreach ($output['deliverer'] as $val) { ?>
              <option value="<?php echo $val['address_id']; ?>" <?php if ($output['deliverer_id']==$val['address_id']) { ?>selected="selected"<?php } ?>><?php echo $val['seller_name']; ?></option>
            <?php } ?>
            </select>
        <?php } else { ?>
          <select name="deliverer_id" class="sgcategory changeSelect">
            <option value="<?php echo $output['goods']['deliverer_id']; ?>"><?php echo $lang['nc_please_choose'];?></option>
          </select>
        <?php } ?>
      </td>
      <!--xinzeng 11.1-->
       <!-- <th>发货人</th> --><!--发货人-->
    <!-- <td class="w100"><input type="text" class="text w150" name="senderusername" value="<?php echo $_GET['senderusername']; ?>" /></td> -->
    </tr>
	<tr>

	  <!--xinjia-->
	  <th><?php echo $lang['store_show_order_receiver'];?></th><!--收货人-->
	  <td class="w80"><input type="text" class="text w200" name="consignee_name" value="<?php echo $_GET['consignee_name']; ?>" /></td>
    <th>自提点</th>
    <td >
      <select name="address_id" class="w160">
        <option value="">--请选择--</option>
        <?php foreach($output['address_list'] as $val) { ?>
            <option <?php if($_GET['address_id'] == $val['address_id']){?>selected<?php }?> value="<?php echo $val['address_id']; ?>"><?php echo $val['seller_name']; ?></option>
        <?php } ?>
      </select>
    </td>  
    </tr>
        <tr>
      <th>仓库</th>
      <td >
        <select name="storage_id" class="w160">
          <option value="">--请选择--</option>
          <?php foreach($output['storage_list'] as $val) { ?>
              <option <?php if($_GET['storage_id'] == $val['storage_id']){?>selected<?php }?> value="<?php echo $val['storage_id']; ?>"><?php echo $val['storage_name']; ?></option>
          <?php } ?>
        </select>
      </td>  
      <th>是否包邮</th>
      <td>
        <select name="by_post">
          <option value=''>所有</option>
          <option value=1 <?php if ($_GET['by_post'] == 1) {?>selected="selected"<?php }?>>自提</option>
          <option value=2 <?php if ($_GET['by_post'] == 2) {?>selected="selected"<?php }?>>包邮</option>
        </select>
      </td>
    </tr>
  <tr>

      <th><?php echo $lang['store_order_order_sn'];?></th><!--订单编号-->
      <td class="w80"><input type="text" class="text w150" name="order_sn" value="<?php echo $_GET['order_sn']; ?>" /></td>
      <th><?php echo "支付方式";?></th><!--模式-->
      <td class="w70">
      <select name="payment_code">
        <option value="online" <?php if ($_GET['payment_code'] == "online") {?>selected="selected"<?php }?>>在线支付</option>
        <option value="zihpay" <?php if ($_GET['payment_code'] == "zihpay") {?>selected="selected"<?php }?>>豫卡通支付</option>
        <option value="offpay" <?php if ($_GET['payment_code'] == "offpay") {?>selected="selected"<?php }?>>站内余额</option>
        <option value="jicardpay" <?php if ($_GET['payment_code'] == "jicardpay") {?>selected="selected"<?php }?>>集团餐卡</option>
         <option value="newpay" <?php if ($_GET['payment_code'] == "newpay") {?>selected="selected"<?php }?>>新零售余额</option>
        <option value="" <?php if ($_GET['payment_code'] == "") {?>selected="selected"<?php }?>>所有</option>
        </select>
      </td>
		<?php if($_GET['state_type']=='cw'){?>
      <td class="w70 tc"><a style="background-color:#ef5a61" class="ncsc-btn-mini ncsc-btn-red mt10" href="index.php?act=store_order&op=cwsubmitall" /><i class="icon-hand-up"></i>补推所有</a></td>
		<?php }?>
    </tr>
    <tr>
      <th>商品名称</th><!--订单编号-->
      <td class="w80"><input type="text" class="text w150" name="goods_name" value="<?php echo $_GET['goods_name']; ?>" /></td>
      <td colspan="2" class="w70 tc" style="text-align: right !important;padding-right:140px;"><label class="submit-border">
          <input type="submit" class="submit" value="<?php echo $lang['store_order_search'];?>" style="font-size:14px;font-weight:bold;color:white;background-color:green;width:80px" /><!--搜索按钮-->
        </label></td>
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
      <th class="w50">售后</th>
      <th class="w110"><?php echo $lang['store_order_buyer'];?></th><!--买家-->
      <th class="w150">其他信息</th><!--其他信息-->
      <th class="w120"><?php echo $lang['store_order_sum'];?></th><!--订单金额-->
      <th class="w100">交易状态</th>
      <th class="w150">交易操作</th>
    </tr>
  </thead>
  <?php if (is_array($output['order_list']) and !empty($output['order_list'])) { ?>
  <?php foreach($output['order_list'] as $order_id => $order) { ?>
  <tbody>
    <tr>
      <td colspan="20" class="sep-row"></td>
    </tr>
    <tr>
      <th colspan="20"><span class="ml10"><?php echo $lang['store_order_order_sn'].$lang['nc_colon'];?><em id="<?php echo $order['order_sn']."_copy_order_id"; ?>"><?php echo $order['order_sn']; ?></em><!--订单编号-->
        <?php if ($order['order_from'] != 1){?><!--下单用的是手机安卓端/IOS端/PC端-->
        <i class="icon-mobile-phone">
        </i>
        <?php }?>
        <a href="javascript:void(0)">
          <i class="icon-copy" id="copy" onclick="tapCopy('<?php echo $order['order_sn']; ?>')" style="font-weight: bold;"></i>
        </a>
</span> <?php if ($order['by_post']>1){ ?>
<span><em class="ncsc-order-amount">邮寄订单</em></span>
  <?php }?>
  <span><?php echo $lang['store_order_add_time'].$lang['nc_colon'];?><em class="goods-time"><?php echo date("Y-m-d H:i:s",$order['add_time']); ?></em></span><!--下单时间-->
  <?php if($order['payment_time']){ ?>
    <span>支付时间：<em class="goods-time"><?php echo date("Y-m-d H:i:s",$order['payment_time']); ?></em></span>
  <?php }?>
<?php if (!empty($order['ziti_ladder_time'])){ ?>
      <span>自提时间:<em class="goods-time"><?php echo date("Y-m-d H:i:s",$order['ziti_ladder_time']); ?></em></span><!--自提时间-->  <?php }?>
<span class="fr mr5"> <a href="index.php?act=store_order_print&order_id=<?php echo $order_id;?>" class="ncsc-btn-mini" target="_blank" title="打印发货单"/><i class="icon-print"></i>打印发货单</a></span><!--打印发货单-->
 </th>
    </tr>
    <?php $i = 0;?>
    <?php foreach($order['goods_list'] as $k => $goods) { ?><!--商品列表-->
    <?php $i++;?>
    <tr>
      <td class="bdl"></td>
      <td class="w80"><div class="ncsc-goods-thumb"><a href="<?php echo $goods['goods_url'];?>" target="_blank"><img src="<?php echo $goods['image_60_url'];?>" onMouseOver="toolTip('<img src=<?php echo $goods['image_240_url'];?>>')" onMouseOut="toolTip()"/></a></div></td>
      <td class="tl"><dl class="goods-name">
          <dt style="max-height: 120px;width: 100px !important;"><a target="_blank" href="<?php echo $goods['goods_url'];?>" title="<?php echo $goods['goods_name']; ?>"><?php echo $goods['goods_name']; ?></a></dt><!--商品名称及链接-->
          <dd>
            <?php if (!empty($goods['goods_type_cn'])){ ?>
            <span class="sale-type"><?php echo $goods['goods_type_cn'];?></span>
            <?php } ?>
              <?php if($goods['is_cw'] == 1){?>
                  <i class="icon-cloud blue"></i>
              <?php }?>
          </dd>
        </dl></td>
      <td><?php echo $goods['goods_price']; ?></td><!--单价-->
      <td><?php echo $goods['goods_num']; ?></td><!--数量-->
      <td><!-- 退款 -->
          <?php if ($goods['refund'] == 1 && $order['order_state']>10){?>
          <p><a href="index.php?act=store_order&op=add_refund&order_id=<?php echo $order['order_id']; ?>&goods_id=<?php echo $goods['rec_id']; ?>&buyer_id=<?php echo $order['buyer_id']; ?>">单品退款</a></p>
          <?php }?></td>

      <!-- S 合并TD -->
      <?php if (($order['goods_count'] > 1 && $k ==0) || ($order['goods_count']) == 1){ ?>
      <td class="bdl" rowspan="<?php echo $order['goods_count'];?>">
	  <div class="buyer"><?php echo $order['buyer_name'];?><br><b style="color:#08a0c3">ID:</b><?php echo $order['buyer_id'];?>
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
                <dd><?php echo $order['extend_order_common']['reciver_info']['address'];?>&nbsp;<?php echo $order['mall_info'];?></dd>
              </dl>
            </div>
          </div>
        </div></td>
        <!-- 新增买家留言，发票 -->
      <!--  <td colspan="20" class="sep-row" style="padding-top: 0px;height: 0px;"></td> -->
        <td class="bdl" rowspan="<?php echo $order['goods_count'];?>">
   
   <!--  <th colspan="20" style="background-color: white;"> -->
      <span class="ml10"><?php echo $lang['store_show_order_buyer_message'].$lang['nc_colon'];?><em><?php echo $order['extend_order_common']['order_message']; ?></em><!--买家留言-->
        </span> <br />

              <span class="ml10"><?php echo "卖家备注".$lang['nc_colon'];?>
                      <textarea id="<?php echo 'deliver_explain'.$order['order_id']?>" name="deliver_explain" cols="10" rows="2" class="w150 tip-t"><?php echo $order['extend_order_common']['deliver_explain'];?></textarea>
                  <input style="display:none;" value="<?php echo $order['order_id']?>" id="order_id"/>
                  <input type="button" value="保存" onclick="deliver_explainsave(<?php echo $order['order_id']?>);" class="ncsc-btn" style="float: right;">

        <span class="ml10" style="display: none"><?php echo "发票".$lang['nc_colon'];?><em class="goods-time">
          <?php foreach ((array)$order['extend_order_common']['invoice_info'] as $key => $value){?>
            <span><?php echo $key;?> (<strong><?php echo $value;?></strong>)
            <?php } ?></em></span><!--发票-->
    <!-- </th> -->
   
      <td class="bdl" rowspan="<?php echo $order['goods_count'];?>"><p class="ncsc-order-amount"><?php echo $order['order_amount']; ?></p>
        <p class="goods-freight">
          <?php if ($order['shipping_fee'] > 0){?>
          (<?php echo $lang['store_show_order_shipping_han']?>运费<?php echo $order['shipping_fee'];?>)
          <?php }else{?>
          <?php echo $lang['nc_common_shipping_free'];?>
          <?php }?>
        </p>
        <p class="goods-pay" title="<?php echo $lang['store_order_pay_method'].$lang['nc_colon'];?><?php echo $order['payment_name']; ?>"><?php echo $order['payment_name']; ?></p></td>
      <td class="bdl bdr" rowspan="<?php echo $order['goods_count'];?>"><p><?php if($order['refund_state']=='1'){echo '部分退款';}else if($order['refund_state']=='2'){echo '已关闭';}else{echo $order['state_desc'];}  ?>
          <?php if($order['evaluation_time']) { ?>
          <br/>
          <?php echo $lang['store_order_evaluated'];?>
          <?php } ?>
        </p>
        
        <!-- 订单查看 -->
        <p><a href="index.php?act=store_order&op=show_order&order_id=<?php echo $order_id;?>" target="_blank"><?php echo $lang['store_order_view_order'];?></a></p>
        
        <!-- 物流跟踪 -->
        <p>
          <?php if ($order['if_deliver']) { ?>
          <a href='index.php?act=store_deliver&op=search_deliver&order_sn=<?php echo $order['order_sn']; ?>'><?php echo $lang['store_order_show_deliver'];?></a>
          <?php } ?>
        </p>

	
	</td>

      <!-- 取消订单 -->
      <td class="bdl bdr" rowspan="<?php echo $order['goods_count'];?>">
        <?php if($order['if_cancel']) { ?>
        <p><a href="javascript:void(0)" class="ncsc-btn ncsc-btn-red mt5" nc_type="dialog" uri="index.php?act=store_order&op=change_state&state_type=order_cancel&order_sn=<?php echo $order['order_sn']; ?>&order_id=<?php echo $order['order_id']; ?>" dialog_title="<?php echo $lang['store_order_cancel_order'];?>" dialog_id="seller_order_cancel_order" dialog_width="400" id="order<?php echo $order['order_id']; ?>_action_cancel" /><i class="icon-remove-circle"></i><?php echo $lang['store_order_cancel_order'];?></a></p>
        <?php } ?>
         <!-- 收货 -->

         <?php if ($order['if_receive']) { ?>
          <p><a href="javascript:void(0)" class="ncsc-btn-mini ncsc-btn-orange mt10" nc_type="dialog" dialog_id="seller_order_receive_order" dialog_width="400" dialog_title="确认收货" uri="index.php?act=store_order&op=change_state&state_type=order_receive&order_sn=<?php echo $order['order_sn']; ?>&order_id=<?php echo $order['order_id']; ?>" id="order<?php echo $order['order_id']; ?>_action_confirm">确认收货</a></p>
           <p><a href="javascript:void(0)" class="ncsc-btn-mini ncsc-btn-orange mt10" nc_type="dialog" dialog_id="seller_order_receive_order" dialog_width="400" dialog_title="转待发货" uri="index.php?act=store_order&op=change_state&state_type=shouhou&order_sn=<?php echo $order['order_sn']; ?>&order_id=<?php echo $order['order_id']; ?>" id="order<?php echo $order['order_id']; ?>_action_confirm">转待发货</a></p>
          <?php } ?>
        
        <!-- 修改运费 
        <?php if ($order['if_modify_price']) { ?>
        <p><a href="javascript:void(0)" class="ncsc-btn-mini ncsc-btn-orange mt10" uri="index.php?act=store_order&op=change_state&state_type=modify_price&order_sn=<?php echo $order['order_sn']; ?>&order_id=<?php echo $order['order_id']; ?>" dialog_width="480" dialog_title="<?php echo $lang['store_order_modify_price'];?>" nc_type="dialog"  dialog_id="seller_order_adjust_fee" id="order<?php echo $order['order_id']; ?>_action_adjust_fee" /><i class="icon-pencil"></i>修改运费</a></p>
        <?php }?>
       修改价格  -->
		<?php if ($order['if_spay_price']) { ?>
        <p><a href="javascript:void(0)" class="ncsc-btn-mini ncsc-btn-green mt10" uri="index.php?act=store_order&op=change_state&state_type=spay_price&order_sn=<?php echo $order['order_sn']; ?>&order_id=<?php echo $order['order_id']; ?>" dialog_width="480" dialog_title="<?php echo $lang['store_order_modify_price'];?>" nc_type="dialog"  dialog_id="seller_order_adjust_fee" id="order<?php echo $order['order_id']; ?>_action_adjust_fee" /><i class="icon-pencil"></i>修改价格</a></p>
		<?php }?>
     <!--订单退款 -->
        <?php if ($order['biaoshi']==0&&$order['order_state']==20){ ?>
          <p><a class="ncsc-btn ncsc-btn-green mt10"href="index.php?act=store_order&op=add_refund_all&order_id=<?php echo $order['order_id']; ?>" class="ncm-btn"><i class="icon-legal"></i>订单退款</a></p>
          <?php } ?>
        <!-- 发货 -->
        <?php if ($order['if_send']) { ?>
        <p><a class="ncsc-btn ncsc-btn-green mt10" href="index.php?act=store_deliver&op=send&order_id=<?php echo $order['order_id']; ?>"/><i class="icon-truck"></i>设置到货</a></p>
        <?php } ?>
        
        <!-- 打印小票-->
        <p><a href="javascript:void(0)" class="ncsc-btn-mini ncsc-btn-blue mt10" uri="index.php?act=store_order&op=print_xiaopiao&order_sn=<?php echo $order['order_sn']; ?>&order_id=<?php echo $order['order_id']; ?>" dialog_width="480" dialog_title="打印小票" nc_type="dialog"  dialog_id="seller_order_xiaopiao" id="order<?php echo $order['order_id']; ?>_action_xiaopiao" /><i class="icon-pencil"></i>打印小票</a></p>
		
        <!-- 补推订单-->
              <?php //if($order['cw_code']!=0 && $order['cw']==1 && $order['cw_msg']!='请勿重复提交订单'){?>
              <?php if($order['cw_code']!=0 && $order['cw']==1 && $order['order_state']>=20){?>
			  <?php if($order['cw_code']!=0) {?>
        <p><a class="ncsc-btn-mini ncsc-btn-red mt10" href="index.php?act=store_order&op=cwsubmit&order_sn=<?php echo $order['order_sn']; ?>&order_id=<?php echo $order['order_id']; ?>" /><i class="icon-hand-up"></i>补推订单</a></p><?php echo $order['cw_msg'];?>
                  <?php }?>
                  <?php }?>
        
        <!-- 锁定 -->
        <?php if ($order['if_lock']) {?>
        <p><?php echo '退款退货中';?></p>
        <?php }?></td>

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
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/select2/select2.min.css"/>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/select2/select2.full.min.js"></script>
<!-- <script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/lib/jquery/1.9.1/jquery.min.js"></script>  -->
<script type="text/javascript">
  //页面加载完成后初始化select2控件
$(function () {
    $("#menu1").select2();
});
//下拉框
function getMenuByajax(){
    //获取menu1的编号
  menuVal = $('#menu1').val();
  //alert('menuVal='+menuVal)
  $("#belong").attr("value",menuVal);//给隐藏的sysNum字段赋值。
}
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
  function tapCopy(order_sn) {
    // console.log(order_sn);
    selectText(order_sn+'_copy_order_id');
    document.execCommand('copy');
    // alert('复制成功');
  }
  //选中文本
  function selectText(element) {
    var text = document.getElementById(element);
    //做下兼容
    if (document.body.createTextRange) {  //如果支持
        var range = document.body.createTextRange(); //获取range
        range.moveToElementText(text); //光标移上去
        range.select();  //选择
    } else if (window.getSelection) {
        var selection = window.getSelection(); //获取selection
        var range = document.createRange(); //创建range
        range.selectNodeContents(text);  //选择节点内容
        selection.removeAllRanges(); //移除所有range
        selection.addRange(range);  //添加range
        /*if(selection.setBaseAndExtent){
          selection.setBaseAndExtent(text, 0, text, 1);
          }*/
    } else {
        alert("复制失败");
    }
  }
  $('#reset').click(function(){
    $('#query_start_date').attr("value","");
  });
</script> 
