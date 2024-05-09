<?php defined('In718Shop') or exit('Access Invalid!');?>
<style>
  .rowform input{
    margin-right: 10px;
    width: 246px;
    padding-left: 10px;
  }
  #btn_submit{
    margin-top: 20px;
    margin-left: 350px;
  }
</style>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>APP分销管理</h3>
      <ul class="tab-base">
<!--        <li><a href="--><?php //echo urlAdmin('mb_payment', 'payment_list');?><!--"><span>分销模式</span></a></li>-->
        <li><a class="current"><span>分销管理</span></a></li>
        <li><a href="<?php echo urlAdmin('withdraw_commission','fx_cash_list')?>"><span><?php echo $output['fenxiao']['fenxiao_brokerage']?>提现管理</span></a></li>
<!--         <li><a href="<?php echo urlAdmin('mb_fenxiao','fx_store')?>"><span>入驻商家分销</span></a></li> -->
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <table class="table tb-type2" id="prompt">
        <tbody>
        <tr class="space odd">
            <th colspan="12"><div class="title"><h5><?php echo $lang['nc_prompts'];?></h5><span class="arrow"></span></div></th>
        </tr>
        <tr>
            <td>
                <ul>
                    <li>此处可编辑APP端分销相关设置</li>
                </ul>
            </td>
        </tr>
        </tbody>
  </table>
  <form id="post_form" method="post" name="form1" action="<?php echo urlAdmin('mb_fenxiao', 'setting_save');?>">
    <table class="table tb-type2 nobdb">
      <tbody>
        <!--隐藏分销模式 181026 -->
       <!--  <tr class="noborder">
          <td class="vatop rowform">分销模式</td>
          <td class="vatop rowform">
            <select name="fenxiao_mode">
              <option value="1" <?php echo $output['fenxiao']['fenxiao_mode']==1 ? 'selected' : ''?>>一级分销</option>
              <option value="2" <?php echo $output['fenxiao']['fenxiao_mode']==2 ? 'selected' : ''?>>二级分销</option>
              <option value="3" <?php echo $output['fenxiao']['fenxiao_mode']==3 ? 'selected' : ''?>>三级分销</option>
            </select>            
          <td class="vatop tips"></td>
        </tr> -->
<!--           <tr class="noborder">
          <td class="vatop rowform">内购模式</td>
          <td class="vatop rowform">
            <select name="fenxiao_in_purchase">
              <option value="1" <?php echo $output['fenxiao']['fenxiao_in_purchase']==1 ? 'selected' : ''?>>关闭</option>
              <option value="2" <?php echo $output['fenxiao']['fenxiao_in_purchase']==2 ? 'selected' : ''?>>开启</option>
            </select>            
        </tr> -->
        <!--隐藏内购模式 181026 -->
      <!--  <tr>
          <td class="vatop rowform">内购模式</td>
          <td class="vatop rowform onoff" style="width:100px;"><label for="fenxiao_in_purchase2" class="cb-enable <?php if($output['fenxiao']['fenxiao_in_purchase'] == '2'){ ?>selected<?php } ?>" ><span><?php echo $lang['open'];?></span></label>
            <label for="fenxiao_in_purchase1" class="cb-disable <?php if($output['fenxiao']['fenxiao_in_purchase'] == '1'){ ?>selected<?php } ?>" ><span><?php echo $lang['close'];?></span></label>
            <input id="fenxiao_in_purchase2" name="fenxiao_in_purchase" <?php if($output['fenxiao']['fenxiao_in_purchase'] == '2'){ ?>checked="checked"<?php } ?>  value="2" type="radio">
            <input id="fenxiao_in_purchase1" name="fenxiao_in_purchase" <?php if($output['fenxiao']['fenxiao_in_purchase'] == '1'){ ?>checked="checked"<?php } ?> value="1" type="radio">
          </td>
        </tr> -->
        <tr class="noborder">
          <td class="vatop rowform">佣金名称</td>
          <td class="vatop rowform">
            <input type="text" name="fenxiao_brokerage" value="<?php echo $output['fenxiao']['fenxiao_brokerage']?>" />
          </td>
          <td class="vatop tips"></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">成为分销商</td>
          <td class="vatop rowform">
            <select name="fenxiao_con" onchange="conReset(event)">
              <option value="1" <?php echo $output['fenxiao']['fenxiao_con']==1 ? 'selected' : ''?>>达到指定消费金额</option>
              <option value="2" <?php echo $output['fenxiao']['fenxiao_con']==2 ? 'selected' : ''?>>购买指定商品</option>
            </select>
          </td>
          <td class="vatop rowform">
            <input type="text" name="fenxiao_con_val" value="<?php echo $output['fenxiao']['fenxiao_con_val']?>"/>
          </td>
        </tr>
            <tr class="hover">
             <td>提现门槛</td>
              <td><input id="points_comments" name="fenxiao_com_wd" value="<?php echo $output['fenxiao']['fenxiao_com_wd'];?>" class="txt" type="text" style="width:50px;">元&nbsp;&nbsp;&nbsp;设置提现金额门槛（例如设置50，则50以下的金额无法提现)</td>
                </tr>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td colspan="15">
            <a href="JavaScript:void(0);" class="btn" id="btn_submit" ><span><?php echo $lang['nc_submit'];?></span></a>
          </td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script>
function conReset(event){
  var oForm = $(event.target).parents("form");
  var oConVal = oForm.find('[name="fenxiao_con_val"]');
  var con_former = <?php echo $output['fenxiao']['fenxiao_con']?>;
  var con_former_val = "<?php echo $output['fenxiao']['fenxiao_con_val']?>";
  var con_now = $(event.target).val();

  if(con_former == con_now){
    oConVal.attr('placeholder',con_former_val);
  }
  else{
    (con_now == 1) ? oConVal.attr('placeholder','请填写金额门槛') : oConVal.attr('placeholder','商品平台货号，最多10个以‘;’分割');
  }
}    
$(function(){
  $('#btn_submit').on('click', function() {
    $('#post_form').submit();
  });
});
</script> 
