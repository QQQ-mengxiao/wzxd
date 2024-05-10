<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo '裂变红包管理';?></h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo '红包列表';?></span></a></li>
          <li><a href="<?php echo urlAdmin('red_packet', 'withdraw_records_list');?>"><span><?php echo '提现记录';?></span></a></li>
          <li><a href="<?php echo urlAdmin('red_packet', 'red_packet_setting');?>"><span><?php echo '红包设置';?></span></a></li>
      </ul>
    </div>
  </div>
  <!--  搜索 -->
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch">
    <input type="hidden" name="act" value="groupbuy">
    <input type="hidden" name="op" value="groupbuy_list">
    <table class="tb-type1 noborder search">
      <!--<tbody>
        <tr>
          <th><label for="xianshi_name">抢购名称</label></th>
          <td><input type="text" value="<?php //echo $_GET['groupbuy_name'];?>" name="groupbuy_name" id="groupbuy_name" class="txt" style="width:100px;"></td>
          <th><label for="store_name"><?php //echo $lang['store_name'];?></label></th>
          <td><input type="text" value="<?php //echo $_GET['store_name'];?>" name="store_name" id="store_name" class="txt" style="width:100px;"></td>
          <th><label for="groupbuy_state">状态</label></th>
          <td>
              <select name="groupbuy_state" class="w90">
                  <?php //if(is_array($output['groupbuy_state_array'])) { ?>
                  <?php //foreach($output['groupbuy_state_array'] as $key=>$val) { ?>
                  <option value="<?php //echo $key;?>" <?php //if($key == $_GET['groupbuy_state']) { echo 'selected';}?>><?php //echo $val;?></option>
                  <?php //} ?>
                  <?php //} ?>
              </select>
          </td>
          <td><a href="javascript:document.formSearch.submit();" class="btn-search " title="<?php //echo $lang['nc_query'];?>">&nbsp;</a></td>
      </tr>
  </tbody>-->
    </table>
  </form>
<div style="text-align:right;">
  <a class="btns" target="_blank" href="index.php?<?php echo $_SERVER['QUERY_STRING'];?>&op=export_red_packet">
  <span><?php echo $lang['nc_export'];?>Excel</span></a></div>
  <form id="list_form" method="post">
    <input type="hidden" id="group_id" name="group_id"  />
    <table class="table tb-type2">
      <thead>
        <tr class="thead">
          <th class="align-center" width="80"><?php echo '红包ID';?></th>
          <th class="align-center" width="80"><?php echo '用户ID';?></th>
          <th class="align-center" width="80"><?php echo '用户名';?></th>
          <th class="align-center" width="80"><?php echo '总金额';?></th>
          <th class="align-center" width="80"><?php echo '已拆金额';?></th>
          <th class="align-center" width="120"><?php echo '获得时间';?></th>
          <th class="align-center" width="120"><?php echo '拆开时间';?></th>
          <th class="align-center" width="50"><?php echo '是否拆开';?></th>
          <th class="align-center" width="80"><?php echo '帮拆人数';?></th>
          <th class="align-center" width="80"><?php echo '操作';?></th>
        </tr>
      </thead>
      <tbody id="treet1">
        <?php if(!empty($output['red_packet_list']) && is_array($output['red_packet_list'])){ ?>
        <?php foreach($output['red_packet_list'] as $k => $val){ ?>
        <tr class="hover">
          <td  class="align-center nowarp"><?php echo $val['red_packetid'];?></td>
          <td  class="align-center nowarp"><?php echo $val['member_id'];?></td>
          <td  class="align-center nowarp"><?php echo Model('member')->getfby_member_id($val['member_id'],'member_name');?></td>
          <td  class="align-center nowarp"><?php echo ncPriceFormat($val['red_packet_amount']);?></td>
          <td  class="align-center nowarp"><?php echo ncPriceFormat($val['amount']);?></td>
          <td  class="align-center nowarp"><?php echo date('Y-m-d H:i:s',substr($val['start_time'],0,10));?></td>
          <td  class="align-center nowarp"><?php echo empty($val['open_time'])?'无':date('Y-m-d H:i:s',substr($val['open_time'],0,10));?></td>
          <td  class="align-center nowarp"><?php echo empty($val['open_time'])?'未拆开':'已拆开';?></td>
          <td  class="align-center nowarp"><?php echo $val['member_nums'];?></td>
            <td class="w96 align-center"><a href="index.php?act=red_packet&op=open_records&red_packetid=<?php echo $val['red_packetid'];?>"><?php echo '帮拆记录';?></a></td>
        </tr>
        <?php } ?>
        <?php }else { ?>
        <tr class="no_data">
          <td colspan="16"><?php echo $lang['nc_no_record'];?></td>
        </tr>
        <?php } ?>
      </tbody>
      <?php if(!empty($output['red_packet_list']) && is_array($output['red_packet_list'])){ ?>
      <tfoot>
        <tr class="tfoot">
          <td colspan="16"><label>
            &nbsp;&nbsp;
            <div class="pagination"><?php echo $output['show_page'];?> </div></td>
        </tr>
      </tfoot>
      <?php } ?>
    </table>
  </form>
</div>
<form id="op_form" action="" method="POST">
    <input type="hidden" id="groupbuy_id" name="groupbuy_id">
</form>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.edit.js" charset="utf-8"></script>
<script type="text/javascript">
</script>
