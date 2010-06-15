<?php

class doAuthActions extends sfActions {
  public function executeSignin($request) {
    $user = $this->getUser();
    if ($user->isAuthenticated()) {
      return $this->redirect('@homepage');
    }

    $this->form = new SigninForm();
    
    $this->preSignin($request);

    if ($request->isMethod('post')) {
      $this->form->bind($request->getParameter('signin'));
      if ($this->form->isValid()) {
        $values = $this->form->getValues();
        $this->getUser()->signin($values['user'], array_key_exists('remember', $values) ? $values['remember'] : false);

        $this->postSignin($request);

        // always redirect to a URL set in app.yml
        // or to the referer
        // or to the homepage
        $signinUrl = sfConfig::get('app_doAuth_signin_url', $user->getReferer($request->getReferer()));

        return $this->redirect('' != $signinUrl ? $signinUrl : '@homepage');
      }
    }
    else {

      // if we have been forwarded, then the referer is the current URL
      // if not, this is the referer of the current request
      $user->setReferer($this->getContext()->getActionStack()->getSize() > 1 ? $request->getUri() : $request->getReferer());

      $module = sfConfig::get('sf_login_module');
      if ($this->getModuleName() != $module) {
        $this->getLogger()->warning('User is accessing signin action which is currently not configured in settings.yml. Please secure this action or update configuration');
      }
    }
  }


  public function executeSignout($request) {
    $this->getUser()->signOut();

    $this->postSignOut($request);

    $signoutUrl = sfConfig::get('app_doAuth_signout_url', $request->getReferer());

    $this->redirect('' != $signoutUrl ? $signoutUrl : '@homepage');
  }

  public function executeRegister(sfWebRequest $request) {

    $this->form = new RegisterUserForm();

    $this->dispatcher->notify(new sfEvent($this, 'user.pre_register'));
    
    $this->preRegister($request);

    if ($request->isMethod('post')) {
      $this->form->bind($request->getParameter('user'),$request->getParameter('user'));
      if ($this->form->isValid()) {
        $this->form->save();
        $user = $this->form->getObject();
        $user->setPassword($this->form->getValue('password'));
        $user->save();

        $this->user = $user;

        $this->dispatcher->notify(new sfEvent($this, 'user.registered',array('password'=> $this->form->getValue('password'))));
        $this->postRegister($request);

        if (!sfConfig::get('app_doAuth_activation',false)) {
          $user->setIsActive(1);
          $user->save();
          $this->firstSignin();
        } else {
          $this->getUser()->setFlash('notice','Please check your email to finish registration process');
        }

        if ($params = sfConfig::get('app_doAuth_register_forward')) {
          list($module, $action) = $params;
          $this->forward($module, $action);
        }
        $this->redirect(sfConfig::get('app_doAuth_register_redirect','@homepage'));
      }
    }
  }

  public function executeActivate(sfWebRequest $request) {

    $this->preActivate($request);

    $activation = Doctrine::getTable('UserActivationCode')->createQuery('a')->
      innerJoin('a.User u')->
      where('a.code = ?', $request->getParameter('code'))->fetchOne();

    $this->forward404Unless($activation,'wrong activation code used');

    $user = $activation->getUser();
    $user->setIsActive(1);
    $user->save();
    $activation->delete();

    $this->user = $user;

    $this->dispatcher->notify(new sfEvent($this, 'user.activated'));    
    $this->postActivate($request);

    $this->getUser()->getAttributeHolder()->removeNamespace('doUser');
    $this->firstSignin();

    $this->redirect(sfConfig::get('app_doAuth_register_redirect','@homepage'));
  }

  public function executeSecure($request) {
    $this->getResponse()->setStatusCode(403);
  }

  public function executeResetPassword(sfWebRequest $request) {

    if ($request->hasParameter('user')) {
      $user = Doctrine::getTable('User')->find($request->getParameter('user'));
      $this->forward404Unless($user);
      if ($request->getParameter('code') != doAuthTools::passwordResetCode($user)) {
        $this->getUser()->setFlash('error','Password reset code is invalid');
        $this->forward404();
      }
      $password = doAuthTools::generatePassword();
      doAuthMailer::sendNewPassword($this,$user,$password);
      $user->setPassword($password);
      $user->save();
      $this->getUser()->setFlash('notice','We have sent a new password on your email');
      $this->redirect(sfConfig::get('app_doAuth_reset_password_url', '@signin'));
    }

    $this->form = new ResetPasswordForm();

    if ($request->isMethod('post')) {
      $this->form->bind($request->getParameter('reset_password'));
      if ($this->form->isValid()) {
        $user = Doctrine::getTable('User')->findOneByEmail($this->form->getValue('email') );
        doAuthMailer::sendPasswordRequest($this,$user);
        $this->getUser()->setFlash('notice','You have requested a new password. Please, check your email and follow the instructions.');
        $this->redirect(sfConfig::get('app_doAuth_reset_password_url', '@signin'));
      }
    }
  }

  /**
   *
   * Automaticaly signs in current user after registration 
   */

  protected function firstSignin()
  {
    if (sfConfig::get('app_doAuth_register_signin',true)) {
      $this->getUser()->signIn($this->user);
      $this->getUser()->setFlash('notice','Congratulations! You are now registered.');
    } else {
      $this->getUser()->setFlash('notice','Congratulations! You are now registered. Please, sign in');
    }
  }

  // use this methods in your class to extend a functionality

  protected function preSignin(sfWebRequest $request) {}
  protected function postSignin(sfWebRequest $request) {}

  protected function preRegister(sfWebRequest $request) {}
  protected function postRegister(sfWebRequest $request) {}

  protected function preActivate(sfWebRequest $request) {}
  protected function postActivate(sfWebRequest $request) {}

  protected function postSignOut(sfWebRequest $request) {}
}
