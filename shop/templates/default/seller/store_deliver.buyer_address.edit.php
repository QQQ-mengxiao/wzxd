<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="eject_con">
  <div class="adds">
    <div id="warning"></div>
    <form method="post" action="index.php?act=store_deliver&op=buyer_address&order_id=<?php echo $_GET['order_id'];?>" id="address_form" target="_parent">
      <input type="hidden" name="form_submit" value="ok" />
      <dl>
        <dt class="required"><?php echo $lang['member_address_receiver_name'].$lang['nc_colon'];?></dt>
        <dd>
          <input type="text" class="text" name="new_reciver_name" id="new_reciver_name" value="<?php echo $output['address_info']['reciver_name'];?>"/>
        </dd>
      </dl>
      <dl>
        <dt class="required">地区<?php echo $lang['nc_colon'];?></dt>
        <dd>
          <input type="text" class="text" name="new_area" id="new_area" value="<?php echo $output['address_info']['reciver_info']['area'];?>"/>
        </dd>
      </dl>
      <dl>
        <dt class="required"><?php echo $lang['member_address_address'].$lang['nc_colon'];?></dt>
        <dd>
          <input type="text" class="text" name="new_street" id="new_street" value="<?php echo $output['address_info']['reciver_info']['street'];?>"/>
        </dd>
      </dl>
      <dl>
        <dt class="required">手机<?php echo $lang['nc_colon'];?></dt>
        <dd>
          <input type="text" class="text" name="new_mob_phone" id="new_mob_phone" value="<?php echo $output['address_info']['reciver_info']['mob_phone'];?>"/>
        </dd>
      </dl>
      <dl>
        <dt class="required">身份证号<?php echo $lang['nc_colon'];?></dt>
        <dd>
          <input type="text" class="text" name="new_id_card" id="new_id_card" value="<?php echo $output['address_info']['reciver_info']['id_card'];?>"/>
        </dd>
      </dl>
      <!--
      <dl>
        <dt class="required">座机<?php echo $lang['nc_colon'];?></dt>
        <dd>
          <input type="text" class="text" name="new_tel_phone" id="new_tel_phone" value="<?php echo $output['address_info']['reciver_info']['tel_phone'];?>"/>
        </dd>
      </dl>
      -->
      <div class="bottom"><label class="submit-border"><a href="javascript:void(0);" id="submit" class="submit"><?php echo $lang['nc_common_button_save'];?></a></label></div>
    </form>
  </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
    $('#address_form').validate({
        rules : {
            new_reciver_name : {
                required : true
            },
            new_area : {
                required : true
            },
            new_mob_phone: {
                required : true
            }//,
            //new_id_card: {
                //required : true
            //}
        },
        messages : {
            new_reciver_name: {
                required : '<i class="icon-exclamation-sign"></i>收货人不能为空'
            },
            new_area: {
                required : '<i class="icon-exclamation-sign"></i>地区不能为空'
            },
            new_mob_phone: {
                required : '<i class="icon-exclamation-sign"></i>手机不能为空'
            }//,
            //new_id_card: {
                //required : '<i class="icon-exclamation-sign"></i>身份证号不能为空'
            //}
        }
    });
	$('#submit').on('click',function(){
		if ($('#address_form').valid()) {
            var reciver_id_card = $('#new_id_card').val();
            var reciver_mob_phone = $('#new_mob_phone').val();
            var reciver_tel_phone = $('#new_tel_phone').val();
            var reciver_area = $('#new_area').val();
            var reciver_street = $('#new_street').val();
            var reciver_name = $('#new_reciver_name').val();
            $.post(
            "<?php echo urlShop('store_deliver', 'buyer_address_save');?>", 
            {
                order_id: <?php echo $_GET['order_id'];?>,
                reciver_name: reciver_name,
                reciver_area: reciver_area,
                reciver_street: reciver_street,
                reciver_mob_phone: reciver_mob_phone,
                reciver_tel_phone: reciver_tel_phone,
                reciver_id_card: reciver_id_card
            })
            .done(function(data) {
                if(data == 'true') {
                  $('#reciver_id_card').val(reciver_id_card);
                    $('#reciver_mob_phone').val(reciver_mob_phone);
                    $('#reciver_tel_phone').val(reciver_tel_phone);
                    $('#reciver_area').val(reciver_area);
                    $('#reciver_street').val(reciver_street);
                    $('#reciver_name').val(reciver_name);
                    var content = reciver_name + '&nbsp' + reciver_mob_phone  + '&nbsp;' + reciver_area + '&nbsp;' + reciver_street + '&nbsp;' + reciver_id_card;
                    $('#buyer_address_span').html(content);
                    DialogManager.close('edit_buyer_address');
                } else {
                    showError('修改失败');
                }
            });
		}
	});
//$('#new_id_card').val($('#reciver_id_card').val());
	$('#new_mob_phone').val($('#reciver_mob_phone').val());
	$('#new_tel_phone').val($('#reciver_tel_phone').val());
	$('#new_area').val($('#reciver_area').val());
	$('#new_street').val($('#reciver_street').val());
	$('#new_reciver_name').val($('#reciver_name').val());	
});
</script>
