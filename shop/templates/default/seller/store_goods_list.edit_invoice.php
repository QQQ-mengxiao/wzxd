<div class="eject_con">
  <div id="warning" class="alert alert-error"></div>
  <form method="post" action="<?php echo urlShop('store_goods_online', 'edit_invoice');?>" id="invoice_form">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="commonid" value="<?php echo $_GET['commonid']; ?>" />
    <dl>
      <dt>是否开具发票：</dt>
      <dd>
       <!--  <p>
          <label>普通发票</label>
          <select name="plate_top">
            <option value="开具" selected="selected">是</option>
             <option value="不开具" selected="selected">否</option>
          </select>
        </p> -->
        <p>
          <label>增值税专用发票</label>
          <select name="invoice_top">
            <option value="1" selected="selected">是</option>
             <option value="0" selected="selected">否</option>
          </select>
        </p>
       <!--  <p class="hint">如不填，所有已选版式将制空，请谨慎操作</p> -->
      </dd>
    </dl>
    <div class="bottom">
      <label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_submit'];?>"/></label>
    </div>
  </form>
</div>
<script>
$(function(){
    $('#invoice_form').submit(function(){
        ajaxpost('invoice_form', '', '', 'onerror');
        return false;
    });
});
</script>