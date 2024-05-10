<?php defined('In718Shop') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<div class="ncsc-form-default">
    <?php if(empty($output['xianshi_info'])) { ?>
    <form id="add_form" action="index.php?act=store_promotion_xianshi_discount&op=xianshi_save" method="post">
    <?php } else { ?>
    <form id="add_form" action="index.php?act=store_promotion_xianshi_discount&op=xianshi_edit_save" method="post">
        <input type="hidden" name="xianshi_id" value="<?php echo $output['xianshi_info']['xianshi_id'];?>">
    <?php } ?>
    <dl>
      <dt><i class="required">*</i><?php echo $lang['xianshi_name'];?><?php echo $lang['nc_colon'];?></dt>
      <dd>
          <input id="xianshi_name" name="xianshi_name" type="text"  maxlength="25" class="text w400" value="<?php echo empty($output['xianshi_info'])?'':$output['xianshi_info']['xianshi_name'];?>"/>
          <span></span>
        <p class="hint"><?php echo $lang['xianshi_name_explain'];?></p>
      </dd>
    </dl>
    <dl>
      <dt>活动标题<?php echo $lang['nc_colon'];?></dt>
      <dd>
          <input id="xianshi_title" name="xianshi_title" type="text"  maxlength="10" class="text w200" value="<?php echo empty($output['xianshi_info'])?'':$output['xianshi_info']['xianshi_title'];?>"/>
          <span></span>
        <p class="hint">活动标题是商家对限时折扣活动的别名操作，请使用例如“新品打折”、“月末折扣”类短语表现，最多可输入10个字符；
            <br>非必填选项，留空商品优惠价格前将默认显示“限时折扣”字样。</p>
      </dd>
    </dl>
    <dl>
      <dt>活动描述<?php echo $lang['nc_colon'];?></dt>
      <dd>
          <input id="xianshi_explain" name="xianshi_explain" type="text"  maxlength="30" class="text w400" value="<?php echo empty($output['xianshi_info'])?'':$output['xianshi_info']['xianshi_explain'];?>"/>
          <span></span>
        <p class="hint">活动描述是商家对限时折扣活动的补充说明文字，在商品详情页-优惠信息位置显示；
            <br>非必填选项，最多可输入30个字符。</p>
      </dd>
    </dl>
    <dl>
      <dt>佣金比例<?php echo $lang['nc_colon'];?></dt>
      <dd>
          <input id="commis_rate" name="commis_rate" type="text"  maxlength="10" class="text w70" value="<?php echo empty($output['xianshi_info'])?'':$output['xianshi_info']['commis_rate'];?>"/><em class="add-on"><i><B>%</B></i></em>
          <span></span>
        <p class="hint">0为不分佣</p>
      </dd>
    </dl>
<!--    --><?php //if(empty($output['xianshi_info'])) { ?>
    <dl>
      <dt><i class="required">*</i><?php echo $lang['start_time'];?><?php echo $lang['nc_colon'];?></dt>
      <dd>
          <input id="start_time" name="start_time" type="text" class="text w130" value="<?php echo $output['xianshi_info']['start_time']?date('Y-m-d H:i',$output['xianshi_info']['start_time']):'';?>"><em class="add-on"><i class="icon-calendar"></i></em><span></span>
        <p class="hint">
<?php //if (!$output['isOwnShop'] && $output['current_xianshi_quota']['start_time'] > 1) { ?>
<!--        --><?php //echo sprintf($lang['xianshi_add_start_time_explain'],date('Y-m-d H:i',$output['current_xianshi_quota']['start_time']));?>
<?php //} ?>
        </p>
      </dd>
    </dl>
    <dl>
      <dt><i class="required">*</i><?php echo $lang['end_time'];?><?php echo $lang['nc_colon'];?></dt>
      <dd>
          <input id="end_time" name="end_time" type="text" class="text w130" value="<?php echo $output['xianshi_info']['end_time']?date('Y-m-d H:i',$output['xianshi_info']['end_time']):'';?>"><em class="add-on"><i class="icon-calendar"></i></em><span></span>
        <p class="hint">
<?php if (!$output['isOwnShop']) { ?>
        <?php echo sprintf($lang['xianshi_add_end_time_explain'],date('Y-m-d H:i',$output['current_xianshi_quota']['end_time']));?>
<?php } ?>
        </p>
      </dd>
    </dl>
<!--    --><?php //} ?>
    <dl>
      <dt><i class="required">*</i>折扣率<?php echo $lang['nc_colon'];?></dt>
            <dd>
                <input id="discount" name="discount" type="text" class="text w130" value="<?php echo empty($output['xianshi_info'])?'0':$output['xianshi_info']['discount'];?>"/><span></span>
                <p class="hint">商品折扣率，请填入1-9之间的数字，可以为小数，填0为不打折。</p>
            </dd>
        </dl>
    <div class="bottom">
      <label class="submit-border"><input id="submit_button" type="submit" class="submit" value="<?php echo $lang['nc_submit'];?>"></label>
    </div>
  </form>
</div>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css"  />
<script>
$(document).ready(function(){
    $('#start_time').datetimepicker({
        controlType: 'select'
    });

    $('#end_time').datetimepicker({
        controlType: 'select'
    });

    jQuery.validator.methods.greaterThanDate = function(value, element, param) {
        var date1 = new Date(Date.parse(param.replace(/-/g, "/")));
        var date2 = new Date(Date.parse(value.replace(/-/g, "/")));
        return date1 < date2;
    };
    jQuery.validator.methods.lessThanDate = function(value, element, param) {
        var date1 = new Date(Date.parse(param.replace(/-/g, "/")));
        var date2 = new Date(Date.parse(value.replace(/-/g, "/")));
        return date1 > date2;
    };
    jQuery.validator.methods.greaterThanStartDate = function(value, element) {
        var start_date = $("#start_time").val();
        var date1 = new Date(Date.parse(start_date.replace(/-/g, "/")));
        var date2 = new Date(Date.parse(value.replace(/-/g, "/")));
        return date1 < date2;
    };

    //页面输入内容验证
    $("#add_form").validate({
        errorPlacement: function(error, element){
            var error_td = element.parent('dd').children('span');
            error_td.append(error);
        },
        onfocusout: false,
    	submitHandler:function(form){
    		ajaxpost('add_form', '', '', 'onerror');
    	},
        rules : {
            xianshi_name : {
                required : true
            },
            start_time : {
                required : true,
                //greaterThanDate : '<?php //echo date('Y-m-d H:i',$output['current_xianshi_quota']['start_time']);?>//'
            },
            end_time : {
                required : true,
<?php if (!$output['isOwnShop']) { ?>
                //lessThanDate : '<?php //echo date('Y-m-d H:i',$output['current_xianshi_quota']['end_time']);?>//',
                greaterThanDate : '<?php echo date('Y-m-d H:i',time());?>',
<?php } ?>
                greaterThanStartDate : true
            },
            lower_limit: {
                required: true,
                digits: true,
                min: 1
            }
        },
        messages : {
            xianshi_name : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['xianshi_name_error'];?>'
            },
            start_time : {
            required : '<i class="icon-exclamation-sign"></i><?php echo sprintf($lang['xianshi_add_start_time_explain'],date('Y-m-d H:i',$output['current_xianshi_quota']['start_time']));?>',
                greaterThanDate : '<i class="icon-exclamation-sign"></i><?php echo sprintf($lang['xianshi_add_start_time_explain'],date('Y-m-d H:i',$output['current_xianshi_quota']['start_time']));?>'
            },
            end_time : {
            required : '<i class="icon-exclamation-sign"></i><?php echo sprintf($lang['xianshi_add_end_time_explain'],date('Y-m-d H:i',time()));?>',
<?php if (!$output['isOwnShop']) { ?>
                greaterThanDate : '<i class="icon-exclamation-sign"></i><?php echo sprintf($lang['xianshi_add_end_time_explain'],date('Y-m-d H:i',time()));?>',
<?php } ?>
                greaterThanStartDate : '<i class="icon-exclamation-sign"></i><?php echo $lang['greater_than_start_time'];?>'
            },
            lower_limit: {
                required : '<i class="icon-exclamation-sign"></i>购买下限不能为空',
                digits: '<i class="icon-exclamation-sign"></i>购买下限必须为数字',
                min: '<i class="icon-exclamation-sign"></i>购买下限不能小于1'
            }
        }
    });
});
</script>
