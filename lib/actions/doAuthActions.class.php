<?php

class doAuthActions extends sfActions {
  public function executeSignin($request) {
    $user = $this->getUser();
    if ($user->isAuthenticated()) {
      return $this->redirect('@homepage');
    }

    $this->form = new SigninUserForm();

    if ($request->isMethod('post')) {
      $this->form->bind($request->getParameter('signin'));
      if ($this->form->isValid()) {
        $values = $this->form->getValues();
        $this->getUser()->signin($values['user'], array_key_exists('remember', $values) ? $values['remember'] : false);

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
        $this->getLogger()->warning('User is accessing signin action which is currently not configured in settings.yml. Please secure this action, or update configuration');
      }
    }
  }


  public function executeSignout($request) {
    $this->getUser()->signOut();

    $signoutUrl = sfConfig::get('app_doAuth_signout_url', $request->getReferer());

    $this->redirect('' != $signoutUrl ? $signoutUrl : '@homepage');
  }

  public function executeRegister(sfWebRequest $request) {

    $this->form = new RegisterUserForm();
    if ($request->isMethod('post')) {
      $this->form->bind($request->getParameter('user'));
      if ($this->form->isValid()) {
        $this->form->save();
        $user = $this->form->getObject();
        $user->setPassword($this->form->getValue('password'));
        $user->save();

        $this->user = $user;
        $event = new sfEvent($this, 'user.registered');
        $this->dispatcher->notify($event);

        if (!sfConfig::get('app_user_activation',false)) {
          $user->setIsActive(1);
          $user->save();
          $this->freshSignin();
        }

        $this->redirect(sfConfig::get('app_user_register_redirect','@homepage'));
      }
    }
  }

  public function executeActivate(sfWebRequest $request) {

    // check stored in session activation data
    $this->forward404Unless($this->getUser()->getAttribute('activation_code','doAuth') == $activation->getCode());
    $this->getUser()->getAttributeHolder()->removeNamespace('doUser');

    $activation = Doctrine::getTable('UserActivationCode')->createQuery('a')->
      innerJoin('a.User u', $params)->
      where('a.code = ?', $request->getParameter('code'))->fetchOne();

    $this->forward404Unless($activation,'wrong activation code used');

    $user = $activation->getUser();
    $user->setIsActive(1);
    $user->save();
    $activation->delete();

    $this->user = $user;

    $event = new sfEvent($this, 'user.activated');
    $this->dispatcher->notify($event);

    $this->freshSignin();

    $this->redirect(sfConfig::get('app_user_register_redirect','@homepage'));
  }

  private function freshSignin() {
    
    if (sfConfig::get('app_doAuth_register_sign_in',true)) {
      $this->getUser()->signIn($this->user);
      $this->getUser()->setFlash('notice','Congratulations! You are now registered.');
    } else {
      $this->getUser()->setFlash('notice','Congratulations! You are now registered. Please, sign in');
    }

  }

  public function executeSecure($request) {
    $this->getResponse()->setStatusCode(403);
  }

  public function executePassword($request) {
    throw new sfException('This method is not yet implemented.');
  }
}
