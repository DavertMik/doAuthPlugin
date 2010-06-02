<?php
/**
 * Listener for doAuthActions, recives notifies and sends emails
 *
 * @author Davert
 */
class doAuthMailer {

  public static function sendActivation(sfEvent $event) {

    $controller = $event->getSubject();
    $user = $controller->user;

    // already activated
    if ($user->getIsActive()) return;

    // save password to send in register email
    $controller->getUser()->setAttribute('user_password',$event['password'],'doPreUser');

    $activation = new UserActivationCode();
    $activation->setUserId($user->getId());
    $activation->setCode(doAuthTools::activationCode($user));
    $activation->save();

    $subject = sfConfig::get('sf_i18n') ? $controller->getContext()->getI18N()->__('Your activation code') :'Your activation code';

    // message should be sent immediately 
    $controller->getMailer()->composeAndSend(
      sfConfig::get('app_doAuth_email_from','mailer@'.$controller->getRequest()->getHost()),
      array($user->getEmail() => $user->getUsername()),
      $subject,
      $controller->getPartial(sfConfig::get('app_doAuth_email_module',$controller->getModuleName()).'/mail_activation', array('code'=> $activation->getCode())),'text/plain');
  }

  public static function sendRegistration(sfEvent $event) {

    $controller = $event->getSubject();
    $user = $controller->user;
    $password = $event->offsetExists('password') ? $event['password'] : $controller->getUser()->getAttribute('user_password',null,'doPreUser');

    $subject = sfConfig::get('sf_i18n') ? $controller->getContext()->getI18N()->__('Thank you for registering') :'Thank you for registering';

    // message should be sent immediately
    $controller->getMailer()->composeAndSend(
      sfConfig::get('app_doAuth_email_from','mailer@'.$controller->getRequest()->getHost()),
      array($user->getEmail() => $user->getUsername()),
      $subject,
      $controller->getPartial(sfConfig::get('app_doAuth_email_module',$controller->getModuleName()).'/mail_registration', array('user'=> $controller->user, 'password'=> $password)),'text/plain');
  }

  public static function sendPasswordRequest(userActions $controller, User $user) {

    $subject = sfConfig::get('sf_i18n') ? $controller->getContext()->getI18N()->__('Password reset') :'Password reset';

    $code = doAuthTools::passwordResetCode($user);

    $controller->getMailer()->composeAndSend(
      sfConfig::get('app_doAuth_email_from','mailer@'.$controller->getRequest()->getHost()),
      array($user->getEmail() => $user->getUsername()),
      $subject,
      $controller->getPartial(sfConfig::get('app_doAuth_email_module',$controller->getModuleName()).'/mail_reset_password', array('user'=> $user, 'code'=> $code)),'text/plain');
  }

  public static function sendNewPassword(userActions $controller, User $user, $password) {

    $subject = sfConfig::get('sf_i18n') ? $controller->getContext()->getI18N()->__('Your new password') :'Your new password';

    $controller->getMailer()->composeAndSend(
      sfConfig::get('app_doAuth_email_from','mailer@'.$controller->getRequest()->getHost()),
      array($user->getEmail() => $user->getUsername()),
      $subject,
      $controller->getPartial(sfConfig::get('app_doAuth_email_module',$controller->getModuleName()).'/mail_new_password', array('user'=> $user, 'password'=> $password)),'text/plain');
  }
  
  
}
?>
