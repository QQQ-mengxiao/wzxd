<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>

<table class="ncsc-default-table">
  <thead>
    <tr><th class="w30"></th>
      <th class="tl"> 活动名称</th>
      <th class="tl"> 配送时间点</th>
      <th class="w250">添加时间</th>
      
      <th class="w300">活动内容</th>
      <!-- <th class="w110"><?php echo $lang['nc_state'];?></th> -->
    </tr>
  </thead>
  <tbody>
    <tr class="bd-line"><td></td>
      <td class="tl"><dl class="goods-name"><dt><?php echo $output['mansong_info']['p_name'];?></dt></dl></td>
       <td class="tl"><dl class="goods-name"><dt><?php echo $output['mansong_info']['deliver_time'];?></dt></dl></td>
      <td><p><?php echo date('Y-m-d H:i',$output['mansong_info']['add_time']);?></p></td>
      <td><ul class="ncsc-mansong-rule-list">
          <?php if(!empty($output['list']) && is_array($output['list'])){?>
          <?php foreach($output['list'] as $key=>$val){?>
          <li>下单后<strong><?php echo $val['time'];?></strong>小时内配送&nbsp;   
                  享受商品折扣<strong><?php echo $val['discount'];?></strong>折&nbsp;
          </li>
          <?php }?>
          <?php }?>
        </ul></td>
    </tr>
  <tbody> 
</table>
