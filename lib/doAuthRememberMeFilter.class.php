<?php

class doAuthRememberMeFilter extends sfFilter
{
  /**
   * Executes the filter chain.
   *
   * @param sfFilterChain $filterChain
   */
  public function execute($filterChain)
  {
    $cookieName = sfConfig::get('app_auth_remember_cookie_name', 'doRemember');

    if ($this->isFirstCall() && $this->context->getUser()->isAnonymous() && $cookie = $this->context->getRequest()->getCookie($cookieName)) {
      
        $value = unserialize(base64_decode($cookie));
        $user = Doctrine::getTable('User')->createQuery('u')
            ->where('u.username = ?', $value[0])->fetchOne();

      if ($user)
      {
        if ($value[2] == doAuthTools::rememberHash($user)) {
          $this->context->getUser()->signIn($q->fetchOne());
        }       
      }
    }

    $filterChain->execute();
  }
}
