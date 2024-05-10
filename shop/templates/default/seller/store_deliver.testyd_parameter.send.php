<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="eject_con">
    <div id="warning" class="alert alert-error"></div>
  <form method="post" action="index.php?act=store_deliver&op=testyd_parameter_send&order_id=<?php echo $_GET['order_id'];?>" id="test_form" target="_parent">
        <dl>
      </dl>
       <dl>
        <dt ><i class="required">*</i>上游公司运单号</dt>
        <dd class="text">
          <input type="text" class="text" name="BillID" id="BillID" value="<?php echo $output['address_info']['waybill_info']['logisticsNo'];?>"/>
        </dd>
        </dl>
        <dl>
        <dt><i class="required">*</i>下游公司订单号</dt>
        <dd class="text">
          <input type="text" class="text" name="OrderID" id="OrderID" value="<?php echo $output['order_info']['order_sn']; ?>"/>
        </dd>
        </dl>
        <dl>
      <dt><i class="required">*</i>事件发生地</dt>
<!--      <dd >-->
<!--        <div>-->
<!--          <input type="hidden" name="DepName" id="DepName" value="--><?php //echo $_GET['DepName'];?><!--"/>-->
<!--        </div>-->
            <dd class="text">
                <input type="text" class="text" name="DepName" id="DepName" value="<?php echo $_GET['DepName'];?>"/>
            </dd>
      </dd>
      </dl>
       <dl>
        <dt><i class="required">*</i>状态代码</dt>
        <dd >
          <select name="StatusCode" id="StatusCode">
        <option value=P767 <?php if ($_GET['StatusCode'] == P767) {?>selected="selected"<?php }?>>P767</option>
        <option value=P760 <?php if ($_GET['StatusCode'] == P760) {?>selected="selected"<?php }?>>P760</option>
        <option value=P765 <?php if ($_GET['StatusCode'] == P765) {?>selected="selected"<?php }?>>P765</option>
        <option value=P769 <?php if ($_GET['StatusCode'] == P769) {?>selected="selected"<?php }?>>P769</option>
        <option value=P762 <?php if ($_GET['StatusCode'] == P762) {?>selected="selected"<?php }?>>P762</option>
        <option value=P764 <?php if ($_GET['StatusCode'] == P764) {?>selected="selected"<?php }?>>P764</option>
        <option value=P771 <?php if ($_GET['StatusCode'] == P771) {?>selected="selected"<?php }?>>P771</option>
        <option value=P773 <?php if ($_GET['StatusCode'] == P773) {?>selected="selected"<?php }?>>P773</option>
          </select>

          <select class="StatusDesc" name="StatusDesc">
        <option>海外派件中</option>
        </select>
        <select class="StatusDesc">
        <option>海外收入</option>
        </select>
        <select class="StatusDesc">
        <option>海外清关完成</option>
        </select>
        <select class="StatusDesc">
        <option>海外签收</option>
        </select>
        <select class="StatusDesc">
        <option>海外发运</option>
        </select>
        <select class="StatusDesc">
        <option>海外清关中</option>
        </select>
        <select class="StatusDesc">
        <option>航班已起飞</option>
        </select>
        <select class="StatusDesc">
        <option>航班抵达保税区</option>
        </select>
        </dd>
      </dl>
      <dl>
        <dt class="required"  >站点类型</dt>
        <dd class="text">
          <select name="FacilityType" id="FacilityType">
        <option value='网点' <?php if ($_GET['FacilityType'] == '网点') {?>selected="selected"<?php }?>>网点</option>
        <option value="中转中心" <?php if ($_GET['FacilityType'] == "中转中心") {?>selected="selected"<?php }?>>中转中心</option>
        <option value="分拨中心" <?php if ($_GET['FacilityType'] == "分拨中心") {?>selected="selected"<?php }?>>分拨中心</option>
          </select>
        </dd>
        </dl>
        <dl>
        <dt class="required"  >联系人</dt>
        <dd class="text">
          <input type="text" class="text" name="Contacter" id="Contacter" value="<?php echo $_GET['Contacter'];?>"/>
        </dd>
        <dt class="required">联系方式</dt>
        <dd class="text">
          <input type="text" class="text" name="ContactInfo" id="ContactInfo" value="<?php echo $_GET['ContactInfo'];?>"/>
        </dd>
        </dl>
        <div class="bottom">
      <label class="submit-border"><input type="submit" class="submit" value="推送" /></label>
    </div>
    </form>
</div>
<script type="text/javascript">

var SITEURL = "<?php echo SHOP_SITE_URL; ?>";
$(document).ready(function(){
  //$("#DepName").nc_region();

   $("#StatusCode").change(function(){
      $("#StatusCode option").each(function(i,o){
          if($(this).attr("selected"))
         {
             $(".StatusDesc").hide();
             $(".StatusDesc").eq(i).show();
          }
      });
  });
  $("#StatusCode").change();

$('#test_form').validate({
  errorLabelContainer: $('#warning'),
    invalidHandler: function(form, validator) {
       var errors = validator.numberOfInvalids();
       if(errors)
       {
           $('#warning').show();
       }
       else
       {
           $('#warning').hide();
       }
    },
    rules : {
        BillID : {
            required : true
        },            
        OrderID : {
            required : true
        },
        DepName : {
            required : true
        },
        StatusCode : {
            required : true
        },
       
      
    },
    messages : {
        BillID: {
            required : '<i class="icon-exclamation-sign"></i>运单号不能为空'
        },
        OrderID: {
            required : '<i class="icon-exclamation-sign"></i>订单号不能为空'
        },
        DepName: {
            required : '<i class="icon-exclamation-sign"></i>事件发生地不能为空'
        },
        StatusCode: {
            required : '<i class="icon-exclamation-sign"></i>状态代码与描述不能为空'
        }
       
    }
});

});
</script>

