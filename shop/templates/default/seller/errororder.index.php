<!--商家管理中心-订单物流-实物交易订单-->
<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div><!--所有订单/待付款/待发货/已发货/已完成/已取消导航栏-->
<form method="get" action="index.php" target="_self">
  <table class="search-form">
    <input type="hidden" name="act" value="store_error_order" />
    <input type="hidden" name="op" value="index" />
    <?php if ($_GET['state_type']) { ?>
    <input type="hidden" name="state_type" value="<?php echo $_GET['state_type']; ?>" /><!---->
    <?php } ?>
    <tr>
      
      <?php if ($_GET['state_type'] == 'store_order') { ?>
      <input type="checkbox" id="skip_off" value="1" <?php echo $_GET['skip_off'] == 1 ? 'checked="checked"' : null;?>  name="skip_off"><label for="skip_off">不显示已关闭的订单</label>
      <?php } ?>
     <th>订单号</th><!--订单编号-->
      <td class="w100"><input type="text" class="text w150" name="order_sn" value="<?php echo $_GET['order_sn']; ?>" /></td>
       <td class="w70 tc"><label class="submit-border">
          <input type="submit" class="submit" value="搜索" /><!--搜索按钮-->
        </label></td>
    </tr>
  </table>
</form>
<table class="ncsc-default-table order">
   <thead>
    <tr>
      <th class="w160">异常订单号</th>
      <th class="w130">买家</th>
      <th class="w120">下单时间</th>
      <th class="w120">订单金额</th>
      <th class="w120">订单活动类型</th>
      <th class="w100">支付方式</th><!--订单金额-->
      <th class="w100">订单状态</th>
      <th class="w140">操作</th>
    </tr>
  </thead>
   <tbody>
    <?php if(!empty($output['order_list']) && is_array($output['order_list'])){ ?>
      <?php foreach($output['order_list'] as $k => $v){ ?>
        <tr class="bd-line">
        <!-- <td><input type="checkbox" name='check_id[]' value="<?php echo $v['id'];?>" class="checkitem"></td> -->
          <td class="align-center"><?php echo $v['order_sn'];?></td>
          <td class="align-center"><?php echo $v['buyer_name'];?></td>
          <td class="align-center"><?php echo date("Y-m-d H:i:s",$v['add_time']); ?></td>
          <td class="align-center"><?php echo $v['order_amount'];?></td>
         <!--  <td class="align-center"><?php echo $v['goods_amount'];?></td> -->
          <td class="align-center"><?php if($v['order_type']=='2'){echo '团购价';}elseif($v['order_type']=='1'){echo '阶梯价';}elseif($v['order_type']=='3'){echo '新人专享价';}elseif($v['order_type']=='4'){echo '限时秒杀';}elseif($v['order_type']=='5'){echo '即买即送';}else{echo '无活动';}?></td>
          <td class="align-center"><?php echo $v['payment_code'];?></td>
           <td class="align-center"><?php if($v['order_state']=='0'){echo '已取消';}elseif($v['order_state']=='10'){echo '未支付';}elseif($v['order_state']=='20'){echo '已支付';}elseif($v['order_state']=='30'){echo '已发货';}else{echo '已收货';}?></td> 
          
          <!-- <td class="align-center"><font color="red"><?php echo $v['tax_rate'];?></font></td> -->
          <!-- <td class="align-center"><?php echo $v['note'];?></td> -->
          <td class="w100 align-center">
        <a href="index.php?act=store_error_order&op=yundayin&order_id=<?php echo $v['order_id'];?> ">手动打单</a>
          </td>
        </tr>
        <?php } ?>
        <?php }else { ?>
        <tr class="no_data">
          <td colspan="10"><?php echo $lang['nc_no_record'];?></td>
        </tr>
        <?php } ?>
      </tbody>
    <tfoot>
      <tr class="tfoot">
        <td colspan="15" id="dataFuncs"><div class="pagination"> <?php echo $output['show_page'];?> </div></td>
      </tr>
    </tfoot>
  </table>
</div>
