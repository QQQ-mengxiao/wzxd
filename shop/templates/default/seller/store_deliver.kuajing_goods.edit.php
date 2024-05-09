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
        <dt class="required">法定数量(单件)</dt>
        <dd>
          <input type="text" class="text" name="transTool" id="transTool" value="<?php echo $output['address_info']['qty1'];?>"/>
        </dd>
      </dl>
       <dl>
        <dt class="required">法定第二数量(单件)</dt>
        <dd>
          <input type="text" class="text" name="transType" id="transType" value="<?php echo $output['address_info']['qty2'];?>"/>
        </dd>
      </dl>
      </dl>
       <dl>
        <dt class="required">unit计量单位(代码、单件)</dt>
        <dd>
          <input type="text" class="text" name="transType1" id="transType1" value="<?php echo $output['address_info']['unit'];?>"/>
        </dd>
      </dl>
      <dl>
        <dt class="required">申报计量单位代码</dt>
        <dd>
          <input type="text" class="text" name="transType7" id="transType7" value="<?php echo $output['address_info']['unitinsp'];?>"/>
        </dd>
      </dl>
       <dl>
        <dt class="required">法定计量单位</dt>
        <dd>
          <input type="text" class="text" name="transType2" id="transType2" value="<?php echo $output['address_info']['unit1'];?>"/>
        </dd>
      </dl>
      </dl>
       <dl>
        <dt class="required">法定第二计量单位</dt>
        <dd>
          <input type="text" class="text" name="transType3" id="transType3" value="<?php echo $output['address_info']['unit2'];?>"/>
        </dd>               
      </dl>
       <dl>
        <dt class="required" style="right:10px">货号</dt>
        <dd>
          <input type="text" class="text" name="transType4" id="transType4" value="<?php echo $output['address_info']['itemNo'];?>"/>
        </dd>
      </dl>
       <dl>
        <dt class="required">备案号</dt>
        <dd>
          <input type="text" class="text" name="transType5" id="transType5" style="width:160px" value="<?php echo $output['address_info']['goodidinsp'];?>"/>
        </dd>
      </dl>
       <dl>
        <dt class="required">HS编码</dt>
        <dd>
          <input type="text" class="text" name="transType6" id="transType6" value="<?php echo $output['address_info']['ciqbarcode'];?>"/>
        </dd>
      </dl>
      <div class="bottom"><label class="submit-border"><a href="javascript:void(0);" id="submit" class="submit" style="padding-bottom: 4px;">保存</a></label></div>
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
            var reciver_transType1 = $('#transType1').val();
            var reciver_transType2 = $('#transType2').val();
            var reciver_transType3 = $('#transType3').val();
            var reciver_transType4 = $('#transType4').val();
            var reciver_transType5 = $('#transType5').val();
            var reciver_transType6 = $('#transType6').val();
            var reciver_transType7 = $('#transType7').val();
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
                reciver_transType1: reciver_transType1,
                reciver_transType2: reciver_transType2,
                reciver_transType3: reciver_transType3,
                reciver_transType4: reciver_transType4,
                reciver_transType5: reciver_transType5,
                reciver_transType6: reciver_transType6,
                reciver_transType7: reciver_transType7,
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
                    $('#transType1').val(reciver_transType1);
                    $('#transType2').val(reciver_transType2);
                    $('#transType3').val(reciver_transType3);
                    $('#transType4').val(reciver_transType4);
                    $('#transType5').val(reciver_transType5);
                    $('#transType6').val(reciver_transType6);
                    $('#transType7').val(reciver_transType7);
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
