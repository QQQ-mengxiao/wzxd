<?php defined('In718Shop') or exit('Access Invalid!');?>
<style type="text/css">
.mb-item-edit-content { background: #EFFAFE url(<?php echo ADMIN_TEMPLATES_URL;?>/images/cms_edit_bg_line.png) repeat-y scroll 0 0;}
</style>
<?php if($item_edit_flag) { ?>
<table class="table tb-type2" id="prompt" style="background-color: #FFF; border-bottom: solid 1px #deeffb">
    <tbody>
      <tr class="space odd">
        <th colspan="12" class="nobg"> <div class="title nomargin">
            <h5><?php echo $lang['nc_prompts'];?></h5>
            <span class="arrow"></span> </div>
        </th>
      </tr>
      <tr>
        <td><ul>
            <li>从右侧筛选按钮，点击添加按钮完成添加</li>
            <li>鼠标移动到已有商品上，会出现删除按钮可以对商品进行删除</li>
            <li>操作完成后点击保存编辑按钮进行保存</li>
          </ul></td>
      </tr>
    </tbody>
  </table>
  <?php } ?>
<div class="index_block goods-list">
  <?php if($item_edit_flag) { ?>
  <h3>商品版块</h3>
  <?php } ?>
  <div class="title">
    <?php if($item_edit_flag) { ?>
    <h5>标题：</h5>
    <input id="home1_title" type="text" class="txt w200" name="item_data[title]" value="<?php echo $item_data['title'];?>">
    <?php } else { ?>
    <span><?php echo $item_data['title'];?></span>
    <?php } ?>
  </div>
  <div nctype="item_content" class="content">
    <?php if($item_edit_flag) { ?>
    <h5>内容：</h5>
    <?php } ?>
    <div class="item_content">
    <input type="hidden" id="orderlist" value="<?php echo $output['order'];?>" />
    <?php if(!empty($item_data['item']) && is_array($item_data['item'])) {?>
    <?php foreach($item_data['item'] as $item_value) {?>
    <div nctype="item_image" class="item">
      <div class="goods-pic"><img nctype="goods_image" src="<?php echo cthumb($item_value['goods_image']);?>" alt=""></div>
      <div class="goods-name" nctype="goods_name"><?php echo $item_value['goods_name'];?></div>
      <div class="goods-price" nctype="goods_price">￥<?php echo $item_value['goods_price'];?></div>
      <?php if($item_edit_flag) { ?>
      <input nctype="goods_id" name="item_data[item][]" type="hidden" value="<?php echo $item_value['goods_id'];?>">
      <a nctype="btn_del_item_image" href="javascript:;"><i class="icon-trash"></i>删除</a>
      <?php } ?>
    </div>
    <?php } ?>
    <?php } ?>
  </div>
</div>
</div>
<?php if($item_edit_flag) { ?>
<div class="search-goods">
<h3>选择商品添加</h3>
  <h5>商品关键字：</h5>
  <input id="txt_goods_name" type="text" class="txt w200" name="">
  <h5>商品货号：</h5>
  <input id="txt_goods_serial" type="text" class="txt w200" name="">
  <a id="btn_mb_special_goods_search" class="btn-search" href="javascript:;" style="vertical-align: top; margin-left: 5px;" title="搜索"></a>
  <div id="mb_special_goods_list"></div>
</div>
<?php } ?>
<script id="item_goods_template" type="text/html">
    <div nctype="item_image" class="item">
        <div class="goods-pic"><img nctype="image" src="<%=goods_image%>" alt=""></div>
        <div class="goods-name" nctype="goods_name"><%=goods_name%></div>
        <div class="goods-price" nctype="goods_price"><%=goods_price%></div>
        <input nctype="goods_id" name="item_data[item][]" type="hidden" value="<%=goods_id%>">
        <a nctype="btn_del_item_image" href="javascript:;">删除</a>
    </div>
</script> 
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.ajaxContent.pack.js" type="text/javascript"></script> 
<script type="text/javascript">
    $(function(){
        $('#btn_mb_special_goods_search').on('click', function() {
            var url = '<?php echo urlAdmin('mb_special1', 'goods_list');?>';
            var keyword = $('#txt_goods_name').val();
            var goods_serial = $('#txt_goods_serial').val();
            if (keyword || goods_serial) {
                $('#mb_special_goods_list').load(url + '&' + $.param({keyword: keyword, goods_serial: goods_serial}));
            }
        });

        $('#mb_special_goods_list').on('click', '[nctype="btn_add_goods"]', function() {
            var item = {};
            if($('input[value="'+$(this).attr('data-goods-id')+'"]').val()){
                showDialog('该商品已添加，请勿重复添加！');
                return;
            }
            item.goods_id = $(this).attr('data-goods-id');
            item.goods_name = $(this).attr('data-goods-name');
            item.goods_price = $(this).attr('data-goods-price');
            item.goods_image = $(this).attr('data-goods-image');
            var html = template.render('item_goods_template', item);
            $('[nctype="item_content"]').append(html);
        });

        $('[nctype="goods_image"]').on('mouseover',function(){
          $(this).css("cursor","pointer")
        });
        var $loader = $('#load');
        var $orderlist = $("#orderlist");
        var $list = $(".item_content");
        $list.sortable({
          opacity: 0.6,
          revert: true,
          // scroll: true,
          tolerance:'pointer',
          cursor: 'pointer',
          handle: '[nctype="goods_image"]',
          update: function(){
             var new_order = [];
             $list.find('[nctype="item_image"]').children('[nctype="goods_id"]').each(function() {
                new_order.push(this.value);
                //console.log(new_order);
              });
             var update_order= new_order.join(',');// 转换为字符串
             var before_order = $orderlist.val();
             var item_id = $('[name="item_id"]').val();
             // console.log(update_order);
             $.ajax({
                      type: "post",
                      url:  "index.php?act=mb_special&op=goods_sort",
                      // url: "sort.update.php",
                      //id:新的排列对应的ID,order：原排列顺序
                      data: {order: update_order, b_order: before_order ,item_id : item_id},
                      beforeSend: function(){
                        $loader.html("<img src='<?php echo UPLOAD_SITE_URL.DS.ATTACH_COMMON.DS."load.gif"; ?>'/>");
                      },
                      success: function(msg){
                        // alert(msg);
                         $loader.html("");
                      }
                   });
            // $.post("index.php?act=mb_special&op=get_data", {order: new_order, b_order: before_order});
          }
        });
    });
</script> 
