Here is your new credentials.

Username: <?php echo $user->getUsername() ?>
Password: <?php echo $password ?>

Use your new login and password and login to enter the site:

<?php echo url_for('@signin',array('absolute'=> true)) ?>