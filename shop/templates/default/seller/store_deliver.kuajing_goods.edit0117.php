<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="eject_con">
  <div class="adds">
    <div id="warning"></div>
    <form method="post" action="index.php?act=store_deliver&op=STO_parameter_save2&$rec_id=<?php echo $output['goods_info']['rec_id'];?>" id="sto_form" target="_parent">
      <input type="hidden" name="form_submit" value="ok" />
      <dl>
        <dt class="required">商品规格</dt>
        <dd>
          <input type="text"  class="text" name="totalLogisticsNo" id="totalLogisticsNo"  style="width:280px" value="<?php echo $output['address_info']['specifications'];?>"/>
        </dd>
      </dl>
      <!--航班航次号-->
      <dl>
        <dt class="required">毛重(单件商品)</dt>
        <dd>
          <input type="text" class="text" name="voyageNo" id="voyageNo" style="width:100px" value="<?php echo $output['address_info']['weight'];?>"/>
        </dd>
      </dl>
      <dl>
        <dl>
        <dt class="required">净重(单件商品)</dt>
        <dd>
          <input type="text" class="text" name="logisticsNo" id="logisticsNo" style="width:100px" value="<?php echo $output['address_info']['net_weight'];?>"/>
        </dd>
      <dl>
        <dt class="required">法定数量</dt>
        <dd>
          <input type="text" class="text" name="transTool" id="transTool" value="<?php echo $output['address_info']['qty1'];?>"/>
        </dd>
      </dl>
      

      <div class="bottom"><label class="submit-border"><a href="javascript:void(0);" id="submit" class="submit">保存</a></label></div>
    </form>
  </div>
</div>
<script type="text/javascript">
$(function(){

$('#jcbOrderTime').datepicker({dateFormat: 'yy-mm-dd'});

});

$(document).ready(function(){
    $('#sto_form').validate({
        rules : {
            totalLogisticsNo : {
                required : false
            },
            logisticsNo : {
                required : false
            },            
            voyageNo : {
                required : false
            },
            transType : {
                required : false
            },
            transTool : {
                required : false
            },
            jcbOrderTime : {
                required : false
            },
            jcbOrderPort : {
                required : false
            },
            jcbOrderPortInsp : {
                required : false
            }
        },
        messages : {
            totalLogisticsNo: {
                required : '<i class="icon-exclamation-sign"></i>总单号不能为空'
            },
            jcbOrderTime: {
                required : '<i class="icon-exclamation-sign"></i>进/出境日期不能为空'
            },
            voyageNo: {
                required : '<i class="icon-exclamation-sign"></i>航班航次号不能为空'
            },
            transType: {
                required : '<i class="icon-exclamation-sign"></i>运输方式不能为空'
            },
            transTool: {
                required : '<i class="icon-exclamation-sign"></i>运输工具不能为空'
            },
            jcbOrderPort: {
                required : '<i class="icon-exclamation-sign"></i>进/出境口岸(关)不能为空'
            },
            jcbOrderPortInsp: {
                required : '<i class="icon-exclamation-sign"></i>进/出境口岸(检)不能为空'
            }
        }
    });
	$('#submit').on('click',function(){
		if ($('#sto_form').valid()) {
            var reciver_transTool = $('#transTool').val();
            var reciver_transType = $('#transType').val();
            var reciver_voyageNo = $('#voyageNo').val();
            var reciver_totalLogisticsNo = $('#totalLogisticsNo').val();
            var reciver_logisticsNo = $('#logisticsNo').val();
            var reciver_jcbOrderTime = $('#jcbOrderTime').val();
            var reciver_jcbOrderPort = $('#jcbOrderPort').val();
            var reciver_jcbOrderPortInsp = $('#jcbOrderPortInsp').val();

            $.post(
            "<?php echo urlShop('store_deliver', 'STO_parameter_save2');?>", 
            {
                rec_id: <?php echo $output['goods_info']['rec_id'];?>,
                reciver_transTool: reciver_transTool,
                reciver_transType: reciver_transType,
                reciver_voyageNo: reciver_voyageNo,
                reciver_totalLogisticsNo: reciver_totalLogisticsNo,
                reciver_logisticsNo: reciver_logisticsNo,
                reciver_jcbOrderTime: reciver_jcbOrderTime,
                reciver_jcbOrderPort: reciver_jcbOrderPort,
                reciver_jcbOrderPortInsp: reciver_jcbOrderPortInsp
            })
            .done(function(data) {
              // console.log(data);return;
                if(data == 'true') {
                    $('#transTool').val(reciver_transTool);
                    $('#transType').val(reciver_transType);
                    $('#voyageNo').val(reciver_voyageNo);
                    $('#totalLogisticsNo').val(reciver_totalLogisticsNo);
                    $('#logisticsNo').val(reciver_logisticsNo);
                    $('#jcbOrderTime').val(reciver_jcbOrderTime);
                    $('#jcbOrderPort').val(reciver_jcbOrderPort);
                    $('#jcbOrderPortInsp').val(reciver_jcbOrderPortInsp);
                               showSucc('修改成功');
                    // var content = reciver_totalLogisticsNo + '&nbsp' + reciver_jcbOrderTime + '&nbsp;' + reciver_jcbOrderPort + '&nbsp;' + reciver_jcbOrderPortInsp + '&nbsp;运单号：'+reciver_logisticsNo;
                    // $('#waybill_span').html(content);
                    DialogManager.close('edit_waybill_info2');

                } else {
                    showError('修改失败');
                }
            });
		}
	});
    // $('#totalLogisticsNo').val($('#reciver_totalLogisticsNo').val());
    // $('#jcbOrderTime').val($('#reciver_jcbOrderTime').val());
    // $('#jcbOrderPort').val($('#reciver_jcbOrderPort').val());
    // $('##jcbOrderPortInsp').val($('reciver_jcbOrderPortInsp').val());
 

});
</script>
