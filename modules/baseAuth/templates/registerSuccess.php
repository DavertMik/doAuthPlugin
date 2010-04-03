<?php use_helper('I18N') ?>
<?php echo form_tag('user/register') ?>
<table>
<?php echo $form ?>
  <tr><td colspan="2">
      <input type="submit" id="submit" name="submit" value="<?php echo __('Register') ?> " />
  </td></tr>
</table>
</form>