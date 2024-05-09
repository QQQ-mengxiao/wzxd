<?php defined('In718Shop') or exit('Access Invalid!');?>
<?php if($item_edit_flag) { ?>
<table class="table tb-type2" id="prompt">
    <tbody>
      <tr class="space odd">
        <th colspan="12" class="nobg"> <div class="title nomargin">
            <h5><?php echo $lang['nc_prompts'];?></h5>
            <span class="arrow"></span> </div>
        </th>
      </tr>
      <tr>
        <td><ul>
            <li>鼠标移动到内容上出现编辑按钮可以对内容进行修改</li>
            <li>操作完成后点击保存编辑按钮进行保存</li>
          </ul></td>
      </tr>
    </tbody>
  </table>
  <?php } ?>
<div class="index_block home2">
      <?php if($item_edit_flag) { ?>
  <h3>国家馆主题区</h3>
  <?php } ?>
  <div class="title">
    <?php if($item_edit_flag) { ?>
    <h5>标题：</h5>
    <input id="home1_title" type="text" class="txt w200" name="item_data[title]" value="<?php echo $item_data['title'];?>">
    <?php } else { ?>
    <span><?php echo $item_data['title'];?></span>
    <?php } ?>
  </div>
  <div class="content">
      <?php if($item_edit_flag) { ?>
    <h5>内容：</h5>
    <?php } ?>
    <div class="home2_1" style="height: 100px;width: 320px;">
      <div nctype="item_image" class="item" style="height: 100px;width: 320px;"> <img style="width: 320px;height: 90px;" nctype="image" src="<?php echo getMbSpecial1ImageUrl($item_data['square_image']);?>" alt="">
        <?php if($item_edit_flag) { ?>
        <input nctype="image_name" name="item_data[square_image]" type="hidden" value="<?php echo $item_data['square_image'];?>">
        <input nctype="image_type" name="item_data[square_type]" type="hidden" value="<?php echo $item_data['square_type'];?>">
        <input nctype="image_data" name="item_data[square_data]" type="hidden" value="<?php echo $item_data['square_data'];?>">
        <a nctype="btn_edit_item_image" data-desc="640*130" href="javascript:;"><i class="icon-edit"></i>编辑</a>
        <?php } ?>
      </div>
    </div>
    <div class="home2_2" style="width: 320px;padding-top: 10px;">
      <div class="home2_2_1" style="float: left;height: 50px;">
        <div nctype="item_image" class="item" style="width: 150px;height: 50px;"> <img nctype="image" style="width: 150px;padding-left: 8px;height: 50px;" src="<?php echo getMbSpecial1ImageUrl($item_data['rectangle1_image']);?>" alt="">
          <?php if($item_edit_flag) { ?>
          <input nctype="image_name" name="item_data[rectangle1_image]" type="hidden" value="<?php echo $item_data['rectangle1_image'];?>">
          <input nctype="image_type" name="item_data[rectangle1_type]" type="hidden" value="<?php echo $item_data['rectangle1_type'];?>">
          <input nctype="image_data" name="item_data[rectangle1_data]" type="hidden" value="<?php echo $item_data['rectangle1_data'];?>">
          <a nctype="btn_edit_item_image" data-desc="320*85" href="javascript:;"><i class="icon-edit"></i>编辑</a>
          <?php } ?>
        </div>
      </div>
      <div class="home2_2_2" style="float: left;height: 50px;">
        <div nctype="item_image" class="item" style="width: 150px;height: 50px;"> <img nctype="image" style="width: 150px;padding-left: 8px;height: 50px;" src="<?php echo getMbSpecial1ImageUrl($item_data['rectangle2_image']);?>" alt="">
          <?php if($item_edit_flag) { ?>
          <input nctype="image_name" name="item_data[rectangle2_image]" type="hidden" value="<?php echo $item_data['rectangle2_image'];?>">
          <input nctype="image_type" name="item_data[rectangle2_type]" type="hidden" value="<?php echo $item_data['rectangle2_type'];?>">
          <input nctype="image_data" name="item_data[rectangle2_data]" type="hidden" value="<?php echo $item_data['rectangle2_data'];?>">
          <a nctype="btn_edit_item_image" data-desc="320*85" href="javascript:;"><i class="icon-edit"></i>编辑</a>
          <?php } ?>
        </div>
      </div>
        <div class="home2_2_3" style="float: left;clear: left;height: 50px;">
            <div nctype="item_image" class="item" style="width: 150px;height: 50px;"> <img nctype="image" style="width: 150px;padding-left: 8px;height: 50px;" src="<?php echo getMbSpecial1ImageUrl($item_data['rectangle3_image']);?>" alt="">
                <?php if($item_edit_flag) { ?>
                    <input nctype="image_name" name="item_data[rectangle3_image]" type="hidden" value="<?php echo $item_data['rectangle3_image'];?>">
                    <input nctype="image_type" name="item_data[rectangle3_type]" type="hidden" value="<?php echo $item_data['rectangle3_type'];?>">
                    <input nctype="image_data" name="item_data[rectangle3_data]" type="hidden" value="<?php echo $item_data['rectangle3_data'];?>">
                    <a nctype="btn_edit_item_image" data-desc="320*85" href="javascript:;"><i class="icon-edit"></i>编辑</a>
                <?php } ?>
            </div>
        </div>
        <div class="home2_2_4" style="float: left;height: 50px;">
            <div nctype="item_image" class="item" style="width: 150px;height: 50px;"> <img nctype="image" style="width: 150px;padding-left: 8px;height: 50px;" src="<?php echo getMbSpecial1ImageUrl($item_data['rectangle4_image']);?>" alt="">
                <?php if($item_edit_flag) { ?>
                    <input nctype="image_name" name="item_data[rectangle4_image]" type="hidden" value="<?php echo $item_data['rectangle4_image'];?>">
                    <input nctype="image_type" name="item_data[rectangle4_type]" type="hidden" value="<?php echo $item_data['rectangle4_type'];?>">
                    <input nctype="image_data" name="item_data[rectangle4_data]" type="hidden" value="<?php echo $item_data['rectangle4_data'];?>">
                    <a nctype="btn_edit_item_image" data-desc="320*85" href="javascript:;"><i class="icon-edit"></i>编辑</a>
                <?php } ?>
            </div>
        </div>
    </div>
  </div>
</div>
