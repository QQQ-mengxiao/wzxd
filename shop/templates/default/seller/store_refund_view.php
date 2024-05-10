<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="ncsc-flow-layout">
  <div class="ncsc-flow-container">
    <div class="title">
      <h3>退款服务</h3>
    </div>
    <div id="saleRefund">
      <div class="ncsc-flow-step">
        <dl class="step-first current">
          <dt>买家申请退款</dt>
          <dd class="bg"></dd>
          <dd style="padding-top: 35px;width: 129px;margin-left: -35px;"><?php echo date("Y-m-d H:i:s",$output['refund']['add_time']); ?></dd>
        </dl>
        <dl class="<?php echo $output['refund']['seller_time'] > 0 ? 'current':'';?>">
          <dt>商家处理退款申请</dt>
          <dd class="bg"> </dd>
          <dd style="padding-top: 35px;width: 129px;margin-left: 135px;"><?php echo $output['refund']['seller_time']>0?date("Y-m-d H:i:s",$output['refund']['seller_time']):""; ?></dd>
        </dl>
        <dl class="<?php echo $output['refund']['admin_time'] > 0 ? 'current':'';?>">
          <dt>平台审核，退款完成</dt>
          <dd class="bg"> </dd>
          <dd style="padding-top: 35px;width: 129px;margin-left: 135px;"><?php echo $output['refund']['admin_time']>0?date("Y-m-d H:i:s",$output['refund']['admin_time']):""; ?></dd>
        </dl>
      </div>
    </div>
    <div class="ncsc-form-default">
      <h3>买家退款申请</h3>
      <dl>
        <dt>退款编号：</dt>
        <dd><?php echo $output['refund']['refund_sn']; ?></dd>
      </dl>
      <dl>
        <dt>申请人（买家）：</dt>
        <dd><?php echo $output['refund']['buyer_name']; ?></dd>
      </dl>
      <dl>
        <dt>申请时间：</dt>
        <dd><?php echo date("Y-m-d H:i:s",$output['refund']['add_time']); ?></dd>
      </dl>
      <dl>
        <dt><?php echo $lang['refund_buyer_message'].$lang['nc_colon'];?></dt>
        <dd> <?php echo $output['refund']['reason_info']; ?> </dd>
      </dl>
      <dl>
        <dt><?php echo $lang['refund_order_refund'].$lang['nc_colon'];?></dt>
        <dd><strong class="red"><?php echo $lang['currency'];?><?php echo $output['refund']['refund_amount']; ?></strong></dd>
      </dl>
      <dl>
        <dt>退款说明：</dt>
        <dd> <?php echo $output['refund']['buyer_message']; ?> </dd>
      </dl>
      <dl>
        <dt>健身房扫码时间：</dt>
        <dd> <?php echo $output['jin_time']; ?> </dd>
      </dl>
      <dl>
        <dt>健身房二维码类型：</dt>
        <dd> <?php echo $output['order_type']; ?> </dd>
      </dl>
      <dl>
        <dt>凭证上传：</dt>
        <dd>
          <?php if (is_array($output['pic_list']) && !empty($output['pic_list'])) { ?>
          <ul class="ncsc-evidence-pic">
            <?php foreach ($output['pic_list'] as $key => $val) { ?>
            <?php if(!empty($val)){ ?>
            <li><a href="<?php echo UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/'.$val;?>" nctype="nyroModal" rel="gal" target="_blank"> <img class="show_image" src="<?php echo UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/'.$val;?>"></a></li>
            <?php } ?>
            <?php } ?>
          </ul>
          <?php } ?>
        </dd>
      </dl>
      <h3>商家处理意见</h3>
      <dl>
        <dt><?php echo '处理状态'.$lang['nc_colon'];?></dt>
        <dd> <?php echo $output['state_array'][$output['refund']['seller_state']]; ?> </dd>
      </dl>
      <?php if ($output['refund']['seller_time'] > 0) { ?>
      <dl>
        <dt><?php echo $lang['refund_seller_message'].$lang['nc_colon'];?></dt>
        <dd> <?php echo $output['refund']['seller_message']; ?> </dd>
        <dt>处理时间：</dt>
        <dd> <?php echo $output['refund']['seller_time']>0?date("Y-m-d H:i:s",$output['refund']['seller_time']):"无" ?> </dd>
      </dl>
      <?php } ?>
      <?php if ($output['refund']['seller_state'] == 2) { ?>
      <h3>商城平台退款审核</h3>
      <dl>
        <dt><?php echo '平台确认'.$lang['nc_colon'];?></dt>
        <dd><?php echo $output['admin_array'][$output['refund']['refund_state']]; ?></dd>
      </dl>
      <?php } ?>
      <?php if ($output['refund']['admin_time'] > 0) { ?>
      <dl>
        <dt><?php echo '平台备注'.$lang['nc_colon'];?></dt>
        <dd> <?php echo $output['refund']['admin_message']; ?> </dd>
        <dt>处理时间：</dt>
        <dd> <?php echo $output['refund']['admin_time']>0?date("Y-m-d H:i:s",$output['refund']['admin_time']):"无" ?> </dd>
      </dl>
      <?php } ?>
      <div class="bottom">
        <label class=""><a href="javascript:history.go(-1);" class="ncsc-btn"><i class="icon-reply"></i>返回列表</a></label>
      </div>
    </div>
  </div>
  <?php require template('seller/store_refund_right');?>
</div>
