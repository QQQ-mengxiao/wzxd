<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="page">
  <!-- 页面导航 -->
  <div class="fixed-bar">
      <div class="item-title">
          <h3><?php echo '裂变红包管理';?></h3>
          <ul class="tab-base">
              <li><a href="<?php echo urlAdmin('red_packet', 'redpacket_list');?>"><span><?php echo '红包列表';?></span></a></li>
              <li><a href="<?php echo urlAdmin('red_packet', 'withdraw_records_list');?>"><span><?php echo '提现记录';?></span></a></li>
              <li><a href="JavaScript:void(0);" class="current"><span><?php echo '红包设置';?></span></a></li>
          </ul>
      </div>
  </div>
  <form id="add_form" method="post" enctype="multipart/form-data" action="<?php echo urlAdmin('red_packet', 'red_packet_setting_save');?>">
    <input type="hidden" id="submit_type" name="submit_type" />
    <table class="table tb-type2">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required">裂变红包活动是否开启:</td>
        </tr>
        <tr class="noborder">
            <td class="vatop rowform">
                <input type="text" id="redpacket_allow" name="redpacket_allow" value="<?php echo $output['redpacket_allow'];?>" class="txt">
            </td>
            <td class="vatop tips">输入0为未开启，1为开启</td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="required">固定金额:</td>
        </tr>
        <tr class="noborder">
            <td class="vatop rowform">
                <input type="text" id="red_packet_price_array" name="red_packet_price_array" value="<?php echo $output['red_packet_price_array'];?>" class="txt">
            </td>
            <td class="vatop tips">红包金额为随机的所填金额，以','分隔多个金额。</td>
        </tr>
		<tr class="noborder">
          <td colspan="2" class="required">需帮拆人次数:</td>
        </tr>
        <tr class="noborder">
            <td class="vatop rowform">
                <input type="text" id="member_nums" name="member_nums" value="<?php echo $output['member_nums'];?>" class="txt">
            </td>
            <td class="vatop tips">包括红包拥有者第一次分享的次数，以','分隔次数的区间。</td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="required">红包个数:</td>
        </tr>
        <tr class="noborder">
            <td class="vatop rowform">
                <input type="text" id="redpacket_max_amount" name="redpacket_max_amount" value="<?php echo $output['redpacket_max_amount'];?>" class="txt">
            </td>
            <td class="vatop tips">用户累计最多提现的红包个数。</td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="required">帮拆次数:</td>
        </tr>
        <tr class="noborder">
            <td class="vatop rowform">
                <input type="text" id="redpacket_member_nums" name="redpacket_member_nums" value="<?php echo $output['redpacket_member_nums'];?>" class="txt">
            </td>
            <td class="vatop tips">每个用户每天拥有的帮拆次数。</td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="required">红包最大限额:</td>
        </tr>
        <tr class="noborder">
            <td class="vatop rowform">
                <input type="text" id="redpacket_num" name="redpacket_num" value="<?php echo $output['redpacket_num'];?>" class="txt">
            </td>
            <td class="vatop tips">系统提现红包总个数不能超过红包最大限额</td>
        </tr>

        <!--        <tr class="noborder">-->
<!--          <td colspan="2" class="required">金额区间:</td>-->
<!--        </tr>-->
<!--        <tr class="noborder">-->
<!--            <td class="vatop rowform">-->
<!--                <input type="text" id="red_packet_price_region" name="red_packet_price_region" value="--><?php //echo $output['red_packet_price_region'];?><!--" class="txt">-->
<!--            </td>-->
<!--            <td class="vatop tips">红包金额为填写的金额区间内的随机值，以'-'分隔始末值。</td>-->
<!--        </tr>-->
<!--        <tr><td colspan="2"><B style="color: red;">固定金额与金额区间只能选择一项进行填写，若固定金额和金额区间都填写以固定金额为准。</B></td></tr>-->
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
        	groupbuy_price: {
                required : true,
                digits : true,
                min : 0
            }
        },
        messages : {
      		groupbuy_price: {
       			required : '必填',
       			digits : '数字',
                min : '最小'
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
