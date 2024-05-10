<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="page">
 <div class="fixed-bar">
    <div class="item-title">
      <h3>APP分销管理</h3>
      <ul class="tab-base">
<!--        <li><a href="--><?php //echo urlAdmin('mb_payment', 'payment_list');?><!--"><span>分销模式</span></a></li>-->
        <li><a href="<?php echo urlAdmin('mb_fenxiao','fx_index')?>"><span>分销管理</span></a></li>
        <li><a class="current"><span>入驻商家分销</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch" id="formSearch">
  <input type="hidden" value="store" name="act">
  <input type="hidden" value="store" name="op">
  <table class="tb-type1 noborder search">
  <tbody>
    <tr><th><label><?php echo $lang['belongs_level'];?></label></th>
      <td><select name="grade_id">
          <option value=""><?php echo $lang['nc_please_choose'];?>...</option>
          <?php if(!empty($output['grade_list']) && is_array($output['grade_list'])){ ?>
          <?php foreach($output['grade_list'] as $k => $v){ ?>
          <option value="<?php echo $v['sg_id'];?>" <?php if($output['grade_id'] == $v['sg_id']){?>selected<?php }?>><?php echo $v['sg_name'];?></option>
          <?php } ?>
          <?php } ?>
        </select></td><th><label for="owner_and_name"><?php echo $lang['store_user'];?></label></th>
      <td></td><th><label>店铺名称</label></th>
      <th><label for="store_name"><?php echo $lang['store_name'];?></label></th>
      <td><input type="text" value="<?php echo $output['store_name'];?>" name="store_name" id="store_name" class="txt"></td>
        <td><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a>
        <?php if($output['owner_and_name'] != '' or $output['store_name'] != '' or $output['grade_id'] != ''){?>
        <a href="index.php?act=store&op=store" class="btns " title="<?php echo $lang['nc_cancel_search'];?>"><span><?php echo $lang['nc_cancel_search'];?></span></a>
        <?php }?></td>
    </tr></tbody>
  </table>
  </form>
   <table class="table tb-type2" id="prompt">
    <tbody>
      <tr class="space odd">
        <th colspan="12"><div class="title">
            <h5><?php echo $lang['nc_prompts'];?></h5>
            <span class="arrow"></span></div></th>
      </tr>
      <tr>
        <td><ul>
            <li><?php echo $lang['store_help1'];?></li>
            <li>在这里可以编辑各个店铺的分销设置</li>
          </ul></td>
      </tr>
    </tbody>
  </table>
  <form method="post" id="store_form">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2">
      <thead>
        <tr class="thead">
          <th>店铺</th>
          <th>店主账号</th>
          <th>店主卖家账号</th>
          <th class="align-center">所属等级</th>
          <th class="align-center">店铺有效期</th>
          <th class="align-center">店铺状态</th>
          <th class="align-center">操作</th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($output['store_list']) && is_array($output['store_list'])){ ?>
        <?php foreach($output['store_list'] as $k => $v){ ?>
        <tr class="hover edit <?php echo getStoreStateClassName($v);?>">
          <td>
              <a href="<?php echo urlShop('show_store','index', array('store_id'=>$v['store_id']));?>" >
                <?php echo $v['store_name'];?>
            </a>
          </td>
          <td><?php echo $v['member_name'];?></td>
          <td><?php echo $v['seller_name'];?></td>
          <td class="align-center"><?php echo $output['search_grade_list'][$v['grade_id']];?></td>
          <td class="nowarp align-center"><?php echo $v['store_end_time']?date('Y-m-d', $v['store_end_time']):$lang['no_limit'];?></td>
          <td class="align-center w72"><?php echo $v['store_state']?$lang['open']:$lang['close'];?></td>
        <td class="align-center w200">
            <a href="index.php?act=store&op=store_joinin_detail&member_id=<?php echo $v['member_id'];?>">查看</a>&nbsp;&nbsp;<a href="index.php?act=store&op=mb_store_edit&store_id=<?php echo $v['store_id']?>"><?php echo $lang['nc_edit'];?></a>&nbsp;&nbsp;
            </td>
        </tr>
        <?php } ?>
        <?php }else { ?>
        <tr class="no_data">
          <td colspan="15"><?php echo $lang['nc_no_record'];?></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td></td>
          <td colspan="16">
            <div class="pagination"><?php echo $output['page'];?></div></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.edit.js" charset="utf-8"></script>
<script>
$(function(){
    $('#ncsubmit').click(function(){
      $('input[name="op"]').val('store');$('#formSearch').submit();
    });
});
</script>
