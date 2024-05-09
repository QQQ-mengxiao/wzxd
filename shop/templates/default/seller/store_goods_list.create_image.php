<div class="eject_con">
  <div id="warning" class="alert alert-error"></div>
  <form method="get" action="<?php echo urlShop('create_image', 'create_image');?>" id="create_image">
    <input type="hidden" name="act" value="create_image" />
    <input type="hidden" name="op" value="create_image" />
    <input type="hidden" name="bg_image_id" value="<?php echo $bg_image_id; ?>" />
    <input type="hidden" name="commonid" value="<?php echo $_GET['commonid']; ?>" />
    <dl>
      <dt>左边距：</dt>
      <dd>
        <input type="text" class="text" name="image_x" id="image_x" value="" />
        <p class="hint">图片在背景图片的左边距，单位px</p>
      </dd>
    </dl>
    <dl>
      <dt>上边距：</dt>
      <dd>
        <input type="text" class="text" name="image_y" id="g_jingle" value="" />
        <p class="hint">图片在背景图片的上边距，单位px</p>
      </dd>
    </dl>
    <dl>
      <dt>图片宽度：</dt>
      <dd>
        <input type="text" class="text" name="poster_x" id="poster_w" value="" />
        <p class="hint">图片宽度,单位px</p>
      </dd>
    </dl>
    <dl>
      <dt>图片高度：</dt>
      <dd>
        <input type="text" class="text" name="poster_y" id="poster_y" value="" />
        <p class="hint">图片的高度，单位px</p>
      </dd>
    </dl>
    <!-- <dl>
      <dt>背景图片：</dt>
      <dd>
        <input type="text" class="text w300" name="bg_image_id" id="bg_image_id" value="" />
        <p class="hint"></p>
      </dd>
    </dl> -->
    <div class="bottom">
      <label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_submit'];?>"/></label>
    </div>
  </form>
</div>