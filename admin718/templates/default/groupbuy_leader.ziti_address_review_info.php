<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>自提点审核</h3>
      <ul class="tab-base">
        <li><a href="index.php?act=groupbuy_leader&op=ziti_address_review" ><span>待审核列表</span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span>自提点详情</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="groupbuy_leader_form" enctype="multipart/form-data" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="address_id" value="<?php echo $output['ziti_address_info']['address_id'];?>" />
    <input type="hidden" name="state" value="" />
    <table class="table tb-type2">
      <tbody>
        <tr>
          <td class="required"><label for="seller_name">自提点名称:</label></td>
          <td class="vatop rowform" style="width: 90%;"><?php echo $output['ziti_address_info']['seller_name'];?></td>
        </tr>
        
        <tr>
          <td class="required"><label for="area_info">自提点地址:</label></td>
          <td id="ziti_area_info"><?php echo $output['ziti_address_info']['area_info'];?></td>
        </tr>

        <tr>
          <td class="required"><label for="address">街道地址:</label></td>
          <td class="vatop rowform"><?php echo $output['ziti_address_info']['address'];?></td>
        </tr>

        <tr>
          <td class="required"><label for="phone_num">电话:</label></td>
          <td class="vatop rowform"><?php echo $output['ziti_address_info']['phone_num'];?></td>
        </tr>

        <tr>
          <td class="required">申请时间:</td>
          <td class="vatop rowform">
            <strong><?php echo date('Y-m-d H:i:s',$output['ziti_address_info']['add_time']);?></strong>
          </td>
        </tr>

        <tr>
          <td class="required">营业执照:</td>
          <td class="vatop rowform">
            <?php echo $output['ziti_address_info']['have_license']?'有':'无'?>
          </td>
        </tr>

        <tr>
          <td class="required">自提点照片:</td>
          <td class="vatop rowform">
            <a href="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_ZITI.'/'.$output['ziti_address_info']['groupbuy_leader_id'].'/'.$output['ziti_address_info']['ziti_photo'];?>" class="nyroModal" rel="gal">
            <img style="max-width:200px;max-height:200px" class="show_image" src="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_ZITI.'/'.$output['ziti_address_info']['groupbuy_leader_id'].'/'.$output['ziti_address_info']['ziti_photo'];?>"></a>
          </td>
        </tr>

        <tr>
          <td class="required">身份证正面照片:</td>
          <td class="vatop rowform">        
            <a href="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_GLID_FRONT.'/'.$output['ziti_address_info']['groupbuy_leader_id'].'/'.$output['ziti_address_info']['id_photo_front'];?>" class="nyroModal" rel="gal">
            <img style="max-width:200px;max-height:200px" class="show_image" src="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_GLID_FRONT.'/'.$output['ziti_address_info']['groupbuy_leader_id'].'/'.$output['ziti_address_info']['id_photo_front'];?>"></a>
          </td>
        </tr>

        <tr>
          <td class="required">身份证反面照片:</td>
          <td class="vatop rowform">
            <a href="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_GLID_BACK.'/'.$output['ziti_address_info']['groupbuy_leader_id'].'/'.$output['ziti_address_info']['id_photo_back'];?>" class="nyroModal" rel="gal">
            <img style="max-width:200px;max-height:200px" class="show_image" src="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_GLID_BACK.'/'.$output['ziti_address_info']['groupbuy_leader_id'].'/'.$output['ziti_address_info']['id_photo_back'];?>"></a>
          </td>
        </tr>

        <tr>
          <td class="required"><label for="review_msg">审核意见:</label></td>
          <td class="vatop rowform"><textarea id="review_msg" name="review_msg" class="tarea" value=""><?php echo $output['ziti_address_info']['review_msg'];?></textarea></td>
        </tr>

      <tfoot>
        <tr class="tfoot">
          <td colspan="15">
            <a href="JavaScript:void(0);" class="btn" id="yBtn"><span>同意</span></a>
            <a href="JavaScript:void(0);" class="btn" id="nBtn"><span>拒绝</span></a>
          </td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/custom.min.js" charset="utf-8"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/styles/nyroModal.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/ajaxfileupload/ajaxfileupload.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.min.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script> 
<script type="text/javascript">
$(function(){
  $('.nyroModal').nyroModal();
});
$("#yBtn").click(function(){
  $('input[name="state"]').val(1);
  $("#groupbuy_leader_form").submit();
});
$("#nBtn").click(function(){
  $('input[name="state"]').val(4);
  $("#groupbuy_leader_form").submit();
});
</script> 
