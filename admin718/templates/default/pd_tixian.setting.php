<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <!-- 页面导航 -->
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo '裂变红包管理';?></h3>
      <ul class="tab-base">
      <!-- <li><a href="index.php?act=predeposit&op=pd_cash_list"><span>提现管理</span></a></li> -->
      <li><a href="<?php echo urlAdmin('red_packet', 'redpacket_list');?>"><span><?php echo '红包列表';?></span></a></li>
              <li><a href="<?php echo urlAdmin('red_packet', 'withdraw_records_list');?>"><span><?php echo '提现记录';?></span></a></li>
              <li><a href="<?php echo urlAdmin('red_packet', 'red_packet_setting');?>"><span><?php echo '红包设置';?></span></a></li>
          <li><a href="JavaScript:void(0);" class="current"><span>余额提现设置</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="add_form" method="post" enctype="multipart/form-data" action="index.php?act=red_packet&op=tixian_setting_save">
    <input type="hidden" id="submit_type" name="submit_type" />
    <table class="table tb-type2">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required"><label class="validation">提现额度:</label></td>
        </tr>
        <tr class="noborder">
            <td class="vatop rowform">
                <input type="text" id="pd_tixian" name="pd_tixian" value="<?php echo $output['setting']['pd_tixian'];?>" class="txt">
            </td>
            <td class="vatop tips">设置为0提示功能未开启，余额大于等于提现额度才可提现</td>
        </tr>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td colspan="15"><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script>
$(document).ready(function(){
    $("#submitBtn").click(function(){ 
        $("#add_form").submit();
    });
    //页面输入内容验证
  $("#add_form").validate({
    errorPlacement: function(error, element){
      error.appendTo(element.parent().parent().prev().find('td:first'));
        },

        rules : {
          pd_tixian: {
                required : true,
                digits : true,
                min : 0
            }
        },
        messages : {
          pd_tixian: {
            required : '<?php echo $lang['xianshi_price_error'];?>',
            digits : '<?php echo $lang['xianshi_price_error'];?>',
                min : '<?php echo $lang['xianshi_price_error'];?>'
            }
        }
  });
});
//submit函数
function submit_form(submit_type){
  $('#submit_type').val(submit_type);
  $('#add_form').submit();
}
</script> 
