<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="tabmenu">
  <?php include template('layout/submenu');?>
  <a class="ncsc-btn ncsc-btn-green" href="<?php echo urlShop('store_buy_deliver', 'buy_deliver_add');?>"><i class="icon-plus-sign"></i><?php echo "添加活动";?></a>
</div>

<div class="alert alert-block mt10">
  <ul>
    <li>1、点击添加活动按钮可以添加即买即送活动，点击管理按钮可以对即买即送活动内的商品进行管理</li>
    <li>2、点击删除按钮可以删除即买即送活动</li>
 </ul>
</div>

<form method="get">
  <table class="search-form">
    <input type="hidden" name="act" value="store_buy_deliver" />
    <input type="hidden" name="op" value="buy_deliver_list" />
    <tr>
      <td>&nbsp;</td>
      <th>状态</th>
      <td class="w100"><select name="state">
          <?php if(is_array($output['buy_deliver_state_array'])) { ?>
          <?php foreach($output['buy_deliver_state_array'] as $key=>$val) { ?>
          <option value="<?php echo $key;?>" <?php if(intval($key) === intval($_GET['state'])) echo 'selected';?>><?php echo $val;?></option>
          <?php } ?>
          <?php } ?>
        </select></td>
      <th class="w110"><?php echo "活动名称";?></th>
      <td class="w160"><input type="text" class="text w150" name="buy_deliver_name" value="<?php echo $_GET['buy_deliver_name'];?>"/></td>
      <td class="w70 tc"><label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_search'];?>" /></label></td>
    </tr>
  </table>
</form>
<table class="ncsc-default-table">
  <thead>
    <tr>
      <th class="w30"></th>
      <th class="tl"><?php echo "物资小店（即送）";?></th>
      <th class="w180"><?php echo "配送点";?></th>
      <th class="w80"><?php echo "活动状态";?></th>
      <th class="w150"><?php echo $lang['nc_handle'];?></th>
    </tr>
  </thead>
  <?php if(!empty($output['buy_deliver_list']) && is_array($output['buy_deliver_list'])){?>
  <?php foreach($output['buy_deliver_list'] as $key=>$val){?>
  <tbody id="buy_deliver_list">
    <tr class="bd-line">
      <td></td>
      <td class="tl"><dl class="goods-name">
          <dt><?php echo $val['buy_deliver_name'];?></dt>
        </dl></td>
      <td><?php echo $val['ziti_name'];?></td>
      <td><?php echo $val['buy_deliver_state_text'];?></td>
      <td class="nscs-table-handle tr">
          <?php if($val['editable']) { ?>
          <span>
              <a href="index.php?act=store_buy_deliver&op=buy_deliver_edit&buy_deliver_id=<?php echo $val['buy_deliver_id'];?>" class="btn-blue">
                  <i class="icon-edit"></i>
                  <p><?php echo $lang['nc_edit'];?></p>
              </a>
          </span>
          <?php } ?>
          <span>
              <a href="index.php?act=store_buy_deliver&op=buy_deliver_manage&buy_deliver_id=<?php echo $val['buy_deliver_id'];?>" class="btn-green">
                  <i class="icon-cog"></i>
                  <p><?php echo $lang['nc_manage'];?></p>
              </a>
          </span>
          <span>
              <a href="javascript:;" nctype="btn_del_buy_deliver" data-buy-deliver-id=<?php echo $val['buy_deliver_id'];?> class="btn-red">
                  <i class="icon-trash"></i>
                  <p><?php echo $lang['nc_delete'];?></p>
              </a>
          </span>
      </td>
  </tr>
  <?php }?>
  <?php }else{?>
  <tr id="buy_deliver_list_norecord">
      <td class="norecord" colspan="20"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
  </tr>
  <?php }?>
  </tbody>
  <tfoot>
    <?php if(!empty($output['buy_deliver_list']) && is_array($output['buy_deliver_list'])){?>
    <tr>
      <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
    </tr>
    <?php } ?>
  </tfoot>
</table>
<form id="submit_form" action="" method="post" >
  <input type="hidden" id="buy_deliver_id" name="buy_deliver_id" value="">
</form>
<script type="text/javascript">
    $(document).ready(function(){
        $('[nctype="btn_del_buy_deliver"]').on('click', function() {
            if(confirm('<?php echo $lang['nc_ensure_del'];?>')) {
                var action = "<?php echo urlShop('store_buy_deliver', 'buy_deliver_del');?>";
                var buy_deliver_id = $(this).attr('data-buy-deliver-id');
                $('#submit_form').attr('action', action);
                $('#buy_deliver_id').val(buy_deliver_id);
                ajaxpost('submit_form', '', '', 'onerror');
            }
        });
    });
</script>
