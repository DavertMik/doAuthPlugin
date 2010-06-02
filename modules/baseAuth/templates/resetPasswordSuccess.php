<?php use_helper('I18N') ?>

<h1><?php echo __('Password reset') ?></h1>

<form action="<?php echo url_for('@password') ?>" method="post">
  <table>
    <?php echo $form ?>
  </table>

  <input type="submit" value="<?php echo __('Password reset') ?>" />

</form>
