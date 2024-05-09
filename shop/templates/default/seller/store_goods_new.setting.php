<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<div class="ncsc-form-default">
  <form method="post"  action="index.php?act=store_goods_new&op=setting" enctype="multipart/form-data" id="my_store_form">
    <input type="hidden" name="form_submit" value="ok" />
      <dl>
          <dd>
              <p style="font-size: x-large;">新品规则设置：</p>
          </dd>
      </dl>
    <dl>
      <dt>新品展示时间：</dt>
        <dd>
            <ul>
                <li>
                    <input type="text" name="goods_show_time" id="goods_show_time" value=<?php echo $output['goods_show_time']?>><em class="add-on"><b>小时</b></em>
                </li>
            </ul>
            <p class="hint vital">*保存后会同步当前所有新品的展示时间。</p>
        </dd>
    </dl>
    <dl>
      <dt>新品折扣率：</dt>
        <dd>
            <ul>
                <li>
                    <input type="text" name="goods_show_discount" id="goods_show_discount" value=<?php echo $output['goods_show_discount']?>><em class="add-on"><b>%</b></em>
                </li>
            </ul>
            <p class="hint vital">*保存后<span style="color: red">不</span>会同步当前所有新品价格比例。</p>
        </dd>
    </dl>
    <div class="bottom">
        <label class="submit-border"><input type="submit" class="submit" value="提交" /></label>
      </div>
  </form>
</div>
<hr>
<div class="ncsc-form-default" style="display: none">
  <form method="post"  action="index.php?act=store_goods_online&op=setting" enctype="multipart/form-data" id="my_store_form">
    <input type="hidden" name="form_submit" value="ok" />
      <dl>
          <dd>
              <p style="font-size: x-large;">进销存同步设置：</p>
          </dd>
      </dl>
    <dl>
      <dt>是否自动同步：</dt>
        <dd>
            <ul class="ncsc-form-radio-list">
                <li>
                    <input type="radio" name="auto_cw" id="auto_cw_1" value="1" <?php if($output['auto_cw']['auto_cw'] == 1) {?>checked<?php }?>>
                    <label for="auto_cw_1">是</label>
                </li>
                <li>
                    <input type="radio" name="auto_cw" id="auto_cw_0" value="0" <?php if($output['auto_cw']['auto_cw'] == 0) {?>checked<?php }?>>
                    <label for="auto_cw_0">否</label>
                </li>
            </ul>
            <p class="hint vital">*开启自动同步后，指定仓库的商品每十分钟进行一次自动同步库存。</p>
        </dd>
    </dl>
    <div class="bottom">
        <label class="submit-border"><input type="submit" class="submit" value="提交" /></label>
      </div>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/ajaxfileupload/ajaxfileupload.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.min.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script type="text/javascript">
var SITEURL = "<?php echo SHOP_SITE_URL; ?>";
</script>
