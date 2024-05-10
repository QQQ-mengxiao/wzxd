<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="eject_con">
  <div class="adds">
    <div id="warning"></div>
    <form method="post" action="index.php?act=store_deliver&op=buyer_address&order_id=<?php echo $_GET['order_id'];?>" id="sto_form" target="_parent">
      <input type="hidden" name="form_submit" value="ok" />
      <dl>
        <dt class="required">总单号</dt>
        <dd>
          <input type="text" class="text" name="totalLogisticsNo" id="totalLogisticsNo" value="<?php echo $output['address_info']['waybill_info']['totalLogisticsNo'];?>"/>
        </dd>
      </dl>
      <!--航班航次号-->
      <dl>
        <dt class="required">航班航次号</dt>
        <dd>
          <input type="text" class="text" name="voyageNo" id="voyageNo" value="<?php echo $output['address_info']['waybill_info']['voyageNo'];?>"/>
        </dd>
      </dl>
      <dl>
        <dt class="required">进/出境日期</dt>
        <dd>
          <input type="text" class="text" name="jcbOrderTime" id="jcbOrderTime" value="<?php echo $output['address_info']['waybill_info']['jcbOrderTime'];?>"/><i class="icon-calendar"></i>
        </dd>
      </dl>
      <dl>
        <dt class="required">进/出境口岸(关)</dt>
        <dd>
          <!--
          <input type="text" class="text" name="jcbOrderPort" id="jcbOrderPort" value="<?php echo $output['address_info']['waybill_info']['jcbOrderPort'];?>"/>
          -->
          <select name="jcbOrderPort" id="jcbOrderPort">
            <option value ="4604" <?php if ($output['address_info']['waybill_info']['jcbOrderPort']==4604){?>selected="selected"<?php }?>>4604（郑州机办）</option>
          </select>
        </dd>
      </dl>
      <dl>
        <dt class="required">进/出境口岸(检)</dt>
        <dd>
          <!--
          <input type="text" class="text" name="jcbOrderPortInsp" id="jcbOrderPortInsp" value="<?php echo $output['address_info']['waybill_info']['jcbOrderPortInsp'];?>"/>
            -->
            <select name="jcbOrderPortInsp" id="jcbOrderPortInsp">
            <option value ="410010" <?php if ($output['address_info']['waybill_info']['jcbOrderPortInsp']==410010){?>selected="selected"<?php }?>>410010（河南局郑州机场口岸）</option>
          </select>
        </dd>
      </dl>
      <!--运输方式-->
      <dl>
        <dt class="required">运输方式</dt>
        <dd>
          <select name="transType" id="transType">
          <?php foreach ($output['kuajing_trans_type'] as $key => $value) {?>
          <option value='<?php echo $value['trans_type_id']?>' <?php if ($output['address_info']['waybill_info']['transType'] == $value['trans_type_id']) {?>selected="selected"<?php }?>><?echo $value['trans_type_name']?></option>      
          <?php } ?>
          </select>
        </dd>
      </dl>
      <!--运输工具-->
      <dl>
        <dt class="required">运输工具</dt>
        <dd>
          <select name="transTool" id="transTool">
          <?php foreach ($output['kuajing_trans_tool'] as $key => $value) {?>
          <option value='<?php echo $value['tool_id']?>' <?php if ($output['address_info']['waybill_info']['transTool'] == $value['tool_id']) {?>selected="selected"<?php }?>><?echo $value['tool_name']?></option>      
          <?php } ?>
          </select>
        </dd>
      </dl>
      <!--运单号-->
      <dl>
        <dt class="required">运单号</dt>
        <dd>
          <input type="text" class="text" name="logisticsNo" id="logisticsNo" value="<?php echo $output['address_info']['waybill_info']['logisticsNo'];?>" placeholder="生成清单报文前需要填写"/>
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
            "<?php echo urlShop('store_deliver', 'STO_parameter_save');?>", 
            {
                order_id: <?php echo $_GET['order_id'];?>,
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
                if(data == 'true') {
                    $('#transTool').val(reciver_transTool);
                    $('#transType').val(reciver_transType);
                    $('#voyageNo').val(reciver_voyageNo);
                    $('#totalLogisticsNo').val(reciver_totalLogisticsNo);
                    $('#logisticsNo').val(reciver_logisticsNo);
                    $('#jcbOrderTime').val(reciver_jcbOrderTime);
                    $('#jcbOrderPort').val(reciver_jcbOrderPort);
                    $('#jcbOrderPortInsp').val(reciver_jcbOrderPortInsp);
                    var content = reciver_totalLogisticsNo + '&nbsp' + reciver_jcbOrderTime + '&nbsp;' + reciver_jcbOrderPort + '&nbsp;' + reciver_jcbOrderPortInsp + '&nbsp;运单号：'+reciver_logisticsNo;
                    $('#waybill_span').html(content);
                    DialogManager.close('edit_waybill_info');
                } else {
                    showError('保存失败');
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
