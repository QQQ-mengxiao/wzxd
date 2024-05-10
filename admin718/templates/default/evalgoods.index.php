<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_goods_evaluate']; ?></h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['admin_evaluate_list'];?></span></a></li>
        <li><a href="index.php?act=evaluate&op=evalstore_list" ><span><?php echo $lang['admin_evalstore_list'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch">
    <input type="hidden" name="act" value="evaluate" />
    <input type="hidden" name="op" value="evalgoods_list" />
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th><label for="goods_name"><?php echo $lang['admin_evaluate_goodsname']?></label></th>
          <td><input class="txt" type="text" name="goods_name" id="goods_name" value="<?php echo $_GET['goods_name'];?>" /></td>
           <!-- 20190830slkadd -->
          <th><label for="from_name"><?php echo $lang['admin_evaluate_frommembername'];?></label></th>
          <td><input class="txt" type="text" name="from_name" id="from_name" value="<?php echo $_GET['from_name'];?>" /></td>
          <!-- 20190830slkadd -->
          <td><?php echo $lang['admin_evaluate_addtime']; ?></td>
          <td><input class="txt date" type="text" name="stime" id="stime" value="<?php echo $_GET['stime'];?>" />
            ~
            <input class="txt date" type="text" name="etime" id="etime" value="<?php echo $_GET['etime'];?>" /></td>
        </tr>

        <tr>
          <th><label>是否带图</label></th>
          <td>
           <select name="is_photo" class="querySelect">
              <option value=""><?php echo $lang['nc_please_choose'];?></option>
              <option value="1" <?php if($_GET['is_photo'] == '1'){?>selected<?php }?>>是</option>
              <option value="0" <?php if($_GET['is_photo'] == '0'){?>selected<?php }?>>否</option>
           </select>
          </td>
          <th><label>评价等级</label></th>
          <td>
           <select name="geval_scores" class="querySelect">
              <option value=""><?php echo $lang['nc_please_choose'];?></option>
              <option value="5" <?php if($_GET['geval_scores'] == '5'){?>selected<?php }?>>好评</option>
              <option value="3" <?php if($_GET['geval_scores'] == '3'){?>selected<?php }?>>中评</option>
              <option value="1" <?php if($_GET['geval_scores'] == '1'){?>selected<?php }?>>差评</option>
           </select>
          </td>
          <th><label>是否发券</label></th>
          <td>
           <select name="is_voucher" class="querySelect">
              <option value=""><?php echo $lang['nc_please_choose'];?></option>
              <option value="0" <?php if($_GET['is_voucher'] == '0'){?>selected<?php }?>>否</option>
              <option value="1" <?php if($_GET['is_voucher'] == '1'){?>selected<?php }?>>是</option>
           </select>
          </td>
          <td><a href="javascript:document.formSearch.submit();" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a></td>
        </tr>

      </tbody>
    </table>
  </form>
  <table class="table tb-type2" id="prompt">
    <tbody>
      <tr class="space odd">
        <th colspan="12"><div class="title">
            <h5><?php echo $lang['nc_prompts'];?></h5>
            <span class="arrow"></span></div></th>
      </tr>
      <tr>
        <td><ul>
            <li><?php echo $lang['admin_evaluate_help1'];?></li>
            <li><?php echo $lang['admin_evaluate_help2'];?></li>
          </ul></td>
      </tr>
    </tbody>
  </table>
    <div style="text-align:right;">
      <a class="btns" href="javascript:void(0);" nctype="voucher_all"><span>发券</span></a>
      <a class="btns" target="_blank" href="index.php?<?php echo $_SERVER['QUERY_STRING'];?>&op=export_step1"><span>导出Excel</span></a>
    </div>
  <table class="table tb-type2" id='table_id'>
    <thead>
      <tr class="thead">
        <td><input type="checkbox" class="checkall" id="checkallBottom"></td>
        <th class="w300"><?php echo $lang['admin_evaluate_goodsname'];?> </th>
        <th><?php echo $lang['admin_evaluate_buyerdesc']; ?></th>
        <th class="w108 align-center"><?php echo '订单号';?> </th>
        <th class="w108 align-center"><?php echo $lang['admin_evaluate_frommembername'];?> </th>
        <th class="w60 align-center">是否发券</th>
        <th class="w72 align-center"><?php echo $lang['nc_handle'];?></th>
      </tr>
    </thead>
    <tbody>
    <?php if(!empty($output['evalgoods_list'])){?>
      <?php foreach($output['evalgoods_list'] as $v){?>
      <tr class="hover">
      <th><input type="checkbox" name="id[]" value="<?php echo $v['geval_id'];?>" class="checkitem"></th>
        <td><a href="<?php echo urlShop('goods','index',array('goods_id'=>$v['geval_goodsid']));?>" target="_blank"><?php echo $v['geval_goodsname'];?></a></td>
        <td class="evaluation"><div>商品评分：<span class="raty" data-score="<?php echo $v['geval_scores'];?>"></span><time>[<?php echo @date('Y-m-d',$v['geval_addtime']);?>]</time></div>
          <div>评价内容：<?php echo $v['geval_content'];?></div>
          
          <?php if(!empty($v['geval_image'])) {?>
          <div>晒单图片：
            <ul class="evaluation-pic-list">
              <?php $image_array = explode(',', $v['geval_image']);?>
              <?php foreach ($image_array as $value) { ?>
              <!-- <li><a nctype="nyroModal"  href="<?php echo snsThumb($value, 1024);?>"> <img src="<?php echo snsThumb($value);?>"> </a></li> -->
              <li><a nctype="nyroModal"  href="<?php echo $value;?>"> <img src="<?php echo $value;?>"> </a></li>
              <?php } ?>
            </ul>
          </div>
          <?php } ?>
          
          <?php if(!empty($v['geval_explain'])){?>
          <div id="explain_div_<?php echo $v['geval_id'];?>"> <span style="color:#996600;padding:5px 0px;">[<?php echo $lang['admin_evaluate_explain']; ?>]<?php echo $v['geval_explain'];?></span> </div>
          <?php }?></td>
          <td class="align-center"><?php echo $v['geval_orderno'];?></td>
        <td class="align-center"><?php echo $v['geval_frommembername'];?></td>
        <td class="align-center"><?php echo $v['is_voucher']==0?'未发':'已发';?></td>
        <td class="align-center">
          <!-- <a nctype="btn_voucher" href="javascript:void(0)" data-geval-id="<?php echo $v['geval_id']; ?>">发券</a> -->
          <a href="javascript:void(0);" onclick="voucher(<?php echo $v['geval_id'];?>);">发券</a>
          <b>|</b>
          <a nctype="btn_del" href="javascript:void(0)" data-geval-id="<?php echo $v['geval_id']; ?>"><?php echo $lang['nc_del']; ?></a>
        </td>
      </tr>
      <?php }?>
      <?php }else{?>
      <tr class="no_data">
        <td colspan="15"><?php echo $lang['nc_no_record'];?></td>
      </tr>
      <?php }?>
    <?php if(!empty($output['evalgoods_list'])){?>
    <tfoot>
      <tr class="tfoot">
        <td colspan="15" id="dataFuncs"><div class="pagination"><?php echo $output['show_page'];?></div></td>
      </tr>
    </tfoot>
    <?php } ?>
  </table>
</div>
<form id="submit_form" action="<?php echo urlAdmin('evaluate', 'evalgoods_del');?>" method="post">
  <input id="geval_id" name="geval_id" type="hidden">
</form>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.raty/jquery.raty.min.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/custom.min.js" charset="utf-8"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.poshytip.min.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js" charset="utf-8"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/styles/nyroModal.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script type="text/javascript">

  $(function(){
    $('#ncsubmit').click(function(){
      $('input[name="op"]').val('evalgoods_list');$('#formSearch').submit();
    }); 
});
  
  function voucher(geval_id) {
    _uri = "<?php echo ADMIN_SITE_URL;?>/index.php?act=evaluate&op=ajaxVoucher&geval_id=" + geval_id;
    CUR_DIALOG = ajax_form('ajaxVoucher', '发券', _uri, 350);
  }

  // 违规下架批量处理
  $('a[nctype="voucher_all"]').click(function(){
      str = getId();
      // alert(str);
      if (str) {
        voucher(str);
      }
  });

  // 获得选中ID
  function getId() {
      var str = '';
      $('#table_id').find('input[name="id[]"]:checked').each(function(){
          id = parseInt($(this).val());
          if (!isNaN(id)) {
              str += id + ',';
          }
      });
      if (str == '') {
          return false;
      }
      str = str.substr(0, (str.length - 1));
      return str;
  }

  $(document).ready(function(){
      $('#stime').datepicker({dateFormat: 'yy-mm-dd'});
      $('#etime').datepicker({dateFormat: 'yy-mm-dd'});

      $('.raty').raty({
          path: "<?php echo RESOURCE_SITE_URL;?>/js/jquery.raty/img",
          readOnly: true,
          score: function() {
            return $(this).attr('data-score');
          }
      });

      $('a[nctype="nyroModal"]').nyroModal();

      $('[nctype="btn_del"]').on('click', function() {
          if(confirm("<?php echo $lang['nc_ensure_del'];?>")) {
              var geval_id = $(this).attr('data-geval-id');
              $('#geval_id').val(geval_id);
              $('#submit_form').submit();
          }
      });
  });
</script> 
