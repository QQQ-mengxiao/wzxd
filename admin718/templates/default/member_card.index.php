<?php defined('In718Shop') or exit('Access Invalid!');?>
<style type="text/css">
/*#formSearch{
  position: absolute!important;
  top:50px;
  z-index: 999;
}*/
#search-export{
  position: absolute!important;
  top:50px;
  z-index: 999;
}
</style>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>一卡通管理</h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span>管理</span></a></li>
        <li><a href="index.php?act=member_card&op=member_add" ><span>新增</span></a></li>
        <li><a href="index.php?act=member_card&op=batch_add" ><span>批量导入</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div> 
  <div id="prompt">
    <div class="title"><h5>操作提示：你可以根据条件搜索会员，然后选择相应的操作</h5></div>
  </div>
  <div id="search-export">
  <form method="get" name="formSearch" id="formSearch">
    <input type="hidden" value="member_card" name="act"/>
    <input type="hidden" value="idnex" name="op"/>
    <select name="search_field_name" >
      <option <?php if($output['search_field_name']=='member_id'){ ?>selected='selected'<?php } ?> value="member_id">会员ID</option>
      <option <?php if($output['search_field_name']=='cardno'){ ?>selected='selected'<?php } ?> value="cardno">一卡通卡号</option>
           <option <?php if($output['search_field_name']=='gonghao'){ ?>selected='selected'<?php } ?> value="gonghao">工号</option>
    </select>
    <input type="text" value="<?php echo $output['search_field_value'];?>" name="search_field_value" class="txt"/>      
    <a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a>
    <?php if($output['search_field_value'] != ''){?>
      <a href="index.php?act=member_card&op=index" class="btns "><span><?php echo $lang['nc_cancel_search']?></span></a>
    <?php }?> 
    <!--导出功能-->
  <span id="excel"><a class="btns" href="index.php?act=member_card&op=index&op=exportExcel"><span>导出Excel</span></a></span>
  </form>
  </div>
  <form method="post" id="form_member">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2 nobdb">
      <thead>
        <tr class="thead">
          <th>&nbsp;</th>
          <th colspan="2" style="padding-left: 100px !important">会员</th>
          <th class="align-center">会员ID</th>
          <th class="align-center">卡号</th>
          <th class="align-center">工号</th>
          <th class="align-center">员工名称</th>
          <!-- <th class="align-center">积分</th> -->
          <th class="align-center">一卡通</th>
          <!-- <th class="align-center">经验值</th>
          <th class="align-center">级别</th> -->
          <th class="align-center">登录</th>
          <th class="align-center">操作</th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($output['member_list']) && is_array($output['member_list'])){ ?>
          <?php foreach($output['member_list'] as $v){ ?>
          <?php if(count($v)>2){ ?>
          <!-- <input name="member_id" type="hidden" value="<?php echo $v['member_id']?>"/> -->
        <tr class="hover member">
          <td>&nbsp;</td>
          <td class="w48 picture">
            <div class="size-44x44"><span class="thumb size-44x44"><i></i><img src="<?php if ($v['member_avatar'] != ''){ echo UPLOAD_SITE_URL.DS.ATTACH_AVATAR.DS.$v['member_avatar'];}else { echo UPLOAD_SITE_URL.'/'.ATTACH_COMMON.DS.C('default_user_portrait');}?>?<?php echo microtime();?>"  onload="javascript:DrawImage(this,44,44);"/></span>
            </div>
          </td>
          <td>
            <p class="name"><strong><?php echo $v['member_name']; ?></strong>(<?php echo $lang['member_index_true_name']?>: <?php echo $v['member_truename']; ?>)</p>
            <p class="smallfont"><?php echo $lang['member_index_reg_time']?>:&nbsp;<?php echo $v['member_time']; ?></p>            
              <div class="im"><span class="email" >
                <?php if($v['member_email'] != ''){ ?>
                <a href="mailto:<?php echo $v['member_email']; ?>" class=" yes" title="<?php echo $lang['member_index_email']?>:<?php echo $v['member_email']; ?>"><?php echo $v['member_email']; ?></a><?php echo $v['member_email']; ?></span>
                <?php }else { ?>
                <a href="JavaScript:void(0);" class="" title="<?php echo $lang['member_index_null']?>" ><?php echo $v['member_email']; ?></a></span>
                <?php } ?>
                <?php if($v['member_ww'] != ''){ ?>
                <a target="_blank" href="http://web.im.alisoft.com/msg.aw?v=2&uid=<?php echo $v['member_ww'];?>&site=cnalichn&s=11" class="" title="WangWang: <?php echo $v['member_ww'];?>"><img border="0" src="http://web.im.alisoft.com/online.aw?v=2&uid=<?php echo $v['member_ww'];?>&site=cntaobao&s=2&charset=<?php echo CHARSET;?>" /></a>
                <?php } ?>
                <?php if($v['member_qq'] != ''){ ?>                
                <a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $v['member_qq'];?>&site=qq&menu=yes" class=""  title="QQ: <?php echo $v['member_qq'];?>"><img border="0" src="http://wpa.qq.com/pa?p=2:<?php echo $v['member_qq'];?>:52"/></a>
                <?php } ?>
                <!-- 显示手机号码-->
               <?php if($v['member_mobile'] != ''){ ?>
               <div style="font-size:13px; padding-left:10px">&nbsp;&nbsp;<?php echo $v['member_mobile']; ?></div>
               <?php } ?>
              </div></td>
          <td class="align-center"><?php echo $v['member_id']; ?></td>
          <td class="align-center"><?php echo $v['cardno']; ?></td>
          <td class="w150 align-center"><?php echo $v['personalId']; ?></td>
          <td class="w150 align-center"><?php echo $v['Name']; ?></td>
          <!-- <td class="align-center"><?php echo $v['member_points']; ?></td> -->
          <td class="align-center"><p>可用:&nbsp;<strong class="red"><?php echo $v['balance']; ?></strong>&nbsp;<?php echo $lang['currency_zh']; ?></p>
          </td>
<!--           <td class="align-center"><?php echo $v['member_exppoints'];?></td>
          <td class="align-center"><?php echo $v['member_grade'];?></td> -->
          <td class="align-center"><?php echo $v['member_state'] == 1?$lang['member_edit_allow']:$lang['member_edit_deny']; ?></td>
          <td class="align-center">
            <a href="javascript:void(0)" value="<?php echo $v['member_id'];?>" cardno="<?php echo $v['cardno'];?>" name="unbind">解绑</a> 
          </td>
        </tr>
        <?php } ?>
        <?php } ?>
        <?php }else { ?>
        <tr class="no_data">
          <td colspan="11"><?php echo $lang['nc_no_record']?></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot class="tfoot">
        <?php if(!empty($output['member_list']) && is_array($output['member_list'])){ ?>
        <tr>        
          <td colspan="16"><div class="pagination"> <?php echo $output['page'];?></div></td>
        </tr>
        <?php } ?>
      </tfoot>
    </table>
  </form>
</div>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui.min.js" type="text/javascript"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" type="text/javascript" charset="utf-8" id="dialog_js"></script>

<script>
$(function(){
  var width=$(window).width();
  $('#search-export').css('left',(width-450));
  $(window).resize(function(){
    var resize_width=$(window).width();
    $('#search-export').css('left',(resize_width-450));
  });
    $('#ncsubmit').click(function(){
    	$('input[name="op"]').val('member_card');
      $('#formSearch').submit();
    });	
    $('[name="unbind"]').on('click',function(){
      if(confirm('您确定要解绑该会员吗？')){
        var member_id=$(this).attr('value');
        var cardno=$(this).attr('cardno');
        $.ajax({
          url:'index.php?act=member_card&op=edit',
          type:'post',
          data:{member_id:member_id,cardno:cardno},
          beforeSend:function(){},
          success:function(data){
            if(data==1){
              showDialog('已成功解绑该会员','succ','','','','','','','',3,'');
              setTimeout(function(){window.location.reload();},3000);
            }
            else{
              showDialog('解绑失败','err','','','','','','','','',3);
            }
          }
        });
      }      
    });
});
</script>
