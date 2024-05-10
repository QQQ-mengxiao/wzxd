<?php defined('In718Shop') or exit('Access Invalid!');?>
<style type="text/css">
table tr{
  text-align: center;
}
table tr td{
  width:50px;
}
</style>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>一卡通管理</h3>
      <ul class="tab-base">
        <li><a href="index.php?act=member_card&op=index" ><span>管理</span></a></li>
        <li><a href="index.php?act=member_card&op=member_add" ><span>新增</span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span>批量导入</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <div id="prompt">
    <div class="title"><h5>操作提示：你可以上传csv格式的Excel文件</h5></div>
  </div><br/><br/>

  <div style="font-size:14px">请选择要上传的文件：</div><br/>
  <form enctype="multipart/form-data" name="upform" id="upform" method="post" action="index.php?act=member_card&op=batch_add_handle">
     上传文件：<input name="upfile" id="upfile" type="file" "/><span id="alr_msg"></span><br/>
     <input type="hidden" name="charset" value="gbk"/><br/>
     <div style="font-size: 14px;color:#0099CC">文件格式示例：<a href="<?php echo RESOURCE_SITE_URL;?>/examples/card_import.csv" class="btns"><span style="margin:0">点击下载文件示例</span></a></div><br/>
     <table border="1" bgcolor="#B8EEF6">
     <tr> <td>姓名</td>   <td>工号</td>  <td>会员id</td> <td>卡号</td>     </tr>
     <tr> <td>赵**</td>   <td>100</td>   <td>1</td>      <td>000001</td>   </tr>
     <tr> <td>钱**</td>   <td>101</td>   <td>2</td>      <td>000002</td>   </tr>
     <tr> <td>孙**</td>   <td>102</td>   <td>3</td>      <td>000003</td>   </tr>
     <tr> <td>李**</td>   <td>103</td>   <td>4</td>      <td>000004</td>   </tr>
     </table><br/>
     <input type="submit" value="上传" id="submit"/>
  </form> <br/>
  <div id="up_msg"><?php if(!empty($output['msg'])){echo $output['msg'];?><a href="index.php?act=member_card&op=index">，点此查看</a>成功插入的会员列表。<?php } ?></div>
</div>

<script type="text/javascript">
$(function(){
  $('#submit').on('click',function(){
    var doc=$('[name="upfile"]').val();
    if(doc.length>1&&doc!=''){
      var dot=doc.lastIndexOf('.');
      var type=doc.substring(dot+1);
      // var allow_type=new Array();
      // allow_type=['xls','xlsx','XLS','XLSX'];
      // if($.inArray(type,allow_type)==-1){
      if(type!='csv'){
        $('#alr_msg').html("<font color='red'>请选择csv格式文件</font>");
        return false;
      }
      else{
        $('#alr_msg').empty();
        $('[type="submit"]').submit();
      }
    }
    else{
      $('#alr_msg').html("<font color='red'>未选择任何文件</font>");
      return false;
    }
  });
});
</script>
